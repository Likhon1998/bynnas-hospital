<?php

namespace Database\Factories;

use App\Models\BoardMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $name = fake()->name();
        $role = fake()->randomElement(array_keys(BoardMember::ROLES));

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'title' => BoardMember::ROLES[$role],
            'role' => $role,
            'role_label' => fake()->optional()->randomElement(['Governance', 'Finance', 'Clinical oversight', 'Board of Directors']),
            'bio' => fake()->sentence(18),
            'achievements' => fake()->optional()->randomElements([
                'Led campus expansion',
                'Opened emergency desk 24/7',
                'Established consultant roster standards',
                'Guided diagnostic floor build-out',
            ], fake()->numberBetween(1, 3)),
            'photo' => null,
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->numerify('017########'),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
