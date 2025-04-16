<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;

class AccidentsController extends Controller
{
    // Vista para todos los accidentes
    public function index()
    {        
        return view('admin.drivers.accidents.index');
    }
    
    // Vista para el historial de accidentes de un conductor específico
    public function driverHistory(UserDriverDetail $driver)
    {
        return view('admin.drivers.accidents.driver_history', compact('driver'));
    }
}