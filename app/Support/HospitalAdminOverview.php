<?php

namespace App\Support;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Department;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HospitalAdminOverview
{
    /**
     * @return array{
     *     enabled: bool,
     *     kpis: list<array{label: string, value: string, meta: string, href: ?string, icon: string}>,
     *     departmentActivity: Collection<int, object{name: string, appointments: int, admissions: int}>,
     *     staffActivity: Collection<int, object{name: string, email: string, present: bool, last_login: ?string}>,
     *     pendingLabs: Collection<int, LabOrder>,
     *     pendingDischarges: Collection<int, Admission>,
     *     pharmacy: Collection<int, Service>
     * }
     */
    public function for(?User $user): array
    {
        if ($user === null || ! $user->hasRole('hospital_admin')) {
            return [
                'enabled' => false,
                'kpis' => [],
                'departmentActivity' => Collection::make(),
                'staffActivity' => Collection::make(),
                'pendingLabs' => Collection::make(),
                'pendingDischarges' => Collection::make(),
                'pharmacy' => Collection::make(),
            ];
        }

        $todayPatients = Patient::query()
            ->whereDate('created_at', today())
            ->count();

        $todayAppointments = Appointment::query()
            ->whereDate('appointment_date', today())
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $pendingAdmissions = Appointment::query()
            ->emergencyDesk(now()->toDateString())
            ->where('status', 'confirmed')
            ->whereDoesntHave('patient.admissions', fn ($query) => $query->open())
            ->count();

        $pendingDischarges = Admission::query()
            ->whereIn('status', ['discharge_authorized', 'admitted', 'transferred'])
            ->count();

        $availableBeds = Bed::query()
            ->where('status', 'available')
            ->where('is_maintenance', false)
            ->count();

        $pendingLabs = LabOrder::query()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        return [
            'enabled' => true,
            'kpis' => [
                [
                    'label' => "Today's Patients",
                    'value' => number_format($todayPatients),
                    'meta' => 'Newly registered today',
                    'href' => route('admin.patients.index'),
                    'icon' => 'users',
                ],
                [
                    'label' => "Today's Appointments",
                    'value' => number_format($todayAppointments),
                    'meta' => today()->format('j M Y'),
                    'href' => route('admin.appointments.index'),
                    'icon' => 'clipboard',
                ],
                [
                    'label' => 'Pending Admissions',
                    'value' => number_format($pendingAdmissions),
                    'meta' => 'Emergency desk waiting for a bed',
                    'href' => route('admin.admissions.create'),
                    'icon' => 'hospital',
                ],
                [
                    'label' => 'Pending Discharges',
                    'value' => number_format($pendingDischarges),
                    'meta' => 'Active inpatient stays',
                    'href' => route('admin.admissions.index'),
                    'icon' => 'bed',
                ],
                [
                    'label' => 'Available Beds',
                    'value' => number_format($availableBeds),
                    'meta' => 'Ready for admission',
                    'href' => route('admin.beds.index'),
                    'icon' => 'bed',
                ],
                [
                    'label' => 'Pending Laboratory Reports',
                    'value' => number_format($pendingLabs),
                    'meta' => 'Open lab orders',
                    'href' => route('admin.labs.index'),
                    'icon' => 'flask',
                ],
            ],
            'departmentActivity' => $this->departmentActivity(),
            'staffActivity' => $this->staffActivity(),
            'pendingLabs' => LabOrder::query()
                ->with(['patient', 'test'])
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->latest('id')
                ->limit(8)
                ->get(),
            'pendingDischarges' => Admission::query()
                ->with(['patient', 'ward', 'bed'])
                ->whereIn('status', ['discharge_authorized', 'admitted', 'transferred'])
                ->orderByRaw("case status when 'discharge_authorized' then 0 else 1 end")
                ->latest('id')
                ->limit(8)
                ->get(),
            'pharmacy' => Service::query()
                ->active()
                ->where(function ($query): void {
                    $query->where('slug', 'like', '%pharmacy%')
                        ->orWhere('title', 'like', '%pharmacy%');
                })
                ->orderBy('title')
                ->limit(6)
                ->get(),
        ];
    }

    /**
     * @return Collection<int, object{name: string, appointments: int, admissions: int}>
     */
    private function departmentActivity(): Collection
    {
        $today = today()->toDateString();

        $appointmentCounts = Appointment::query()
            ->select('department_id', DB::raw('COUNT(*) as total'))
            ->whereDate('appointment_date', $today)
            ->whereNotIn('status', ['cancelled'])
            ->whereNotNull('department_id')
            ->groupBy('department_id')
            ->pluck('total', 'department_id');

        $admissionCounts = Admission::query()
            ->select('department_id', DB::raw('COUNT(*) as total'))
            ->whereIn('status', ['admitted', 'transferred', 'discharge_authorized'])
            ->whereNotNull('department_id')
            ->groupBy('department_id')
            ->pluck('total', 'department_id');

        return Department::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Department $department): object => (object) [
                'name' => $department->name,
                'appointments' => (int) ($appointmentCounts[$department->id] ?? 0),
                'admissions' => (int) ($admissionCounts[$department->id] ?? 0),
            ])
            ->filter(fn (object $row): bool => $row->appointments > 0 || $row->admissions > 0)
            ->sortByDesc(fn (object $row): int => $row->appointments + $row->admissions)
            ->take(8)
            ->values();
    }

    /**
     * @return Collection<int, object{name: string, email: string, present: bool, last_login: ?string}>
     */
    private function staffActivity(): Collection
    {
        $staffIds = DB::table('model_has_roles')
            ->where('model_type', (new User)->getMorphClass())
            ->distinct()
            ->pluck('model_id');

        return User::query()
            ->whereIn('id', $staffIds)
            ->where('is_active', true)
            ->orderByDesc('last_login_at')
            ->limit(12)
            ->get()
            ->map(fn (User $user): object => (object) [
                'name' => $user->name,
                'email' => $user->email,
                'present' => $user->last_login_at !== null && $user->last_login_at->isToday(),
                'last_login' => $user->last_login_at?->format('j M Y H:i'),
            ]);
    }
}
