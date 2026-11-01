<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentDesk
{
    /**
     * @param  array{patient_id: int, doctor_id: int, appointment_date: string, start_time: string, reason?: string|null, notes?: string|null, status: string, source: string}  $attributes
     */
    public function book(array $attributes, User $actor): Appointment
    {
        return DB::transaction(function () use ($attributes, $actor): Appointment {
            $doctor = Doctor::query()->findOrFail($attributes['doctor_id']);
            $start = $this->normalizeTime($attributes['start_time']);
            $end = $this->endTime($start);
            $date = $attributes['appointment_date'];
            $source = $attributes['source'];
            $status = $attributes['status'];

            return Appointment::query()->create([
                'appointment_no' => Appointment::nextNumber($source === 'emergency' ? 'ER' : 'APT'),
                'patient_id' => $attributes['patient_id'],
                'doctor_id' => $doctor->id,
                'department_id' => $doctor->department_id,
                'appointment_date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'reason' => $attributes['reason'] ?? null,
                'status' => $status,
                'notes' => $attributes['notes'] ?? null,
                'source' => $source,
                'created_by' => $actor->id,
                'confirmation_token' => (string) Str::uuid(),
                'booking_key' => $this->bookingKey($doctor->id, $date, $start, $status, $source),
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ]);
        });
    }

    /**
     * @param  array{status: string, notes?: string|null, cancellation_reason?: string|null, appointment_date?: string, start_time?: string, reason?: string|null}  $attributes
     */
    public function updateVisit(Appointment $appointment, array $attributes): Appointment
    {
        $status = $attributes['status'];
        $date = $attributes['appointment_date'] ?? $appointment->appointment_date?->toDateString();
        $start = isset($attributes['start_time'])
            ? $this->normalizeTime($attributes['start_time'])
            : $this->normalizeTime((string) $appointment->start_time);
        $end = $this->endTime($start);

        $appointment->update([
            'appointment_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'reason' => array_key_exists('reason', $attributes) ? $attributes['reason'] : $appointment->reason,
            'status' => $status,
            'notes' => array_key_exists('notes', $attributes) ? $attributes['notes'] : $appointment->notes,
            'cancelled_at' => $status === 'cancelled' ? ($appointment->cancelled_at ?? now()) : null,
            'cancellation_reason' => $status === 'cancelled' ? ($attributes['cancellation_reason'] ?? null) : null,
            'booking_key' => $this->bookingKey((int) $appointment->doctor_id, (string) $date, $start, $status, (string) $appointment->source),
        ]);

        return $appointment->refresh();
    }

    public function slotTaken(int $doctorId, string $date, string $startTime, ?int $ignoreId = null): bool
    {
        $start = $this->normalizeTime($startTime);
        $end = $this->endTime($start);

        return Appointment::query()
            ->holdingSlot()
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();
    }

    public function normalizeTime(string $time): string
    {
        return Carbon::parse($time)->format('H:i:s');
    }

    public function endTime(string $start): string
    {
        return Carbon::parse($start)->addMinutes(30)->format('H:i:s');
    }

    public function bookingKey(int $doctorId, string $date, string $start, string $status, string $source): ?string
    {
        if ($status === 'cancelled') {
            return null;
        }

        if ($status === 'pending' && $source === 'web') {
            return 'web-'.Str::uuid();
        }

        return sprintf('%d:%s:%s', $doctorId, $date, substr($this->normalizeTime($start), 0, 5));
    }
}
