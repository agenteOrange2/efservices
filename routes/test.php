<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Driver\Steps\ApplicationStep;

Route::get('/test-driver-form', function () {
    return view('test-driver-form');
});