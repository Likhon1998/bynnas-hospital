<?php

namespace App\Models;

use Database\Factories\NursingTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'patient_id',
    'bed_id',
    'ward_id',
    'created_by',
    'assigned_to',
    'task_type',
    'title',
    'instructions',
    'status',
    'due_at',
    'started_at',
    'started_by',
    'completed_at',
    'completed_by',
])]
class NursingTask extends Model
{
    /** @use HasFactory<NursingTaskFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const TYPES = [
        'observation' => 'Observations',
        'wound_care' => 'Wound care',
        'fluid_monitoring' => 'Fluid monitoring',
        'medication' => 'Medication',
        'other' => 'Other prescribed care',
    ];

    /**
     * @var array<string, string>
     */
    public const STATUSES = [
        'pending' => 'Pending',
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'overdue' => 'Overdue',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->task_type] ?? ucfirst(str_replace('_', ' ', (string) $this->task_type));
    }

    public function isOpen(): bool
    {
        return $this->status !== 'completed';
    }

    public function isOverdue(): bool
    {
        return $this->isOpen()
            && $this->due_at !== null
            && $this->due_at->isPast();
    }

    public function displayStatus(): string
    {
        return $this->isOverdue() ? 'overdue' : $this->status;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->displayStatus()] ?? ucfirst(str_replace('_', ' ', $this->displayStatus()));
    }

    /**
     * @param  Builder<NursingTask>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', '!=', 'completed');
    }

    /**
     * @param  Builder<NursingTask>  $query
     */
    public function scopeOverdue(Builder $query): void
    {
        $query->open()
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    /**
     * @param  Builder<NursingTask>  $query
     */
    public function scopeDue(Builder $query): void
    {
        $query->open()
            ->where(function (Builder $builder): void {
                $builder->whereNull('due_at')
                    ->orWhere('due_at', '<=', now()->endOfDay());
            });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
