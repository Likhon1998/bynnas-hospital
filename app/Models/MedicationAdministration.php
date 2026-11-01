<?php

namespace App\Models;

use Database\Factories\MedicationAdministrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'patient_id',
    'bed_id',
    'ward_id',
    'medication_name',
    'dose',
    'route',
    'status',
    'scheduled_at',
    'administered_at',
    'administered_by',
    'notes',
])]
class MedicationAdministration extends Model
{
    /** @use HasFactory<MedicationAdministrationFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const ROUTES = [
        'oral' => 'Oral',
        'iv' => 'IV',
        'im' => 'IM',
        'subcutaneous' => 'Subcutaneous',
        'topical' => 'Topical',
        'other' => 'Other',
    ];

    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'given' => 'Given',
        'withheld' => 'Withheld',
        'missed' => 'Missed',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'administered_at' => 'datetime',
        ];
    }

    public function routeLabel(): string
    {
        return self::ROUTES[$this->route] ?? ucfirst((string) $this->route);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
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

    public function administeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administered_by');
    }
}
