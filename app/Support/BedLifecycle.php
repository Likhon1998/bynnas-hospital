<?php

namespace App\Support;

use App\Models\Bed;
use App\Models\BedEvent;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BedLifecycle
{
    /**
     * Cleaning / occupancy state machine only.
     * Reservation, maintenance, and transfer live outside this field.
     *
     * @var array<string, list<string>>
     */
    public const TRANSITIONS = [
        'available' => ['occupied'],
        'occupied' => ['dirty'],
        'dirty' => ['cleaning'],
        'cleaning' => ['available', 'dirty'],
    ];

    public function __construct(
        private BedAuditor $auditor,
        private BedConcurrency $concurrency,
    ) {}

    /**
     * @return list<string>
     */
    public function allowedNextStatuses(Bed $bed): array
    {
        $next = self::TRANSITIONS[$bed->status] ?? [];

        if ($bed->status === 'available' && $bed->is_maintenance) {
            return [];
        }

        return $next;
    }

    public function canTransition(Bed $bed, string $nextStatus): bool
    {
        return in_array($nextStatus, $this->allowedNextStatuses($bed), true);
    }

    public function apply(
        Bed $bed,
        string $nextStatus,
        ?int $patientId = null,
        ?User $actor = null,
        ?string $notes = null,
        ?int $lockVersion = null,
    ): Bed {
        if (! $this->canTransition($bed, $nextStatus)) {
            throw new InvalidArgumentException(
                "Bed {$bed->bed_code} cannot move from {$bed->status} to {$nextStatus}."
            );
        }

        return match ($nextStatus) {
            'occupied' => $this->admit($bed, $patientId, $actor, $notes, $lockVersion),
            'dirty' => $bed->status === 'occupied'
                ? $this->discharge($bed, $actor, $notes, $lockVersion)
                : $this->failInspection($bed, $actor, $notes, $lockVersion),
            'cleaning' => $this->startCleaning($bed, $actor, $notes, $lockVersion),
            'available' => $this->passInspection($bed, $actor, $notes, $lockVersion),
            default => throw new InvalidArgumentException("Unknown bed status [{$nextStatus}]."),
        };
    }

    public function admit(
        Bed $bed,
        ?int $patientId,
        ?User $actor = null,
        ?string $notes = null,
        ?int $lockVersion = null,
    ): Bed {
        return DB::transaction(function () use ($bed, $patientId, $actor, $notes, $lockVersion): Bed {
            $bed = Bed::query()->lockForUpdate()->findOrFail($bed->id);
            $this->clearExpiredReservation($bed, $actor);
            $this->concurrency->claim($bed, $lockVersion);

            if ($bed->status !== 'available') {
                throw new InvalidArgumentException('Only an available bed can admit a patient.');
            }

            if ($patientId === null) {
                throw new InvalidArgumentException('A patient is required to occupy a bed.');
            }

            $blockers = $bed->admissionBlockers($patientId);

            if ($blockers !== []) {
                throw new InvalidArgumentException(implode(' ', $blockers));
            }

            $patient = Patient::query()->find($patientId);

            if ($patient === null || ! $patient->is_active) {
                throw new InvalidArgumentException('Choose an active patient record.');
            }

            $alreadyAssigned = Bed::query()
                ->where('patient_id', $patientId)
                ->where('id', '!=', $bed->id)
                ->exists();

            if ($alreadyAssigned) {
                throw new InvalidArgumentException('That patient is already assigned to another bed.');
            }

            $fromStatus = $bed->status;

            $bed->fill([
                'status' => 'occupied',
                'patient_id' => $patientId,
                'admitted_at' => now(),
                'status_changed_at' => now(),
                'turnover_hours' => null,
                'reserved_patient_id' => null,
                'reserved_until' => null,
                'reservation_note' => null,
                'reserved_by' => null,
                'reserved_at' => null,
            ])->save();

            $this->auditor->record(
                bed: $bed,
                eventType: BedEvent::TYPE_ADMITTED,
                actor: $actor,
                fromStatus: $fromStatus,
                toStatus: 'occupied',
                patientId: $patientId,
                notes: $notes,
            );

            return $bed->refresh();
        });
    }

    public function discharge(
        Bed $bed,
        ?User $actor = null,
        ?string $notes = null,
        ?int $lockVersion = null,
    ): Bed {
        return DB::transaction(function () use ($bed, $actor, $notes, $lockVersion): Bed {
            $bed = Bed::query()->lockForUpdate()->findOrFail($bed->id);
            $this->concurrency->claim($bed, $lockVersion);

            if ($bed->status !== 'occupied') {
                throw new InvalidArgumentException('Only an occupied bed can be discharged.');
            }

            $patientId = $bed->patient_id;
            $fromStatus = $bed->status;
            $turnoverHours = $bed->admitted_at !== null
                ? round(max(0.1, $bed->admitted_at->diffInMinutes(now()) / 60), 1)
                : null;

            $bed->fill([
                'status' => 'dirty',
                'patient_id' => null,
                'admitted_at' => null,
                'status_changed_at' => now(),
                'turnover_hours' => $turnoverHours,
            ])->save();

            $this->auditor->record(
                bed: $bed,
                eventType: BedEvent::TYPE_DISCHARGED,
                actor: $actor,
                fromStatus: $fromStatus,
                toStatus: 'dirty',
                patientId: $patientId,
                notes: $notes,
                meta: ['turnover_hours' => $turnoverHours],
            );

            return $bed->refresh();
        });
    }

    public function startCleaning(
        Bed $bed,
        ?User $actor = null,
        ?string $notes = null,
        ?int $lockVersion = null,
    ): Bed {
        return DB::transaction(function () use ($bed, $actor, $notes, $lockVersion): Bed {
            $bed = Bed::query()->lockForUpdate()->findOrFail($bed->id);
            $this->concurrency->claim($bed, $lockVersion);

            if ($bed->status !== 'dirty') {
                throw new InvalidArgumentException('Housekeeping can only start on a dirty bed.');
            }

            $fromStatus = $bed->status;

            $bed->fill([
                'status' => 'cleaning',
                'patient_id' => null,
                'admitted_at' => null,
                'status_changed_at' => now(),
            ])->save();

            $this->auditor->record(
                bed: $bed,
                eventType: BedEvent::TYPE_CLEANING_STARTED,
                actor: $actor,
                fromStatus: $fromStatus,
                toStatus: 'cleaning',
                notes: $notes,
            );

            return $bed->refresh();
        });
    }

    public function passInspection(
        Bed $bed,
        ?User $actor = null,
        ?string $notes = null,
        ?int $lockVersion = null,
    ): Bed {
        return DB::transaction(function () use ($bed, $actor, $notes, $lockVersion): Bed {
            $bed = Bed::query()->lockForUpdate()->findOrFail($bed->id);
            $this->concurrency->claim($bed, $lockVersion);

            if ($bed->status !== 'cleaning') {
                throw new InvalidArgumentException('Only a cleaned bed can pass inspection.');
            }

            $fromStatus = $bed->status;

            $bed->fill([
                'status' => 'available',
                'patient_id' => null,
                'admitted_at' => null,
                'status_changed_at' => now(),
            ])->save();

            $this->auditor->record(
                bed: $bed,
                eventType: BedEvent::TYPE_INSPECTION_PASSED,
                actor: $actor,
                fromStatus: $fromStatus,
                toStatus: 'available',
                notes: $notes ?: 'Inspection passed — bed ready for admission.',
            );

            return $bed->refresh();
        });
    }

    public function failInspection(
        Bed $bed,
        ?User $actor = null,
        ?string $notes = null,
        ?int $lockVersion = null,
    ): Bed {
        return DB::transaction(function () use ($bed, $actor, $notes, $lockVersion): Bed {
            $bed = Bed::query()->lockForUpdate()->findOrFail($bed->id);
            $this->concurrency->claim($bed, $lockVersion);

            if ($bed->status !== 'cleaning') {
                throw new InvalidArgumentException('Only a bed in cleaning can fail inspection.');
            }

            if ($notes === null || trim($notes) === '') {
                throw new InvalidArgumentException('Inspection failure requires a reason.');
            }

            $fromStatus = $bed->status;

            $bed->fill([
                'status' => 'dirty',
                'patient_id' => null,
                'admitted_at' => null,
                'status_changed_at' => now(),
            ])->save();

            $this->auditor->record(
                bed: $bed,
                eventType: BedEvent::TYPE_INSPECTION_FAILED,
                actor: $actor,
                fromStatus: $fromStatus,
                toStatus: 'dirty',
                notes: $notes,
            );

            return $bed->refresh();
        });
    }

    /**
     * Caller must already hold a row lock when mutating. Does not bump lock_version.
     */
    public function clearExpiredReservation(Bed $bed, ?User $actor = null): void
    {
        if (! $bed->reservationIsExpired()) {
            return;
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
            eventType: BedEvent::TYPE_RESERVATION_EXPIRED,
            actor: $actor,
            fromStatus: $bed->status,
            toStatus: $bed->status,
            patientId: $patientId,
            notes: 'Reservation expired and was cleared.',
        );
    }

    public function actionLabel(string $fromStatus, string $toStatus): string
    {
        return match (true) {
            $fromStatus === 'available' && $toStatus === 'occupied' => 'Admit patient',
            $fromStatus === 'occupied' && $toStatus === 'dirty' => 'Discharge',
            $fromStatus === 'dirty' && $toStatus === 'cleaning' => 'Start cleaning',
            $fromStatus === 'cleaning' && $toStatus === 'available' => 'Pass inspection',
            $fromStatus === 'cleaning' && $toStatus === 'dirty' => 'Fail inspection',
            default => 'Update status',
        };
    }
}
