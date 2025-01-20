<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Driver\DashboardController;
use App\Http\Controllers\Auth\DriverRegistrationController;

// Rutas públicas (no requieren autenticación)
Route::middleware('guest')->group(function () {
    // Registro por referencia de carrier
    Route::get('register/{carrier:slug}', [DriverRegistrationController::class, 'showRegistrationForm'])
        ->name('register');
    Route::post('register/{carrier:slug}', [DriverRegistrationController::class, 'register'])
        ->name('register.submit');

    // Confirmación de email
    Route::get('confirm/{token}', [DriverRegistrationController::class, 'confirmEmail'])
        ->name('confirm');

    // Páginas de estado
    Route::view('registration/success', 'auth.user_driver.success')
        ->name('registration.success');
    Route::view('quota-exceeded', 'auth.user_driver.quota-exceeded')
        ->name('quota-exceeded');
});

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth', 'role:driver'])->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Pasos del registro
    Route::prefix('registration')->name('registration.')->group(function () {
        Route::get('step1', [DriverRegistrationController::class, 'showStep1'])
            ->name('step1');
        Route::post('step1', [DriverRegistrationController::class, 'processStep1'])
            ->name('step1.process');
        
        Route::get('step2', [DriverRegistrationController::class, 'showStep2'])
            ->name('step2');
        Route::post('step2', [DriverRegistrationController::class, 'processStep2'])
            ->name('step2.process');
    });
});