<?php

namespace App\Http\Controllers\Admin;

use App\Fakers\Countries;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    
    public function dashboard(): View
    {
        return view('admin/dashboard', [
            'countries' => Countries::fakeCountries()
        ]);
    }
}
