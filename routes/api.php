<?php

use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TempUploadController;
use App\Http\Controllers\Admin\UserDriverController;
use App\Http\Controllers\Api\UserDriverApiController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\DocumentController;

// Ruta pública para eliminar documentos de manera segura (solo requiere CSRF)
// Esta ruta es necesaria para el funcionamiento del modal de eliminación de documentos
Route::post('documents/delete', [DocumentController::class, 'safeDeletePost'])->name('api.documents.delete.post');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Ruta para eliminar documentos de manera segura (evitando la eliminación en cascada)
    Route::delete('documents/{mediaId}', [DocumentController::class, 'safeDelete'])->name('api.documents.delete');
    
    // Traffic Convictions API
    Route::put('/traffic/convictions/{conviction}', [\App\Http\Controllers\Admin\Driver\TrafficConvictionsController::class, 'apiUpdate']);
    
    // Driver Testing API Routes
    Route::prefix('drivers/testing')->name('api.drivers.testing.')->group(function () {
        Route::get('search-carriers', [\App\Http\Controllers\Admin\Driver\DriverTestingController::class, 'searchCarriers'])->name('search-carriers');
        Route::get('get-drivers/{carrier}', [\App\Http\Controllers\Admin\Driver\DriverTestingController::class, 'getDriversByCarrier'])->name('get-drivers');
        Route::get('by-carrier/{carrier}', [\App\Http\Controllers\Admin\Driver\DriverTestingController::class, 'getDriversByCarrier'])->name('by-carrier');
        Route::get('driver-details/{driverDetail}', [\App\Http\Controllers\Admin\Driver\DriverTestingController::class, 'getDriverDetails'])->name('driver-details');
    });
});

// En routes/api.php
Route::get('/get-drivers-by-carrier-id/{carrierId}', function ($carrierId) {
    $drivers = \App\Models\UserDriverDetail::where('carrier_id', $carrierId)
        ->with('user')
        ->get(['id', 'user_id', 'last_name']);
    return response()->json($drivers);
});

// Ruta para obtener conductores activos por carrier
Route::get('/active-drivers-by-carrier/{carrierId}', function ($carrierId) {
    $drivers = \App\Models\UserDriverDetail::where('carrier_id', $carrierId)
        ->whereHas('user', function($query) {
            $query->where('status', 1);
        })
        ->with('user')
        ->get();
    return response()->json($drivers);
});

// Ruta para obtener todos los carriers activos
Route::get('/active-carriers', function () {
    $carriers = \App\Models\Carrier::where('status', 'active')
        ->orderBy('name')
        ->get(['id', 'name', 'dot_number']);
    return response()->json($carriers);
});

// Rutas API para gestión de documentos (sin autenticación ni CSRF para facilitar desarrollo)
Route::prefix('documents')->group(function () {
    // Ruta para carga temporal de archivos
    Route::post('/upload', [UploadController::class, 'upload']);
    
    // Rutas para guardar documentos permanentes en diferentes colecciones
    Route::post('/store', [UploadController::class, 'storeDocument']);
    
    // Ruta para eliminar documentos
    Route::delete('/{id}', [UploadController::class, 'deleteDocument']);
    
    // Ruta para obtener documentos de un modelo
    Route::get('/model/{type}/{id}', [UploadController::class, 'getDocuments']);
});