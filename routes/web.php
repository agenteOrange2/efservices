<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Middleware\EnsureCarrierRegistered;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;


// Rutas para UserCarrier
Route::prefix('user-carrier')->name('user_carrier.')->group(function () {
    // Dashboard para UserCarrier
    Route::middleware(['auth:user_carrier', EnsureCarrierRegistered::class])->group(function () {
        Route::get('/dashboard', function () {
            return view('user_carrier.dashboard');
        })->name('dashboard');
    });

    // Login para UserCarrier
    Route::get('/login', [CustomLoginController::class, 'showLoginForm'])
        ->middleware(['guest:user_carrier'])
        ->name('login');
    Route::post('/login', [CustomLoginController::class, 'login'])
        ->middleware(['guest:user_carrier']);

    // Registro para UserCarrier
    Route::get('/register', [CustomLoginController::class, 'showRegisterForm'])
        ->middleware(['guest:user_carrier'])
        ->name('register');
    Route::post('/register', [CustomLoginController::class, 'register'])
        ->middleware(['guest:user_carrier']);

    // Confirmación de email
    Route::get('/confirm/{token}', [CustomLoginController::class, 'confirmEmail'])
        ->name('confirm');

    // Completar registro del Carrier
    Route::get('/complete-registration', [CustomLoginController::class, 'showCompleteRegistrationForm'])
        ->middleware('auth:user_carrier')
        ->name('complete_registration');
    Route::post('/complete-registration', [CustomLoginController::class, 'completeRegistration'])
        ->middleware('auth:user_carrier');
});

// Rutas para UserDriver
Route::prefix('user-driver')->name('user_driver.')->group(function () {
    Route::get('/login', [CustomLoginController::class, 'showLoginForm'])
        ->middleware(['guest:user_driver'])
        ->name('login');
    Route::post('/login', [CustomLoginController::class, 'login'])
        ->middleware(['guest:user_driver']);

    //Driver Register
    Route::get('/register', [CustomLoginController::class, 'showRegisterForm'])
        ->middleware(['guest:user_carrier'])
        ->name('register');
    Route::post('/register', [CustomLoginController::class, 'register'])
        ->middleware(['guest:user_carrier']);
});



Route::get('/', function () {
    return view('welcome');
});

/*
Route::group(['middleware' => ['auth:sanctum', config('jetstream.auth_session'), 'verified'],
'prefix' => 'dashboard'], function(){
    Route::get('/', function(){
        return view('dashboard');
    })->name('dashboard');

});
*/