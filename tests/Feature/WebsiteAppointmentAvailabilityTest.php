<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use App\Models\DoctorSchedule;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteAppointmentAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_request_rejects_a_doctor_off_day(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        [$service, $doctor] = $this->bookableSaturdayDoctor();

        $response = $this->from(route('home'))->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'doctor' => $doctor->id,
            'date' => '2026-10-29',
            'time' => '09:00',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('time');
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_website_request_rejects_a_doctor_on_vacation(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        [$service, $doctor] = $this->bookableSaturdayDoctor();
        DoctorLeave::factory()->recycle($doctor)->create([
            'starts_on' => '2026-10-31',
            'ends_on' => '2026-11-07',
            'type' => 'vacation',
            'reason' => 'Annual leave',
        ]);

        $response = $this->from(route('home'))->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'doctor' => $doctor->id,
            'date' => '2026-10-31',
            'time' => '09:00',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('time');
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_second_website_request_cannot_take_the_same_doctor_and_time(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        [$service, $doctor] = $this->bookableSaturdayDoctor();

        $this->post(route('appointment-requests.store'), [
            'name' => 'First Patient',
            'phone' => '01700111222',
            'service' => $service->slug,
            'doctor' => $doctor->id,
            'date' => '2026-10-31',
            'time' => '09:00',
        ])->assertRedirect();

        $response = $this->from(route('home'))->post(route('appointment-requests.store'), [
            'name' => 'Second Patient',
            'phone' => '01700333444',
            'service' => $service->slug,
            'doctor' => $doctor->id,
            'date' => '2026-10-31',
            'time' => '09:00',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('time');
        $this->assertSame(1, Appointment::query()->count());
        $this->assertSame('01700111222', Appointment::query()->first()?->patient?->phone);
    }

    public function test_cancelled_visit_frees_the_slot_for_another_patient(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        [$service, $doctor] = $this->bookableSaturdayDoctor();
        Appointment::factory()->recycle($doctor)->create([
            'appointment_date' => '2026-10-31',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'cancelled',
            'source' => 'web',
            'booking_key' => null,
        ]);

        $this->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'doctor' => $doctor->id,
            'date' => '2026-10-31',
            'time' => '09:00',
        ])->assertRedirect(route('home').'#appointment');

        $this->assertSame(2, Appointment::query()->count());
        $this->assertSame('pending', Appointment::query()->where('status', 'pending')->value('status'));
    }

    public function test_slot_feed_hides_off_days_vacation_and_taken_times(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        [, $doctor] = $this->bookableSaturdayDoctor();
        Appointment::factory()->recycle($doctor)->create([
            'appointment_date' => '2026-10-31',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'pending',
            'source' => 'web',
            'booking_key' => $doctor->id.':2026-10-31:09:00',
        ]);
        DoctorLeave::factory()->recycle($doctor)->create([
            'starts_on' => '2026-11-07',
            'ends_on' => '2026-11-14',
            'type' => 'vacation',
        ]);

        $open = $this->getJson(route('appointment-slots', [
            'doctor' => $doctor->id,
            'date' => '2026-10-31',
        ]));

        $open->assertOk();
        $open->assertJsonPath('available', true);
        $this->assertNotContains('09:00', $open->json('slots'));
        $this->assertContains('09:30', $open->json('slots'));

        $this->getJson(route('appointment-slots', [
            'doctor' => $doctor->id,
            'date' => '2026-10-29',
        ]))->assertOk()->assertJsonPath('available', false);

        $this->getJson(route('appointment-slots', [
            'doctor' => $doctor->id,
            'date' => '2026-11-07',
        ]))->assertOk()->assertJsonPath('available', false);
    }

    /**
     * @return array{0: Service, 1: Doctor}
     */
    private function bookableSaturdayDoctor(): array
    {
        $department = Department::factory()->create();
        $doctor = Doctor::factory()->recycle($department)->create(['is_active' => true]);
        DoctorSchedule::factory()->recycle($doctor)->create([
            'weekday' => 'saturday',
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
            'break_start' => '11:00:00',
            'break_end' => '11:20:00',
            'slot_duration_minutes' => 30,
            'is_active' => true,
        ]);
        $service = Service::factory()->recycle($department)->create([
            'title' => 'General OPD',
            'slug' => 'general-opd',
            'is_active' => true,
        ]);

        return [$service, $doctor];
    }
}
