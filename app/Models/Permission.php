<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Permission extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions');
    }

    public function displayName(): string
    {
        return Str::of($this->name)->replace('.', ' · ')->replace('_', ' ')->title()->toString();
    }

    public function group(): string
    {
        $parts = explode('.', (string) $this->name, 2);

        return Str::of($parts[0] ?? 'other')->replace('_', ' ')->title()->toString();
    }
}
