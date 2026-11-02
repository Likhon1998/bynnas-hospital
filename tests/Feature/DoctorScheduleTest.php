<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_clinic_days_and_the_public_calendar_updates(): void
    {
        $this->travelTo('2026-10-28 10:00:00');

        $user = $this->createStaffUser(['doctors.manage']);
        $doctor = $this->saturdayDoctor();

        $this->actingAs($user)
            ->get(route('admin.doctors.edit', $doctor))
            ->assertOk()
            ->assertSee('Weekly clinic days')
            ->assertSee('Leave and vacation');

        $this->actingAs($user)
            ->put(route('admin.doctors.schedules.update', $doctor), $this->weekPayload(['thursday']))
            ->assertRedirect(route('admin.doctors.edit', $doctor));

        $this->assertSame(['thursday'], $doctor->doctorSchedules()->pluck('weekday')->all());

        $days = collect($this->getJson(route('appointment-schedule', [
            'doctor' => $doctor->id,
            'view' => 'month',
            'date' => '2026-10-31',
        ]))->json('days'));

        $this->assertSame('off', $days->firstWhere('date', '2026-10-31')['status']);
        $this->assertSame('open', $days->firstWhere('date', '2026-10-29')['status']);
    }

    public function test_linked_doctor_can_change_own_clinic_hours(): void
    {
        $user = $this->createStaffUser(['appointments.view'], 'doctor');
        $doctor = $this->saturdayDoctor(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('admin.clinic-hours.edit'))
            ->assertOk()
            ->assertSee('Weekly clinic days')
            ->assertSee($doctor->name);

        $this->actingAs($user)
            ->put(route('admin.doctors.schedules.update', $doctor), $this->weekPayload(['monday', 'wednesday']))
            ->assertRedirect(route('admin.clinic-hours.edit'));

        $this->assertEqualsCanonicalizing(
            ['monday', 'wednesday'],
            $doctor->doctorSchedules()->pluck('weekday')->all(),
        );
    }

    public function test_linked_doctor_cannot_change_another_doctors_hours(): void
    {
        $user = $this->createStaffUser(['appointments.view'], 'doctor');
        $own = $this->saturdayDoctor(['user_id' => $user->id]);
        $other = $this->saturdayDoctor();

        $this->actingAs($user)
            ->put(route('admin.doctors.schedules.update', $other), $this->weekPayload(['monday']))
            ->assertForbidden();

        $this->assertSame('saturday', $own->doctorSchedules()->value('weekday'));
        $this->assertSame('saturday', $other->doctorSchedules()->value('weekday'));
    }

    public function test_staff_without_clinic_hours_access_cannot_change_days(): void
    {
        $user = $this->createStaffUser(['appointments.manage']);
        $doctor = $this->saturdayDoctor();

        $this->actingAs($user)
            ->get(route('admin.clinic-hours.edit'))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.doctors.schedules.update', $doctor), $this->weekPayload(['monday']))
            ->assertForbidden();
    }

    public function test_clinic_hours_require_at_least_one_open_day(): void
    {
        $user = $this->createStaffUser(['doctors.manage']);
        $doctor = $this->saturdayDoctor();

        $this->actingAs($user)
            ->from(route('admin.doctors.edit', $doctor))
            ->put(route('admin.doctors.schedules.update', $doctor), $this->weekPayload([]))
            ->assertRedirect(route('admin.doctors.edit', $doctor))
            ->assertSessionHasErrors('days');

        $this->assertSame(1, $doctor->doctorSchedules()->count());
    }

    public function test_linked_doctor_can_block_and_open_own_dates(): void
    {
        $user = $this->createStaffUser(['appointments.view'], 'doctor');
        $doctor = $this->saturdayDoctor(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('admin.doctors.leaves.store', $doctor), [
                'starts_on' => '2026-12-01',
                'ends_on' => '2026-12-05',
                'type' => 'vacation',
                'reason' => 'Conference',
            ])
            ->assertRedirect(route('admin.clinic-hours.edit'));

        $leave = $doctor->doctorLeaves()->first();
        $this->assertNotNull($leave);
        $this->assertSame('Conference', $leave->reason);

        $this->actingAs($user)
            ->delete(route('admin.doctors.leaves.destroy', [$doctor, $leave]))
            ->assertRedirect(route('admin.clinic-hours.edit'));

        $this->assertSame(0, $doctor->doctorLeaves()->count());
    }

    public function test_booked_times_stay_on_the_public_calendar_as_taken_slots(): void
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

        $saturday = collect($this->getJson(route('appointment-schedule', [
            'doctor' => $doctor->id,
            'view' => 'month',
            'date' => '2026-10-31',
        ]))->json('days'))->firstWhere('date', '2026-10-31');

        $this->assertSame('open', $saturday['status']);
        $this->assertSame(1, $saturday['booked_count']);
        $this->assertSame('booked', collect($saturday['slots'])->firstWhere('time', '09:00')['status']);
        $this->assertSame('open', collect($saturday['slots'])->firstWhere('time', '09:30')['status']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function saturdayDoctor(array $attributes = []): Doctor
    {
        $department = Department::factory()->create();
        $doctor = Doctor::factory()->recycle($department)->create([
            'is_active' => true,
            ...$attributes,
        ]);
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

    /**
     * @param  list<string>  $openDays
     * @return array{days: array<string, array<string, mixed>>}
     */
    private function weekPayload(array $openDays): array
    {
        $days = [];

        foreach (array_keys(DoctorSchedule::WEEKDAYS) as $weekday) {
            $days[$weekday] = [
                'start_time' => '09:00',
                'end_time' => '13:00',
                'break_start' => '',
                'break_end' => '',
                'slot_duration_minutes' => 30,
            ];

            if (in_array($weekday, $openDays, true)) {
                $days[$weekday]['open'] = '1';
            }
        }

        return ['days' => $days];
    }
}
