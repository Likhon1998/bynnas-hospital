<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Role extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    public function displayName(): string
    {
        return Str::of($this->name)->replace('_', ' ')->title()->toString();
    }

    public function isProtected(): bool
    {
        return $this->name === 'super_admin';
    }

    public function usersCount(): int
    {
        return (int) DB::table('model_has_roles')
            ->where('role_id', $this->id)
            ->where('model_type', (new User)->getMorphClass())
            ->count();
    }

    /**
     * @param  list<int>  $permissionIds
     */
    public function syncPermissionIds(array $permissionIds): void
    {
        $permissionIds = array_values(array_unique(array_map('intval', $permissionIds)));

        if ($this->isProtected() && $permissionIds === []) {
            $usersManageId = Permission::query()->where('name', 'users.manage')->value('id');

            if ($usersManageId) {
                $permissionIds = [(int) $usersManageId];
            }
        }

        DB::table('role_has_permissions')->where('role_id', $this->id)->delete();

        foreach ($permissionIds as $permissionId) {
            DB::table('role_has_permissions')->insert([
                'role_id' => $this->id,
                'permission_id' => $permissionId,
            ]);
        }
    }
}
