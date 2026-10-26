<?php

namespace App\Support;

use App\Models\Bed;
use App\Models\BedEvent;
use App\Models\User;

class BedAuditor
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function record(
        Bed $bed,
        string $eventType,
        ?User $actor = null,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?int $patientId = null,
        ?int $relatedBedId = null,
        ?string $notes = null,
        array $meta = [],
    ): BedEvent {
        return BedEvent::query()->create([
            'bed_id' => $bed->id,
            'patient_id' => $patientId ?? $bed->patient_id,
            'actor_user_id' => $actor?->id,
            'event_type' => $eventType,
            'from_status' => $fromStatus ?? $bed->status,
            'to_status' => $toStatus,
            'related_bed_id' => $relatedBedId,
            'notes' => $notes,
            'meta' => $meta === [] ? null : $meta,
            'created_at' => now(),
        ]);
    }
}
