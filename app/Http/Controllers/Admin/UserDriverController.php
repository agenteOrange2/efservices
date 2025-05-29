<?php

namespace App\Http\Controllers\Admin;

use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Http\Controllers\Controller;
use App\Services\Admin\DriverStepService;
use Illuminate\Support\Facades\Log;

class UserDriverController extends Controller
{
    protected $driverStepService;
    
    public function __construct(DriverStepService $driverStepService)
    {
        $this->driverStepService = $driverStepService;
    }
    
    /**
     * Display a listing of drivers for a carrier.
     */
    public function index(Carrier $carrier)
    {
        $maxDrivers = $carrier->membership->max_drivers ?? 1;
        $currentDrivers = UserDriverDetail::where('carrier_id', $carrier->id)->count();
        $exceededLimit = $currentDrivers >= $maxDrivers;
        
        return view('admin.user_driver.index', [
            'carrier' => $carrier,
            'userDrivers' => UserDriverDetail::where('carrier_id', $carrier->id)
                ->with(['user', 'primaryLicense', 'assignedVehicle', 'vehicles'])
                ->paginate(10),
            'maxDrivers' => $maxDrivers,
            'currentDrivers' => $currentDrivers,
            'exceeded_limit' => $exceededLimit,
        ]);
    }
    
    /**
     * Show the form for creating a new driver using new architecture.
     */
    public function create(Carrier $carrier)
    {
        // Verify driver limit for carrier
        $maxDrivers = $carrier->membership->max_drivers ?? 1;
        $currentDriversCount = UserDriverDetail::where('carrier_id', $carrier->id)->count();
        
        if ($currentDriversCount >= $maxDrivers) {
            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('exceeded_limit', true)
                ->with('error', 'No puedes agregar más conductores a este carrier. Actualiza tu plan o contacta al administrador.');
        }
        
        // Return the new component-based registration form
        return view('admin.user_driver.create', [
            'carrier' => $carrier
        ]);
    }
    
    /**
     * Show the form for editing an existing driver using new architecture.
     */
    public function edit(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        // Agregar logs detallados para diagnosticar problemas
        \Illuminate\Support\Facades\Log::info('UserDriverController@edit - Inicio', [
            'carrier_id' => $carrier->id,
            'carrier_slug' => $carrier->slug,
            'driver_id' => $userDriverDetail->id,
            'user_id' => request()->user() ? request()->user()->id : null,
            'url' => request()->fullUrl()
        ]);
        
        // Verify that the driver belongs to the carrier
        // Convertir ambos IDs a enteros para asegurar una comparación correcta
        $driverCarrierId = (int)$userDriverDetail->carrier_id;
        $requestedCarrierId = (int)$carrier->id;
        
        \Illuminate\Support\Facades\Log::info('UserDriverController@edit - Comparación de IDs', [
            'driver_carrier_id_raw' => $userDriverDetail->carrier_id,
            'driver_carrier_id_int' => $driverCarrierId,
            'requested_carrier_id_raw' => $carrier->id,
            'requested_carrier_id_int' => $requestedCarrierId,
            'son_iguales' => ($driverCarrierId === $requestedCarrierId) ? 'sí' : 'no'
        ]);
        
        if ($driverCarrierId !== $requestedCarrierId) {
            \Illuminate\Support\Facades\Log::warning('UserDriverController@edit - Redirección: conductor no pertenece al carrier', [
                'driver_carrier_id' => $driverCarrierId,
                'requested_carrier_id' => $requestedCarrierId
            ]);
            
            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('error', 'El conductor no pertenece a este transportista');
        }
        
        // Log antes de retornar la vista
        \Illuminate\Support\Facades\Log::info('UserDriverController@edit - Cargando vista', [
            'view' => 'admin.user_driver.edit',
            'carrier_id' => $carrier->id,
            'driver_id' => $userDriverDetail->id
        ]);
        
        // Return the new component-based edit form
        return view('admin.user_driver.edit', [
            'carrier' => $carrier,
            'userDriverDetail' => $userDriverDetail
        ]);
    }
    
    /**
     * Remove the specified driver.
     */
    public function destroy(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        try {
            $user = $userDriverDetail->user;
            if ($user) {
                // Remove profile photo
                $userDriverDetail->clearMediaCollection('profile_photo_driver');
                $user->delete(); // This will also delete the UserDriverDetail due to cascade
            }
            
            Log::info('Driver deleted successfully', [
                'carrier_id' => $carrier->id,
                'user_driver_detail_id' => $userDriverDetail->id
            ]);
            
            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('success', 'Driver deleted successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error deleting driver', [
                'error' => $e->getMessage(),
                'carrier_id' => $carrier->id,
                'user_driver_detail_id' => $userDriverDetail->id
            ]);
            
            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->withErrors('Error deleting driver.');
        }
    }
    
    /**
     * Delete the profile photo of a driver.
     */
    public function deletePhoto(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        try {
            if ($userDriverDetail->hasMedia('profile_photo_driver')) {
                $userDriverDetail->clearMediaCollection('profile_photo_driver');
                
                Log::info('Driver photo deleted successfully.', [
                    'user_driver_detail_id' => $userDriverDetail->id,
                ]);
                
                return response()->json([
                    'message' => 'Photo deleted successfully.',
                    'defaultPhotoUrl' => asset('build/default_profile.png'),
                ]);
            }
            
            return response()->json(['message' => 'No photo to delete.'], 404);
            
        } catch (\Exception $e) {
            Log::error('Error deleting driver photo.', [
                'error' => $e->getMessage(),
                'user_driver_detail_id' => $userDriverDetail->id,
            ]);
            
            return response()->json(['message' => 'Error deleting photo: ' . $e->getMessage()], 500);
        }
    }
}