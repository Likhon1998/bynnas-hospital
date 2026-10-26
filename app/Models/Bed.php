<?php

namespace App\Models;

use App\Support\BedLifecycle;
use Database\Factories\BedFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'ward_id',
    'patient_id',
    'bed_code',
    'status',
    'admitted_at',
    'status_changed_at',
    'turnover_hours',
    'is_maintenance',
    'maintenance_reason',
    'maintenance_started_at',
    'reserved_patient_id',
    'reserved_until',
    'reservation_note',
    'reserved_by',
    'reserved_at',
    'lock_version',
])]
class Bed extends Model
{
    /** @use HasFactory<BedFactory> */
    use HasFactory;

    public const STATUSES = ['available', 'occupied', 'dirty', 'cleaning'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'admitted_at' => 'datetime',
            'status_changed_at' => 'datetime',
            'turnover_hours' => 'decimal:1',
            'is_maintenance' => 'boolean',
            'maintenance_started_at' => 'datetime',
            'reserved_until' => 'datetime',
            'reserved_at' => 'datetime',
            'lock_version' => 'integer',
        ];
    }

    /**
     * @param  Builder<Bed>  $query
     */
    public function scopeStatus(Builder $query, string $status): void
    {
        $query->where('status', $status);
    }

    /**
     * @param  Builder<Bed>  $query
     */
    public function scopeOccupied(Builder $query): void
    {
        $query->where('status', 'occupied');
    }

    /**
     * @param  Builder<Bed>  $query
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('status', 'available');
    }

    /**
     * @param  Builder<Bed>  $query
     */
    public function scopeInTurnaround(Builder $query): void
    {
        $query->whereIn('status', ['dirty', 'cleaning']);
    }

    /**
     * @param  Builder<Bed>  $query
     */
    public function scopeAdmittable(Builder $query, ?int $patientId = null): void
    {
        $query->where('status', 'available')
            ->where('is_maintenance', false)
            ->where(function (Builder $builder) use ($patientId): void {
                $builder->where(function (Builder $open): void {
                    $open->whereNull('reserved_patient_id')
                        ->orWhere(function (Builder $expired): void {
                            $expired->whereNotNull('reserved_until')
                                ->where('reserved_until', '<=', now());
                        });
                });

                if ($patientId !== null) {
                    $builder->orWhere(function (Builder $mine) use ($patientId): void {
                        $mine->where('reserved_patient_id', $patientId)
                            ->where(function (Builder $active): void {
                                $active->whereNull('reserved_until')
                                    ->orWhere('reserved_until', '>', now());
                            });
                    });
                }
            });
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function reservedPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'reserved_patient_id');
    }

    public function reservedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(BedEvent::class)->orderByDesc('created_at');
    }

    /**
     * @return list<string>
     */
    public function nextStatuses(): array
    {
        return app(BedLifecycle::class)->allowedNextStatuses($this);
    }

    public function hasActiveReservation(): bool
    {
        if ($this->reserved_patient_id === null) {
            return false;
        }

        if ($this->reserved_until !== null && $this->reserved_until->isPast()) {
            return false;
        }

        return true;
    }

    public function reservationIsExpired(): bool
    {
        return $this->reserved_patient_id !== null
            && $this->reserved_until !== null
            && $this->reserved_until->isPast();
    }

    /**
     * Admission eligibility is layered on top of the cleaning state machine.
     * Status must remain `available`; maintenance and reservations are separate.
     */
    public function isAdmittable(?int $patientId = null): bool
    {
        if ($this->status !== 'available' || $this->is_maintenance) {
            return false;
        }

        if ($this->reservationIsExpired() || ! $this->hasActiveReservation()) {
            return true;
        }

        return $patientId !== null && $this->reserved_patient_id === $patientId;
    }

    /**
     * @return list<string>
     */
    public function admissionBlockers(?int $patientId = null): array
    {
        $blockers = [];

        if ($this->status !== 'available') {
            $blockers[] = 'Bed must be available (current: '.$this->status.').';
        }

        if ($this->is_maintenance) {
            $blockers[] = 'Bed is under maintenance'.($this->maintenance_reason ? ': '.$this->maintenance_reason : '.');
        }

        if ($this->hasActiveReservation() && ($patientId === null || $this->reserved_patient_id !== $patientId)) {
            $name = $this->reservedPatient?->full_name ?: 'another patient';
            $blockers[] = 'Bed is reserved for '.$name.'.';
        }

        return $blockers;
    }

    public function operationalLabel(): ?string
    {
        if ($this->is_maintenance) {
            return 'Maintenance';
        }

        if ($this->hasActiveReservation()) {
            return 'Reserved';
        }

        return null;
    }

    public function lengthOfStayLabel(): string
    {
        if ($this->status !== 'occupied' || $this->admitted_at === null) {
            return '—';
        }

        $hours = max(0, (int) $this->admitted_at->diffInHours(Carbon::now()));

        if ($hours < 1) {
            $minutes = max(1, (int) $this->admitted_at->diffInMinutes(Carbon::now()));

            return $minutes.'m';
        }

        if ($hours < 24) {
            return $hours.'h';
        }

        $days = intdiv($hours, 24);

        return $days.'d '.($hours % 24).'h';
    }

    public function statusAgeLabel(): string
    {
        $since = $this->status_changed_at ?? $this->updated_at;

        if ($since === null) {
            return '—';
        }

        $hours = max(0, (int) $since->diffInHours(Carbon::now()));

        if ($hours < 1) {
            return max(1, (int) $since->diffInMinutes(Carbon::now())).'m';
        }

        if ($hours < 24) {
            return $hours.'h';
        }

        return intdiv($hours, 24).'d '.($hours % 24).'h';
    }

    public function occupancyIsValid(): bool
    {
        if ($this->status === 'occupied') {
            return $this->patient_id !== null && $this->admitted_at !== null;
        }

        return $this->patient_id === null;
    }
}
