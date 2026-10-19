<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bill>
 */
class BillFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bill_no' => 'BILL-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'patient_id' => Patient::factory(),
            'appointment_id' => null,
            'issued_by' => null,
            'bill_date' => now()->toDateString(),
            'status' => 'draft',
            'subtotal' => 0,
            'discount' => 0,
            'total' => 0,
            'paid_amount' => 0,
            'notes' => null,
        ];
    }
}
