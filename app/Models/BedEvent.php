<?php

namespace App\Models;

use Database\Factories\BedEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bed_id',
    'patient_id',
    'actor_user_id',
    'event_type',
    'from_status',
    'to_status',
    'related_bed_id',
    'notes',
    'meta',
    'created_at',
])]
class BedEvent extends Model
{
    /** @use HasFactory<BedEventFactory> */
    use HasFactory;

    public $timestamps = false;

    public const TYPE_ADMITTED = 'admitted';

    public const TYPE_DISCHARGED = 'discharged';

    public const TYPE_CLEANING_STARTED = 'cleaning_started';

    public const TYPE_INSPECTION_PASSED = 'inspection_passed';

    public const TYPE_INSPECTION_FAILED = 'inspection_failed';

    public const TYPE_RESERVED = 'reserved';

    public const TYPE_RESERVATION_CANCELLED = 'reservation_cancelled';

    public const TYPE_RESERVATION_EXPIRED = 'reservation_expired';

    public const TYPE_TRANSFERRED_OUT = 'transferred_out';

    public const TYPE_TRANSFERRED_IN = 'transferred_in';

    public const TYPE_MAINTENANCE_STARTED = 'maintenance_started';

    public const TYPE_MAINTENANCE_ENDED = 'maintenance_ended';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function relatedBed(): BelongsTo
    {
        return $this->belongsTo(Bed::class, 'related_bed_id');
    }
}
