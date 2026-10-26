<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'patient_id',
    'from_bed_id',
    'to_bed_id',
    'transferred_by',
    'reason',
    'created_at',
])]
class BedTransfer extends Model
{
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function fromBed(): BelongsTo
    {
        return $this->belongsTo(Bed::class, 'from_bed_id');
    }

    public function toBed(): BelongsTo
    {
        return $this->belongsTo(Bed::class, 'to_bed_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
