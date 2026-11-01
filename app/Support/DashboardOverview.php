<?php

namespace App\Support;

use App\Models\Bed;
use App\Models\User;
use App\Models\WorkItem;
use Illuminate\Support\Collection;

class DashboardOverview
{
    public function __construct(private WorkInbox $inbox) {}

    /**
     * @return array{
     *     deskHeading: string,
     *     deskSummary: string,
     *     kpis: list<array{label: string, value: string, meta: string, percent: int, tone: string, trend: string, trendUp: bool, href: ?string, chart: string, icon: string}>,
     *     quickActions: list<array{label: string, href: string, tone: string, icon: string}>,
     *     queueSections: list<array{desk: string, desk_label: string, kind: string, label: string, tone: string, icon: string, items: Collection<int, WorkItem>}>,
     *     history: Collection<int, WorkItem>,
     *     occupancy: array{total: int, occupied: int, available: int, dirty: int, cleaning: int, maintenance: int, percent: int},
     *     alerts: Collection<int, array{id: string, tone: string, title: string, detail: string, href: string, time: string, work_id: int, can_acknowledge: bool, can_escalate: bool}>,
     *     alertTotal: int,
     *     alertsHref: string,
     *     historyHref: string,
     *     reportLinks: list<array{label: string, href: string, icon: string}>,
     *     showOccupancy: bool
     * }
     */
    public function for(?User $user): array
    {
        if ($user === null) {
            return $this->emptyState();
        }

        $this->inbox->syncFor($user);

        $sections = collect($this->inbox->sectionsFor($user))
            ->map(function (array $section): array {
                $section['icon'] = $this->iconForKind($section['kind'], $section['desk']);

                return $section;
            })
            ->all();
        $openCount = collect($sections)->sum(fn (array $section): int => $section['items']->count());
        $alertTotal = $this->inbox->unreadNotificationCount($user);
        $desks = $this->inbox->desksFor($user);
        $occupancy = in_array('beds', $desks, true)
            ? $this->occupancy()
            : [
                'total' => 0,
                'occupied' => 0,
                'available' => 0,
                'dirty' => 0,
                'cleaning' => 0,
                'maintenance' => 0,
                'percent' => 0,
            ];

        return [
            'deskHeading' => $this->inbox->deskHeading($user),
            'deskSummary' => $this->deskSummary($openCount, $alertTotal),
            'kpis' => $this->kpis($sections),
            'quickActions' => $this->quickActions($user),
            'queueSections' => $sections,
            'history' => $this->inbox->historyFor($user, 8),
            'occupancy' => $occupancy,
            'alerts' => $this->inbox->notificationsFor($user, 6),
            'alertTotal' => $alertTotal,
            'alertsHref' => $user->can('operations.manage')
                ? route('admin.alerts')
                : route('admin.dashboard'),
            'historyHref' => route('admin.work.history'),
            'reportLinks' => $this->reportLinks($user),
            'showOccupancy' => in_array('beds', $desks, true),
        ];
    }

    /**
     * @return array{
     *     deskHeading: string,
     *     deskSummary: string,
     *     kpis: list<array{label: string, value: string, meta: string, percent: int, tone: string, trend: string, trendUp: bool, href: ?string, chart: string, icon: string}>,
     *     quickActions: list<array{label: string, href: string, tone: string, icon: string}>,
     *     queueSections: list<array{desk: string, desk_label: string, kind: string, label: string, tone: string, icon: string, items: Collection<int, WorkItem>}>,
     *     history: Collection<int, WorkItem>,
     *     occupancy: array{total: int, occupied: int, available: int, dirty: int, cleaning: int, maintenance: int, percent: int},
     *     alerts: Collection<int, array{id: string, tone: string, title: string, detail: string, href: string, time: string, work_id: int, can_acknowledge: bool, can_escalate: bool}>,
     *     alertTotal: int,
     *     alertsHref: string,
     *     historyHref: string,
     *     reportLinks: list<array{label: string, href: string, icon: string}>,
     *     showOccupancy: bool
     * }
     */
    private function emptyState(): array
    {
        return [
            'deskHeading' => 'Administration Desk',
            'deskSummary' => 'Sign in to see the next work for your shift.',
            'kpis' => [],
            'quickActions' => [],
            'queueSections' => [],
            'history' => Collection::make(),
            'occupancy' => [
                'total' => 0,
                'occupied' => 0,
                'available' => 0,
                'dirty' => 0,
                'cleaning' => 0,
                'maintenance' => 0,
                'percent' => 0,
            ],
            'alerts' => Collection::make(),
            'alertTotal' => 0,
            'alertsHref' => route('admin.dashboard'),
            'historyHref' => route('admin.work.history'),
            'reportLinks' => [],
            'showOccupancy' => false,
        ];
    }

