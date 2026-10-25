<?php

namespace Tests\Feature;

use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_change_appointment_status_from_the_list(): void
    {
        $user = $this->createStaffUser(['appointments.manage', 'appointments.view']);
        $appointment = Appointment::factory()->create([
            'status' => 'pending',
            'notes' => 'Keep this note.',
        ]);

        $response = $this->actingAs($user)
            ->from(route('admin.appointments.index'))
            ->put(route('admin.appointments.update', $appointment), [
                'status' => 'confirmed',
            ]);

        $response->assertRedirect(route('admin.appointments.index'));
        $appointment->refresh();
        $this->assertSame('confirmed', $appointment->status);
        $this->assertSame('Keep this note.', $appointment->notes);
    }

    public function test_staff_without_manage_permission_cannot_change_status(): void
    {
        $user = $this->createStaffUser(['appointments.view'], 'receptionist');
        $appointment = Appointment::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->put(route('admin.appointments.update', $appointment), [
            'status' => 'confirmed',
        ]);

        $response->assertForbidden();
        $this->assertSame('pending', $appointment->fresh()->status);
    }
}
