<?php

namespace App\Models;

use Database\Factories\WorkItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'work_key',
    'desk',
    'kind',
    'title',
    'detail',
    'href',
    'tone',
    'source_type',
    'source_id',
    'patient_id',
    'audience_user_id',
    'audience_doctor_id',
    'is_critical',
    'is_notification',
    'status',
    'read_at',
    'read_by',
    'acknowledged_at',
    'acknowledged_by',
    'escalated_at',
    'escalated_by',
    'completed_at',
    'due_at',
    'meta',
])]
class WorkItem extends Model
{
    /** @use HasFactory<WorkItemFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const DESKS = [
        'reception' => 'Reception',
        'nursing' => 'Nursing',
        'doctor' => 'Doctor',
        'beds' => 'Bed management',
        'admin' => 'Administration',
        'lab' => 'Laboratory',
        'content' => 'Website',
    ];

    /**
     * @var array<string, string>
     */
    public const KINDS = [
        'pending_web' => 'Website requests',
        'arrival' => "Today's arrivals",
        'waiting' => 'Waiting patients',
        'no_show' => 'No-shows',
        'assigned' => 'Assigned patients',
        'overdue_observation' => 'Overdue observations',
        'pending_task' => 'Pending care tasks',
        'critical' => 'Critical alerts',
        'consult' => 'Waiting consultations',
        'result' => 'Outstanding results',
        'follow_up' => 'Follow-ups',
        'ready' => 'Ready beds',
        'pending_admission' => 'Pending admissions',
        'cleaning' => 'Cleaning',
        'maintenance' => 'Maintenance',
        'unpaid' => 'Unpaid bills',
        'exception' => 'Operational exceptions',
        'escalated' => 'Escalated alerts',
        'message' => 'Unread messages',
        'lab_work' => 'Lab work',
    ];

    /**
     * @var list<string>
     */
    public const ACK_COMPLETES = ['no_show', 'follow_up'];

    /**
     * @var array<string, list<string>>
     */
    public const QUEUE_ORDER = [
        'reception' => ['pending_web', 'arrival', 'waiting', 'no_show'],
        'nursing' => ['assigned', 'overdue_observation', 'pending_task', 'critical'],
        'doctor' => ['consult', 'assigned', 'result', 'follow_up', 'critical'],
        'beds' => ['ready', 'pending_admission', 'cleaning', 'maintenance'],
        'admin' => ['unpaid', 'exception', 'critical', 'escalated'],
        'lab' => ['lab_work'],
        'content' => ['message'],
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_critical' => 'boolean',
            'is_notification' => 'boolean',
            'read_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'escalated_at' => 'datetime',
            'completed_at' => 'datetime',
            'due_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function deskLabel(): string
    {
        return self::DESKS[$this->desk] ?? ucfirst($this->desk);
    }

    public function kindLabel(): string
    {
        return self::KINDS[$this->kind] ?? ucfirst(str_replace('_', ' ', $this->kind));
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function isAcknowledged(): bool
    {
        return $this->acknowledged_at !== null;
    }

    public function isEscalated(): bool
    {
        return $this->escalated_at !== null;
    }

    public function canAcknowledge(): bool
    {
        return $this->isOpen() && ! $this->isAcknowledged();
    }

    public function canEscalate(): bool
    {
        return $this->isOpen() && $this->is_critical && ! $this->isEscalated();
    }

    /**
     * @param  Builder<WorkItem>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', 'open');
    }

    /**
     * @param  Builder<WorkItem>  $query
     */
    public function scopeDone(Builder $query): void
    {
        $query->where('status', 'done');
    }

    /**
     * @param  Builder<WorkItem>  $query
     */
    public function scopeUnread(Builder $query): void
    {
        $query->whereNull('read_at');
    }

    /**
     * @param  Builder<WorkItem>  $query
     */
    public function scopeForDesks(Builder $query, array $desks): void
    {
        $query->whereIn('desk', $desks);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function audienceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audience_user_id');
    }

    public function audienceDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'audience_doctor_id');
    }
}
