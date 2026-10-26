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
        $alertFeed = $view->name() === 'layouts.admin'
            ? $this->criticalAlertFeed->for($user, 6)
            : ['alerts' => collect(), 'counts' => [], 'total' => 0];

        $staffRoute = $user?->can('users.manage')
            ? 'admin.users.index'
            : 'admin.doctors.index';

        $staffPermission = $user?->can('users.manage')
            ? 'users.manage'
            : 'doctors.manage';

        $navigation = collect([
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'permission' => null, 'group' => 'Main Menu', 'icon' => 'home'],
            ['label' => 'Bed & Wards', 'route' => 'admin.beds.index', 'permission' => 'beds.view', 'group' => 'Main Menu', 'icon' => 'bed'],
            ['label' => 'Bed History', 'route' => 'admin.beds.events', 'permission' => 'beds.view', 'group' => 'Main Menu', 'icon' => 'clock'],
            ['label' => 'Emergency Flow', 'route' => 'admin.emergency', 'permission' => 'operations.manage', 'group' => 'Main Menu', 'icon' => 'spark'],
            ['label' => 'Staff Management', 'route' => $staffRoute, 'permission' => $staffPermission, 'group' => 'Main Menu', 'icon' => 'badge'],
            ['label' => 'Critical Alerts', 'route' => 'admin.alerts', 'permission' => 'operations.manage', 'group' => 'Monitoring & Control', 'icon' => 'megaphone'],
            ['label' => 'Capacity Forecasts', 'route' => 'admin.forecasts', 'permission' => 'operations.manage', 'group' => 'Monitoring & Control', 'icon' => 'grid'],
            ['label' => 'Operational Reports', 'route' => 'admin.reports', 'permission' => 'operations.manage', 'group' => 'Analytics & Reports', 'icon' => 'clock'],
            ['label' => 'Help & Supports', 'route' => 'admin.help', 'permission' => null, 'group' => 'Analytics & Reports', 'icon' => 'help'],
            ['label' => 'Settings', 'route' => 'admin.settings.edit', 'permission' => 'settings.manage', 'group' => 'Analytics & Reports', 'icon' => 'settings'],
            ['label' => 'Appointments', 'route' => 'admin.appointments.index', 'permission' => 'appointments.view', 'group' => 'Patient Care', 'icon' => 'calendar'],
            ['label' => 'Patients', 'route' => 'admin.patients.index', 'permission' => 'patients.view', 'group' => 'Patient Care', 'icon' => 'users'],
            ['label' => 'Billing', 'route' => 'admin.bills.index', 'permission' => 'billing.manage', 'group' => 'Patient Care', 'icon' => 'receipt'],
            ['label' => 'Website', 'route' => 'admin.services.index', 'permission' => 'content.manage', 'group' => 'Patient Care', 'icon' => 'globe'],
        ])->filter(function (array $item) use ($user): bool {
            return $item['permission'] === null || ($user?->can($item['permission']) ?? false);
        })->map(function (array $item): array {
            $item['href'] = route($item['route']);
            $item['active'] = $this->isCurrent($item['route']);

            return $item;
        })->values();

        $view->with([
            'hospitalName' => $hospitalName,
            'adminNavigation' => $navigation,
            'staffDisplayName' => $this->displayName($user),
            'staffRoleLabel' => $this->roleLabel($user),
            'staffInitials' => $this->initials($user),
            'staffEmail' => (string) $user?->email,
            'adminSearchRoute' => $this->searchRoute($user),
            'adminSearchPlaceholder' => $this->searchPlaceholder($user),
            'adminSearchUrl' => route('admin.search'),
            'adminNotifications' => $alertFeed['alerts'],
            'adminNotificationCount' => $alertFeed['total'],
            'adminNotificationsHref' => $user?->can('operations.manage')
                ? route('admin.alerts')
                : ($user?->can('beds.view') ? route('admin.beds.index') : route('admin.dashboard')),
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
            $user?->hasRole('hospital_admin') => 'Hospital Admin',
            $user?->hasRole('nurse_station') => 'Nurse Station',
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

    private function isCurrent(string $route): bool
    {
        if ($route === 'admin.services.index') {
            return request()->routeIs('admin.services.*')
                || request()->routeIs('admin.health-packages.*')
                || request()->routeIs('admin.blog-*')
                || request()->routeIs('admin.testimonials.*')
                || request()->routeIs('admin.galleries.*')
                || request()->routeIs('admin.banners.*')
                || request()->routeIs('admin.contact-messages.*');
        }

        return request()->routeIs($route)
            || request()->routeIs(str_replace('.index', '.*', $route))
            || request()->routeIs(str_replace('.edit', '.*', $route));
    }

    private function searchRoute(?User $user): string
    {
        if ($user?->can('beds.view') || $user?->can('operations.manage')) {
            return route('admin.beds.index');
        }

        if ($user?->can('appointments.view')) {
            return route('admin.appointments.index');
        }

        if ($user?->can('patients.view')) {
            return route('admin.patients.index');
        }

        return route('admin.dashboard');
    }

    private function searchPlaceholder(?User $user): string
    {
        if ($user?->can('beds.view') || $user?->can('operations.manage')) {
            return 'Search beds or patients';
        }

        if ($user?->can('appointments.view')) {
            return 'Search visits, patients or doctors';
        }

        if ($user?->can('patients.view')) {
            return 'Search patients';
        }

        return 'Search';
    }
}
