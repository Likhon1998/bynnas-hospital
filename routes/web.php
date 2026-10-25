<?php

use App\Http\Controllers\Web\AboutController;
use App\Http\Controllers\Web\AppointmentRequestController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/services', [ServiceController::class, 'index'])->name('services');
Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
Route::post('/appointment-requests', [AppointmentRequestController::class, 'store'])
    ->middleware('throttle:appointment-requests')
    ->name('appointment-requests.store');

require __DIR__.'/admin.php';
