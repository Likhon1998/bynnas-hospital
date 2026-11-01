<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\AdmissionEvent;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\BedEvent;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_er_patient_is_admitted_transferred_discharged_and_bed_opens_after_cleaning(): void
    {
        $receptionist = $this->createStaffUser(['admissions.view', 'admissions.admit', 'beds.view', 'beds.operate'], 'receptionist');
        $doctor = $this->createStaffUser(['admissions.view', 'admissions.discharge'], 'doctor');
        $icu = Ward::factory()->create(['name' => 'ICU A', 'code' => 'ICU-A', 'type' => 'icu']);
        $general = Ward::factory()->create(['name' => 'General 1', 'code' => 'GEN-1', 'type' => 'general']);
        $fromBed = Bed::factory()->create(['ward_id' => $icu->id, 'bed_code' => 'ICU-A-04', 'status' => 'available']);
        $toBed = Bed::factory()->create(['ward_id' => $general->id, 'bed_code' => 'GEN-1-08', 'status' => 'available']);
        $attending = Doctor::factory()->create(['name' => 'Dr. Arif Rahman']);
        $appointment = Appointment::factory()->create([
            'source' => 'emergency',
            'status' => 'confirmed',
            'reason' => 'Chest pain since dawn',
        ]);

        $this->actingAs($receptionist)
            ->from(route('admin.admissions.create', ['source' => 'emergency', 'appointment_id' => $appointment->id]))
            ->post(route('admin.admissions.store'), [
                'patient_id' => $appointment->patient_id,
                'bed_id' => $fromBed->id,
                'source' => 'emergency',
                'appointment_id' => $appointment->id,
                'attending_doctor_id' => $attending->id,
                'admission_reason' => 'Chest pain since dawn',
            ])
            ->assertRedirect();

        $admission = Admission::query()->first();
        $this->assertNotNull($admission);
        $this->assertSame('emergency', $admission->source);
        $this->assertSame($fromBed->id, $admission->bed_id);
        $this->assertSame('occupied', $fromBed->fresh()->status);
        $this->assertSame($appointment->patient_id, $fromBed->fresh()->patient_id);

        $this->actingAs($receptionist)
            ->from(route('admin.admissions.show', $admission))
            ->post(route('admin.admissions.transfers.store', $admission), [
                'to_bed_id' => $toBed->id,
                'reason' => 'Step-down to general ward',
            ])
            ->assertRedirect();

        $admission->refresh();
        $this->assertSame($toBed->id, $admission->bed_id);
        $this->assertSame('dirty', $fromBed->fresh()->status);
        $this->assertSame('occupied', $toBed->fresh()->status);
        $this->assertDatabaseHas('bed_transfers', [
            'admission_id' => $admission->id,
            'from_bed_id' => $fromBed->id,
            'to_bed_id' => $toBed->id,
            'reason' => 'Step-down to general ward',
        ]);

        $this->actingAs($receptionist)
            ->from(route('admin.admissions.show', $admission))
            ->post(route('admin.admissions.discharge', $admission))
            ->assertRedirect()
            ->assertSessionHasErrors('status');
        $this->assertSame('occupied', $toBed->fresh()->status);

        $this->actingAs($doctor)
            ->from(route('admin.admissions.show', $admission))
            ->post(route('admin.admissions.authorize', $admission), [
                'discharge_summary' => 'Pain settled. ECG unchanged.',
                'discharge_medications' => 'Aspirin 75mg daily',
                'follow_up_plan' => 'Cardiology clinic in 7 days',
            ])
            ->assertRedirect(route('admin.admissions.show', $admission));

        $admission->refresh();
        $this->assertSame('discharge_authorized', $admission->status);
        $this->assertNotNull($admission->authorized_at);

        $this->actingAs($receptionist)
            ->from(route('admin.admissions.show', $admission))
            ->post(route('admin.admissions.discharge', $admission))
            ->assertRedirect();

        $admission->refresh();
        $this->assertSame('discharged', $admission->status);
        $this->assertSame('dirty', $toBed->fresh()->status);
        $this->assertNull($toBed->fresh()->patient_id);
        $this->assertNotSame('available', $toBed->fresh()->status);

        $this->actingAs($receptionist)->put(route('admin.beds.update', $toBed->fresh()), [
            'lock_version' => $toBed->fresh()->lock_version,
            'status' => 'cleaning',
        ])->assertRedirect();

        $this->actingAs($receptionist)->post(route('admin.beds.inspect', $toBed->fresh()), [
            'lock_version' => $toBed->fresh()->lock_version,
            'result' => 'pass',
        ])->assertRedirect();

        $this->assertSame('available', $toBed->fresh()->status);
        $this->assertSame([
            AdmissionEvent::TYPE_ADMITTED,
            AdmissionEvent::TYPE_TRANSFERRED,
            AdmissionEvent::TYPE_DISCHARGE_AUTHORIZED,
            AdmissionEvent::TYPE_DISCHARGED,
        ], $admission->events()->orderBy('id')->pluck('event_type')->all());
        $this->assertDatabaseHas('bed_events', [
            'bed_id' => $toBed->id,
            'event_type' => BedEvent::TYPE_INSPECTION_PASSED,
        ]);
    }

    public function test_receptionist_cannot_authorize_discharge(): void
    {
        $user = $this->createStaffUser(['admissions.view', 'admissions.admit'], 'receptionist');
        $admission = $this->openAdmission();

        $this->actingAs($user)
            ->post(route('admin.admissions.authorize', $admission), [
                'discharge_summary' => 'Should not save',
                'discharge_medications' => 'None',
                'follow_up_plan' => 'None',
            ])
            ->assertForbidden();
    }

    public function test_outstanding_bills_must_be_reviewed_before_authorization(): void
    {
        $doctor = $this->createStaffUser(['admissions.view', 'admissions.discharge'], 'doctor');
        $admission = $this->openAdmission();
        Bill::factory()->create([
            'patient_id' => $admission->patient_id,
            'status' => 'issued',
            'total' => 1500,
        ]);

        $this->actingAs($doctor)
            ->from(route('admin.admissions.show', $admission))
            ->post(route('admin.admissions.authorize', $admission), [
                'discharge_summary' => 'Home with advice',
                'discharge_medications' => 'None',
                'follow_up_plan' => 'GP in 3 days',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('billing_reviewed');

        $this->actingAs($doctor)
            ->from(route('admin.admissions.show', $admission))
            ->post(route('admin.admissions.authorize', $admission), [
                'discharge_summary' => 'Home with advice',
                'discharge_medications' => 'None',
                'follow_up_plan' => 'GP in 3 days',
                'billing_reviewed' => '1',
            ])
            ->assertRedirect();

        $admission->refresh();
        $this->assertTrue($admission->billing_reviewed);
        $this->assertSame('issued', $admission->outstanding_bills[0]['status'] ?? null);
    }

    public function test_bed_board_cannot_release_an_unauthorized_admission(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate', 'admissions.view']);
        $admission = $this->openAdmission();
        $bed = $admission->bed;

        $this->actingAs($user)->put(route('admin.beds.update', $bed), [
            'lock_version' => $bed->lock_version,
            'status' => 'dirty',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame('occupied', $bed->fresh()->status);
        $this->assertSame('admitted', $admission->fresh()->status);
    }

    public function test_escapes_discharge_summary_on_the_admission(): void
    {
        $doctor = $this->createStaffUser(['admissions.view', 'admissions.discharge'], 'doctor');
        $admission = $this->openAdmission();

        $this->actingAs($doctor)->post(route('admin.admissions.authorize', $admission), [
            'discharge_summary' => "Stable <script>alert('xss')</script>",
            'discharge_medications' => 'None',
            'follow_up_plan' => 'Clinic',
        ])->assertRedirect();

        $response = $this->actingAs($doctor)->get(route('admin.admissions.show', $admission));

        $response->assertOk();
        $this->assertStringContainsString('&lt;script&gt;', $response->getContent());
        $this->assertStringNotContainsString("<script>alert('xss')</script>", $response->getContent());
    }

    public function test_guest_is_redirected_from_admissions(): void
    {
        $this->get(route('admin.admissions.index'))
            ->assertRedirect(route('admin.login'));
    }

    private function openAdmission(): Admission
    {
        $ward = Ward::factory()->create();
        $patient = Patient::factory()->create();
        $bed = Bed::factory()->occupied()->create([
            'ward_id' => $ward->id,
            'patient_id' => $patient->id,
            'bed_code' => 'GEN-ADM-01',
        ]);

        return Admission::factory()->forBed($bed)->create([
            'status' => 'admitted',
        ]);
    }
}
