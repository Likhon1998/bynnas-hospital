<?php

namespace App\Providers;

use App\Models\User;
use App\Support\CriticalAlertFeed;
use App\View\Composers\AdminLayoutComposer;
use App\View\Composers\PublicLayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CriticalAlertFeed::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer(['layouts.public', 'web.*'], PublicLayoutComposer::class);
        View::composer(['layouts.admin', 'admin.*'], AdminLayoutComposer::class);

        foreach ([
            'appointments.view',
            'appointments.manage',
            'patients.view',
            'patients.manage',
            'doctors.manage',
            'content.manage',
            'billing.manage',
            'operations.manage',
            'settings.manage',
            'users.manage',
        ] as $permission) {
            Gate::define($permission, fn (?User $user) => (bool) $user?->hasPermission($permission));
        }

        Gate::define('beds.view', function (?User $user): bool {
            return (bool) (
                $user?->hasPermission('beds.view')
                || $user?->hasPermission('beds.operate')
                || $user?->hasPermission('beds.maintain')
                || $user?->hasPermission('operations.manage')
            );
        });

        Gate::define('beds.operate', function (?User $user): bool {
            return (bool) (
                $user?->hasPermission('beds.operate')
                || $user?->hasPermission('operations.manage')
            );
        });

        Gate::define('beds.maintain', function (?User $user): bool {
            return (bool) (
                $user?->hasPermission('beds.maintain')
                || $user?->hasPermission('operations.manage')
            );
        });

        Gate::define('visits.view', function (?User $user): bool {
            return (bool) (
                $user?->hasPermission('visits.view')
                || $user?->hasPermission('visits.checkin')
                || $user?->hasPermission('visits.triage')
                || $user?->hasPermission('visits.clinical')
            );
        });

        Gate::define('visits.checkin', fn (?User $user) => (bool) $user?->hasPermission('visits.checkin'));
        Gate::define('visits.triage', fn (?User $user) => (bool) $user?->hasPermission('visits.triage'));
        Gate::define('visits.clinical', fn (?User $user) => (bool) $user?->hasPermission('visits.clinical'));

        Gate::define('nursing.view', function (?User $user): bool {
            return (bool) (
                $user?->hasPermission('nursing.view')
                || $user?->hasPermission('nursing.chart')
                || $user?->hasPermission('nursing.handover')
                || $user?->hasPermission('nursing.meds')
            );
        });

        Gate::define('nursing.chart', fn (?User $user) => (bool) $user?->hasPermission('nursing.chart'));
        Gate::define('nursing.handover', fn (?User $user) => (bool) $user?->hasPermission('nursing.handover'));
        Gate::define('nursing.meds', fn (?User $user) => (bool) $user?->hasPermission('nursing.meds'));

        Gate::define('admissions.view', function (?User $user): bool {
            return (bool) (
                $user?->hasPermission('admissions.view')
                || $user?->hasPermission('admissions.admit')
                || $user?->hasPermission('admissions.discharge')
            );
        });

        Gate::define('admissions.admit', fn (?User $user) => (bool) $user?->hasPermission('admissions.admit'));
        Gate::define('admissions.discharge', fn (?User $user) => (bool) $user?->hasPermission('admissions.discharge'));

        Gate::define('lab.view', function (?User $user): bool {
            return (bool) (
                $user?->hasPermission('lab.view')
                || $user?->hasPermission('lab.order')
                || $user?->hasPermission('lab.collect')
                || $user?->hasPermission('lab.report')
            );
        });

        Gate::define('lab.order', fn (?User $user) => (bool) $user?->hasPermission('lab.order'));
        Gate::define('lab.collect', fn (?User $user) => (bool) $user?->hasPermission('lab.collect'));
        Gate::define('lab.report', fn (?User $user) => (bool) $user?->hasPermission('lab.report'));

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::transliterate(
                Str::lower($request->string('email')->toString()).'|'.$request->ip()
            ));
        });

        RateLimiter::for('appointment-requests', function (Request $request) {
            return Limit::perMinute(6)->by($request->ip());
        });

        RateLimiter::for('appointment-slots', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
