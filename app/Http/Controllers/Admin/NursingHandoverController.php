<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNursingHandoverRequest;
use App\Models\Ward;
use App\Support\NursingDesk;
use Illuminate\Http\RedirectResponse;

class NursingHandoverController extends Controller
{
    public function store(StoreNursingHandoverRequest $request, Ward $ward, NursingDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->recordHandover($ward, $request->validated(), $actor);

        return back()->with('status', 'Shift handover recorded.');
    }
}