    /**
     * @param  list<array{desk: string, desk_label: string, kind: string, label: string, tone: string, items: Collection<int, WorkItem>}>  $sections
     * @return list<array{label: string, value: string, meta: string, percent: int, tone: string, trend: string, trendUp: bool, href: ?string, chart: string, icon: string}>
     */
    private function kpis(array $sections): array
    {
        return collect($sections)
            ->take(6)
            ->values()
            ->map(function (array $section): array {
                $count = $section['items']->count();

                return [
                    'label' => $section['label'],
                    'value' => (string) $count,
                    'meta' => $section['desk_label'],
                    'percent' => (int) min(100, $count * 12),
                    'tone' => match ($section['tone']) {
                        'blue' => 'blue',
                        'green' => 'green',
                        'red' => 'orange',
                        default => 'navy',
                    },
                    'trend' => $count === 0 ? 'Queue clear' : $count.' need action',
                    'trendUp' => $count === 0,
                    'href' => '#queue-'.$section['desk'].'-'.$section['kind'],
                    'chart' => 'none',
                    'icon' => $this->iconForKind($section['kind'], $section['desk']),
                ];
            })
            ->all();
    }

    /**
     * @return list<array{label: string, href: string, tone: string, icon: string}>
     */
    private function quickActions(?User $user): array
    {
        $actions = [];

        if ($user?->can('appointments.manage')) {
            $actions[] = [
                'label' => 'Take appointment',
                'href' => route('admin.appointments.create'),
                'tone' => 'critical',
                'icon' => 'clipboard-plus',
            ];
        } elseif ($user?->can('appointments.view')) {
            $actions[] = [
                'label' => 'Appointments',
                'href' => route('admin.appointments.index'),
                'tone' => 'critical',
                'icon' => 'clipboard',
            ];
        }

        if ($user?->can('operations.manage')) {
            $actions[] = [
                'label' => 'Emergency desk',
                'href' => route('admin.emergency'),
                'tone' => 'urgent',
                'icon' => 'ambulance',
            ];
        }

        if ($user?->can('patients.manage')) {
            $actions[] = [
                'label' => 'Register patient',
                'href' => route('admin.patients.create'),
                'tone' => 'primary',
                'icon' => 'users',
            ];
        }

        if ($user?->can('admissions.admit')) {
            $actions[] = [
                'label' => 'Admit patient',
                'href' => route('admin.admissions.create'),
                'tone' => 'primary',
                'icon' => 'hospital',
            ];
        } elseif ($user?->can('admissions.view')) {
            $actions[] = [
                'label' => 'Admissions',
                'href' => route('admin.admissions.index'),
                'tone' => 'primary',
                'icon' => 'hospital',
            ];
        }

        if ($user?->can('visits.view')) {
            $actions[] = [
                'label' => 'Visit desk',
                'href' => route('admin.visits.index'),
                'tone' => 'strong',
                'icon' => 'clipboard-check',
            ];
        }

        if ($user?->can('beds.view')) {
            $actions[] = [
                'label' => $user?->can('beds.operate') ? 'Assign bed' : 'Bed board',
                'href' => route('admin.beds.index'),
                'tone' => 'strong',
                'icon' => 'bed',
            ];
        }

        if ($user?->can('nursing.view')) {
            $actions[] = [
                'label' => 'Nursing ward',
                'href' => route('admin.nursing.index'),
                'tone' => 'mid',
                'icon' => 'syringe',
            ];
        }

        if ($user?->can('lab.view')) {
            $actions[] = [
                'label' => 'Lab desk',
                'href' => route('admin.labs.index'),
                'tone' => 'mid',
                'icon' => 'flask',
            ];
        }

        if ($user?->can('billing.manage')) {
            $actions[] = [
                'label' => 'Create bill',
                'href' => route('admin.bills.create'),
                'tone' => 'soft',
                'icon' => 'pill',
            ];
        }

        if ($user?->can('content.manage') && $actions === []) {
            $actions[] = [
                'label' => 'Contact inbox',
                'href' => route('admin.contact-messages.index'),
                'tone' => 'primary',
                'icon' => 'clipboard',
            ];
            $actions[] = [
                'label' => 'Website services',
                'href' => route('admin.services.index'),
                'tone' => 'soft',
                'icon' => 'first-aid',
            ];
        }

        return $actions;
    }

