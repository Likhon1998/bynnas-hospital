<?php

namespace Tests\Feature;

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
        $response->assertSee('Our services', false);
        $response->assertSee('Complete hospital support', false);
    }

    public function test_services_index_links_to_service_detail_pages(): void
    {
        $service = Service::factory()->create([
            'title' => 'Laboratory',
            'slug' => 'laboratory',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $response = $this->get(route('services'));

        $response->assertOk();
        $response->assertSee(route('services.show', $service), false);
    }
}
