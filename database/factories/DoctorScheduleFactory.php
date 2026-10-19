<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorSchedule>
 */
class DoctorScheduleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'weekday' => fake()->randomElement(['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday']),
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
            'break_start' => '11:00:00',
            'break_end' => '11:20:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ];
    }
}
