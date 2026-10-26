<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bed;
use Illuminate\View\View;

class EmergencyFlowController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();

        $emergencyVisits = Appointment::query()
            ->with(['patient', 'doctor', 'department'])
            ->whereDate('appointment_date', $today)
            ->where(function ($query): void {
                $query->where('status', 'pending')
                    ->orWhereHas('department', fn ($department) => $department->where('name', 'like', '%Emergency%'));
            })
            ->orderBy('start_time')
            ->limit(20)
            ->get();

        $freeEmergencyBeds = Bed::query()
            ->with('ward')
            ->admittable()
            ->whereHas('ward', fn ($ward) => $ward->whereIn('type', ['icu', 'isolation', 'general']))
            ->orderBy('bed_code')
            ->limit(12)
            ->get();

        return view('admin.operations.emergency', [
            'emergencyVisits' => $emergencyVisits,
            'freeEmergencyBeds' => $freeEmergencyBeds,
            'pendingCount' => $emergencyVisits->where('status', 'pending')->count(),
            'availableBeds' => $freeEmergencyBeds->count(),
        ]);
    }
}
