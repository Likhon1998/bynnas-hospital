<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'visit_no' => 'VIS-'.now()->format('Ymd').'-'.fake()->unique()->numerify('###'),
            'patient_id' => Patient::factory(),
            'appointment_id' => null,
            'doctor_id' => Doctor::factory(),
            'department_id' => fn (array $attributes) => Doctor::query()->find($attributes['doctor_id'])?->department_id,
            'checked_in_by' => null,
            'status' => 'waiting',
            'chief_complaint' => 'Fever and cough',
            'arrived_at' => now(),
        ];
    }

    public function waiting(): static
    {
        return $this->state(fn (): array => [
            'status' => 'waiting',
        ]);
    }

    public function withVitals(): static
    {
        return $this->state(fn (): array => [
            'status' => 'triage',
            'triage_started_at' => now(),
            'blood_pressure' => '120/80',
            'temperature' => 37.2,
            'pulse' => 78,
            'oxygen_saturation' => 98,
            'weight' => 64.5,
            'pain_score' => 2,
            'vitals_recorded_at' => now(),
        ]);
    }
}
