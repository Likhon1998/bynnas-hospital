<?php

namespace Database\Factories;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admission>
 */
class AdmissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'admission_no' => 'ADM-'.now()->format('Ymd').'-'.fake()->unique()->numerify('###'),
            'patient_id' => Patient::factory(),
            'source' => 'direct',
            'admission_reason' => 'Observation',
            'status' => 'admitted',
            'admitted_at' => now(),
        ];
    }

    public function forBed(Bed $bed): static
    {
        return $this->state(fn (): array => [
            'patient_id' => $bed->patient_id,
            'bed_id' => $bed->id,
            'ward_id' => $bed->ward_id,
            'admission_reason' => $bed->admission_reason ?: 'Observation',
        ]);
    }

    public function authorized(): static
    {
        return $this->state(fn (): array => [
            'status' => 'discharge_authorized',
            'authorized_at' => now(),
            'discharge_summary' => 'Recovered enough for home care.',
            'discharge_medications' => 'Paracetamol as needed.',
            'follow_up_plan' => 'Clinic review in 7 days.',
            'billing_reviewed' => true,
        ]);
    }
}
