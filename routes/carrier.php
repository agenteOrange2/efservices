<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Carrier\DocumentController;
use App\Http\Controllers\Carrier\CarrierProfileController;
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
    Route::group([
        'prefix' => '{carrier}',  // Quitar :slug de aquí
        'middleware' => ['auth']
    ], function () {
        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/documents/upload/{documentType}', [DocumentController::class, 'upload'])->name('documents.upload');
        Route::post('/documents/skip', [DocumentController::class, 'skipDocuments'])->name('documents.skip');
        Route::post('/documents/complete', [DocumentController::class, 'complete'])->name('documents.complete');
        Route::post('/documents/{documentType}/toggle-default', [DocumentController::class, 'toggleDefaultDocument'])
            ->name('documents.toggle-default');
    });


    
        // La vista principal del perfil
        Route::get('/profile', [CarrierProfileController::class, 'index'])->name('profile');
        // Vista de edición del perfil
        Route::get('/profile/edit', [CarrierProfileController::class, 'edit'])->name('profile.edit');
        // Actualizar perfil
        Route::put('/profile/update', [CarrierProfileController::class, 'update'])->name('profile.update');
    

});