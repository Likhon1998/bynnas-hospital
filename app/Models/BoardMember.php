<?php

namespace App\Models;

use Database\Factories\BoardMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'title',
    'role',
    'role_label',
    'bio',
    'achievements',
    'photo',
    'email',
    'phone',
    'is_active',
    'sort_order',
])]
class BoardMember extends Model
{
    /** @use HasFactory<BoardMemberFactory> */
    use HasFactory;

    public const ROLES = [
        'founder' => 'Founder',
        'co_founder' => 'Co-founder',
        'chairman' => 'Chairman',
        'managing_director' => 'Managing Director',
        'director' => 'Director',
        'advisor' => 'Advisor',
        'other' => 'Leadership',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'achievements' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (BoardMember $member): void {
            if (! filled($member->slug) && filled($member->name)) {
                $member->slug = static::uniqueSlugFor((string) $member->name, $member->id);
            }

            if (! array_key_exists((string) $member->role, self::ROLES)) {
                $member->role = 'director';
            }
        });
    }

    /**
     * @param  Builder<BoardMember>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<BoardMember>  $query
     */
    public function scopeRole(Builder $query, string $role): void
    {
        $query->where('role', $role);
    }

    public function roleName(): string
    {
        return self::ROLES[$this->role] ?? 'Leadership';
    }

    public static function isValidRole(string $role): bool
    {
        return array_key_exists($role, self::ROLES);
    }

    /**
     * @return list<string>
     */
    public function achievementList(): array
    {
        return collect($this->achievements ?? [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    public function achievementsText(): string
    {
        return implode("\n", $this->achievementList());
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];

        $letters = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return $letters !== '' ? $letters : 'BM';
    }

    public static function uniqueSlugFor(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'leader';
        $slug = $base;
        $i = 2;

        while (static::query()
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
