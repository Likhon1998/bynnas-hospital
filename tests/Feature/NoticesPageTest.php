<?php

namespace Tests\Feature;

use App\Models\Notice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_notices_page_lists_current_and_previous_notices(): void
    {
        Notice::factory()->create([
            'title' => 'OPD counters open at 8 AM',
            'body' => 'Collect a token before your visit.',
            'published_on' => now()->toDateString(),
            'is_active' => true,
        ]);
        Notice::factory()->create([
            'title' => 'Flu camp closed',
            'body' => 'October vaccination camp has ended.',
            'published_on' => '2026-10-01',
            'expires_on' => '2026-10-31',
            'is_active' => true,
        ]);
        Notice::factory()->inactive()->create([
            'title' => 'Draft desk notice',
            'published_on' => now()->toDateString(),
        ]);
        Notice::factory()->create([
            'title' => 'Future notice',
            'published_on' => now()->addDays(5)->toDateString(),
            'is_active' => true,
        ]);

        $page = $this->get(route('notices'));

        $page->assertOk();
        $page->assertSee('Current notices', false);
        $page->assertSee('Previous notices', false);
        $page->assertSee('OPD counters open at 8 AM', false);
        $page->assertSee('Collect a token before your visit.', false);
        $page->assertSee('Flu camp closed', false);
        $page->assertSee('October vaccination camp has ended.', false);
        $page->assertSee('Archived', false);
        $page->assertSee('Ended 31 Oct 2026', false);
        $page->assertSee(route('notices.show', Notice::query()->where('title', 'OPD counters open at 8 AM')->first()), false);
        $page->assertDontSee('Draft desk notice', false);
        $page->assertDontSee('Future notice', false);

        $content = $page->getContent();
        $this->assertLessThan(
            strpos($content, 'Previous notices'),
            strpos($content, 'Current notices'),
        );
        $this->assertLessThan(
            strpos($content, 'Flu camp closed'),
            strpos($content, 'OPD counters open at 8 AM'),
        );
    }

    public function test_home_links_to_the_notices_page(): void
    {
        $notice = Notice::factory()->create([
            'title' => 'Desk update',
            'published_on' => now()->toDateString(),
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('All notices', false)
            ->assertSee(route('notices'), false)
            ->assertSee(route('notices.show', $notice), false);
    }

    public function test_notice_show_page_displays_full_notice(): void
    {
        $notice = Notice::factory()->create([
            'title' => 'Pharmacy stock check',
            'body' => 'Prescriptions resume after the afternoon stock check.',
            'published_on' => now()->toDateString(),
            'is_active' => true,
        ]);

        $this->get(route('notices.show', $notice))
            ->assertOk()
            ->assertSee('Pharmacy stock check', false)
            ->assertSee('Prescriptions resume after the afternoon stock check.', false)
            ->assertSee('All notices', false);
    }

    public function test_inactive_notice_show_page_is_not_found(): void
    {
        $notice = Notice::factory()->inactive()->create([
            'title' => 'Hidden desk note',
            'published_on' => now()->toDateString(),
        ]);

        $this->get(route('notices.show', $notice))->assertNotFound();
    }
}
