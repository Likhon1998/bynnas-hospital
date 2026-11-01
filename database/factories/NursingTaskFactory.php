<?php

namespace Database\Factories;

use App\Models\Bed;
use App\Models\NursingTask;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NursingTask>
 */
class NursingTaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'bed_id' => null,
            'ward_id' => null,
            'created_by' => null,
            'task_type' => 'observation',
            'title' => 'Four-hourly observations',
            'instructions' => 'Record vitals and report deterioration.',
            'status' => 'pending',
            'due_at' => now()->addHours(2),
        ];
    }

    public function forBed(Bed $bed): static
    {
        return $this->state(fn (): array => [
            'patient_id' => $bed->patient_id,
            'bed_id' => $bed->id,
            'ward_id' => $bed->ward_id,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (): array => [
            'status' => 'pending',
            'due_at' => now()->subHour(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
