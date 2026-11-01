<?php

namespace App\Models;

use Database\Factories\LabOrderEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lab_order_id',
    'patient_id',
    'visit_id',
    'actor_user_id',
    'event_type',
    'from_status',
    'to_status',
    'notes',
    'meta',
    'created_at',
])]
class LabOrderEvent extends Model
{
    /** @use HasFactory<LabOrderEventFactory> */
    use HasFactory;

    public $timestamps = false;

    public const TYPE_ORDERED = 'ordered';

    public const TYPE_COLLECTED = 'collected';

    public const TYPE_PROCESSING = 'processing';

    public const TYPE_COMPLETED = 'completed';

    public const TYPE_CANCELLED = 'cancelled';

    /**
     * @var array<string, string>
     */
    public const LABELS = [
        self::TYPE_ORDERED => 'Ordered',
        self::TYPE_COLLECTED => 'Sample collected',
        self::TYPE_PROCESSING => 'Processing',
        self::TYPE_COMPLETED => 'Result verified',
        self::TYPE_CANCELLED => 'Cancelled',
    ];

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

    public function label(): string
    {
        return self::LABELS[$this->event_type] ?? ucfirst(str_replace('_', ' ', (string) $this->event_type));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
