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
            'doctor' => $doctor->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
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
        $this->assertSame('09:00:00', $appointment->start_time);
        $status = (string) session('status');
        $this->assertStringContainsString($appointment->appointment_no, $status);
        $this->assertStringContainsString($doctor->name, $status);
        $this->assertStringContainsString('09:00', $status);
        $this->assertStringContainsString('01700111222', $status);

        $this->followRedirects($response)
            ->assertOk()
            ->assertSee($appointment->appointment_no, false)
            ->assertSee('ch-card__flash', false);
    }

    public function test_website_request_reuses_existing_patient_by_phone(): void
    {
        [$service, $doctor] = $this->bookableServiceAndDoctor();
        $patient = Patient::factory()->create([
            'full_name' => 'Old Name',
            'phone' => '01700333444',
        ]);

        $this->post(route('appointment-requests.store'), [
            'name' => 'Updated Name',
            'phone' => '01700333444',
            'service' => $service->slug,
            'doctor' => $doctor->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
        ])->assertRedirect();

        $this->assertSame(1, Patient::query()->count());
        $this->assertSame('Updated Name', $patient->fresh()->full_name);
        $this->assertSame($patient->id, Appointment::query()->first()?->patient_id);
    }

    public function test_website_request_requires_name_phone_service_and_date(): void
    {
        $response = $this->from(route('home'))->post(route('appointment-requests.store'), []);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors(['name', 'phone', 'service', 'date', 'doctor', 'time']);
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_website_request_requires_a_doctor_and_a_visit_time(): void
    {
        [$service] = $this->bookableServiceAndDoctor();

        $response = $this->from(route('home'))->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'date' => now()->addDay()->toDateString(),
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors(['doctor', 'time']);
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_website_request_rejects_inactive_service(): void
    {
        [, $doctor] = $this->bookableServiceAndDoctor();
        $service = Service::factory()->create(['is_active' => false]);

        $response = $this->from(route('home'))->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'doctor' => $doctor->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
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
        $response->assertSessionHasErrors('doctor');
        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_website_request_rejects_a_service_outside_the_doctors_department(): void
    {
        [$service, $doctor] = $this->bookableServiceAndDoctor();
        $otherService = Service::factory()->create([
            'title' => 'Dermatology Clinic',
            'slug' => 'dermatology-clinic',
            'is_active' => true,
        ]);

        $response = $this->from(route('home'))->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'doctor' => $doctor->id,
            'service' => $otherService->slug,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('service');
        $this->assertSame(0, Appointment::query()->count());
        $this->assertNotSame($service->slug, $otherService->slug);
    }

    public function test_staff_can_see_website_requests_in_the_appointment_list(): void
    {
        [$service, $doctor] = $this->bookableServiceAndDoctor();
        $user = $this->createStaffUser(['appointments.view']);

        $this->post(route('appointment-requests.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '01700111222',
            'service' => $service->slug,
            'doctor' => $doctor->id,
            'date' => now()->addDay()->toDateString(),
            'time' => '09:00',
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
