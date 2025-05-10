<?php

namespace App\Http\Controllers\Admin;

use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DriversController extends Controller
{
    public function index()
    {
        $drivers = UserDriverDetail::with(['user', 'carrier', 'assignedVehicle'])
            ->select('user_driver_details.*')
            ->addSelect([
                'total_trips' => DB::table('trips')
                    ->whereColumn('driver_id', 'user_driver_details.id')
                    ->selectRaw('COUNT(*)')
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_drivers' => UserDriverDetail::count(),
            'active_drivers' => UserDriverDetail::where('status', UserDriverDetail::STATUS_ACTIVE)->count(),
            'drivers_without_vehicle' => UserDriverDetail::whereNull('assigned_vehicle_id')->count(),
            'carriers_with_drivers' => Carrier::has('userDrivers')->count(),
        ];

        return view('admin.drivers.index', compact('drivers', 'stats'));
    }

    public function toggleStatus(UserDriverDetail $driver)
    {
        $driver->status = $driver->status === UserDriverDetail::STATUS_ACTIVE 
            ? UserDriverDetail::STATUS_INACTIVE 
            : UserDriverDetail::STATUS_ACTIVE;
        $driver->save();

        return back()->with('success', 'Driver status updated successfully');
    }
}