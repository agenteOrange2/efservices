<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Auth\CustomLoginController;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;


// Rutas para UserCarrier
Route::prefix('user-carrier')->name('user_carrier.')->group(function () {
    Route::get('/login', [CustomLoginController::class, 'showLoginForm'])
        ->middleware(['guest:user_carrier'])
        ->name('login');
    Route::post('/login', [CustomLoginController::class, 'login'])
        ->middleware(['guest:user_carrier']);

    //Carrier
    Route::get('/register', [CustomLoginController::class, 'showRegisterForm'])
        ->middleware(['guest:user_carrier'])
        ->name('register');
    Route::post('/register', [CustomLoginController::class, 'register'])
        ->middleware(['guest:user_carrier']);
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