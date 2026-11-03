<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\HealthPackage;
use App\Models\HealthPackageItem;
use App\Models\HospitalSetting;
use App\Models\Service;
use App\Models\Testimonial;
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
        $response->assertDontSee('Exceptional Care.', false);
    }

    public function test_home_patient_services_list_featured_desk_records(): void
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
        $response->assertSee('Neonatal Intensive Care', false);
        $response->assertSee('Specialist care for newborns who need extra support.', false);
        $response->assertDontSee('Hidden Ward Service', false);
        $response->assertDontSee('Patient Portal', false);
        $response->assertDontSee('Insurance & Billing', false);
    }

    public function test_home_faq_and_reviews_come_from_the_desk(): void
    {
        Faq::factory()->create([
            'question' => 'Can I book a named consultant online?',
            'answer' => 'Yes. Choose the doctor, then pick a free clinic slot.',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        Faq::factory()->create([
            'question' => 'Hidden draft question',
            'answer' => 'Should not appear.',
            'is_active' => false,
        ]);
        Testimonial::query()->create([
            'display_name' => 'Rahima Akter',
            'display_role' => 'Patient, Dhaka',
            'quote' => 'The team explained each step clearly.',
            'rating' => 5,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Can I book a named consultant online?', false);
        $response->assertSee('Yes. Choose the doctor, then pick a free clinic slot.', false);
        $response->assertDontSee('Hidden draft question', false);
        $response->assertSee('The team explained each step clearly.', false);
        $response->assertSee('Rahima Akter', false);
        $response->assertDontSee('Jessica M.', false);
        $response->assertDontSee('2,340 reviews', false);
        $response->assertSee('photo-1519494026892-80bbd2d6fd0d', false);
        $response->assertSee('photo-1579684385127-1ef15d508118', false);
        $response->assertDontSee('Main lobby (demo)', false);
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

    public function test_home_shows_active_health_packages_from_the_desk(): void
    {
        $package = HealthPackage::query()->create([
            'title' => 'Executive Heart Screen',
            'slug' => 'executive-heart-screen',
            'price' => 8500,
            'duration_label' => 'Half day',
            'description' => 'ECG, lipid panel, and consultant review.',
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        HealthPackageItem::query()->create([
            'health_package_id' => $package->id,
            'title' => 'ECG',
            'sort_order' => 1,
        ]);
        HealthPackage::query()->create([
            'title' => 'Hidden Draft Package',
            'slug' => 'hidden-draft-package',
            'price' => 1200,
            'description' => 'Should not appear.',
            'is_featured' => true,
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Health Packages', false);
        $response->assertSee('Executive Heart Screen', false);
        $response->assertSee('ECG', false);
        $response->assertSee(route('packages'), false);
        $response->assertDontSee('Hidden Draft Package', false);
    }
}
