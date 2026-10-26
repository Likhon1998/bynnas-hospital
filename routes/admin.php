<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BedEventController;
use App\Http\Controllers\Admin\BedWardController;
use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\CapacityForecastController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CriticalAlertController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\EmergencyFlowController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\HealthPackageController;
use App\Http\Controllers\Admin\HelpSupportController;
use App\Http\Controllers\Admin\HospitalSettingController;
use App\Http\Controllers\Admin\OperationalReportController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])
            ->middleware('throttle:admin-login')
            ->name('login.store');
    });

    Route::post('logout', [LoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    Route::middleware(['auth', 'staff'])->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('search', SearchController::class)->name('search');
        Route::get('help', HelpSupportController::class)->name('help');

        Route::middleware('permission:beds.view')->group(function (): void {
            Route::get('beds', [BedWardController::class, 'index'])->name('beds.index');
            Route::get('beds/events', [BedEventController::class, 'index'])->name('beds.events');
        });

        Route::middleware('permission:beds.operate')->group(function (): void {
            Route::put('beds/{bed}', [BedWardController::class, 'update'])->name('beds.update');
            Route::post('beds/{bed}/reserve', [BedWardController::class, 'reserve'])->name('beds.reserve');
            Route::delete('beds/{bed}/reservation', [BedWardController::class, 'cancelReservation'])->name('beds.reservation.cancel');
            Route::post('beds/{bed}/transfer', [BedWardController::class, 'transfer'])->name('beds.transfer');
            Route::post('beds/{bed}/inspect', [BedWardController::class, 'inspect'])->name('beds.inspect');
        });

        Route::middleware('permission:beds.maintain')->group(function (): void {
            Route::post('beds/{bed}/maintain', [BedWardController::class, 'maintain'])->name('beds.maintain');
        });

        Route::middleware('permission:operations.manage')->group(function (): void {
            Route::get('emergency', EmergencyFlowController::class)->name('emergency');
            Route::get('alerts', CriticalAlertController::class)->name('alerts');
            Route::get('forecasts', CapacityForecastController::class)->name('forecasts');
            Route::get('reports', OperationalReportController::class)->name('reports');
        });

        Route::middleware('permission:appointments.view')->group(function (): void {
            Route::resource('appointments', AppointmentController::class)->only(['index', 'show', 'update']);
        });

        Route::middleware('permission:patients.view')->group(function (): void {
            Route::resource('patients', PatientController::class)->only(['index', 'show', 'create', 'store', 'edit', 'update']);
        });

        Route::middleware('permission:billing.manage')->group(function (): void {
            Route::post('bills/{bill}/issue', [BillController::class, 'issue'])->name('bills.issue');
            Route::post('bills/{bill}/pay', [BillController::class, 'pay'])->name('bills.pay');
            Route::post('bills/{bill}/cancel', [BillController::class, 'cancel'])->name('bills.cancel');
            Route::get('bills/{bill}/print', [BillController::class, 'print'])->name('bills.print');
            Route::resource('bills', BillController::class);
        });

        Route::middleware('permission:doctors.manage')->group(function (): void {
            Route::resource('departments', DepartmentController::class)->except(['show']);
            Route::resource('doctors', DoctorController::class)->except(['show']);
        });

        Route::middleware('permission:content.manage')->group(function (): void {
            Route::resource('services', ServiceController::class)->except(['show']);
            Route::resource('health-packages', HealthPackageController::class)->except(['show']);
            Route::resource('blog-categories', BlogCategoryController::class)->except(['show']);
            Route::resource('blog-posts', BlogPostController::class)->except(['show']);
            Route::resource('testimonials', TestimonialController::class)->except(['show']);
            Route::resource('galleries', GalleryController::class)->except(['show']);
            Route::resource('banners', BannerController::class)->except(['show']);
            Route::resource('contact-messages', ContactMessageController::class)->only(['index', 'show', 'update', 'destroy']);
        });

        Route::middleware('permission:settings.manage')->group(function (): void {
            Route::get('settings', [HospitalSettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [HospitalSettingController::class, 'update'])->name('settings.update');
        });

        Route::middleware('permission:users.manage')->group(function (): void {
            Route::resource('users', UserController::class)->only(['index', 'edit', 'update']);
        });
    });
});
