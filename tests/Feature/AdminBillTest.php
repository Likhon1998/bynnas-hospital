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

        $response = $this->actingAs($user)->post(route('admin.bills.pay', $bill));

        $response->assertRedirect(route('admin.bills.show', $bill));
        $bill->refresh();
        $this->assertSame('paid', $bill->status);
        $this->assertSame(1200.0, (float) $bill->paid_amount);
    }

    public function test_draft_bill_cannot_be_marked_paid(): void
    {
        $user = $this->createStaffUser(['billing.manage'], 'receptionist');
        $bill = Bill::factory()->create([
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->post(route('admin.bills.pay', $bill));

        $response->assertForbidden();
        $this->assertSame('draft', $bill->fresh()->status);
    }
}
