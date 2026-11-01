<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNursingTaskRequest;
use App\Http\Requests\Admin\UpdateNursingTaskRequest;
use App\Models\NursingTask;
use App\Models\Patient;
use App\Support\NursingDesk;
use Illuminate\Http\RedirectResponse;

class NursingTaskController extends Controller
{
    public function store(StoreNursingTaskRequest $request, Patient $patient, NursingDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->createTask($patient, $request->validated(), $actor);

        return back()->with('status', 'Nursing task added.');
    }

    public function update(UpdateNursingTaskRequest $request, NursingTask $task, NursingDesk $desk): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor !== null, 403);

        $desk->updateTaskStatus($task, $request->validated('status'), $actor);

        return back()->with('status', 'Task updated.');
    }
}
