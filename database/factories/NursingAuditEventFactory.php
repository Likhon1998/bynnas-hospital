<?php

namespace Database\Factories;

use App\Models\NursingAuditEvent;
use App\Models\NursingObservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NursingAuditEvent>
 */
class NursingAuditEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => null,
            'ward_id' => null,
            'actor_user_id' => null,
            'auditable_type' => (new NursingObservation)->getMorphClass(),
            'auditable_id' => 1,
            'action' => NursingAuditEvent::ACTION_RECORDED,
            'summary' => 'Observation recorded.',
            'meta' => null,
            'created_at' => now(),
        ];
    }
}
