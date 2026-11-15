<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeNoticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_published_notices_with_dates_before_departments(): void
    {
        Notice::factory()->create([
            'title' => 'OPD counters open at 8 AM',
            'body' => 'Collect a token before your visit.',
            'published_on' => '2026-11-14',
            'is_urgent' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);
        Notice::factory()->urgent()->create([
            'title' => 'Emergency attendant limit',
            'body' => 'One attendant per patient in triage.',
            'published_on' => '2026-11-15',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        Notice::factory()->inactive()->create([
            'title' => 'Draft desk notice',
            'published_on' => '2026-11-15',
        ]);
        Notice::factory()->create([
            'title' => 'Expired campus notice',
            'published_on' => '2026-10-01',
            'expires_on' => '2026-10-31',
            'is_active' => true,
        ]);
        Notice::factory()->create([
            'title' => 'Future notice',
            'published_on' => now()->addDays(3)->toDateString(),
            'is_active' => true,
        ]);

        $home = $this->get(route('home'));

        $home->assertOk();
        $home->assertSee('id="notices"', false);
        $home->assertSee('Gallery', false);
        $home->assertSee('Notices', false);
        $home->assertSee('data-gallery-slider', false);
        $home->assertSee('ch-spotlight', false);
        $home->assertSee('OPD counters open at 8 AM', false);
        $home->assertSee('Collect a token before your visit.', false);
        $home->assertSee('14 Nov 2026', false);
        $home->assertSee('Emergency attendant limit', false);
        $home->assertSee('15 Nov 2026', false);
        $home->assertSee('Urgent', false);
        $home->assertDontSee('Draft desk notice', false);
        $home->assertDontSee('Expired campus notice', false);
        $home->assertDontSee('Future notice', false);

        $content = $home->getContent();
        $noticesPos = strpos($content, 'id="notices"');
        $departmentsPos = strpos($content, 'id="departments"');

        $this->assertNotFalse($noticesPos);
        $this->assertNotFalse($departmentsPos);
        $this->assertLessThan($departmentsPos, $noticesPos);
        $this->assertLessThan(
            strpos($content, 'OPD counters open at 8 AM'),
            strpos($content, 'Emergency attendant limit'),
        );
    }

    public function test_staff_can_publish_a_notice_from_the_desk(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $this->actingAs($user)
            ->post(route('admin.notices.store'), [
                'title' => 'Pharmacy counter closed for stock check',
                'body' => 'Reopens at 2:00 PM today.',
                'published_on' => now()->toDateString(),
                'expires_on' => '',
                'is_urgent' => '1',
                'is_active' => '1',
                'sort_order' => '1',
            ])
            ->assertRedirect(route('admin.notices.index'));

        $this->assertDatabaseHas('notices', [
            'title' => 'Pharmacy counter closed for stock check',
            'is_urgent' => true,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Pharmacy counter closed for stock check', false)
            ->assertSee('Reopens at 2:00 PM today.', false);
    }

    public function test_home_hides_notices_section_when_none_are_published(): void
    {
        Notice::factory()->inactive()->create([
            'title' => 'Hidden notice',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="notices"', false)
            ->assertDontSee('Hidden notice', false);
    }

    public function test_home_shows_gallery_slider_beside_notices(): void
    {
        Notice::factory()->create([
            'title' => 'Blood bank hours this week',
            'body' => 'Donors welcome Saturday to Thursday.',
            'published_on' => '2026-11-15',
            'is_active' => true,
        ]);

        Gallery::query()->create([
            'title' => 'Main lobby after renovation',
            'image' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=1400&q=80',
            'caption' => 'Ground floor reception.',
            'album' => 'Campus',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Gallery::query()->create([
            'title' => 'Consultation corridor',
            'image' => 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?auto=format&fit=crop&w=1400&q=80',
            'caption' => 'OPD wing.',
            'album' => 'Campus',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $home = $this->get(route('home'));

        $home->assertOk();
        $home->assertSee('Gallery', false);
        $home->assertSee('data-gallery-slider', false);
        $home->assertSee('data-gallery-next', false);
        $home->assertSee('Main lobby after renovation', false);
        $home->assertSee('photo-1516549655169-df83a0774514', false);
        $home->assertSee('photo-1586773860418-d37222d8fce3', false);
        $home->assertSee('Blood bank hours this week', false);
        $home->assertSee('All notices →', false);
    }

    public function test_home_shows_at_most_five_notices_in_the_shared_panel(): void
    {
        foreach (range(1, 6) as $number) {
            Notice::factory()->create([
                'title' => "Desk notice number {$number}",
                'body' => "Body for notice {$number}.",
                'published_on' => now()->subDays($number)->toDateString(),
                'is_active' => true,
                'is_urgent' => false,
                'sort_order' => $number,
            ]);
        }

        $home = $this->get(route('home'));

        $home->assertOk();
        $home->assertSee('data-notice-slots="5"', false);
        $home->assertSee('Desk notice number 1', false);
        $home->assertSee('Desk notice number 5', false);
        $home->assertDontSee('Desk notice number 6', false);
    }
}
