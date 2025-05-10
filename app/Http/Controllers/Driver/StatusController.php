<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Driver\DriverApplication;

class StatusController extends Controller
{
    /**
     * Mostrar página para aplicación pendiente
     */
    public function pending()
    {
        $user = auth()->user();
        $application = $user->driverApplication;
        
        // Si la aplicación no está pendiente, redirigir
        if (!$application || $application->status !== DriverApplication::STATUS_PENDING) {
            return redirect()->route('driver.dashboard');
        }
        
        return view('driver.status.pending');
    }
    
    /**
     * Mostrar página para aplicación rechazada
     */
    public function rejected()
    {
        $user = auth()->user();
        $application = $user->driverApplication;
        
        // Si la aplicación no está rechazada, redirigir
        if (!$application || $application->status !== DriverApplication::STATUS_REJECTED) {
            return redirect()->route('driver.dashboard');
        }
        
        return view('driver.status.rejected');
    }
    
    /**
     * Mostrar página para documentos pendientes
     */
    public function documentsPending()
    {
        return view('driver.status.documents-pending');
    }
}