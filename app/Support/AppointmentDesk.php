<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppointmentDesk
{
    /**
     * @var list<string>
     */
    public const FALLBACK_SLOTS = ['08:00', '09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00'];

    /**
     * @param  array{patient_id: int, doctor_id: int, appointment_date: string, start_time: string, reason?: string|null, notes?: string|null, status: string, source: string}  $attributes
     */
    public function book(array $attributes, ?User $actor = null): Appointment
    {
        return DB::transaction(function () use ($attributes, $actor): Appointment {
            $doctor = $this->lockDoctor((int) $attributes['doctor_id']);
            $start = $this->normalizeTime($attributes['start_time']);
            $date = $attributes['appointment_date'];
            $source = $attributes['source'];
            $status = $attributes['status'];

            $this->assertSlotOpen($doctor, $date, $start, $source);

            return $this->insertAppointment($attributes, $doctor, $date, $start, $status, $source, $actor);
        });
    }

    /**
     * @param  array{name: string, phone: string, service: string, date: string, doctor?: int|string|null, time?: string|null}  $payload
     */
    public function requestFromWebsite(array $payload, Service $service): Appointment
    {
        return DB::transaction(function () use ($payload, $service): Appointment {
            $patient = Patient::query()->firstOrCreate(
                ['phone' => $payload['phone']],
                [
                    'full_name' => $payload['name'],
                    'is_active' => true,
                ],
            );

            if ($patient->full_name !== $payload['name']) {
                $patient->update(['full_name' => $payload['name']]);
            }

            $date = $payload['date'];
            $requestedTime = filled($payload['time'] ?? null)
                ? $this->normalizeTime((string) $payload['time'])
                : null;
            $requestedDoctorId = filled($payload['doctor'] ?? null)
                ? (int) $payload['doctor']
                : null;

            $doctor = $requestedDoctorId !== null
                ? $this->lockDoctor($requestedDoctorId)
                : $this->lockFirstAvailableDoctor($service->department_id, $date, $requestedTime);

            if ($doctor === null || $doctor->department_id === null) {
                throw ValidationException::withMessages([
                    'date' => 'No doctor is available for that date and time. Try another slot.',
                ]);
            }

            $start = $requestedTime ?? $this->firstOpenSlot($doctor, $date);

            if ($start === null) {
                throw ValidationException::withMessages([
                    'date' => $this->dayUnavailableReason($doctor, $date)
                        ?? 'No open slots remain for this doctor on that day.',
                ]);
            }

            $this->assertSlotOpen($doctor, $date, $start, 'web', null, 'time');

            return $this->insertAppointment([
                'patient_id' => $patient->id,
                'reason' => $service->title,
                'notes' => 'Website request. Confirm the time with the patient.',
            ], $doctor, $date, $start, 'pending', 'web', null);
        });
    }

    /**
     * @param  array{status: string, notes?: string|null, cancellation_reason?: string|null, appointment_date?: string, start_time?: string, reason?: string|null}  $attributes
     */
    public function updateVisit(Appointment $appointment, array $attributes): Appointment
    {
        return DB::transaction(function () use ($appointment, $attributes): Appointment {
            $status = $attributes['status'];
            $date = $attributes['appointment_date'] ?? $appointment->appointment_date?->toDateString();
            $start = isset($attributes['start_time'])
                ? $this->normalizeTime($attributes['start_time'])
                : $this->normalizeTime((string) $appointment->start_time);

            if ($status !== 'cancelled' && $date !== null) {
                $doctor = $this->lockDoctor((int) $appointment->doctor_id);
                $this->assertSlotOpen(
                    $doctor,
                    $date,
                    $start,
                    (string) $appointment->source,
                    $appointment->id,
                    'start_time',
                );
            }

            $appointment->update([
                'appointment_date' => $date,
                'start_time' => $start,
                'end_time' => $this->endTime($start),
                'reason' => array_key_exists('reason', $attributes) ? $attributes['reason'] : $appointment->reason,
                'status' => $status,
                'notes' => array_key_exists('notes', $attributes) ? $attributes['notes'] : $appointment->notes,
                'cancelled_at' => $status === 'cancelled' ? ($appointment->cancelled_at ?? now()) : null,
                'cancellation_reason' => $status === 'cancelled' ? ($attributes['cancellation_reason'] ?? null) : null,
                'booking_key' => $this->bookingKey((int) $appointment->doctor_id, (string) $date, $start, $status, (string) $appointment->source),
            ]);

            return $appointment->refresh();
        });
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

    /**
     * @return array{available: bool, reason: string|null, slots: list<string>, clinic_days: list<string>}
     */
    public function availability(Doctor $doctor, string $date): array
    {
        $clinicDays = $this->clinicDays($doctor);
        $dayReason = $this->dayUnavailableReason($doctor, $date);

        if ($dayReason !== null) {
            return [
                'available' => false,
                'reason' => $dayReason,
                'slots' => [],
                'clinic_days' => $clinicDays,
            ];
        }

        $slots = $this->openSlots($doctor, $date);

        return [
            'available' => $slots !== [],
            'reason' => $slots === []
                ? 'No open slots remain for this doctor on that day.'
                : null,
            'slots' => $slots,
            'clinic_days' => $clinicDays,
        ];
    }

    public function unavailableReason(
        Doctor $doctor,
        string $date,
        string $startTime,
        string $source = 'web',
        ?int $ignoreId = null,
    ): ?string {
        if (! $doctor->is_active || $doctor->department_id === null) {
            return 'This doctor is not available for booking.';
        }

        $start = $this->normalizeTime($startTime);
        $slotAt = Carbon::parse($date.' '.$start);

        if ($slotAt->lt(now())) {
            return 'That time has already passed.';
        }

        $leave = $this->coveringLeave($doctor, $date);

        if ($leave !== null) {
            return $this->leaveMessage($doctor, $leave);
        }

        if ($source !== 'emergency' && $this->publishesClinicHours($doctor)) {
            if ($this->clinicSessions($doctor, $date) === []) {
                return $this->offDayMessage($doctor, $date);
            }

            if (! $this->timeFitsClinic($doctor, $date, $start)) {
                return 'That time is outside this doctor\'s clinic hours.';
            }
        }

        if ($this->slotTaken((int) $doctor->id, $date, $start, $ignoreId)) {
            return 'That time is already booked. Choose another slot.';
        }

        return null;
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

        return sprintf('%d:%s:%s', $doctorId, $date, substr($this->normalizeTime($start), 0, 5));
    }

    public function assertSlotOpen(
        Doctor $doctor,
        string $date,
        string $startTime,
        string $source,
        ?int $ignoreId = null,
        string $field = 'start_time',
    ): void {
        $reason = $this->unavailableReason($doctor, $date, $startTime, $source, $ignoreId);

        if ($reason !== null) {
            throw ValidationException::withMessages([
                $field => $reason,
            ]);
        }
    }

    /**
     * @param  array{patient_id: int, reason?: string|null, notes?: string|null}  $attributes
     */
    private function insertAppointment(
        array $attributes,
        Doctor $doctor,
        string $date,
        string $start,
        string $status,
        string $source,
        ?User $actor,
    ): Appointment {
        try {
            return Appointment::query()->create([
                'appointment_no' => Appointment::nextNumber($this->numberPrefix($source)),
                'patient_id' => $attributes['patient_id'],
                'doctor_id' => $doctor->id,
                'department_id' => $doctor->department_id,
                'appointment_date' => $date,
                'start_time' => $start,
                'end_time' => $this->endTime($start),
                'reason' => $attributes['reason'] ?? null,
                'status' => $status,
                'notes' => $attributes['notes'] ?? null,
                'source' => $source,
                'created_by' => $actor?->id,
                'confirmation_token' => (string) Str::uuid(),
                'booking_key' => $this->bookingKey((int) $doctor->id, $date, $start, $status, $source),
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            if (! str_contains($exception->getMessage(), 'booking_key')) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'start_time' => 'That time was just booked. Choose another slot.',
                'time' => 'That time was just booked. Choose another slot.',
            ]);
        }
    }

    private function lockDoctor(int $doctorId): Doctor
    {
        $doctor = Doctor::query()
            ->with(['doctorSchedules', 'doctorLeaves'])
            ->whereKey($doctorId)
            ->lockForUpdate()
            ->first();

        if ($doctor === null) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Choose an active doctor with a department.',
            ]);
        }

        return $doctor;
    }

    private function lockFirstAvailableDoctor(?int $departmentId, string $date, ?string $startTime): ?Doctor
    {
        $ids = $this->candidateDoctorIds($departmentId);

        if ($ids === [] && $departmentId !== null) {
            $ids = $this->candidateDoctorIds(null);
        }

        foreach ($ids as $doctorId) {
            $doctor = $this->lockDoctor($doctorId);

            if ($startTime !== null) {
                if ($this->unavailableReason($doctor, $date, $startTime, 'web') === null) {
                    return $doctor;
                }

                continue;
            }

            if ($this->firstOpenSlot($doctor, $date) !== null) {
                return $doctor;
            }
        }

        return null;
    }

    /**
     * @return list<int>
     */
    private function candidateDoctorIds(?int $departmentId): array
    {
        return Doctor::query()
            ->active()
            ->whereNotNull('department_id')
            ->when($departmentId !== null, fn ($query) => $query->where('department_id', $departmentId))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->pluck('id')
            ->all();
    }

    private function firstOpenSlot(Doctor $doctor, string $date): ?string
    {
        $slots = $this->openSlots($doctor, $date);

        return $slots[0] ?? null;
    }

    /**
     * @return list<string>
     */
    private function openSlots(Doctor $doctor, string $date): array
    {
        if ($this->dayUnavailableReason($doctor, $date) !== null) {
            return [];
        }

        $open = [];

        foreach ($this->candidateSlots($doctor, $date) as $slot) {
            if ($this->unavailableReason($doctor, $date, $slot, 'web') === null) {
                $open[] = substr($this->normalizeTime($slot), 0, 5);
            }
        }

        return $open;
    }

    /**
     * @return list<string>
     */
    private function candidateSlots(Doctor $doctor, string $date): array
    {
        $sessions = $this->clinicSessions($doctor, $date);

        if ($sessions === []) {
            return self::FALLBACK_SLOTS;
        }

        $slots = [];

        foreach ($sessions as $session) {
            $duration = max(15, (int) $session->slot_duration_minutes);
            $cursor = Carbon::parse($date.' '.$this->normalizeTime((string) $session->start_time));
            $sessionEnd = Carbon::parse($date.' '.$this->normalizeTime((string) $session->end_time));

            while ($cursor->copy()->addMinutes(30)->lte($sessionEnd)) {
                $slots[] = $cursor->format('H:i');
                $cursor->addMinutes($duration);
            }
        }

        return array_values(array_unique($slots));
    }

    private function dayUnavailableReason(Doctor $doctor, string $date): ?string
    {
        if (! $doctor->is_active || $doctor->department_id === null) {
            return 'This doctor is not available for booking.';
        }

        $leave = $this->coveringLeave($doctor, $date);

        if ($leave !== null) {
            return $this->leaveMessage($doctor, $leave);
        }

        if ($this->publishesClinicHours($doctor) && $this->clinicSessions($doctor, $date) === []) {
            return $this->offDayMessage($doctor, $date);
        }

        return null;
    }

    /**
     * @return list<DoctorSchedule>
     */
    private function clinicSessions(Doctor $doctor, string $date): array
    {
        $weekday = $this->weekdayFor($date);

        return $this->activeSchedules($doctor)
            ->filter(fn (DoctorSchedule $schedule): bool => $schedule->weekday === $weekday)
            ->sortBy('start_time')
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function clinicDays(Doctor $doctor): array
    {
        return $this->activeSchedules($doctor)
            ->pluck('weekday')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, DoctorSchedule>
     */
    private function activeSchedules(Doctor $doctor): Collection
    {
        if ($doctor->relationLoaded('doctorSchedules')) {
            return $doctor->doctorSchedules
                ->filter(fn (DoctorSchedule $schedule): bool => $schedule->is_active)
                ->values();
        }

        return $doctor->doctorSchedules()->active()->get();
    }

    private function publishesClinicHours(Doctor $doctor): bool
    {
        return $this->activeSchedules($doctor)->isNotEmpty();
    }

    private function coveringLeave(Doctor $doctor, string $date): ?DoctorLeave
    {
        $day = Carbon::parse($date)->toDateString();

        if ($doctor->relationLoaded('doctorLeaves')) {
            return $doctor->doctorLeaves->first(function (DoctorLeave $leave) use ($day): bool {
                if (! $leave->is_active || $leave->starts_on === null || $leave->ends_on === null) {
                    return false;
                }

                return $leave->starts_on->toDateString() <= $day
                    && $leave->ends_on->toDateString() >= $day;
            });
        }

        return $doctor->doctorLeaves()->active()->covering($day)->first();
    }

    private function timeFitsClinic(Doctor $doctor, string $date, string $start): bool
    {
        $end = Carbon::parse($date.' '.$this->endTime($start));
        $slotStart = Carbon::parse($date.' '.$start);

        foreach ($this->clinicSessions($doctor, $date) as $session) {
            $sessionStart = Carbon::parse($date.' '.$this->normalizeTime((string) $session->start_time));
            $sessionEnd = Carbon::parse($date.' '.$this->normalizeTime((string) $session->end_time));

            if ($slotStart->lt($sessionStart) || $end->gt($sessionEnd)) {
                continue;
            }

            $duration = max(15, (int) $session->slot_duration_minutes);
            $elapsed = (int) $sessionStart->diffInMinutes($slotStart, false);

            if ($elapsed < 0 || $elapsed % $duration !== 0) {
                continue;
            }

            if ($session->break_start && $session->break_end) {
                $breakStart = Carbon::parse($date.' '.$this->normalizeTime((string) $session->break_start));
                $breakEnd = Carbon::parse($date.' '.$this->normalizeTime((string) $session->break_end));

                if ($slotStart->lt($breakEnd) && $end->gt($breakStart)) {
                    continue;
                }
            }

            return true;
        }

        return false;
    }

    private function weekdayFor(string $date): string
    {
        return strtolower(Carbon::parse($date)->format('l'));
    }

    private function leaveMessage(Doctor $doctor, DoctorLeave $leave): string
    {
        return sprintf(
            '%s is %s from %s to %s.',
            $doctor->name,
            $leave->absencePhrase(),
            $leave->starts_on?->format('d M Y'),
            $leave->ends_on?->format('d M Y'),
        );
    }

    private function offDayMessage(Doctor $doctor, string $date): string
    {
        $weekday = Carbon::parse($date)->format('l');
        $days = collect($this->clinicDays($doctor))
            ->map(fn (string $day): string => ucfirst($day).'s')
            ->implode(', ');

        if ($days === '') {
            return $doctor->name.' is not in clinic on '.$weekday.'s.';
        }

        return $doctor->name.' is not in clinic on '.$weekday.'s. Clinic days: '.$days.'.';
    }

    private function numberPrefix(string $source): string
    {
        return match ($source) {
            'web' => 'WEB',
            'emergency' => 'ER',
            default => 'APT',
        };
    }
}
