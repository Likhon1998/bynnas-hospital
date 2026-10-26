<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteAppointmentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_request_creates_pending_appointment_for_the_desk(): void
    {
        [$service, $doctor] = $this->bookableServiceAndDoctor();

        $response = $this->from(route('home'))->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'date' => now()->addDay()->toDateString(),
        ]);

        $response->assertRedirect(route('home').'#appointment');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('patients', [
            'full_name' => 'Rahim Uddin',
            'phone' => '01700111222',
        ]);

        $appointment = Appointment::query()->first();
        $this->assertNotNull($appointment);
        $this->assertSame('pending', $appointment->status);
        $this->assertSame('web', $appointment->source);
        $this->assertSame($doctor->id, $appointment->doctor_id);
        $this->assertSame($service->department_id, $appointment->department_id);
        $this->assertSame($service->title, $appointment->reason);
    }

    public function test_website_request_reuses_existing_patient_by_phone(): void
    {
        [$service] = $this->bookableServiceAndDoctor();
        $patient = Patient::factory()->create([
            'full_name' => 'Old Name',
            'phone' => '01700333444',
        ]);

        $this->post(route('appointment-requests.store'), [
            'name' => 'Updated Name',
            'phone' => '01700333444',
            'service' => $service->slug,
            'date' => now()->addDay()->toDateString(),
        ])->assertRedirect();

        $this->assertSame(1, Patient::query()->count());
        $this->assertSame('Updated Name', $patient->fresh()->full_name);
        $this->assertSame($patient->id, Appointment::query()->first()?->patient_id);
    }

    public function test_website_request_requires_name_phone_service_and_date(): void
    {
        $response = $this->from(route('home'))->post(route('appointment-requests.store'), []);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors(['name', 'phone', 'service', 'date']);
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_website_request_rejects_inactive_service(): void
    {
        $this->bookableServiceAndDoctor();
        $service = Service::factory()->create(['is_active' => false]);

        $response = $this->from(route('home'))->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'date' => now()->addDay()->toDateString(),
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('service');
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_website_request_fails_when_no_doctor_is_on_the_roster(): void
    {
        $service = Service::factory()->create(['is_active' => true]);

        $response = $this->from(route('home'))->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'date' => now()->addDay()->toDateString(),
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_staff_can_see_website_requests_in_the_appointment_list(): void
    {
        [$service] = $this->bookableServiceAndDoctor();
        $user = $this->createStaffUser(['appointments.view']);

        $this->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'date' => now()->addDay()->toDateString(),
        ])->assertRedirect();

        $response = $this->actingAs($user)->get(route('admin.appointments.index'));

        $response->assertOk();
        $response->assertSee('Rahim Uddin', false);
        $response->assertSee('Website', false);
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
