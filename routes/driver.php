<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Driver\StepController;
use App\Http\Controllers\Driver\StatusController;
use App\Livewire\Driver\CarrierSelectionComponent;
use App\Livewire\Driver\DriverRegistrationManager;
use App\Http\Controllers\Driver\TempUploadController;
use App\Http\Controllers\Driver\DashboardController;
use App\Http\Controllers\Driver\RegistrationController;
use App\Http\Controllers\Auth\DriverRegistrationController;


// Rutas públicas (no requieren autenticación)
Route::middleware('guest')->group(function () {
    // Registro por referencia de carrier
    Route::get('register/{carrier:slug}', [DriverRegistrationController::class, 'showRegistrationForm'])
        ->name('register')
        ->where('carrier', '[a-z0-9-]+')
        ->whereUuid('token'); // Add token validation

    // Application routes
    // Route::prefix('application')->name('application.')->group(function () {
    //     Route::get('/step/{step}', [StepController::class, 'showStep'])->name('step');
    //     Route::post('/step/{step}', [StepController::class, 'processStep'])->name('process');
    //     Route::get('/success', [StepController::class, 'success'])->name('success');
    // });

    Route::get('/complete-registration', [DriverRegistrationController::class, 'showCompleteRegistration'])
        ->name('complete_registration');

    Route::post('register/{carrier:slug}', [DriverRegistrationController::class, 'register'])
        ->name('register.submit');

    // Rutas de error y estado (necesitan ser públicas)
    Route::get('error', function () {
        return view('auth.user_driver.error');
    })->name('register.error');

    Route::get('quota-exceeded', function () {
        return view('auth.user_driver.quota-exceeded');
    })->name('quota-exceeded');

    Route::get('driver-status', function () {
        return view('auth.user_driver.driver-status');
    })->name('status');

    // Confirmación de email
    Route::get('confirm/{token}', [DriverRegistrationController::class, 'confirmEmail'])
        ->name('confirm');

    Route::get('registration/success', function () {
        $carrierName = session('carrier_name', 'the carrier');
        return view('auth.user_driver.success', ['carrierName' => $carrierName]);
    })->name('registration.success');
});


// Ruta para mostrar la selección de carriers para registro independiente
Route::get('/register', [DriverRegistrationController::class, 'showIndependentCarrierSelection'])
    ->name('register');


// Modificar esta ruta para incluir el carrier_slug como parámetro de ruta

Route::get('/register/form/{carrier_slug}', [DriverRegistrationController::class, 'showIndependentRegistrationForm'])
    ->name('register.form');

// Ruta para procesar el registro independiente
Route::post('/register', [DriverRegistrationController::class, 'registerIndependent'])
    ->name('register.submit');

// Ruta para registro con referencia
Route::get('/register/{carrier:slug}', [DriverRegistrationController::class, 'showRegistrationForm'])
    ->name('register.referred');


// Ruta para registro con token (referencia)
Route::get('/register/{carrier:slug}/token/{token}', App\Livewire\Driver\DriverRegistrationManager::class)
    ->name('referred.registration');


// Para la selección de carrier después de confirmar email
Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Estados de aplicación
    Route::get('pending', [StatusController::class, 'pending'])->name('pending');
    Route::get('rejected', [StatusController::class, 'rejected'])->name('rejected');
    Route::get('documents-pending', [StatusController::class, 'documentsPending'])->name('documents.pending');
    
    // Registro continuo
    Route::get('registration/continue/{step?}', [RegistrationController::class, 'continue'])->name('registration.continue');
    Route::post('registration/complete', [RegistrationController::class, 'complete'])->name('registration.complete');
    
    // Selección de transportista
    Route::get('/select-carrier', [DriverRegistrationController::class, 'showSelectCarrier'])->name('select_carrier');
    Route::post('/select-carrier', [DriverRegistrationController::class, 'selectCarrier'])->name('select_carrier.submit');

    // La ruta de carga de archivos temporales se ha movido fuera del grupo de autenticación
});

Route::post('/temp-upload', [TempUploadController::class, 'upload'])
    ->name('driver.temp.upload')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);


// This route is now handled within the auth middleware group above



// Rutas protegidas (requieren autenticación y rol de driver)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Estado pendiente    

    // Status routes
    Route::get('/carrier-status', function () {
        return view('driver.status.carrier');
    })->name('carrier.status');

    Route::get('/documents-pending', function () {
        return view('driver.status.documents-pending');
    })->name('documents.pending');
});
