<?php

namespace App\Models;

use Database\Factories\DoctorScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'doctor_id',
    'weekday',
    'start_time',
    'end_time',
    'break_start',
    'break_end',
    'slot_duration_minutes',
    'is_active',
])]
class DoctorSchedule extends Model
{
    /** @use HasFactory<DoctorScheduleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slot_duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<DoctorSchedule>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
