<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMedicationAdministrationRequest;
use App\Models\Patient;
use App\Support\NursingDesk;
use Illuminate\Http\RedirectResponse;

class MedicationAdministrationController extends Controller
{
    public function store(StoreMedicationAdministrationRequest $request, Patient $patient, NursingDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->administerMedication($patient, $request->validated(), $actor);

        return back()->with('status', 'Medication recorded.');
    }
}
