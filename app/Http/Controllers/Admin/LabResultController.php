<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecordLabResultRequest;
use App\Models\LabOrder;
use App\Support\LabDesk;
use Illuminate\Http\RedirectResponse;

class LabResultController extends Controller
{
    public function store(RecordLabResultRequest $request, LabOrder $labOrder, LabDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $result = $desk->recordAndVerify($labOrder, $request->safe()->only([
            'value',
            'unit',
            'reference_range',
            'notes',
            'is_critical',
        ]), $actor);

        return back()->with('status', $result->is_critical
            ? 'Critical result verified. The ordering clinician has been notified.'
            : 'Result recorded and verified.');
    }
}
