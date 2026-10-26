<?php

namespace Tests\Feature;

use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_manager_is_forbidden_from_user_management(): void
    {
        $user = $this->createStaffUser(['content.manage'], 'content_manager');

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_super_admin_can_open_user_management(): void
    {
        $user = $this->createStaffUser([], 'super_admin');

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Staff');
        $response->assertSee($user->email);
    }

    public function test_staff_dashboard_shows_medcore_desk_sections(): void
    {
        $user = $this->createStaffUser([
            'appointments.view',
            'patients.view',
            'doctors.manage',
            'billing.manage',
        ], 'hospital_admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Operations Desk');
        $response->assertSee('Total visits');
        $response->assertSee('Visit status');
        $response->assertSee('Upcoming appointments');
        $response->assertSee('Generate bill');
        $response->assertSee('Patient billing');
        $response->assertSee('Care timeline');
        $response->assertDontSee('Welcome back');
        $response->assertDontSee('Quick actions');
        $response->assertDontSee('New appointment');
        $response->assertDontSee('Add patient');
        $response->assertDontSee('Admin command desk');
        $response->assertDontSee('Patient ID P-');
    }

    public function test_dashboard_shows_real_upcoming_visit_details(): void
    {
        $user = $this->createStaffUser(['appointments.view', 'appointments.manage']);
        $appointment = Appointment::factory()->create([
            'appointment_no' => 'APT-TEST-1001',
            'appointment_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('APT-TEST-1001');
        $response->assertSee($appointment->patient->full_name);
        $response->assertSee($appointment->patient->phone);
        $response->assertSee('1 visit today');
        $response->assertSee('1 still needs confirmation');
        $response->assertSee('Upcoming appointments');
        $response->assertDontSee('Patient ID P-');
    }
}
