<?php

use App\Http\Controllers\Admin\AdmissionController;
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
use App\Http\Controllers\Admin\DoctorLeaveController;
use App\Http\Controllers\Admin\EmergencyFlowController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\HealthPackageController;
use App\Http\Controllers\Admin\HelpSupportController;
use App\Http\Controllers\Admin\HospitalSettingController;
use App\Http\Controllers\Admin\LabOrderController;
use App\Http\Controllers\Admin\LabResultController;
use App\Http\Controllers\Admin\LabTestController;
use App\Http\Controllers\Admin\MedicationAdministrationController;
use App\Http\Controllers\Admin\NotificationFeedController;
use App\Http\Controllers\Admin\NursingController;
use App\Http\Controllers\Admin\NursingHandoverController;
use App\Http\Controllers\Admin\NursingObservationController;
use App\Http\Controllers\Admin\NursingTaskController;
use App\Http\Controllers\Admin\OperationalReportController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitController;
use App\Http\Controllers\Admin\WorkItemController;
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
        Route::get('notifications', [NotificationFeedController::class, 'index'])->name('notifications');
        Route::post('notifications/seen', [NotificationFeedController::class, 'markSeen'])->name('notifications.seen');
        Route::get('work/history', [WorkItemController::class, 'history'])->name('work.history');
        Route::post('work/{workItem}/acknowledge', [WorkItemController::class, 'acknowledge'])->name('work.acknowledge');
        Route::post('work/{workItem}/escalate', [WorkItemController::class, 'escalate'])->name('work.escalate');
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
            Route::resource('appointments', AppointmentController::class)->only(['index', 'show', 'create', 'store', 'update']);
        });

        Route::middleware('permission:visits.checkin')->group(function (): void {
            Route::post('appointments/{appointment}/check-in', [VisitController::class, 'checkIn'])->name('appointments.check-in');
            Route::get('visits/create', [VisitController::class, 'create'])->name('visits.create');
            Route::post('visits', [VisitController::class, 'store'])->name('visits.store');
        });

        Route::middleware('permission:visits.view')->group(function (): void {
            Route::get('visits', [VisitController::class, 'index'])->name('visits.index');
            Route::get('visits/{visit}', [VisitController::class, 'show'])->name('visits.show');
        });

        Route::middleware('permission:visits.triage')->group(function (): void {
            Route::post('visits/{visit}/vitals', [VisitController::class, 'vitals'])->name('visits.vitals');
        });

        Route::middleware('permission:visits.clinical')->group(function (): void {
            Route::post('visits/{visit}/clinical', [VisitController::class, 'clinical'])->name('visits.clinical');
        });

        Route::middleware('permission:nursing.view')->group(function (): void {
            Route::get('nursing', [NursingController::class, 'index'])->name('nursing.index');
            Route::get('nursing/wards/{ward}', [NursingController::class, 'ward'])->name('nursing.wards.show');
            Route::get('nursing/patients/{patient}', [NursingController::class, 'chart'])->name('nursing.patients.show');
        });

        Route::middleware('permission:nursing.chart')->group(function (): void {
            Route::post('nursing/patients/{patient}/observations', [NursingObservationController::class, 'store'])->name('nursing.observations.store');
            Route::post('nursing/patients/{patient}/tasks', [NursingTaskController::class, 'store'])->name('nursing.tasks.store');
            Route::patch('nursing/tasks/{task}', [NursingTaskController::class, 'update'])->name('nursing.tasks.update');
        });

        Route::middleware('permission:nursing.handover')->group(function (): void {
            Route::post('nursing/wards/{ward}/handovers', [NursingHandoverController::class, 'store'])->name('nursing.handovers.store');
        });

        Route::middleware('permission:nursing.meds')->group(function (): void {
            Route::post('nursing/patients/{patient}/medications', [MedicationAdministrationController::class, 'store'])->name('nursing.medications.store');
        });

        Route::middleware('permission:admissions.admit')->group(function (): void {
            Route::get('admissions/create', [AdmissionController::class, 'create'])->name('admissions.create');
            Route::post('admissions', [AdmissionController::class, 'store'])->name('admissions.store');
        });

        Route::middleware('permission:admissions.view')->group(function (): void {
            Route::get('admissions', [AdmissionController::class, 'index'])->name('admissions.index');
            Route::get('admissions/{admission}', [AdmissionController::class, 'show'])->name('admissions.show');
            Route::post('admissions/{admission}/discharge', [AdmissionController::class, 'discharge'])->name('admissions.discharge');
        });

        Route::middleware('permission:beds.operate')->group(function (): void {
            Route::post('admissions/{admission}/transfers', [AdmissionController::class, 'transfer'])->name('admissions.transfers.store');
        });

        Route::middleware('permission:admissions.discharge')->group(function (): void {
            Route::post('admissions/{admission}/authorize', [AdmissionController::class, 'authorizeDischarge'])->name('admissions.authorize');
        });

        Route::middleware('permission:lab.order')->group(function (): void {
            Route::get('labs/create', [LabOrderController::class, 'create'])->name('labs.create');
            Route::post('labs', [LabOrderController::class, 'store'])->name('labs.store');
        });

        Route::middleware('permission:lab.report')->group(function (): void {
            Route::get('labs/tests/create', [LabTestController::class, 'create'])->name('labs.tests.create');
            Route::post('labs/tests', [LabTestController::class, 'store'])->name('labs.tests.store');
            Route::get('labs/tests/{labTest}/edit', [LabTestController::class, 'edit'])->name('labs.tests.edit');
            Route::put('labs/tests/{labTest}', [LabTestController::class, 'update'])->name('labs.tests.update');
            Route::post('labs/{labOrder}/process', [LabOrderController::class, 'process'])->name('labs.process');
            Route::post('labs/{labOrder}/results', [LabResultController::class, 'store'])->name('labs.results.store');
        });

        Route::middleware('permission:lab.view')->group(function (): void {
            Route::get('labs', [LabOrderController::class, 'index'])->name('labs.index');
            Route::get('labs/tests', [LabTestController::class, 'index'])->name('labs.tests.index');
            Route::get('labs/{labOrder}', [LabOrderController::class, 'show'])->name('labs.show');
            Route::post('labs/{labOrder}/cancel', [LabOrderController::class, 'cancel'])->name('labs.cancel');
        });

        Route::middleware('permission:lab.collect')->group(function (): void {
            Route::post('labs/{labOrder}/collect', [LabOrderController::class, 'collect'])->name('labs.collect');
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
            Route::post('doctors/{doctor}/leaves', [DoctorLeaveController::class, 'store'])->name('doctors.leaves.store');
            Route::delete('doctors/{doctor}/leaves/{doctorLeave}', [DoctorLeaveController::class, 'destroy'])
                ->scopeBindings()
                ->name('doctors.leaves.destroy');
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
