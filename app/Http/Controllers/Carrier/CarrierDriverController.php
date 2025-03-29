<?php

namespace App\Http\Controllers\Carrier;

use App\Models\User;
use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CarrierDriverController extends Controller
{
    /**
     * Muestra la página principal de gestión de conductores.
     */
    public function index()
    {
        // Verifica que el usuario autenticado sea un carrier
        if (!Auth::user()->hasRole('user_carrier')) {
            return redirect()->route('login');
        }
        
        return view('carrier.driver.index');
    }
    
    /**
     * Muestra el formulario para crear un nuevo conductor.
     */
    public function create()
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar si se ha alcanzado el límite de conductores
        $maxDrivers = $carrier->membership->max_drivers ?? 1;
        $currentDriversCount = UserDriverDetail::where('carrier_id', $carrier->id)->count();
        
        if ($currentDriversCount >= $maxDrivers) {
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'Has alcanzado el límite máximo de conductores para tu plan. Actualiza tu membresía para añadir más conductores.');
        }

        // Pasar el carrier y la bandera de "isIndependent" en false, ya que el conductor 
        // está siendo creado por el carrier, no es un registro independiente
        return view('carrier.driver.create', [
            'carrier' => $carrier,
            'isIndependent' => false
        ]);
    }
    
    /**
     * Muestra el formulario para la gestión por pasos de un conductor.
     */
    public function edit(UserDriverDetail $driver)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
        return view('carrier.driver.edit', [
            'driver' => $driver,
            'carrier' => $carrier,
            'driverId' => $driver->id
        ]);
    }
    
    /**
     * Muestra los detalles de un conductor.
     */
    public function show(UserDriverDetail $driver)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
        return view('carrier.driver.show', compact('driver'));
    }
    
    /**
     * Elimina un conductor.
     */
    public function destroy(UserDriverDetail $driver)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
        try {
            $user = $driver->user;
            
            if ($user) {
                // Eliminar foto de perfil
                $driver->clearMediaCollection('profile_photo_driver');
                // Eliminar otras colecciones de medios relacionadas con el driver
                $driver->licenses()->get()->each(function($license) {
                    $license->clearMediaCollection('license_front');
                    $license->clearMediaCollection('license_back');
                });
                
                if ($driver->medicalQualification) {
                    $driver->medicalQualification->clearMediaCollection('medical_card');
                }
                
                $driver->trainingSchools()->get()->each(function($school) {
                    $school->clearMediaCollection('school_certificates');
                });
                
                if ($driver->certification) {
                    $driver->certification->clearMediaCollection('signature');
                }
                
                // Eliminar el usuario (que también eliminará el UserDriverDetail por cascada)
                $user->delete();
            }
            
            return redirect()->route('carrier.drivers.index')
                ->with('success', 'Conductor eliminado exitosamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar conductor', [
                'error' => $e->getMessage(),
                'driver_id' => $driver->id
            ]);
            
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'Error al eliminar conductor: ' . $e->getMessage());
        }
    }
}