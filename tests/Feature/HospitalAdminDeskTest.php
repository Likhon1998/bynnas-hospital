<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\FinanceApproval;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalAdminDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_hospital_admin_dashboard_shows_operations_widgets(): void
    {
        $admin = $this->createStaffUser([
            'appointments.view',
            'appointments.manage',
            'patients.view',
            'patients.manage',
            'doctors.manage',
            'content.manage',
            'billing.manage',
            'operations.manage',
            'beds.view',
            'beds.operate',
            'admissions.view',
            'admissions.admit',
            'admissions.discharge',
            'lab.view',
            'settings.manage',
        ], 'hospital_admin');

        Patient::factory()->create(['created_at' => now()]);
        Appointment::factory()->create([
            'appointment_date' => today()->toDateString(),
            'status' => 'confirmed',
        ]);
        Admission::factory()->create(['status' => 'discharge_authorized']);
        Service::factory()->create([
            'title' => 'Pharmacy',
            'slug' => 'pharmacy',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Hospital Admin Desk', false);
        $response->assertSee('Today&#039;s Patients', false);
        $response->assertSee('Today&#039;s Appointments', false);
        $response->assertSee('Pending Admissions', false);
        $response->assertSee('Pending Discharges', false);
        $response->assertSee('Available Beds', false);
        $response->assertSee('Pending Laboratory Reports', false);
        $response->assertSee('Department-wise Activity', false);
        $response->assertSee('Staff Activity', false);
        $response->assertSee('Pharmacy', false);
        $response->assertSee(route('admin.owner.approvals.index'), false);
        $response->assertSee(route('admin.reports'), false);
    }

    public function test_hospital_admin_cannot_apply_high_value_discount_without_director_approval(): void
    {
        $admin = $this->createStaffUser(['billing.manage'], 'hospital_admin');
        $patient = Patient::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.bills.store'), [
            'patient_id' => $patient->id,
            'bill_date' => now()->toDateString(),
            'discount' => 750,
            'notes' => null,
            'items' => [
                [
                    'item_type' => 'custom',
                    'description' => 'Consultation',
                    'quantity' => 1,
                    'unit_price' => 2000,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('discount');
        $this->assertDatabaseCount('bills', 0);
        $this->assertDatabaseHas('finance_approvals', [
            'type' => FinanceApproval::TYPE_DISCOUNT,
            'status' => FinanceApproval::STATUS_PENDING,
            'amount' => 750,
        ]);
    }

    public function test_owner_can_apply_high_value_discount_directly(): void
    {
        $owner = $this->createStaffUser(['billing.manage', 'finance.approve'], 'hospital_owner');
        $patient = Patient::factory()->create();

        $response = $this->actingAs($owner)->post(route('admin.bills.store'), [
            'patient_id' => $patient->id,
            'bill_date' => now()->toDateString(),
            'discount' => 750,
            'notes' => null,
            'items' => [
                [
                    'item_type' => 'custom',
                    'description' => 'Consultation',
                    'quantity' => 1,
                    'unit_price' => 2000,
                ],
            ],
        ]);

        $bill = Bill::query()->first();
        $this->assertNotNull($bill);
        $response->assertRedirect(route('admin.bills.show', $bill));
        $this->assertEquals(750.0, (float) $bill->discount);
    }

    public function test_hospital_admin_can_open_approvals_but_not_decide(): void
    {
        $admin = $this->createStaffUser(['billing.manage', 'operations.manage'], 'hospital_admin');
        $approval = FinanceApproval::factory()->create([
            'status' => FinanceApproval::STATUS_PENDING,
            'type' => FinanceApproval::TYPE_REFUND,
            'amount' => 200,
            'reason' => 'Duplicate payment',
        ]);

        $this->actingAs($admin)->get(route('admin.owner.approvals.index'))->assertOk();
        $this->actingAs($admin)->post(route('admin.owner.approvals.decide', $approval), [
            'decision' => 'approved',
        ])->assertForbidden();
        $this->assertSame(FinanceApproval::STATUS_PENDING, $approval->fresh()->status);
    }
}
