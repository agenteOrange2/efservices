<?php

namespace App\Http\Controllers\Admin;

use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Http\Controllers\Controller;
use App\Services\Admin\DriverStepService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        try {
            Log::info('UserDriverController::edit - Método iniciado', [
                'carrier_id' => $carrier->id,
                'driver_id' => $userDriverDetail->id,
                'user_id' => $userDriverDetail->user_id ?? 'null'
            ]);

            // Ensure the driver belongs to the carrier
            Log::info('UserDriverController::edit - Validando ownership del driver', [
                'carrier_id' => $carrier->id,
                'carrier_id_type' => gettype($carrier->id),
                'driver_carrier_id' => $userDriverDetail->carrier_id,
                'driver_carrier_id_type' => gettype($userDriverDetail->carrier_id),
                'driver_id' => $userDriverDetail->id,
                'comparison_result' => ($userDriverDetail->carrier_id === $carrier->id)
            ]);
            
            // Fixed: Using loose comparison (==) instead of strict (===) to handle type differences
            if ($userDriverDetail->carrier_id != $carrier->id) {
                Log::error('UserDriverController::edit - Driver no pertenece al carrier', [
                    'carrier_id' => $carrier->id,
                    'driver_carrier_id' => $userDriverDetail->carrier_id,
                    'driver_id' => $userDriverDetail->id,
                    'strict_comparison' => ($userDriverDetail->carrier_id === $carrier->id),
                    'loose_comparison' => ($userDriverDetail->carrier_id == $carrier->id),
                    'fix_applied' => 'Changed to loose comparison to handle type differences'
                ]);
                
                return redirect()->route('admin.carrier.user_drivers.index', $carrier)
                    ->with('error', 'Driver no pertenece a este carrier.');
            }

            Log::info('UserDriverController::edit - Validación de ownership completada exitosamente', [
                'carrier_id' => $carrier->id,
                'driver_carrier_id' => $userDriverDetail->carrier_id,
                'fix_applied' => 'Using loose comparison (==) to handle type differences between integer and string'
            ]);

            Log::info('UserDriverController::edit - Cargando relaciones del driver');
            // Load necessary relationships for the edit form
            $userDriverDetail->load([
                'user',
                /*
                'addresses',
                'licenses',
                'application',
                'accidents',
                'trafficConvictions',
                'medicalQualification',
                'trainingSchools',
                'relatedEmployments',
                'employmentCompanies',
                'criminalHistory',
                'employmentHistory'
                */
            ]);

            Log::info('UserDriverController::edit - Relaciones cargadas, retornando vista', [
                'view' => 'admin.user_driver.edit',
                'carrier_name' => $carrier->name ?? 'N/A',
                'driver_user_name' => $userDriverDetail->user->name ?? 'N/A'
            ]);

            return view('admin.user_driver.edit', compact('carrier', 'userDriverDetail'));
            
        } catch (\Exception $e) {
            Log::error('UserDriverController::edit - Error en el método', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'carrier_id' => $carrier->id ?? 'null',
                'driver_id' => $userDriverDetail->id ?? 'null'
            ]);
            
            throw $e;
        }
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