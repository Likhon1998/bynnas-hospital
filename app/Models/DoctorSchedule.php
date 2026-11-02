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
     * @var array<string, string>
     */
    public const WEEKDAYS = [
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
    ];

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

    /**
     * @return array<string, array{label: string, open: bool, start_time: string, end_time: string, break_start: string, break_end: string, slot_duration_minutes: int}>
     */
    public static function formStateFor(Doctor $doctor): array
    {
        $hours = [];
        $old = old('days');

        foreach (self::WEEKDAYS as $weekday => $label) {
            $session = $doctor->doctorSchedules->firstWhere('weekday', $weekday);
            $posted = is_array($old) ? ($old[$weekday] ?? []) : [];

            $hours[$weekday] = [
                'label' => $label,
                'open' => array_key_exists('open', $posted)
                    ? (bool) $posted['open']
                    : ($session?->is_active ?? false),
                'start_time' => $posted['start_time'] ?? self::clock($session?->start_time, '09:00'),
                'end_time' => $posted['end_time'] ?? self::clock($session?->end_time, '13:00'),
                'break_start' => $posted['break_start'] ?? self::clock($session?->break_start, ''),
                'break_end' => $posted['break_end'] ?? self::clock($session?->break_end, ''),
                'slot_duration_minutes' => (int) ($posted['slot_duration_minutes'] ?? $session?->slot_duration_minutes ?? 30),
            ];
        }

        return $hours;
    }

    private static function clock(mixed $time, string $fallback): string
    {
        $value = substr((string) $time, 0, 5);

        return $value !== '' ? $value : $fallback;
    }
}
