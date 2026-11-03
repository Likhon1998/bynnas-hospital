<?php

namespace Database\Factories;

use App\Models\HospitalExpense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HospitalExpense>
 */
class HospitalExpenseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_date' => now()->toDateString(),
            'category' => fake()->randomElement(['Supplies', 'Utilities', 'Payroll', 'Maintenance']),
            'description' => fake()->sentence(4),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'recorded_by' => null,
        ];
    }
}
