<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $selectedDepartment = trim((string) $request->query('department', ''));

        $services = Service::query()
            ->active()
            ->with('department')
            ->when($selectedDepartment !== '', function (Builder $query) use ($selectedDepartment): void {
                $query->whereHas('department', function (Builder $departmentQuery) use ($selectedDepartment): void {
                    $departmentQuery->where('name', $selectedDepartment);
                });
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('web.services', [
            'services' => $services,
            'selectedDepartment' => $selectedDepartment,
            'departments' => Department::query()
                ->active()
                ->whereHas('services', fn (Builder $query) => $query->active())
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function show(Service $service): View
    {
        abort_unless($service->is_active, 404);

        $service->load('department');

        $relatedServices = Service::query()
            ->active()
            ->with('department')
            ->where('id', '!=', $service->id)
            ->when(
                $service->department_id,
                fn (Builder $query) => $query->where('department_id', $service->department_id),
            )
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        if ($relatedServices->count() < 3) {
            $extra = Service::query()
                ->active()
                ->with('department')
                ->where('id', '!=', $service->id)
                ->whereNotIn('id', $relatedServices->pluck('id'))
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->limit(3 - $relatedServices->count())
                ->get();

            $relatedServices = $relatedServices->concat($extra)->values();
        }

        $departmentDoctors = collect();

        if ($service->department_id) {
            $departmentDoctors = Doctor::query()
                ->active()
                ->with('department')
                ->where('department_id', $service->department_id)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->limit(4)
                ->get();
        }

        return view('web.service-show', [
            'service' => $service,
            'relatedServices' => $relatedServices,
            'departmentDoctors' => $departmentDoctors,
        ]);
    }
}
