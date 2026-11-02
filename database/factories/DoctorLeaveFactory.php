<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorLeave;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorLeave>
 */
class DoctorLeaveFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->addDay()->startOfDay();

        return [
            'doctor_id' => Doctor::factory(),
            'starts_on' => $start->toDateString(),
            'ends_on' => $start->copy()->addDays(6)->toDateString(),
            'type' => 'vacation',
            'reason' => 'Annual leave',
            'is_active' => true,
            'created_by' => null,
        ];
    }
}
