<?php

namespace App\Providers;

use App\Models\User;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.public', 'web.*'], PublicLayoutComposer::class);
        View::composer(['layouts.admin'], AdminLayoutComposer::class);

        foreach ([
            'appointments.view',
            'appointments.manage',
            'patients.view',
            'patients.manage',
            'doctors.manage',
            'content.manage',
            'billing.manage',
            'settings.manage',
            'users.manage',
        ] as $permission) {
            Gate::define($permission, fn (?User $user) => (bool) $user?->hasPermission($permission));
        }

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by(Str::transliterate(
                Str::lower($request->string('email')->toString()).'|'.$request->ip()
            ));
        });
    }
}
