<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Department;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $services = Service::query()
            ->with('department')
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where('title', 'like', '%'.$term.'%');
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('admin.services.index', compact('services'));
    }

    public function create(): View
    {
        return view('admin.services.create', [
            'service' => new Service(['is_active' => true, 'sort_order' => 0]),
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        Service::query()->create($request->safe()->except(['_token']));

        return redirect()->route('admin.services.index')->with('status', 'Service created.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service' => $service,
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->safe()->except(['_token']));

        return redirect()->route('admin.services.index')->with('status', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.services.index')->with('status', 'Service deleted.');
    }
}
