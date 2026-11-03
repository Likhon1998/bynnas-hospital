<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\SystemAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_shows_executive_kpis_and_transactions(): void
    {
        $admin = $this->createStaffUser([], 'super_admin');
        Patient::factory()->count(2)->create();
        Doctor::factory()->create();
        Appointment::factory()->create([
            'appointment_date' => today()->toDateString(),
            'status' => 'confirmed',
        ]);
        $bill = Bill::factory()->create([
            'status' => 'paid',
            'paid_amount' => 1500,
            'paid_at' => now(),
            'bill_no' => 'BILL-SA-1001',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Total Patients', false);
        $response->assertSee('Total Doctors', false);
        $response->assertSee('Total Staff', false);
        $response->assertSee('Today&#039;s Appointments', false);
        $response->assertSee('Total Revenue', false);
        $response->assertSee('Active Users', false);
        $response->assertSee('System Activity', false);
        $response->assertSee('Recent Transactions', false);
        $response->assertSee('BILL-SA-1001', false);
        $response->assertSee(route('admin.system.audit-logs.index'), false);
        $response->assertSee(number_format((float) $bill->paid_amount, 2), false);
    }

    public function test_super_admin_can_create_and_deactivate_staff_accounts(): void
    {
        $admin = $this->createStaffUser([], 'super_admin');

        $create = $this->actingAs($admin)->get(route('admin.users.create'));
        $create->assertOk();
        $create->assertSee('Create staff', false);

        $store = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Desk Nurse',
            'email' => 'desknurse@hospital.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => '1',
            'role_ids' => [],
        ]);
        $store->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'desknurse@hospital.com')->firstOrFail();
        $this->assertTrue($user->is_active);
        $this->assertDatabaseHas('system_audit_logs', [
            'action' => 'users.create',
            'subject_id' => $user->id,
        ]);

        $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'Desk Nurse',
            'email' => 'desknurse@hospital.com',
            'is_active' => '0',
            'role_ids' => [],
        ])->assertRedirect(route('admin.users.index'));

        $this->assertFalse($user->fresh()->is_active);
        $this->assertDatabaseHas('system_audit_logs', [
            'action' => 'users.update',
            'subject_id' => $user->id,
        ]);
    }

    public function test_deactivated_staff_cannot_sign_in(): void
    {
        $user = $this->createStaffUser([], 'receptionist');
        $user->forceFill([
            'email' => 'locked@hospital.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ])->save();

        $response = $this->post(route('admin.login.store'), [
            'email' => 'locked@hospital.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_super_admin_can_open_audit_logs_and_backup_pages(): void
    {
        $admin = $this->createStaffUser([], 'super_admin');
        SystemAuditLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'settings.update',
            'summary' => 'Updated hospital settings.',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)->get(route('admin.system.audit-logs.index'))
            ->assertOk()
            ->assertSee('Updated hospital settings.', false);

        $this->actingAs($admin)->get(route('admin.system.backup.index'))
            ->assertOk()
            ->assertSee('Download backup', false);

        $other = $this->createStaffUser(['users.manage'], 'hospital_admin');
        $this->actingAs($other)->get(route('admin.system.audit-logs.index'))->assertForbidden();
        $this->actingAs($other)->get(route('admin.system.backup.index'))->assertForbidden();
    }

    public function test_super_admin_nav_includes_departments_audit_and_backup(): void
    {
        $admin = $this->createStaffUser([], 'super_admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee(route('admin.departments.index'), false);
        $response->assertSee(route('admin.system.audit-logs.index'), false);
        $response->assertSee(route('admin.system.backup.index'), false);
        $response->assertSee('>Departments</span>', false);
    }
}
