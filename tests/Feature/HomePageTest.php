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

    public function test_home_page_matches_lakeside_landing(): void
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
        $response->assertSee('Exceptional Care.', false);
        $response->assertSee('Closer to Home.', false);
        $response->assertSee('Book an Appointment', false);
        $response->assertSee('Our Departments', false);
        $response->assertSee('Cardiology', false);
        $response->assertSee('Find the Right Doctor for You', false);
        $response->assertSee('Patient Services', false);
        $response->assertSee('Emergency Care', false);
        $response->assertSee('Our Expert Doctors', false);
        $response->assertSee('Dr. Hamida Jannat', false);
        $response->assertSee('Book Your Appointment Today', false);
        $response->assertSee('250+', false);
        $response->assertSee('Our Locations', false);
        $response->assertSee('Frequently Asked Questions', false);
        $response->assertSee('News & Updates', false);
        $response->assertSee('Your Health. Our Priority.', false);
    }

    public function test_home_page_lists_real_doctors_and_filters_by_specialty(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);
        $neurology = Department::factory()->create([
            'name' => 'Neurology',
            'is_active' => true,
        ]);

        Doctor::factory()->create([
            'name' => 'Dr. Hamida Jannat',
            'department_id' => $cardiology->id,
            'specialization' => 'Cardiologist',
            'is_active' => true,
            'is_featured' => true,
        ]);
        Doctor::factory()->create([
            'name' => 'Dr. Nabila Rahman',
            'department_id' => $neurology->id,
            'specialization' => 'Neurologist',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('Browse consultants', false);
        $home->assertSee('Dr. Hamida Jannat', false);
        $home->assertSee('Dr. Nabila Rahman', false);
        $home->assertSee('Search by a specialty', false);

        $filtered = $this->get(route('home', ['specialty' => 'Cardiology']));
        $filtered->assertOk();
        $filtered->assertSee('Dr. Hamida Jannat', false);
        $filtered->assertSee('Showing consultants for Cardiology', false);
    }
}
