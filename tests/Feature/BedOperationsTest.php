<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\BedEvent;
use App\Models\BedTransfer;
use App\Models\Patient;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BedOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_blocks_other_patients_but_allows_reserved_admit(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        $reservedPatient = Patient::factory()->create(['full_name' => 'Reserved Patient']);
        $otherPatient = Patient::factory()->create(['full_name' => 'Other Patient']);
        $bed = Bed::factory()->create(['bed_code' => 'GEN-1-10', 'status' => 'available']);

        $this->actingAs($user)->post(route('admin.beds.reserve', $bed), [
            'lock_version' => $bed->lock_version,
            'patient_id' => $reservedPatient->id,
            'reservation_note' => 'OR hold',
        ])->assertRedirect();

        $bed->refresh();

        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'reserved_patient_id' => $reservedPatient->id,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('bed_events', [
            'bed_id' => $bed->id,
            'event_type' => BedEvent::TYPE_RESERVED,
        ]);

        $this->actingAs($user)->put(route('admin.beds.update', $bed), [
            'lock_version' => $bed->lock_version,
            'status' => 'occupied',
            'patient_id' => $otherPatient->id,
        ])->assertSessionHasErrors('patient_id');

        $this->actingAs($user)->put(route('admin.beds.update', $bed), [
            'lock_version' => $bed->fresh()->lock_version,
            'status' => 'occupied',
            'patient_id' => $reservedPatient->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'status' => 'occupied',
            'patient_id' => $reservedPatient->id,
            'reserved_patient_id' => null,
        ]);
        $this->assertDatabaseHas('bed_events', [
            'bed_id' => $bed->id,
            'event_type' => BedEvent::TYPE_ADMITTED,
        ]);
    }

    public function test_maintenance_blocks_admission_without_changing_cleaning_status(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate', 'beds.maintain']);
        $patient = Patient::factory()->create();
        $bed = Bed::factory()->create(['bed_code' => 'ICU-A-03', 'status' => 'available']);

        $this->actingAs($user)->post(route('admin.beds.maintain', $bed), [
            'lock_version' => $bed->lock_version,
            'action' => 'start',
            'reason' => 'Oxygen outlet repair',
        ])->assertRedirect();

        $bed->refresh();
        $this->assertTrue($bed->is_maintenance);
        $this->assertSame('available', $bed->status);
        $this->assertFalse($bed->isAdmittable($patient->id));

        $this->actingAs($user)->put(route('admin.beds.update', $bed), [
            'lock_version' => $bed->lock_version,
            'status' => 'occupied',
            'patient_id' => $patient->id,
        ])->assertSessionHasErrors('status');

        $this->actingAs($user)->post(route('admin.beds.maintain', $bed), [
            'lock_version' => $bed->fresh()->lock_version,
            'action' => 'end',
        ])->assertRedirect();

        $this->assertFalse($bed->fresh()->is_maintenance);
        $this->assertDatabaseHas('bed_events', [
            'bed_id' => $bed->id,
            'event_type' => BedEvent::TYPE_MAINTENANCE_ENDED,
        ]);
    }

    public function test_transfer_moves_patient_and_marks_source_dirty(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        $ward = Ward::factory()->create();
        $patient = Patient::factory()->create();
        $from = Bed::factory()->occupied()->create([
            'ward_id' => $ward->id,
            'bed_code' => 'GEN-2-01',
            'patient_id' => $patient->id,
        ]);
        $to = Bed::factory()->create([
            'ward_id' => $ward->id,
            'bed_code' => 'GEN-2-02',
            'status' => 'available',
        ]);

        $this->actingAs($user)->post(route('admin.beds.transfer', $from), [
            'lock_version' => $from->lock_version,
            'to_lock_version' => $to->lock_version,
            'to_bed_id' => $to->id,
            'reason' => 'Step-down',
        ])->assertRedirect();

        $this->assertDatabaseHas('beds', [
            'id' => $from->id,
            'status' => 'dirty',
            'patient_id' => null,
        ]);
        $this->assertDatabaseHas('beds', [
            'id' => $to->id,
            'status' => 'occupied',
            'patient_id' => $patient->id,
        ]);
        $this->assertDatabaseHas('bed_transfers', [
            'patient_id' => $patient->id,
            'from_bed_id' => $from->id,
            'to_bed_id' => $to->id,
        ]);
        $this->assertSame(1, BedTransfer::query()->count());
    }

    public function test_inspection_pass_and_fail_require_correct_rules(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        $bed = Bed::factory()->cleaning()->create(['bed_code' => 'ISO-1-02']);

        $this->actingAs($user)->post(route('admin.beds.inspect', $bed), [
            'lock_version' => $bed->lock_version,
            'result' => 'fail',
        ])->assertSessionHasErrors('notes');

        $this->actingAs($user)->post(route('admin.beds.inspect', $bed), [
            'lock_version' => $bed->lock_version,
            'result' => 'fail',
            'notes' => 'Stains remain on rails',
        ])->assertRedirect();

        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'status' => 'dirty',
        ]);
        $this->assertDatabaseHas('bed_events', [
            'bed_id' => $bed->id,
            'event_type' => BedEvent::TYPE_INSPECTION_FAILED,
        ]);

        $bed->refresh();

        $this->actingAs($user)->put(route('admin.beds.update', $bed), [
            'lock_version' => $bed->lock_version,
            'status' => 'cleaning',
        ])->assertRedirect();

        $bed->refresh();

        $this->actingAs($user)->post(route('admin.beds.inspect', $bed), [
            'lock_version' => $bed->lock_version,
            'result' => 'pass',
        ])->assertRedirect();

        $this->assertDatabaseHas('beds', [
            'id' => $bed->id,
            'status' => 'available',
            'is_maintenance' => false,
        ]);
        $this->assertDatabaseHas('bed_events', [
            'bed_id' => $bed->id,
            'event_type' => BedEvent::TYPE_INSPECTION_PASSED,
        ]);
    }

    public function test_ready_to_admit_kpi_excludes_reserved_and_maintenance_beds(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate']);
        $ward = Ward::factory()->create();
        Bed::factory()->create(['ward_id' => $ward->id, 'bed_code' => 'A-01', 'status' => 'available']);
        Bed::factory()->maintenance()->create(['ward_id' => $ward->id, 'bed_code' => 'A-02']);
        Bed::factory()->reserved()->create(['ward_id' => $ward->id, 'bed_code' => 'A-03']);

        $response = $this->actingAs($user)->get(route('admin.beds.index'));

        $response->assertOk();
        $response->assertSee('Ready to admit');
        $response->assertSee('1 eligible now');
        $response->assertSee('Bed board');
    }
}
