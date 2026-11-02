<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppointmentStatusRequest;
use App\Http\Requests\Admin\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Support\AppointmentDesk;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $source = $request->string('source')->toString();

        if (! array_key_exists($status, Appointment::STATUSES)) {
            $status = '';
        }

        if (! array_key_exists($source, Appointment::SOURCES)) {
            $source = '';
        }

        $doctorId = $request->integer('doctor');
        $selectedDoctor = $doctorId > 0
            ? Doctor::query()->select(['id', 'name'])->find($doctorId)
            : null;
        $selectedDate = $this->selectedVisitDate($request);

        $appointments = Appointment::query()
            ->with(['patient', 'doctor', 'department'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($source !== '', fn ($query) => $query->where('source', $source))
            ->when($selectedDoctor !== null, fn ($query) => $query->where('doctor_id', $selectedDoctor->id))
            ->when($selectedDate !== null, fn ($query) => $query->whereDate('appointment_date', $selectedDate->toDateString()))
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where(function ($builder) use ($term): void {
                    $builder->where('appointment_no', 'like', '%'.$term.'%')
                        ->orWhereHas('patient', fn ($patient) => $patient->where('full_name', 'like', '%'.$term.'%'))
                        ->orWhereHas('doctor', fn ($doctor) => $doctor->where('name', 'like', '%'.$term.'%'));
                });
            })
            ->orderByDesc('appointment_date')
            ->orderByDesc('start_time')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'doctors' => Doctor::query()->orderBy('name')->orderBy('id')->get(['id', 'name']),
            'selectedDoctor' => $selectedDoctor,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('appointments.manage') ?? false, 403);

        return view('admin.appointments.create', [
            'patients' => Patient::query()->active()->orderBy('full_name')->orderBy('id')->get(),
            'doctors' => Doctor::query()->active()->whereNotNull('department_id')->with('department')->orderBy('name')->orderBy('id')->get(),
            'selectedPatientId' => $request->integer('patient_id') ?: null,
        ]);
    }

    public function store(StoreAppointmentRequest $request, AppointmentDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $appointment = $desk->book($request->validated(), $actor);

        return redirect()
            ->route('admin.appointments.show', $appointment)
            ->with('status', 'Visit booked.');
    }

    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient', 'doctor.department', 'department', 'createdBy', 'visits']);

        return view('admin.appointments.show', compact('appointment'));
    }

    public function update(AppointmentStatusRequest $request, Appointment $appointment, AppointmentDesk $desk): RedirectResponse
    {
        $desk->updateVisit($appointment, $request->validated());

        return back()->with('status', 'Appointment updated.');
    }

    private function selectedVisitDate(Request $request): ?Carbon
    {
        $raw = $request->string('date')->toString();

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $raw);
        } catch (InvalidFormatException) {
            return null;
        }

        if ($date->format('Y-m-d') !== $raw) {
            return null;
        }

        return $date->startOfDay();
    }
}
