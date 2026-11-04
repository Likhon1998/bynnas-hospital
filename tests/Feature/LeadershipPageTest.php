<?php

namespace Tests\Feature;

use App\Models\BoardMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadershipPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_person_role_shows_full_profile_on_the_same_page(): void
    {
        BoardMember::factory()->create([
            'name' => 'Prof. Dr. Anwar Hossain',
            'slug' => 'prof-dr-anwar-hossain',
            'title' => 'Founder',
            'role' => 'founder',
            'bio' => 'Founded the campus with consultant clinics.',
            'achievements' => ['Opened emergency desk', 'Built consultant roster'],
            'is_active' => true,
        ]);
        BoardMember::factory()->create([
            'name' => 'Hidden Co-founder',
            'slug' => 'hidden-co-founder',
            'title' => 'Co-founder',
            'role' => 'co_founder',
            'is_active' => true,
        ]);
        BoardMember::factory()->create([
            'name' => 'Draft Founder',
            'slug' => 'draft-founder',
            'title' => 'Founder',
            'role' => 'founder',
            'is_active' => false,
        ]);

        $this->get(route('about.leadership.role', 'founder'))
            ->assertOk()
            ->assertSee('Prof. Dr. Anwar Hossain', false)
            ->assertSee('Founded the campus with consultant clinics.', false)
            ->assertSee('Opened emergency desk', false)
            ->assertSee('Built consultant roster', false)
            ->assertSee('ch-lead-stage--solo', false)
            ->assertSee('ch-lead-profile--enter-left', false)
            ->assertDontSee('Hidden Co-founder', false)
            ->assertDontSee('Draft Founder', false)
            ->assertDontSee('Open profile', false);
    }

    public function test_two_people_in_a_role_show_side_by_side_profiles_with_enter_animations(): void
    {
        BoardMember::factory()->create([
            'name' => 'Prof. Dr. Anwar Hossain',
            'slug' => 'prof-dr-anwar-hossain',
            'title' => 'Founder',
            'role' => 'founder',
            'bio' => 'First founder bio.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        BoardMember::factory()->create([
            'name' => 'Mrs. Nasrin Akter',
            'slug' => 'mrs-nasrin-akter',
            'title' => 'Founder',
            'role' => 'founder',
            'bio' => 'Second founder bio.',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->get(route('about.leadership.role', 'founder'))
            ->assertOk()
            ->assertSee('Founder', false)
            ->assertSee('Prof. Dr. Anwar Hossain', false)
            ->assertSee('Mrs. Nasrin Akter', false)
            ->assertSee('First founder bio.', false)
            ->assertSee('Second founder bio.', false)
            ->assertSee('ch-lead-stage--pair', false)
            ->assertSee('ch-lead-profile--enter-left', false)
            ->assertSee('ch-lead-profile--enter-right', false)
            ->assertDontSee('Open profile', false);
    }

    public function test_unknown_role_returns_not_found(): void
    {
        $this->get(route('about.leadership.role', 'unknown-role'))
            ->assertNotFound();
    }

    public function test_old_person_url_redirects_to_the_role_page(): void
    {
        $member = BoardMember::factory()->create([
            'name' => 'Mrs. Nasrin Akter',
            'slug' => 'mrs-nasrin-akter',
            'title' => 'Co-founder',
            'role' => 'co_founder',
            'is_active' => true,
        ]);

        $this->get(route('about.people.show', $member))
            ->assertRedirect(route('about.leadership.role', 'co_founder'));
    }

    public function test_inactive_person_url_is_not_found(): void
    {
        $member = BoardMember::factory()->create([
            'slug' => 'inactive-leader',
            'is_active' => false,
        ]);

        $this->get(route('about.people.show', $member))
            ->assertNotFound();
    }
}
