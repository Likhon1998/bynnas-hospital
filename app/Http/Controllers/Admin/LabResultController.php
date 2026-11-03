<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecordLabResultRequest;
use App\Http\Requests\Admin\ReturnLabResultRequest;
use App\Http\Requests\Admin\VerifyLabResultRequest;
use App\Models\LabOrder;
use App\Support\LabDesk;
use Illuminate\Http\RedirectResponse;

class LabResultController extends Controller
{
    public function store(RecordLabResultRequest $request, LabOrder $labOrder, LabDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->assertCanModifyResult($labOrder, $actor);

        $result = $desk->recordPreliminary($labOrder, $request->safe()->only([
            'value',
            'unit',
            'reference_range',
            'notes',
            'findings',
            'is_critical',
            'is_abnormal',
        ]), $actor);

        return back()->with('status', $result->is_critical
            ? 'Critical preliminary result submitted for pathologist verification.'
            : 'Preliminary result submitted for pathologist verification.');
    }

    public function verify(VerifyLabResultRequest $request, LabOrder $labOrder, LabDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $result = $desk->verifyResult($labOrder, $request->safe()->only([
            'value',
            'unit',
            'reference_range',
            'notes',
            'findings',
            'is_critical',
            'is_abnormal',
            'signature_name',
            'signature_title',
        ]), $actor);

        return back()->with('status', $result->is_critical
            ? 'Critical final report approved and published. The ordering clinician has been notified.'
            : 'Final report approved, signed, and published.');
    }

    public function returnForCorrection(ReturnLabResultRequest $request, LabOrder $labOrder, LabDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->returnForCorrection($labOrder, $request->validated('return_reason'), $actor);

        return back()->with('status', 'Report returned to the laboratory for correction.');
    }
}
