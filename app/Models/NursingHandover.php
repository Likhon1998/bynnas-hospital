<?php

namespace App\Models;

use Database\Factories\NursingHandoverFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ward_id',
    'from_user_id',
    'shift',
    'incoming_shift',
    'summary',
    'outstanding_work',
    'meta',
    'handed_over_at',
])]
class NursingHandover extends Model
{
    /** @use HasFactory<NursingHandoverFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    public const SHIFTS = [
        'morning' => 'Morning',
        'evening' => 'Evening',
        'night' => 'Night',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'handed_over_at' => 'datetime',
        ];
    }

    public function shiftLabel(): string
    {
        return self::SHIFTS[$this->shift] ?? ucfirst((string) $this->shift);
    }

    public function incomingShiftLabel(): string
    {
        return self::SHIFTS[$this->incoming_shift] ?? ucfirst((string) $this->incoming_shift);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}
