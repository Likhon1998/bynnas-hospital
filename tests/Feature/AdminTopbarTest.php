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
        $response->assertSee('Notifications', false);
        $response->assertSee('GEN-9-01');
        $response->assertSee('Sign out');
    }

    public function test_search_requires_two_characters(): void
    {
        $user = $this->createStaffUser(['beds.view']);

        $response = $this->actingAs($user)->getJson(route('admin.search', ['q' => 'a']));

        $response->assertOk();
        $response->assertExactJson(['results' => []]);
    }
}
