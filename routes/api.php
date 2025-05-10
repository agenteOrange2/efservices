<?php

use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TempUploadController;
use App\Http\Controllers\Admin\UserDriverController;
use App\Http\Controllers\Api\UserDriverApiController;
use App\Http\Controllers\Api\UploadController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Traffic Convictions API
    Route::put('/traffic/convictions/{conviction}', [\App\Http\Controllers\Admin\Driver\TrafficConvictionsController::class, 'apiUpdate']);
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

// Ruta API para carga de archivos (sin autenticación ni CSRF)
Route::post('/upload', [UploadController::class, 'upload']);