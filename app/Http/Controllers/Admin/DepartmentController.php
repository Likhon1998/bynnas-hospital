<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DepartmentRequest;
use App\Models\Department;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $departments = Department::query()
            ->withCount('doctors')
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where('name', 'like', '%'.$term.'%');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('admin.departments.create', ['department' => new Department(['is_active' => true, 'sort_order' => 0])]);
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        Department::query()->create($request->safe()->except(['_token']));

        return redirect()->route('admin.departments.index')->with('status', 'Department created.');
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->safe()->except(['_token']));

        return redirect()->route('admin.departments.index')->with('status', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        try {
            $department->delete();
        } catch (QueryException) {
            return back()->with('error', 'This department cannot be deleted while doctors, services, or appointments are linked to it.');
        }

        return redirect()->route('admin.departments.index')->with('status', 'Department deleted.');
    }
}
