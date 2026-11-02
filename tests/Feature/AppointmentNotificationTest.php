<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_booking_stays_in_the_notification_bell_until_it_is_read(): void
    {
        [$service, $doctor] = $this->bookableServiceAndDoctor();
        $receptionist = $this->createStaffUser(['appointments.view'], 'receptionist');

        $this->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'doctor' => $doctor->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
        ])->assertRedirect();

        $appointment = Appointment::query()->first();
        $this->assertNotNull($appointment);

        $feed = $this->actingAs($receptionist)->getJson(route('admin.notifications'));
        $feed->assertOk();
        $feed->assertJsonPath('total', 1);
        $feed->assertJsonPath('alerts.0.read', false);
        $feed->assertJsonFragment(['title' => 'New appointment '.$appointment->appointment_no]);
        $feed->assertJsonFragment(['detail' => 'Rahim Uddin booked '.$doctor->name.' on '.$appointment->appointment_date?->format('d M Y').' at 09:00']);

        $this->actingAs($receptionist)
            ->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 1);

        $id = $feed->json('alerts.0.id');
        $this->assertIsString($id);

        $this->actingAs($receptionist)
            ->postJson(route('admin.notifications.seen'), ['ids' => [$id]])
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath('alerts.0.id', $id)
            ->assertJsonPath('alerts.0.read', true);

        $this->actingAs($receptionist)
            ->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath('alerts.0.id', $id)
            ->assertJsonPath('alerts.0.read', true)
            ->assertJsonFragment(['title' => 'New appointment '.$appointment->appointment_no]);
    }

    public function test_admin_and_assigned_doctor_also_see_the_unread_booking(): void
    {
        [$service, $doctor] = $this->bookableServiceAndDoctor();
        $admin = $this->createStaffUser(['appointments.view', 'doctors.manage']);
        $doctorUser = $this->createStaffUser(['appointments.view'], 'doctor');
        $doctor->update(['user_id' => $doctorUser->id]);
        $otherDoctorUser = $this->createStaffUser(['appointments.view'], 'doctor');
        Doctor::factory()->create(['user_id' => $otherDoctorUser->id, 'is_active' => true]);

        $this->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'doctor' => $doctor->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
        ])->assertRedirect();

        $appointment = Appointment::query()->first();
        $this->assertNotNull($appointment);
        $this->assertSame(3, WorkItem::query()->where('kind', 'pending_web')->where('is_notification', true)->count());

        $this->actingAs($admin)
            ->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonFragment(['title' => 'New appointment '.$appointment->appointment_no]);

        $this->actingAs($doctorUser)
            ->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonFragment(['title' => 'New appointment '.$appointment->appointment_no]);

        $this->actingAs($otherDoctorUser)
            ->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 0);
    }

    public function test_staff_without_the_booking_desk_do_not_see_the_appointment_notification(): void
    {
        [$service, $doctor] = $this->bookableServiceAndDoctor();
        $nurse = $this->createStaffUser(['nursing.view', 'beds.view'], 'nurse_station');

        $this->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'doctor' => $doctor->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
        ])->assertRedirect();

        $this->actingAs($nurse)
            ->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 0);
    }

    /**
     * @return array{0: Service, 1: Doctor}
     */
    private function bookableServiceAndDoctor(): array
    {
        $department = Department::factory()->create();
        $doctor = Doctor::factory()->recycle($department)->create(['is_active' => true]);
        $service = Service::factory()->recycle($department)->create([
            'title' => 'General OPD',
            'slug' => 'general-opd',
            'is_active' => true,
        ]);

        return [$service, $doctor];
    }
}
