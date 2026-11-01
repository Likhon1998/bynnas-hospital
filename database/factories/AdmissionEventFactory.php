<?php

namespace Database\Factories;

use App\Models\Admission;
use App\Models\AdmissionEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdmissionEvent>
 */
class AdmissionEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'admission_id' => Admission::factory(),
            'patient_id' => null,
            'actor_user_id' => null,
            'event_type' => AdmissionEvent::TYPE_ADMITTED,
            'from_status' => null,
            'to_status' => 'admitted',
            'notes' => 'Admitted.',
            'meta' => null,
            'created_at' => now(),
        ];
    }
}
