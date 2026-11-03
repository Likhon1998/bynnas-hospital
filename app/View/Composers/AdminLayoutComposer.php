<?php

namespace App\View\Composers;

use App\Models\HospitalSetting;
use App\Models\User;
use App\Support\CriticalAlertFeed;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function __construct(private CriticalAlertFeed $criticalAlertFeed) {}

    public function compose(View $view): void
    {
        $user = Auth::user();
        $hospitalName = HospitalSetting::query()->where('key', 'hospital_name')->value('value') ?: config('app.name');
        $hospitalLogo = HospitalSetting::query()->where('key', 'logo')->value('value');
        $alertFeed = $view->name() === 'layouts.admin'
            ? $this->criticalAlertFeed->for($user)
            : ['alerts' => collect(), 'counts' => [], 'total' => 0];

        $primaryAction = $this->hydrateItem($user, [
            'label' => 'Take appointment',
            'route' => 'admin.appointments.create',
            'permission' => 'appointments.manage',
            'icon' => 'clipboard-plus',
        ]);
        $dashboardItem = $this->hydrateItem($user, [
            'label' => 'Dashboard',
            'route' => 'admin.dashboard',
            'permission' => null,
            'icon' => 'pulse',
        ]);
        $navSections = $this->navigationSections($user);
        $navigation = collect([$primaryAction, $dashboardItem])
            ->filter()
            ->concat(collect($navSections)->flatMap(function (array $section) {
                return collect($section['items'])->flatMap(function (array $item) {
                    return collect([$item])->concat($item['children']);
                });
            }))
            ->values();

        $view->with([
            'hospitalName' => $hospitalName,
            'hospitalLogo' => $hospitalLogo,
            'adminPrimaryAction' => $primaryAction,
            'adminDashboardItem' => $dashboardItem,
            'adminNavSections' => $navSections,
            'adminNavigation' => $navigation,
            'staffDisplayName' => $this->displayName($user),
            'staffRoleLabel' => $this->roleLabel($user),
            'staffInitials' => $this->initials($user),
            'staffEmail' => (string) $user?->email,
            'adminSearchRoute' => $this->searchRoute($user),
            'adminSearchPlaceholder' => $this->searchPlaceholder($user),
            'adminSearchUrl' => route('admin.search'),
            'adminNotificationsUrl' => route('admin.notifications'),
            'adminNotificationsSeenUrl' => route('admin.notifications.seen'),
            'adminNotifications' => $alertFeed['alerts'],
            'adminNotificationCount' => $alertFeed['total'],
            'adminNotificationsHref' => $user?->can('operations.manage')
                ? route('admin.alerts')
                : ($user?->can('beds.view') ? route('admin.beds.index') : route('admin.dashboard')),
            'adminCanBookAppointment' => $user?->can('appointments.manage') ?? false,
            'adminCanManageSettings' => $user?->can('settings.manage') ?? false,
            'adminCanOperateBeds' => $user?->can('beds.operate') ?? false,
            'adminCanMaintainBeds' => $user?->can('beds.maintain') ?? false,
        ]);
    }

    private function displayName(?User $user): string
    {
        return trim(str_replace('(Demo)', '', (string) $user?->name));
    }

    private function roleLabel(?User $user): string
    {
        return match (true) {
            $user?->hasRole('super_admin') => 'Super Admin',
            $user?->hasRole('hospital_owner') => 'Hospital Owner / Director',
            $user?->hasRole('hospital_admin') => 'Hospital Admin',
            $user?->hasRole('nurse_station') => 'Nurse Station',
            $user?->hasRole('lab_technician') => 'Lab Technician',
            $user?->hasRole('pathologist') => 'Pathologist',
            $user?->hasRole('radiologist') => 'Radiologist',
            $user?->hasRole('cashier') => 'Cashier',
            $user?->hasRole('receptionist') => 'Receptionist',
            $user?->hasRole('doctor') => 'Doctor',
            $user?->hasRole('content_manager') => 'Content Manager',
            default => 'Staff',
        };
    }

    private function initials(?User $user): string
    {
        $words = preg_split('/\s+/', $this->displayName($user)) ?: [];

        return collect($words)
            ->filter()
            ->map(fn (string $word): string => strtoupper(substr($word, 0, 1)))
            ->take(2)
            ->implode('') ?: 'ST';
    }

    /**
     * @return list<array{label: string, open: bool, items: list<array{label: string, href: string, icon: string, active: bool, current: bool, children: list<array{label: string, href: string, icon: string, active: bool, current: bool, children: list<empty>}>}>}>
     */
    private function navigationSections(?User $user): array
    {
        $sections = [];

        foreach ($this->navigationTree() as $section) {
            $items = [];

            foreach ($section['items'] as $item) {
                foreach ($this->hydrateBranch($user, $item) as $hydrated) {
                    $items[] = $hydrated;
                }
            }

            if ($items === []) {
                continue;
            }

            $sections[] = [
                'label' => $section['label'],
                'open' => collect($items)->contains(fn (array $item): bool => $item['current']),
                'items' => $items,
            ];
        }

        return $sections;
    }

    /**
     * @return list<array{label: string, items: list<array<string, mixed>>}>
     */
    private function navigationTree(): array
    {
        return [
            [
                'label' => 'Front desk',
                'items' => [
                    [
                        'label' => 'Appointments',
                        'route' => 'admin.appointments.index',
                        'permission' => 'appointments.view',
                        'icon' => 'clipboard',
                        'children' => [
                            ['label' => 'Check-in', 'route' => 'admin.visits.index', 'permission' => 'visits.view', 'icon' => 'clipboard-check'],
                        ],
                    ],
                    [
                        'label' => 'Patients',
                        'route' => 'admin.patients.index',
                        'permission' => 'patients.view',
                        'icon' => 'users',
                        'children' => [
                            ['label' => 'Billing', 'route' => 'admin.bills.index', 'permission' => 'billing.manage', 'icon' => 'pill'],
                            ['label' => 'Daily collections', 'route' => 'admin.bills.collections', 'permission' => 'billing.manage', 'icon' => 'receipt'],
                        ],
                    ],
                ],
            ],
            [
                'label' => 'Care',
                'items' => [
                    [
                        'label' => 'Admissions',
                        'route' => 'admin.admissions.index',
                        'permission' => 'admissions.view',
                        'icon' => 'hospital',
                        'children' => [
                            ['label' => 'Beds', 'route' => 'admin.beds.index', 'permission' => 'beds.view', 'icon' => 'bed'],
                            ['label' => 'Bed history', 'route' => 'admin.beds.events', 'permission' => 'beds.view', 'icon' => 'clipboard-clock'],
                        ],
                    ],
                    ['label' => 'Nursing', 'route' => 'admin.nursing.index', 'permission' => 'nursing.view', 'icon' => 'syringe'],
                    ['label' => 'Laboratory', 'route' => 'admin.labs.index', 'permission' => 'lab.view', 'icon' => 'flask'],
                ],
            ],
            [
                'label' => 'Hospital',
                'items' => [
                    ['label' => 'Emergency', 'route' => 'admin.emergency', 'permission' => 'operations.manage', 'icon' => 'ambulance'],
                    ['label' => 'Departments', 'route' => 'admin.departments.index', 'permission' => 'doctors.manage', 'icon' => 'hospital'],
                    ['label' => 'Doctors', 'route' => 'admin.doctors.index', 'permission' => 'doctors.manage', 'icon' => 'stethoscope'],
                    ['label' => 'Clinic hours', 'route' => 'admin.clinic-hours.edit', 'permission' => null, 'icon' => 'calendar', 'when' => 'linked-doctor'],
                    ['label' => 'Staff', 'route' => 'admin.users.index', 'permission' => 'users.manage', 'icon' => 'nurse'],
                    ['label' => 'Roles & permissions', 'route' => 'admin.roles.index', 'permission' => 'users.manage', 'icon' => 'clipboard-check'],
                ],
            ],
            [
                'label' => 'Reports',
                'items' => [
                    ['label' => 'Queue history', 'route' => 'admin.work.history', 'permission' => null, 'icon' => 'clipboard-clock'],
                    ['label' => 'Critical alerts', 'route' => 'admin.alerts', 'permission' => 'operations.manage', 'icon' => 'pulse'],
                    ['label' => 'Capacity forecasts', 'route' => 'admin.forecasts', 'permission' => 'operations.manage', 'icon' => 'hospital'],
                    ['label' => 'Operational reports', 'route' => 'admin.reports', 'permission' => 'operations.manage', 'icon' => 'thermometer'],
                ],
            ],
            [
                'label' => 'System',
                'items' => [
                    [
                        'label' => 'Website',
                        'route' => 'admin.services.index',
                        'permission' => 'content.manage',
                        'icon' => 'first-aid',
                        'children' => [
                            ['label' => 'Services', 'route' => 'admin.services.index', 'permission' => 'content.manage', 'icon' => 'first-aid'],
                            ['label' => 'Banners', 'route' => 'admin.banners.index', 'permission' => 'content.manage', 'icon' => 'spark'],
                            ['label' => 'FAQs', 'route' => 'admin.faqs.index', 'permission' => 'content.manage', 'icon' => 'help'],
                            ['label' => 'News', 'route' => 'admin.blog-posts.index', 'permission' => 'content.manage', 'icon' => 'clipboard'],
                            ['label' => 'News categories', 'route' => 'admin.blog-categories.index', 'permission' => 'content.manage', 'icon' => 'grid'],
                            ['label' => 'Reviews', 'route' => 'admin.testimonials.index', 'permission' => 'content.manage', 'icon' => 'users'],
                            ['label' => 'Gallery', 'route' => 'admin.galleries.index', 'permission' => 'content.manage', 'icon' => 'eye'],
                            ['label' => 'Packages', 'route' => 'admin.health-packages.index', 'permission' => 'content.manage', 'icon' => 'pill'],
                        ],
                    ],
                    ['label' => 'Finance overview', 'route' => 'admin.owner.finance.index', 'permission' => 'finance.view', 'icon' => 'receipt'],
                    ['label' => 'Finance approvals', 'route' => 'admin.owner.approvals.index', 'permission' => null, 'icon' => 'clipboard-check', 'when' => 'finance-approvals'],
                    ['label' => 'Settings', 'route' => 'admin.settings.edit', 'permission' => 'settings.manage', 'icon' => 'settings'],
                    ['label' => 'Audit logs', 'route' => 'admin.system.audit-logs.index', 'permission' => 'audit.view', 'icon' => 'clipboard-clock'],
                    ['label' => 'Database backup', 'route' => 'admin.system.backup.index', 'permission' => null, 'icon' => 'settings', 'when' => 'super-admin'],
                    ['label' => 'Help', 'route' => 'admin.help', 'permission' => null, 'icon' => 'help'],
                ],
            ],
        ];
    }

    /**
     * @param  array{label: string, route: string, permission: ?string, icon: string, when?: string, children?: list<array<string, mixed>>}  $item
     * @return list<array{label: string, href: string, icon: string, active: bool, current: bool, children: list<array{label: string, href: string, icon: string, active: bool, current: bool, children: list<empty>}>}>
     */
    private function hydrateBranch(?User $user, array $item): array
    {
        $children = [];

        foreach ($item['children'] ?? [] as $child) {
            $hydratedChild = $this->hydrateItem($user, $child);

            if ($hydratedChild !== null) {
                $children[] = $hydratedChild;
            }
        }

        $parent = $this->hydrateItem($user, $item);

        if ($parent === null) {
            return $children;
        }

        $parent['children'] = $children;
        $parent['current'] = $parent['active'] || collect($children)->contains(fn (array $child): bool => $child['current']);

        return [$parent];
    }

    /**
     * @param  array{label: string, route: string, permission: ?string, icon: string, when?: string}  $item
     * @return array{label: string, href: string, icon: string, active: bool, current: bool, children: list<empty>}|null
     */
    private function hydrateItem(?User $user, array $item): ?array
    {
        if (($item['when'] ?? null) === 'linked-doctor' && $user?->doctor === null) {
            return null;
        }

        if (($item['when'] ?? null) === 'super-admin' && ! ($user?->hasRole('super_admin') ?? false)) {
            return null;
        }

        if (($item['when'] ?? null) === 'finance-approvals') {
            $canFinanceApprovals = ($user?->can('finance.view') ?? false)
                || ($user?->can('finance.approve') ?? false)
                || ($user?->can('billing.manage') ?? false);

            if (! $canFinanceApprovals) {
                return null;
            }
        }

        if ($item['permission'] !== null && ! ($user?->can($item['permission']) ?? false)) {
            return null;
        }

        $active = $this->isCurrent($item['route']);

        return [
            'label' => $item['label'],
            'href' => route($item['route']),
            'icon' => $item['icon'],
            'active' => $active,
            'current' => $active,
            'children' => [],
        ];
    }

    private function isCurrent(string $route): bool
    {
        if ($route === 'admin.appointments.create') {
            return request()->routeIs('admin.appointments.create');
        }

        if ($route === 'admin.appointments.index') {
            return request()->routeIs('admin.appointments.index')
                || request()->routeIs('admin.appointments.show');
        }

        if ($route === 'admin.beds.index') {
            return request()->routeIs('admin.beds.index')
                || request()->routeIs('admin.beds.show');
        }

        if ($route === 'admin.beds.events') {
            return request()->routeIs('admin.beds.events');
        }

        if ($route === 'admin.doctors.index') {
            return request()->routeIs('admin.doctors.*')
                || request()->routeIs('admin.departments.*');
        }

        return request()->routeIs($route)
            || request()->routeIs(str_replace('.index', '.*', $route))
            || request()->routeIs(str_replace('.edit', '.*', $route));
    }

    private function searchRoute(?User $user): string
    {
        if ($user?->hasRole('receptionist') && $user->can('appointments.view')) {
            return route('admin.appointments.index');
        }

        if ($user?->hasRole('nurse_station') && $user->can('nursing.view')) {
            return route('admin.nursing.index');
        }

        if ($user?->can('appointments.view')) {
            return route('admin.appointments.index');
        }

        if ($user?->can('nursing.view')) {
            return route('admin.nursing.index');
        }

        if ($user?->can('beds.view') || $user?->can('operations.manage')) {
            return route('admin.beds.index');
        }

        if ($user?->can('patients.view')) {
            return route('admin.patients.index');
        }

        return route('admin.dashboard');
    }

    private function searchPlaceholder(?User $user): string
    {
        if ($user?->hasRole('receptionist') || $user?->can('appointments.view')) {
            return 'Search patients or appointments';
        }

        if ($user?->can('nursing.view')) {
            return 'Search wards or patients';
        }

        if ($user?->can('beds.view') || $user?->can('operations.manage')) {
            return 'Search beds or patients';
        }

        if ($user?->can('patients.view')) {
            return 'Search patients';
        }

        return 'Search';
    }
}
