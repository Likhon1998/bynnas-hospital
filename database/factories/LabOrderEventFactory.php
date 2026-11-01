<?php

namespace Database\Factories;

use App\Models\LabOrder;
use App\Models\LabOrderEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LabOrderEvent>
 */
class LabOrderEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lab_order_id' => LabOrder::factory(),
            'patient_id' => fn (array $attributes) => LabOrder::query()->find($attributes['lab_order_id'])?->patient_id,
            'visit_id' => fn (array $attributes) => LabOrder::query()->find($attributes['lab_order_id'])?->visit_id,
            'event_type' => LabOrderEvent::TYPE_ORDERED,
            'to_status' => 'ordered',
            'notes' => 'Ordered.',
            'created_at' => now(),
        ];
    }
}
