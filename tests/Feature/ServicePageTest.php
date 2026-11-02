<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_page_renders_successfully(): void
    {
        $response = $this->get(route('services'));

        $response->assertOk();
        $response->assertSee('Our Services', false);
        $response->assertSee('How a visit works', false);
        $response->assertSee(route('home').'#appointment', false);
    }

    public function test_services_index_lists_active_desks_with_photos_and_detail_links(): void
    {
        $service = Service::factory()->create([
            'title' => 'Laboratory',
            'slug' => 'laboratory',
            'summary' => 'Pathology tests and sample collection on site.',
            'image' => 'https://images.unsplash.com/photo-1579154204601-01588f351e67?auto=format&fit=crop&w=1400&q=80',
            'is_active' => true,
            'is_featured' => true,
        ]);
        Service::factory()->create([
            'title' => 'Hidden Infusion Lounge',
            'slug' => 'hidden-infusion-lounge',
            'is_active' => false,
            'sort_order' => 99,
        ]);

        $response = $this->get(route('services'));

        $response->assertOk();
        $response->assertSee('Laboratory', false);
        $response->assertSee('Pathology tests and sample collection on site.', false);
        $response->assertSee('photo-1579154204601-01588f351e67', false);
        $response->assertSee(route('services.show', $service), false);
        $response->assertDontSee('Hidden Infusion Lounge', false);
        $response->assertDontSee(route('services.show', 'hidden-infusion-lounge'), false);
    }

    public function test_services_index_can_filter_by_department(): void
    {
        $cardiology = Department::factory()->create([
            'name' => 'Cardiology',
            'is_active' => true,
        ]);
        $ecg = Service::factory()->create([
            'title' => 'Live ECG Trace Desk',
            'slug' => 'live-ecg-trace-desk',
            'department_id' => $cardiology->id,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        Service::factory()->count(6)->create([
            'is_active' => true,
            'sort_order' => 2,
        ]);
        $pharmacy = Service::factory()->create([
            'title' => 'Hidden Infusion Lounge XYZ',
            'slug' => 'hidden-infusion-lounge-xyz',
            'department_id' => null,
            'is_active' => true,
            'sort_order' => 99,
        ]);

        $response = $this->get(route('services', ['department' => 'Cardiology']));

        $response->assertOk();
        $response->assertSee('Desks in Cardiology', false);
        $response->assertSee(route('services.show', $ecg), false);
        $response->assertDontSee(route('services.show', $pharmacy), false);
    }
}
