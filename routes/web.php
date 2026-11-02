<?php

use App\Http\Controllers\Web\AboutController;
use App\Http\Controllers\Web\AppointmentRequestController;
use App\Http\Controllers\Web\AppointmentScheduleController;
use App\Http\Controllers\Web\AppointmentSlotController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\DoctorController;
use App\Http\Controllers\Web\EmergencyController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LegalPageController;
use App\Http\Controllers\Web\PackageController;
use App\Http\Controllers\Web\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/departments', DepartmentController::class)->name('departments');
Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors');
Route::get('/doctors/{doctor:slug}', [DoctorController::class, 'show'])->name('doctors.show');
Route::get('/emergency', EmergencyController::class)->name('emergency');
Route::get('/services', [ServiceController::class, 'index'])->name('services');
Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::get('/packages', [PackageController::class, 'index'])->name('packages');
Route::get('/news', [BlogController::class, 'index'])->name('news');
Route::get('/news/{post:slug}', [BlogController::class, 'show'])->name('news.show');
Route::get('/privacy', [LegalPageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [LegalPageController::class, 'terms'])->name('terms');
Route::get('/appointment-slots', AppointmentSlotController::class)
    ->middleware('throttle:appointment-slots')
    ->name('appointment-slots');
Route::get('/appointment-schedule', AppointmentScheduleController::class)
    ->middleware('throttle:appointment-slots')
    ->name('appointment-schedule');
Route::post('/appointment-requests', [AppointmentRequestController::class, 'store'])
    ->middleware('throttle:appointment-requests')
    ->name('appointment-requests.store');

require __DIR__.'/admin.php';
