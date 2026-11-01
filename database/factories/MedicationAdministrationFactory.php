<?php

namespace Database\Factories;

use App\Models\MedicationAdministration;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicationAdministration>
 */
class MedicationAdministrationFactory extends Factory
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
            'medication_name' => 'Paracetamol',
            'dose' => '500mg',
            'route' => 'oral',
            'status' => 'given',
            'administered_at' => now(),
            'administered_by' => null,
            'notes' => null,
        ];
    }
}
