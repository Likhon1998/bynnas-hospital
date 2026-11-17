<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_show_page_lists_related_doctors_and_services(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'slug' => 'cardiology',
            'description' => 'Heart clinics and ward support for cardiac patients.',
            'is_active' => true,
            'is_featured' => true,
        ]);
        $neurology = Department::factory()->create([
            'name' => 'Neurology',
            'slug' => 'neurology',
            'is_active' => true,
        ]);

        $doctor = Doctor::factory()->create([
            'name' => 'Dr. Hamida Jannat',
            'department_id' => $cardiology->id,
            'specialization' => 'Cardiologist',
            'is_active' => true,
        ]);
        $service = Service::factory()->create([
            'title' => 'ECG Check',
            'slug' => 'ecg-check',
            'department_id' => $cardiology->id,
            'summary' => 'Heart rhythm assessment.',
            'is_active' => true,
        ]);

        $response = $this->get(route('departments.show', $cardiology));

        $response->assertOk();
        $response->assertSee('Cardiology', false);
        $response->assertSee('Heart clinics and ward support for cardiac patients.', false);
        $response->assertSee('About this department', false);
        $response->assertSee('Dr. Hamida Jannat', false);
        $response->assertSee('ECG Check', false);
        $response->assertSee('Neurology', false);
        $response->assertSee(route('doctors', ['specialty' => 'Cardiology']), false);
        $response->assertSee(route('doctors.show', $doctor), false);
        $response->assertSee(route('services.show', $service), false);
        $response->assertSee(route('departments.show', $neurology), false);
    }

    public function test_inactive_department_show_page_is_not_found(): void
    {
        $department = Department::factory()->create([
            'name' => 'Hidden Ward',
            'slug' => 'hidden-ward',
            'is_active' => false,
        ]);

        $this->get(route('departments.show', $department))->assertNotFound();
        $this->get('/departments/missing-clinic')->assertNotFound();
    }
}
