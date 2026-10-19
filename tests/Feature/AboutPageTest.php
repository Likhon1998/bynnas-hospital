<?php

namespace Tests\Feature;

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
    }
}
