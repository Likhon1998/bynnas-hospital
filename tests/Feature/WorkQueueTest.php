<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\NursingTask;
use App\Models\Patient;
use App\Models\Visit;
use App\Models\WorkItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_queue_history(): void
    {
        $this->get(route('admin.work.history'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_acknowledge_a_work_item(): void
    {
        $item = WorkItem::factory()->create();

        $this->post(route('admin.work.acknowledge', $item))
            ->assertRedirect(route('admin.login'));
    }

    public function test_each_role_sees_only_its_own_queue(): void
    {
        $receptionist = $this->createStaffUser(['appointments.view', 'appointments.manage'], 'receptionist');
        $nurse = $this->createStaffUser(['nursing.view', 'nursing.chart', 'beds.view'], 'nurse_station');
        $admin = $this->createStaffUser(['billing.manage', 'operations.manage'], 'hospital_admin');

        $appointment = Appointment::factory()->create([
            'appointment_no' => 'APT-ROLE-WEB',
            'source' => 'web',
            'status' => 'pending',
            'appointment_date' => now()->addDay()->toDateString(),
        ]);
        $task = NursingTask::factory()->create([
            'title' => 'Dressing change for ward 2',
            'task_type' => 'medication',
            'status' => 'pending',
            'due_at' => now()->addHour(),
        ]);
        $bill = Bill::factory()->create([
            'bill_no' => 'BILL-ROLE-UNPAID',
            'status' => 'issued',
            'total' => 1200,
        ]);

        $reception = $this->actingAs($receptionist)->get(route('admin.dashboard'));
        $reception->assertOk();
        $reception->assertSee('Reception Desk');
        $reception->assertSee('APT-ROLE-WEB');
        $reception->assertSee($appointment->patient->full_name);
        $reception->assertDontSee('Dressing change for ward 2');
        $reception->assertDontSee('BILL-ROLE-UNPAID');

        $nursing = $this->actingAs($nurse)->get(route('admin.dashboard'));
        $nursing->assertOk();
        $nursing->assertSee('Nursing Desk');
        $nursing->assertSee('Dressing change for ward 2');
        $nursing->assertSee($task->patient->full_name);
        $nursing->assertDontSee('APT-ROLE-WEB');
        $nursing->assertDontSee('BILL-ROLE-UNPAID');

        $administration = $this->actingAs($admin)->get(route('admin.dashboard'));
        $administration->assertOk();
        $administration->assertSee('Administration Desk');
        $administration->assertSee('BILL-ROLE-UNPAID');
        $administration->assertDontSee('APT-ROLE-WEB');
        $administration->assertDontSee('Dressing change for ward 2');
    }

    public function test_completed_task_leaves_the_active_queue_and_stays_in_history(): void
    {
        $nurse = $this->createStaffUser(['nursing.view', 'nursing.chart'], 'nurse_station');
        $task = NursingTask::factory()->overdue()->create([
            'title' => 'Late night observations',
            'task_type' => 'observation',
        ]);

        $this->actingAs($nurse)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Late night observations');

        $this->actingAs($nurse)
            ->from(route('admin.dashboard'))
            ->patch(route('admin.nursing.tasks.update', $task), [
                'status' => 'completed',
            ])
            ->assertRedirect();

        $desk = $this->actingAs($nurse)->get(route('admin.dashboard'));
        $desk->assertOk();
        $this->assertQueueSectionDoesNotContain($desk->getContent(), 'nursing', 'overdue_observation', 'Late night observations');

        $this->actingAs($nurse)
            ->get(route('admin.work.history'))
            ->assertOk()
            ->assertSee('Late night observations')
            ->assertSee($task->patient->full_name);

        $this->assertDatabaseHas('work_items', [
            'kind' => 'overdue_observation',
            'source_id' => $task->id,
            'status' => 'done',
        ]);
    }

    public function test_acknowledged_no_show_leaves_the_queue_and_is_not_reopened(): void
    {
        $receptionist = $this->createStaffUser(['appointments.view'], 'receptionist');
        $appointment = Appointment::factory()->create([
            'appointment_no' => 'APT-NOSHOW-1',
            'status' => 'no_show',
            'appointment_date' => now()->toDateString(),
        ]);

        $this->actingAs($receptionist)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('APT-NOSHOW-1');

        $item = WorkItem::query()->where('kind', 'no_show')->where('source_id', $appointment->id)->first();
        $this->assertNotNull($item);

        $this->actingAs($receptionist)
            ->from(route('admin.dashboard'))
            ->post(route('admin.work.acknowledge', $item))
            ->assertRedirect()
            ->assertSessionHas('status', 'Queue item acknowledged.');

        $desk = $this->actingAs($receptionist)->get(route('admin.dashboard'));
        $desk->assertOk();
        $this->assertQueueSectionDoesNotContain($desk->getContent(), 'reception', 'no_show', 'APT-NOSHOW-1');

        $this->actingAs($receptionist)
            ->get(route('admin.work.history'))
            ->assertOk()
            ->assertSee('APT-NOSHOW-1');

        $this->assertSame('done', $item->fresh()->status);
    }

    public function test_nurse_can_escalate_a_critical_item_to_administration(): void
    {
        $nurse = $this->createStaffUser(['nursing.view', 'nursing.chart'], 'nurse_station');
        $admin = $this->createStaffUser(['operations.manage'], 'hospital_admin');
        $task = NursingTask::factory()->overdue()->create([
            'title' => 'Missed neuro observations',
            'task_type' => 'observation',
        ]);

        $this->actingAs($nurse)->get(route('admin.dashboard'))->assertOk();

        $item = WorkItem::query()->where('kind', 'overdue_observation')->where('source_id', $task->id)->first();
        $this->assertNotNull($item);

        $this->actingAs($nurse)
            ->from(route('admin.dashboard'))
            ->post(route('admin.work.escalate', $item))
            ->assertRedirect()
            ->assertSessionHas('status', 'Critical item escalated to administration.');

        $this->assertNotNull($item->fresh()->escalated_at);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Escalated · Missed neuro observations');
    }

    public function test_receptionist_cannot_acknowledge_a_nursing_item(): void
    {
        $nurse = $this->createStaffUser(['nursing.view'], 'nurse_station');
        $receptionist = $this->createStaffUser(['appointments.view'], 'receptionist');
        $task = NursingTask::factory()->create([
            'title' => 'Hidden nursing task',
        ]);

        $this->actingAs($nurse)->get(route('admin.dashboard'))->assertOk();

        $item = WorkItem::query()->where('source_type', 'nursing_task')->where('source_id', $task->id)->first();
        $this->assertNotNull($item);

        $this->actingAs($receptionist)
            ->post(route('admin.work.acknowledge', $item))
            ->assertForbidden();
    }

    public function test_doctors_only_see_their_assigned_consultations(): void
    {
        $alphaUser = $this->createStaffUser(['visits.view'], 'doctor');
        $betaUser = $this->createStaffUser(['visits.view'], 'doctor');
        $alpha = Doctor::factory()->create([
            'user_id' => $alphaUser->id,
            'name' => 'Dr. Alpha Queue',
        ]);
        $beta = Doctor::factory()->create([
            'user_id' => $betaUser->id,
            'name' => 'Dr. Beta Queue',
        ]);
        $alphaVisit = Visit::factory()->waiting()->create([
            'doctor_id' => $alpha->id,
            'visit_no' => 'VIS-ALPHA-1',
        ]);
        $betaVisit = Visit::factory()->waiting()->create([
            'doctor_id' => $beta->id,
            'visit_no' => 'VIS-BETA-1',
        ]);

        $alphaDesk = $this->actingAs($alphaUser)->get(route('admin.dashboard'));
        $alphaDesk->assertOk();
        $alphaDesk->assertSee('Doctor Desk');
        $alphaDesk->assertSee('VIS-ALPHA-1');
        $alphaDesk->assertSee($alphaVisit->patient->full_name);
        $alphaDesk->assertDontSee('VIS-BETA-1');
        $alphaDesk->assertDontSee($betaVisit->patient->full_name);

        $betaDesk = $this->actingAs($betaUser)->get(route('admin.dashboard'));
        $betaDesk->assertOk();
        $betaDesk->assertSee('VIS-BETA-1');
        $betaDesk->assertDontSee('VIS-ALPHA-1');
    }

    public function test_dashboard_escapes_dangerous_patient_names_in_the_queue(): void
    {
        $receptionist = $this->createStaffUser(['appointments.view', 'visits.view'], 'receptionist');
        $patient = Patient::factory()->create([
            'full_name' => "O'Reilly <script>alert('xss')</script>",
        ]);
        Visit::factory()->waiting()->create([
            'patient_id' => $patient->id,
            'visit_no' => 'VIS-XSS-1',
        ]);

        $response = $this->actingAs($receptionist)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('VIS-XSS-1');
        $response->assertSee('&lt;script&gt;', false);
        $response->assertDontSee("<script>alert('xss')</script>", false);
    }

    private function assertQueueSectionDoesNotContain(string $html, string $desk, string $kind, string $needle): void
    {
        $matched = preg_match(
            '/<section id="queue-'.preg_quote($desk, '/').'-'.preg_quote($kind, '/').'".*?<\/section>/s',
            $html,
            $matches,
        );

        $this->assertSame(1, $matched);
        $this->assertStringNotContainsString($needle, $matches[0]);
        $this->assertStringContainsString('Queue clear.', $matches[0]);
    }
}
