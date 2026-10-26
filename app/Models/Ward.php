<?php

namespace App\Models;

use Database\Factories\WardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'code',
    'type',
    'capacity',
    'is_active',
    'sort_order',
])]
class Ward extends Model
{
    /** @use HasFactory<WardFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<Ward>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function occupiedBeds(): HasMany
    {
        return $this->beds()->where('status', 'occupied');
    }

    public function availableBeds(): HasMany
    {
        return $this->beds()->where('status', 'available');
    }

    public function occupancyPercent(): int
    {
        $total = max(1, $this->beds_count ?? $this->beds()->count());
        $occupied = $this->occupied_beds_count ?? $this->occupiedBeds()->count();

        return (int) round(($occupied / $total) * 100);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'icu' => 'ICU',
            'nicu' => 'NICU',
            'isolation' => 'Isolation',
            'general' => 'General',
            default => ucfirst((string) $this->type),
        };
    }
}
