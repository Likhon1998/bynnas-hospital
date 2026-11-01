<?php

namespace Database\Factories;

use App\Models\WorkItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkItem>
 */
class WorkItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sourceId = fake()->unique()->numberBetween(1, 999999);

        return [
            'work_key' => 'reception:waiting:visit:'.$sourceId,
            'desk' => 'reception',
            'kind' => 'waiting',
            'title' => 'Waiting VIS-'.fake()->numerify('###'),
            'detail' => 'Patient is in the waiting area.',
            'href' => '/admin/dashboard',
            'tone' => 'orange',
            'source_type' => 'visit',
            'source_id' => $sourceId,
            'patient_id' => null,
            'audience_user_id' => null,
            'audience_doctor_id' => null,
            'is_critical' => false,
            'is_notification' => false,
            'status' => 'open',
        ];
    }

    public function critical(): static
    {
        return $this->state(fn (): array => [
            'is_critical' => true,
            'is_notification' => true,
            'tone' => 'red',
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (): array => [
            'status' => 'done',
            'completed_at' => now(),
        ]);
    }
}
