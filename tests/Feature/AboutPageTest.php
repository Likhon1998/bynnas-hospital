<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_renders_successfully(): void
    {
        $response = $this->get(route('about'));

        $response->assertOk();
        $response->assertSee('About us', false);
        $response->assertSee('Our story', false);
        $response->assertSee('Named consultants', false);
        $response->assertSee('Visit the campus', false);
        $response->assertSee(route('doctors'), false);
    }

    public function test_about_page_band_shows_live_desk_counts(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);
        Department::factory()->create([
            'name' => 'Hidden Ward',
            'is_active' => false,
        ]);

        Doctor::factory()->count(2)->create([
            'department_id' => $cardiology->id,
            'is_active' => true,
            'is_featured' => true,
        ]);
        Doctor::factory()->create([
            'department_id' => $cardiology->id,
            'is_active' => false,
        ]);

        Patient::factory()->count(5)->create(['is_active' => true]);
        Service::factory()->count(3)->create([
            'department_id' => $cardiology->id,
            'is_active' => true,
        ]);

        $response = $this->get(route('about'));

        $response->assertOk();
        $response->assertSee('2 consultants across 1 specialty', false);
        $response->assertSee('data-stat="doctors"><strong>2</strong>', false);
        $response->assertSee('data-stat="departments"><strong>1</strong>', false);
        $response->assertSee('data-stat="patients"><strong>5</strong>', false);
        $response->assertSee('data-stat="services"><strong>3</strong>', false);
        $response->assertDontSee('data-stat="doctors"><strong>3</strong>', false);
    }
}
