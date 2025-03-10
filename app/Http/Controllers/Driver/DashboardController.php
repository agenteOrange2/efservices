<?php

namespace App\Http\Controllers\Driver;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{


    
    public function index()
    {
        $driver = Auth::user()->driverDetails;
        $carrier = $driver->carrier;

        // $stats = [
        //     'total_trips' => $driver->trips()->count(),
        //     'completed_trips' => $driver->trips()->where('status', 'completed')->count(),
        //     'pending_documents' => $driver->pendingDocuments()->count(),
        //     'vehicle' => $driver->assignedVehicle,
        // ];

        return view('driver.dashboard');
    }
}
