<?php

namespace Database\Factories;

use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ward>
 */
class WardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['icu', 'general', 'nicu', 'isolation']);

        return [
            'name' => strtoupper($type).' Ward '.fake()->unique()->numberBetween(1, 99),
            'code' => strtoupper(substr($type, 0, 3)).'-'.fake()->unique()->numerify('##'),
            'type' => $type,
            'capacity' => fake()->numberBetween(8, 30),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
