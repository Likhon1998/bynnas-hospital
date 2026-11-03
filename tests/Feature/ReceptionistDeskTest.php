<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\FinanceApproval;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceptionistDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptionist_dashboard_shows_front_desk_widgets(): void
    {
        $user = $this->createStaffUser([
            'appointments.view',
            'appointments.manage',
            'patients.view',
            'patients.manage',
            'billing.manage',
            'beds.view',
            'visits.view',
            'visits.checkin',
            'lab.view',
        ], 'receptionist');

        Patient::factory()->create();
        Appointment::factory()->create([
            'appointment_date' => today()->toDateString(),
            'status' => 'confirmed',
            'serial_no' => 1,
        ]);
        Visit::factory()->create(['status' => 'waiting']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Reception Desk', false);
        $response->assertSee('Today&#039;s Appointments', false);
        $response->assertSee('Waiting Patients', false);
        $response->assertSee('Registered Patients', false);
        $response->assertSee('Doctor Schedule', false);
        $response->assertSee('Pending Serial Numbers', false);
        $response->assertSee('Patient Search', false);
        $response->assertSee(route('admin.patients.index'), false);
        $response->assertDontSee(route('admin.reports'), false);
    }

    public function test_receptionist_registers_patient_with_unique_id_and_prints_slip(): void
    {
        $user = $this->createStaffUser(['patients.view', 'patients.manage'], 'receptionist');

        $response = $this->actingAs($user)->post(route('admin.patients.store'), [
            'full_name' => 'Reception Patient',
            'phone' => '01700111222',
            'email' => null,
            'date_of_birth' => '1990-05-01',
            'gender' => 'female',
            'address' => 'Dhaka',
            'emergency_contact_name' => null,
            'emergency_contact_phone' => null,
            'is_active' => '1',
        ]);

        $patient = Patient::query()->where('full_name', 'Reception Patient')->firstOrFail();
        $this->assertNotEmpty($patient->patient_code);
        $response->assertRedirect(route('admin.patients.show', $patient));

        $this->actingAs($user)->get(route('admin.patients.print', $patient))
            ->assertOk()
            ->assertSee($patient->patient_code, false)
            ->assertSee('Patient slip', false);
    }

    public function test_confirmed_appointment_gets_doctor_serial_and_opd_ticket(): void
    {
        $this->travelTo('2026-11-03 08:00:00');

        $user = $this->createStaffUser([
            'appointments.view',
            'appointments.manage',
            'patients.view',
        ], 'receptionist');

        $doctor = Doctor::factory()->create();
        $patient = Patient::factory()->create();

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'appointment_date' => '2026-11-03',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'pending',
            'serial_no' => null,
            'source' => 'web',
        ]);

        $this->actingAs($user)->put(route('admin.appointments.update', $appointment), [
            'status' => 'confirmed',
            'appointment_date' => '2026-11-03',
            'start_time' => '09:00',
        ])->assertRedirect(route('admin.appointments.show', $appointment));

        $appointment->refresh();
        $this->assertSame(1, (int) $appointment->serial_no);

        $this->actingAs($user)->get(route('admin.appointments.print', $appointment))
            ->assertOk()
            ->assertSee('OPD ticket', false)
            ->assertSee($appointment->serialLabel(), false);
    }

    public function test_receptionist_cannot_access_reports_or_approve_refunds_or_edit_clinical_labs(): void
    {
        $user = $this->createStaffUser([
            'appointments.view',
            'patients.view',
            'billing.manage',
            'lab.view',
            'visits.view',
        ], 'receptionist');

        $this->actingAs($user)->get(route('admin.reports'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.owner.finance.index'))->assertForbidden();

        $approval = FinanceApproval::factory()->create([
            'status' => FinanceApproval::STATUS_PENDING,
            'type' => FinanceApproval::TYPE_REFUND,
        ]);

        $this->actingAs($user)->post(route('admin.owner.approvals.decide', $approval), [
            'decision' => 'approved',
        ])->assertForbidden();

        $this->assertFalse($user->can('visits.clinical'));
        $this->assertFalse($user->can('lab.report'));
        $this->assertFalse($user->can('finance.approve'));
    }
}
