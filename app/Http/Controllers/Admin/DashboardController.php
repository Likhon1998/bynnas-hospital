<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\ContactMessage;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd = now()->endOfWeek(Carbon::SUNDAY)->toDateString();
        $prevWeekStart = now()->subWeek()->startOfWeek(Carbon::MONDAY)->toDateString();
        $prevWeekEnd = now()->subWeek()->endOfWeek(Carbon::SUNDAY)->toDateString();

        $todayAppointments = Appointment::query()
            ->whereDate('appointment_date', $today)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $weekAppointments = Appointment::query()
            ->whereBetween('appointment_date', [$weekStart, $weekEnd])
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $prevWeekAppointments = Appointment::query()
            ->whereBetween('appointment_date', [$prevWeekStart, $prevWeekEnd])
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $pendingVisits = Appointment::query()
            ->where('status', 'pending')
            ->whereDate('appointment_date', '>=', $today)
            ->count();

        $confirmedToday = Appointment::query()
            ->whereDate('appointment_date', $today)
            ->where('status', 'confirmed')
            ->count();

        $completedWeek = Appointment::query()
            ->whereBetween('appointment_date', [$weekStart, $weekEnd])
            ->where('status', 'completed')
            ->count();

        $unpaidBills = Bill::query()->unpaid()->count();
        $patientCount = Patient::query()->count();
        $doctorCount = Doctor::query()->active()->count();

        $statusCounts = [
            'pending' => Appointment::query()->where('status', 'pending')->whereDate('appointment_date', '>=', $today)->count(),
            'confirmed' => Appointment::query()->where('status', 'confirmed')->whereDate('appointment_date', '>=', $today)->count(),
            'completed' => Appointment::query()->where('status', 'completed')->whereBetween('appointment_date', [$weekStart, $weekEnd])->count(),
            'cancelled' => Appointment::query()->where('status', 'cancelled')->whereBetween('appointment_date', [$weekStart, $weekEnd])->count(),
        ];

        return view('admin.dashboard', [
            'deskSummary' => $this->deskSummary($user, $todayAppointments, $pendingVisits, $unpaidBills),
            'kpis' => $this->kpis(
                $user,
                $todayAppointments,
                $weekAppointments,
                $prevWeekAppointments,
                $pendingVisits,
                $confirmedToday,
                $completedWeek,
                $patientCount,
                $unpaidBills,
            ),
            'weeklyStatus' => $this->weeklyStatusSeries($weekStart),
            'statusMix' => $this->statusMix($statusCounts),
            'careTimeline' => $this->careTimeline($user, $pendingVisits, $weekAppointments, $unpaidBills, $completedWeek),
            'statusTabs' => $statusCounts,
            'upcomingAppointments' => $user?->can('appointments.view')
                ? Appointment::query()
                    ->with(['patient', 'doctor', 'department'])
                    ->whereDate('appointment_date', '>=', $today)
                    ->whereNotIn('status', ['cancelled', 'completed', 'no_show'])
                    ->orderBy('appointment_date')
                    ->orderBy('start_time')
                    ->orderBy('id')
                    ->limit(8)
                    ->get()
                : Collection::make(),
            'recentBills' => $user?->can('billing.manage')
                ? Bill::query()
                    ->with('patient')
                    ->orderByDesc('bill_date')
                    ->orderByDesc('id')
                    ->limit(4)
                    ->get()
                : Collection::make(),
            'doctorCount' => $doctorCount,
        ]);
    }

    /**
     * @return list<array{label: string, value: string, meta: string, percent: int, tone: string, trend: string, trendUp: bool, href: ?string, chart: string}>
     */
    private function kpis(
        ?User $user,
        int $todayAppointments,
        int $weekAppointments,
        int $prevWeekAppointments,
        int $pendingVisits,
        int $confirmedToday,
        int $completedWeek,
        int $patientCount,
        int $unpaidBills,
    ): array {
        $kpis = [];
        $weekDelta = $weekAppointments - $prevWeekAppointments;
        $weekPct = $prevWeekAppointments > 0
            ? round(abs($weekDelta) / $prevWeekAppointments * 100, 1)
            : ($weekAppointments > 0 ? 100.0 : 0.0);

        if ($user?->can('appointments.view')) {
            $capacity = max($weekAppointments, $todayAppointments, 1);
            $kpis[] = [
                'label' => 'Total visits',
                'value' => $todayAppointments.'/'.max($weekAppointments, $todayAppointments),
                'meta' => 'Today / this week',
                'percent' => (int) min(100, round(($todayAppointments / $capacity) * 100)),
                'tone' => 'orange',
                'trend' => ($weekDelta >= 0 ? '+' : '').$weekDelta.' vs last week · '.($weekDelta >= 0 ? '↑' : '↓').' '.$weekPct.'%',
                'trendUp' => $weekDelta >= 0,
                'href' => route('admin.appointments.index'),
                'chart' => 'bar',
            ];

            $confirmedPct = $todayAppointments > 0
                ? (int) round(($confirmedToday / $todayAppointments) * 100)
                : 0;
            $kpis[] = [
                'label' => 'Confirmed today',
                'value' => $confirmedToday.'/'.max($todayAppointments, 1),
                'meta' => $confirmedPct.'% confirmed',
                'percent' => $confirmedPct,
                'tone' => 'blue',
                'trend' => $pendingVisits.' still pending',
                'trendUp' => true,
                'href' => route('admin.appointments.index', ['status' => 'confirmed']),
                'chart' => 'donut',
            ];

            $kpis[] = [
                'label' => 'Pending visits',
                'value' => (string) $pendingVisits,
                'meta' => 'Need confirmation',
                'percent' => (int) min(100, $pendingVisits * 8),
                'tone' => 'green',
                'trend' => $completedWeek.' completed this week',
                'trendUp' => $pendingVisits === 0,
                'href' => route('admin.appointments.index', ['status' => 'pending']),
                'chart' => 'bar',
            ];
        }

        if ($user?->can('patients.view')) {
            $kpis[] = [
                'label' => 'Patients on file',
                'value' => number_format($patientCount),
                'meta' => 'Registered patients',
                'percent' => (int) min(100, max(8, $patientCount % 100)),
                'tone' => 'navy',
                'trend' => $user?->can('billing.manage') ? $unpaidBills.' unpaid bills' : 'Active records',
                'trendUp' => true,
                'href' => route('admin.patients.index'),
                'chart' => 'none',
            ];
        } elseif ($user?->can('billing.manage')) {
            $kpis[] = [
                'label' => 'Unpaid bills',
                'value' => (string) $unpaidBills,
                'meta' => 'Open balances',
                'percent' => (int) min(100, max(5, $unpaidBills * 10)),
                'tone' => 'navy',
                'trend' => 'Billing desk',
                'trendUp' => $unpaidBills === 0,
                'href' => route('admin.bills.index'),
                'chart' => 'none',
            ];
        } elseif ($user?->can('content.manage')) {
            $unread = ContactMessage::query()->unread()->count();
            $kpis[] = [
                'label' => 'Messages',
                'value' => (string) $unread,
                'meta' => 'Unread website mail',
                'percent' => (int) min(100, max(5, $unread * 12)),
                'tone' => 'navy',
                'trend' => 'Content inbox',
                'trendUp' => $unread === 0,
                'href' => route('admin.contact-messages.index'),
                'chart' => 'none',
            ];
        }

        return $kpis;
    }

    /**
     * @return list<array{day: string, pending: int, confirmed: int, completed: int, cancelled: int, total: int}>
     */
    private function weeklyStatusSeries(string $weekStart): array
    {
        $start = Carbon::parse($weekStart)->startOfDay();
        $end = $start->copy()->addDays(6)->toDateString();

        $rows = Appointment::query()
            ->whereBetween('appointment_date', [$start->toDateString(), $end])
            ->selectRaw('appointment_date, status, COUNT(*) as aggregate')
            ->groupBy('appointment_date', 'status')
            ->get();

        $indexed = [];
        foreach ($rows as $row) {
            $date = Carbon::parse((string) $row->appointment_date)->toDateString();
            $indexed[$date][(string) $row->status] = (int) $row->aggregate;
        }

        $series = [];

        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i);
            $date = $day->toDateString();
            $pending = $indexed[$date]['pending'] ?? 0;
            $confirmed = $indexed[$date]['confirmed'] ?? 0;
            $completed = $indexed[$date]['completed'] ?? 0;
            $cancelled = $indexed[$date]['cancelled'] ?? 0;

            $series[] = [
                'day' => $day->format('D'),
                'pending' => $pending,
                'confirmed' => $confirmed,
                'completed' => $completed,
                'cancelled' => $cancelled,
                'total' => $pending + $confirmed + $completed + $cancelled,
            ];
        }

        return $series;
    }

    /**
     * @param  array{pending: int, confirmed: int, completed: int, cancelled: int}  $counts
     * @return list<array{label: string, value: int, percent: int, tone: string}>
     */
    private function statusMix(array $counts): array
    {
        $total = max(array_sum($counts), 1);

        return [
            ['label' => 'Pending', 'value' => $counts['pending'], 'percent' => (int) round($counts['pending'] / $total * 100), 'tone' => 'orange'],
            ['label' => 'Confirmed', 'value' => $counts['confirmed'], 'percent' => (int) round($counts['confirmed'] / $total * 100), 'tone' => 'red'],
            ['label' => 'Completed', 'value' => $counts['completed'], 'percent' => (int) round($counts['completed'] / $total * 100), 'tone' => 'blue'],
            ['label' => 'Cancelled', 'value' => $counts['cancelled'], 'percent' => (int) round($counts['cancelled'] / $total * 100), 'tone' => 'sand'],
        ];
    }

    /**
     * @return list<array{label: string, percent: int, tone: string}>
     */
    private function careTimeline(?User $user, int $pendingVisits, int $weekAppointments, int $unpaidBills, int $completedWeek): array
    {
        $items = [];

        if ($user?->can('appointments.view')) {
            $confirmRate = $weekAppointments > 0
                ? (int) round(($completedWeek / $weekAppointments) * 100)
                : 0;
            $items[] = ['label' => 'Visit completion', 'percent' => min(100, $confirmRate), 'tone' => 'green'];
            $items[] = ['label' => 'Pending queue', 'percent' => min(100, $pendingVisits * 6), 'tone' => 'peach'];
        }

        if ($user?->can('billing.manage')) {
            $items[] = ['label' => 'Unpaid follow-up', 'percent' => min(100, max(8, $unpaidBills * 9)), 'tone' => 'navy'];
        }

        if ($items === [] && $user?->can('content.manage')) {
            $unread = ContactMessage::query()->unread()->count();
            $items[] = ['label' => 'Message reply rate', 'percent' => min(100, max(12, 100 - $unread * 8)), 'tone' => 'green'];
        }

        return $items;
    }

    private function deskSummary(?User $user, int $todayAppointments, int $pendingVisits, int $unpaidBills): string
    {
        $parts = [];

        if ($user?->can('appointments.view')) {
            $parts[] = $todayAppointments === 1 ? '1 visit today' : $todayAppointments.' visits today';

            if ($pendingVisits > 0) {
                $parts[] = $pendingVisits === 1 ? '1 still needs confirmation' : $pendingVisits.' still need confirmation';
            }
        }

        if ($user?->can('billing.manage') && $unpaidBills > 0) {
            $parts[] = $unpaidBills === 1 ? '1 unpaid bill' : $unpaidBills.' unpaid bills';
        }

        return $parts !== []
            ? implode(' · ', $parts)
            : 'Operations overview for today’s hospital desk.';
    }
}
