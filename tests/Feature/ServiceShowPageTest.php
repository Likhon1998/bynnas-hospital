<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_service_page_renders_successfully(): void
    {
        $service = Service::factory()->create([
            'title' => 'Emergency Care',
            'slug' => 'emergency-care',
            'summary' => 'A 24/7 desk for urgent assessment.',
            'image' => 'https://images.unsplash.com/photo-1516574187841-cb9cc2ca948b?auto=format&fit=crop&w=1400&q=80',
            'is_active' => true,
        ]);

        $response = $this->get(route('services.show', $service));

        $response->assertOk();
        $response->assertSee('Emergency Care', false);
        $response->assertSee('What to expect', false);
        $response->assertSee('Go to emergency', false);
        $response->assertSee(route('emergency'), false);
        $response->assertSee('photo-1516574187841-cb9cc2ca948b', false);
    }

    public function test_service_page_lists_department_consultants_with_profile_links(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);
        $service = Service::factory()->create([
            'title' => 'CCU',
            'slug' => 'ccu',
            'department_id' => $cardiology->id,
            'is_active' => true,
        ]);
        $doctor = Doctor::factory()->create([
            'name' => 'Dr. Hamida Jannat',
            'department_id' => $cardiology->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('services.show', $service));

        $response->assertOk();
        $response->assertSee('Doctors in this department', false);
        $response->assertSee('Dr. Hamida Jannat', false);
        $response->assertSee(route('doctors.show', $doctor), false);
        $response->assertSee(route('doctors', ['specialty' => 'Cardiology']), false);
    }

    public function test_inactive_service_page_returns_not_found(): void
    {
        $service = Service::factory()->create([
            'slug' => 'inactive-service',
            'is_active' => false,
        ]);

        $this->get(route('services.show', $service))->assertNotFound();
    }
}
