<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Appointments', 'value' => Appointment::query()->count()],
                ['label' => 'Patients', 'value' => Patient::query()->count()],
                ['label' => 'Doctors', 'value' => Doctor::query()->count()],
                ['label' => 'Departments', 'value' => Department::query()->count()],
                ['label' => 'Pending visits', 'value' => Appointment::query()->where('status', 'pending')->count()],
                ['label' => 'Unread messages', 'value' => ContactMessage::query()->unread()->count()],
            ],
            'upcomingAppointments' => $user?->can('appointments.view')
                ? Appointment::query()
                    ->with(['patient', 'doctor', 'department'])
                    ->whereDate('appointment_date', '>=', now()->toDateString())
                    ->whereNotIn('status', ['cancelled', 'completed', 'no_show'])
                    ->orderBy('appointment_date')
                    ->orderBy('start_time')
                    ->orderBy('id')
                    ->limit(8)
                    ->get()
                : Collection::make(),
            'recentMessages' => $user?->can('content.manage')
                ? ContactMessage::query()->latest()->limit(5)->get()
                : Collection::make(),
            'recentBills' => $user?->can('billing.manage')
                ? Bill::query()
                    ->with('patient')
                    ->orderByDesc('bill_date')
                    ->orderByDesc('id')
                    ->limit(6)
                    ->get()
                : Collection::make(),
        ]);
    }
}
