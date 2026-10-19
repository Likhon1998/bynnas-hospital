<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>|null
     */
    protected ?array $roleNameCache = null;

    /**
     * @var list<string>|null
     */
    protected ?array $permissionNameCache = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function createdAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'created_by');
    }

    public function issuedBills(): HasMany
    {
        return $this->hasMany(Bill::class, 'issued_by');
    }

    public function isStaff(): bool
    {
        return $this->roleNames() !== [];
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roleNames(), true);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return in_array($permission, $this->permissionNames(), true);
    }

    /**
     * @return list<string>
     */
    public function roleNames(): array
    {
        if ($this->roleNameCache !== null) {
            return $this->roleNameCache;
        }

        return $this->roleNameCache = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', $this->getMorphClass())
            ->orderBy('roles.name')
            ->pluck('roles.name')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function permissionNames(): array
    {
        if ($this->permissionNameCache !== null) {
            return $this->permissionNameCache;
        }

        return $this->permissionNameCache = DB::table('model_has_roles')
            ->join('role_has_permissions', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', $this->getMorphClass())
            ->pluck('permissions.name')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $roleIds
     */
    public function syncRoleIds(array $roleIds): void
    {
        DB::table('model_has_roles')
            ->where('model_id', $this->id)
            ->where('model_type', $this->getMorphClass())
            ->delete();

        foreach (array_unique($roleIds) as $roleId) {
            DB::table('model_has_roles')->insert([
                'role_id' => $roleId,
                'model_id' => $this->id,
                'model_type' => $this->getMorphClass(),
            ]);
        }

        $this->roleNameCache = null;
        $this->permissionNameCache = null;
    }
}
