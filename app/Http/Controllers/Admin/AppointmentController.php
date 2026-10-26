<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppointmentStatusRequest;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $appointments = Appointment::query()
            ->with(['patient', 'doctor', 'department'])
            ->when($request->string('status')->toString(), function ($query, string $status): void {
                $query->where('status', $status);
            })
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

    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient', 'doctor.department', 'department', 'createdBy']);

        return view('admin.appointments.show', compact('appointment'));
    }

    public function update(AppointmentStatusRequest $request, Appointment $appointment): RedirectResponse
    {
        $status = $request->validated('status');

        $appointment->update([
            'status' => $status,
            'notes' => $request->validated('notes'),
            'cancelled_at' => $status === 'cancelled' ? ($appointment->cancelled_at ?? now()) : null,
            'cancellation_reason' => $status === 'cancelled' ? $request->validated('cancellation_reason') : null,
            'booking_key' => $status === 'cancelled' ? null : $appointment->booking_key,
        ]);

        return redirect()->route('admin.appointments.show', $appointment)->with('status', 'Appointment updated.');
    }
}
