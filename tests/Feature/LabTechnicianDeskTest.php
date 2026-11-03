<?php

namespace Tests\Feature;

use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabTechnicianDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_lab_technician_dashboard_shows_laboratory_desk_widgets(): void
    {
        $user = $this->createStaffUser([
            'patients.view',
            'visits.view',
            'lab.view',
            'lab.collect',
            'lab.report',
        ], 'lab_technician');

        $ordered = LabOrder::factory()->create(['status' => 'ordered', 'created_at' => now()]);
        LabOrder::factory()->collected()->create();
        LabOrder::factory()->processing()->create();
        $pending = LabOrder::factory()->processing()->create(['status' => 'pending_verification', 'submitted_for_verification_at' => now()]);
        LabResult::factory()->create([
            'lab_order_id' => $pending->id,
            'patient_id' => $pending->patient_id,
            'visit_id' => $pending->visit_id,
            'verified_by' => null,
            'verified_at' => null,
        ]);
        LabOrder::factory()->create([
            'status' => 'completed',
            'completed_at' => now(),
            'is_critical' => false,
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Today&#039;s Lab Orders', false);
        $response->assertSee('Pending Samples', false);
        $response->assertSee('Collected Samples', false);
        $response->assertSee('Tests in Progress', false);
        $response->assertSee('Pending Results', false);
        $response->assertSee('Completed Tests', false);
        $response->assertSee($ordered->order_no, false);
        $response->assertDontSee(route('admin.owner.finance.index'), false);
        $response->assertDontSee(route('admin.reports'), false);
    }

    public function test_technician_collects_sample_with_id_and_prints_worksheet(): void
    {
        $user = $this->createStaffUser(['lab.view', 'lab.collect', 'lab.report'], 'lab_technician');
        $order = LabOrder::factory()->create(['status' => 'ordered']);

        $this->actingAs($user)
            ->post(route('admin.labs.collect', $order), [
                'sample_id' => 'SMP-DEMO-001',
                'barcode' => 'BC-DEMO-001',
                'collected_at' => '2026-11-03 09:30:00',
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('collected', $order->status);
        $this->assertSame('SMP-DEMO-001', $order->sample_id);
        $this->assertSame('BC-DEMO-001', $order->barcode);
        $this->assertSame('2026-11-03 09:30:00', $order->collected_at?->format('Y-m-d H:i:s'));

        $this->actingAs($user)
            ->get(route('admin.labs.worksheet', $order))
            ->assertOk()
            ->assertSee('Laboratory worksheet', false)
            ->assertSee('SMP-DEMO-001', false)
            ->assertSee('BC-DEMO-001', false);
    }

    public function test_technician_cannot_verify_or_modify_verified_results_or_prescriptions(): void
    {
        $technician = $this->createStaffUser(['lab.view', 'lab.collect', 'lab.report', 'visits.view'], 'lab_technician');
        $pathologist = $this->createStaffUser(['lab.view', 'lab.verify'], 'pathologist');
        $visit = Visit::factory()->withVitals()->create(['status' => 'consultation', 'prescriptions' => 'Amoxicillin 500mg']);
        $order = LabOrder::factory()->collected()->create([
            'visit_id' => $visit->id,
            'patient_id' => $visit->patient_id,
        ]);

        $this->actingAs($technician)->post(route('admin.labs.results.store', $order), [
            'value' => '4.2',
            'unit' => 'mmol/L',
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame('pending_verification', $order->status);

        $this->actingAs($technician)
            ->post(route('admin.labs.verify', $order))
            ->assertForbidden();

        $this->actingAs($pathologist)
            ->post(route('admin.labs.verify', $order))
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('completed', $order->status);
        $this->assertTrue($order->result?->isVerified());

        $this->actingAs($technician)
            ->post(route('admin.labs.results.store', $order), ['value' => '9.9'])
            ->assertRedirect()
            ->assertSessionHasErrors('value');

        $this->assertSame('4.2', $order->fresh()->result?->value);
        $this->assertFalse($technician->can('lab.verify'));
        $this->assertFalse($technician->can('visits.clinical'));

        $this->actingAs($technician)
            ->post(route('admin.visits.clinical', $visit), [
                'symptoms' => 'Cough',
                'prescriptions' => 'Changed by lab',
                'complete' => '0',
            ])
            ->assertForbidden();

        $this->assertSame('Amoxicillin 500mg', $visit->fresh()->prescriptions);
    }
}
