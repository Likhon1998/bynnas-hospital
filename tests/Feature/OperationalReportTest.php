<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\BedEvent;
use App\Models\Bill;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_redirects_to_admin_login(): void
    {
        $this->get(route('admin.reports'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_staff_without_operations_permission_is_forbidden(): void
    {
        $user = $this->createStaffUser([
            'beds.view',
            'appointments.view',
        ], 'nurse_station');

        $this->actingAs($user)
            ->get(route('admin.reports'))
            ->assertForbidden();
    }

    public function test_report_counts_ready_beds_visits_and_billing_for_the_week(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['operations.manage']);
        $ward = Ward::factory()->create([
            'name' => 'ICU East',
            'code' => 'ICU-E',
            'type' => 'icu',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        Ward::factory()->create([
            'name' => 'Closed Wing',
            'code' => 'CLS-1',
            'type' => 'general',
            'is_active' => false,
            'sort_order' => 9,
        ]);

        $occupied = Bed::factory()->occupied()->create([
            'ward_id' => $ward->id,
            'bed_code' => 'ICU-E-01',
        ]);
        Bed::factory()->create([
            'ward_id' => $ward->id,
            'bed_code' => 'ICU-E-02',
            'status' => 'available',
        ]);
        Bed::factory()->reserved()->create([
            'ward_id' => $ward->id,
            'bed_code' => 'ICU-E-03',
        ]);
        Bed::factory()->maintenance()->create([
            'ward_id' => $ward->id,
            'bed_code' => 'ICU-E-04',
        ]);
        Bed::factory()->dirty()->create([
            'ward_id' => $ward->id,
            'bed_code' => 'ICU-E-05',
        ]);

        BedEvent::factory()->create([
            'bed_id' => $occupied->id,
            'event_type' => BedEvent::TYPE_ADMITTED,
            'from_status' => 'available',
            'to_status' => 'occupied',
            'created_at' => now(),
        ]);
        BedEvent::factory()->create([
            'bed_id' => $occupied->id,
            'event_type' => BedEvent::TYPE_ADMITTED,
            'from_status' => 'available',
            'to_status' => 'occupied',
            'created_at' => now()->subMonth(),
        ]);

        $emergency = Department::factory()->create(['name' => 'Emergency', 'slug' => 'emergency']);
        $clinic = Department::factory()->create(['name' => 'Cardiology', 'slug' => 'cardiology']);
        $doctor = Doctor::factory()->create(['department_id' => $clinic->id]);
        $patient = Patient::factory()->create();

        Appointment::factory()->recycle([$doctor, $patient])->create([
            'department_id' => $clinic->id,
            'appointment_date' => '2026-10-28',
            'status' => 'pending',
            'source' => 'admin',
        ]);
        Appointment::factory()->recycle([$doctor, $patient])->create([
            'department_id' => $clinic->id,
            'appointment_date' => '2026-10-28',
            'status' => 'completed',
            'source' => 'admin',
        ]);
        Appointment::factory()->recycle([$doctor, $patient])->create([
            'department_id' => $clinic->id,
            'appointment_date' => '2026-10-28',
            'status' => 'cancelled',
            'source' => 'admin',
        ]);
        Appointment::factory()->recycle([$doctor, $patient])->create([
            'department_id' => $emergency->id,
            'appointment_date' => '2026-10-28',
            'status' => 'confirmed',
            'source' => 'emergency',
        ]);
        Appointment::factory()->recycle([$doctor, $patient])->create([
            'department_id' => $clinic->id,
            'appointment_date' => '2026-09-01',
            'status' => 'pending',
            'source' => 'admin',
        ]);

        Bill::factory()->create([
            'bill_date' => '2026-10-28',
            'status' => 'issued',
            'total' => 500,
        ]);
        Bill::factory()->create([
            'bill_date' => '2026-10-28',
            'status' => 'paid',
            'total' => 250,
            'paid_amount' => 250,
            'paid_at' => '2026-10-28 11:00:00',
        ]);
        Bill::factory()->create([
            'bill_date' => '2026-10-28',
            'status' => 'cancelled',
            'total' => 999,
            'cancelled_at' => now(),
        ]);
        Bill::factory()->create([
            'bill_date' => '2026-09-01',
            'status' => 'paid',
            'total' => 80,
            'paid_amount' => 80,
            'paid_at' => '2026-10-28 12:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('admin.reports'));

        $response->assertOk();
        $response->assertSee('Operational Reports');
        $response->assertSee('Hospital snapshot');
        $response->assertSee('This week');
        $response->assertSee('ICU East');
        $response->assertDontSee('Closed Wing');
        $response->assertSee('Ready to admit');
        $response->assertSee('Bed census');

        $this->assertSame('week', $response->viewData('period'));
        $this->assertSame('2026-10-26', $response->viewData('from'));
        $this->assertSame('2026-11-01', $response->viewData('to'));

        $beds = $response->viewData('beds');
        $this->assertSame(5, $beds['total']);
        $this->assertSame(1, $beds['occupied']);
        $this->assertSame(1, $beds['ready']);
        $this->assertSame(1, $beds['turnaround']);
        $this->assertSame(1, $beds['maintenance']);
        $this->assertSame(20, $beds['percent']);
        $this->assertSame(1, $beds['admitted']);

        $icu = collect($beds['wards'])->firstWhere('name', 'ICU East');
        $this->assertNotNull($icu);
        $this->assertSame('ICU', $icu['type']);
        $this->assertSame(1, $icu['occupied']);
        $this->assertSame(5, $icu['total']);
        $this->assertSame(20, $icu['percent']);

        $visits = $response->viewData('visits');
        $this->assertSame(3, $visits['total']);
        $this->assertSame(1, $visits['pending']);
        $this->assertSame(1, $visits['confirmed']);
        $this->assertSame(1, $visits['completed']);
        $this->assertSame(1, $visits['cancelled']);
        $this->assertSame(1, $visits['emergency']);

        $billing = $response->viewData('billing');
        $this->assertSame('750.00', $billing['billed']);
        $this->assertSame('330.00', $billing['collected']);
        $this->assertSame(1, $billing['unpaid']);
    }

    public function test_period_filter_limits_visits_to_the_selected_range(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['operations.manage']);
        $clinic = Department::factory()->create(['name' => 'Cardiology', 'slug' => 'cardiology']);
        $doctor = Doctor::factory()->create(['department_id' => $clinic->id]);
        $patient = Patient::factory()->create();

        Appointment::factory()->recycle([$doctor, $patient])->create([
            'department_id' => $clinic->id,
            'appointment_date' => '2026-10-23',
            'status' => 'pending',
            'source' => 'admin',
        ]);
        Appointment::factory()->recycle([$doctor, $patient])->create([
            'department_id' => $clinic->id,
            'appointment_date' => '2026-10-28',
            'status' => 'pending',
            'source' => 'admin',
        ]);
        Appointment::factory()->recycle([$doctor, $patient])->create([
            'department_id' => $clinic->id,
            'appointment_date' => '2026-10-30',
            'status' => 'pending',
            'source' => 'admin',
        ]);

        $today = $this->actingAs($user)->get(route('admin.reports', ['period' => 'today']));
        $today->assertOk();
        $this->assertSame('today', $today->viewData('period'));
        $this->assertSame(1, $today->viewData('visits')['total']);
        $today->assertSee('Today · 28 Oct 2026');

        $week = $this->actingAs($user)->get(route('admin.reports', ['period' => 'week']));
        $this->assertSame(2, $week->viewData('visits')['total']);
        $this->assertSame('2026-10-26', $week->viewData('from'));
        $this->assertSame('2026-11-01', $week->viewData('to'));

        $lastSeven = $this->actingAs($user)->get(route('admin.reports', ['period' => '7d']));
        $this->assertSame('7d', $lastSeven->viewData('period'));
        $this->assertSame(2, $lastSeven->viewData('visits')['total']);
        $this->assertSame('2026-10-22', $lastSeven->viewData('from'));
        $this->assertSame('2026-10-28', $lastSeven->viewData('to'));
    }

    public function test_invalid_period_falls_back_to_this_week(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['operations.manage']);

        $response = $this->actingAs($user)->get(route('admin.reports', ['period' => 'month']));

        $response->assertOk();
        $this->assertSame('week', $response->viewData('period'));
        $this->assertSame('2026-10-26', $response->viewData('from'));
        $this->assertSame('2026-11-01', $response->viewData('to'));
        $response->assertSee('This week');
    }

    public function test_escapes_dangerous_ward_name_in_the_census(): void
    {
        $user = $this->createStaffUser(['operations.manage']);
        Ward::factory()->create([
            'name' => "ICU <script>alert('xss')</script>",
            'code' => 'ICU-X',
            'type' => 'icu',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.reports'));

        $response->assertOk();
        $this->assertStringContainsString('&lt;script&gt;', $response->getContent());
        $this->assertStringNotContainsString("<script>alert('xss')</script>", $response->getContent());
    }
}
