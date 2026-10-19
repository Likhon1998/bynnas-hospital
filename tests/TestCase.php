<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  list<string>  $permissions
     */
    protected function createStaffUser(array $permissions = [], string $role = 'hospital_admin'): User
    {
        $user = User::factory()->create();

        $roleId = DB::table('roles')->where('name', $role)->value('id');

        if (! $roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => $role,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($permissions as $permission) {
            $permissionId = DB::table('permissions')->where('name', $permission)->value('id');

            if (! $permissionId) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        }

        DB::table('model_has_roles')->updateOrInsert([
            'role_id' => $roleId,
            'model_id' => $user->id,
            'model_type' => $user->getMorphClass(),
        ]);

        return $user->fresh();
    }
}
