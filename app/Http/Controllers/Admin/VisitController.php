<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CheckInVisitRequest;
use App\Http\Requests\Admin\RecordVisitClinicalRequest;
use App\Http\Requests\Admin\RecordVisitVitalsRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\Visit;
use App\Support\VisitDesk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        if (! array_key_exists($status, Visit::STATUSES)) {
            $status = '';
        }

        $visits = Visit::query()
            ->with(['patient', 'doctor', 'department', 'appointment'])
            ->when(
                $status !== '',
                fn ($query) => $query->where('status', $status),
                function ($query): void {
                    $query->where(function ($inner): void {
                        $inner->open()->orWhereDate('arrived_at', now()->toDateString());
                    });
                },
            )
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where(function ($builder) use ($term): void {
                    $builder->where('visit_no', 'like', '%'.$term.'%')
                        ->orWhereHas('patient', fn ($patient) => $patient->where('full_name', 'like', '%'.$term.'%'));
                });
            })
            ->orderByRaw("case status when 'waiting' then 1 when 'triage' then 2 when 'consultation' then 3 when 'completed' then 4 else 5 end")
            ->orderBy('arrived_at')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.visits.index', [
            'visits' => $visits,
            'counts' => [
                'waiting' => Visit::query()->where('status', 'waiting')->count(),
                'triage' => Visit::query()->where('status', 'triage')->count(),
                'consultation' => Visit::query()->where('status', 'consultation')->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('visits.checkin') ?? false, 403);

        return view('admin.visits.create', [
            'patients' => Patient::query()->active()->orderBy('full_name')->orderBy('id')->get(),
            'doctors' => Doctor::query()->active()->whereNotNull('department_id')->with('department')->orderBy('name')->orderBy('id')->get(),
            'selectedPatientId' => $request->integer('patient_id') ?: null,
        ]);
    }

    public function store(CheckInVisitRequest $request, VisitDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $visit = $desk->checkInWalkIn($request->validated(), $actor);

        return redirect()
            ->route('admin.visits.show', $visit)
            ->with('status', 'Patient checked in.');
    }

    public function checkIn(Request $request, Appointment $appointment, VisitDesk $desk): RedirectResponse
    {
        abort_unless($request->user()?->can('visits.checkin') ?? false, 403);

        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $visit = $desk->checkInAppointment($appointment, $actor);

        return redirect()
            ->route('admin.visits.show', $visit)
            ->with('status', 'Patient checked in and is waiting.');
    }

    public function show(Visit $visit): View
    {
        $visit->load([
            'patient',
            'doctor.department',
            'department',
            'appointment',
            'checkedInBy',
            'vitalsRecordedBy',
            'clinicallyRecordedBy',
            'events.actor',
            'labOrders.test',
            'labOrders.result',
        ]);

        return view('admin.visits.show', [
            'visit' => $visit,
            'labTests' => LabTest::query()->active()->orderBy('name')->orderBy('id')->get(),
        ]);
    }

    public function vitals(RecordVisitVitalsRequest $request, Visit $visit, VisitDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->recordVitals($visit, $request->validated(), $actor);

        return back()->with('status', 'Vitals recorded. Patient is ready for the doctor.');
    }

    public function clinical(RecordVisitClinicalRequest $request, Visit $visit, VisitDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->recordClinical($visit, $request->validated(), $actor);

        return back()->with('status', $request->boolean('complete')
            ? 'Encounter completed.'
            : 'Consultation notes saved.');
    }
}
