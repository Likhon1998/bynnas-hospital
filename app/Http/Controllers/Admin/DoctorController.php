<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DoctorRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $doctors = Doctor::query()
            ->with('department')
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where(function ($builder) use ($term): void {
                    $builder->where('name', 'like', '%'.$term.'%')
                        ->orWhere('specialization', 'like', '%'.$term.'%');
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.doctors.index', compact('doctors'));
    }

    public function create(): View
    {
        return view('admin.doctors.create', [
            'doctor' => new Doctor(['is_active' => true, 'sort_order' => 0]),
            'departments' => Department::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(DoctorRequest $request): RedirectResponse
    {
        Doctor::query()->create($request->safe()->except(['_token']));

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor created.');
    }

    public function edit(Doctor $doctor): View
    {
        return view('admin.doctors.edit', [
            'doctor' => $doctor,
            'departments' => Department::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function update(DoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        $doctor->update($request->safe()->except(['_token']));

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor updated.');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        try {
            $doctor->delete();
        } catch (QueryException) {
            return back()->with('error', 'This doctor cannot be deleted while appointments are linked to the profile.');
        }

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor deleted.');
    }
}
