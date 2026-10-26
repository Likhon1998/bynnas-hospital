<?php

namespace App\Support;

use App\Models\Bed;
use App\Models\BedEvent;
use App\Models\BedTransfer;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BedOperations
{
    public function __construct(
        private BedLifecycle $lifecycle,
        private BedAuditor $auditor,
        private BedConcurrency $concurrency,
    ) {}

    public function reserve(
        Bed $bed,
        int $patientId,
        ?User $actor = null,
        ?string $note = null,
        ?\DateTimeInterface $until = null,
        ?int $lockVersion = null,
    ): Bed {
        return DB::transaction(function () use ($bed, $patientId, $actor, $note, $until, $lockVersion): Bed {
            $bed = Bed::query()->lockForUpdate()->findOrFail($bed->id);
            $this->lifecycle->clearExpiredReservation($bed, $actor);
            $this->concurrency->claim($bed, $lockVersion);

            if ($bed->status !== 'available') {
                throw new InvalidArgumentException('Only available beds can be reserved.');
            }

            if ($bed->is_maintenance) {
                throw new InvalidArgumentException('A bed under maintenance cannot be reserved.');
            }

            if ($bed->hasActiveReservation()) {
                throw new InvalidArgumentException('This bed already has an active reservation.');
            }

            $patient = Patient::query()->find($patientId);

            if ($patient === null || ! $patient->is_active) {
                throw new InvalidArgumentException('Choose an active patient for the reservation.');
            }

            if (Bed::query()->where('patient_id', $patientId)->exists()) {
                throw new InvalidArgumentException('That patient is already occupying a bed.');
            }

            if (Bed::query()->where('reserved_patient_id', $patientId)->where('id', '!=', $bed->id)->exists()) {
                throw new InvalidArgumentException('That patient already has another bed reserved.');
            }

            $bed->fill([
                'reserved_patient_id' => $patientId,
                'reserved_until' => $until,
                'reservation_note' => $note,
                'reserved_by' => $actor?->id,
                'reserved_at' => now(),
            ])->save();

            $this->auditor->record(
                bed: $bed,
                eventType: BedEvent::TYPE_RESERVED,
                actor: $actor,
                fromStatus: $bed->status,
                toStatus: $bed->status,
                patientId: $patientId,
                notes: $note,
                meta: ['reserved_until' => $until?->format('c')],
            );

            return $bed->refresh();
        });
    }

    public function cancelReservation(
        Bed $bed,
        ?User $actor = null,
        ?string $notes = null,
        ?int $lockVersion = null,
    ): Bed {
        return DB::transaction(function () use ($bed, $actor, $notes, $lockVersion): Bed {
            $bed = Bed::query()->lockForUpdate()->findOrFail($bed->id);
            $this->concurrency->claim($bed, $lockVersion);

            if ($bed->reserved_patient_id === null) {
                throw new InvalidArgumentException('This bed has no reservation to cancel.');
            }

            $patientId = $bed->reserved_patient_id;

            $bed->fill([
                'reserved_patient_id' => null,
                'reserved_until' => null,
                'reservation_note' => null,
                'reserved_by' => null,
                'reserved_at' => null,
            ])->save();

            $this->auditor->record(
                bed: $bed,
                eventType: BedEvent::TYPE_RESERVATION_CANCELLED,
                actor: $actor,
                fromStatus: $bed->status,
                toStatus: $bed->status,
                patientId: $patientId,
                notes: $notes ?: 'Reservation cancelled.',
            );

            return $bed->refresh();
        });
    }

    public function startMaintenance(
        Bed $bed,
        string $reason,
        ?User $actor = null,
        ?int $lockVersion = null,
    ): Bed {
        return DB::transaction(function () use ($bed, $reason, $actor, $lockVersion): Bed {
            $bed = Bed::query()->lockForUpdate()->findOrFail($bed->id);
            $this->lifecycle->clearExpiredReservation($bed, $actor);
            $this->concurrency->claim($bed, $lockVersion);

            if (! in_array($bed->status, ['available', 'dirty'], true)) {
                throw new InvalidArgumentException('Maintenance can only start on available or dirty beds.');
            }

            if ($bed->is_maintenance) {
                throw new InvalidArgumentException('This bed is already under maintenance.');
            }

            if ($bed->hasActiveReservation()) {
                throw new InvalidArgumentException('Cancel the reservation before placing the bed in maintenance.');
            }

            $bed->fill([
                'is_maintenance' => true,
                'maintenance_reason' => $reason,
                'maintenance_started_at' => now(),
            ])->save();

            $this->auditor->record(
                bed: $bed,
                eventType: BedEvent::TYPE_MAINTENANCE_STARTED,
                actor: $actor,
                fromStatus: $bed->status,
                toStatus: $bed->status,
                notes: $reason,
            );

            return $bed->refresh();
        });
    }

    public function endMaintenance(
        Bed $bed,
        ?User $actor = null,
        ?string $notes = null,
        ?int $lockVersion = null,
    ): Bed {
        return DB::transaction(function () use ($bed, $actor, $notes, $lockVersion): Bed {
            $bed = Bed::query()->lockForUpdate()->findOrFail($bed->id);
            $this->concurrency->claim($bed, $lockVersion);

            if (! $bed->is_maintenance) {
                throw new InvalidArgumentException('This bed is not under maintenance.');
            }

            $bed->fill([
                'is_maintenance' => false,
                'maintenance_reason' => null,
                'maintenance_started_at' => null,
            ])->save();

            $this->auditor->record(
                bed: $bed,
                eventType: BedEvent::TYPE_MAINTENANCE_ENDED,
                actor: $actor,
                fromStatus: $bed->status,
                toStatus: $bed->status,
                notes: $notes ?: 'Maintenance completed.',
            );

            return $bed->refresh();
        });
    }

    public function transfer(
        Bed $fromBed,
        Bed $toBed,
        ?User $actor = null,
        ?string $reason = null,
        ?int $fromLockVersion = null,
        ?int $toLockVersion = null,
    ): BedTransfer {
        return DB::transaction(function () use ($fromBed, $toBed, $actor, $reason, $fromLockVersion, $toLockVersion): BedTransfer {
            $ids = collect([$fromBed->id, $toBed->id])->sort()->values();
            $locked = Bed::query()->whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

            $from = $locked->get($fromBed->id);
            $to = $locked->get($toBed->id);

            if ($from === null || $to === null) {
                throw new InvalidArgumentException('Both beds are required for a transfer.');
            }

            if ($from->id === $to->id) {
                throw new InvalidArgumentException('Choose a different destination bed.');
            }

            if ($from->status !== 'occupied' || $from->patient_id === null) {
                throw new InvalidArgumentException('Transfer requires an occupied source bed.');
            }

            $this->lifecycle->clearExpiredReservation($to, $actor);
            $this->concurrency->claim($from, $fromLockVersion);
            $this->concurrency->claim($to, $toLockVersion);

            if (! $to->isAdmittable($from->patient_id)) {
                $blockers = $to->admissionBlockers($from->patient_id);

                throw new InvalidArgumentException(
                    'Destination bed is not eligible for admission. '.implode(' ', $blockers)
                );
            }

            $patientId = $from->patient_id;
            $admittedAt = $from->admitted_at ?? now();

            $from->fill([
                'status' => 'dirty',
                'patient_id' => null,
                'admitted_at' => null,
                'status_changed_at' => now(),
                'turnover_hours' => round(max(0.1, $admittedAt->diffInMinutes(now()) / 60), 1),
            ])->save();

            $to->fill([
                'status' => 'occupied',
                'patient_id' => $patientId,
                'admitted_at' => $admittedAt,
                'status_changed_at' => now(),
                'turnover_hours' => null,
                'reserved_patient_id' => null,
                'reserved_until' => null,
                'reservation_note' => null,
                'reserved_by' => null,
                'reserved_at' => null,
            ])->save();

            $transfer = BedTransfer::query()->create([
                'patient_id' => $patientId,
                'from_bed_id' => $from->id,
                'to_bed_id' => $to->id,
                'transferred_by' => $actor?->id,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            $this->auditor->record(
                bed: $from,
                eventType: BedEvent::TYPE_TRANSFERRED_OUT,
                actor: $actor,
                fromStatus: 'occupied',
                toStatus: 'dirty',
                patientId: $patientId,
                relatedBedId: $to->id,
                notes: $reason,
            );

            $this->auditor->record(
                bed: $to,
                eventType: BedEvent::TYPE_TRANSFERRED_IN,
                actor: $actor,
                fromStatus: 'available',
                toStatus: 'occupied',
                patientId: $patientId,
                relatedBedId: $from->id,
                notes: $reason,
            );

            return $transfer;
        });
    }
}
