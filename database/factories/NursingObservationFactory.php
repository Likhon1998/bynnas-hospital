<?php

namespace Database\Factories;

use App\Models\Bed;
use App\Models\NursingObservation;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NursingObservation>
 */
class NursingObservationFactory extends Factory
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
            'recorded_by' => null,
            'recorded_at' => now(),
            'observation_type' => 'vitals',
            'blood_pressure' => '120/80',
            'temperature' => 37.1,
            'pulse' => 78,
            'oxygen_saturation' => 98,
            'weight' => 64.0,
            'pain_score' => 2,
            'notes' => 'Stable on the ward.',
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
}
