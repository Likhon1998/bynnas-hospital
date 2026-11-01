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
    'notes',
    'entered_by',
    'verified_by',
    'verified_at',
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
            'verified_at' => 'datetime',
        ];
    }

    public function displayValue(): string
    {
        $unit = filled($this->unit) ? ' '.$this->unit : '';

        return $this->value.$unit;
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
