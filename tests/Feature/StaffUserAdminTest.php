<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StaffUserAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_toggle_and_delete_staff_and_change_roles(): void
    {
        $admin = $this->createStaffUser([], 'super_admin');
        $role = Role::query()->firstOrCreate(
            ['name' => 'receptionist', 'guard_name' => 'web']
        );
        $doctorRole = Role::query()->firstOrCreate(
            ['name' => 'doctor', 'guard_name' => 'web']
        );

        $staff = User::factory()->create([
            'email' => 'desk@hospital.com',
            'is_active' => true,
        ]);
        $staff->syncRoleIds([$role->id]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Deactivate', false)
            ->assertSee('Delete', false);

        $this->actingAs($admin)
            ->post(route('admin.users.toggle-active', $staff))
            ->assertRedirect();

        $this->assertFalse($staff->fresh()->is_active);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'is_active' => '1',
                'role_ids' => [$doctorRole->id],
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue($staff->fresh()->is_active);
        $this->assertTrue($staff->fresh()->hasRole('doctor'));
        $this->assertFalse($staff->fresh()->hasRole('receptionist'));

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $staff))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['email' => 'desk@hospital.com']);
        $this->assertDatabaseMissing('model_has_roles', [
            'model_id' => $staff->id,
            'model_type' => $staff->getMorphClass(),
        ]);
    }

    public function test_staff_cannot_deactivate_or_delete_themselves(): void
    {
        $admin = $this->createStaffUser([], 'super_admin');

        $this->actingAs($admin)
            ->post(route('admin.users.toggle-active', $admin))
            ->assertStatus(422);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_users_index_can_filter_by_role(): void
    {
        $admin = $this->createStaffUser([], 'super_admin');
        $role = Role::query()->firstOrCreate(
            ['name' => 'lab_technician', 'guard_name' => 'web']
        );

        $matched = User::factory()->create(['email' => 'lab@hospital.com']);
        $matched->syncRoleIds([$role->id]);

        $other = User::factory()->create(['email' => 'other@hospital.com']);
        DB::table('model_has_roles')->where('model_id', $other->id)->delete();

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'lab_technician']))
            ->assertOk()
            ->assertSee('lab@hospital.com', false)
            ->assertDontSee('other@hospital.com', false);
    }
}
