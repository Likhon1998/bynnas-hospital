<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_departments_page_lists_active_clinics_from_the_desk(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'description' => 'Heart clinics and ward support.',
            'is_active' => true,
            'is_featured' => true,
        ]);
        Department::factory()->create([
            'name' => 'Hidden Ward',
            'is_active' => false,
        ]);
        Doctor::factory()->create([
            'name' => 'Dr. Hamida Jannat',
            'department_id' => $cardiology->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('departments'));

        $response->assertOk();
        $response->assertSee('Our Departments', false);
        $response->assertSee('Cardiology', false);
        $response->assertSee('Heart clinics and ward support.', false);
        $response->assertSee('1 consultant', false);
        $response->assertSee(route('doctors', ['specialty' => 'Cardiology']), false);
        $response->assertDontSee('Hidden Ward', false);
    }

    public function test_home_view_all_departments_links_to_the_directory(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee(route('departments'), false);
        $response->assertSee('View all Departments', false);
    }
}
