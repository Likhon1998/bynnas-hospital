<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_without_doctor_permission_cannot_manage_departments(): void
    {
        $user = $this->createStaffUser(['content.manage'], 'content_manager');

        $response = $this->actingAs($user)->get(route('admin.departments.index'));

        $response->assertForbidden();
    }

    public function test_empty_department_payload_is_rejected(): void
    {
        $user = $this->createStaffUser(['doctors.manage']);

        $response = $this->actingAs($user)
            ->from(route('admin.departments.create'))
            ->post(route('admin.departments.store'), []);

        $response->assertRedirect(route('admin.departments.create'));
        $response->assertSessionHasErrors(['name']);
        $this->assertSame(0, Department::query()->count());
    }

    public function test_authorized_staff_can_create_a_department(): void
    {
        $user = $this->createStaffUser(['doctors.manage']);

        $response = $this->actingAs($user)->post(route('admin.departments.store'), [
            'name' => 'Cardiology',
            'description' => 'Heart care unit',
            'is_active' => '1',
            'sort_order' => '1',
        ]);

        $response->assertRedirect(route('admin.departments.index'));
        $this->assertDatabaseHas('departments', [
            'name' => 'Cardiology',
            'slug' => 'cardiology',
            'is_active' => 1,
        ]);
    }
}
