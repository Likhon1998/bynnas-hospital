<?php

namespace Database\Factories;

use App\Models\Bed;
use App\Models\BedEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BedEvent>
 */
class BedEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bed_id' => Bed::factory(),
            'patient_id' => null,
            'actor_user_id' => null,
            'event_type' => BedEvent::TYPE_CLEANING_STARTED,
            'from_status' => 'dirty',
            'to_status' => 'cleaning',
            'related_bed_id' => null,
            'notes' => null,
            'meta' => null,
            'created_at' => now(),
        ];
    }
}
