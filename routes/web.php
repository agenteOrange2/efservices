<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomLoginController;

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
    Route::get('/confirmation', function () {
        return view('auth.user_carrier.confirmation');
    })->name('confirmation');
});

Route::get('/', function () {
    return view('welcome');
});