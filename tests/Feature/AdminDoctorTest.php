<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDoctorTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_staff_can_open_the_doctor_roster(): void
    {
        $user = $this->createStaffUser(['doctors.manage']);
        $department = Department::factory()->create(['name' => 'Cardiology']);
        Doctor::factory()->create([
            'name' => 'Dr. Arif Rahman',
            'department_id' => $department->id,
            'designation' => 'Senior Consultant',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.doctors.index'))
            ->assertOk()
            ->assertSee('Clinical roster')
            ->assertSee('Add doctor')
            ->assertSee('Departments')
            ->assertSee('Dr. Arif Rahman')
            ->assertSee('Cardiology')
            ->assertSee(route('admin.doctors.create'), false)
            ->assertSee(route('admin.departments.index'), false)
            ->assertDontSee('table-frame--fill', false);
    }
}
