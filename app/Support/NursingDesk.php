<?php

namespace App\Support;

use App\Models\Bed;
use App\Models\MedicationAdministration;
use App\Models\NursingAuditEvent;
use App\Models\NursingHandover;
use App\Models\NursingObservation;
use App\Models\NursingTask;
use App\Models\Patient;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NursingDesk
{
    /**
     * @return Collection<int, Ward>
     */
    public function wards(): Collection
    {
        return Ward::query()
            ->active()
            ->withCount(['beds', 'occupiedBeds'])
            ->withCount([
                'nursingTasks as open_tasks_count' => fn ($query) => $query->open(),
                'nursingTasks as overdue_tasks_count' => fn ($query) => $query->overdue(),
            ])
            ->with(['latestHandover.fromUser'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array{
     *     beds: Collection<int, Bed>,
     *     tasks: Collection<int, NursingTask>,
     *     handovers: Collection<int, NursingHandover>,
     *     outstanding: string,
     *     counts: array{patients: int, due: int, overdue: int}
     * }
     */
    public function wardBoard(Ward $ward): array
    {
        $beds = Bed::query()
            ->where('ward_id', $ward->id)
            ->occupied()
            ->with(['patient.latestClinicalVisit'])
            ->orderBy('bed_code')
            ->orderBy('id')
            ->get();

        $tasks = NursingTask::query()
            ->where('ward_id', $ward->id)
            ->open()
            ->with(['patient', 'bed'])
            ->orderByRaw('case when due_at is not null and due_at < ? then 0 else 1 end', [now()])
            ->orderBy('due_at')
            ->orderBy('id')
            ->get();

        $handovers = NursingHandover::query()
            ->where('ward_id', $ward->id)
            ->with('fromUser')
            ->orderByDesc('handed_over_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $outstanding = $tasks
            ->map(fn (NursingTask $task): string => $task->title.' · '.$task->patient?->full_name.' · '.$task->statusLabel())
            ->implode("\n");

        return [
            'beds' => $beds,
            'tasks' => $tasks,
            'handovers' => $handovers,
            'outstanding' => $outstanding,
            'counts' => [
                'patients' => $beds->count(),
                'due' => $tasks->count(),
                'overdue' => $tasks->filter(fn (NursingTask $task): bool => $task->isOverdue())->count(),
            ],
        ];
    }

    /**
     * @param  array{observation_type?: string, blood_pressure?: string|null, temperature?: float|int|string|null, pulse?: int|null, oxygen_saturation?: int|null, weight?: float|int|string|null, pain_score?: int|null, notes?: string|null}  $attributes
     */
    public function recordObservation(Patient $patient, array $attributes, User $actor): NursingObservation
    {
        $bed = $this->occupiedBedFor($patient);

        return DB::transaction(function () use ($patient, $bed, $attributes, $actor): NursingObservation {
            $observation = NursingObservation::query()->create([
                'patient_id' => $patient->id,
                'bed_id' => $bed->id,
                'ward_id' => $bed->ward_id,
                'recorded_by' => $actor->id,
                'recorded_at' => now(),
                'observation_type' => $attributes['observation_type'] ?? 'vitals',
                'blood_pressure' => $attributes['blood_pressure'] ?? null,
                'temperature' => $attributes['temperature'] ?? null,
                'pulse' => $attributes['pulse'] ?? null,
                'oxygen_saturation' => $attributes['oxygen_saturation'] ?? null,
                'weight' => $attributes['weight'] ?? null,
                'pain_score' => $attributes['pain_score'] ?? null,
                'notes' => $attributes['notes'] ?? null,
            ]);

            $this->audit(
                $actor,
                $observation,
                NursingAuditEvent::ACTION_RECORDED,
                'Observation recorded on '.$bed->bed_code.'.',
                $patient,
                $bed->ward_id,
                [
                    'observation_type' => $observation->observation_type,
                    'blood_pressure' => $observation->blood_pressure,
                    'recorded_at' => $observation->recorded_at?->toIso8601String(),
                ],
            );

            return $observation;
        });
    }

    /**
     * @param  array{task_type: string, title: string, instructions?: string|null, due_at?: string|null}  $attributes
     */
    public function createTask(Patient $patient, array $attributes, User $actor): NursingTask
    {
        $bed = $this->occupiedBedFor($patient);

        return DB::transaction(function () use ($patient, $bed, $attributes, $actor): NursingTask {
            $task = NursingTask::query()->create([
                'patient_id' => $patient->id,
                'bed_id' => $bed->id,
                'ward_id' => $bed->ward_id,
                'created_by' => $actor->id,
                'task_type' => $attributes['task_type'],
                'title' => $attributes['title'],
                'instructions' => $attributes['instructions'] ?? null,
                'status' => 'pending',
                'due_at' => $attributes['due_at'] ?? null,
            ]);

            $this->audit(
                $actor,
                $task,
                NursingAuditEvent::ACTION_CREATED,
                'Nursing task created: '.$task->title,
                $patient,
                $bed->ward_id,
                ['task_type' => $task->task_type, 'due_at' => $task->due_at?->toIso8601String()],
            );

            return $task;
        });
    }

    public function updateTaskStatus(NursingTask $task, string $status, User $actor): NursingTask
    {
        if (! in_array($status, ['pending', 'in_progress', 'completed'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Choose a valid task status.',
            ]);
        }

        return DB::transaction(function () use ($task, $status, $actor): NursingTask {
            $from = $task->status;

            $task->update([
                'status' => $status,
                'started_at' => $status === 'in_progress' ? ($task->started_at ?? now()) : $task->started_at,
                'started_by' => $status === 'in_progress' ? ($task->started_by ?? $actor->id) : $task->started_by,
                'completed_at' => $status === 'completed' ? now() : null,
                'completed_by' => $status === 'completed' ? $actor->id : null,
            ]);

            $this->audit(
                $actor,
                $task,
                NursingAuditEvent::ACTION_UPDATED,
                'Task moved from '.$from.' to '.$status.'.',
                $task->patient,
                $task->ward_id,
                ['from' => $from, 'to' => $status],
            );

            return $task->refresh();
        });
    }

    /**
     * @param  array{shift: string, incoming_shift: string, summary: string, outstanding_work?: string|null}  $attributes
     */
    public function recordHandover(Ward $ward, array $attributes, User $actor): NursingHandover
    {
        return DB::transaction(function () use ($ward, $attributes, $actor): NursingHandover {
            $openTasks = NursingTask::query()
                ->where('ward_id', $ward->id)
                ->open()
                ->with('patient')
                ->orderBy('due_at')
                ->orderBy('id')
                ->get();

            $outstanding = filled($attributes['outstanding_work'] ?? null)
                ? $attributes['outstanding_work']
                : $openTasks
                    ->map(fn (NursingTask $task): string => $task->title.' · '.$task->patient?->full_name.' · '.$task->statusLabel())
                    ->implode("\n");

            $handover = NursingHandover::query()->create([
                'ward_id' => $ward->id,
                'from_user_id' => $actor->id,
                'shift' => $attributes['shift'],
                'incoming_shift' => $attributes['incoming_shift'],
                'summary' => $attributes['summary'],
                'outstanding_work' => $outstanding,
                'handed_over_at' => now(),
                'meta' => [
                    'open_task_ids' => $openTasks->pluck('id')->all(),
                    'open_task_count' => $openTasks->count(),
                ],
            ]);

            $this->audit(
                $actor,
                $handover,
                NursingAuditEvent::ACTION_HANDED_OVER,
                $ward->name.' handed from '.$handover->shiftLabel().' to '.$handover->incomingShiftLabel().'.',
                null,
                $ward->id,
                [
                    'shift' => $handover->shift,
                    'incoming_shift' => $handover->incoming_shift,
                    'handed_over_at' => $handover->handed_over_at?->toIso8601String(),
                    'open_task_count' => $openTasks->count(),
                ],
            );

            return $handover;
        });
    }

    /**
     * @param  array{medication_name: string, dose: string, route: string, status: string, notes?: string|null}  $attributes
     */
    public function administerMedication(Patient $patient, array $attributes, User $actor): MedicationAdministration
    {
        $bed = $this->occupiedBedFor($patient);

        return DB::transaction(function () use ($patient, $bed, $attributes, $actor): MedicationAdministration {
            $record = MedicationAdministration::query()->create([
                'patient_id' => $patient->id,
                'bed_id' => $bed->id,
                'ward_id' => $bed->ward_id,
                'medication_name' => $attributes['medication_name'],
                'dose' => $attributes['dose'],
                'route' => $attributes['route'],
                'status' => $attributes['status'],
                'administered_at' => now(),
                'administered_by' => $actor->id,
                'notes' => $attributes['notes'] ?? null,
            ]);

            $this->audit(
                $actor,
                $record,
                NursingAuditEvent::ACTION_ADMINISTERED,
                $record->medication_name.' '.$record->statusLabel().' on '.$bed->bed_code.'.',
                $patient,
                $bed->ward_id,
                [
                    'medication_name' => $record->medication_name,
                    'dose' => $record->dose,
                    'route' => $record->route,
                    'status' => $record->status,
                    'administered_at' => $record->administered_at?->toIso8601String(),
                ],
            );

            return $record;
        });
    }

    public function occupiedBedFor(Patient $patient): Bed
    {
        $bed = Bed::query()
            ->occupied()
            ->where('patient_id', $patient->id)
            ->first();

        if ($bed === null) {
            throw ValidationException::withMessages([
                'patient_id' => 'This patient is not currently admitted to a bed.',
            ]);
        }

        return $bed;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function audit(
        User $actor,
        NursingObservation|NursingTask|NursingHandover|MedicationAdministration $auditable,
        string $action,
        string $summary,
        ?Patient $patient,
        ?int $wardId,
        array $meta = [],
    ): void {
        NursingAuditEvent::query()->create([
            'patient_id' => $patient?->id,
            'ward_id' => $wardId,
            'actor_user_id' => $actor->id,
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => $auditable->id,
            'action' => $action,
            'summary' => $summary,
            'meta' => $meta === [] ? null : $meta,
            'created_at' => now(),
        ]);
    }
}
