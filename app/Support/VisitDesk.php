<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VisitDesk
{
    public function __construct(private BillingDesk $billing) {}

    public function checkInAppointment(Appointment $appointment, User $actor): Visit
    {
        $this->assertAppointmentCanCheckIn($appointment);

        return DB::transaction(function () use ($appointment, $actor): Visit {
            $this->assertPatientHasNoOpenVisit((int) $appointment->patient_id);

            if ($appointment->status === 'pending') {
                $appointment->update([
                    'status' => 'confirmed',
                    'cancelled_at' => null,
                    'cancellation_reason' => null,
                ]);
            }

            $visit = Visit::query()->create([
                'visit_no' => Visit::nextNumber(),
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'department_id' => $appointment->department_id,
                'checked_in_by' => $actor->id,
                'status' => 'waiting',
                'chief_complaint' => $appointment->reason,
                'arrived_at' => now(),
            ]);

            $this->recordEvent($visit, $actor, VisitEvent::TYPE_CHECKED_IN, null, 'waiting', 'Patient arrived and was checked in.');

            return $visit;
        });
    }

    /**
     * @param  array{patient_id: int, doctor_id?: int|null, chief_complaint?: string|null}  $attributes
     */
    public function checkInWalkIn(array $attributes, User $actor): Visit
    {
        $doctor = isset($attributes['doctor_id'])
            ? Doctor::query()->find($attributes['doctor_id'])
            : null;

        return DB::transaction(function () use ($attributes, $actor, $doctor): Visit {
            $this->assertPatientHasNoOpenVisit((int) $attributes['patient_id']);

            $visit = Visit::query()->create([
                'visit_no' => Visit::nextNumber(),
                'patient_id' => $attributes['patient_id'],
                'appointment_id' => null,
                'doctor_id' => $doctor?->id,
                'department_id' => $doctor?->department_id,
                'checked_in_by' => $actor->id,
                'status' => 'waiting',
                'chief_complaint' => $attributes['chief_complaint'] ?? null,
                'arrived_at' => now(),
            ]);

            $this->recordEvent($visit, $actor, VisitEvent::TYPE_CHECKED_IN, null, 'waiting', 'Walk-in patient checked in.');

            return $visit;
        });
    }

    /**
     * @param  array{blood_pressure: string, temperature: float|int|string, pulse: int, oxygen_saturation: int, weight: float|int|string, pain_score: int}  $vitals
     */
    public function recordVitals(Visit $visit, array $vitals, User $actor): Visit
    {
        if (! $visit->isOpen()) {
            throw ValidationException::withMessages([
                'status' => 'This encounter is already closed.',
            ]);
        }

        if (! in_array($visit->status, ['waiting', 'triage', 'consultation'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Vitals can be recorded while the encounter is still open.',
            ]);
        }

        return DB::transaction(function () use ($visit, $vitals, $actor): Visit {
            $from = $visit->status;
            $next = $from === 'consultation' ? 'consultation' : 'triage';

            $visit->update([
                'blood_pressure' => $vitals['blood_pressure'],
                'temperature' => $vitals['temperature'],
                'pulse' => $vitals['pulse'],
                'oxygen_saturation' => $vitals['oxygen_saturation'],
                'weight' => $vitals['weight'],
                'pain_score' => $vitals['pain_score'],
                'vitals_recorded_at' => now(),
                'vitals_recorded_by' => $actor->id,
                'status' => $next,
                'triage_started_at' => $visit->triage_started_at ?? now(),
            ]);

            $this->recordEvent(
                $visit,
                $actor,
                VisitEvent::TYPE_VITALS_RECORDED,
                $from,
                $next,
                'Nurse recorded vital signs.',
                [
                    'blood_pressure' => $vitals['blood_pressure'],
                    'temperature' => $vitals['temperature'],
                    'pulse' => $vitals['pulse'],
                    'oxygen_saturation' => $vitals['oxygen_saturation'],
                    'weight' => $vitals['weight'],
                    'pain_score' => $vitals['pain_score'],
                ],
            );

            return $visit->refresh();
        });
    }

    /**
     * @param  array{symptoms?: string|null, examination_notes?: string|null, diagnosis?: string|null, treatment_plan?: string|null, prescriptions?: string|null, follow_up_instructions?: string|null, complete?: bool}  $notes
     */
    public function recordClinical(Visit $visit, array $notes, User $actor): Visit
    {
        if (! $visit->isOpen()) {
            throw ValidationException::withMessages([
                'status' => 'This encounter is already closed.',
            ]);
        }

        $complete = (bool) ($notes['complete'] ?? false);

        if ($complete && ! $visit->hasVitals()) {
            throw ValidationException::withMessages([
                'diagnosis' => 'A nurse must record vitals before this encounter can be completed.',
            ]);
        }

        if ($complete && blank($notes['diagnosis'] ?? null)) {
            throw ValidationException::withMessages([
                'diagnosis' => 'Add a diagnosis before completing the encounter.',
            ]);
        }

        if ($complete && blank($notes['treatment_plan'] ?? null)) {
            throw ValidationException::withMessages([
                'treatment_plan' => 'Add a treatment plan before completing the encounter.',
            ]);
        }

        return DB::transaction(function () use ($visit, $notes, $actor, $complete): Visit {
            $from = $visit->status;
            $startedConsultation = $visit->consultation_started_at === null;
            $actorDoctorId = $actor->doctor?->id;

            if (
                filled($visit->prescriptions)
                && $visit->clinically_recorded_by !== null
                && (int) $visit->clinically_recorded_by !== (int) $actor->id
                && array_key_exists('prescriptions', $notes)
                && (string) ($notes['prescriptions'] ?? '') !== (string) $visit->prescriptions
            ) {
                throw ValidationException::withMessages([
                    'prescriptions' => 'You cannot modify another doctor\'s prescription.',
                ]);
            }

            $visit->update([
                'doctor_id' => $visit->doctor_id ?? $actorDoctorId,
                'symptoms' => $notes['symptoms'] ?? $visit->symptoms,
                'examination_notes' => $notes['examination_notes'] ?? $visit->examination_notes,
                'diagnosis' => $notes['diagnosis'] ?? $visit->diagnosis,
                'treatment_plan' => $notes['treatment_plan'] ?? $visit->treatment_plan,
                'prescriptions' => $notes['prescriptions'] ?? $visit->prescriptions,
                'follow_up_instructions' => $notes['follow_up_instructions'] ?? $visit->follow_up_instructions,
                'clinically_recorded_at' => now(),
                'clinically_recorded_by' => $actor->id,
                'status' => $complete ? 'completed' : 'consultation',
                'consultation_started_at' => $visit->consultation_started_at ?? now(),
                'completed_at' => $complete ? now() : null,
            ]);

            if ($startedConsultation) {
                $this->recordEvent($visit, $actor, VisitEvent::TYPE_CONSULTATION_STARTED, $from, 'consultation', 'Doctor started the consultation.');
            }

            if ($complete) {
                $this->recordEvent($visit, $actor, VisitEvent::TYPE_COMPLETED, $startedConsultation ? 'consultation' : $from, 'completed', 'Doctor completed the encounter.');

                if ($visit->appointment && $visit->appointment->status !== 'cancelled') {
                    $visit->appointment->update([
                        'status' => 'completed',
                        'cancelled_at' => null,
                        'cancellation_reason' => null,
                    ]);

                    $this->billing->ensureAppointmentConsultationBill($visit->appointment->fresh(), $actor);
                }
            }

            return $visit->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function recordEvent(
        Visit $visit,
        User $actor,
        string $type,
        ?string $from,
        ?string $to,
        string $notes,
        array $meta = [],
    ): void {
        VisitEvent::query()->create([
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient_id,
            'actor_user_id' => $actor->id,
            'event_type' => $type,
            'from_status' => $from,
            'to_status' => $to,
            'notes' => $notes,
            'meta' => $meta === [] ? null : $meta,
            'created_at' => now(),
        ]);
    }

    private function assertAppointmentCanCheckIn(Appointment $appointment): void
    {
        if (in_array($appointment->status, ['cancelled', 'no_show', 'completed'], true)) {
            throw ValidationException::withMessages([
                'appointment' => 'This appointment cannot be checked in.',
            ]);
        }

        $existing = Visit::query()
            ->where('appointment_id', $appointment->id)
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'appointment' => 'This appointment already has an encounter.',
            ]);
        }
    }

    private function assertPatientHasNoOpenVisit(int $patientId): void
    {
        $open = Visit::query()
            ->where('patient_id', $patientId)
            ->open()
            ->lockForUpdate()
            ->first();

        if ($open) {
            throw ValidationException::withMessages([
                'patient_id' => 'This patient already has an open encounter ('.$open->visit_no.').',
            ]);
        }
    }
}
