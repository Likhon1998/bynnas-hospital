<?php

namespace Database\Factories;

use App\Models\FinanceApproval;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinanceApproval>
 */
class FinanceApprovalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement([FinanceApproval::TYPE_DISCOUNT, FinanceApproval::TYPE_REFUND]),
            'status' => FinanceApproval::STATUS_PENDING,
            'bill_id' => null,
            'amount' => fake()->randomFloat(2, 50, 2000),
            'reason' => fake()->sentence(6),
            'notes' => null,
            'requested_by' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
