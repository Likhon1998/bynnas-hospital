<?php

namespace Database\Factories;

use App\Models\LabTest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LabTest>
 */
class LabTestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('L??##')),
            'name' => 'Serum Potassium',
            'category' => 'chemistry',
            'specimen' => 'blood',
            'unit' => 'mmol/L',
            'reference_range' => '3.5–5.1',
            'critical_low' => 2.5,
            'critical_high' => 6.5,
            'price' => 450,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
