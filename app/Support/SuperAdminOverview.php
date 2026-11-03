<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Bill;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\SystemAuditLog;
use App\Models\User;
use App\Models\WorkItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SuperAdminOverview
{
    /**
     * @return array{
     *     enabled: bool,
     *     kpis: list<array{label: string, value: string, meta: string, href: ?string, icon: string}>,
     *     activity: Collection<int, object{kind: string, title: string, detail: string, when: string, href: ?string}>,
     *     transactions: Collection<int, Bill>
     * }
     */
    public function for(?User $user): array
    {
        if ($user === null || ! $user->hasRole('super_admin')) {
            return [
                'enabled' => false,
                'kpis' => [],
                'activity' => Collection::make(),
                'transactions' => Collection::make(),
            ];
        }

        $staffCount = (int) DB::table('model_has_roles')
            ->where('model_type', (new User)->getMorphClass())
            ->distinct()
            ->count('model_id');

        $activeUsers = User::query()
            ->where('is_active', true)
            ->whereNotNull('last_login_at')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();

        $todayAppointments = Appointment::query()
            ->whereDate('appointment_date', today())
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $totalRevenue = (float) Bill::query()
            ->where('status', 'paid')
            ->sum('paid_amount');

        return [
            'enabled' => true,
            'kpis' => [
                [
                    'label' => 'Total Patients',
                    'value' => number_format(Patient::query()->count()),
                    'meta' => 'Registered records',
                    'href' => route('admin.patients.index'),
                    'icon' => 'users',
                ],
                [
                    'label' => 'Total Doctors',
                    'value' => number_format(Doctor::query()->count()),
                    'meta' => 'Consultant roster',
                    'href' => route('admin.doctors.index'),
                    'icon' => 'stethoscope',
                ],
                [
                    'label' => 'Total Staff',
                    'value' => number_format($staffCount),
                    'meta' => 'Login accounts with roles',
                    'href' => route('admin.users.index'),
                    'icon' => 'nurse',
                ],
                [
                    'label' => "Today's Appointments",
                    'value' => number_format($todayAppointments),
                    'meta' => today()->format('j M Y'),
                    'href' => route('admin.appointments.index'),
                    'icon' => 'clipboard',
                ],
                [
                    'label' => 'Total Revenue',
                    'value' => '৳'.number_format($totalRevenue, 2),
                    'meta' => 'Paid bills',
                    'href' => route('admin.bills.index'),
                    'icon' => 'pill',
                ],
                [
                    'label' => 'Active Users',
                    'value' => number_format($activeUsers),
                    'meta' => 'Signed in within 30 days',
                    'href' => route('admin.users.index'),
                    'icon' => 'users',
                ],
            ],
            'activity' => $this->activityFeed(),
            'transactions' => Bill::query()
                ->with('patient')
                ->where('status', 'paid')
                ->latest('paid_at')
                ->limit(8)
                ->get(),
        ];
    }

    /**
     * @return Collection<int, object{kind: string, title: string, detail: string, when: string, href: ?string}>
     */
    private function activityFeed(): Collection
    {
        $audits = SystemAuditLog::query()
            ->with('user')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (SystemAuditLog $log): object => (object) [
                'kind' => 'audit',
                'title' => $log->summary,
                'detail' => ($log->user?->name ?: 'System').' · '.$log->action,
                'when' => $log->created_at?->diffForHumans() ?: '',
                'href' => route('admin.system.audit-logs.index'),
                'sort_at' => $log->created_at?->getTimestamp() ?? 0,
            ]);

        $work = WorkItem::query()
            ->latest('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (WorkItem $item): object => (object) [
                'kind' => 'work',
                'title' => $item->title,
                'detail' => $item->kindLabel().($item->detail ? ' · '.$item->detail : ''),
                'when' => $item->updated_at?->diffForHumans() ?: '',
                'href' => $item->href,
                'sort_at' => $item->updated_at?->getTimestamp() ?? 0,
            ]);

        return $audits
            ->concat($work)
            ->sortByDesc(fn (object $row): int => (int) $row->sort_at)
            ->values()
            ->take(10)
            ->map(function (object $row): object {
                unset($row->sort_at);

                return $row;
            });
    }
}
