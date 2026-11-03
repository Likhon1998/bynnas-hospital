<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\FinanceApproval;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_dashboard_shows_billing_desk_widgets(): void
    {
        $cashier = $this->createStaffUser([
            'patients.view',
            'appointments.view',
            'visits.view',
            'admissions.view',
            'billing.manage',
        ], 'cashier');

        $patient = Patient::factory()->create();
        $bill = Bill::factory()->create([
            'patient_id' => $patient->id,
            'issued_by' => $cashier->id,
            'bill_date' => today(),
            'status' => 'issued',
            'subtotal' => 1000,
            'discount' => 0,
            'total' => 1000,
            'paid_amount' => 0,
        ]);
        $bill->syncItems([
            [
                'item_type' => 'consultation',
                'description' => 'OPD consultation',
                'quantity' => 1,
                'unit_price' => 1000,
            ],
        ]);
        $bill->update(['status' => 'issued', 'total' => 1000, 'subtotal' => 1000]);

        FinanceApproval::factory()->create([
            'type' => FinanceApproval::TYPE_REFUND,
            'status' => FinanceApproval::STATUS_PENDING,
            'bill_id' => $bill->id,
            'amount' => 200,
            'requested_by' => $cashier->id,
        ]);

        $response = $this->actingAs($cashier)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Today&#039;s Bills', false);
        $response->assertSee('Today&#039;s Collections', false);
        $response->assertSee('Pending Payments', false);
        $response->assertSee('Outstanding Dues', false);
        $response->assertSee('Refund Requests', false);
        $response->assertSee($bill->bill_no, false);
        $response->assertDontSee(route('admin.owner.finance.index'), false);
    }

    public function test_cashier_collects_cash_card_mobile_payments_and_prints_receipt(): void
    {
        $cashier = $this->createStaffUser(['billing.manage'], 'cashier');
        $patient = Patient::factory()->create();
        $bill = Bill::factory()->create([
            'patient_id' => $patient->id,
            'issued_by' => $cashier->id,
            'status' => 'draft',
            'discount' => 0,
            'paid_amount' => 0,
        ]);
        $bill->syncItems([
            [
                'item_type' => 'consultation',
                'description' => 'OPD consultation',
                'quantity' => 1,
                'unit_price' => 800,
            ],
            [
                'item_type' => 'bed',
                'description' => 'Bed charge',
                'quantity' => 1,
                'unit_price' => 500,
            ],
            [
                'item_type' => 'pharmacy',
                'description' => 'Pharmacy medicines',
                'quantity' => 1,
                'unit_price' => 300,
            ],
            [
                'item_type' => 'admission',
                'description' => 'IPD admission payment',
                'quantity' => 1,
                'unit_price' => 2000,
            ],
        ]);
        $bill->appendUniqueLine('lab', 99, 'Laboratory charge', 1, 400);
        $bill->update(['status' => 'issued']);
        $bill->refresh();

        $this->actingAs($cashier)
            ->post(route('admin.bills.pay', $bill), [
                'amount' => 1000,
                'method' => 'cash',
            ])
            ->assertRedirect(route('admin.bills.show', $bill));

        $bill->refresh();
        $this->assertSame('issued', $bill->status);
        $this->assertSame(1000.0, (float) $bill->paid_amount);
        $this->assertSame(1, $bill->payments()->count());

        $this->actingAs($cashier)
            ->post(route('admin.bills.pay', $bill), [
                'amount' => 1500,
                'method' => 'bkash',
                'reference' => 'BKASH-123',
            ])
            ->assertRedirect();

        $this->actingAs($cashier)
            ->post(route('admin.bills.pay', $bill), [
                'amount' => $bill->fresh()->balanceDue(),
                'method' => 'card',
                'reference' => 'CARD-999',
            ])
            ->assertRedirect();

        $bill->refresh();
        $this->assertSame('paid', $bill->status);
        $this->assertSame(0.0, $bill->balanceDue());
        $this->assertSame(3, BillPayment::query()->where('bill_id', $bill->id)->count());

        $this->actingAs($cashier)
            ->get(route('admin.bills.print', $bill))
            ->assertOk()
            ->assertSee('Money receipt', false)
            ->assertSee('bKash', false);

        $this->actingAs($cashier)
            ->get(route('admin.bills.collections'))
            ->assertOk()
            ->assertSee('Daily collections', false);
    }

    public function test_cashier_can_request_refund_but_cannot_approve_or_apply_high_discount(): void
    {
        $cashier = $this->createStaffUser(['billing.manage'], 'cashier');
        $owner = $this->createStaffUser(['finance.view', 'finance.approve', 'billing.manage'], 'hospital_owner');
        $patient = Patient::factory()->create();
        $bill = Bill::factory()->create([
            'patient_id' => $patient->id,
            'issued_by' => $cashier->id,
            'status' => 'draft',
            'discount' => 0,
            'paid_amount' => 0,
        ]);
        $bill->syncItems([
            [
                'item_type' => 'custom',
                'description' => 'Service charge',
                'quantity' => 1,
                'unit_price' => 2000,
            ],
        ]);
        $bill->update(['status' => 'issued']);

        $this->actingAs($cashier)->post(route('admin.bills.pay', $bill), [
            'amount' => 2000,
            'method' => 'nagad',
            'reference' => 'NAGAD-55',
        ])->assertRedirect();

        $this->actingAs($cashier)->post(route('admin.bills.refund-request', $bill), [
            'amount' => 500,
            'reason' => 'Patient overpaid at desk',
        ])->assertRedirect();

        $approval = FinanceApproval::query()->where('type', FinanceApproval::TYPE_REFUND)->first();
        $this->assertNotNull($approval);
        $this->assertSame(FinanceApproval::STATUS_PENDING, $approval->status);

        $this->actingAs($cashier)
            ->post(route('admin.owner.approvals.decide', $approval), [
                'decision' => 'approved',
            ])
            ->assertForbidden();

        $this->assertFalse($cashier->can('finance.approve'));

        $this->actingAs($cashier)->post(route('admin.bills.store'), [
            'patient_id' => $patient->id,
            'bill_date' => now()->toDateString(),
            'discount' => 600,
            'action' => 'draft',
            'items' => [
                [
                    'item_type' => 'custom',
                    'description' => 'Consult',
                    'quantity' => 1,
                    'unit_price' => 1000,
                ],
            ],
        ])->assertRedirect()->assertSessionHasErrors('discount');

        $this->actingAs($owner)
            ->post(route('admin.owner.approvals.decide', $approval), [
                'decision' => 'approved',
            ])
            ->assertRedirect();

        $bill->refresh();
        $this->assertSame(1500.0, (float) $bill->paid_amount);
        $this->assertSame('issued', $bill->status);
    }
}
