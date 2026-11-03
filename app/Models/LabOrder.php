<?php

namespace App\Models;

use Database\Factories\LabOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'order_no',
    'patient_id',
    'visit_id',
    'appointment_id',
    'lab_test_id',
    'ordered_by',
    'clinical_notes',
    'sample_id',
    'barcode',
    'status',
    'is_critical',
    'collected_at',
    'collected_by',
    'processing_at',
    'processing_by',
    'submitted_for_verification_at',
    'submitted_for_verification_by',
    'completed_at',
    'cancelled_at',
    'cancelled_by',
    'cancellation_reason',
])]
class LabOrder extends Model
{
    /** @use HasFactory<LabOrderFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'ordered' => 'Ordered',
        'collected' => 'Collected',
        'processing' => 'Processing',
        'pending_verification' => 'Pending verification',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_critical' => 'boolean',
            'collected_at' => 'datetime',
            'processing_at' => 'datetime',
            'submitted_for_verification_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public static function nextNumber(): string
    {
        $stamp = 'LAB-'.now()->format('Ymd').'-';

        $last = static::query()
            ->where('order_no', 'like', $stamp.'%')
            ->lockForUpdate()
            ->orderByDesc('order_no')
            ->value('order_no');

        $sequence = $last ? ((int) substr((string) $last, -3)) + 1 : 1;

        return $stamp.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    public static function nextSampleId(): string
    {
        $stamp = 'SMP-'.now()->format('Ymd').'-';

        $last = static::query()
            ->where('sample_id', 'like', $stamp.'%')
            ->lockForUpdate()
            ->orderByDesc('sample_id')
            ->value('sample_id');

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

    public function canCollect(): bool
    {
        return $this->status === 'ordered';
    }

    public function canProcess(): bool
    {
        return $this->status === 'collected';
    }

    public function canRecordResult(): bool
    {
        return in_array($this->status, ['collected', 'processing'], true);
    }

    public function canVerify(): bool
    {
        return $this->status === 'pending_verification' && $this->result !== null && ! $this->result->isVerified();
    }

    public function canReturnForCorrection(): bool
    {
        return $this->status === 'pending_verification' && $this->result !== null && ! $this->result->isVerified();
    }

    public function isImagingStudy(): bool
    {
        $category = $this->test?->category;

        return in_array($category, ['radiology', 'imaging'], true);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['ordered', 'collected', 'processing', 'pending_verification'], true);
    }

    /**
     * @param  Builder<LabOrder>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * @param  Builder<LabOrder>  $query
     */
    public function scopeCritical(Builder $query): void
    {
        $query->where('is_critical', true)->where('status', 'completed');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function processingBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processing_by');
    }

    public function submittedForVerificationBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_for_verification_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function result(): HasOne
    {
        return $this->hasOne(LabResult::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(LabOrderEvent::class)->orderBy('created_at')->orderBy('id');
    }
}
