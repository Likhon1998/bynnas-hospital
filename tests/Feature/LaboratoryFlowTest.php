<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\LabOrder;
use App\Models\LabOrderEvent;
use App\Models\LabTest;
use App\Models\Visit;
use App\Models\VisitEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratoryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_orders_lab_records_result_and_reviews_it_on_the_encounter(): void
    {
        $doctor = $this->createStaffUser(['visits.view', 'visits.clinical', 'lab.view', 'lab.order'], 'doctor');
        $technician = $this->createStaffUser(['lab.view', 'lab.collect', 'lab.report'], 'lab_technician');
        $pathologist = $this->createStaffUser(['lab.view', 'lab.verify'], 'pathologist');
        $visit = Visit::factory()->withVitals()->create([
            'status' => 'consultation',
            'chief_complaint' => 'Chest pain since dawn',
        ]);
        $test = LabTest::factory()->create([
            'code' => 'TROP',
            'name' => 'Troponin I',
            'unit' => 'ng/mL',
            'reference_range' => '<0.04',
            'critical_high' => 0.5,
            'price' => 1800,
        ]);

        $this->actingAs($doctor)
            ->from(route('admin.visits.show', $visit))
            ->post(route('admin.labs.store'), [
                'visit_id' => $visit->id,
                'lab_test_id' => $test->id,
                'clinical_notes' => 'Chest pain since dawn',
            ])
            ->assertRedirect();

        $order = LabOrder::query()->first();
        $this->assertNotNull($order);
        $this->assertSame('ordered', $order->status);
        $this->assertSame($visit->id, $order->visit_id);
        $this->assertSame($visit->patient_id, $order->patient_id);
        $this->assertSame(1, BillItem::query()->where('item_type', 'lab')->where('item_id', $order->id)->count());
        $this->assertSame(1800.0, (float) Bill::query()->first()?->total);
        $this->assertDatabaseHas('visit_events', [
            'visit_id' => $visit->id,
            'event_type' => VisitEvent::TYPE_LAB_ORDERED,
        ]);

        $this->actingAs($technician)
            ->from(route('admin.labs.show', $order))
            ->post(route('admin.labs.collect', $order), [
                'collected_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();
        $order->refresh();
        $this->assertSame('collected', $order->status);
        $this->assertNotNull($order->sample_id);
        $this->assertSame($order->sample_id, $order->barcode);

        $this->actingAs($technician)
            ->from(route('admin.labs.show', $order))
            ->post(route('admin.labs.process', $order))
            ->assertRedirect();
        $this->assertSame('processing', $order->fresh()->status);

        $this->actingAs($technician)
            ->from(route('admin.labs.show', $order))
            ->post(route('admin.labs.results.store', $order), [
                'value' => '2.40',
                'unit' => 'ng/mL',
                'reference_range' => '<0.04',
                'notes' => 'Repeat in 6 hours',
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('pending_verification', $order->status);
        $this->assertSame('2.40', $order->result?->value);
        $this->assertTrue($order->result?->is_critical);
        $this->assertNull($order->result?->verified_by);
        $this->assertTrue($order->result?->isPreliminary());

        $this->actingAs($technician)
            ->from(route('admin.labs.show', $order))
            ->post(route('admin.labs.verify', $order))
            ->assertForbidden();

        $this->actingAs($pathologist)
            ->from(route('admin.labs.show', $order))
            ->post(route('admin.labs.verify', $order), [
                'value' => '2.40',
                'unit' => 'ng/mL',
                'reference_range' => '<0.04',
                'is_critical' => '1',
                'is_abnormal' => '1',
                'signature_name' => 'Dr. Path Demo',
                'signature_title' => 'Pathologist',
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('completed', $order->status);
        $this->assertTrue($order->is_critical);
        $this->assertSame($pathologist->id, $order->result?->verified_by);
        $this->assertTrue($order->result?->isPublished());
        $this->assertSame('Dr. Path Demo', $order->result?->signature_name);
        $this->assertSame([
            LabOrderEvent::TYPE_ORDERED,
            LabOrderEvent::TYPE_COLLECTED,
            LabOrderEvent::TYPE_PROCESSING,
            LabOrderEvent::TYPE_RESULT_SUBMITTED,
            LabOrderEvent::TYPE_COMPLETED,
        ], $order->events()->orderBy('id')->pluck('event_type')->all());

        $review = $this->actingAs($doctor)->get(route('admin.visits.show', $visit));
        $review->assertOk();
        $review->assertSee('Troponin I');
        $review->assertSee('2.40 ng/mL');
        $review->assertSee('Lab result verified');
        $review->assertSee('2.40 ng/mL (critical)');

        $this->actingAs($doctor)
            ->get(route('admin.notifications'))
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'Critical lab '.$order->order_no,
            ]);

        $this->actingAs($technician)
            ->from(route('admin.labs.show', $order))
            ->post(route('admin.labs.results.store', $order), [
                'value' => '0.02',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('value');
        $this->assertSame(1, BillItem::query()->where('item_type', 'lab')->where('item_id', $order->id)->count());
    }

    public function test_receptionist_cannot_order_a_lab_test(): void
    {
        $user = $this->createStaffUser(['visits.view', 'billing.manage'], 'receptionist');
        $visit = Visit::factory()->create();
        $test = LabTest::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.labs.store'), [
                'visit_id' => $visit->id,
                'lab_test_id' => $test->id,
            ])
            ->assertForbidden();
    }

    public function test_cancelling_an_unordered_collection_removes_the_draft_charge(): void
    {
        $doctor = $this->createStaffUser(['lab.view', 'lab.order'], 'doctor');
        $visit = Visit::factory()->create();
        $test = LabTest::factory()->create(['price' => 250]);

        $this->actingAs($doctor)->post(route('admin.labs.store'), [
            'visit_id' => $visit->id,
            'lab_test_id' => $test->id,
        ]);

        $order = LabOrder::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(1, Bill::query()->count());

        $this->actingAs($doctor)
            ->from(route('admin.labs.show', $order))
            ->post(route('admin.labs.cancel', $order), [
                'cancellation_reason' => 'Duplicate order',
            ])
            ->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(0, BillItem::query()->where('item_type', 'lab')->count());
        $this->assertSame(0, Bill::query()->count());
    }

    public function test_reception_bill_edit_keeps_the_lab_charge(): void
    {
        $doctor = $this->createStaffUser(['lab.view', 'lab.order'], 'doctor');
        $receptionist = $this->createStaffUser(['billing.manage'], 'receptionist');
        $visit = Visit::factory()->create();
        $test = LabTest::factory()->create(['name' => 'Serum Potassium', 'price' => 450]);

        $this->actingAs($doctor)->post(route('admin.labs.store'), [
            'visit_id' => $visit->id,
            'lab_test_id' => $test->id,
        ]);

        $bill = Bill::query()->first();
        $this->assertNotNull($bill);

        $this->actingAs($receptionist)->put(route('admin.bills.update', $bill), [
            'patient_id' => $visit->patient_id,
            'bill_date' => now()->toDateString(),
            'discount' => 0,
            'items' => [
                [
                    'item_type' => 'custom',
                    'description' => 'Consultation fee',
                    'quantity' => 1,
                    'unit_price' => 800,
                ],
            ],
        ])->assertRedirect();

        $bill->refresh();
        $this->assertSame(2, $bill->billItems()->count());
        $this->assertTrue($bill->billItems->contains(fn (BillItem $item): bool => $item->item_type === 'lab'));
        $this->assertSame(1250.0, (float) $bill->total);
    }

    public function test_escapes_lab_result_notes_on_the_encounter(): void
    {
        $technician = $this->createStaffUser(['lab.view', 'lab.collect', 'lab.report'], 'lab_technician');
        $pathologist = $this->createStaffUser(['lab.view', 'lab.verify'], 'pathologist');
        $doctor = $this->createStaffUser(['visits.view', 'lab.view'], 'doctor');
        $order = LabOrder::factory()->collected()->create();

        $this->actingAs($technician)->post(route('admin.labs.results.store', $order), [
            'value' => 'Positive',
            'notes' => "Alert <script>alert('xss')</script>",
        ])->assertRedirect();

        $this->actingAs($pathologist)->post(route('admin.labs.verify', $order), [
            'value' => 'Positive',
            'signature_name' => 'Pathologist',
            'signature_title' => 'Pathologist',
        ])->assertRedirect();

        $response = $this->actingAs($doctor)->get(route('admin.labs.show', $order));
        $response->assertOk();
        $this->assertStringContainsString('&lt;script&gt;', $response->getContent());
        $this->assertStringNotContainsString("<script>alert('xss')</script>", $response->getContent());
    }

    public function test_guest_is_redirected_from_the_lab_desk(): void
    {
        $this->get(route('admin.labs.index'))
            ->assertRedirect(route('admin.login'));
    }
}
