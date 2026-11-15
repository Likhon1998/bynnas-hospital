<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\HospitalSetting;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_hero_uses_the_published_banner(): void
    {
        Banner::query()->create([
            'title' => 'Night Clinic Opens in Gulshan',
            'subtitle' => 'Walk-in consultant cover after sunset.',
            'image' => 'https://placehold.co/1600x700/0F766E/FFFFFF?text=Hero',
            'cta_label' => 'See hours',
            'cta_url' => '/about',
            'placement' => 'hero',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Night Clinic Opens in Gulshan', false);
        $response->assertSee('Walk-in consultant cover after sunset.', false);
        $response->assertSee('See hours', false);
        $response->assertDontSee('See a consultant. Leave with a plan.', false);
    }

    public function test_home_services_appear_in_the_services_nav_dropdown(): void
    {
        Service::factory()->create([
            'title' => 'Neonatal Intensive Care',
            'slug' => 'nicu-live',
            'summary' => 'Specialist care for newborns who need extra support.',
            'is_active' => true,
            'is_featured' => true,
        ]);
        Service::factory()->create([
            'title' => 'Hidden Ward Service',
            'slug' => 'hidden-ward-service',
            'is_active' => false,
            'is_featured' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('id="patients"', false);
        $response->assertDontSee('Patient services', false);
        $response->assertSee('Neonatal Intensive Care', false);
        $response->assertSee(route('services.show', 'nicu-live'), false);
        $response->assertSee('All services', false);
        $response->assertDontSee('Hidden Ward Service', false);
        $response->assertDontSee('Specialist care for newborns who need extra support.', false);
    }

    public function test_home_no_longer_shows_faq_guides_and_reviews_trio(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('id="faq"', false);
        $response->assertDontSee('Questions before a visit', false);
        $response->assertDontSee('What to bring', false);
        $response->assertDontSee('Recent reviews', false);
    }

    public function test_home_news_and_campus_use_published_records(): void
    {
        HospitalSetting::query()->create([
            'key' => 'address',
            'group' => 'contact',
            'value' => '88 Riverside Road, Banani, Dhaka',
        ]);
        HospitalSetting::query()->create([
            'key' => 'campus_name',
            'group' => 'contact',
            'value' => 'Banani Riverside Campus',
        ]);
        $post = BlogPost::factory()->create([
            'title' => 'Ward 4 visiting hours change next week',
            'excerpt' => 'Families can visit from 4pm on weekdays.',
            'cover_image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=900&q=80',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
        BlogPost::factory()->create([
            'title' => 'Draft campus memo',
            'is_published' => false,
            'published_at' => null,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Ward 4 visiting hours change next week', false);
        $response->assertSee('Families can visit from 4pm on weekdays.', false);
        $response->assertSee('photo-1576091160399-112ba8d25d1d', false);
        $response->assertSee(route('news.show', $post), false);
        $response->assertDontSee('Draft campus memo', false);
        $response->assertDontSee('Cityhealth Medical Center Earns Top Safety Rating', false);
        $response->assertSee('88 Riverside Road, Banani, Dhaka', false);
        $response->assertSee('Banani Riverside Campus', false);
    }
}
