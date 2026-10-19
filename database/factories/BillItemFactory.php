<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\BillItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillItem>
 */
class BillItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 200, 2500);
        $quantity = fake()->numberBetween(1, 3);

        return [
            'bill_id' => Bill::factory(),
            'item_type' => 'custom',
            'item_id' => null,
            'description' => fake()->words(3, true).' (Demo)',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => round($quantity * $unitPrice, 2),
            'sort_order' => 1,
        ];
    }
}
