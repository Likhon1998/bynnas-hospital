<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bill;
use App\Models\FinanceApproval;
use App\Models\HospitalExpense;
use App\Models\Patient;
use App\Models\SystemAuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerDirectorDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_dashboard_shows_executive_monitoring_widgets(): void
    {
        $owner = $this->createStaffUser([
            'finance.view',
            'finance.approve',
            'audit.view',
            'settings.manage',
            'operations.manage',
            'billing.manage',
            'patients.view',
            'appointments.view',
            'beds.view',
            'admissions.view',
            'lab.view',
            'doctors.manage',
        ], 'hospital_owner');

        Patient::factory()->create();
        Bill::factory()->create([
            'status' => 'paid',
            'paid_amount' => 2500,
            'paid_at' => now(),
            'bill_no' => 'BILL-OWNER-1',
        ]);
        HospitalExpense::factory()->create(['amount' => 400, 'expense_date' => now()->toDateString()]);
        Admission::factory()->create(['status' => 'discharge_authorized']);

        $response = $this->actingAs($owner)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Today&#039;s Revenue', false);
        $response->assertSee('Monthly Revenue', false);
        $response->assertSee('Total Expenses', false);
        $response->assertSee('Total Outstanding Dues', false);
        $response->assertSee('Total Registered Patients', false);
        $response->assertSee('OPD / IPD', false);
        $response->assertSee('Available Beds', false);
        $response->assertSee('Occupied Beds', false);
        $response->assertSee('Doctor Performance', false);
        $response->assertSee('Department-wise Revenue', false);
        $response->assertSee('Staff Attendance', false);
        $response->assertSee('Pending Laboratory Reports', false);
        $response->assertSee('Pending Discharges', false);
        $response->assertSee('Profit / Loss', false);
        $response->assertSee(route('admin.owner.finance.index'), false);
        $response->assertSee(route('admin.owner.approvals.index'), false);
    }

    public function test_owner_can_manage_finance_and_approvals_but_not_users_or_backup(): void
    {
        $owner = $this->createStaffUser([
            'finance.view',
            'finance.approve',
            'audit.view',
            'settings.manage',
            'billing.manage',
        ], 'hospital_owner');

        $this->actingAs($owner)->get(route('admin.owner.finance.index'))->assertOk();
        $this->actingAs($owner)->post(route('admin.owner.finance.expenses.store'), [
            'expense_date' => now()->toDateString(),
            'category' => 'Utilities',
            'description' => 'Power bill',
            'amount' => 1200,
        ])->assertRedirect();

        $this->assertDatabaseHas('hospital_expenses', [
            'category' => 'Utilities',
            'description' => 'Power bill',
        ]);

        $approval = FinanceApproval::factory()->create([
            'type' => FinanceApproval::TYPE_REFUND,
            'status' => FinanceApproval::STATUS_PENDING,
            'amount' => 300,
            'reason' => 'Duplicate charge',
        ]);

        $this->actingAs($owner)->post(route('admin.owner.approvals.decide', $approval), [
            'decision' => 'approved',
        ])->assertRedirect();

        $this->assertSame(FinanceApproval::STATUS_APPROVED, $approval->fresh()->status);

        SystemAuditLog::query()->create([
            'user_id' => $owner->id,
            'action' => 'finance.approved',
            'summary' => 'Approved refund request',
        ]);

        $this->actingAs($owner)->get(route('admin.system.audit-logs.index'))
            ->assertOk()
            ->assertSee('Approved refund request', false)
            ->assertDontSee('Database backup', false);

        $this->actingAs($owner)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($owner)->get(route('admin.system.backup.index'))->assertForbidden();
    }

    public function test_non_owner_cannot_open_owner_finance_desk(): void
    {
        $admin = $this->createStaffUser(['billing.manage', 'operations.manage'], 'hospital_admin');

        $this->actingAs($admin)->get(route('admin.owner.finance.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.system.audit-logs.index'))->assertForbidden();
    }
}
