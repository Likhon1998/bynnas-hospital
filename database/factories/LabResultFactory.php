<?php

namespace Database\Factories;

use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LabResult>
 */
class LabResultFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lab_order_id' => LabOrder::factory()->processing(),
            'patient_id' => fn (array $attributes) => LabOrder::query()->find($attributes['lab_order_id'])?->patient_id,
            'visit_id' => fn (array $attributes) => LabOrder::query()->find($attributes['lab_order_id'])?->visit_id,
            'value' => '4.2',
            'numeric_value' => 4.2,
            'unit' => 'mmol/L',
            'reference_range' => '3.5–5.1',
            'is_critical' => false,
            'entered_by' => User::factory(),
            'verified_by' => fn (array $attributes) => $attributes['entered_by'],
            'verified_at' => now(),
        ];
    }
}
