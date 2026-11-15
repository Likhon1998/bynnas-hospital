<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
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
        $response->assertSee('Care you can count on.', false);
        $response->assertSee('Named consultants. Clear next steps.', false);
        $response->assertSee('Book an Appointment', false);
        $response->assertSee('Departments', false);
        $response->assertSee('Cardiology', false);
        $response->assertSee('Find a Doctor', false);
        $response->assertSee('Department Wise', false);
        $response->assertSee('Branch Wise', false);
        $response->assertSee('Doctor Name Wise', false);
        $response->assertSee(route('doctors', ['view' => 'department']), false);
        $response->assertDontSee('id="patients"', false);
        $response->assertSee('Services', false);
        $response->assertSee('Emergency Care', false);
        $response->assertDontSee('>Consultants<', false);
        $response->assertDontSee('All consultants', false);
        $response->assertSee('Dr. Hamida Jannat', false);
        $response->assertSee('Ready to book a slot?', false);
        $response->assertSee('How to get here', false);
        $response->assertDontSee('Questions before a visit', false);
        $response->assertDontSee('What to bring', false);
        $response->assertDontSee('Recent reviews', false);
        $response->assertSee('News &amp; updates', false);
        $response->assertDontSee('Need a clinic slot?', false);
        $response->assertDontSee('ch-priority', false);
        $response->assertSee('data-nav-toggle', false);
        $response->assertSee('id="mobile-navigation"', false);
        $response->assertSee('ch-menu__label', false);
        $response->assertSee('ch-dock', false);
        $response->assertSee('width=device-width', false);
    }

    public function test_home_does_not_show_consultants_grid(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);

        Doctor::factory()->create([
            'name' => 'Dr. Featured 1',
            'department_id' => $cardiology->id,
            'specialization' => 'Cardiologist',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertDontSee('id="doctors"', false);
        $home->assertDontSee('>Consultants<', false);
        $home->assertDontSee('All consultants', false);
        $home->assertDontSee('ch-docs-sec', false);
        $home->assertSee('Find a Doctor', false);
        $home->assertSee('Dr. Featured 1', false);
        $home->assertSee(route('doctors'), false);
        $home->assertSee(route('doctors', ['specialty' => 'Cardiology']), false);
    }

    public function test_home_finder_uses_live_consultants(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
            'is_featured' => true,
        ]);
        Doctor::factory()->create([
            'name' => 'Dr. Hamida Jannat',
            'department_id' => $cardiology->id,
            'specialization' => 'Cardiologist',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('consultant', false);
        $home->assertSee('Dr. Hamida Jannat', false);
        $home->assertSee('Find a Doctor', false);
        $home->assertSee(route('doctors'), false);
        $home->assertDontSee('id="find-doctor"', false);
        $home->assertDontSee('250+', false);
        $home->assertDontSee('Banani Clinic', false);
        $home->assertDontSee('Dr. Michael Anderson', false);
    }

    public function test_home_page_lists_active_departments_from_the_desk(): void
    {
        Department::factory()->create([
            'name' => 'Neonatal Care',
            'description' => 'Care for newborns after delivery.',
            'icon' => 'baby',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 20,
        ]);
        Department::factory()->create([
            'name' => 'Cardiology',
            'description' => 'Heart clinics and ward support.',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
        ]);
        Department::factory()->create([
            'name' => 'Hidden Ward',
            'is_active' => false,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Major departments', false);
        $response->assertDontSee('What we do', false);
        $response->assertSee('All departments', false);
        $response->assertSeeInOrder(['Neonatal Care', 'Cardiology']);
        $response->assertSee('Care for newborns after delivery.', false);
        $response->assertSee('Heart clinics and ward support.', false);
        $response->assertSee('ch-dept__icon', false);
        $response->assertSee('Find consultants', false);
        $response->assertDontSee('ch-dept__media', false);
        $response->assertDontSee('Hidden Ward', false);
        $response->assertDontSee('Skin, Hair & Nails', false);
    }

    public function test_home_shows_at_most_eight_departments_in_two_rows(): void
    {
        foreach (range(1, 9) as $number) {
            Department::factory()->create([
                'name' => "Clinic Unit {$number}",
                'description' => "Description for clinic unit {$number}.",
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => $number,
            ]);
        }

        $home = $this->get(route('home'));

        $home->assertOk();
        $home->assertSee('Clinic Unit 1', false);
        $home->assertSee('Clinic Unit 8', false);
        $home->assertDontSee('Clinic Unit 9', false);
    }

    public function test_home_booking_band_shows_live_desk_counts(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);
        Department::factory()->create([
            'name' => 'Hidden Ward',
            'is_active' => false,
        ]);

        Doctor::factory()->count(3)->create([
            'department_id' => $cardiology->id,
            'is_active' => true,
        ]);
        Doctor::factory()->create([
            'department_id' => $cardiology->id,
            'is_active' => false,
        ]);

        Patient::factory()->count(4)->create(['is_active' => true]);
        Patient::factory()->create(['is_active' => false]);

        Service::factory()->count(2)->create([
            'department_id' => $cardiology->id,
            'is_active' => true,
        ]);
        Service::factory()->create([
            'department_id' => $cardiology->id,
            'is_active' => false,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Ready to book a slot?', false);
        $response->assertSee('3 consultants across 1 specialty', false);
        $response->assertSee('data-stat="doctors"', false);
        $response->assertSee('data-count-to="3"', false);
        $response->assertSee('data-stat="departments"', false);
        $response->assertSee('data-count-to="1"', false);
        $response->assertSee('data-stat="patients"', false);
        $response->assertSee('data-count-to="4"', false);
        $response->assertSee('data-stat="services"', false);
        $response->assertSee('data-count-to="2"', false);
        $response->assertDontSee('data-count-to="250"', false);
        $response->assertDontSee('250+', false);
    }
}
