<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\BedEvent;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BedHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_bed_event_history_page_is_available_to_floor_staff(): void
    {
        $user = $this->createStaffUser(['beds.view'], 'nurse_station');
        $bed = Bed::factory()->create(['bed_code' => 'GEN-9-01']);
        BedEvent::factory()->create([
            'bed_id' => $bed->id,
            'event_type' => BedEvent::TYPE_CLEANING_STARTED,
            'notes' => 'Housekeeping started on GEN-9-01',
        ]);

        $response = $this->actingAs($user)->get(route('admin.beds.events'));

        $response->assertOk();
        $response->assertSee('Bed History');
        $response->assertSee('GEN-9-01');
        $response->assertSee('Housekeeping started on GEN-9-01');
    }

    public function test_stale_lock_version_is_rejected(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        $patient = Patient::factory()->create();
        $bed = Bed::factory()->create([
            'bed_code' => 'ICU-LOCK-1',
            'status' => 'available',
            'lock_version' => 3,
        ]);

        $this->actingAs($user)->put(route('admin.beds.update', $bed), [
            'lock_version' => 2,
            'status' => 'occupied',
            'patient_id' => $patient->id,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'status' => 'available',
            'patient_id' => null,
            'lock_version' => 3,
        ]);
    }

    public function test_expire_reservations_command_clears_due_holds(): void
    {
        $patient = Patient::factory()->create();
        $bed = Bed::factory()->create([
            'bed_code' => 'RES-EXP-1',
            'status' => 'available',
            'reserved_patient_id' => $patient->id,
            'reserved_until' => now()->subMinute(),
            'reservation_note' => 'Expired hold',
            'reserved_at' => now()->subHours(2),
            'lock_version' => 1,
        ]);

        Artisan::call('beds:expire-reservations');

        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'reserved_patient_id' => null,
            'reserved_until' => null,
            'lock_version' => 2,
        ]);
        $this->assertDatabaseHas('bed_events', [
            'bed_id' => $bed->id,
            'event_type' => BedEvent::TYPE_RESERVATION_EXPIRED,
        ]);
    }

    public function test_nurse_station_can_operate_beds_but_not_operations_desk(): void
    {
        $user = $this->createStaffUser([
            'beds.view',
            'beds.operate',
            'beds.maintain',
            'patients.view',
            'appointments.view',
        ], 'nurse_station');

        $this->actingAs($user)->get(route('admin.beds.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.beds.events'))->assertOk();
        $this->actingAs($user)->get(route('admin.emergency'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.alerts'))->assertForbidden();
    }

    public function test_content_manager_cannot_open_bed_history(): void
    {
        $user = $this->createStaffUser(['content.manage'], 'content_manager');

        $this->actingAs($user)->get(route('admin.beds.events'))->assertForbidden();
    }
}
