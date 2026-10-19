<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->active()
            ->with('department')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();

        $featuredServices = $services->where('is_featured', true)->take(6)->values();

        if ($featuredServices->isEmpty()) {
            $featuredServices = $services->take(6)->values();
        }

        return view('web.services', [
            'services' => $services,
            'featuredServices' => $featuredServices,
            'departments' => Department::query()
                ->active()
                ->whereHas('services', fn ($query) => $query->active())
                ->with(['services' => fn ($query) => $query->active()->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get(),
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
                fn ($query) => $query->where('department_id', $service->department_id),
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
