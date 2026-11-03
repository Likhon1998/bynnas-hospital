<?php

namespace Tests\Feature;

use App\Models\LabOrder;
use App\Models\LabOrderEvent;
use App\Models\LabResult;
use App\Models\LabTest;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PathologistDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_pathologist_dashboard_shows_verification_desk_widgets(): void
    {
        $user = $this->createStaffUser([
            'patients.view',
            'visits.view',
            'lab.view',
            'lab.report',
            'lab.verify',
        ], 'pathologist');

        $pending = LabOrder::factory()->processing()->create([
            'status' => 'pending_verification',
            'is_critical' => true,
            'submitted_for_verification_at' => now(),
        ]);
        LabResult::factory()->create([
            'lab_order_id' => $pending->id,
            'patient_id' => $pending->patient_id,
            'visit_id' => $pending->visit_id,
            'is_critical' => true,
            'is_abnormal' => true,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        $imagingTest = LabTest::factory()->create(['category' => 'radiology', 'name' => 'Chest X-Ray PA']);
        LabOrder::factory()->create([
            'lab_test_id' => $imagingTest->id,
            'status' => 'ordered',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Pending Report Verification', false);
        $response->assertSee('Abnormal Results', false);
        $response->assertSee('Urgent Reports', false);
        $response->assertSee('Approved Reports', false);
        $response->assertSee('Today&#039;s Laboratory Reports', false);
        $response->assertSee('Imaging requests', false);
        $response->assertSee('Chest X-Ray PA', false);
        $response->assertDontSee(route('admin.owner.finance.index'), false);
        $response->assertDontSee(route('admin.bills.index'), false);
    }

    public function test_pathologist_approves_signs_publishes_and_can_return_for_correction(): void
    {
        $technician = $this->createStaffUser(['lab.view', 'lab.collect', 'lab.report'], 'lab_technician');
        $pathologist = $this->createStaffUser(['lab.view', 'lab.report', 'lab.verify'], 'pathologist');
        $order = LabOrder::factory()->collected()->create();

        $this->actingAs($technician)->post(route('admin.labs.results.store', $order), [
            'value' => '6.8',
            'unit' => 'mmol/L',
            'reference_range' => '3.5–5.1',
            'is_abnormal' => '1',
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame('pending_verification', $order->status);
        $this->assertTrue($order->result?->isPreliminary());

        $this->actingAs($pathologist)
            ->post(route('admin.labs.return', $order), [
                'return_reason' => 'Recheck hemolysis',
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('processing', $order->status);
        $this->assertNull($order->result);
        $this->assertSame(
            LabOrderEvent::TYPE_RETURNED,
            $order->events()->reorder()->orderByDesc('id')->value('event_type'),
        );

        $this->actingAs($technician)->post(route('admin.labs.results.store', $order), [
            'value' => '6.9',
            'unit' => 'mmol/L',
            'is_abnormal' => '1',
            'is_critical' => '1',
        ])->assertRedirect();

        $this->actingAs($pathologist)->post(route('admin.labs.verify', $order), [
            'value' => '6.9',
            'unit' => 'mmol/L',
            'reference_range' => '3.5–5.1',
            'is_abnormal' => '1',
            'is_critical' => '1',
            'signature_name' => 'Dr. Verifier',
            'signature_title' => 'Pathologist',
            'findings' => 'Significant hyperkalemia',
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame('completed', $order->status);
        $this->assertTrue($order->result?->isPublished());
        $this->assertSame('Dr. Verifier', $order->result?->signature_name);
        $this->assertTrue($order->result?->is_abnormal);

        $this->actingAs($pathologist)
            ->get(route('admin.labs.report', $order))
            ->assertOk()
            ->assertSee('Final laboratory report', false)
            ->assertSee('Dr. Verifier', false)
            ->assertSee('Significant hyperkalemia', false);
    }

    public function test_radiologist_imaging_report_and_final_report_lock(): void
    {
        $radiologist = $this->createStaffUser(['lab.view', 'lab.report', 'lab.verify'], 'radiologist');
        $other = $this->createStaffUser(['lab.view', 'lab.report', 'lab.verify'], 'pathologist');
        $visit = Visit::factory()->create(['status' => 'consultation']);
        $test = LabTest::factory()->create([
            'category' => 'imaging',
            'name' => 'Ultrasound Whole Abdomen',
            'code' => 'USG-ABD',
        ]);
        $order = LabOrder::factory()->collected()->create([
            'lab_test_id' => $test->id,
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient_id,
        ]);

        $this->actingAs($radiologist)->post(route('admin.labs.results.store', $order), [
            'value' => 'Mild fatty liver',
            'findings' => 'Liver echogenicity increased. No focal lesion.',
        ])->assertRedirect();

        $this->actingAs($radiologist)->post(route('admin.labs.verify', $order), [
            'value' => 'Mild fatty liver',
            'findings' => 'Liver echogenicity increased. No focal lesion.',
            'signature_name' => 'Dr. Radio',
            'signature_title' => 'Radiologist',
        ])->assertRedirect();

        $order->refresh();
        $this->assertTrue($order->result?->isPublished());
        $this->assertTrue($order->isImagingStudy());

        $this->actingAs($other)
            ->post(route('admin.labs.results.store', $order), [
                'value' => 'Changed without authority',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors();

        $this->assertSame('Mild fatty liver', $order->fresh()->result?->value);
        $this->assertFalse($radiologist->can('billing.manage'));

        $this->actingAs($radiologist)
            ->get(route('admin.bills.index'))
            ->assertForbidden();
    }

    public function test_unpublished_preliminary_results_are_hidden_from_clinician_encounter(): void
    {
        $doctor = $this->createStaffUser(['visits.view', 'lab.view'], 'doctor');
        $technician = $this->createStaffUser(['lab.view', 'lab.report'], 'lab_technician');
        $visit = Visit::factory()->create(['status' => 'consultation']);
        $order = LabOrder::factory()->collected()->create([
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient_id,
        ]);

        $this->actingAs($technician)->post(route('admin.labs.results.store', $order), [
            'value' => 'SECRET-PRELIM',
        ])->assertRedirect();

        $this->actingAs($doctor)
            ->get(route('admin.visits.show', $visit))
            ->assertOk()
            ->assertSee('awaiting pathologist approval', false)
            ->assertDontSee('SECRET-PRELIM', false);
    }
}
