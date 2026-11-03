<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthorizeAdmissionDischargeRequest;
use App\Http\Requests\Admin\CompleteAdmissionDischargeRequest;
use App\Http\Requests\Admin\PostAdmissionDayChargesRequest;
use App\Http\Requests\Admin\StoreAdmissionRequest;
use App\Http\Requests\Admin\TransferAdmissionRequest;
use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;
use App\Support\AdmissionDesk;
use App\Support\BillingDesk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        if (! array_key_exists($status, Admission::STATUSES)) {
            $status = '';
        }

        $admissions = Admission::query()
            ->with(['patient', 'ward', 'bed', 'attendingDoctor'])
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status),
                fn ($query) => $query->open(),
            )
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where(function ($builder) use ($term): void {
                    $builder->where('admission_no', 'like', '%'.$term.'%')
                        ->orWhereHas('patient', fn ($patient) => $patient->where('full_name', 'like', '%'.$term.'%'));
                });
            })
            ->orderByRaw("case status when 'discharge_authorized' then 1 when 'admitted' then 2 when 'transferred' then 3 else 4 end")
            ->orderByDesc('admitted_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.admissions.index', [
            'admissions' => $admissions,
            'counts' => [
                'open' => Admission::query()->open()->count(),
                'authorized' => Admission::query()->where('status', 'discharge_authorized')->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('admissions.admit') ?? false, 403);

        $appointment = $request->filled('appointment_id')
            ? Appointment::query()->with(['patient', 'doctor'])->find($request->integer('appointment_id'))
            : null;
        $visit = $request->filled('visit_id')
            ? Visit::query()->with(['patient', 'doctor'])->find($request->integer('visit_id'))
            : null;
        $source = $request->string('source')->toString();

        if (! array_key_exists($source, Admission::SOURCES)) {
            $source = $appointment?->source === 'emergency' ? 'emergency' : ($appointment ? 'appointment' : ($visit ? 'emergency' : 'direct'));
        }

        return view('admin.admissions.create', [
            'source' => $source,
            'appointment' => $appointment,
            'visit' => $visit,
            'patients' => Patient::query()->active()->orderBy('full_name')->orderBy('id')->get(),
            'doctors' => Doctor::query()->active()->whereNotNull('department_id')->with('department')->orderBy('name')->orderBy('id')->get(),
            'beds' => Bed::query()->with('ward')->admittable()->orderBy('bed_code')->orderBy('id')->get(),
            'selectedPatientId' => $appointment?->patient_id ?? $visit?->patient_id ?? $request->integer('patient_id') ?: null,
            'selectedDoctorId' => $appointment?->doctor_id ?? $visit?->doctor_id,
            'reason' => $appointment?->reason ?? $visit?->chief_complaint,
        ]);
    }

    public function store(StoreAdmissionRequest $request, AdmissionDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $admission = $desk->admit($request->safe()->only([
            'patient_id',
            'bed_id',
            'source',
            'admission_reason',
            'appointment_id',
            'visit_id',
            'attending_doctor_id',
        ]), $actor);

        return redirect()
            ->route('admin.admissions.show', $admission)
            ->with('status', 'Patient admitted to '.$admission->bed?->bed_code.'.');
    }

    public function show(Admission $admission, BillingDesk $billing): View
    {
        $admission->load([
            'patient',
            'appointment',
            'visit',
            'attendingDoctor.department',
            'department',
            'ward',
            'bed',
            'admittedBy',
            'authorizedBy',
            'dischargedBy',
            'events.actor',
            'transfers.fromBed',
            'transfers.toBed',
            'bills.billItems',
        ]);

        $outstandingBills = Bill::query()
            ->unpaid()
            ->where(function ($query) use ($admission): void {
                $query->where('admission_id', $admission->id)
                    ->orWhere(function ($inner) use ($admission): void {
                        $inner->where('patient_id', $admission->patient_id)
                            ->whereNull('admission_id');
                    });
            })
            ->orderByDesc('id')
            ->get();

        $transferTargets = $admission->isOpen()
            ? Bed::query()->with('ward')->admittable($admission->patient_id)->orderBy('bed_code')->orderBy('id')->get()
            : collect();

        return view('admin.admissions.show', [
            'admission' => $admission,
            'outstandingBills' => $outstandingBills,
            'transferTargets' => $transferTargets,
            'dailyBedRate' => $billing->dailyBedRate($admission->ward),
        ]);
    }

    public function postDayCharges(
        PostAdmissionDayChargesRequest $request,
        Admission $admission,
        BillingDesk $billing,
    ): RedirectResponse {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $extraItems = collect($request->validated('items') ?? [])
            ->filter(fn (array $item): bool => filled($item['description'] ?? null))
            ->values()
            ->all();

        $bill = $billing->postAdmissionDayCharges(
            $admission,
            $request->string('service_date')->toString(),
            $extraItems,
            $actor,
            $request->boolean('include_bed_charge', true),
        );

        return redirect()
            ->route('admin.bills.show', $bill)
            ->with('status', 'Day charges saved as draft bill '.$bill->bill_no.'. Issue and collect payment when ready.');
    }

    public function transfer(TransferAdmissionRequest $request, Admission $admission, AdmissionDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $toBed = Bed::query()->findOrFail($request->integer('to_bed_id'));
        $desk->transfer($admission, $toBed, $request->validated('reason'), $actor);

        return back()->with('status', 'Patient transferred to '.$toBed->bed_code.'.');
    }

    public function authorizeDischarge(AuthorizeAdmissionDischargeRequest $request, Admission $admission, AdmissionDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->authorizeDischarge($admission, $request->validated(), $actor);

        return back()->with('status', 'Discharge authorized. The bed can be released when ready.');
    }

    public function discharge(CompleteAdmissionDischargeRequest $request, Admission $admission, AdmissionDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->completeDischarge($admission, $actor);

        return back()->with('status', 'Patient discharged. The bed is dirty until cleaning and inspection are completed.');
    }
}
