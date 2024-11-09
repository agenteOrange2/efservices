<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Log;


Route::get('/', function () {
    return view ('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/admin', function () {
        return view('pages.dashboard-overview-4');
    });
});


