<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\HospitalSetting;
use App\Models\Patient;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmergencyFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_emergency_page_is_available(): void
    {
        HospitalSetting::query()->create([
            'key' => 'emergency_phone',
            'group' => 'contact',
            'value' => '+880 1700-911911',
        ]);
        HospitalSetting::query()->create([
            'key' => 'emergency_content',
            'group' => 'content',
            'value' => 'The emergency desk is open day and night.',
        ]);

        $this->get(route('emergency'))
            ->assertOk()
            ->assertSee('Emergency desk')
            ->assertSee('+880 1700-911911')
            ->assertSee('The emergency desk is open day and night.');
    }

    public function test_emergency_board_lists_today_er_visits_not_clinic_pending(): void
    {
        $user = $this->createStaffUser(['operations.manage', 'appointments.view']);
        $emergency = Department::factory()->create(['name' => 'Emergency', 'slug' => 'emergency']);
        $clinic = Department::factory()->create(['name' => 'Cardiology', 'slug' => 'cardiology']);
        $doctor = Doctor::factory()->create(['department_id' => $emergency->id]);
        $erPatient = Patient::factory()->create(['full_name' => 'ER Walk-in Patient']);
        $clinicPatient = Patient::factory()->create(['full_name' => 'Clinic Pending Patient']);
        $ward = Ward::factory()->create(['type' => 'icu', 'name' => 'ICU A', 'code' => 'ICU-A']);
        Bed::factory()->create([
            'ward_id' => $ward->id,
            'bed_code' => 'ICU-A-11',
            'status' => 'available',
        ]);

        Appointment::factory()->create([
            'appointment_no' => 'ER-TEST-1001',
            'patient_id' => $erPatient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $emergency->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '08:15:00',
            'reason' => 'Chest pain since dawn',
            'status' => 'pending',
            'source' => 'emergency',
            'booking_key' => null,
        ]);
        Appointment::factory()->create([
            'appointment_no' => 'APT-CLINIC-1001',
            'patient_id' => $clinicPatient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $clinic->id,
            'appointment_date' => now()->toDateString(),
            'status' => 'pending',
            'source' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('admin.emergency'));

        $response->assertOk();
        $response->assertSee('Emergency Flow');
        $response->assertSee('ER Walk-in Patient');
        $response->assertSee('Chest pain since dawn');
        $response->assertSee('ER-TEST-1001');
        $response->assertSee('ICU-A-11');
        $response->assertViewHas('emergencyVisits', function ($visits): bool {
            return $visits->count() === 1
                && $visits->first()?->appointment_no === 'ER-TEST-1001';
        });
    }
}
