<?php

namespace Database\Factories;

use App\Models\Acknowledgement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Acknowledgement>
 */
class AcknowledgementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message' => 'আমাদের হাসপাতালে আপনাকে স্বাগতম।',
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
