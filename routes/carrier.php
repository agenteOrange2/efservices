<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Auth\CarrierRegistrationController;
use App\Http\Controllers\Auth\CarrierOnboardingController;
use App\Http\Controllers\Auth\CarrierAuthController;
use App\Http\Controllers\Auth\CarrierStatusController;
use App\Http\Controllers\Auth\CarrierDocumentController;
use App\Http\Controllers\Auth\CarrierWizardController;
use App\Http\Controllers\Carrier\DocumentController;
use App\Http\Controllers\Carrier\CarrierDriverController;
use App\Http\Controllers\Carrier\CarrierProfileController;
use App\Http\Controllers\Carrier\CarrierDriverManagementController;
use App\Http\Controllers\Carrier\CarrierVehicleController;
use App\Http\Controllers\Carrier\CarrierDriverAccidentsController;
use App\Http\Controllers\Carrier\CarrierDriverTestingsController;
use App\Http\Controllers\Carrier\CarrierDriverInspectionsController;
use App\Http\Controllers\Carrier\CarrierDashboardController;
use App\Http\Controllers\Admin\UserCarrierDocumentController;

// Rutas públicas para registro multi-paso
Route::middleware('guest')->group(function () {
    // Wizard multi-paso
    Route::prefix('wizard')->name('wizard.')->group(function () {
        // Paso 1: Información básica
        Route::get('/step1', [CarrierWizardController::class, 'showStep1'])->name('step1');
        Route::post('/step1', [CarrierWizardController::class, 'processStep1'])->name('step1.process');
        // Route removed: step1.success - now redirects directly to login after registration
        
        // AJAX endpoints for wizard
        Route::post('/check-uniqueness', [CarrierWizardController::class, 'checkUniqueness'])
            ->name('check.uniqueness')
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    });
    
    // Rutas de compatibilidad (redirigen al wizard)
    Route::get('/register', function () {
        return redirect()->route('carrier.wizard.step1');
    })->name('register');
});

// Ruta de confirmación de email
Route::get('/confirm/{token}', [CarrierRegistrationController::class, 'confirmEmail'])->name('confirm');

