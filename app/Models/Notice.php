<?php

namespace App\Models;

use Database\Factories\NoticeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'body',
    'published_on',
    'expires_on',
    'is_urgent',
    'is_active',
    'sort_order',
])]
class Notice extends Model
{
    /** @use HasFactory<NoticeFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_on' => 'date',
            'expires_on' => 'date',
            'is_urgent' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<Notice>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Notice>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $today = now()->toDateString();

        $query->active()
            ->whereDate('published_on', '<=', $today)
            ->where(function (Builder $builder) use ($today): void {
                $builder->whereNull('expires_on')
                    ->orWhereDate('expires_on', '>=', $today);
            });
    }

    /**
     * Active notices that have already ended (kept for the public archive).
     *
     * @param  Builder<Notice>  $query
     */
    public function scopePrevious(Builder $query): void
    {
        $today = now()->toDateString();

        $query->active()
            ->whereDate('published_on', '<=', $today)
            ->whereNotNull('expires_on')
            ->whereDate('expires_on', '<', $today);
    }
}
