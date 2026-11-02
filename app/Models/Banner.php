<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'subtitle',
    'image',
    'cta_label',
    'cta_url',
    'placement',
    'is_active',
    'sort_order',
    'starts_at',
    'ends_at',
])]
class Banner extends Model
{
    /**
     * @return array<string, string>
     */
    public static function placements(): array
    {
        return [
            'hero' => 'Homepage hero',
            'emergency' => 'Top emergency bar',
            'promo' => 'Footer promo band',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Banner>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(function (Builder $builder): void {
                $builder->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $builder): void {
                $builder->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    public function destination(): string
    {
        $url = trim((string) $this->cta_url);

        return match (true) {
            $url === '', $url === '/appointments' => url('/').'#appointment',
            $url === '/health-packages' => route('packages'),
            $url === '/doctors' => route('doctors'),
            $url === '/emergency' => route('emergency'),
            default => $url,
        };
    }
}
