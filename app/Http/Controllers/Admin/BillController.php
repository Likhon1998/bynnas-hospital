<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BillRequest;
use App\Http\Requests\Admin\CollectBillPaymentRequest;
use App\Http\Requests\Admin\RequestBillRefundRequest;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Doctor;
use App\Models\HealthPackage;
use App\Models\HospitalSetting;
use App\Models\Patient;
use App\Models\Service;
use App\Support\FinanceGuard;
use App\Support\SystemAuditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function store(BillRequest $request, FinanceGuard $financeGuard, SystemAuditor $auditor): RedirectResponse
    {
        $discount = (float) $request->input('discount', 0);
        $subtotal = $this->estimateSubtotal($request->validated('items'));

        if (! $financeGuard->canApplyDiscount($request->user(), $discount, $subtotal)) {
            $financeGuard->requestDiscountApproval(
                $request->user(),
                $discount,
                'High-value discount requires Director approval.',
                null,
                'Bill create blocked until approved.'
            );
            $auditor->record('finance.request', 'Requested high-value discount approval.', null, [
                'amount' => $discount,
                'subtotal' => $subtotal,
            ], $request->user());

            return back()
                ->withInput()
                ->withErrors([
                    'discount' => 'High-value discounts need Director approval. A request was submitted under Finance approvals.',
                ]);
        }

        $bill = Bill::createFromReception([
            'patient_id' => $request->integer('patient_id'),
            'appointment_id' => $request->integer('appointment_id') ?: null,
            'issued_by' => $request->user()->id,
            'bill_date' => $request->date('bill_date')?->toDateString() ?? now()->toDateString(),
            'status' => 'draft',
            'discount' => $discount,
            'notes' => $request->validated('notes'),
        ], $request->validated('items'));

        if ($request->input('action') === 'issue') {
            $this->issueBill($bill);
        }

        return redirect()->route('admin.bills.show', $bill)->with('status', 'Bill saved.');
    }

    public function show(Bill $bill): View
    {
        $bill->load([
            'patient',
            'appointment.doctor',
            'appointment.department',
            'issuedBy',
            'billItems',
            'payments.collectedBy',
        ]);

        return view('admin.bills.show', [
            'bill' => $bill,
            'paymentMethods' => BillPayment::METHODS,
        ]);
    }

    public function collections(Request $request): View
    {
        $date = $request->date('date')?->toDateString() ?? today()->toDateString();

        $payments = BillPayment::query()
            ->with(['bill.patient', 'collectedBy'])
            ->whereDate('collected_at', $date)
            ->orderByDesc('collected_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $totalsByMethod = BillPayment::query()
            ->whereDate('collected_at', $date)
            ->selectRaw('method, sum(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        return view('admin.bills.collections', [
            'payments' => $payments,
            'date' => $date,
            'totalsByMethod' => $totalsByMethod,
            'grandTotal' => (float) $totalsByMethod->sum(),
            'paymentMethods' => BillPayment::METHODS,
        ]);
    }

    public function edit(Bill $bill): View
    {
        abort_unless($bill->isEditable(), 403);

        $bill->load('billItems');

        return view('admin.bills.edit', $this->formData(compact('bill')));
    }

    public function update(BillRequest $request, Bill $bill, FinanceGuard $financeGuard, SystemAuditor $auditor): RedirectResponse
    {
        abort_unless($bill->isEditable(), 403);

        $discount = (float) $request->input('discount', 0);
        $subtotal = $this->estimateSubtotal($request->validated('items'));

        if (! $financeGuard->canApplyDiscount($request->user(), $discount, $subtotal)) {
            $financeGuard->requestDiscountApproval(
                $request->user(),
                $discount,
                'High-value discount requires Director approval.',
                $bill,
                'Bill '.$bill->bill_no.' update blocked until approved.'
            );
            $auditor->record('finance.request', 'Requested high-value discount approval.', $bill, [
                'amount' => $discount,
                'subtotal' => $subtotal,
            ], $request->user());

            return back()
                ->withInput()
                ->withErrors([
                    'discount' => 'High-value discounts need Director approval. A request was submitted under Finance approvals.',
                ]);
        }

        $bill->update([
            'patient_id' => $request->integer('patient_id'),
            'appointment_id' => $request->integer('appointment_id') ?: null,
            'bill_date' => $request->date('bill_date')?->toDateString() ?? $bill->bill_date?->toDateString(),
            'discount' => $discount,
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

    public function pay(CollectBillPaymentRequest $request, Bill $bill): RedirectResponse
    {
        abort_unless($bill->canCollectPayment(), 403);

        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $amount = round((float) $request->validated('amount'), 2);

        DB::transaction(function () use ($bill, $request, $actor, $amount): void {
            BillPayment::query()->create([
                'bill_id' => $bill->id,
                'amount' => $amount,
                'method' => $request->validated('method'),
                'reference' => $request->validated('reference'),
                'notes' => $request->validated('notes'),
                'collected_by' => $actor->id,
                'collected_at' => now(),
            ]);

            $paidAmount = round((float) $bill->paid_amount + $amount, 2);
            $fullyPaid = $paidAmount >= (float) $bill->total;

            $bill->update([
                'paid_amount' => min($paidAmount, (float) $bill->total),
                'paid_at' => $fullyPaid ? now() : $bill->paid_at,
                'status' => $fullyPaid ? 'paid' : 'issued',
            ]);

            if ($bill->hasLabCharges()) {
                $bill->ensureReportAccessCode();
            }
        });

        $bill->refresh();

        $status = $bill->status === 'paid'
            ? 'Payment collected. Bill is fully paid.'
            : 'Partial payment collected. Outstanding due: '.number_format($bill->balanceDue(), 2).'.';

        if (filled($bill->report_access_code)) {
            $status .= ' Lab report access code: '.$bill->report_access_code.'.';
        }

        return redirect()
            ->route('admin.bills.show', $bill)
            ->with('status', $status);
    }

    public function requestRefund(
        RequestBillRefundRequest $request,
        Bill $bill,
        FinanceGuard $financeGuard,
        SystemAuditor $auditor,
    ): RedirectResponse {
        abort_unless($bill->canRequestRefund(), 403);

        $actor = $request->user();
        abort_unless($actor !== null, 403);

        // Cashiers may request refunds but cannot approve them.
        abort_if($actor->can('finance.approve') === false && $request->boolean('approve'), 403);

        $approval = $financeGuard->requestRefundApproval(
            $actor,
            (float) $request->validated('amount'),
            $request->validated('reason'),
            $bill,
            $request->validated('notes'),
        );

        $auditor->record('finance.request', 'Requested refund approval for '.$bill->bill_no.'.', $approval, [
            'amount' => $approval->amount,
            'bill_id' => $bill->id,
        ], $actor);

        return back()->with('status', 'Refund request submitted for Director approval.');
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
        $bill->load([
            'patient',
            'appointment.doctor',
            'appointment.department',
            'issuedBy',
            'billItems',
            'payments.collectedBy',
        ]);

        if ($bill->paid_amount > 0 && $bill->hasLabCharges()) {
            $bill->ensureReportAccessCode();
            $bill->refresh();
        }

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

    /**
     * @param  list<array{quantity?: int, unit_price?: float|int|string}>  $items
     */
    private function estimateSubtotal(array $items): float
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $subtotal += $quantity * $unitPrice;
        }

        return round($subtotal, 2);
    }
}
