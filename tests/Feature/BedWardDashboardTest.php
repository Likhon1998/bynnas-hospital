<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\Patient;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedWardDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_operations_staff_can_open_bed_and_wards_board(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        $ward = Ward::factory()->create([
            'name' => 'ICU A',
            'code' => 'ICU-A',
            'type' => 'icu',
        ]);
        $patient = Patient::factory()->create(['full_name' => 'Rahim Uddin']);
        Bed::factory()->occupied()->create([
            'ward_id' => $ward->id,
            'patient_id' => $patient->id,
            'bed_code' => 'ICU-A-01',
            'status' => 'occupied',
        ]);
        Bed::factory()->dirty()->create([
            'ward_id' => $ward->id,
            'bed_code' => 'ICU-A-02',
            'status' => 'dirty',
        ]);

        $response = $this->actingAs($user)->get(route('admin.beds.index'));

        $response->assertOk();
        $response->assertSee('Bed & Wards');
        $response->assertSee('Census beds');
        $response->assertSee('Bed board');
        $response->assertSee('ICU-A-01');
        $response->assertSee('Rahim Uddin');
        $response->assertSee('Discharge');
        $response->assertSee('ICU-A-02');
        $response->assertSee('Start cleaning');
        $response->assertSee('mc-beds', false);
        $response->assertSee('>Patient</th>', false);
        $response->assertSee('>Status</th>', false);
        $response->assertSee('Action</th>', false);
        $response->assertSee(route('admin.beds.index', ['status' => 'occupied']), false);
        $response->assertSee(route('admin.beds.index', ['status' => 'turnaround']), false);
        $response->assertDontSee('Care Timeline');
        $response->assertDontSee('Needs clean');
        $response->assertDontSee('Patient / hold');
        $response->assertDontSee('Cleaning status');
    }

    public function test_turnaround_filter_shows_dirty_and_cleaning_beds(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        Bed::factory()->dirty()->create(['bed_code' => 'GEN-DIRTY-1', 'status' => 'dirty']);
        Bed::factory()->cleaning()->create(['bed_code' => 'GEN-CLEAN-1', 'status' => 'cleaning']);
        Bed::factory()->occupied()->create(['bed_code' => 'GEN-OCC-1', 'status' => 'occupied']);

        $response = $this->actingAs($user)->get(route('admin.beds.index', ['status' => 'turnaround']));

        $response->assertOk();
        $response->assertSee('GEN-DIRTY-1');
        $response->assertSee('GEN-CLEAN-1');
        $response->assertDontSee('GEN-OCC-1');
    }

    public function test_staff_can_start_cleaning_on_dirty_bed(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        $bed = Bed::factory()->dirty()->create([
            'bed_code' => 'GEN-1-04',
            'status' => 'dirty',
        ]);

        $response = $this->actingAs($user)->put(route('admin.beds.update', $bed), [
            'lock_version' => $bed->lock_version,
            'status' => 'cleaning',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'status' => 'cleaning',
            'patient_id' => null,
        ]);
    }

    public function test_admit_requires_patient_and_marks_bed_occupied(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        $bed = Bed::factory()->create([
            'bed_code' => 'GEN-2-01',
            'status' => 'available',
        ]);
        $patient = Patient::factory()->create(['full_name' => 'Admit Patient']);

        $this->actingAs($user)->put(route('admin.beds.update', $bed), [
            'lock_version' => $bed->lock_version,
            'status' => 'occupied',
        ])->assertSessionHasErrors('patient_id');

        $this->actingAs($user)->put(route('admin.beds.update', $bed), [
            'lock_version' => $bed->lock_version,
            'status' => 'occupied',
            'patient_id' => $patient->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'status' => 'occupied',
            'patient_id' => $patient->id,
        ]);
        $this->assertNotNull($bed->fresh()->admitted_at);
    }

    public function test_discharge_moves_occupied_bed_to_dirty_and_clears_patient(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        $patient = Patient::factory()->create();
        $bed = Bed::factory()->occupied()->create([
            'bed_code' => 'ICU-B-02',
            'patient_id' => $patient->id,
        ]);

        $this->actingAs($user)->put(route('admin.beds.update', $bed), [
            'lock_version' => $bed->lock_version,
            'status' => 'dirty',
        ])->assertRedirect();

        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'status' => 'dirty',
            'patient_id' => null,
            'admitted_at' => null,
        ]);
    }

    public function test_illegal_status_jump_is_rejected(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        $bed = Bed::factory()->create([
            'bed_code' => 'ISO-1-01',
            'status' => 'available',
        ]);

        $this->actingAs($user)->put(route('admin.beds.update', $bed), [
            'lock_version' => $bed->lock_version,
            'status' => 'dirty',
        ])->assertSessionHasErrors('status');

        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'status' => 'available',
        ]);
    }

    public function test_patient_cannot_occupy_two_beds(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        $patient = Patient::factory()->create();
        Bed::factory()->occupied()->create([
            'bed_code' => 'GEN-1-01',
            'patient_id' => $patient->id,
        ]);
        $freeBed = Bed::factory()->create([
            'bed_code' => 'GEN-1-02',
            'status' => 'available',
        ]);

        $this->actingAs($user)->put(route('admin.beds.update', $freeBed), [
            'lock_version' => $freeBed->lock_version,
            'status' => 'occupied',
            'patient_id' => $patient->id,
        ])->assertSessionHasErrors('patient_id');
    }

    public function test_medcore_operation_pages_are_reachable(): void
    {
        $user = $this->createStaffUser(['operations.manage', 'settings.manage']);

        $this->actingAs($user)->get(route('admin.emergency'))->assertOk()->assertSee('Emergency Flow');
        $this->actingAs($user)->get(route('admin.alerts'))->assertOk()->assertSee('Critical Alerts');
        $this->actingAs($user)->get(route('admin.forecasts'))->assertOk()->assertSee('Capacity Forecasts');
        $this->actingAs($user)->get(route('admin.reports'))->assertOk()->assertSee('Operational Reports');
        $this->actingAs($user)->get(route('admin.help'))->assertOk()->assertSee('Help & Supports');
    }

    public function test_content_manager_cannot_open_bed_board(): void
    {
        $user = $this->createStaffUser(['content.manage'], 'content_manager');

        $this->actingAs($user)->get(route('admin.beds.index'))->assertForbidden();
    }
}
