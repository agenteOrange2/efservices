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
        // Ensure the driver belongs to the carrier
        if ($userDriverDetail->carrier_id !== $carrier->id) {
            abort(404);
        }

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

        return view('admin.user_driver.edit', compact('carrier', 'userDriverDetail'));
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