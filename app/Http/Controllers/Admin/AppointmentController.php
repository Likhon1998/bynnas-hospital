<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppointmentStatusRequest;
use App\Http\Requests\Admin\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Support\AppointmentDesk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $appointments = Appointment::query()
            ->with(['patient', 'doctor', 'department'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($source !== '', fn ($query) => $query->where('source', $source))
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

        return view('admin.appointments.index', compact('appointments'));
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
}
