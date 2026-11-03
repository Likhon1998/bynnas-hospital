<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillPayment>
 */
class BillPaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bill_id' => Bill::factory(),
            'amount' => fake()->randomElement([500, 800, 1200, 1500]),
            'method' => fake()->randomElement(array_keys(BillPayment::METHODS)),
            'reference' => null,
            'notes' => null,
            'collected_by' => User::factory(),
            'collected_at' => now(),
        ];
    }
}
