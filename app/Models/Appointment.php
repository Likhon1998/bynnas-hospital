<?php

namespace App\Models;

use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'appointment_no',
    'serial_no',
    'patient_id',
    'doctor_id',
    'department_id',
    'appointment_date',
    'start_time',
    'end_time',
    'reason',
    'status',
    'notes',
    'source',
    'created_by',
    'confirmation_token',
    'booking_key',
    'cancelled_at',
    'cancellation_reason',
])]
#[Hidden([
    'reason',
    'notes',
    'confirmation_token',
    'booking_key',
    'cancellation_reason',
    'patient_id',
    'created_by',
])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No show',
    ];

    /**
     * @var array<string, string>
     */
    public const SOURCES = [
        'web' => 'Website',
        'admin' => 'Desk',
        'emergency' => 'Emergency',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'cancelled_at' => 'datetime',
        ];
    }

    public static function nextNumber(string $prefix = 'APT'): string
    {
        $stamp = $prefix.'-'.now()->format('Ymd').'-';

        $last = static::query()
            ->where('appointment_no', 'like', $stamp.'%')
            ->lockForUpdate()
            ->orderByDesc('appointment_no')
            ->value('appointment_no');

        $sequence = $last ? ((int) substr((string) $last, -3)) + 1 : 1;

        return $stamp.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    public static function nextSerialForDoctor(int $doctorId, string $date): int
    {
        $last = static::query()
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereNotNull('serial_no')
            ->lockForUpdate()
            ->max('serial_no');

        return ((int) $last) + 1;
    }

    public function serialLabel(): string
    {
        return $this->serial_no !== null ? 'S-'.str_pad((string) $this->serial_no, 3, '0', STR_PAD_LEFT) : '—';
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? 'Desk';
    }

    public function timeRangeLabel(): string
    {
        return substr((string) $this->start_time, 0, 5).'–'.substr((string) $this->end_time, 0, 5);
    }

    public function timeInputValue(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function holdsSlot(): bool
    {
        return $this->status !== 'cancelled';
    }

    /**
     * @param  Builder<Appointment>  $query
     */
    public function scopeHoldingSlot(Builder $query): void
    {
        $query->where('status', '!=', 'cancelled');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class)->orderByDesc('id');
    }

    /**
     * @param  Builder<Appointment>  $query
     */
    public function scopeEmergency(Builder $query): void
    {
        $query->where(function (Builder $inner): void {
            $inner->where('source', 'emergency')
                ->orWhereHas('department', function (Builder $department): void {
                    $department->where('slug', 'emergency')
                        ->orWhere('name', 'like', '%Emergency%');
                });
        });
    }

    /**
     * @param  Builder<Appointment>  $query
     */
    public function scopeEmergencyDesk(Builder $query, ?string $date = null): void
    {
        $query->whereDate('appointment_date', $date ?? now()->toDateString())
            ->emergency();
    }
}
