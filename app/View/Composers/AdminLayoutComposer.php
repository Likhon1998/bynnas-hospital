<?php

namespace App\View\Composers;

use App\Models\HospitalSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();
        $hospitalName = HospitalSetting::query()->where('key', 'hospital_name')->value('value') ?: config('app.name');

        $navigation = collect([
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'permission' => null],
            ['label' => 'Appointments', 'route' => 'admin.appointments.index', 'permission' => 'appointments.view'],
            ['label' => 'Patients', 'route' => 'admin.patients.index', 'permission' => 'patients.view'],
            ['label' => 'Billing', 'route' => 'admin.bills.index', 'permission' => 'billing.manage'],
            ['label' => 'Departments', 'route' => 'admin.departments.index', 'permission' => 'doctors.manage'],
            ['label' => 'Doctors', 'route' => 'admin.doctors.index', 'permission' => 'doctors.manage'],
            ['label' => 'Services', 'route' => 'admin.services.index', 'permission' => 'content.manage'],
            ['label' => 'Health Packages', 'route' => 'admin.health-packages.index', 'permission' => 'content.manage'],
            ['label' => 'Blog Categories', 'route' => 'admin.blog-categories.index', 'permission' => 'content.manage'],
            ['label' => 'Blog Posts', 'route' => 'admin.blog-posts.index', 'permission' => 'content.manage'],
            ['label' => 'Testimonials', 'route' => 'admin.testimonials.index', 'permission' => 'content.manage'],
            ['label' => 'Gallery', 'route' => 'admin.galleries.index', 'permission' => 'content.manage'],
            ['label' => 'Banners', 'route' => 'admin.banners.index', 'permission' => 'content.manage'],
            ['label' => 'Messages', 'route' => 'admin.contact-messages.index', 'permission' => 'content.manage'],
            ['label' => 'Settings', 'route' => 'admin.settings.edit', 'permission' => 'settings.manage'],
            ['label' => 'Users', 'route' => 'admin.users.index', 'permission' => 'users.manage'],
        ])->filter(function (array $item) use ($user): bool {
            return $item['permission'] === null || ($user?->can($item['permission']) ?? false);
        })->values();

        $view->with([
            'hospitalName' => $hospitalName,
            'adminNavigation' => $navigation,
        ]);
    }
}
