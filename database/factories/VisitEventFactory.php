<?php

namespace Database\Factories;

use App\Models\Visit;
use App\Models\VisitEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitEvent>
 */
class VisitEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'visit_id' => Visit::factory(),
            'patient_id' => null,
            'actor_user_id' => null,
            'event_type' => VisitEvent::TYPE_CHECKED_IN,
            'from_status' => null,
            'to_status' => 'waiting',
            'notes' => 'Patient arrived and was checked in.',
            'meta' => null,
            'created_at' => now(),
        ];
    }
}
