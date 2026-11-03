<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Faq;
use App\Models\HealthPackage;
use App\Models\HospitalSetting;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteContentAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_without_content_permission_cannot_open_website_desks(): void
    {
        $user = $this->createStaffUser(['doctors.manage'], 'hospital_admin');

        $this->actingAs($user)
            ->get(route('admin.banners.index'))
            ->assertForbidden();
    }

    public function test_authorized_staff_can_open_every_website_desk(): void
    {
        $user = $this->createStaffUser(['content.manage', 'settings.manage', 'doctors.manage']);

        $this->actingAs($user);

        $this->get(route('admin.services.index'))->assertOk()->assertSee('Website', false);
        $this->get(route('admin.banners.index'))->assertOk()->assertSee('Add banner', false);
        $this->get(route('admin.banners.create'))->assertOk()->assertSee('Homepage hero', false);
        $this->get(route('admin.faqs.index'))->assertOk();
        $this->get(route('admin.faqs.create'))->assertOk();
        $this->get(route('admin.blog-posts.index'))->assertOk()->assertSee('Categories', false);
        $this->get(route('admin.blog-posts.create'))->assertOk();
        $this->get(route('admin.blog-categories.index'))->assertOk()->assertSee('News categories', false);
        $this->get(route('admin.testimonials.index'))->assertOk();
        $this->get(route('admin.testimonials.create'))->assertOk();
        $this->get(route('admin.galleries.index'))->assertOk();
        $this->get(route('admin.galleries.create'))->assertOk();
        $this->get(route('admin.health-packages.index'))->assertOk();
        $this->get(route('admin.health-packages.create'))->assertOk();
        $this->get(route('admin.settings.edit'))->assertOk();
        $this->get(route('admin.doctors.index'))->assertOk();
        $this->get(route('admin.departments.index'))->assertOk();
    }

    public function test_website_submenu_marks_only_the_open_desk_current(): void
    {
        $user = $this->createStaffUser(['content.manage']);
        $banners = route('admin.banners.index');
        $services = route('admin.services.index');
        $faqs = route('admin.faqs.index');

        $onBanners = $this->actingAs($user)->get($banners);

        $onBanners->assertOk();
        $this->assertSame(1, substr_count($onBanners->getContent(), 'admin-nav-link is-sub is-active'));
        $onBanners->assertSee('href="'.$banners.'"', false);
        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote($banners, '/').'"[^>]*is-active/',
            $onBanners->getContent(),
        );
        $this->assertDoesNotMatchRegularExpression(
            '/href="'.preg_quote($services, '/').'"[^>]*is-active/',
            $onBanners->getContent(),
        );
        $this->assertDoesNotMatchRegularExpression(
            '/href="'.preg_quote($faqs, '/').'"[^>]*is-active/',
            $onBanners->getContent(),
        );

        $onServices = $this->actingAs($user)->get($services);

        $onServices->assertOk();
        $this->assertSame(1, substr_count($onServices->getContent(), 'admin-nav-link is-sub is-active'));
        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote($services, '/').'"[^>]*is-active/',
            $onServices->getContent(),
        );
        $this->assertDoesNotMatchRegularExpression(
            '/href="'.preg_quote($banners, '/').'"[^>]*is-active/',
            $onServices->getContent(),
        );
    }

    public function test_unknown_banner_placement_is_rejected(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $response = $this->actingAs($user)
            ->from(route('admin.banners.create'))
            ->post(route('admin.banners.store'), [
                'title' => 'Night clinic',
                'image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=1400&q=80',
                'placement' => 'homepage',
                'is_active' => '1',
                'sort_order' => '1',
            ]);

        $response->assertRedirect(route('admin.banners.create'));
        $response->assertSessionHasErrors(['placement']);
        $this->assertSame(0, Banner::query()->count());
    }

    public function test_hero_emergency_and_promo_banners_appear_on_the_public_site(): void
    {
        $user = $this->createStaffUser(['content.manage']);
        $photo = 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=1400&q=80';

        $this->actingAs($user)
            ->post(route('admin.banners.store'), [
                'title' => 'Night Clinic Opens in Banani',
                'subtitle' => 'Walk-in consultant cover after sunset.',
                'image' => $photo,
                'cta_label' => 'See hours',
                'cta_url' => '/about',
                'placement' => 'hero',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.banners.index'));

        $this->actingAs($user)
            ->post(route('admin.banners.store'), [
                'title' => 'Stroke desk open now',
                'image' => $photo,
                'placement' => 'emergency',
                'cta_url' => '/emergency',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.banners.index'));

        $this->actingAs($user)
            ->post(route('admin.banners.store'), [
                'title' => 'Executive Health Checkup',
                'subtitle' => 'Full screening before the work week.',
                'image' => $photo,
                'cta_label' => 'See packages',
                'cta_url' => '/health-packages',
                'placement' => 'promo',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.banners.index'));

        $home = $this->get(route('home'));

        $home->assertOk();
        $home->assertSee('Night Clinic Opens in Banani', false);
        $home->assertSee('Walk-in consultant cover after sunset.', false);
        $home->assertSee('See hours', false);
        $home->assertSee('photo-1586773860418-d37222d8fce3', false);
        $home->assertSee('Stroke desk open now', false);
        $home->assertSee('Executive Health Checkup', false);
        $home->assertSee('See packages', false);
        $home->assertSee(route('packages'), false);
        $home->assertDontSee('Exceptional Care.', false);
    }

    public function test_creating_an_faq_and_review_shows_them_on_the_homepage(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $this->actingAs($user)
            ->post(route('admin.faqs.store'), [
                'question' => 'Can I book a named consultant online?',
                'answer' => 'Yes. Choose the doctor, then pick a free clinic slot.',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.faqs.index'));

        $this->actingAs($user)
            ->post(route('admin.testimonials.store'), [
                'display_name' => 'Farhana Rahman',
                'display_role' => 'Patient, Banani',
                'quote' => 'Reception named the consultant before I arrived.',
                'rating' => '5',
                'photo' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.testimonials.index'));

        $home = $this->get(route('home'));

        $home->assertOk();
        $home->assertSee('Can I book a named consultant online?', false);
        $home->assertSee('Yes. Choose the doctor, then pick a free clinic slot.', false);
        $home->assertSee('Reception named the consultant before I arrived.', false);
        $home->assertSee('Farhana Rahman', false);
        $home->assertSee('photo-1544005313-94ddf0286df2', false);
    }

    public function test_gallery_photo_from_the_desk_appears_on_the_homepage(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $this->actingAs($user)
            ->post(route('admin.galleries.store'), [
                'title' => 'Main lobby after renovation',
                'image' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=1400&q=80',
                'caption' => 'Ground floor reception.',
                'album' => 'Campus',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.galleries.index'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('photo-1516549655169-df83a0774514', false);
    }

    public function test_publishing_an_article_shows_it_on_news_and_home(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $this->actingAs($user)
            ->post(route('admin.blog-categories.store'), [
                'name' => 'Visit guides',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.blog-categories.index'));

        $category = BlogCategory::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.blog-posts.store'), [
                'blog_category_id' => (string) $category->id,
                'title' => 'Ward 4 visiting hours change next week',
                'excerpt' => 'Families can visit from 4pm on weekdays.',
                'body' => 'Bring one adult attendant only.',
                'cover_image' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=900&q=80',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.blog-posts.index'));

        $post = BlogPost::query()->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Ward 4 visiting hours change next week', false)
            ->assertSee(route('news.show', $post), false);

        $this->get(route('news'))
            ->assertOk()
            ->assertSee('Ward 4 visiting hours change next week', false);

        $this->get(route('news.show', $post))
            ->assertOk()
            ->assertSee('Bring one adult attendant only.', false);
    }

    public function test_creating_a_health_package_shows_it_on_the_packages_page(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $this->actingAs($user)
            ->post(route('admin.health-packages.store'), [
                'title' => 'Executive Heart Screen',
                'price' => '8500',
                'duration_label' => 'Half day',
                'description' => 'ECG, lipid panel, and consultant review.',
                'items' => "ECG\nLipid panel\nConsultant review",
                'is_featured' => '1',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.health-packages.index'));

        $this->get(route('packages'))
            ->assertOk()
            ->assertSee('Executive Heart Screen', false)
            ->assertSee('ECG', false)
            ->assertSee('Consultant review', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Executive Heart Screen', false)
            ->assertSee('Health Packages', false)
            ->assertSee(route('packages'), false);
    }

    public function test_featured_service_from_the_desk_appears_on_the_homepage(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $this->actingAs($user)
            ->post(route('admin.services.store'), [
                'title' => 'Neonatal Intensive Care',
                'summary' => 'Specialist care for newborns who need extra support.',
                'is_featured' => '1',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.services.index'));

        $service = Service::query()->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Neonatal Intensive Care', false)
            ->assertSee('Specialist care for newborns who need extra support.', false);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('Neonatal Intensive Care', false);
    }

    public function test_saving_hospital_settings_updates_the_public_site(): void
    {
        $user = $this->createStaffUser(['settings.manage']);
        HospitalSetting::query()->create([
            'key' => 'hospital_name',
            'group' => 'general',
            'value' => 'Old Campus Name',
        ]);
        HospitalSetting::query()->create([
            'key' => 'address',
            'group' => 'contact',
            'value' => 'Old address line',
        ]);
        HospitalSetting::query()->create([
            'key' => 'privacy_content',
            'group' => 'content',
            'value' => 'Old privacy copy.',
        ]);

        $this->actingAs($user)
            ->put(route('admin.settings.update'), [
                'settings' => [
                    'hospital_name' => 'Riverside General Hospital',
                    'address' => '88 Riverside Road, Banani, Dhaka',
                    'privacy_content' => 'Families may request a printed record at reception.',
                ],
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Riverside', false)
            ->assertSee('88 Riverside Road, Banani, Dhaka', false)
            ->assertDontSee('Old Campus Name', false);

        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('Families may request a printed record at reception.', false);
    }

    public function test_authorized_staff_can_upload_hospital_logo_and_see_it_publicly(): void
    {
        Storage::fake('public');

        $user = $this->createStaffUser(['settings.manage']);
        HospitalSetting::ensureDefaults();
        HospitalSetting::query()->where('key', 'logo')->update([
            'value' => 'https://images.example.com/old-logo.png',
        ]);

        $upload = UploadedFile::fake()->image('hospital-logo.png', 320, 80);

        $this->actingAs($user)
            ->post(route('admin.settings.update'), [
                '_method' => 'PUT',
                'settings' => [
                    'hospital_name' => 'Lakeside Medical Center',
                    'tagline' => 'Care closer to home',
                    'logo' => 'https://images.example.com/old-logo.png',
                ],
                'logo_upload' => $upload,
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $logo = HospitalSetting::query()->where('key', 'logo')->value('value');

        $this->assertStringStartsWith('/storage/branding/', (string) $logo);
        Storage::disk('public')->assertExists(ltrim(str_replace('/storage/', '', (string) $logo), '/'));

        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('Lakeside Medical Center', false);
        $home->assertSee('Care closer to home', false);
        $home->assertSee($logo, false);
        $home->assertDontSee('https://images.example.com/old-logo.png', false);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($logo, false);
    }

    public function test_authorized_staff_can_upload_a_banner_image(): void
    {
        Storage::fake('public');

        $user = $this->createStaffUser(['content.manage']);
        $upload = UploadedFile::fake()->image('hero-banner.jpg', 1600, 700);

        $this->actingAs($user)
            ->post(route('admin.banners.store'), [
                'title' => 'Dawn clinic open',
                'subtitle' => 'Walk-ins from 7am.',
                'image_upload' => $upload,
                'cta_label' => 'Book now',
                'cta_url' => '/doctors',
                'placement' => 'hero',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.banners.index'));

        $banner = Banner::query()->firstOrFail();

        $this->assertStringStartsWith('/storage/banners/', $banner->image);
        Storage::disk('public')->assertExists(ltrim(str_replace('/storage/', '', $banner->image), '/'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Dawn clinic open', false)
            ->assertSee($banner->image, false);
    }

    public function test_settings_desk_shows_branding_upload_controls(): void
    {
        $user = $this->createStaffUser(['settings.manage', 'content.manage']);

        $this->actingAs($user)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Brand identity', false)
            ->assertSee('Upload logo', false)
            ->assertSee('Hospital name', false)
            ->assertSee(route('admin.banners.index'), false);
    }

    public function test_creating_a_department_and_doctor_shows_them_on_the_website(): void
    {
        $user = $this->createStaffUser(['doctors.manage']);

        $this->actingAs($user)
            ->post(route('admin.departments.store'), [
                'name' => 'Cardiology',
                'description' => 'Heart clinics and ward support.',
                'is_featured' => '1',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.departments.index'));

        $department = Department::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.doctors.store'), [
                'department_id' => (string) $department->id,
                'name' => 'Dr. Hamida Jannat',
                'designation' => 'Senior Consultant',
                'specialization' => 'Cardiologist',
                'is_featured' => '1',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.doctors.index'));

        $doctor = Doctor::query()->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Cardiology', false)
            ->assertSee('Dr. Hamida Jannat', false);

        $this->get(route('doctors.show', $doctor))
            ->assertOk()
            ->assertSee('Dr. Hamida Jannat', false)
            ->assertSee('Cardiologist', false);
    }

    public function test_inactive_website_records_stay_off_the_public_site(): void
    {
        $user = $this->createStaffUser(['content.manage']);
        $faq = Faq::factory()->create([
            'question' => 'Visible clinic question',
            'answer' => 'Book at reception.',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->put(route('admin.faqs.update', $faq), [
                'question' => 'Visible clinic question',
                'answer' => 'Book at reception.',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.faqs.index'));

        $this->assertFalse($faq->fresh()->is_active);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Visible clinic question', false);

        $this->actingAs($user)
            ->post(route('admin.testimonials.store'), [
                'display_name' => 'Hidden Reviewer',
                'quote' => 'This quote must stay off the homepage.',
                'rating' => '4',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.testimonials.index'));

        $this->assertFalse(Testimonial::query()->firstOrFail()->is_active);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('This quote must stay off the homepage.', false);

        $this->actingAs($user)
            ->post(route('admin.health-packages.store'), [
                'title' => 'Draft Wellness Screen',
                'price' => '1200',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.health-packages.index'));

        $this->assertFalse(HealthPackage::query()->firstOrFail()->is_active);

        $this->get(route('packages'))
            ->assertOk()
            ->assertDontSee('Draft Wellness Screen', false);
    }
}
