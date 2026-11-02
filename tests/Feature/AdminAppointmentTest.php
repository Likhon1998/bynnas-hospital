<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_redirects_to_admin_login(): void
    {
        $this->get(route('admin.appointments.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_staff_without_manage_permission_cannot_open_book_form(): void
    {
        $user = $this->createStaffUser(['appointments.view'], 'doctor');

        $this->actingAs($user)
            ->get(route('admin.appointments.create'))
            ->assertForbidden();
    }

    public function test_staff_can_book_a_desk_visit(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['appointments.view', 'appointments.manage']);
        $patient = Patient::factory()->create(['full_name' => 'Nusrat Jahan']);
        $doctor = Doctor::factory()->create(['name' => 'Dr. Desk Clinician']);

        $response = $this->actingAs($user)->post(route('admin.appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-10-28',
            'start_time' => '11:00',
            'reason' => 'Follow-up review',
            'status' => 'confirmed',
            'source' => 'admin',
        ]);

        $appointment = Appointment::query()->first();

        $this->assertNotNull($appointment);
        $response->assertRedirect(route('admin.appointments.show', $appointment));
        $this->assertSame('confirmed', $appointment->status);
        $this->assertSame('admin', $appointment->source);
        $this->assertSame('11:00:00', $appointment->start_time);
        $this->assertSame('11:30:00', $appointment->end_time);
        $this->assertSame($doctor->department_id, $appointment->department_id);
        $this->assertSame($user->id, $appointment->created_by);
        $this->assertSame('Desk', $appointment->sourceLabel());
    }

    public function test_desk_booking_requires_patient_doctor_date_and_time(): void
    {
        $user = $this->createStaffUser(['appointments.view', 'appointments.manage']);

        $response = $this->actingAs($user)
            ->from(route('admin.appointments.create'))
            ->post(route('admin.appointments.store'), []);

        $response->assertRedirect(route('admin.appointments.create'));
        $response->assertSessionHasErrors(['patient_id', 'doctor_id', 'appointment_date', 'start_time', 'status', 'source']);
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_overlapping_held_slot_is_rejected(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['appointments.view', 'appointments.manage']);
        $doctor = Doctor::factory()->create();
        $first = Appointment::factory()->recycle($doctor)->create([
            'appointment_date' => '2026-10-28',
            'start_time' => '11:00:00',
            'end_time' => '11:30:00',
            'status' => 'confirmed',
            'source' => 'admin',
        ]);

        $response = $this->actingAs($user)->post(route('admin.appointments.store'), [
            'patient_id' => Patient::factory()->create()->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-10-28',
            'start_time' => '11:15',
            'status' => 'confirmed',
            'source' => 'admin',
        ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertSame(1, Appointment::query()->count());
        $this->assertModelExists($first);
    }

    public function test_pending_website_request_blocks_the_same_desk_slot(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['appointments.view', 'appointments.manage']);
        $doctor = Doctor::factory()->create();
        Appointment::factory()->recycle($doctor)->create([
            'appointment_date' => '2026-10-28',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'pending',
            'source' => 'web',
        ]);

        $response = $this->actingAs($user)->post(route('admin.appointments.store'), [
            'patient_id' => Patient::factory()->create()->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-10-28',
            'start_time' => '09:00',
            'status' => 'confirmed',
            'source' => 'admin',
        ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertSame(1, Appointment::query()->count());
    }

    public function test_desk_booking_rejects_an_off_day_when_the_doctor_has_clinic_hours(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['appointments.view', 'appointments.manage']);
        $doctor = Doctor::factory()->create();
        DoctorSchedule::factory()->recycle($doctor)->create([
            'weekday' => 'saturday',
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->post(route('admin.appointments.store'), [
            'patient_id' => Patient::factory()->create()->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-10-29',
            'start_time' => '09:00',
            'status' => 'confirmed',
            'source' => 'admin',
        ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_desk_booking_rejects_a_doctor_on_vacation(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['appointments.view', 'appointments.manage']);
        $doctor = Doctor::factory()->create();
        DoctorLeave::factory()->recycle($doctor)->create([
            'starts_on' => '2026-10-28',
            'ends_on' => '2026-11-04',
            'type' => 'vacation',
        ]);

        $response = $this->actingAs($user)->post(route('admin.appointments.store'), [
            'patient_id' => Patient::factory()->create()->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-10-28',
            'start_time' => '11:00',
            'status' => 'confirmed',
            'source' => 'admin',
        ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_emergency_can_book_an_off_day_but_not_vacation(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['appointments.view', 'appointments.manage']);
        $doctor = Doctor::factory()->create();
        DoctorSchedule::factory()->recycle($doctor)->create([
            'weekday' => 'saturday',
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
        ]);

        $this->actingAs($user)->post(route('admin.appointments.store'), [
            'patient_id' => Patient::factory()->create()->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-10-29',
            'start_time' => '21:00',
            'status' => 'confirmed',
            'source' => 'emergency',
        ])->assertRedirect();

        $this->assertSame(1, Appointment::query()->count());

        DoctorLeave::factory()->recycle($doctor)->create([
            'starts_on' => '2026-10-30',
            'ends_on' => '2026-11-02',
            'type' => 'vacation',
        ]);

        $response = $this->actingAs($user)->post(route('admin.appointments.store'), [
            'patient_id' => Patient::factory()->create()->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-10-30',
            'start_time' => '21:00',
            'status' => 'confirmed',
            'source' => 'emergency',
        ]);

        $response->assertSessionHasErrors('start_time');
        $this->assertSame(1, Appointment::query()->count());
    }

    public function test_staff_can_confirm_a_website_request_with_a_real_slot(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['appointments.view', 'appointments.manage']);
        $appointment = Appointment::factory()->create([
            'appointment_date' => '2026-10-28',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'pending',
            'source' => 'web',
            'reason' => 'General OPD',
            'notes' => 'Website request. Confirm the time with the patient.',
        ]);

        $response = $this->actingAs($user)
            ->from(route('admin.appointments.show', $appointment))
            ->put(route('admin.appointments.update', $appointment), [
                'status' => 'confirmed',
                'appointment_date' => '2026-10-29',
                'start_time' => '16:00',
                'reason' => 'General OPD',
                'notes' => 'Called the patient. Slot confirmed.',
            ]);

        $response->assertRedirect(route('admin.appointments.show', $appointment));
        $appointment->refresh();
        $this->assertSame('confirmed', $appointment->status);
        $this->assertSame('2026-10-29', $appointment->appointment_date?->toDateString());
        $this->assertSame('16:00:00', $appointment->start_time);
        $this->assertSame('16:30:00', $appointment->end_time);
        $this->assertSame('Called the patient. Slot confirmed.', $appointment->notes);
    }

    public function test_list_shows_emergency_source_and_book_visit(): void
    {
        $user = $this->createStaffUser(['appointments.view', 'appointments.manage']);
        $emergency = Department::factory()->create(['name' => 'Emergency', 'slug' => 'emergency']);
        Appointment::factory()->create([
            'source' => 'emergency',
            'department_id' => $emergency->id,
            'reason' => 'Chest pain since dawn',
        ]);

        $response = $this->actingAs($user)->get(route('admin.appointments.index'));

        $response->assertOk();
        $response->assertSee('Take appointment');
        $response->assertSee('Emergency');
        $response->assertSee('Chest pain since dawn');
    }

    public function test_invalid_status_filter_is_ignored(): void
    {
        $user = $this->createStaffUser(['appointments.view']);
        Appointment::factory()->create([
            'status' => 'pending',
            'appointment_no' => 'APT-KEEP-1',
        ]);

        $response = $this->actingAs($user)->get(route('admin.appointments.index', ['status' => 'drop-table']));

        $response->assertOk();
        $response->assertSee('APT-KEEP-1');
    }

    public function test_escapes_patient_name_in_the_appointment_list(): void
    {
        $user = $this->createStaffUser(['appointments.view']);
        $patient = Patient::factory()->create([
            'full_name' => "Nusrat <script>alert('xss')</script>",
        ]);
        Appointment::factory()->recycle($patient)->create();

        $response = $this->actingAs($user)->get(route('admin.appointments.index'));

        $response->assertOk();
        $this->assertStringContainsString('&lt;script&gt;', $response->getContent());
        $this->assertStringNotContainsString("<script>alert('xss')</script>", $response->getContent());
    }

    public function test_staff_can_list_appointments_for_one_doctor(): void
    {
        $user = $this->createStaffUser(['appointments.view']);
        $chosen = Doctor::factory()->create(['name' => 'Dr. Hamida Jannat']);
        $other = Doctor::factory()->create(['name' => 'Dr. Other Consultant']);
        Appointment::factory()->recycle($chosen)->create([
            'appointment_no' => 'APT-HAMIDA-1',
        ]);
        Appointment::factory()->recycle($other)->create([
            'appointment_no' => 'APT-OTHER-9',
        ]);

        $response = $this->actingAs($user)->get(route('admin.appointments.index', ['doctor' => $chosen->id]));

        $response->assertOk();
        $response->assertSee('APT-HAMIDA-1', false);
        $response->assertDontSee('APT-OTHER-9', false);
        $response->assertSee('Dr. Hamida Jannat', false);
        $response->assertSee('name="doctor"', false);
        $response->assertSee('All doctors', false);
    }

    public function test_unknown_doctor_filter_is_ignored(): void
    {
        $user = $this->createStaffUser(['appointments.view']);
        Appointment::factory()->create([
            'appointment_no' => 'APT-KEEP-DOC',
        ]);

        $response = $this->actingAs($user)->get(route('admin.appointments.index', ['doctor' => 99999]));

        $response->assertOk();
        $response->assertSee('APT-KEEP-DOC', false);
    }

    public function test_staff_can_list_one_doctors_appointments_on_a_date(): void
    {
        $user = $this->createStaffUser(['appointments.view']);
        $chosen = Doctor::factory()->create(['name' => 'Dr. Hamida Jannat']);
        $other = Doctor::factory()->create(['name' => 'Dr. Other Consultant']);
        Appointment::factory()->recycle($chosen)->create([
            'appointment_no' => 'APT-DAY-KEEP',
            'appointment_date' => '2026-11-02',
        ]);
        Appointment::factory()->recycle($chosen)->create([
            'appointment_no' => 'APT-DAY-SKIP',
            'appointment_date' => '2026-11-03',
        ]);
        Appointment::factory()->recycle($other)->create([
            'appointment_no' => 'APT-DAY-OTHER',
            'appointment_date' => '2026-11-02',
        ]);

        $response = $this->actingAs($user)->get(route('admin.appointments.index', [
            'doctor' => $chosen->id,
            'date' => '2026-11-02',
        ]));

        $response->assertOk();
        $response->assertSee('APT-DAY-KEEP', false);
        $response->assertDontSee('APT-DAY-SKIP', false);
        $response->assertDontSee('APT-DAY-OTHER', false);
        $response->assertSee('02 Nov 2026', false);
        $response->assertSee('name="date"', false);
        $response->assertSee('data-live-filter', false);
    }

    public function test_invalid_date_filter_is_ignored(): void
    {
        $user = $this->createStaffUser(['appointments.view']);
        Appointment::factory()->create([
            'appointment_no' => 'APT-KEEP-DATE',
        ]);

        $response = $this->actingAs($user)->get(route('admin.appointments.index', ['date' => 'not-a-date']));

        $response->assertOk();
        $response->assertSee('APT-KEEP-DATE', false);
    }

    public function test_appointment_detail_links_to_that_doctors_visits(): void
    {
        $user = $this->createStaffUser(['appointments.view']);
        $doctor = Doctor::factory()->create(['name' => 'Dr. Hamida Jannat']);
        $appointment = Appointment::factory()->recycle($doctor)->create();

        $this->actingAs($user)
            ->get(route('admin.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('All visits', false)
            ->assertSee(route('admin.appointments.index', ['doctor' => $doctor->id]), false);
    }
}
