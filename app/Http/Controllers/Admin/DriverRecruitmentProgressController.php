<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverRecruitmentVerification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DriverRecruitmentProgressController extends Controller
{
    /**
     * Obtener el porcentaje de progreso para un conductor específico
     */
    public function getProgress($applicationId)
    {
        $verification = DriverRecruitmentVerification::where('driver_application_id', $applicationId)
            ->orderBy('verified_at', 'desc')
            ->first();

        $percentage = 0;

        if ($verification && !empty($verification->verification_items)) {
            $totalItems = count($verification->verification_items);
            $checkedItems = 0;
            
            foreach ($verification->verification_items as $key => $value) {
                if (isset($value['checked']) && $value['checked'] === true) {
                    $checkedItems++;
                }
            }
            
            $percentage = $totalItems > 0 ? round(($checkedItems / $totalItems) * 100) : 0;
        }
        
        // Si está aprobado, mostrar 100%
        $application = DriverApplication::find($applicationId);
        if ($application && $application->status === 'approved') {
            $percentage = 100;
        }

        return response()->json([
            'percentage' => $percentage
        ]);
    }
}
