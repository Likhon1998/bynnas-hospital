<?php

namespace App\Models;

use Database\Factories\LabResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lab_order_id',
    'patient_id',
    'visit_id',
    'value',
    'numeric_value',
    'unit',
    'reference_range',
    'is_critical',
    'is_abnormal',
    'notes',
    'findings',
    'entered_by',
    'verified_by',
    'verified_at',
    'signature_name',
    'signature_title',
    'published_at',
])]
class LabResult extends Model
{
    /** @use HasFactory<LabResultFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'numeric_value' => 'decimal:3',
            'is_critical' => 'boolean',
            'is_abnormal' => 'boolean',
            'verified_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function displayValue(): string
    {
        $unit = filled($this->unit) ? ' '.$this->unit : '';

        return $this->value.$unit;
    }

    public function isVerified(): bool
    {
        return $this->verified_by !== null && $this->verified_at !== null;
    }

    public function isPreliminary(): bool
    {
        return ! $this->isVerified();
    }

    public function isPublished(): bool
    {
        return $this->isVerified() && $this->published_at !== null;
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

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
