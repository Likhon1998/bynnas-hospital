<?php

namespace App\Models;

use Database\Factories\NursingAuditEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'patient_id',
    'ward_id',
    'actor_user_id',
    'auditable_type',
    'auditable_id',
    'action',
    'summary',
    'meta',
    'created_at',
])]
class NursingAuditEvent extends Model
{
    /** @use HasFactory<NursingAuditEventFactory> */
    use HasFactory;

    public $timestamps = false;

    public const ACTION_RECORDED = 'recorded';

    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_ADMINISTERED = 'administered';

    public const ACTION_HANDED_OVER = 'handed_over';

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

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
