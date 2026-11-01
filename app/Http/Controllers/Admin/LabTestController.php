<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLabTestRequest;
use App\Http\Requests\Admin\UpdateLabTestRequest;
use App\Models\LabTest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabTestController extends Controller
{
    public function index(Request $request): View
    {
        $tests = LabTest::query()
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where(function ($builder) use ($term): void {
                    $builder->where('code', 'like', '%'.$term.'%')
                        ->orWhere('name', 'like', '%'.$term.'%');
                });
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.labs.tests.index', [
            'tests' => $tests,
        ]);
    }

    public function create(): View
    {
        return view('admin.labs.tests.create');
    }

    public function store(StoreLabTestRequest $request): RedirectResponse
    {
        LabTest::query()->create($request->safe()->only([
            'code',
            'name',
            'category',
            'specimen',
            'unit',
            'reference_range',
            'critical_low',
            'critical_high',
            'price',
            'is_active',
        ]));

        return redirect()
            ->route('admin.labs.tests.index')
            ->with('status', 'Test added to the catalog.');
    }

    public function edit(LabTest $labTest): View
    {
        return view('admin.labs.tests.edit', [
            'test' => $labTest,
        ]);
    }

    public function update(UpdateLabTestRequest $request, LabTest $labTest): RedirectResponse
    {
        $labTest->update($request->safe()->only([
            'code',
            'name',
            'category',
            'specimen',
            'unit',
            'reference_range',
            'critical_low',
            'critical_high',
            'price',
            'is_active',
        ]));

        return redirect()
            ->route('admin.labs.tests.index')
            ->with('status', 'Catalog updated.');
    }
}
