<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Carrier\DocumentController;
use App\Http\Controllers\Carrier\CarrierDriverController;
use App\Http\Controllers\Carrier\CarrierProfileController;
use App\Http\Controllers\Carrier\CarrierDriverManagementController;
use App\Http\Controllers\Carrier\CarrierVehicleController;
use App\Http\Controllers\Carrier\CarrierDriverAccidentsController;
use App\Http\Controllers\Carrier\CarrierDriverTestingsController;
use App\Http\Controllers\Carrier\CarrierDriverInspectionsController;
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
        $carrier = Auth::user()->carrierDetails->carrier;
        return view('carrier.dashboard', compact('carrier'));
    })->name('dashboard');

    Route::get('/complete-registration', [CustomLoginController::class, 'showCompleteRegistrationForm'])
        ->name('complete_registration');
    Route::post('/complete-registration', [CustomLoginController::class, 'completeRegistration']);

    Route::get('/confirmation', function () {
        return view('auth.user_carrier.confirmation');
    })->name('confirmation');

    Route::get('/pending', function () {
        return view('auth.user_carrier.pending_usercarrier');
    })->name('pending'); // Cambio de nombre de ruta

    // Rutas para documentos
    Route::group([
        'prefix' => '{carrier}',  // Quitar :slug de aquí
        'middleware' => ['auth']
    ], function () {
        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/documents/upload/{documentType}', [DocumentController::class, 'upload'])->name('documents.upload');
        Route::post('/documents/skip', [DocumentController::class, 'skipDocuments'])->name('documents.skip');
        Route::post('/documents/complete', [DocumentController::class, 'complete'])->name('documents.complete');
        Route::post('/documents/use-default', [DocumentController::class, 'toggleDefaultDocument'])
            ->name('documents.use-default');
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
    Route::prefix('driver-management')->name('drivers.')->group(function () {
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
    Route::prefix('driver-accidents')->name('drivers.accidents.')->group(function () {
        Route::get('/', [CarrierDriverAccidentsController::class, 'index'])->name('index');
        Route::get('/create', [CarrierDriverAccidentsController::class, 'create'])->name('create');
        Route::post('/', [CarrierDriverAccidentsController::class, 'store'])->name('store');
        Route::get('/{accident}/edit', [CarrierDriverAccidentsController::class, 'edit'])->name('edit');
        Route::put('/{accident}', [CarrierDriverAccidentsController::class, 'update'])->name('update');
        Route::delete('/{accident}', [CarrierDriverAccidentsController::class, 'destroy'])->name('destroy');
        Route::get('/driver/{driver}', [CarrierDriverAccidentsController::class, 'driverHistory'])->name('driver_history');
    });
    
    // Rutas para pruebas de conductores
    Route::prefix('driver-testings')->name('drivers.testings.')->group(function () {
        Route::get('/', [CarrierDriverTestingsController::class, 'index'])->name('index');
        Route::get('/create', [CarrierDriverTestingsController::class, 'create'])->name('create');
        Route::post('/', [CarrierDriverTestingsController::class, 'store'])->name('store');
        Route::get('/{testing}/edit', [CarrierDriverTestingsController::class, 'edit'])->name('edit');
        Route::put('/{testing}', [CarrierDriverTestingsController::class, 'update'])->name('update');
        Route::delete('/{testing}', [CarrierDriverTestingsController::class, 'destroy'])->name('destroy');
        Route::get('/driver/{driver}', [CarrierDriverTestingsController::class, 'driverHistory'])->name('driver_history');
    });
    
    // Rutas para inspecciones de conductores
    Route::prefix('driver-inspections')->name('drivers.inspections.')->group(function () {
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
