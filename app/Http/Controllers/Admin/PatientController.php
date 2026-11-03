<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PatientRequest;
use App\Models\HospitalSetting;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $patients = Patient::query()
            ->when($request->string('q')->toString(), function ($query, string $term): void {
                $query->where(function ($builder) use ($term): void {
                    $builder->where('full_name', 'like', '%'.$term.'%')
                        ->orWhere('patient_code', 'like', '%'.$term.'%')
                        ->orWhere('phone', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%');
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.patients.index', compact('patients'));
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'appointments' => fn ($query) => $query->with(['doctor', 'department'])->latest('appointment_date')->latest('id'),
            'visits' => fn ($query) => $query->with(['doctor', 'events'])->latest('arrived_at')->latest('id'),
            'bills' => fn ($query) => $query->latest('bill_date')->latest('id')->limit(10),
            'bed.ward',
            'currentAdmission',
            'labOrders' => fn ($query) => $query->with(['test', 'result', 'visit'])->latest('id')->limit(10),
        ]);

        return view('admin.patients.show', compact('patient'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('patients.manage') ?? false, 403);

        return view('admin.patients.create', [
            'returnTo' => $request->string('return_to')->toString(),
        ]);
    }

    public function store(PatientRequest $request): RedirectResponse
    {
        $patient = Patient::query()->create($request->safe()->except(['_token', 'return_to']));

        if (
            $request->string('return_to')->toString() === 'beds'
            && ($request->user()?->can('beds.view') ?? false)
        ) {
            return redirect()
                ->route('admin.beds.index', ['status' => 'available'])
                ->with('status', 'Patient '.$patient->full_name.' registered. Select them to admit.');
        }

        return redirect()
            ->route('admin.patients.show', $patient)
            ->with('status', 'Patient registered.');
    }

    public function edit(Request $request, Patient $patient): View
    {
        abort_unless($request->user()?->can('patients.manage') ?? false, 403);

        return view('admin.patients.edit', compact('patient'));
    }

    public function update(PatientRequest $request, Patient $patient): RedirectResponse
    {
        $patient->update($request->safe()->except(['_token']));

        return redirect()->route('admin.patients.show', $patient)->with('status', 'Patient updated.');
    }

    public function printSlip(Patient $patient): View
    {
        $patient->load([
            'appointments' => fn ($query) => $query->with('doctor')->latest('appointment_date')->limit(5),
            'bills' => fn ($query) => $query->latest('bill_date')->limit(5),
        ]);

        return view('admin.patients.print-slip', [
            'patient' => $patient,
            'hospital' => HospitalSetting::map(),
        ]);
    }
}
