<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\NursingAuditEvent;
use App\Models\NursingTask;
use App\Models\Patient;
use App\Models\Ward;
use App\Support\NursingDesk;
use Illuminate\View\View;

class NursingController extends Controller
{
    public function index(NursingDesk $desk): View
    {
        $wards = $desk->wards();

        return view('admin.nursing.index', [
            'wards' => $wards,
            'counts' => [
                'patients' => $wards->sum('occupied_beds_count'),
                'due' => $wards->sum('open_tasks_count'),
                'overdue' => $wards->sum('overdue_tasks_count'),
            ],
        ]);
    }

    public function ward(Ward $ward, NursingDesk $desk): View
    {
        abort_unless($ward->is_active, 404);

        $board = $desk->wardBoard($ward);

        return view('admin.nursing.ward', [
            'ward' => $ward,
            'beds' => $board['beds'],
            'tasks' => $board['tasks'],
            'handovers' => $board['handovers'],
            'outstanding' => $board['outstanding'],
            'counts' => $board['counts'],
        ]);
    }

    public function chart(Patient $patient): View
    {
        $bed = Bed::query()
            ->occupied()
            ->where('patient_id', $patient->id)
            ->with('ward')
            ->first();

        abort_if($bed === null, 404);

        $patient->load('latestClinicalVisit');

        return view('admin.nursing.chart', [
            'patient' => $patient,
            'bed' => $bed,
            'observations' => $patient->nursingObservations()->with('recordedBy')->limit(12)->get(),
            'tasks' => $patient->nursingTasks()->orderByRaw('case when status = ? then 1 else 0 end', ['completed'])->orderBy('due_at')->orderBy('id')->get(),
            'medications' => $patient->medicationAdministrations()->with('administeredBy')->limit(12)->get(),
            'auditEvents' => NursingAuditEvent::query()
                ->where('patient_id', $patient->id)
                ->with('actor')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(16)
                ->get(),
            'openTaskCount' => NursingTask::query()->where('patient_id', $patient->id)->open()->count(),
        ]);
    }
}
