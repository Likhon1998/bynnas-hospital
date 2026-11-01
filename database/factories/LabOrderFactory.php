<?php

namespace Database\Factories;

use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LabOrder>
 */
class LabOrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_no' => 'LAB-'.now()->format('Ymd').'-'.fake()->unique()->numerify('###'),
            'patient_id' => Patient::factory(),
            'visit_id' => fn (array $attributes) => Visit::factory()->create([
                'patient_id' => $attributes['patient_id'],
            ])->id,
            'lab_test_id' => LabTest::factory(),
            'ordered_by' => User::factory(),
            'clinical_notes' => 'Chest pain workup',
            'status' => 'ordered',
        ];
    }

    public function collected(): static
    {
        return $this->state(fn (): array => [
            'status' => 'collected',
            'collected_at' => now(),
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (): array => [
            'status' => 'processing',
            'collected_at' => now()->subHour(),
            'processing_at' => now(),
        ]);
    }
}
