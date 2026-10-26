<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_matches_medical_landing_mockup(): void
    {
        $department = Department::factory()->create([
            'name' => 'Cardiology',
            'description' => 'Heart clinics and ward support.',
            'is_active' => true,
        ]);

        Doctor::factory()->create([
            'name' => 'Dr. Hamida Jannat',
            'department_id' => $department->id,
            'specialization' => 'Cardiologist & Medicine',
            'is_active' => true,
            'is_featured' => true,
        ]);

        Service::factory()->create([
            'title' => 'Emergency Care',
            'slug' => 'emergency-care',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('$250M', false);
        $response->assertSee('Patients Served Globally', false);
        $response->assertSee('We Offer Pro', false);
        $response->assertSee('DNA Testing', false);
        $response->assertSee('DNA Reports: 5.5K', false);
        $response->assertSee('Get Started', false);
        $response->assertSee('Trusted', false);
        $response->assertSee('Secure', false);
        $response->assertSee('Testing', false);
        $response->assertSee('99.9%', false);
        $response->assertSee('DNA Matching Rate', false);
        $response->assertSee('Meet Our Specialized', false);
        $response->assertSee('Medical Team', false);
        $response->assertSee('Dr. Hamida Jannat', false);
        $response->assertSee('Explore Our Medical', false);
        $response->assertSee('Departments', false);
        $response->assertSee('Latest From Medical', false);
        $response->assertSee('Frontlines', false);
        $response->assertSee('Ask The Desk', false);
    }
}
