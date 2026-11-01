<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Visit;
use App\Models\VisitEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitEncounterTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptionist_checks_in_nurse_records_vitals_and_doctor_completes_encounter(): void
    {
        $receptionist = $this->createStaffUser(['visits.view', 'visits.checkin'], 'receptionist');
        $nurse = $this->createStaffUser(['visits.view', 'visits.triage'], 'nurse_station');
        $doctor = $this->createStaffUser(['visits.view', 'visits.clinical', 'patients.view'], 'doctor');
        $appointment = Appointment::factory()->create([
            'status' => 'confirmed',
            'reason' => 'Fever since last night',
        ]);

        $checkIn = $this->actingAs($receptionist)
            ->from(route('admin.appointments.show', $appointment))
            ->post(route('admin.appointments.check-in', $appointment));

        $visit = Visit::query()->first();
        $this->assertNotNull($visit);
        $checkIn->assertRedirect(route('admin.visits.show', $visit));
        $this->assertSame('waiting', $visit->status);
        $this->assertSame($appointment->patient_id, $visit->patient_id);
        $this->assertSame($appointment->id, $visit->appointment_id);
        $this->assertNotNull($visit->arrived_at);
        $this->assertDatabaseHas('visit_events', [
            'visit_id' => $visit->id,
            'event_type' => VisitEvent::TYPE_CHECKED_IN,
            'actor_user_id' => $receptionist->id,
        ]);

        $this->actingAs($nurse)
            ->from(route('admin.visits.show', $visit))
            ->post(route('admin.visits.vitals', $visit), [
                'blood_pressure' => '118/76',
                'temperature' => '37.4',
                'pulse' => '82',
                'oxygen_saturation' => '97',
                'weight' => '61.5',
                'pain_score' => '3',
            ])
            ->assertRedirect(route('admin.visits.show', $visit));

        $visit->refresh();
        $this->assertSame('triage', $visit->status);
        $this->assertSame('118/76', $visit->blood_pressure);
        $this->assertSame(82, $visit->pulse);
        $this->assertSame(3, $visit->pain_score);
        $this->assertTrue($visit->hasVitals());
        $this->assertDatabaseHas('visit_events', [
            'visit_id' => $visit->id,
            'event_type' => VisitEvent::TYPE_VITALS_RECORDED,
            'actor_user_id' => $nurse->id,
        ]);

        $this->actingAs($doctor)
            ->from(route('admin.visits.show', $visit))
            ->post(route('admin.visits.clinical', $visit), [
                'symptoms' => 'Fever and dry cough',
                'examination_notes' => 'Chest clear, no distress',
                'diagnosis' => 'Viral upper respiratory infection',
                'treatment_plan' => 'Rest, fluids, paracetamol',
                'prescriptions' => 'Paracetamol 500mg three times daily for 3 days',
                'follow_up_instructions' => 'Return if fever lasts more than 3 days',
                'complete' => '1',
            ])
            ->assertRedirect(route('admin.visits.show', $visit));

        $visit->refresh();
        $this->assertSame('completed', $visit->status);
        $this->assertSame('Viral upper respiratory infection', $visit->diagnosis);
        $this->assertSame('completed', $appointment->fresh()->status);
        $this->assertSame(4, $visit->events()->count());
        $this->assertSame([
            VisitEvent::TYPE_CHECKED_IN,
            VisitEvent::TYPE_VITALS_RECORDED,
            VisitEvent::TYPE_CONSULTATION_STARTED,
            VisitEvent::TYPE_COMPLETED,
        ], $visit->events()->orderBy('id')->pluck('event_type')->all());

        $this->actingAs($doctor)
            ->get(route('admin.patients.show', $appointment->patient))
            ->assertOk()
            ->assertSee('Encounter history')
            ->assertSee($visit->visit_no)
            ->assertSee('Checked in')
            ->assertSee('Vitals recorded')
            ->assertSee('Encounter completed');
    }

    public function test_staff_without_checkin_permission_cannot_check_in(): void
    {
        $user = $this->createStaffUser(['visits.view'], 'doctor');
        $appointment = Appointment::factory()->create(['status' => 'confirmed']);

        $this->actingAs($user)
            ->post(route('admin.appointments.check-in', $appointment))
            ->assertForbidden();

        $this->assertSame(0, Visit::query()->count());
    }

    public function test_nurse_cannot_complete_the_encounter(): void
    {
        $nurse = $this->createStaffUser(['visits.view', 'visits.triage'], 'nurse_station');
        $visit = Visit::factory()->withVitals()->create();

        $this->actingAs($nurse)
            ->post(route('admin.visits.clinical', $visit), [
                'diagnosis' => 'Should not save',
                'treatment_plan' => 'Should not save',
                'complete' => '1',
            ])
            ->assertForbidden();

        $this->assertSame('triage', $visit->fresh()->status);
    }

    public function test_doctor_cannot_complete_until_vitals_are_recorded(): void
    {
        $doctor = $this->createStaffUser(['visits.view', 'visits.clinical'], 'doctor');
        $visit = Visit::factory()->waiting()->create();

        $this->actingAs($doctor)
            ->from(route('admin.visits.show', $visit))
            ->post(route('admin.visits.clinical', $visit), [
                'symptoms' => 'Headache',
                'diagnosis' => 'Migraine',
                'treatment_plan' => 'Analgesia',
                'complete' => '1',
            ])
            ->assertRedirect(route('admin.visits.show', $visit))
            ->assertSessionHasErrors('diagnosis');

        $this->assertSame('waiting', $visit->fresh()->status);
    }

    public function test_cancelled_appointment_cannot_be_checked_in(): void
    {
        $user = $this->createStaffUser(['visits.checkin']);
        $appointment = Appointment::factory()->create(['status' => 'cancelled']);

        $this->actingAs($user)
            ->from(route('admin.appointments.show', $appointment))
            ->post(route('admin.appointments.check-in', $appointment))
            ->assertRedirect(route('admin.appointments.show', $appointment))
            ->assertSessionHasErrors('appointment');
    }

    public function test_patient_cannot_have_two_open_encounters(): void
    {
        $user = $this->createStaffUser(['visits.checkin']);
        $patient = Patient::factory()->create();
        Visit::factory()->waiting()->create(['patient_id' => $patient->id]);

        $this->actingAs($user)
            ->from(route('admin.visits.create'))
            ->post(route('admin.visits.store'), [
                'patient_id' => $patient->id,
                'chief_complaint' => 'Second arrival',
            ])
            ->assertRedirect(route('admin.visits.create'))
            ->assertSessionHasErrors('patient_id');

        $this->assertSame(1, Visit::query()->count());
    }

    public function test_vitals_validation_rejects_an_invalid_blood_pressure(): void
    {
        $nurse = $this->createStaffUser(['visits.triage']);
        $visit = Visit::factory()->waiting()->create();

        $this->actingAs($nurse)
            ->from(route('admin.visits.show', $visit))
            ->post(route('admin.visits.vitals', $visit), [
                'blood_pressure' => 'high',
                'temperature' => '37.0',
                'pulse' => '70',
                'oxygen_saturation' => '98',
                'weight' => '60',
                'pain_score' => '1',
            ])
            ->assertRedirect(route('admin.visits.show', $visit))
            ->assertSessionHasErrors('blood_pressure');
    }

    public function test_escapes_diagnosis_on_the_completed_encounter(): void
    {
        $doctor = $this->createStaffUser(['visits.view', 'visits.clinical']);
        $visit = Visit::factory()->withVitals()->create();

        $this->actingAs($doctor)->post(route('admin.visits.clinical', $visit), [
            'symptoms' => 'Cough',
            'diagnosis' => "Asthma <script>alert('xss')</script>",
            'treatment_plan' => 'Inhaler',
            'complete' => '1',
        ])->assertRedirect();

        $response = $this->actingAs($doctor)->get(route('admin.visits.show', $visit));

        $response->assertOk();
        $this->assertStringContainsString('&lt;script&gt;', $response->getContent());
        $this->assertStringNotContainsString("<script>alert('xss')</script>", $response->getContent());
    }
}
