<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctors_page_lists_every_active_consultant(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);

        $hamida = Doctor::factory()->create([
            'name' => 'Dr. Hamida Jannat',
            'department_id' => $cardiology->id,
            'specialization' => 'Cardiologist',
            'is_active' => true,
        ]);
        Doctor::factory()->create([
            'name' => 'Dr. Hidden Consultant',
            'department_id' => $cardiology->id,
            'is_active' => false,
        ]);

        $response = $this->get(route('doctors'));

        $response->assertOk();
        $response->assertSee('Find a doctor', false);
        $response->assertSee('Narrow the roster', false);
        $response->assertSee('Browse by clinic', false);
        $response->assertSee('Dr. Hamida Jannat', false);
        $response->assertSee('1 consultant', false);
        $response->assertSee('Profile', false);
        $response->assertSee(route('doctors.show', $hamida), false);
        $response->assertDontSee('Dr. Hidden Consultant', false);
    }

    public function test_doctors_page_supports_name_sort_and_featured_filters(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);

        Doctor::factory()->create([
            'name' => 'Dr. Featured Heart',
            'department_id' => $cardiology->id,
            'is_featured' => true,
            'is_active' => true,
            'experience_years' => 8,
        ]);
        Doctor::factory()->create([
            'name' => 'Dr. Standard Heart',
            'department_id' => $cardiology->id,
            'is_featured' => false,
            'is_active' => true,
            'experience_years' => 20,
        ]);

        $this->get(route('doctors', ['q' => 'Featured']))
            ->assertOk()
            ->assertSee('Dr. Featured Heart', false)
            ->assertDontSee('Dr. Standard Heart', false)
            ->assertSee('Active:', false);

        $this->get(route('doctors', ['featured' => 1]))
            ->assertOk()
            ->assertSee('Dr. Featured Heart', false)
            ->assertDontSee('Dr. Standard Heart', false);

        $sorted = $this->get(route('doctors', ['sort' => 'experience']));
        $sorted->assertOk();
        $sorted->assertSeeInOrder(['Dr. Standard Heart', 'Dr. Featured Heart'], false);
    }

    public function test_doctor_profile_shows_public_consultant_details(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);
        $doctor = Doctor::factory()->create([
            'name' => 'Dr. Hamida Jannat',
            'slug' => 'dr-hamida-jannat',
            'department_id' => $cardiology->id,
            'designation' => 'Senior Consultant',
            'specialization' => 'Cardiologist',
            'qualifications' => 'MBBS, MD (Cardiology)',
            'experience_years' => 12,
            'biography' => 'Sees heart patients on the main campus.',
            'consultation_fee' => 1800,
            'is_active' => true,
        ]);
        DoctorSchedule::factory()->create([
            'doctor_id' => $doctor->id,
            'weekday' => 'saturday',
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
            'is_active' => true,
        ]);

        $response = $this->get(route('doctors.show', $doctor));

        $response->assertOk();
        $response->assertSee('Dr. Hamida Jannat', false);
        $response->assertSee('Senior Consultant', false);
        $response->assertSee('Cardiologist', false);
        $response->assertSee('MBBS, MD (Cardiology)', false);
        $response->assertSee('Sees heart patients on the main campus.', false);
        $response->assertSee('12 yrs', false);
        $response->assertSee('Saturday', false);
        $response->assertSee('9:00 AM', false);
        $response->assertSee('1:00 PM', false);
        $response->assertSee('Book this doctor', false);
        $response->assertSee(route('home', ['doctor' => $doctor->id]).'#appointment', false);
    }

    public function test_inactive_doctor_profile_is_not_found(): void
    {
        $doctor = Doctor::factory()->create([
            'name' => 'Dr. Hidden Consultant',
            'slug' => 'dr-hidden-consultant',
            'is_active' => false,
        ]);

        $this->get(route('doctors.show', $doctor))->assertNotFound();
        $this->get('/doctors/missing-consultant')->assertNotFound();
    }

    public function test_doctors_page_filters_by_specialty_and_shows_empty_clinics(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);
        Department::factory()->create([
            'name' => 'Oncology',
            'is_active' => true,
        ]);

        Doctor::factory()->create([
            'name' => 'Dr. Hamida Jannat',
            'department_id' => $cardiology->id,
            'specialization' => 'Cardiologist',
            'is_active' => true,
        ]);

        $filtered = $this->get(route('doctors', ['specialty' => 'Cardiology']));
        $filtered->assertOk();
        $filtered->assertSee('Dr. Hamida Jannat', false);
        $filtered->assertSee('Showing consultants for Cardiology', false);

        $empty = $this->get(route('doctors', ['specialty' => 'Oncology']));
        $empty->assertOk();
        $empty->assertSee('No consultants in this clinic yet', false);
        $empty->assertSee('Showing consultants for Oncology', false);
    }
}
