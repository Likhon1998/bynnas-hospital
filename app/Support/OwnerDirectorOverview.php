<?php

namespace App\Support;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\HospitalExpense;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OwnerDirectorOverview
{
    /**
     * @return array{
     *     enabled: bool,
     *     kpis: list<array{label: string, value: string, meta: string, href: ?string, icon: string}>,
     *     doctorPerformance: Collection<int, object{name: string, appointments: int, revenue: float}>,
     *     departmentRevenue: Collection<int, object{name: string, revenue: float}>,
     *     staffAttendance: Collection<int, object{name: string, email: string, present: bool, last_login: ?string}>,
     *     pendingLabs: Collection<int, LabOrder>,
     *     pendingDischarges: Collection<int, Admission>,
     *     profitLoss: array{income: float, expenses: float, profit: float}
     * }
     */
    public function for(?User $user): array
    {
        if ($user === null || ! $user->hasRole('hospital_owner')) {
            return [
                'enabled' => false,
                'kpis' => [],
                'doctorPerformance' => Collection::make(),
                'departmentRevenue' => Collection::make(),
                'staffAttendance' => Collection::make(),
                'pendingLabs' => Collection::make(),
                'pendingDischarges' => Collection::make(),
                'profitLoss' => ['income' => 0.0, 'expenses' => 0.0, 'profit' => 0.0],
            ];
        }

        $todayRevenue = (float) Bill::query()
            ->where('status', 'paid')
            ->whereDate('paid_at', today())
            ->sum('paid_amount');

        $monthStart = now()->copy()->startOfMonth();
        $monthEnd = now()->copy()->endOfMonth();

        $monthlyRevenue = (float) Bill::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->sum('paid_amount');

        $totalExpenses = (float) HospitalExpense::query()->sum('amount');
        $monthExpenses = (float) HospitalExpense::query()
            ->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        $outstanding = (float) Bill::query()
            ->whereIn('status', ['draft', 'issued'])
            ->selectRaw('COALESCE(SUM(total - paid_amount), 0) as due')
            ->value('due');

        $opdToday = Appointment::query()
            ->whereDate('appointment_date', today())
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $ipdActive = Admission::query()
            ->whereIn('status', ['admitted', 'transferred', 'discharge_authorized'])
            ->count();

        $availableBeds = Bed::query()->where('status', 'available')->where('is_maintenance', false)->count();
        $occupiedBeds = Bed::query()->where('status', 'occupied')->count();

        $monthIncome = $monthlyRevenue;

        return [
            'enabled' => true,
            'kpis' => [
                [
                    'label' => "Today's Revenue",
                    'value' => '৳'.number_format($todayRevenue, 2),
                    'meta' => today()->format('j M Y'),
                    'href' => route('admin.owner.finance.index'),
                    'icon' => 'pill',
                ],
                [
                    'label' => 'Monthly Revenue',
                    'value' => '৳'.number_format($monthlyRevenue, 2),
                    'meta' => now()->format('F Y'),
                    'href' => route('admin.owner.finance.index'),
                    'icon' => 'receipt',
                ],
                [
                    'label' => 'Total Expenses',
                    'value' => '৳'.number_format($totalExpenses, 2),
                    'meta' => 'All recorded expenses',
                    'href' => route('admin.owner.finance.index'),
                    'icon' => 'clipboard',
                ],
                [
                    'label' => 'Total Outstanding Dues',
                    'value' => '৳'.number_format($outstanding, 2),
                    'meta' => 'Unpaid bill balance',
                    'href' => route('admin.bills.index'),
                    'icon' => 'badge',
                ],
                [
                    'label' => 'Total Registered Patients',
                    'value' => number_format(Patient::query()->count()),
                    'meta' => 'Patient registry',
                    'href' => route('admin.patients.index'),
                    'icon' => 'users',
                ],
                [
                    'label' => 'OPD / IPD',
                    'value' => $opdToday.' / '.$ipdActive,
                    'meta' => "Today's OPD · Active IPD",
                    'href' => route('admin.appointments.index'),
                    'icon' => 'hospital',
                ],
                [
                    'label' => 'Available Beds',
                    'value' => number_format($availableBeds),
                    'meta' => 'Ready for admission',
                    'href' => route('admin.beds.index'),
                    'icon' => 'bed',
                ],
                [
                    'label' => 'Occupied Beds',
                    'value' => number_format($occupiedBeds),
                    'meta' => 'Currently admitted',
                    'href' => route('admin.beds.index'),
                    'icon' => 'bed',
                ],
            ],
            'doctorPerformance' => $this->doctorPerformance(),
            'departmentRevenue' => $this->departmentRevenue(),
            'staffAttendance' => $this->staffAttendance(),
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
            'profitLoss' => [
                'income' => $monthIncome,
                'expenses' => $monthExpenses,
                'profit' => round($monthIncome - $monthExpenses, 2),
            ],
        ];
    }

    /**
     * @return Collection<int, object{name: string, appointments: int, revenue: float}>
     */
    private function doctorPerformance(): Collection
    {
        $monthStart = now()->copy()->startOfMonth()->toDateString();
        $monthEnd = now()->copy()->endOfMonth()->toDateString();
        $paidFrom = now()->copy()->startOfMonth();
        $paidTo = now()->copy()->endOfMonth();

        return Doctor::query()
            ->withCount([
                'appointments as month_appointments' => fn ($query) => $query
                    ->whereBetween('appointment_date', [$monthStart, $monthEnd])
                    ->whereNotIn('status', ['cancelled']),
            ])
            ->orderByDesc('month_appointments')
            ->limit(8)
            ->get()
            ->map(function (Doctor $doctor) use ($paidFrom, $paidTo): object {
                $revenue = (float) Bill::query()
                    ->where('status', 'paid')
                    ->whereBetween('paid_at', [$paidFrom, $paidTo])
                    ->whereHas('appointment', fn ($query) => $query->where('doctor_id', $doctor->id))
                    ->sum('paid_amount');

                return (object) [
                    'name' => $doctor->name,
                    'appointments' => (int) $doctor->month_appointments,
                    'revenue' => $revenue,
                ];
            });
    }

    /**
     * @return Collection<int, object{name: string, revenue: float}>
     */
    private function departmentRevenue(): Collection
    {
        $paidFrom = now()->copy()->startOfMonth();
        $paidTo = now()->copy()->endOfMonth();

        return Collection::make(
            DB::table('bills')
                ->join('appointments', 'appointments.id', '=', 'bills.appointment_id')
                ->join('departments', 'departments.id', '=', 'appointments.department_id')
                ->where('bills.status', 'paid')
                ->whereBetween('bills.paid_at', [$paidFrom, $paidTo])
                ->groupBy('departments.id', 'departments.name')
                ->orderByDesc(DB::raw('SUM(bills.paid_amount)'))
                ->limit(8)
                ->get([
                    'departments.name as name',
                    DB::raw('SUM(bills.paid_amount) as revenue'),
                ])
                ->map(fn (object $row): object => (object) [
                    'name' => (string) $row->name,
                    'revenue' => (float) $row->revenue,
                ])
        );
    }

    /**
     * @return Collection<int, object{name: string, email: string, present: bool, last_login: ?string}>
     */
    private function staffAttendance(): Collection
    {
        $staffIds = DB::table('model_has_roles')
            ->where('model_type', (new User)->getMorphClass())
            ->distinct()
            ->pluck('model_id');

        return User::query()
            ->whereIn('id', $staffIds)
            ->where('is_active', true)
            ->orderBy('name')
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
