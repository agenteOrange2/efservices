<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Log;


Route::get('/', function () {
    return view('welcome');
});

/*
Route::group(['middleware' => ['auth:sanctum', config('jetstream.auth_session'), 'verified'],
'prefix' => 'dashboard'], function(){
    Route::get('/', function(){
        return view('dashboard');
    })->name('dashboard');

});
*/