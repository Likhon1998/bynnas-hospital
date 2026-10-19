<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceShowPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_service_page_renders_successfully(): void
    {
        $service = Service::factory()->create([
            'title' => 'Emergency Care',
            'slug' => 'emergency-care',
            'summary' => 'A 24/7 desk for urgent assessment.',
            'is_active' => true,
        ]);

        $response = $this->get(route('services.show', $service));

        $response->assertOk();
        $response->assertSee('Emergency Care', false);
        $response->assertSee('What to expect', false);
    }

    public function test_inactive_service_page_returns_not_found(): void
    {
        $service = Service::factory()->create([
            'slug' => 'inactive-service',
            'is_active' => false,
        ]);

        $this->get(route('services.show', $service))->assertNotFound();
    }
}
