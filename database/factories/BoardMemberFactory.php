<?php

namespace Database\Factories;

use App\Models\BoardMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoardMember>
 */
class BoardMemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'title' => fake()->randomElement(['Chairman', 'Managing Director', 'Board Member', 'Director']),
            'role_label' => fake()->optional()->randomElement(['Governance', 'Finance', 'Clinical oversight']),
            'bio' => fake()->sentence(18),
            'photo' => null,
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->numerify('017########'),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
