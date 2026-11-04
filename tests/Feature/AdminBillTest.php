<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBillTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_without_billing_permission_cannot_open_billing(): void
    {
        $user = $this->createStaffUser(['content.manage'], 'content_manager');

        $response = $this->actingAs($user)->get(route('admin.bills.index'));

        $response->assertForbidden();
    }

    public function test_receptionist_can_open_the_billing_index(): void
    {
        $user = $this->createStaffUser(['billing.manage'], 'receptionist');

        $response = $this->actingAs($user)->get(route('admin.bills.index'));

        $response->assertOk();
        $response->assertSee('Patient billing');
    }

    public function test_empty_bill_payload_is_rejected(): void
    {
        $user = $this->createStaffUser(['billing.manage'], 'receptionist');

        $response = $this->actingAs($user)
            ->from(route('admin.bills.create'))
            ->post(route('admin.bills.store'), []);

        $response->assertRedirect(route('admin.bills.create'));
        $response->assertSessionHasErrors(['patient_id', 'items']);
        $this->assertSame(0, Bill::query()->count());
    }

    public function test_receptionist_can_generate_a_patient_bill(): void
    {
        $user = $this->createStaffUser(['billing.manage'], 'receptionist');
        $patient = Patient::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.bills.store'), [
            'patient_id' => $patient->id,
            'bill_date' => now()->toDateString(),
            'discount' => '50',
            'action' => 'issue',
            'items' => [
                [
                    'item_type' => 'custom',
                    'description' => 'Consultation fee',
                    'quantity' => '1',
                    'unit_price' => '800',
                ],
            ],
        ]);

        $bill = Bill::query()->first();

        $this->assertNotNull($bill);
        $response->assertRedirect(route('admin.bills.show', $bill));
        $this->assertSame('issued', $bill->status);
        $this->assertSame(800.0, (float) $bill->subtotal);
        $this->assertSame(50.0, (float) $bill->discount);
        $this->assertSame(750.0, (float) $bill->total);
        $this->assertDatabaseHas('bill_items', [
            'bill_id' => $bill->id,
            'description' => 'Consultation fee',
        ]);
    }

    public function test_create_bill_page_shows_stepped_cashier_flow(): void
    {
        $user = $this->createStaffUser(['billing.manage'], 'cashier');

        $this->actingAs($user)
            ->get(route('admin.bills.create'))
            ->assertOk()
            ->assertSee('1. Patient', false)
            ->assertSee('2. Charges', false)
            ->assertSee('3. Settle', false)
            ->assertSee('Add from catalog', false)
            ->assertSee('No charges yet', false)
            ->assertSee('bill-form__charges', false)
            ->assertSee('data-bill-action', false)
            ->assertDontSee('name="items[0][description]"', false);
    }

    public function test_issuing_a_zero_total_bill_is_rejected(): void
    {
        $user = $this->createStaffUser(['billing.manage'], 'cashier');
        $patient = Patient::factory()->create();

        $this->actingAs($user)
            ->from(route('admin.bills.create'))
            ->post(route('admin.bills.store'), [
                'patient_id' => $patient->id,
                'bill_date' => now()->toDateString(),
                'discount' => '0',
                'action' => 'issue',
                'items' => [
                    [
                        'item_type' => 'custom',
                        'description' => 'Empty charge',
                        'quantity' => '1',
                        'unit_price' => '0',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.bills.create'))
            ->assertSessionHasErrors(['items']);

        $this->assertSame(0, Bill::query()->count());
    }

    public function test_bill_show_page_uses_compact_action_rail(): void
    {
        $user = $this->createStaffUser(['billing.manage'], 'cashier');
        $patient = Patient::factory()->create(['full_name' => 'Compact Bill Patient']);
        $bill = Bill::factory()->create([
            'patient_id' => $patient->id,
            'issued_by' => $user->id,
            'status' => 'issued',
        ]);
        $bill->syncItems([
            [
                'item_type' => 'custom',
                'description' => 'OPD fee',
                'quantity' => 1,
                'unit_price' => 500,
            ],
        ]);

        $this->actingAs($user)
            ->get(route('admin.bills.show', $bill))
            ->assertOk()
            ->assertSee('bill-desk', false)
            ->assertSee('data-bill-actions', false)
            ->assertSee('Print money receipt', false)
            ->assertSee('Daily collections', false)
            ->assertSee('Compact Bill Patient', false)
            ->assertDontSee('rounded-full bg-navy-900', false);
    }

    public function test_receptionist_can_mark_an_issued_bill_as_paid(): void
    {
        $user = $this->createStaffUser(['billing.manage'], 'receptionist');
        $patient = Patient::factory()->create();
        $bill = Bill::factory()->create([
            'patient_id' => $patient->id,
            'issued_by' => $user->id,
            'status' => 'draft',
        ]);
        $bill->syncItems([
            [
                'item_type' => 'custom',
                'description' => 'Lab test',
                'quantity' => 1,
                'unit_price' => 1200,
            ],
        ]);
        $bill->update(['status' => 'issued']);

        $response = $this->actingAs($user)->post(route('admin.bills.pay', $bill), [
            'amount' => 1200,
            'method' => 'cash',
        ]);

        $response->assertRedirect(route('admin.bills.show', $bill));
        $bill->refresh();
        $this->assertSame('paid', $bill->status);
        $this->assertSame(1200.0, (float) $bill->paid_amount);
        $this->assertSame(1, $bill->payments()->count());
    }

    public function test_draft_bill_cannot_be_marked_paid(): void
    {
        $user = $this->createStaffUser(['billing.manage'], 'receptionist');
        $bill = Bill::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->post(route('admin.bills.pay', $bill), [
            'amount' => 100,
            'method' => 'cash',
        ]);

        $response->assertForbidden();
        $this->assertSame('draft', $bill->fresh()->status);
    }
}
