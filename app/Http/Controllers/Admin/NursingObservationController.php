<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecordNursingObservationRequest;
use App\Models\Patient;
use App\Support\NursingDesk;
use Illuminate\Http\RedirectResponse;

class NursingObservationController extends Controller
{
    public function store(RecordNursingObservationRequest $request, Patient $patient, NursingDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->recordObservation($patient, $request->validated(), $actor);

        return back()->with('status', 'Observation recorded.');
    }
}
