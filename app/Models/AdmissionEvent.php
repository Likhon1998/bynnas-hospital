<?php

namespace App\Models;

use Database\Factories\AdmissionEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'admission_id',
    'patient_id',
    'actor_user_id',
    'event_type',
    'from_status',
    'to_status',
    'notes',
    'meta',
    'created_at',
])]
class AdmissionEvent extends Model
{
    /** @use HasFactory<AdmissionEventFactory> */
    use HasFactory;

    public $timestamps = false;

    public const TYPE_ADMITTED = 'admitted';

    public const TYPE_TRANSFERRED = 'transferred';

    public const TYPE_DISCHARGE_AUTHORIZED = 'discharge_authorized';

    public const TYPE_DISCHARGED = 'discharged';

    /**
     * @var array<string, string>
     */
    public const LABELS = [
        self::TYPE_ADMITTED => 'Admitted',
        self::TYPE_TRANSFERRED => 'Transferred',
        self::TYPE_DISCHARGE_AUTHORIZED => 'Discharge authorized',
        self::TYPE_DISCHARGED => 'Discharged',
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

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
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
