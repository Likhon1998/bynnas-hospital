<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'patient_code',
    'full_name',
    'phone',
    'secondary_phone',
    'email',
    'nid_number',
    'date_of_birth',
    'gender',
    'blood_group',
    'marital_status',
    'occupation',
    'guardian_name',
    'address',
    'district',
    'emergency_contact_name',
    'emergency_contact_relation',
    'emergency_contact_phone',
    'is_active',
    'care_flags',
    'allergies',
    'notes',
])]
#[Hidden([
    'phone',
    'secondary_phone',
    'email',
    'nid_number',
    'date_of_birth',
    'gender',
    'blood_group',
    'marital_status',
    'occupation',
    'guardian_name',
    'address',
    'district',
    'emergency_contact_name',
    'emergency_contact_relation',
    'emergency_contact_phone',
    'allergies',
    'notes',
    'user_id',
])]
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const CARE_FLAGS = [
        'isolation' => 'Isolation precautions',
        'fall_risk' => 'Fall risk',
        'diabetic' => 'Diabetic',
        'allergy_alert' => 'Allergy alert',
        'mobility_assist' => 'Mobility assist',
        'priority' => 'Priority / VIP',
    ];

    /**
     * @var list<string>
     */
    public const BLOOD_GROUPS = [
        'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown',
    ];

    /**
     * @var array<string, string>
     */
    public const MARITAL_STATUSES = [
        'single' => 'Single',
        'married' => 'Married',
        'divorced' => 'Divorced',
        'widowed' => 'Widowed',
        'other' => 'Other',
    ];

    /**
     * @var array<string, string>
     */
    public const EMERGENCY_RELATIONS = [
        'spouse' => 'Spouse',
        'parent' => 'Parent',
        'child' => 'Child',
        'sibling' => 'Sibling',
        'guardian' => 'Guardian',
        'friend' => 'Friend',
        'other' => 'Other',
    ];

    /**
     * @return array<string, string>
     */
    protected static function booted(): void
    {
        static::creating(function (Patient $patient): void {
            if (blank($patient->patient_code)) {
                $patient->patient_code = static::nextPatientCode();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'care_flags' => 'array',
        ];
    }

    public static function nextPatientCode(): string
    {
        $stamp = 'PAT-'.now()->format('Ymd').'-';

        $last = static::query()
            ->where('patient_code', 'like', $stamp.'%')
            ->orderByDesc('patient_code')
            ->value('patient_code');

        $sequence = $last ? ((int) substr((string) $last, -4)) + 1 : 1;

        return $stamp.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function ageLabel(): string
    {
        if ($this->date_of_birth === null) {
            return '—';
        }

        return (string) $this->date_of_birth->age.' yrs';
    }

    /**
     * @return list<string>
     */
    public function careFlagLabels(): array
    {
        return collect($this->care_flags ?? [])
            ->map(fn (string $flag): string => self::CARE_FLAGS[$flag] ?? $flag)
            ->values()
            ->all();
    }

    public function allergyLabel(): string
    {
        if (filled($this->allergies)) {
            return (string) $this->allergies;
        }

        if (in_array('allergy_alert', $this->care_flags ?? [], true)) {
            return 'Allergy alert on file';
        }

        return 'None recorded';
    }

    public function maritalStatusLabel(): string
    {
        return self::MARITAL_STATUSES[$this->marital_status] ?? ($this->marital_status ?: '—');
    }

    public function emergencyRelationLabel(): string
    {
        return self::EMERGENCY_RELATIONS[$this->emergency_contact_relation] ?? ($this->emergency_contact_relation ?: '—');
    }

    public function currentDiagnosis(?string $admissionReason = null): ?string
    {
        $diagnosis = $this->latestClinicalVisit?->diagnosis;

        return filled($diagnosis) ? $diagnosis : $admissionReason;
    }

    public function latestClinicalVisit(): HasOne
    {
        return $this->hasOne(Visit::class)->ofMany(
            ['completed_at' => 'max', 'id' => 'max'],
            function (Builder $query): void {
                $query->where('status', 'completed')->whereNotNull('diagnosis');
            },
        );
    }

    /**
     * @param  Builder<Patient>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class)->orderByDesc('arrived_at')->orderByDesc('id');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function bed(): HasOne
    {
        return $this->hasOne(Bed::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class)->orderByDesc('admitted_at')->orderByDesc('id');
    }

    public function currentAdmission(): HasOne
    {
        return $this->hasOne(Admission::class)->ofMany(
            ['id' => 'max'],
            fn (Builder $query) => $query->where('status', '!=', 'discharged'),
        );
    }

    public function nursingObservations(): HasMany
    {
        return $this->hasMany(NursingObservation::class)->orderByDesc('recorded_at')->orderByDesc('id');
    }

    public function nursingTasks(): HasMany
    {
        return $this->hasMany(NursingTask::class)->orderBy('due_at')->orderBy('id');
    }

    public function medicationAdministrations(): HasMany
    {
        return $this->hasMany(MedicationAdministration::class)->orderByDesc('administered_at')->orderByDesc('id');
    }

    public function nursingAuditEvents(): HasMany
    {
        return $this->hasMany(NursingAuditEvent::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class)->orderByDesc('created_at')->orderByDesc('id');
    }
}
