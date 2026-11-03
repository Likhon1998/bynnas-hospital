<?php

namespace Tests\Feature;

use App\Mail\LabReportAccessCodeMail;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WebsiteLabReportPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_the_lab_report_portal(): void
    {
        $this->get(route('lab-reports.index'))
            ->assertOk()
            ->assertSee('Lab reports')
            ->assertSee('Report access code');
    }

    public function test_payment_for_lab_bill_issues_a_numeric_access_code(): void
    {
        $cashier = $this->createStaffUser(['billing.manage'], 'cashier');
        $patient = Patient::factory()->create(['phone' => '01700111222']);
        $order = LabOrder::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'ordered',
        ]);

        $bill = Bill::factory()->create([
            'patient_id' => $patient->id,
            'issued_by' => $cashier->id,
            'status' => 'issued',
            'subtotal' => 500,
            'total' => 500,
            'paid_amount' => 0,
        ]);

        BillItem::query()->create([
            'bill_id' => $bill->id,
            'item_type' => 'lab',
            'item_id' => $order->id,
            'description' => 'CBC',
            'quantity' => 1,
            'unit_price' => 500,
            'line_total' => 500,
            'sort_order' => 1,
        ]);

        $this->actingAs($cashier)
            ->post(route('admin.bills.pay', $bill), [
                'amount' => 500,
                'method' => 'cash',
            ])
            ->assertRedirect(route('admin.bills.show', $bill));

        $bill->refresh();
        $this->assertNotNull($bill->report_access_code);
        $this->assertMatchesRegularExpression('/^\d{8}$/', (string) $bill->report_access_code);
        $this->assertStringContainsString($bill->report_access_code, session('status'));
    }

    public function test_guest_can_lookup_and_download_a_published_report_with_phone_and_access_code(): void
    {
        $patient = Patient::factory()->create([
            'phone' => '01700999888',
            'full_name' => 'Portal Patient Demo',
        ]);
        $order = LabOrder::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        LabResult::factory()->create([
            'lab_order_id' => $order->id,
            'patient_id' => $patient->id,
            'visit_id' => $order->visit_id,
            'value' => '12.5',
            'published_at' => now(),
            'verified_at' => now(),
        ]);

        $bill = Bill::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'paid',
            'paid_amount' => 900,
            'total' => 900,
            'report_access_code' => '48291037',
        ]);
        BillItem::query()->create([
            'bill_id' => $bill->id,
            'item_type' => 'lab',
            'item_id' => $order->id,
            'description' => 'Glucose',
            'quantity' => 1,
            'unit_price' => 900,
            'line_total' => 900,
            'sort_order' => 1,
        ]);

        $this->from(route('lab-reports.index'))
            ->post(route('lab-reports.lookup'), [
                'phone' => '01700-999-888',
                'access_code' => '48291037',
            ])
            ->assertOk()
            ->assertSee('Your reports')
            ->assertSee($order->order_no)
            ->assertSee('View / download');

        $this->get(route('lab-reports.download', $order))
            ->assertOk()
            ->assertSee($order->order_no)
            ->assertSee('12.5');
    }

    public function test_wrong_phone_or_code_is_rejected(): void
    {
        $patient = Patient::factory()->create(['phone' => '01700123456']);
        $bill = Bill::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'paid',
            'paid_amount' => 100,
            'total' => 100,
            'report_access_code' => '11112222',
        ]);

        $this->from(route('lab-reports.index'))
            ->post(route('lab-reports.lookup'), [
                'phone' => '01700999999',
                'access_code' => '11112222',
            ])
            ->assertRedirect(route('lab-reports.index'))
            ->assertSessionHasErrors('access_code');

        $this->from(route('lab-reports.index'))
            ->post(route('lab-reports.lookup'), [
                'phone' => '01700123456',
                'access_code' => '99998888',
            ])
            ->assertRedirect(route('lab-reports.index'))
            ->assertSessionHasErrors('access_code');

        $this->assertSame($bill->id, $bill->fresh()->id);
    }

    public function test_download_requires_a_successful_lookup_session(): void
    {
        $order = LabOrder::factory()->create(['status' => 'completed', 'completed_at' => now()]);
        LabResult::factory()->create([
            'lab_order_id' => $order->id,
            'patient_id' => $order->patient_id,
            'visit_id' => $order->visit_id,
        ]);

        $this->get(route('lab-reports.download', $order))->assertForbidden();
    }

    public function test_guest_can_recover_forgotten_access_codes_with_phone_and_patient_id(): void
    {
        Mail::fake();

        $patient = Patient::factory()->create([
            'phone' => '01700555444',
            'email' => 'patient.demo@example.com',
        ]);
        $bill = Bill::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'paid',
            'paid_amount' => 400,
            'total' => 400,
            'report_access_code' => '76543210',
            'bill_date' => now()->toDateString(),
        ]);
        BillItem::query()->create([
            'bill_id' => $bill->id,
            'item_type' => 'lab',
            'item_id' => LabOrder::factory()->create(['patient_id' => $patient->id])->id,
            'description' => 'CBC',
            'quantity' => 1,
            'unit_price' => 400,
            'line_total' => 400,
            'sort_order' => 1,
        ]);

        $this->from(route('lab-reports.recover'))
            ->post(route('lab-reports.recover.submit'), [
                'phone' => '01700555444',
                'patient_code' => $patient->patient_code,
            ])
            ->assertOk()
            ->assertSee('Access codes found')
            ->assertSee('76543210')
            ->assertSee($bill->bill_no);

        Mail::assertSent(LabReportAccessCodeMail::class, function (LabReportAccessCodeMail $mail) use ($patient): bool {
            return $mail->hasTo($patient->email);
        });
    }

    public function test_unpublished_report_is_listed_as_not_ready_after_lookup(): void
    {
        $patient = Patient::factory()->create(['phone' => '01700333444']);
        $order = LabOrder::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'processing',
        ]);
        $bill = Bill::factory()->create([
            'patient_id' => $patient->id,
            'status' => 'paid',
            'paid_amount' => 200,
            'total' => 200,
            'report_access_code' => '33334444',
        ]);
        BillItem::query()->create([
            'bill_id' => $bill->id,
            'item_type' => 'lab',
            'item_id' => $order->id,
            'description' => 'Pending test',
            'quantity' => 1,
            'unit_price' => 200,
            'line_total' => 200,
            'sort_order' => 1,
        ]);

        $this->post(route('lab-reports.lookup'), [
            'phone' => '01700333444',
            'access_code' => '33334444',
        ])
            ->assertOk()
            ->assertSee('No published report is ready for this payment');
    }
}
