<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Carrier\DocumentController;
use App\Http\Controllers\Admin\UserCarrierDocumentController;

// Rutas públicas
Route::middleware('guest')->group(function () {
    Route::get('/register', [CustomLoginController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [CustomLoginController::class, 'register']);
});

// Ruta de confirmación
Route::get('/confirm/{token}', [CustomLoginController::class, 'confirmEmail'])->name('confirm');

// Rutas que requieren autenticación
Route::middleware(['auth'])->group(function () {
    // Dashboard y otras rutas protegidas
    Route::get('/dashboard', function () {
        // Obtener el carrier del usuario autenticado
        $carrier = auth()->user()->carrierDetails->carrier;
        return view('carrier.dashboard', compact('carrier'));
    })->name('dashboard');

    Route::get('/complete-registration', [CustomLoginController::class, 'showCompleteRegistrationForm'])
        ->name('complete_registration');
    Route::post('/complete-registration', [CustomLoginController::class, 'completeRegistration']);

    Route::get('/confirmation', function () {
        return view('auth.user_carrier.confirmation');
    })->name('confirmation');

    // Rutas para documentos
    Route::prefix('{carrier:slug}/documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');        
        Route::post('/upload/{documentType}', [DocumentController::class, 'upload'])->name('upload');
        Route::post('/skip', [DocumentController::class, 'skipDocuments'])->name('skip');
        Route::post('/complete', [DocumentController::class, 'complete'])->name('complete');
        Route::post('/{documentType}/toggle-default', [DocumentController::class, 'toggleDefaultDocument'])
        ->name('toggle-default');  //
    });
});