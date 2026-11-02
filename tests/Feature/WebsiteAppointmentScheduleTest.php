<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use App\Models\DoctorSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteAppointmentScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_month_schedule_marks_clinic_days_off_days_and_taken_slots(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $doctor = $this->saturdayDoctor();
        Appointment::factory()->recycle($doctor)->create([
            'appointment_date' => '2026-10-31',
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'status' => 'pending',
            'source' => 'web',
            'booking_key' => $doctor->id.':2026-10-31:09:00',
        ]);

        $response = $this->getJson(route('appointment-schedule', [
            'doctor' => $doctor->id,
            'view' => 'month',
            'date' => '2026-10-31',
        ]));

        $response->assertOk();
        $response->assertJsonPath('view', 'month');
        $response->assertJsonPath('label', 'October 2026');

        $days = collect($response->json('days'));
        $thursday = $days->firstWhere('date', '2026-10-29');
        $saturday = $days->firstWhere('date', '2026-10-31');

        $this->assertNotNull($thursday);
        $this->assertSame('off', $thursday['status']);
        $this->assertSame([], $thursday['slots']);

        $this->assertNotNull($saturday);
        $this->assertSame('open', $saturday['status']);
        $this->assertSame(1, $saturday['booked_count']);
        $this->assertSame('booked', collect($saturday['slots'])->firstWhere('time', '09:00')['status']);
        $this->assertSame('open', collect($saturday['slots'])->firstWhere('time', '09:30')['status']);
    }

    public function test_week_schedule_hides_vacation_days(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $doctor = $this->saturdayDoctor();
        DoctorLeave::factory()->recycle($doctor)->create([
            'starts_on' => '2026-10-31',
            'ends_on' => '2026-11-07',
            'type' => 'vacation',
        ]);

        $response = $this->getJson(route('appointment-schedule', [
            'doctor' => $doctor->id,
            'view' => 'week',
            'date' => '2026-10-31',
        ]));

        $response->assertOk();
        $response->assertJsonPath('view', 'week');
        $this->assertCount(7, $response->json('days'));

        $saturday = collect($response->json('days'))->firstWhere('date', '2026-10-31');
        $this->assertNotNull($saturday);
        $this->assertSame('leave', $saturday['status']);
        $this->assertSame([], $saturday['slots']);
    }

    public function test_home_page_shows_the_clinic_schedule_board(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Clinic schedule', false)
            ->assertSee('data-appointment-schedule', false)
            ->assertSee('Month', false)
            ->assertSee('Week', false)
            ->assertSee('Choose from clinic schedule', false)
            ->assertSee('Choose a doctor first', false)
            ->assertSee('name="doctor" required', false)
            ->assertDontSee('Select Date', false)
            ->assertDontSee('Preferred Time', false)
            ->assertSee('data-cal-times', false)
            ->assertSee('Free', false)
            ->assertSee('Booked', false);
    }

    private function saturdayDoctor(): Doctor
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

        return $doctor;
    }
}
