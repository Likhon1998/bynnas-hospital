<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HealthPackageRequest;
use App\Models\HealthPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HealthPackageController extends Controller
{
    public function index(Request $request): View
    {
        $packages = HealthPackage::query()
            ->withCount('healthPackageItems')
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where('title', 'like', '%'.$term.'%');
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        return view('admin.health-packages.index', compact('packages'));
    }

    public function create(): View
    {
        return view('admin.health-packages.create', [
            'package' => new HealthPackage(['is_active' => true, 'sort_order' => 0, 'price' => 0]),
            'itemsText' => '',
        ]);
    }

    public function store(HealthPackageRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $package = HealthPackage::query()->create($request->safe()->except(['items', '_token']));
            $this->syncItems($package, $request->string('items')->toString());
        });

        return redirect()->route('admin.health-packages.index')->with('status', 'Health package created.');
    }

    public function edit(HealthPackage $healthPackage): View
    {
        $healthPackage->load('healthPackageItems');

        return view('admin.health-packages.edit', [
            'package' => $healthPackage,
            'itemsText' => $healthPackage->healthPackageItems->sortBy('sort_order')->pluck('title')->implode("\n"),
        ]);
    }

    public function update(HealthPackageRequest $request, HealthPackage $healthPackage): RedirectResponse
    {
        DB::transaction(function () use ($request, $healthPackage): void {
            $healthPackage->update($request->safe()->except(['items', '_token']));
            $this->syncItems($healthPackage, $request->string('items')->toString());
        });

        return redirect()->route('admin.health-packages.index')->with('status', 'Health package updated.');
    }

    public function destroy(HealthPackage $healthPackage): RedirectResponse
    {
        $healthPackage->delete();

        return redirect()->route('admin.health-packages.index')->with('status', 'Health package deleted.');
    }

    private function syncItems(HealthPackage $package, string $itemsText): void
    {
        $titles = collect(preg_split('/\r\n|\r|\n/', $itemsText) ?: [])
            ->map(fn (string $title): string => trim($title))
            ->filter()
            ->values();

        $package->healthPackageItems()->delete();

        foreach ($titles as $index => $title) {
            $package->healthPackageItems()->create([
                'title' => $title,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
