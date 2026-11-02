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
        $response->assertSee('Our Locations', false);
        $response->assertSee('Frequently Asked Questions', false);
        $response->assertSee('News & Updates', false);
        $response->assertSee('Your Health. Our Priority.', false);
        $response->assertSee('data-nav-toggle', false);
        $response->assertSee('id="mobile-navigation"', false);
        $response->assertSee('ch-menu__label', false);
        $response->assertSee('ch-dock', false);
        $response->assertSee('width=device-width', false);
    }

    public function test_home_expert_doctors_shows_eight_and_ignores_finder_search(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);
        $gynecology = Department::factory()->create([
            'name' => 'Gynecology & Obstetrics',
            'is_active' => true,
        ]);

        foreach (range(1, 8) as $index) {
            Doctor::factory()->create([
                'name' => 'Dr. Featured '.$index,
                'department_id' => $cardiology->id,
                'specialization' => 'Cardiologist',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => $index,
            ]);
        }

        Doctor::factory()->create([
            'name' => 'Dr. Extra Consultant',
            'department_id' => $cardiology->id,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 99,
        ]);
        Doctor::factory()->create([
            'name' => 'Dr. Anika Rahman',
            'department_id' => $gynecology->id,
            'specialization' => 'General Gynaecology',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 100,
        ]);

        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('Browse consultants', false);
        $home->assertSee('Dr. Featured 1', false);
        $home->assertSee('Dr. Featured 8', false);
        $home->assertSee('View all Doctors', false);
        $home->assertSee(route('doctors'), false);
        $home->assertSee('<strong>Dr. Featured 1</strong>', false);
        $home->assertSee('<strong>Dr. Featured 8</strong>', false);
        $home->assertDontSee('<strong>Dr. Extra Consultant</strong>', false);
        $home->assertDontSee('<strong>Dr. Anika Rahman</strong>', false);
        $home->assertDontSee('Clear search', false);

        $filteredHome = $this->get(route('home', ['specialty' => 'Gynecology & Obstetrics']));
        $filteredHome->assertOk();
        $filteredHome->assertSee('Dr. Featured 1', false);
        $filteredHome->assertSee('Dr. Featured 8', false);
        $filteredHome->assertSee('1 consultant in Gynecology', false);
        $filteredHome->assertSee('<strong>Dr. Anika Rahman</strong>', false);
        $filteredHome->assertDontSee('Clear search', false);
    }

    public function test_home_finder_lists_every_consultant_in_the_selected_specialty(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);
        $neurology = Department::factory()->create([
            'name' => 'Neurology',
            'is_active' => true,
        ]);

        foreach (range(1, 7) as $index) {
            Doctor::factory()->create([
                'name' => 'Dr. Cardio '.$index,
                'department_id' => $cardiology->id,
                'specialization' => 'Cardiologist',
                'is_active' => true,
                'is_featured' => $index <= 4,
                'sort_order' => $index,
            ]);
        }

        Doctor::factory()->create([
            'name' => 'Dr. Nabila Rahman',
            'department_id' => $neurology->id,
            'specialization' => 'Neurologist',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $filtered = $this->get(route('home', ['specialty' => 'Cardiology']));

        $filtered->assertOk();
        $filtered->assertSee('7 consultants in Cardiology', false);
        $filtered->assertSee(route('home').'#find-doctor', false);

        foreach (range(1, 7) as $index) {
            $filtered->assertSee('<strong>Dr. Cardio '.$index.'</strong>', false);
        }

        $empty = $this->get(route('home', ['specialty' => 'Oncology']));
        $empty->assertOk();
        $empty->assertSee('No consultants in this clinic yet', false);
        $empty->assertSee('is-vacant', false);
        $empty->assertSee('<strong data-finder-count>0</strong>', false);
        $empty->assertSee('No consultants in this clinic', false);
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
        $home->assertSee('Specialist', false);
        $home->assertSee('Dr. Hamida Jannat', false);
        $home->assertSee('Search by a specialty', false);
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
        $response->assertSeeInOrder(['Neonatal Care', 'Cardiology']);
        $response->assertSee('title="Care for newborns after delivery."', false);
        $response->assertSee('title="Heart clinics and ward support."', false);
        $response->assertDontSee('Hidden Ward', false);
        $response->assertDontSee('Skin, Hair & Nails', false);
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
        $response->assertSee('Book Your Appointment Today', false);
        $response->assertSee('3 consultants across 1 specialty', false);
        $response->assertSee('data-stat="doctors"><strong>3</strong>', false);
        $response->assertSee('data-stat="departments"><strong>1</strong>', false);
        $response->assertSee('data-stat="patients"><strong>4</strong>', false);
        $response->assertSee('data-stat="services"><strong>2</strong>', false);
        $response->assertDontSee('data-stat="doctors"><strong>4</strong>', false);
        $response->assertDontSee('250+', false);
    }
}
