<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;
use App\Models\Ward;
use App\Support\BillingDesk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingProcedureTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_an_appointment_prepares_a_consultation_bill(): void
    {
        $this->travelTo('2026-11-03 08:00:00');

        $user = $this->createStaffUser(['appointments.view', 'appointments.manage', 'billing.manage'], 'receptionist');
        $doctor = Doctor::factory()->create([
            'name' => 'Dr. Farhana Akter',
            'consultation_fee' => 1800,
        ]);
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
        ]);

        $this->actingAs($user)
            ->put(route('admin.appointments.update', $appointment), [
                'status' => 'confirmed',
                'appointment_date' => '2026-11-03',
                'start_time' => '09:00',
            ])
            ->assertRedirect();

        $bill = Bill::query()->where('appointment_id', $appointment->id)->first();

        $this->assertNotNull($bill);
        $this->assertSame('draft', $bill->status);
        $this->assertSame(1800.0, (float) $bill->total);
        $this->assertTrue(
            $bill->billItems()->where('item_type', 'consultation')->where('description', 'like', 'Consultation — Dr. Farhana Akter%')->exists()
        );

        $this->actingAs($user)
            ->get(route('admin.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Open consultation bill '.$bill->bill_no, false);
    }

    public function test_completing_a_visit_prepares_the_consultation_bill_once(): void
    {
        $doctorUser = $this->createStaffUser(['visits.view', 'visits.clinical'], 'doctor');
        $doctor = Doctor::factory()->create([
            'user_id' => $doctorUser->id,
            'consultation_fee' => 1500,
            'name' => 'Dr. Desk Clinician',
        ]);
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'status' => 'confirmed',
        ]);
        $visit = Visit::factory()->withVitals()->create([
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'department_id' => $doctor->department_id,
        ]);

        $this->actingAs($doctorUser)->post(route('admin.visits.clinical', $visit), [
            'symptoms' => 'Fever',
            'examination_notes' => 'Stable',
            'diagnosis' => 'Viral fever',
            'treatment_plan' => 'Rest and fluids',
            'complete' => '1',
        ])->assertRedirect();

        $this->assertSame('completed', $appointment->fresh()->status);
        $this->assertSame(1, Bill::query()->where('appointment_id', $appointment->id)->count());
        $this->assertSame(1500.0, (float) Bill::query()->where('appointment_id', $appointment->id)->value('total'));

        $again = app(BillingDesk::class)->ensureAppointmentConsultationBill($appointment->fresh(), $doctorUser);
        $this->assertSame(1, Bill::query()->where('appointment_id', $appointment->id)->count());
        $this->assertSame(Bill::query()->where('appointment_id', $appointment->id)->value('id'), $again?->id);
    }

    public function test_cashier_posts_a_daily_inpatient_bill_with_bed_and_day_services(): void
    {
        $cashier = $this->createStaffUser(['billing.manage', 'admissions.view'], 'cashier');
        $ward = Ward::factory()->create([
            'name' => 'General 2',
            'type' => 'general',
            'daily_bed_rate' => 2000,
        ]);
        $bed = Bed::factory()->create(['ward_id' => $ward->id, 'bed_code' => 'GEN-2-01', 'status' => 'occupied']);
        $patient = Patient::factory()->create();
        $admission = Admission::factory()->create([
            'patient_id' => $patient->id,
            'ward_id' => $ward->id,
            'bed_id' => $bed->id,
            'status' => 'admitted',
        ]);

        $response = $this->actingAs($cashier)->post(route('admin.admissions.day-charges', $admission), [
            'service_date' => '2026-11-03',
            'include_bed_charge' => '1',
            'items' => [
                [
                    'description' => 'Oxygen support',
                    'quantity' => '1',
                    'unit_price' => '500',
                ],
                [
                    'description' => 'Wound dressing',
                    'quantity' => '2',
                    'unit_price' => '150',
                ],
            ],
        ]);

        $bill = Bill::query()->where('admission_id', $admission->id)->first();

        $this->assertNotNull($bill);
        $response->assertRedirect(route('admin.bills.show', $bill));
        $this->assertSame('draft', $bill->status);
        $this->assertSame('2026-11-03', $bill->bill_date?->toDateString());
        $this->assertSame(2800.0, (float) $bill->total);
        $this->assertSame(3, $bill->billItems()->count());

        $this->actingAs($cashier)
            ->post(route('admin.admissions.day-charges', $admission), [
                'service_date' => '2026-11-04',
                'include_bed_charge' => '1',
                'items' => [
                    [
                        'description' => 'Physiotherapy',
                        'quantity' => '1',
                        'unit_price' => '800',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(2, Bill::query()->where('admission_id', $admission->id)->count());
        $dayTwo = Bill::query()->where('admission_id', $admission->id)->whereDate('bill_date', '2026-11-04')->first();
        $this->assertNotNull($dayTwo);
        $this->assertSame(2800.0, (float) $dayTwo->total);
    }
}
