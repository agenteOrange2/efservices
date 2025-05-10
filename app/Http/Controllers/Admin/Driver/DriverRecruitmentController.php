<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\UserDriverDetail;
use Illuminate\Http\Request;

class DriverRecruitmentController extends Controller
{
    /**
     * Mostrar el listado de solicitudes de conductores
     */
    public function index()
    {
        return view('admin.drivers.recruitment.index');
    }

    /**
     * Mostrar la revisión detallada de una solicitud específica
     */
    public function show($driverId)
    {
        $driver = UserDriverDetail::findOrFail($driverId);
        return view('admin.drivers.recruitment.show', [
            'driverId' => $driverId,
            'driver' => $driver
        ]);
    }
}