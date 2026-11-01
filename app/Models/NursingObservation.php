<?php

namespace App\Models;

use Database\Factories\NursingObservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'patient_id',
    'bed_id',
    'ward_id',
    'recorded_by',
    'recorded_at',
    'observation_type',
    'blood_pressure',
    'temperature',
    'pulse',
    'oxygen_saturation',
    'weight',
    'pain_score',
    'notes',
])]
class NursingObservation extends Model
{
    /** @use HasFactory<NursingObservationFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const TYPES = [
        'vitals' => 'Vital signs',
        'general' => 'General observation',
        'fluid' => 'Fluid monitoring',
        'wound' => 'Wound care',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'temperature' => 'decimal:1',
            'weight' => 'decimal:1',
            'pulse' => 'integer',
            'oxygen_saturation' => 'integer',
            'pain_score' => 'integer',
        ];
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->observation_type] ?? ucfirst((string) $this->observation_type);
    }

    public function hasVitals(): bool
    {
        return $this->blood_pressure !== null
            || $this->temperature !== null
            || $this->pulse !== null
            || $this->oxygen_saturation !== null;
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
