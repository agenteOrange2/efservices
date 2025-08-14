<?php

namespace App\Http\Controllers\Admin\Driver;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpdateApplicationDatesController extends Controller
{
    /**
     * Actualiza las fechas de completed_at para aplicaciones aprobadas que tienen este campo en null
     */
    public function updateCompletedDates()
    {
        // Obtener todas las aplicaciones aprobadas sin fecha de completado
        $applications = DriverApplication::where('status', 'approved')
            ->whereNull('completed_at')
            ->get();
        
        $count = $applications->count();
        
        if ($count > 0) {
            foreach ($applications as $application) {
                // Usar la última fecha de actualización como fecha de completado
                $application->completed_at = $application->updated_at;
                $application->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => "Se actualizaron {$count} aplicaciones aprobadas con su fecha de completado."
            ]);
        } else {
            return response()->json([
                'success' => true,
                'message' => "No se encontraron aplicaciones aprobadas sin fecha de completado."
            ]);
        }
    }
}
