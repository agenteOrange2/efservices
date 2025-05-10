<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\VehicleVerificationController;

// Rutas públicas (sin autenticación)
/*
Route::middleware('guest')->group(function () {
    Route::get('/register', [CustomLoginController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [CustomLoginController::class, 'register']);
});
*/


Route::redirect('/user-carrier/register', '/carrier/register');
// Ruta de confirmación
Route::get('/confirm/{token}', [CustomLoginController::class, 'confirmEmail'])->name('confirm');

// Rutas que requieren autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('/complete-registration', [CustomLoginController::class, 'showCompleteRegistrationForm'])
        ->name('complete_registration');
    Route::post('/complete-registration', [CustomLoginController::class, 'completeRegistration']);
    // Route::get('/confirmation', function () {
    //     return view('auth.user_carrier.confirmation');
    // })->name('confirmation');
    
});

// Rutas para verificación de vehículos de terceros (sin autenticación)
Route::prefix('vehicle-verification')->name('vehicle.verification.')->group(function () {
    // Mostrar formulario de verificación
    Route::get('/{token}', [VehicleVerificationController::class, 'showVerificationForm'])
        ->name('form');
    
    // Procesar la verificación
    Route::post('/{token}/process', [VehicleVerificationController::class, 'processVerification'])
        ->name('process');
    
    // Página de agradecimiento
    Route::get('/{token}/thank-you', [VehicleVerificationController::class, 'showThankYou'])
        ->name('thank-you');
});

Route::get('/', function () {
    return view('welcome');
});

// Ruta personalizada para cierre de sesión
Route::post('/custom-logout', [LogoutController::class, 'logout'])->name('custom.logout');