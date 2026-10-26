<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Ward;
use Illuminate\View\View;

class OperationalReportController extends Controller
{
    public function __invoke(): View
    {
        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();

        return view('admin.operations.reports', [
            'stats' => [
                ['label' => 'Active wards', 'value' => Ward::query()->active()->count()],
                ['label' => 'Total beds', 'value' => Bed::query()->count()],
                ['label' => 'Occupied beds', 'value' => Bed::query()->where('status', 'occupied')->count()],
                ['label' => 'Available beds', 'value' => Bed::query()->where('status', 'available')->count()],
                ['label' => 'Visits this week', 'value' => Appointment::query()->whereBetween('appointment_date', [$weekStart, $weekEnd])->whereNotIn('status', ['cancelled'])->count()],
                ['label' => 'Completed visits', 'value' => Appointment::query()->whereBetween('appointment_date', [$weekStart, $weekEnd])->where('status', 'completed')->count()],
                ['label' => 'Patients on file', 'value' => Patient::query()->count()],
                ['label' => 'Active doctors', 'value' => Doctor::query()->active()->count()],
                ['label' => 'Unpaid bills', 'value' => Bill::query()->unpaid()->count()],
                ['label' => 'Billed this week', 'value' => number_format((float) Bill::query()->whereBetween('bill_date', [$weekStart, $weekEnd])->sum('total'), 2)],
            ],
        ]);
    }
}
