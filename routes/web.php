<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\VehicleVerificationController;
use App\Http\Controllers\EmploymentVerificationController;

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

// Rutas que requieren autenticación pero NO son de carrier (estas deben ir en carrier.php)
Route::middleware(['auth'])->group(function () {
    // Aquí solo rutas generales autenticadas que no sean de carrier
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

// Rutas para verificación de empleo (sin autenticación)
Route::prefix('employment-verification')->name('employment-verification.')->group(function () {
    // IMPORTANTE: Las rutas específicas deben ir ANTES de las rutas con parámetros
    
    // Página de agradecimiento
    Route::get('/thank-you', [EmploymentVerificationController::class, 'thankYou'])
        ->name('thank-you');
    
    // Página de token expirado
    Route::get('/expired', [EmploymentVerificationController::class, 'expired'])
        ->name('expired');
    
    // Página de error
    Route::get('/error', [EmploymentVerificationController::class, 'error'])
        ->name('error');
    
    // Mostrar formulario de verificación (debe ir después de las rutas específicas)
    Route::get('/{token}', [EmploymentVerificationController::class, 'showVerificationForm'])
        ->name('form');
    
    // Procesar la verificación
    Route::post('/{token}/process', [EmploymentVerificationController::class, 'processVerification'])
        ->name('process');
});

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Ruta personalizada para cierre de sesión
Route::post('/custom-logout', [LogoutController::class, 'logout'])->name('custom.logout');