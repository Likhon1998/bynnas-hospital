<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorLeave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorLeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_record_and_remove_doctor_vacation(): void
    {
        $user = $this->createStaffUser(['doctors.manage']);
        $doctor = Doctor::factory()->create(['name' => 'Dr. Arif Rahman']);

        $store = $this->actingAs($user)->post(route('admin.doctors.leaves.store', $doctor), [
            'starts_on' => '2026-12-01',
            'ends_on' => '2026-12-10',
            'type' => 'vacation',
            'reason' => 'Annual leave',
        ]);

        $store->assertRedirect(route('admin.doctors.edit', $doctor));
        $leave = DoctorLeave::query()->first();
        $this->assertNotNull($leave);
        $this->assertSame('vacation', $leave->type);
        $this->assertSame($user->id, $leave->created_by);
        $this->assertSame('2026-12-01', $leave->starts_on?->toDateString());

        $this->actingAs($user)
            ->get(route('admin.doctors.edit', $doctor))
            ->assertOk()
            ->assertSee('Leave and vacation')
            ->assertSee('Annual leave');

        $this->actingAs($user)
            ->delete(route('admin.doctors.leaves.destroy', [$doctor, $leave]))
            ->assertRedirect(route('admin.doctors.edit', $doctor));

        $this->assertSame(0, DoctorLeave::query()->count());
    }

    public function test_staff_without_doctor_permission_cannot_record_leave(): void
    {
        $user = $this->createStaffUser(['appointments.manage']);
        $doctor = Doctor::factory()->create();

        $this->actingAs($user)->post(route('admin.doctors.leaves.store', $doctor), [
            'starts_on' => '2026-12-01',
            'ends_on' => '2026-12-10',
            'type' => 'vacation',
        ])->assertForbidden();

        $this->assertSame(0, DoctorLeave::query()->count());
    }

    public function test_linked_doctor_cannot_record_leave_for_another_doctor(): void
    {
        $user = $this->createStaffUser(['appointments.view'], 'doctor');
        Doctor::factory()->create(['user_id' => $user->id]);
        $other = Doctor::factory()->create();

        $this->actingAs($user)->post(route('admin.doctors.leaves.store', $other), [
            'starts_on' => '2026-12-01',
            'ends_on' => '2026-12-10',
            'type' => 'vacation',
        ])->assertForbidden();

        $this->assertSame(0, DoctorLeave::query()->count());
    }
}
