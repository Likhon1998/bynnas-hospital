<?php

namespace App\Models;

use App\Support\PublicPhoto;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'department_id',
    'title',
    'slug',
    'summary',
    'body',
    'icon',
    'image',
    'is_featured',
    'is_active',
    'sort_order',
])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function publicImage(): string
    {
        return PublicPhoto::usable($this->image) ?? PublicPhoto::campus(max(0, (int) $this->id - 1));
    }

    public function publicIcon(): string
    {
        $key = Str::lower(trim($this->slug.' '.$this->icon.' '.$this->title));

        return match (true) {
            str_contains($key, 'emergency'), str_contains($key, 'ambulance') => 'ambulance',
            str_contains($key, 'icu'), str_contains($key, 'ccu'), str_contains($key, 'nicu') => 'pulse',
            str_contains($key, 'lab'), str_contains($key, 'blood') => 'flask',
            str_contains($key, 'pharmacy'), str_contains($key, 'pill') => 'pill',
            str_contains($key, 'physio') => 'activity',
            default => 'hospital',
        };
    }

    public function isUrgent(): bool
    {
        $key = Str::lower(trim($this->slug.' '.$this->title));

        return str_contains($key, 'emergency') || str_contains($key, 'ambulance');
    }
}