    /**
     * @return array{total: int, occupied: int, available: int, dirty: int, cleaning: int, maintenance: int, percent: int}
     */
    private function occupancy(): array
    {
        $rows = Bed::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $occupied = (int) ($rows['occupied'] ?? 0);
        $available = (int) ($rows['available'] ?? 0);
        $dirty = (int) ($rows['dirty'] ?? 0);
        $cleaning = (int) ($rows['cleaning'] ?? 0);
        $total = $occupied + $available + $dirty + $cleaning;
        $maintenance = Bed::query()->where('is_maintenance', true)->count();

        return [
            'total' => $total,
            'occupied' => $occupied,
            'available' => $available,
            'dirty' => $dirty,
            'cleaning' => $cleaning,
            'maintenance' => $maintenance,
            'percent' => $total > 0 ? (int) round(($occupied / $total) * 100) : 0,
        ];
    }

    /**
     * @return list<array{label: string, href: string, icon: string}>
     */
    private function reportLinks(?User $user): array
    {
        $links = [];

        if ($user?->can('operations.manage')) {
            $links[] = ['label' => 'Operational reports', 'href' => route('admin.reports'), 'icon' => 'thermometer'];
            $links[] = ['label' => 'Capacity forecasts', 'href' => route('admin.forecasts'), 'icon' => 'hospital'];
            $links[] = ['label' => 'Critical alerts', 'href' => route('admin.alerts'), 'icon' => 'pulse'];
        }

        if ($user?->can('beds.view')) {
            $links[] = ['label' => 'Bed history', 'href' => route('admin.beds.events'), 'icon' => 'bed'];
        }

        if ($user?->can('billing.manage')) {
            $links[] = ['label' => 'Billing ledger', 'href' => route('admin.bills.index'), 'icon' => 'pill'];
        }

        return $links;
    }

    private function iconForKind(string $kind, string $desk): string
    {
        return match ($kind) {
            'pending_web' => 'clipboard-plus',
            'arrival', 'waiting' => 'clipboard',
            'no_show' => 'clipboard-check',
            'assigned', 'consult', 'follow_up' => 'stethoscope',
            'overdue_observation', 'pending_task' => 'syringe',
            'critical', 'escalated', 'exception' => 'pulse',
            'result', 'lab_work' => 'flask',
            'ready', 'cleaning', 'maintenance' => 'bed',
            'pending_admission' => 'hospital',
            'unpaid' => 'pill',
            'message' => 'clipboard',
            default => match ($desk) {
                'nursing' => 'syringe',
                'doctor' => 'stethoscope',
                'beds' => 'bed',
                'lab' => 'flask',
                'admin' => 'hospital',
                default => 'cross',
            },
        };
    }

    private function deskSummary(int $openCount, int $alertTotal): string
    {
        $parts = [
            $openCount === 1 ? '1 open task' : $openCount.' open tasks',
        ];

        if ($alertTotal > 0) {
            $parts[] = $alertTotal === 1 ? '1 unread alert' : $alertTotal.' unread alerts';
        }

        return implode(' · ', $parts);
    }
}
