<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DoctorRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        Doctor::query()->create($this->doctorPayload($request));

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor created.');
    }

    public function edit(Doctor $doctor): View
    {
        $doctor->load([
            'doctorLeaves' => fn ($query) => $query->orderByDesc('starts_on')->orderByDesc('id'),
            'doctorSchedules',
        ]);

        return view('admin.doctors.edit', [
            'doctor' => $doctor,
            'departments' => Department::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'hours' => DoctorSchedule::formStateFor($doctor),
        ]);
    }

    public function update(DoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        $doctor->update($this->doctorPayload($request, $doctor->photo));

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor updated.');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        try {
            $this->deleteStoredPhoto($doctor->photo);
            $doctor->delete();
        } catch (QueryException) {
            return back()->with('error', 'This doctor cannot be deleted while appointments are linked to the profile.');
        }

        return redirect()->route('admin.doctors.index')->with('status', 'Doctor deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function doctorPayload(DoctorRequest $request, ?string $currentPhoto = null): array
    {
        $data = $request->safe()->except(['photo_upload']);

        if ($request->hasFile('photo_upload')) {
            $path = $request->file('photo_upload')->store('doctors', 'public');
            // Store a root-relative URL so the photo works on any host/domain.
            $data['photo'] = '/storage/'.$path;
            $this->deleteStoredPhoto($currentPhoto);
        }

        return $data;
    }

    private function deleteStoredPhoto(?string $photo): void
    {
        if (! filled($photo)) {
            return;
        }

        $path = parse_url($photo, PHP_URL_PATH) ?: $photo;

        if (! is_string($path) || ! str_contains($path, '/storage/doctors/')) {
            return;
        }

        Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', $path), '/'));
    }
}