// Rutas que requieren autenticación
Route::middleware(['auth'])->group(function () {
    // Wizard multi-paso (pasos que requieren autenticación)
    Route::prefix('wizard')->name('wizard.')->group(function () {
        // Paso 2: Información de la empresa
        Route::get('/step2', [CarrierWizardController::class, 'showStep2'])->name('step2');
        Route::post('/step2', [CarrierWizardController::class, 'processStep2'])->name('step2.process');
        
        // Paso 3: Selección de membresía
        Route::get('/step3', [CarrierWizardController::class, 'showStep3'])->name('step3');
        Route::post('/step3', [CarrierWizardController::class, 'processStep3'])->name('step3.process');
        
        // Paso 4: Información bancaria
        Route::get('/step4', [CarrierWizardController::class, 'showStep4'])->name('step4');
        Route::post('/step4', [CarrierWizardController::class, 'processStep4'])->name('step4.process');
        
        // AJAX endpoints for authenticated wizard steps
        Route::get('/check-verification', [CarrierWizardController::class, 'checkVerification'])->name('check.verification');
    });
    
    // Rutas de compatibilidad
    Route::get('/complete-registration', function () {
        return redirect()->route('carrier.wizard.step2');
    })->name('complete_registration');
    
    // Rutas de estado y confirmación
    Route::get('/confirmation', [CarrierStatusController::class, 'showConfirmation'])->name('confirmation');
    Route::get('/pending', [CarrierStatusController::class, 'showPending'])->name('pending');
    Route::get('/pending-validation', [CarrierStatusController::class, 'pendingValidation'])->name('pending.validation');
    Route::get('/inactive', [CarrierStatusController::class, 'showInactive'])->name('inactive');
    Route::get('/payment-validated', [CarrierStatusController::class, 'showPaymentValidated'])->name('payment-validated');
    Route::post('/request-reactivation', [CarrierStatusController::class, 'requestReactivation'])->name('request.reactivation');
    Route::get('/status', [CarrierStatusController::class, 'getRegistrationStatus'])->name('status');
    Route::get('/support', [CarrierStatusController::class, 'showSupport'])->name('support');
    Route::post('/support', [CarrierStatusController::class, 'submitSupportRequest']);

    // Dashboard y otras rutas protegidas (con verificación de estado)
    Route::middleware(['check.user.status'])->group(function () {
        Route::get('/dashboard', [CarrierDashboardController::class, 'index'])->name('dashboard');

        // Rutas para documentos usando el nuevo controlador especializado
        Route::group([
            'prefix' => '{carrierSlug}'
        ], function () {
            Route::get('/documents', [CarrierDocumentController::class, 'index'])->name('documents.index');
            Route::post('/documents/upload/{documentType}', [CarrierDocumentController::class, 'upload'])->name('documents.upload');
            Route::post('/documents/toggle-default/{documentType}', [CarrierDocumentController::class, 'toggleDefaultDocument'])
                ->name('documents.toggle-default');
            Route::post('/documents/accept-default/{documentType}', [CarrierDocumentController::class, 'toggleDefaultDocument'])
                ->name('documents.accept-default');
            Route::delete('/documents/{documentType}', [CarrierDocumentController::class, 'deleteDocument'])
                ->name('documents.delete');
            Route::get('/documents/progress', [CarrierDocumentController::class, 'getDocumentProgress'])
                ->name('documents.progress');
            Route::get('/documents/{document}/view', [CarrierDocumentController::class, 'viewDocument'])
                ->name('documents.view');
            Route::get('/documents/skip', [CarrierDocumentController::class, 'skipDocuments'])
                ->name('documents.skip');
        });

        // La vista principal del perfil
        Route::get('/profile', [CarrierProfileController::class, 'index'])->name('profile');
        // Vista de edición del perfil
        Route::get('/profile/edit', [CarrierProfileController::class, 'edit'])->name('profile.edit');
        // Actualizar perfil
        Route::put('/profile/update', [CarrierProfileController::class, 'update'])->name('profile.update');

        // Rutas para gestión de conductores
        // Gestión de conductores (ruta original)
        Route::resource('drivers', CarrierDriverController::class);
        
        // Nuevas rutas para gestión de conductores
        Route::prefix('carrier-driver-management')->name('driver-management.')->group(function () {
            Route::get('/', [CarrierDriverManagementController::class, 'index'])->name('index');
            Route::get('/create', [CarrierDriverManagementController::class, 'create'])->name('create');
            Route::post('/', [CarrierDriverManagementController::class, 'store'])->name('store');
            Route::get('/{driver}', [CarrierDriverManagementController::class, 'show'])->name('show');
            Route::get('/{driver}/edit', [CarrierDriverManagementController::class, 'edit'])->name('edit');
            Route::put('/{driver}', [CarrierDriverManagementController::class, 'update'])->name('update');
            Route::delete('/{driver}', [CarrierDriverManagementController::class, 'destroy'])->name('destroy');
            Route::delete('/{driver}/photo', [CarrierDriverManagementController::class, 'deletePhoto'])->name('delete-photo');
        });
        
        // Rutas para accidentes de conductores
        Route::prefix('carrier-driver-accidents')->name('drivers.accidents.')->group(function () {
            Route::get('/', [CarrierDriverAccidentsController::class, 'index'])->name('index');
            Route::get('/create', [CarrierDriverAccidentsController::class, 'create'])->name('create');
            Route::post('/', [CarrierDriverAccidentsController::class, 'store'])->name('store');
            Route::get('/{accident}/edit', [CarrierDriverAccidentsController::class, 'edit'])->name('edit');
            Route::put('/{accident}', [CarrierDriverAccidentsController::class, 'update'])->name('update');
            Route::delete('/{accident}', [CarrierDriverAccidentsController::class, 'destroy'])->name('destroy');
            Route::get('/driver/{driver}', [CarrierDriverAccidentsController::class, 'driverHistory'])->name('driver_history');
        });
        
        // Rutas para pruebas de conductores
        Route::prefix('carrier-driver-testings')->name('drivers.testings.')->group(function () {
            Route::get('/', [CarrierDriverTestingsController::class, 'index'])->name('index');
            Route::get('/create', [CarrierDriverTestingsController::class, 'create'])->name('create');
            Route::post('/', [CarrierDriverTestingsController::class, 'store'])->name('store');
            Route::get('/{testing}/edit', [CarrierDriverTestingsController::class, 'edit'])->name('edit');
            Route::put('/{testing}', [CarrierDriverTestingsController::class, 'update'])->name('update');
            Route::delete('/{testing}', [CarrierDriverTestingsController::class, 'destroy'])->name('destroy');
            Route::get('/driver/{driver}', [CarrierDriverTestingsController::class, 'driverHistory'])->name('driver_history');
        });
        
        // Rutas para inspecciones de conductores
        Route::prefix('carrier-driver-inspections')->name('drivers.inspections.')->group(function () {
            Route::get('/', [CarrierDriverInspectionsController::class, 'index'])->name('index');
            Route::get('/create', [CarrierDriverInspectionsController::class, 'create'])->name('create');
            Route::post('/', [CarrierDriverInspectionsController::class, 'store'])->name('store');
            Route::get('/{inspection}/edit', [CarrierDriverInspectionsController::class, 'edit'])->name('edit');
            Route::put('/{inspection}', [CarrierDriverInspectionsController::class, 'update'])->name('update');
            Route::delete('/{inspection}', [CarrierDriverInspectionsController::class, 'destroy'])->name('destroy');
            Route::get('/driver/{driver}', [CarrierDriverInspectionsController::class, 'driverHistory'])->name('driver_history');
            Route::delete('/{inspection}/files/{mediaId}', [CarrierDriverInspectionsController::class, 'deleteFile'])->name('delete-file');
            Route::get('/{inspection}/files', [CarrierDriverInspectionsController::class, 'getFiles'])->name('files');
            Route::get('/driver/{driver}/vehicles', [CarrierDriverInspectionsController::class, 'getVehiclesByDriver'])->name('vehicles.by.driver');
        });
        
        // Rutas para gestión de vehículos
        Route::prefix('vehicles')->name('vehicles.')->group(function () {
            Route::get('/', [CarrierVehicleController::class, 'index'])->name('index');
            Route::get('/create', [CarrierVehicleController::class, 'create'])->name('create');
            Route::post('/', [CarrierVehicleController::class, 'store'])->name('store');
            Route::get('/{vehicle}', [CarrierVehicleController::class, 'show'])->name('show');
            Route::get('/{vehicle}/edit', [CarrierVehicleController::class, 'edit'])->name('edit');
            Route::put('/{vehicle}', [CarrierVehicleController::class, 'update'])->name('update');
            Route::delete('/{vehicle}', [CarrierVehicleController::class, 'destroy'])->name('destroy');
            
            // Rutas para documentos de vehículos
            Route::get('/{vehicle}/documents', [CarrierVehicleController::class, 'documents'])->name('documents');
            Route::get('/{vehicle}/documents/create', [CarrierVehicleController::class, 'createDocument'])->name('documents.create');
            Route::post('/{vehicle}/documents', [CarrierVehicleController::class, 'storeDocument'])->name('documents.store');
            Route::get('/{vehicle}/documents/{document}/edit', [CarrierVehicleController::class, 'editDocument'])->name('documents.edit');
            Route::put('/{vehicle}/documents/{document}', [CarrierVehicleController::class, 'updateDocument'])->name('documents.update');
            Route::delete('/{vehicle}/documents/{document}', [CarrierVehicleController::class, 'destroyDocument'])->name('documents.destroy');
            Route::get('/{vehicle}/documents/{document}/download', [CarrierVehicleController::class, 'downloadDocument'])->name('documents.download');
            
            // Rutas para items de servicio de vehículos
            Route::get('/{vehicle}/service-items', [CarrierVehicleController::class, 'serviceItems'])->name('service-items');
            Route::get('/{vehicle}/service-items/create', [CarrierVehicleController::class, 'createServiceItem'])->name('service-items.create');
            Route::post('/{vehicle}/service-items', [CarrierVehicleController::class, 'storeServiceItem'])->name('service-items.store');
            Route::get('/{vehicle}/service-items/{serviceItem}/edit', [CarrierVehicleController::class, 'editServiceItem'])->name('service-items.edit');
            Route::put('/{vehicle}/service-items/{serviceItem}', [CarrierVehicleController::class, 'updateServiceItem'])->name('service-items.update');
            Route::delete('/{vehicle}/service-items/{serviceItem}', [CarrierVehicleController::class, 'destroyServiceItem'])->name('service-items.destroy');
            Route::put('/{vehicle}/service-items/{serviceItem}/toggle-status', [CarrierVehicleController::class, 'toggleServiceItemStatus'])->name('service-items.toggle-status');
        });
    });
});
