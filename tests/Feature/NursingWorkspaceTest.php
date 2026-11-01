<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\MedicationAdministration;
use App\Models\NursingAuditEvent;
use App\Models\NursingHandover;
use App\Models\NursingObservation;
use App\Models\NursingTask;
use App\Models\Patient;
use App\Models\Visit;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NursingWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_nurse_opens_a_ward_records_observations_and_hands_over_outstanding_work(): void
    {
        $nurse = $this->createStaffUser([
            'nursing.view',
            'nursing.chart',
            'nursing.handover',
            'nursing.meds',
        ], 'nurse_station');
        $bed = $this->admitPatient([
            'full_name' => 'Karim Mia (Demo)',
            'care_flags' => ['fall_risk', 'allergy_alert'],
            'allergies' => 'Penicillin',
        ], [
            'bed_code' => 'GEN-1-04',
            'admission_reason' => 'Respiratory support',
        ]);
        Visit::factory()->create([
            'patient_id' => $bed->patient_id,
            'status' => 'completed',
            'diagnosis' => 'Community-acquired pneumonia',
            'completed_at' => now()->subDay(),
        ]);
        NursingTask::factory()->forBed($bed)->create([
            'title' => 'Four-hourly observations',
            'task_type' => 'observation',
            'due_at' => now()->addHour(),
        ]);
        NursingTask::factory()->forBed($bed)->overdue()->create([
            'title' => 'Wound dressing change',
            'task_type' => 'wound_care',
        ]);

        $this->actingAs($nurse)
            ->get(route('admin.nursing.wards.show', $bed->ward))
            ->assertOk()
            ->assertSee('Karim Mia (Demo)')
            ->assertSee('GEN-1-04')
            ->assertSee('Fall risk')
            ->assertSee('Penicillin')
            ->assertSee('Community-acquired pneumonia')
            ->assertSee('Four-hourly observations')
            ->assertSee('Wound dressing change')
            ->assertSee('Overdue');

        $this->actingAs($nurse)
            ->from(route('admin.nursing.patients.show', $bed->patient))
            ->post(route('admin.nursing.observations.store', $bed->patient), [
                'observation_type' => 'vitals',
                'blood_pressure' => '118/76',
                'temperature' => '37.4',
                'pulse' => '82',
                'oxygen_saturation' => '97',
                'weight' => '61.5',
                'pain_score' => '3',
                'notes' => 'Breathing easier after nebuliser',
            ])
            ->assertRedirect(route('admin.nursing.patients.show', $bed->patient));

        $observation = NursingObservation::query()->first();
        $this->assertNotNull($observation);
        $this->assertSame('118/76', $observation->blood_pressure);
        $this->assertSame(82, $observation->pulse);
        $this->assertSame($nurse->id, $observation->recorded_by);
        $this->assertNotNull($observation->recorded_at);

        $this->actingAs($nurse)
            ->from(route('admin.nursing.wards.show', $bed->ward))
            ->post(route('admin.nursing.handovers.store', $bed->ward), [
                'shift' => 'morning',
                'incoming_shift' => 'evening',
                'summary' => 'Karim is stable. Wound care is still outstanding.',
            ])
            ->assertRedirect(route('admin.nursing.wards.show', $bed->ward));

        $handover = NursingHandover::query()->first();
        $this->assertNotNull($handover);
        $this->assertSame($nurse->id, $handover->from_user_id);
        $this->assertSame('morning', $handover->shift);
        $this->assertSame('evening', $handover->incoming_shift);
        $this->assertNotNull($handover->handed_over_at);
        $this->assertStringContainsString('Wound dressing change', (string) $handover->outstanding_work);
        $this->assertStringContainsString('Four-hourly observations', (string) $handover->outstanding_work);

        $this->assertDatabaseHas('nursing_audit_events', [
            'patient_id' => $bed->patient_id,
            'actor_user_id' => $nurse->id,
            'action' => NursingAuditEvent::ACTION_RECORDED,
        ]);
        $this->assertDatabaseHas('nursing_audit_events', [
            'ward_id' => $bed->ward_id,
            'actor_user_id' => $nurse->id,
            'action' => NursingAuditEvent::ACTION_HANDED_OVER,
        ]);

        $this->actingAs($nurse)
            ->get(route('admin.nursing.wards.show', $bed->ward))
            ->assertOk()
            ->assertSee('Karim is stable. Wound care is still outstanding.')
            ->assertSee('Morning')
            ->assertSee('Evening');
    }

    public function test_guest_is_redirected_from_the_nursing_workspace(): void
    {
        $this->get(route('admin.nursing.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_staff_without_nursing_permission_cannot_open_the_workspace(): void
    {
        $user = $this->createStaffUser(['operations.manage', 'beds.view'], 'receptionist');
        $ward = Ward::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.nursing.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.nursing.wards.show', $ward))
            ->assertForbidden();
    }

    public function test_doctor_cannot_administer_medication(): void
    {
        $doctor = $this->createStaffUser(['nursing.view', 'nursing.chart'], 'doctor');
        $bed = $this->admitPatient();

        $this->actingAs($doctor)
            ->post(route('admin.nursing.medications.store', $bed->patient), [
                'medication_name' => 'Paracetamol',
                'dose' => '500mg',
                'route' => 'oral',
                'status' => 'given',
            ])
            ->assertForbidden();

        $this->assertSame(0, MedicationAdministration::query()->count());
    }

    public function test_nurse_medication_record_is_timestamped_and_audited(): void
    {
        $nurse = $this->createStaffUser(['nursing.view', 'nursing.meds'], 'nurse_station');
        $bed = $this->admitPatient();

        $this->actingAs($nurse)
            ->from(route('admin.nursing.patients.show', $bed->patient))
            ->post(route('admin.nursing.medications.store', $bed->patient), [
                'medication_name' => 'Amoxicillin',
                'dose' => '500mg',
                'route' => 'oral',
                'status' => 'given',
                'notes' => 'Given with food',
            ])
            ->assertRedirect();

        $record = MedicationAdministration::query()->first();
        $this->assertNotNull($record);
        $this->assertSame('Amoxicillin', $record->medication_name);
        $this->assertSame($nurse->id, $record->administered_by);
        $this->assertNotNull($record->administered_at);
        $this->assertDatabaseHas('nursing_audit_events', [
            'patient_id' => $bed->patient_id,
            'actor_user_id' => $nurse->id,
            'action' => NursingAuditEvent::ACTION_ADMINISTERED,
            'auditable_id' => $record->id,
        ]);
    }

    public function test_nurse_can_complete_an_open_task(): void
    {
        $nurse = $this->createStaffUser(['nursing.view', 'nursing.chart'], 'nurse_station');
        $bed = $this->admitPatient();
        $task = NursingTask::factory()->forBed($bed)->create([
            'title' => 'Fluid balance chart',
            'status' => 'pending',
        ]);

        $this->actingAs($nurse)
            ->from(route('admin.nursing.patients.show', $bed->patient))
            ->patch(route('admin.nursing.tasks.update', $task), [
                'status' => 'completed',
            ])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame('completed', $task->status);
        $this->assertSame($nurse->id, $task->completed_by);
        $this->assertNotNull($task->completed_at);
        $this->assertDatabaseHas('nursing_audit_events', [
            'auditable_id' => $task->id,
            'action' => NursingAuditEvent::ACTION_UPDATED,
            'actor_user_id' => $nurse->id,
        ]);
    }

    public function test_chart_is_not_found_when_the_patient_is_not_admitted(): void
    {
        $nurse = $this->createStaffUser(['nursing.view'], 'nurse_station');
        $patient = Patient::factory()->create();

        $this->actingAs($nurse)
            ->get(route('admin.nursing.patients.show', $patient))
            ->assertNotFound();
    }

    public function test_observation_validation_rejects_an_invalid_blood_pressure(): void
    {
        $nurse = $this->createStaffUser(['nursing.chart'], 'nurse_station');
        $bed = $this->admitPatient();

        $this->actingAs($nurse)
            ->from(route('admin.nursing.patients.show', $bed->patient))
            ->post(route('admin.nursing.observations.store', $bed->patient), [
                'observation_type' => 'vitals',
                'blood_pressure' => 'high',
                'temperature' => '37.0',
                'pulse' => '70',
                'oxygen_saturation' => '98',
                'pain_score' => '1',
            ])
            ->assertRedirect(route('admin.nursing.patients.show', $bed->patient))
            ->assertSessionHasErrors('blood_pressure');
    }

    public function test_escapes_observation_notes_on_the_chart(): void
    {
        $nurse = $this->createStaffUser(['nursing.view', 'nursing.chart'], 'nurse_station');
        $bed = $this->admitPatient();

        $this->actingAs($nurse)->post(route('admin.nursing.observations.store', $bed->patient), [
            'observation_type' => 'general',
            'notes' => "Alert <script>alert('xss')</script>",
        ])->assertRedirect();

        $response = $this->actingAs($nurse)->get(route('admin.nursing.patients.show', $bed->patient));

        $response->assertOk();
        $this->assertStringContainsString('&lt;script&gt;', $response->getContent());
        $this->assertStringNotContainsString("<script>alert('xss')</script>", $response->getContent());
    }

    /**
     * @param  array<string, mixed>  $patientAttributes
     * @param  array<string, mixed>  $bedAttributes
     */
    private function admitPatient(array $patientAttributes = [], array $bedAttributes = []): Bed
    {
        $ward = Ward::factory()->create([
            'name' => 'General 1',
            'code' => 'GEN-1',
            'type' => 'general',
        ]);
        $patient = Patient::factory()->create($patientAttributes);

        return Bed::factory()->occupied()->create(array_merge([
            'ward_id' => $ward->id,
            'patient_id' => $patient->id,
            'bed_code' => 'GEN-1-04',
            'admission_reason' => 'Observation',
        ], $bedAttributes));
    }
}
