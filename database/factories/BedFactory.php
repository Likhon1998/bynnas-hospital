<?php

namespace Database\Factories;

use App\Models\Bed;
use App\Models\Patient;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bed>
 */
class BedFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ward_id' => Ward::factory(),
            'patient_id' => null,
            'bed_code' => 'B-'.fake()->unique()->numerify('####'),
            'status' => 'available',
            'admitted_at' => null,
            'status_changed_at' => now()->subHours(2),
            'turnover_hours' => null,
            'is_maintenance' => false,
            'maintenance_reason' => null,
            'maintenance_started_at' => null,
            'reserved_patient_id' => null,
            'reserved_until' => null,
            'reservation_note' => null,
            'reserved_by' => null,
            'reserved_at' => null,
            'lock_version' => 0,
        ];
    }

    public function occupied(): static
    {
        return $this->state(function (): array {
            $admittedAt = now()->subHours(fake()->numberBetween(6, 96));

            return [
                'status' => 'occupied',
                'patient_id' => Patient::factory(),
                'admitted_at' => $admittedAt,
                'status_changed_at' => $admittedAt,
                'turnover_hours' => null,
            ];
        });
    }

    public function dirty(): static
    {
        return $this->state(fn (): array => [
            'status' => 'dirty',
            'patient_id' => null,
            'admitted_at' => null,
            'status_changed_at' => now()->subHours(fake()->numberBetween(1, 8)),
            'turnover_hours' => fake()->randomFloat(1, 8, 48),
        ]);
    }

    public function cleaning(): static
    {
        return $this->state(fn (): array => [
            'status' => 'cleaning',
            'patient_id' => null,
            'admitted_at' => null,
            'status_changed_at' => now()->subMinutes(fake()->numberBetween(20, 180)),
            'turnover_hours' => fake()->randomFloat(1, 8, 48),
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (): array => [
            'status' => 'available',
            'is_maintenance' => true,
            'maintenance_reason' => 'Equipment check',
            'maintenance_started_at' => now()->subHours(2),
        ]);
    }

    public function reserved(): static
    {
        return $this->state(fn (): array => [
            'status' => 'available',
            'reserved_patient_id' => Patient::factory(),
            'reserved_until' => now()->addHours(4),
            'reservation_note' => 'OR recovery hold',
            'reserved_at' => now(),
        ]);
    }
}
