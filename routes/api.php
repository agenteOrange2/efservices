<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TempUploadController;
use App\Http\Controllers\Admin\UserDriverController;
use App\Http\Controllers\Api\UserDriverApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// routes/api.php
// routes/api.php
Route::prefix('drivers')->name('api.drivers.')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/{carrier}', [UserDriverApiController::class, 'index']);
    Route::get('/{carrier}/{userDriverDetail}', [UserDriverApiController::class, 'show']);
    Route::post('/{carrier}', [UserDriverApiController::class, 'store']);
    Route::delete('/{carrier}/{userDriverDetail}', [UserDriverApiController::class, 'destroy']);
    Route::delete('/{carrier}/{userDriverDetail}/photo', [UserDriverApiController::class, 'deletePhoto']);
    
    // Endpoints para cada paso - make sure these are working correctly
    Route::post('/{carrier}/{userDriverDetail}/general', [UserDriverApiController::class, 'updateGeneral']);
    Route::post('/{carrier}/{userDriverDetail}/licenses', [UserDriverApiController::class, 'updateLicenses']);
    Route::post('/{carrier}/{userDriverDetail}/medical', [UserDriverApiController::class, 'updateMedical']);
    Route::post('/{carrier}/{userDriverDetail}/training', [UserDriverApiController::class, 'updateTraining']);
    Route::post('/{carrier}/{userDriverDetail}/traffic', [UserDriverApiController::class, 'updateTraffic']);
    Route::post('/{carrier}/{userDriverDetail}/accident', [UserDriverApiController::class, 'updateAccident']);
    
    // Autosave
    Route::post('/{carrier}/autosave', [UserDriverApiController::class, 'autosave']);
});

Route::post('/temp-uploads', [TempUploadController::class, 'store'])
    ->middleware(['web', 'auth']); // A