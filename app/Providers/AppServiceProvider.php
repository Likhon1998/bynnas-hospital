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

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::transliterate(
                Str::lower($request->string('email')->toString()).'|'.$request->ip()
            ));
        });

        RateLimiter::for('appointment-requests', function (Request $request) {
            return Limit::perMinute(6)->by($request->ip());
        });
    }
}
