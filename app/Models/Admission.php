<?php

namespace App\Models;

use Database\Factories\AdmissionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'admission_no',
    'patient_id',
    'source',
    'appointment_id',
    'visit_id',
    'attending_doctor_id',
    'department_id',
    'ward_id',
    'bed_id',
    'admission_reason',
    'status',
    'admitted_by',
    'admitted_at',
    'authorized_by',
    'authorized_at',
    'discharged_by',
    'discharged_at',
    'discharge_summary',
    'discharge_medications',
    'follow_up_plan',
    'outstanding_bills',
    'billing_reviewed',
])]
class Admission extends Model
{
    /** @use HasFactory<AdmissionFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const SOURCES = [
        'appointment' => 'Appointment',
        'emergency' => 'Emergency',
        'direct' => 'Direct admission',
    ];

    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'admitted' => 'Admitted',
        'transferred' => 'Transferred',
        'discharge_authorized' => 'Discharge authorized',
        'discharged' => 'Discharged',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'admitted_at' => 'datetime',
            'authorized_at' => 'datetime',
            'discharged_at' => 'datetime',
            'outstanding_bills' => 'array',
            'billing_reviewed' => 'boolean',
        ];
    }

    public static function nextNumber(): string
    {
        $stamp = 'ADM-'.now()->format('Ymd').'-';

        $last = static::query()
            ->where('admission_no', 'like', $stamp.'%')
            ->lockForUpdate()
            ->orderByDesc('admission_no')
            ->value('admission_no');

        $sequence = $last ? ((int) substr((string) $last, -3)) + 1 : 1;

        return $stamp.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst((string) $this->source);
    }

    public function isOpen(): bool
    {
        return $this->status !== 'discharged';
    }

    public function isAuthorizedForRelease(): bool
    {
        return $this->status === 'discharge_authorized'
            && $this->authorized_at !== null
            && filled($this->discharge_summary);
    }

    /**
     * @param  Builder<Admission>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', '!=', 'discharged');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function attendingDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'attending_doctor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function admittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admitted_by');
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function dischargedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'discharged_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AdmissionEvent::class)->orderBy('created_at')->orderBy('id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(BedTransfer::class)->orderBy('created_at')->orderBy('id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class)->orderByDesc('bill_date')->orderByDesc('id');
    }
}
