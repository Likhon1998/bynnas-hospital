<?php

namespace Database\Factories;

use App\Models\HospitalAchievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HospitalAchievement>
 */
class HospitalAchievementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'year' => (string) fake()->numberBetween(2008, 2026),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->sentence(16),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
