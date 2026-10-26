<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Development/demo accounts only. Safe to re-run.
     */
    public function run(): void
    {
        $roles = [
            'super_admin' => 'Super Admin',
            'hospital_admin' => 'Hospital Admin',
            'receptionist' => 'Receptionist',
            'doctor' => 'Doctor',
            'content_manager' => 'Content Manager',
        ];

        $roleIds = [];

        foreach ($roles as $name => $label) {
            $existing = DB::table('roles')->where('name', $name)->where('guard_name', 'web')->first();

            if ($existing) {
                $roleIds[$name] = $existing->id;

                continue;
            }

            $roleIds[$name] = DB::table('roles')->insertGetId([
                'name' => $name,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $permissions = [
            'appointments.view',
            'appointments.manage',
            'patients.view',
            'patients.manage',
            'doctors.manage',
            'content.manage',
            'billing.manage',
            'settings.manage',
            'users.manage',
        ];

        $permissionIds = [];

        foreach ($permissions as $permission) {
            $existing = DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->first();

            if ($existing) {
                $permissionIds[$permission] = $existing->id;

                continue;
            }

            $permissionIds[$permission] = DB::table('permissions')->insertGetId([
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $rolePermissions = [
            'super_admin' => $permissions,
            'hospital_admin' => [
                'appointments.view',
                'appointments.manage',
                'patients.view',
                'patients.manage',
                'doctors.manage',
                'content.manage',
                'billing.manage',
                'settings.manage',
            ],
            'receptionist' => [
                'appointments.view',
                'appointments.manage',
                'patients.view',
                'patients.manage',
                'billing.manage',
            ],
            'doctor' => [
                'appointments.view',
                'patients.view',
            ],
            'content_manager' => [
                'content.manage',
            ],
        ];

        foreach ($rolePermissions as $roleName => $names) {
            foreach ($names as $permissionName) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionIds[$permissionName],
                    'role_id' => $roleIds[$roleName],
                ]);
            }
        }

        $users = [
            [
                'name' => 'Super Admin (Demo)',
                'email' => 'superadmin@hospital.com',
                'role' => 'super_admin',
            ],
            [
                'name' => 'Hospital Admin (Demo)',
                'email' => 'admin@hospital.com',
                'role' => 'hospital_admin',
            ],
            [
                'name' => 'Receptionist (Demo)',
                'email' => 'receptionist@hospital.com',
                'role' => 'receptionist',
            ],
            [
                'name' => 'Dr. Arif Rahman (Demo)',
                'email' => 'doctor@hospital.com',
                'role' => 'doctor',
            ],
            [
                'name' => 'Content Manager (Demo)',
                'email' => 'content@hospital.com',
                'role' => 'content_manager',
            ],
        ];

        foreach ($users as $userData) {
            $legacyEmail = str_replace('@hospital.com', '@demo.hospital', $userData['email']);

            $user = User::query()
                ->whereIn('email', [$userData['email'], $legacyEmail])
                ->first();

            if (! $user) {
                $user = User::query()->create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('12345678'),
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->forceFill([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('12345678'),
                ])->save();
            }

            DB::table('model_has_roles')->updateOrInsert([
                'role_id' => $roleIds[$userData['role']],
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        }
    }
}
