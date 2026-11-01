<?php

namespace App\Support;

use App\Models\Admission;
use App\Models\AdmissionEvent;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\BedTransfer;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdmissionDesk
{
    public function __construct(
        private BedLifecycle $lifecycle,
        private BedOperations $operations,
    ) {}

    /**
     * @param  array{patient_id: int, bed_id: int, source: string, admission_reason: string, appointment_id?: int|null, visit_id?: int|null, attending_doctor_id?: int|null}  $attributes
     */
    public function admit(array $attributes, User $actor): Admission
    {
        $patient = Patient::query()->find($attributes['patient_id']);
        $bed = Bed::query()->with('ward')->find($attributes['bed_id']);
        $doctor = isset($attributes['attending_doctor_id'])
            ? Doctor::query()->find($attributes['attending_doctor_id'])
            : null;

        if ($patient === null || ! $patient->is_active) {
            throw ValidationException::withMessages([
                'patient_id' => 'Choose an active patient.',
            ]);
        }

        if ($bed === null) {
            throw ValidationException::withMessages([
                'bed_id' => 'Choose a bed.',
            ]);
        }

        return DB::transaction(function () use ($attributes, $actor, $patient, $bed, $doctor): Admission {
            $this->assertNoOpenAdmission((int) $patient->id);

            $appointment = isset($attributes['appointment_id'])
                ? Appointment::query()->find($attributes['appointment_id'])
                : null;
            $visit = isset($attributes['visit_id'])
                ? Visit::query()->find($attributes['visit_id'])
                : null;

            $admission = Admission::query()->create([
                'admission_no' => Admission::nextNumber(),
                'patient_id' => $patient->id,
                'source' => $attributes['source'],
                'appointment_id' => $appointment?->id,
                'visit_id' => $visit?->id,
                'attending_doctor_id' => $doctor?->id,
                'department_id' => $doctor?->department_id ?? $appointment?->department_id ?? $visit?->department_id,
                'ward_id' => $bed->ward_id,
                'bed_id' => $bed->id,
                'admission_reason' => $attributes['admission_reason'],
                'status' => 'admitted',
                'admitted_by' => $actor->id,
                'admitted_at' => now(),
            ]);

            $this->lifecycle->admit(
                $bed,
                (int) $patient->id,
                $actor,
                $attributes['admission_reason'],
                (int) $bed->lock_version,
            );

            $this->recordEvent(
                $admission,
                $actor,
                AdmissionEvent::TYPE_ADMITTED,
                null,
                'admitted',
                'Admitted to '.$bed->bed_code.'.',
                [
                    'source' => $admission->source,
                    'bed_id' => $bed->id,
                    'ward_id' => $bed->ward_id,
                ],
            );

            return $admission->refresh();
        });
    }

    public function ensureFromOccupiedBed(Bed $bed, User $actor, string $source = 'direct', ?string $reason = null): ?Admission
    {
        if ($bed->status !== 'occupied' || $bed->patient_id === null) {
            return null;
        }

        $open = Admission::query()
            ->open()
            ->where('patient_id', $bed->patient_id)
            ->first();

        if ($open) {
            $open->update([
                'bed_id' => $bed->id,
                'ward_id' => $bed->ward_id,
                'admission_reason' => $reason ?: $open->admission_reason ?: $bed->admission_reason,
            ]);

            return $open->refresh();
        }

        return DB::transaction(function () use ($bed, $actor, $source, $reason): Admission {
            $admission = Admission::query()->create([
                'admission_no' => Admission::nextNumber(),
                'patient_id' => $bed->patient_id,
                'source' => $source,
                'ward_id' => $bed->ward_id,
                'bed_id' => $bed->id,
                'admission_reason' => $reason ?: $bed->admission_reason ?: 'Direct admission',
                'status' => 'admitted',
                'admitted_by' => $actor->id,
                'admitted_at' => $bed->admitted_at ?? now(),
            ]);

            $this->recordEvent(
                $admission,
                $actor,
                AdmissionEvent::TYPE_ADMITTED,
                null,
                'admitted',
                'Admitted to '.$bed->bed_code.'.',
                ['source' => $source, 'bed_id' => $bed->id],
            );

            return $admission;
        });
    }

    public function transfer(Admission $admission, Bed $toBed, string $reason, User $actor): BedTransfer
    {
        if (! $admission->isOpen()) {
            throw ValidationException::withMessages([
                'status' => 'A discharged admission cannot be transferred.',
            ]);
        }

        $fromBed = $admission->bed;

        if ($fromBed === null || $fromBed->status !== 'occupied') {
            throw ValidationException::withMessages([
                'bed_id' => 'This admission is not on an occupied bed.',
            ]);
        }

        return DB::transaction(function () use ($admission, $fromBed, $toBed, $reason, $actor): BedTransfer {
            $transfer = $this->operations->transfer(
                fromBed: $fromBed,
                toBed: $toBed,
                actor: $actor,
                reason: $reason,
                fromLockVersion: (int) $fromBed->lock_version,
                toLockVersion: (int) $toBed->lock_version,
            );

            $freshTo = $toBed->fresh() ?? $toBed;
            $fromStatus = $admission->status;

            $admission->update([
                'bed_id' => $freshTo->id,
                'ward_id' => $freshTo->ward_id,
                'status' => $fromStatus === 'discharge_authorized' ? 'discharge_authorized' : 'transferred',
            ]);

            $transfer->update(['admission_id' => $admission->id]);

            $this->recordEvent(
                $admission,
                $actor,
                AdmissionEvent::TYPE_TRANSFERRED,
                $fromStatus,
                'transferred',
                'Transferred from '.$fromBed->bed_code.' to '.$freshTo->bed_code.'.',
                [
                    'from_bed_id' => $fromBed->id,
                    'to_bed_id' => $freshTo->id,
                    'reason' => $reason,
                ],
            );

            return $transfer->refresh();
        });
    }

    /**
     * @param  array{discharge_summary: string, discharge_medications?: string|null, follow_up_plan?: string|null, billing_reviewed?: bool}  $attributes
     */
    public function authorizeDischarge(Admission $admission, array $attributes, User $actor): Admission
    {
        if (! $admission->isOpen()) {
            throw ValidationException::withMessages([
                'status' => 'This admission is already closed.',
            ]);
        }

        return DB::transaction(function () use ($admission, $attributes, $actor): Admission {
            $outstanding = Bill::query()
                ->unpaid()
                ->where('patient_id', $admission->patient_id)
                ->orderBy('id')
                ->get(['id', 'bill_no', 'status', 'total']);

            if ($outstanding->isNotEmpty() && ! ($attributes['billing_reviewed'] ?? false)) {
                throw ValidationException::withMessages([
                    'billing_reviewed' => 'Review outstanding bills before authorizing discharge.',
                ]);
            }

            $from = $admission->status;

            $admission->update([
                'status' => 'discharge_authorized',
                'authorized_by' => $actor->id,
                'authorized_at' => now(),
                'discharge_summary' => $attributes['discharge_summary'],
                'discharge_medications' => $attributes['discharge_medications'] ?? null,
                'follow_up_plan' => $attributes['follow_up_plan'] ?? null,
                'outstanding_bills' => $outstanding
                    ->map(fn (Bill $bill): array => [
                        'id' => $bill->id,
                        'bill_no' => $bill->bill_no,
                        'status' => $bill->status,
                        'total' => (string) $bill->total,
                    ])
                    ->all(),
                'billing_reviewed' => $outstanding->isEmpty() || (bool) ($attributes['billing_reviewed'] ?? false),
            ]);

            $this->recordEvent(
                $admission,
                $actor,
                AdmissionEvent::TYPE_DISCHARGE_AUTHORIZED,
                $from,
                'discharge_authorized',
                'Clinical discharge authorized.',
                [
                    'outstanding_count' => $outstanding->count(),
                    'authorized_at' => now()->toIso8601String(),
                ],
            );

            return $admission->refresh();
        });
    }

    public function completeDischarge(Admission $admission, User $actor): Admission
    {
        if (! $admission->isAuthorizedForRelease()) {
            throw ValidationException::withMessages([
                'status' => 'A doctor must authorize the discharge summary before the bed can be released.',
            ]);
        }

        $bed = $admission->bed;

        if ($bed === null || $bed->status !== 'occupied') {
            throw ValidationException::withMessages([
                'bed_id' => 'This admission is not occupying a bed.',
            ]);
        }

        return DB::transaction(function () use ($admission, $bed, $actor): Admission {
            $this->lifecycle->discharge(
                $bed,
                $actor,
                'Clinical discharge '.$admission->admission_no,
                (int) $bed->lock_version,
            );

            $from = $admission->status;

            $admission->update([
                'status' => 'discharged',
                'discharged_by' => $actor->id,
                'discharged_at' => now(),
                'bed_id' => $bed->id,
            ]);

            $this->recordEvent(
                $admission,
                $actor,
                AdmissionEvent::TYPE_DISCHARGED,
                $from,
                'discharged',
                'Patient discharged. '.$bed->bed_code.' is dirty until cleaning is completed.',
                ['bed_id' => $bed->id, 'discharged_at' => now()->toIso8601String()],
            );

            return $admission->refresh();
        });
    }

    public function syncTransfer(BedTransfer $transfer): void
    {
        $admission = Admission::query()
            ->open()
            ->where('patient_id', $transfer->patient_id)
            ->first();

        if ($admission === null) {
            return;
        }

        $toBed = $transfer->toBed;

        $admission->update([
            'bed_id' => $transfer->to_bed_id,
            'ward_id' => $toBed?->ward_id ?? $admission->ward_id,
            'status' => $admission->status === 'discharge_authorized' ? 'discharge_authorized' : 'transferred',
        ]);

        $transfer->update(['admission_id' => $admission->id]);

        $this->recordEvent(
            $admission,
            $transfer->actor,
            AdmissionEvent::TYPE_TRANSFERRED,
            'admitted',
            'transferred',
            'Transferred from bed #'.$transfer->from_bed_id.' to bed #'.$transfer->to_bed_id.'.',
            [
                'from_bed_id' => $transfer->from_bed_id,
                'to_bed_id' => $transfer->to_bed_id,
                'reason' => $transfer->reason,
            ],
        );
    }

    public function finalizeReleasedAdmission(int $patientId, User $actor): void
    {
        $admission = Admission::query()
            ->open()
            ->where('patient_id', $patientId)
            ->first();

        if ($admission === null || ! $admission->isAuthorizedForRelease()) {
            return;
        }

        $from = $admission->status;

        $admission->update([
            'status' => 'discharged',
            'discharged_by' => $actor->id,
            'discharged_at' => now(),
        ]);

        $this->recordEvent(
            $admission,
            $actor,
            AdmissionEvent::TYPE_DISCHARGED,
            $from,
            'discharged',
            'Patient discharged. The bed is dirty until cleaning is completed.',
        );
    }

    public function assertAuthorizedForRelease(Admission $admission): void
    {
        if (! $admission->isAuthorizedForRelease()) {
            throw ValidationException::withMessages([
                'status' => 'Clinical discharge authorization is required before this bed can be released.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function recordEvent(
        Admission $admission,
        ?User $actor,
        string $type,
        ?string $from,
        ?string $to,
        string $notes,
        array $meta = [],
    ): void {
        AdmissionEvent::query()->create([
            'admission_id' => $admission->id,
            'patient_id' => $admission->patient_id,
            'actor_user_id' => $actor?->id,
            'event_type' => $type,
            'from_status' => $from,
            'to_status' => $to,
            'notes' => $notes,
            'meta' => $meta === [] ? null : $meta,
            'created_at' => now(),
        ]);
    }

    private function assertNoOpenAdmission(int $patientId): void
    {
        $open = Admission::query()
            ->where('patient_id', $patientId)
            ->open()
            ->lockForUpdate()
            ->first();

        if ($open) {
            throw ValidationException::withMessages([
                'patient_id' => 'This patient already has an open admission ('.$open->admission_no.').',
            ]);
        }
    }
}
