<?php

use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TempUploadController;
use App\Http\Controllers\Admin\UserDriverController;
use App\Http\Controllers\Api\UserDriverApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



// En routes/api.php
Route::get('/get-drivers-by-carrier-id/{carrierId}', function ($carrierId) {
    $drivers = \App\Models\UserDriverDetail::where('carrier_id', $carrierId)
        ->with('user')
        ->get(['id', 'user_id', 'last_name']);
    return response()->json($drivers);
});