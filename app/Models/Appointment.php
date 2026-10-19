<?php

namespace App\Models;

use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'appointment_no',
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'cancelled_at' => 'datetime',
        ];
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
}
