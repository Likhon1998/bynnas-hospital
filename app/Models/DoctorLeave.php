<?php

namespace App\Models;

use Database\Factories\DoctorLeaveFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'doctor_id',
    'starts_on',
    'ends_on',
    'type',
    'reason',
    'is_active',
    'created_by',
])]
class DoctorLeave extends Model
{
    /** @use HasFactory<DoctorLeaveFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const TYPES = [
        'vacation' => 'Vacation',
        'leave' => 'Leave',
        'training' => 'Training',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<DoctorLeave>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<DoctorLeave>  $query
     */
    public function scopeCovering(Builder $query, string $date): void
    {
        $query->whereDate('starts_on', '<=', $date)
            ->whereDate('ends_on', '>=', $date);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? 'Leave';
    }

    public function absencePhrase(): string
    {
        return match ($this->type) {
            'vacation' => 'on vacation',
            'training' => 'away for training',
            default => 'on leave',
        };
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
