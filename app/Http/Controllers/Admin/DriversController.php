<?php

namespace App\Http\Controllers\Admin;

use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Http\Controllers\Controller;
use App\Services\DriverService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriversController extends Controller
{
    protected $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->driverService = $driverService;
    }
    public function index()
    {
        try {
            // Obtener conductores con paginación usando el servicio
            $drivers = $this->driverService->getAllDrivers(10);
            
            // Obtener estadísticas usando el servicio
            $stats = $this->driverService->getDriverStatistics();

            return view('admin.drivers.index', compact('drivers', 'stats'));
        } catch (\Exception $e) {
            Log::error('Error loading drivers index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error loading drivers');
        }
    }

    public function toggleStatus(UserDriverDetail $driver)
    {
        try {
            // Cambiar estado usando el servicio
            $newStatus = $driver->status === UserDriverDetail::STATUS_ACTIVE 
                ? UserDriverDetail::STATUS_INACTIVE 
                : UserDriverDetail::STATUS_ACTIVE;
            
            $this->driverService->updateDriverStatus($driver->id, $newStatus);

            return back()->with('success', 'Driver status updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating driver status', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error updating driver status');
        }
    }
}