<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Driver\StepController;
use App\Http\Controllers\Driver\DashboardController;
use App\Http\Controllers\Auth\DriverRegistrationController;


// Rutas públicas (no requieren autenticación)
Route::middleware('guest')->group(function () {
    // Registro por referencia de carrier
    Route::get('register/{carrier:slug}', [DriverRegistrationController::class, 'showRegistrationForm'])
        ->name('register')
        ->where('carrier', '[a-z0-9-]+')
        ->whereUuid('token'); // Add token validation

    // Application routes
    Route::prefix('application')->name('application.')->group(function () {
        Route::get('/step/{step}', [StepController::class, 'showStep'])->name('step');
        Route::post('/step/{step}', [StepController::class, 'processStep'])->name('process');
        Route::get('/success', [StepController::class, 'success'])->name('success');
    });

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




    // Pasos del registro
    Route::prefix('registration')->name('registration.')->group(function () {
        /*
        Route::get('step1', [DriverRegistrationController::class, 'showStep1'])
            ->name('step1');
        Route::post('step1', [DriverRegistrationController::class, 'processStep1'])
            ->name('step1.process');

        Route::get('step2', [DriverRegistrationController::class, 'showStep2'])
            ->name('step2');
        Route::post('step2', [DriverRegistrationController::class, 'processStep2'])
            ->name('step2.process');
        */
        Route::get('/step/{step}', [StepController::class, 'showStep'])->name('step');
        Route::post('/step/1', [StepController::class, 'processStep1'])->name('step.1');
        Route::post('/step/2', [StepController::class, 'processStep2'])->name('step.2');
        Route::post('/step/3', [StepController::class, 'processStep3'])->name('step.3');
    });
});



/*
|--------------------------------------------------------------------------
| RUTAS PARA SUPERADMIN: ADMIN DRIVERS
|--------------------------------------------------------------------------
*/

// En el grupo existente de user_drivers
Route::prefix('carrier/{carrier}/drivers')->name('carrier.user_drivers.')->group(function () {
    // Rutas existentes...
    Route::get('/', [StepController::class, 'index'])->name('index');
    Route::get('/create', [StepController::class, 'create'])->name('create');
    Route::post('/', [StepController::class, 'store'])->name('store');
    Route::get('/{userDriverDetail}/edit', [StepController::class, 'edit'])->name('edit');
    Route::put('/{userDriverDetail}', [StepController::class, 'update'])->name('update');
    Route::delete('/{userDriverDetail}', [StepController::class, 'destroy'])->name('destroy');
    Route::delete('/{userDriverDetail}/photo', [StepController::class, 'deletePhoto'])->name('delete-photo');

    // Agregar las rutas de aplicación
    Route::get('/application/step1', [StepController::class, 'createStep1'])->name('application.step1');
    Route::post('/application/step1', [StepController::class, 'storeStep1'])->name('application.step1.store');
    Route::get('/application/step2/{application}', [StepController::class, 'createStep2'])->name('application.step2');
    Route::post('/application/step2/{application}', [StepController::class, 'storeStep2'])->name('application.step2.store');
    Route::get('/application/step3/{application}', [StepController::class, 'createStep3'])->name('application.step3');
    Route::post('/application/step3/{application}', [StepController::class, 'storeStep3'])->name('application.step3.store');
    Route::get('/application/{application}/review', [StepController::class, 'reviewApplication'])->name('application.review');
});
