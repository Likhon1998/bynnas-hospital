<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\Patient;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTopbarTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_use_admin_search(): void
    {
        $response = $this->getJson(route('admin.search', ['q' => 'icu']));

        $response->assertUnauthorized();
    }

    public function test_staff_can_search_beds_and_patients(): void
    {
        $user = $this->createStaffUser(['beds.view', 'patients.view']);
        $ward = Ward::factory()->create(['name' => 'ICU A', 'code' => 'ICU-A', 'type' => 'icu']);
        $patient = Patient::factory()->create(['full_name' => 'Rahim Searchable']);
        Bed::factory()->occupied()->create([
            'ward_id' => $ward->id,
            'patient_id' => $patient->id,
            'bed_code' => 'ICU-A-77',
            'status' => 'occupied',
        ]);

        $response = $this->actingAs($user)->getJson(route('admin.search', ['q' => 'ICU-A-77']));

        $response->assertOk();
        $response->assertJsonFragment([
            'type' => 'Bed',
            'title' => 'ICU-A-77',
        ]);

        $patientResponse = $this->actingAs($user)->getJson(route('admin.search', ['q' => 'Rahim']));

        $patientResponse->assertOk();
        $patientResponse->assertJsonFragment([
            'type' => 'Patient',
            'title' => 'Rahim Searchable',
        ]);
    }

    public function test_topbar_shows_notification_badge_for_dirty_beds(): void
    {
        $user = $this->createStaffUser(['beds.view', 'operations.manage']);
        Bed::factory()->dirty()->create([
            'bed_code' => 'GEN-9-01',
            'status' => 'dirty',
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('data-admin-search', false);
        $response->assertSee('data-admin-notifications-url', false);
        $response->assertSee('Notifications', false);
        $response->assertSee('GEN-9-01');
        $response->assertSee('Sign out');
    }

    public function test_notification_feed_returns_live_bed_alerts_as_json(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        Bed::factory()->dirty()->create([
            'bed_code' => 'GEN-LIVE-01',
            'status' => 'dirty',
        ]);
        Bed::factory()->cleaning()->create([
            'bed_code' => 'GEN-LIVE-02',
            'status' => 'cleaning',
        ]);

        $response = $this->actingAs($user)->getJson(route('admin.notifications'));

        $response->assertOk();
        $response->assertJsonPath('total', 2);
        $response->assertJsonFragment(['title' => 'Bed GEN-LIVE-01 needs cleaning']);
        $response->assertJsonFragment(['title' => 'Bed GEN-LIVE-02 is being cleaned']);
        $this->assertNotEmpty($response->json('alerts.0.id'));
    }

    public function test_marking_notifications_read_clears_the_badge_and_keeps_the_recent_alerts(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        Bed::factory()->dirty()->create([
            'bed_code' => 'GEN-SEEN-01',
            'status' => 'dirty',
        ]);

        $this->actingAs($user)
            ->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('alerts.0.read', false);

        $this->actingAs($user)
            ->postJson(route('admin.notifications.seen'), ['all' => true])
            ->assertOk()
            ->assertJson(['ok' => true])
            ->assertJsonPath('total', 0)
            ->assertJsonPath('alerts.0.read', true)
            ->assertJsonFragment(['title' => 'Bed GEN-SEEN-01 needs cleaning']);

        $this->actingAs($user)
            ->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath('alerts.0.read', true)
            ->assertJsonFragment(['title' => 'Bed GEN-SEEN-01 needs cleaning']);
    }

    public function test_notification_bell_keeps_the_five_most_recent_alerts(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);

        foreach (range(1, 6) as $number) {
            Bed::factory()->dirty()->create([
                'bed_code' => 'GEN-KEEP-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'status' => 'dirty',
            ]);
        }

        $feed = $this->actingAs($user)->getJson(route('admin.notifications'));

        $feed->assertOk();
        $feed->assertJsonPath('total', 6);
        $this->assertCount(5, $feed->json('alerts'));
        $feed->assertJsonFragment(['title' => 'Bed GEN-KEEP-06 needs cleaning']);
        $feed->assertJsonMissing(['title' => 'Bed GEN-KEEP-01 needs cleaning']);

        $seen = $this->actingAs($user)->postJson(route('admin.notifications.seen'), ['all' => true]);

        $seen->assertOk();
        $seen->assertJsonPath('total', 0);
        $this->assertCount(5, $seen->json('alerts'));
        $seen->assertJsonPath('alerts.0.read', true);
        $seen->assertJsonFragment(['title' => 'Bed GEN-KEEP-06 needs cleaning']);
        $seen->assertJsonMissing(['title' => 'Bed GEN-KEEP-01 needs cleaning']);
    }

    public function test_fetching_the_notification_feed_does_not_mark_alerts_read(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        Bed::factory()->dirty()->create([
            'bed_code' => 'GEN-KEEP-01',
            'status' => 'dirty',
        ]);

        $this->actingAs($user)
            ->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 1);

        $this->actingAs($user)
            ->getJson(route('admin.notifications'))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonFragment(['title' => 'Bed GEN-KEEP-01 needs cleaning']);
    }

    public function test_guest_cannot_fetch_notification_feed(): void
    {
        $this->getJson(route('admin.notifications'))->assertUnauthorized();
    }

    public function test_guest_cannot_mark_notifications_seen(): void
    {
        $this->postJson(route('admin.notifications.seen'), ['ids' => ['bed:1']])->assertUnauthorized();
    }

    public function test_search_requires_two_characters(): void
    {
        $user = $this->createStaffUser(['beds.view']);

        $response = $this->actingAs($user)->getJson(route('admin.search', ['q' => 'a']));

        $response->assertOk();
        $response->assertExactJson(['results' => []]);
    }
}
