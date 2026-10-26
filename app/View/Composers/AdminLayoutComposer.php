<?php

namespace App\View\Composers;

use App\Models\HospitalSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();
        $hospitalName = HospitalSetting::query()->where('key', 'hospital_name')->value('value') ?: config('app.name');

        $navigation = collect([
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'permission' => null, 'group' => 'Overview', 'icon' => 'home'],
            ['label' => 'Appointments', 'route' => 'admin.appointments.index', 'permission' => 'appointments.view', 'group' => 'Patient Care', 'icon' => 'calendar'],
            ['label' => 'Patients', 'route' => 'admin.patients.index', 'permission' => 'patients.view', 'group' => 'Patient Care', 'icon' => 'users'],
            ['label' => 'Billing', 'route' => 'admin.bills.index', 'permission' => 'billing.manage', 'group' => 'Patient Care', 'icon' => 'receipt'],
            ['label' => 'Doctors', 'route' => 'admin.doctors.index', 'permission' => 'doctors.manage', 'group' => 'Medical Team', 'icon' => 'stethoscope'],
            ['label' => 'Departments', 'route' => 'admin.departments.index', 'permission' => 'doctors.manage', 'group' => 'Medical Team', 'icon' => 'building'],
            ['label' => 'Services', 'route' => 'admin.services.index', 'permission' => 'content.manage', 'group' => 'Medical Team', 'icon' => 'heart'],
            ['label' => 'Packages', 'route' => 'admin.health-packages.index', 'permission' => 'content.manage', 'group' => 'Medical Team', 'icon' => 'package'],
            ['label' => 'Blog Posts', 'route' => 'admin.blog-posts.index', 'permission' => 'content.manage', 'group' => 'Website Content', 'icon' => 'file'],
            ['label' => 'Blog Categories', 'route' => 'admin.blog-categories.index', 'permission' => 'content.manage', 'group' => 'Website Content', 'icon' => 'folder'],
            ['label' => 'Testimonials', 'route' => 'admin.testimonials.index', 'permission' => 'content.manage', 'group' => 'Website Content', 'icon' => 'quote'],
            ['label' => 'Gallery', 'route' => 'admin.galleries.index', 'permission' => 'content.manage', 'group' => 'Website Content', 'icon' => 'image'],
            ['label' => 'Banners', 'route' => 'admin.banners.index', 'permission' => 'content.manage', 'group' => 'Website Content', 'icon' => 'megaphone'],
            ['label' => 'Messages', 'route' => 'admin.contact-messages.index', 'permission' => 'content.manage', 'group' => 'Website Content', 'icon' => 'mail'],
            ['label' => 'Settings', 'route' => 'admin.settings.edit', 'permission' => 'settings.manage', 'group' => 'System', 'icon' => 'settings'],
            ['label' => 'Users', 'route' => 'admin.users.index', 'permission' => 'users.manage', 'group' => 'System', 'icon' => 'shield'],
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
        return request()->routeIs($route)
            || request()->routeIs(str_replace('.index', '.*', $route))
            || request()->routeIs(str_replace('.edit', '.*', $route));
    }

    private function searchRoute(?User $user): string
    {
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
        if ($user?->can('appointments.view')) {
            return 'Search visits, patients or doctors';
        }

        if ($user?->can('patients.view')) {
            return 'Search patients';
        }

        return 'Search';
    }
}
