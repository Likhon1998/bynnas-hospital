<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::query()
            ->active()
            ->withCount([
                'doctors as consultants_count' => fn (Builder $query) => $query->active(),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('web.departments', [
            'departments' => $departments,
            'consultantTotal' => Doctor::query()->active()->count(),
        ]);
    }

    public function show(Department $department): View
    {
        abort_unless($department->is_active, 404);

        $doctors = Doctor::query()
            ->active()
            ->with(['department', 'doctorSchedules' => fn ($query) => $query->active()])
            ->where('department_id', $department->id)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->active()
            ->with('department')
            ->where('department_id', $department->id)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(6)
            ->get();

        $relatedDepartments = Department::query()
            ->active()
            ->withCount([
                'doctors as consultants_count' => fn (Builder $query) => $query->active(),
            ])
            ->where('id', '!=', $department->id)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(4)
            ->get();

        return view('web.department-show', [
            'department' => $department,
            'doctors' => $doctors,
            'services' => $services,
            'relatedDepartments' => $relatedDepartments,
        ]);
    }
}
