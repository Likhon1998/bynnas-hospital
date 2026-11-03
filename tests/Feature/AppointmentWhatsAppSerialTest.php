<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\HospitalSetting;
use App\Models\Patient;
use App\Support\AppointmentWhatsAppNotice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentWhatsAppSerialTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_an_appointment_opens_whatsapp_with_the_serial_message(): void
    {
        $this->travelTo('2026-11-03 08:00:00');

        HospitalSetting::query()->create([
            'key' => 'hospital_name',
            'group' => 'general',
            'value' => 'Lakeside Medical Center',
        ]);

        $user = $this->createStaffUser(['appointments.view', 'appointments.manage'], 'receptionist');
        $doctor = Doctor::factory()->create(['name' => 'Dr. Farhana Akter']);
        $patient = Patient::factory()->create([
            'full_name' => 'Rahim Uddin',
            'phone' => '01700111222',
        ]);

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

        $response = $this->actingAs($user)
            ->from(route('admin.appointments.index'))
            ->put(route('admin.appointments.update', $appointment), [
                'status' => 'confirmed',
            ]);

        $appointment->refresh();
        $this->assertSame(1, (int) $appointment->serial_no);

        $response->assertRedirect(route('admin.appointments.show', $appointment));
        $response->assertSessionHas('open_whatsapp', true);
        $response->assertSessionHas('whatsapp_serial_url');

        $url = session('whatsapp_serial_url');
        $this->assertIsString($url);
        $this->assertStringStartsWith('https://wa.me/8801700111222?text=', $url);
        $this->assertStringContainsString(rawurlencode('Serial: S-001'), $url);
        $this->assertStringContainsString(rawurlencode('Dr. Farhana Akter'), $url);
        $this->assertStringContainsString(rawurlencode('Lakeside Medical Center'), $url);

        $this->actingAs($user)
            ->get(route('admin.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Send serial on WhatsApp', false)
            ->assertSee('S-001', false)
            ->assertSee('Preview WhatsApp message', false);
    }

    public function test_desk_booking_with_serial_prepares_whatsapp_for_the_patient_phone(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['appointments.view', 'appointments.manage']);
        $patient = Patient::factory()->create([
            'full_name' => 'Nusrat Jahan',
            'phone' => '+880 1812-334455',
        ]);
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-10-28',
            'start_time' => '11:00',
            'reason' => 'Follow-up review',
            'status' => 'confirmed',
            'source' => 'admin',
        ]);

        $appointment = Appointment::query()->firstOrFail();
        $this->assertNotNull($appointment->serial_no);

        $response->assertRedirect(route('admin.appointments.show', $appointment));
        $response->assertSessionHas('open_whatsapp', true);

        $url = session('whatsapp_serial_url');
        $this->assertIsString($url);
        $this->assertStringStartsWith('https://wa.me/8801812334455?text=', $url);
    }

    public function test_whatsapp_notice_normalizes_local_bangladesh_numbers(): void
    {
        $notice = new AppointmentWhatsAppNotice;

        $this->assertSame('8801700111222', $notice->phoneDigits('01700-111222'));
        $this->assertSame('8801700111222', $notice->phoneDigits('+8801700111222'));
        $this->assertSame('8801700111222', $notice->phoneDigits('8801700111222'));
        $this->assertNull($notice->phoneDigits('123'));
    }
}
