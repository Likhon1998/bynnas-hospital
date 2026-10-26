<?php

namespace Tests\Feature;

use App\Models\Bed;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_without_patient_manage_cannot_open_create_form(): void
    {
        $user = $this->createStaffUser(['patients.view'], 'doctor');

        $this->actingAs($user)
            ->get(route('admin.patients.create'))
            ->assertForbidden();
    }

    public function test_authorized_staff_can_register_a_patient(): void
    {
        $user = $this->createStaffUser(['patients.view', 'patients.manage']);

        $response = $this->actingAs($user)->post(route('admin.patients.store'), [
            'full_name' => 'Nusrat Jahan',
            'phone' => '01710001122',
            'email' => 'nusrat@example.com',
            'gender' => 'female',
            'is_active' => '1',
        ]);

        $patient = Patient::query()->where('full_name', 'Nusrat Jahan')->first();

        $this->assertNotNull($patient);
        $response->assertRedirect(route('admin.patients.show', $patient));
        $this->assertDatabaseHas('patients', [
            'full_name' => 'Nusrat Jahan',
            'phone' => '01710001122',
            'is_active' => 1,
        ]);
    }

    public function test_registering_from_bed_board_returns_to_available_beds(): void
    {
        $user = $this->createStaffUser(['patients.view', 'patients.manage', 'beds.view', 'beds.operate']);

        $response = $this->actingAs($user)->post(route('admin.patients.store'), [
            'full_name' => 'Bed Admit Patient',
            'phone' => '01820003344',
            'is_active' => '1',
            'return_to' => 'beds',
        ]);

        $response->assertRedirect(route('admin.beds.index', ['status' => 'available']));
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('patients', [
            'full_name' => 'Bed Admit Patient',
            'phone' => '01820003344',
        ]);
    }

    public function test_bed_board_links_to_new_patient_when_staff_can_manage_patients(): void
    {
        $user = $this->createStaffUser(['beds.view', 'beds.operate', 'patients.manage']);
        Bed::factory()->create([
            'bed_code' => 'GEN-NEW-01',
            'status' => 'available',
        ]);

        $this->actingAs($user)
            ->get(route('admin.beds.index', ['status' => 'available']))
            ->assertOk()
            ->assertSee(route('admin.patients.create', ['return_to' => 'beds']), false)
            ->assertSee('New patient');
    }
}
