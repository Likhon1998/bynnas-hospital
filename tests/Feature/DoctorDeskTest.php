<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_dashboard_shows_clinical_desk_widgets(): void
    {
        $user = $this->createStaffUser([
            'appointments.view',
            'appointments.manage',
            'patients.view',
            'visits.view',
            'visits.clinical',
            'lab.view',
            'lab.order',
            'admissions.view',
            'admissions.admit',
        ], 'doctor');

        $doctor = Doctor::factory()->create(['user_id' => $user->id, 'is_active' => true]);
        $patient = Patient::factory()->create();

        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'appointment_date' => today()->toDateString(),
            'status' => 'confirmed',
        ]);

        Visit::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => 'waiting',
            'arrived_at' => now(),
        ]);

        Visit::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'status' => 'completed',
            'completed_at' => now(),
            'follow_up_instructions' => 'Review in 1 week',
            'diagnosis' => 'Follow-up case',
        ]);

        $response = $this->actingAs($user->fresh())->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Doctor Desk', false);
        $response->assertSee('Today&#039;s Appointments', false);
        $response->assertSee('Today&#039;s Patient Queue', false);
        $response->assertSee('Waiting Patients', false);
        $response->assertSee('Completed Consultations', false);
        $response->assertSee('Follow-up Patients', false);
        $response->assertSee('Pending Laboratory Reports', false);
        $response->assertSee(route('admin.admissions.create'), false);
        $response->assertDontSee(route('admin.reports'), false);
        $response->assertDontSee(route('admin.owner.finance.index'), false);
    }

    public function test_doctor_cannot_edit_another_doctors_prescription(): void
    {
        $alphaUser = $this->createStaffUser(['visits.view', 'visits.clinical'], 'doctor');
        $betaUser = $this->createStaffUser(['visits.view', 'visits.clinical'], 'doctor');
        $alpha = Doctor::factory()->create(['user_id' => $alphaUser->id]);
        $beta = Doctor::factory()->create(['user_id' => $betaUser->id]);

        $visit = Visit::factory()->create([
            'doctor_id' => $alpha->id,
            'status' => 'consultation',
            'prescriptions' => 'Amoxicillin 500mg',
            'clinically_recorded_by' => $alphaUser->id,
            'clinically_recorded_at' => now(),
        ]);

        $this->actingAs($betaUser->fresh())
            ->post(route('admin.visits.clinical', $visit), [
                'symptoms' => 'Cough',
                'prescriptions' => 'Changed without permission',
                'complete' => '0',
            ])
            ->assertForbidden();

        $this->assertSame('Amoxicillin 500mg', $visit->fresh()->prescriptions);
    }

    public function test_doctor_can_schedule_follow_up_and_recommend_admission(): void
    {
        $user = $this->createStaffUser([
            'appointments.view',
            'appointments.manage',
            'patients.view',
            'admissions.view',
            'admissions.admit',
        ], 'doctor');
        Doctor::factory()->create(['user_id' => $user->id, 'is_active' => true]);

        $this->actingAs($user->fresh())->get(route('admin.appointments.create'))
            ->assertOk();

        $this->actingAs($user->fresh())->get(route('admin.admissions.create'))
            ->assertOk();
    }

    public function test_doctor_cannot_delete_patients_or_open_finance_reports(): void
    {
        $user = $this->createStaffUser([
            'patients.view',
            'appointments.view',
            'visits.view',
        ], 'doctor');

        $patient = Patient::factory()->create();

        $this->actingAs($user)->get(route('admin.reports'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.owner.finance.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.patients.edit', $patient))->assertForbidden();
        $this->assertFalse($user->can('patients.manage'));
        $this->assertFalse($user->can('billing.manage'));
        $this->assertFalse($user->can('operations.manage'));
    }
}
