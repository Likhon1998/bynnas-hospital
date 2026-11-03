<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\BedEvent;
use App\Models\Bill;
use App\Models\Ward;
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

    public function test_staff_and_doctors_appear_as_separate_nav_items(): void
    {
        $user = $this->createStaffUser(['users.manage', 'doctors.manage']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('>Staff</span>', false);
        $response->assertSee('>Doctors</span>', false);
        $response->assertSee(route('admin.users.index'), false);
        $response->assertSee(route('admin.doctors.index'), false);
        $response->assertDontSee('Staff Management');
    }

    public function test_staff_dashboard_shows_medcore_desk_sections(): void
    {
        $user = $this->createStaffUser([
            'appointments.view',
            'patients.view',
            'patients.manage',
            'doctors.manage',
            'billing.manage',
        ], 'hospital_admin');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Hospital Admin Desk');
        $response->assertSee('Unpaid bills');
        $response->assertSee('Operational exceptions');
        $response->assertSee('Escalated alerts');
        $response->assertSee('Desk actions');
        $response->assertSee('Register patient');
        $response->assertSee('Create bill');
        $response->assertSee('Queue history');
        $response->assertDontSee('Welcome back');
        $response->assertDontSee('Admin command desk');
        $response->assertDontSee('Patient ID P-');
        $response->assertDontSee('Bed occupancy');
        $response->assertDontSee('Housekeeping queue');
        $response->assertDontSee('Website requests');
        $response->assertDontSee('Upcoming appointments');
    }

    public function test_dashboard_shows_real_upcoming_visit_details(): void
    {
        $user = $this->createStaffUser(['appointments.view', 'appointments.manage'], 'receptionist');
        $appointment = Appointment::factory()->create([
            'appointment_no' => 'APT-TEST-1001',
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'status' => 'pending',
            'source' => 'web',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Reception Desk');
        $response->assertSee('Website requests');
        $response->assertSee('APT-TEST-1001');
        $response->assertSee($appointment->patient->full_name);
        $response->assertDontSee('Patient ID P-');
    }

    public function test_nurse_dashboard_emphasizes_beds_and_housekeeping(): void
    {
        $user = $this->createStaffUser([
            'appointments.view',
            'patients.view',
            'patients.manage',
            'beds.view',
            'beds.operate',
            'beds.maintain',
            'nursing.view',
        ], 'nurse_station');

        $ward = Ward::factory()->create(['name' => 'General A']);
        Bed::factory()->dirty()->create([
            'ward_id' => $ward->id,
            'bed_code' => 'GEN-DASH-01',
            'status' => 'dirty',
        ]);
        $occupied = Bed::factory()->occupied()->create([
            'ward_id' => $ward->id,
            'bed_code' => 'GEN-DASH-02',
            'status' => 'occupied',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Nursing Desk');
        $response->assertSee('Assigned patients');
        $response->assertSee('Cleaning');
        $response->assertSee('Bed occupancy');
        $response->assertSee('GEN-DASH-01');
        $response->assertSee('GEN-DASH-02');
        $response->assertSee($occupied->patient?->full_name);
        $response->assertSee('Assign bed');
        $response->assertDontSee('Create bill');
        $response->assertDontSee('Website requests');
        $response->assertDontSee('Unpaid bills');
        $response->assertDontSee('Upcoming appointments');
    }

    public function test_receptionist_sidebar_puts_take_appointment_first(): void
    {
        $user = $this->createStaffUser([
            'appointments.view',
            'appointments.manage',
            'patients.view',
            'beds.view',
        ], 'receptionist');

        $html = $this->actingAs($user)->get(route('admin.dashboard'))->assertOk()->getContent();

        $this->assertNotFalse(strpos($html, 'Front desk'));
        $this->assertNotFalse(strpos($html, 'Take appointment'));
        $this->assertNotFalse(strpos($html, 'M12 11v5M9.5 13.5h5'));
        $this->assertNotFalse(strpos($html, 'M3 12h3.2l1.6-4.2L11 17l2.2-5H21'));
        $this->assertNotFalse(strpos($html, 'M16 9v14M9 16h14'));
        $this->assertLessThan(
            strpos($html, '>Appointments</span>'),
            strpos($html, 'Take appointment'),
        );
        $this->assertLessThan(
            strpos($html, '>Beds</span>'),
            strpos($html, '>Appointments</span>'),
        );
    }

    public function test_receptionist_dashboard_shows_register_and_billing_actions(): void
    {
        $user = $this->createStaffUser([
            'appointments.view',
            'appointments.manage',
            'patients.view',
            'patients.manage',
            'billing.manage',
            'beds.view',
            'beds.operate',
        ], 'receptionist');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Reception Desk');
        $response->assertSee('Take appointment');
        $response->assertSee(route('admin.appointments.create'), false);
        $response->assertSee('Register patient');
        $response->assertSee('Create bill');
        $response->assertSee('Doctor Schedule');
        $response->assertSee('Waiting Patients');
        $response->assertSee('Pending Serial Numbers');
        $response->assertSee('Patient Search');
        $response->assertDontSee('Housekeeping queue');
    }

    public function test_content_manager_dashboard_hides_clinical_widgets(): void
    {
        $user = $this->createStaffUser(['content.manage'], 'content_manager');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Website Desk');
        $response->assertSee('Unread messages');
        $response->assertSee('Contact inbox');
        $response->assertDontSee('Bed occupancy');
        $response->assertDontSee("Today's appointments");
        $response->assertDontSee('Create bill');
        $response->assertDontSee('Housekeeping queue');
    }

    public function test_dashboard_shows_admit_discharge_trend_from_bed_events(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate', 'operations.manage']);
        $bed = Bed::factory()->create();

        BedEvent::factory()->create([
            'bed_id' => $bed->id,
            'event_type' => BedEvent::TYPE_ADMITTED,
            'from_status' => 'available',
            'to_status' => 'occupied',
            'created_at' => now(),
        ]);
        BedEvent::factory()->create([
            'bed_id' => $bed->id,
            'event_type' => BedEvent::TYPE_DISCHARGED,
            'from_status' => 'occupied',
            'to_status' => 'dirty',
            'created_at' => now(),
        ]);

        Bill::factory()->create([
            'status' => 'paid',
            'paid_amount' => 250,
            'total' => 250,
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Hospital Admin Desk');
        $response->assertSee('Essential reports');
        $response->assertSee('Operational reports');
        $response->assertSee('Queue history');
    }
}
