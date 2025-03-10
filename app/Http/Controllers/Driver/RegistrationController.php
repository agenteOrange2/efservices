<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Driver\DriverApplication;

class RegistrationController extends Controller
{
    /**
     * Mostrar la vista para continuar el registro
     */
    public function continue($step = null)
    {
        $user = auth()->user();
        $driverDetail = $user->driverDetails;
        
        if (!$driverDetail) {
            return redirect()->route('login')
                ->with('error', 'Driver details not found.');
        }
        
        // Si la aplicación ya está completa, redirigir al dashboard
        if ($driverDetail->application_completed) {
            return redirect()->route('driver.dashboard');
        }
        
        // Si no se proporciona un paso, usar el paso actual del driver
        if ($step === null) {
            $step = $driverDetail->current_step ?? 1;
        }
        
        return view('driver.registration.continue', [
            'step' => $step,
            'driverDetail' => $driverDetail
        ]);
    }
    
    /**
     * Marcar la aplicación como completa y enviarla para revisión
     */
    public function complete(Request $request)
    {
        $user = auth()->user();
        $driverDetail = $user->driverDetails;
        
        if (!$driverDetail) {
            return redirect()->route('login')
                ->with('error', 'Driver details not found.');
        }
        
        // Actualizar el estado de la aplicación
        $driverDetail->update([
            'application_completed' => true,
        ]);
        
        // Actualizar el estado de DriverApplication
        $user->driverApplication()->updateOrCreate(
            ['user_id' => $user->id],
            ['status' => DriverApplication::STATUS_PENDING]
        );
        
        return redirect()->route('driver.pending')
            ->with('success', 'Your application has been submitted for review.');
    }
}