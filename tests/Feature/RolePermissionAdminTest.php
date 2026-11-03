<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RolePermissionAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_role_and_assign_permissions(): void
    {
        $admin = $this->createStaffUser([], 'super_admin');
        $billingId = Permission::query()->firstOrCreate(
            ['name' => 'billing.manage', 'guard_name' => 'web']
        )->id;
        $patientsId = Permission::query()->firstOrCreate(
            ['name' => 'patients.view', 'guard_name' => 'web']
        )->id;

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('Create role', false)
            ->assertSee('Edit permissions', false);

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'Ward Clerk',
                'permission_ids' => [$billingId, $patientsId],
            ])
            ->assertRedirect();

        $role = Role::query()->where('name', 'ward_clerk')->first();
        $this->assertNotNull($role);
        $this->assertEqualsCanonicalizing(
            [$billingId, $patientsId],
            $role->permissions()->pluck('permissions.id')->map(fn ($id) => (int) $id)->all()
        );

        $staff = User::factory()->create([
            'email' => 'wardclerk@hospital.com',
            'is_active' => true,
        ]);
        $staff->syncRoleIds([$role->id]);

        $this->assertTrue($staff->fresh()->hasPermission('billing.manage'));
        $this->assertTrue($staff->fresh()->hasPermission('patients.view'));
        $this->assertFalse($staff->fresh()->hasPermission('finance.approve'));
    }

    public function test_super_admin_can_update_role_permissions_and_cannot_delete_super_admin(): void
    {
        $admin = $this->createStaffUser([], 'super_admin');
        $role = Role::query()->create(['name' => 'float_nurse', 'guard_name' => 'web']);
        $nursingId = Permission::query()->firstOrCreate(
            ['name' => 'nursing.view', 'guard_name' => 'web']
        )->id;
        $role->syncPermissionIds([]);

        $this->actingAs($admin)
            ->put(route('admin.roles.update', $role), [
                'name' => 'float_nurse',
                'permission_ids' => [$nursingId],
            ])
            ->assertRedirect(route('admin.roles.edit', $role));

        $this->assertTrue($role->fresh()->permissions()->where('permissions.name', 'nursing.view')->exists());

        $superAdmin = Role::query()->firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web']
        );

        $this->actingAs($admin)
            ->delete(route('admin.roles.destroy', $superAdmin))
            ->assertForbidden();

        $this->assertDatabaseHas('roles', ['name' => 'super_admin']);
    }

    public function test_non_super_admin_cannot_manage_roles(): void
    {
        $admin = $this->createStaffUser(['billing.manage'], 'hospital_admin');

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_role_with_assigned_staff_cannot_be_deleted(): void
    {
        $admin = $this->createStaffUser([], 'super_admin');
        $role = Role::query()->create(['name' => 'temp_desk', 'guard_name' => 'web']);
        $user = User::factory()->create();
        DB::table('model_has_roles')->insert([
            'role_id' => $role->id,
            'model_id' => $user->id,
            'model_type' => $user->getMorphClass(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.roles.destroy', $role))
            ->assertStatus(422);

        $this->assertDatabaseHas('roles', ['name' => 'temp_desk']);
    }
}
