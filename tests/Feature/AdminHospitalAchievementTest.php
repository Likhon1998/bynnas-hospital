<?php

namespace Tests\Feature;

use App\Models\HospitalAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHospitalAchievementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_without_content_permission_cannot_manage_achievements(): void
    {
        $user = $this->createStaffUser(['doctors.manage'], 'hospital_admin');

        $this->actingAs($user)
            ->get(route('admin.hospital-achievements.index'))
            ->assertForbidden();
    }

    public function test_authorized_staff_can_create_an_achievement_shown_on_about(): void
    {
        $user = $this->createStaffUser(['content.manage']);

        $this->actingAs($user)
            ->post(route('admin.hospital-achievements.store'), [
                'year' => '2018',
                'title' => 'Opened cardiac ICU',
                'description' => 'Dedicated intensive care for cardiac patients.',
                'is_active' => '1',
                'sort_order' => '2',
            ])
            ->assertRedirect(route('admin.hospital-achievements.index'));

        $achievement = HospitalAchievement::query()->firstOrFail();

        $this->assertSame('Opened cardiac ICU', $achievement->title);
        $this->assertTrue($achievement->is_active);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Our achievements', false)
            ->assertSee('Opened cardiac ICU', false)
            ->assertSee('2018', false)
            ->assertSee('Dedicated intensive care for cardiac patients.', false);
    }

    public function test_inactive_achievements_stay_off_the_about_page(): void
    {
        HospitalAchievement::factory()->create([
            'title' => 'Hidden Milestone',
            'is_active' => false,
        ]);
        HospitalAchievement::factory()->create([
            'title' => 'Visible Milestone',
            'is_active' => true,
        ]);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Visible Milestone', false)
            ->assertDontSee('Hidden Milestone', false);
    }
}
