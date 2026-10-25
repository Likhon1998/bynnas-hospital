<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentRequestController extends Controller
{
    public function store(StoreAppointmentRequest $request): RedirectResponse
    {
        $service = Service::query()
            ->active()
            ->where('slug', $request->validated('service'))
            ->first();

        if (! $service) {
            return back()
                ->withInput()
                ->withErrors(['service' => 'That service is not available right now.']);
        }

        $doctor = Doctor::query()
            ->active()
            ->when($service->department_id, fn ($query) => $query->where('department_id', $service->department_id))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->first()
            ?? Doctor::query()->active()->orderBy('sort_order')->orderBy('name')->first();

        if (! $doctor || ! $doctor->department_id) {
            return back()
                ->withInput()
                ->with('error', 'The desk cannot take website requests until a doctor is on the roster.');
        }

        $appointment = DB::transaction(function () use ($request, $service, $doctor): Appointment {
            $patient = Patient::query()->firstOrCreate(
                ['phone' => $request->validated('phone')],
                [
                    'full_name' => $request->validated('name'),
                    'is_active' => true,
                ],
            );

            if ($patient->full_name !== $request->validated('name')) {
                $patient->update(['full_name' => $request->validated('name')]);
            }

            return Appointment::query()->create([
                'appointment_no' => $this->nextAppointmentNumber(),
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'department_id' => $service->department_id ?: $doctor->department_id,
                'appointment_date' => $request->validated('date'),
                'start_time' => '09:00:00',
                'end_time' => '09:30:00',
                'reason' => $service->title,
                'status' => 'pending',
                'notes' => 'Website request. Confirm the time with the patient.',
                'source' => 'web',
                'confirmation_token' => (string) Str::uuid(),
                'booking_key' => 'web-'.Str::uuid(),
            ]);
        });

        return redirect()
            ->to(route('home').'#appointment')
            ->with('status', 'Request received. The desk will call '.$appointment->patient?->phone.' to confirm.');
    }

    private function nextAppointmentNumber(): string
    {
        do {
            $number = 'WEB-'.now()->format('ymd').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (Appointment::query()->where('appointment_no', $number)->exists());

        return $number;
    }
}
