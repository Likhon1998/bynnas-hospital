<?php

namespace Database\Factories;

use App\Models\NursingHandover;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NursingHandover>
 */
class NursingHandoverFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ward_id' => Ward::factory(),
            'from_user_id' => null,
            'shift' => 'morning',
            'incoming_shift' => 'evening',
            'summary' => 'Ward is stable. Two patients need close observation.',
            'outstanding_work' => 'Complete overdue wound care.',
            'meta' => null,
            'handed_over_at' => now(),
        ];
    }
}
