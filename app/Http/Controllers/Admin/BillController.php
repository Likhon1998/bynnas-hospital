<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BillRequest;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\HealthPackage;
use App\Models\HospitalSetting;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(Request $request): View
    {
        $bills = Bill::query()
            ->with(['patient', 'appointment.doctor'])
            ->when($request->string('status')->toString(), function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where(function ($builder) use ($term): void {
                    $builder->where('bill_no', 'like', '%'.$term.'%')
                        ->orWhereHas('patient', fn ($patient) => $patient->where('full_name', 'like', '%'.$term.'%'));
                });
            })
            ->orderByDesc('bill_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.bills.index', compact('bills'));
    }

    public function create(Request $request): View
    {
        return view('admin.bills.create', $this->formData([
            'bill' => new Bill([
                'bill_date' => now()->toDateString(),
                'discount' => 0,
                'patient_id' => $request->integer('patient_id') ?: null,
                'appointment_id' => $request->integer('appointment_id') ?: null,
            ]),
        ]));
    }

    public function store(BillRequest $request): RedirectResponse
    {
        $bill = Bill::createFromReception([
            'patient_id' => $request->integer('patient_id'),
            'appointment_id' => $request->integer('appointment_id') ?: null,
            'issued_by' => $request->user()->id,
            'bill_date' => $request->date('bill_date')?->toDateString() ?? now()->toDateString(),
            'status' => 'draft',
            'discount' => $request->input('discount', 0),
            'notes' => $request->validated('notes'),
        ], $request->validated('items'));

        if ($request->input('action') === 'issue') {
            $this->issueBill($bill);
        }

        return redirect()->route('admin.bills.show', $bill)->with('status', 'Bill saved.');
    }

    public function show(Bill $bill): View
    {
        $bill->load(['patient', 'appointment.doctor', 'appointment.department', 'issuedBy', 'billItems']);

        return view('admin.bills.show', compact('bill'));
    }

    public function edit(Bill $bill): View
    {
        abort_unless($bill->isEditable(), 403);

        $bill->load('billItems');

        return view('admin.bills.edit', $this->formData(compact('bill')));
    }

    public function update(BillRequest $request, Bill $bill): RedirectResponse
    {
        abort_unless($bill->isEditable(), 403);

        $bill->update([
            'patient_id' => $request->integer('patient_id'),
            'appointment_id' => $request->integer('appointment_id') ?: null,
            'bill_date' => $request->date('bill_date')?->toDateString() ?? $bill->bill_date?->toDateString(),
            'discount' => $request->input('discount', 0),
            'notes' => $request->validated('notes'),
        ]);

        $bill->syncItems($request->validated('items'));

        if ($request->input('action') === 'issue') {
            $this->issueBill($bill->fresh());
        }

        return redirect()->route('admin.bills.show', $bill)->with('status', 'Bill updated.');
    }

    public function destroy(Bill $bill): RedirectResponse
    {
        abort_unless($bill->isDraft(), 403);

        $bill->delete();

        return redirect()->route('admin.bills.index')->with('status', 'Draft bill deleted.');
    }

    public function issue(Bill $bill): RedirectResponse
    {
        abort_unless($bill->canIssue(), 403);

        $this->issueBill($bill);

        return redirect()->route('admin.bills.show', $bill)->with('status', 'Bill issued.');
    }

    public function pay(Bill $bill): RedirectResponse
    {
        abort_unless($bill->canMarkPaid(), 403);

        $bill->update([
            'status' => 'paid',
            'paid_amount' => $bill->total,
            'paid_at' => now(),
        ]);

        return redirect()->route('admin.bills.show', $bill)->with('status', 'Bill marked as paid.');
    }

    public function cancel(Request $request, Bill $bill): RedirectResponse
    {
        abort_unless($bill->canCancel(), 403);

        $validated = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $bill->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $validated['cancellation_reason'] ?? null,
            'paid_amount' => 0,
        ]);

        return redirect()->route('admin.bills.show', $bill)->with('status', 'Bill cancelled.');
    }

    public function print(Bill $bill): View
    {
        $bill->load(['patient', 'appointment.doctor', 'appointment.department', 'issuedBy', 'billItems']);

        return view('admin.bills.print', [
            'bill' => $bill,
            'hospital' => HospitalSetting::map(),
        ]);
    }

    private function issueBill(Bill $bill): void
    {
        abort_unless($bill->canIssue(), 403);

        $bill->update([
            'status' => 'issued',
            'issued_at' => $bill->issued_at ?? now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function formData(array $data): array
    {
        return [
            ...$data,
            'patients' => Patient::query()->active()->orderBy('full_name')->get(['id', 'full_name', 'phone']),
            'appointments' => Appointment::query()
                ->with('doctor:id,name,consultation_fee')
                ->orderByDesc('appointment_date')
                ->orderByDesc('id')
                ->limit(200)
                ->get(['id', 'appointment_no', 'patient_id', 'doctor_id', 'appointment_date', 'start_time']),
            'services' => Service::query()->active()->orderBy('title')->get(['id', 'title']),
            'packages' => HealthPackage::query()->active()->orderBy('title')->get(['id', 'title', 'price']),
            'doctors' => Doctor::query()->active()->orderBy('name')->get(['id', 'name', 'consultation_fee', 'designation']),
        ];
    }
}
