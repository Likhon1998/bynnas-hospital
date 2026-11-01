<?php

namespace App\Models;

use Database\Factories\VisitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'visit_no',
    'patient_id',
    'appointment_id',
    'doctor_id',
    'department_id',
    'checked_in_by',
    'status',
    'chief_complaint',
    'arrived_at',
    'triage_started_at',
    'consultation_started_at',
    'completed_at',
    'blood_pressure',
    'temperature',
    'pulse',
    'oxygen_saturation',
    'weight',
    'pain_score',
    'vitals_recorded_at',
    'vitals_recorded_by',
    'symptoms',
    'examination_notes',
    'diagnosis',
    'treatment_plan',
    'prescriptions',
    'follow_up_instructions',
    'clinically_recorded_at',
    'clinically_recorded_by',
])]
#[Hidden([
    'symptoms',
    'examination_notes',
    'diagnosis',
    'treatment_plan',
    'prescriptions',
    'follow_up_instructions',
])]
class Visit extends Model
{
    /** @use HasFactory<VisitFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'waiting' => 'Waiting',
        'triage' => 'Triage',
        'consultation' => 'Consultation',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'arrived_at' => 'datetime',
            'triage_started_at' => 'datetime',
            'consultation_started_at' => 'datetime',
            'completed_at' => 'datetime',
            'vitals_recorded_at' => 'datetime',
            'clinically_recorded_at' => 'datetime',
            'temperature' => 'decimal:1',
            'weight' => 'decimal:1',
            'pulse' => 'integer',
            'oxygen_saturation' => 'integer',
            'pain_score' => 'integer',
        ];
    }

    public static function nextNumber(): string
    {
        $stamp = 'VIS-'.now()->format('Ymd').'-';

        $last = static::query()
            ->where('visit_no', 'like', $stamp.'%')
            ->lockForUpdate()
            ->orderByDesc('visit_no')
            ->value('visit_no');

        $sequence = $last ? ((int) substr((string) $last, -3)) + 1 : 1;

        return $stamp.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['completed', 'cancelled'], true);
    }

    public function hasVitals(): bool
    {
        return $this->vitals_recorded_at !== null;
    }

    /**
     * @param  Builder<Visit>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * @param  Builder<Visit>  $query
     */
    public function scopeToday(Builder $query): void
    {
        $query->whereDate('arrived_at', now()->toDateString());
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function vitalsRecordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vitals_recorded_by');
    }

    public function clinicallyRecordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'clinically_recorded_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(VisitEvent::class)->orderBy('created_at')->orderBy('id');
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class)->orderByDesc('created_at')->orderByDesc('id');
    }
}
