<?php

namespace App\Models;

use Database\Factories\VisitEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'visit_id',
    'patient_id',
    'actor_user_id',
    'event_type',
    'from_status',
    'to_status',
    'notes',
    'meta',
    'created_at',
])]
class VisitEvent extends Model
{
    /** @use HasFactory<VisitEventFactory> */
    use HasFactory;

    public $timestamps = false;

    public const TYPE_CHECKED_IN = 'checked_in';

    public const TYPE_VITALS_RECORDED = 'vitals_recorded';

    public const TYPE_CONSULTATION_STARTED = 'consultation_started';

    public const TYPE_COMPLETED = 'completed';

    public const TYPE_CANCELLED = 'cancelled';

    public const TYPE_LAB_ORDERED = 'lab_ordered';

    public const TYPE_LAB_RESULTED = 'lab_resulted';

    /**
     * @var array<string, string>
     */
    public const LABELS = [
        self::TYPE_CHECKED_IN => 'Checked in',
        self::TYPE_VITALS_RECORDED => 'Vitals recorded',
        self::TYPE_CONSULTATION_STARTED => 'Consultation started',
        self::TYPE_COMPLETED => 'Encounter completed',
        self::TYPE_CANCELLED => 'Visit cancelled',
        self::TYPE_LAB_ORDERED => 'Lab ordered',
        self::TYPE_LAB_RESULTED => 'Lab result verified',
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

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
