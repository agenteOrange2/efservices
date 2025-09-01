<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\EmergencyRepair;
use App\Models\Carrier;
use App\Models\UserDriverDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EmergencyRepairController extends Controller
{
    /**
     * Display a listing of emergency repairs.
     */
    public function index(Request $request)
    {
        $query = EmergencyRepair::with(['vehicle', 'vehicle.carrier', 'vehicle.driver']);

        // Filter by carrier
        if ($request->filled('carrier_id')) {
            $query->whereHas('vehicle', function ($q) use ($request) {
                $q->where('carrier_id', $request->carrier_id);
            });
        }

        // Filter by driver
        if ($request->filled('driver_id')) {
            $query->whereHas('vehicle', function ($q) use ($request) {
                $q->where('user_driver_detail_id', $request->driver_id);
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('repair_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('repair_date', '<=', $request->end_date);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('repair_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                      $vehicleQuery->where('make', 'like', "%{$search}%")
                                   ->orWhere('model', 'like', "%{$search}%")
                                   ->orWhere('vin', 'like', "%{$search}%")
                                   ->orWhere('company_unit_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $emergencyRepairs = $query->orderBy('repair_date', 'desc')->paginate(15);
        
        // Get carriers and drivers for filters
        $carriers = Carrier::orderBy('name')->get();
        $drivers = collect();
        
        if ($request->filled('carrier_id')) {
            $drivers = UserDriverDetail::whereHas('vehicles', function ($q) use ($request) {
                $q->where('carrier_id', $request->carrier_id);
            })->orderBy('first_name')->get();
        }

        return view('admin.vehicles.emergency-repairs.index', compact('emergencyRepairs', 'carriers', 'drivers'));
    }

    /**
     * Show the form for creating a new emergency repair.
     */
    public function create(Request $request)
    {
        $vehicles = collect();
        $carriers = Carrier::orderBy('name')->get();
        
        // If vehicle_id is provided, get that specific vehicle
        if ($request->filled('vehicle_id')) {
            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            $vehicles = collect([$vehicle]);
        }
        
        return view('admin.vehicles.emergency-repairs.create', compact('vehicles', 'carriers'));
    }

    /**
     * Store a newly created emergency repair.
     */
    public function store(Request $request)
    {
        Log::info('Creating emergency repair', [
            'request_data' => $request->except(['_token']),
            'request_has_files' => $request->hasFile('repair_files')
        ]);

        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'repair_name' => 'required|string|max:255',
            'repair_date' => 'required|date',
            'cost' => 'required|numeric|min:0',
            'status' => 'required|in:pending,in_progress,completed',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for emergency repair creation', [
                'errors' => $validator->errors()->toArray()
            ]);

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $emergencyRepair = new EmergencyRepair([
                'vehicle_id' => $request->vehicle_id,
                'repair_name' => $request->repair_name,
                'repair_date' => $request->repair_date,
                'cost' => $request->cost,
                'status' => $request->status,
                'description' => $request->description,
                'notes' => $request->notes,
            ]);

            $result = $emergencyRepair->save();

            Log::info('Emergency repair saved', [
                'repair_id' => $emergencyRepair->id,
                'save_result' => $result,
                'data_saved' => $emergencyRepair->toArray()
            ]);

            // Process repair files if they exist
            if ($request->hasFile('repair_files')) {
                Log::info('Emergency repair files found', [
                    'file_count' => count($request->file('repair_files'))
                ]);

                foreach ($request->file('repair_files') as $file) {
                    Log::info('Processing file', [
                        'name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize()
                    ]);

                    try {
                        $media = $emergencyRepair->addMedia($file)
                            ->toMediaCollection('emergency_repair_files');

                        Log::info('File saved successfully', [
                            'media_id' => $media->id,
                            'file_name' => $media->file_name
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error saving file', [
                            'error' => $e->getMessage(),
                            'file_name' => $file->getClientOriginalName()
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.vehicles.emergency-repairs.index')
                ->with('success', 'Emergency repair created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving emergency repair', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Error saving emergency repair: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified emergency repair.
     */
    public function show(EmergencyRepair $emergencyRepair)
    {
        $emergencyRepair->load(['vehicle', 'vehicle.carrier', 'vehicle.driver']);
        
        return view('admin.vehicles.emergency-repairs.show', compact('emergencyRepair'));
    }

    /**
     * Show the form for editing the specified emergency repair.
     */
    public function edit(EmergencyRepair $emergencyRepair)
    {
        $emergencyRepair->load(['vehicle', 'vehicle.carrier']);
        $carriers = Carrier::orderBy('name')->get();
        $vehicles = Vehicle::where('carrier_id', $emergencyRepair->vehicle->carrier_id)
                          ->orderBy('company_unit_number')
                          ->get();
        
        return view('admin.vehicles.emergency-repairs.edit', compact('emergencyRepair', 'carriers', 'vehicles'));
    }

    /**
     * Update the specified emergency repair.
     */
    public function update(Request $request, EmergencyRepair $emergencyRepair)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'required|exists:vehicles,id',
            'repair_name' => 'required|string|max:255',
            'repair_date' => 'required|date',
            'cost' => 'required|numeric|min:0',
            'status' => 'required|in:pending,in_progress,completed',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $emergencyRepair->update([
                'vehicle_id' => $request->vehicle_id,
                'repair_name' => $request->repair_name,
                'repair_date' => $request->repair_date,
                'cost' => $request->cost,
                'status' => $request->status,
                'description' => $request->description,
                'notes' => $request->notes,
            ]);

            // Process repair files if they exist
            if ($request->hasFile('repair_files')) {
                Log::info('Emergency repair files found in update: ' . count($request->file('repair_files')));

                foreach ($request->file('repair_files') as $file) {
                    Log::info('Processing file in update: ' . $file->getClientOriginalName() . ' - ' . $file->getMimeType());

                    try {
                        $media = $emergencyRepair->addMedia($file)
                            ->toMediaCollection('emergency_repair_files');

                        Log::info('File updated successfully: ' . $media->id);
                    } catch (\Exception $e) {
                        Log::error('Error saving file in update: ' . $e->getMessage());
                    }
                }
            }

            return redirect()->route('admin.vehicles.emergency-repairs.index')
                ->with('success', 'Emergency repair updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating emergency repair', [
                'error' => $e->getMessage(),
                'repair_id' => $emergencyRepair->id
            ]);

            return redirect()->back()
                ->with('error', 'Error updating emergency repair: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified emergency repair.
     */
    public function destroy(EmergencyRepair $emergencyRepair)
    {
        try {
            // Delete all associated files
            $emergencyRepair->clearMediaCollection('emergency_repair_files');
            
            $emergencyRepair->delete();

            return redirect()->route('admin.vehicles.emergency-repairs.index')
                ->with('success', 'Emergency repair deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting emergency repair', [
                'error' => $e->getMessage(),
                'repair_id' => $emergencyRepair->id
            ]);

            return redirect()->back()
                ->with('error', 'Error deleting emergency repair: ' . $e->getMessage());
        }
    }

    /**
     * Delete a specific file from an emergency repair.
     */
    public function deleteFile(EmergencyRepair $emergencyRepair, $mediaId)
    {
        Log::info('deleteFile called', [
            'repair_id' => $emergencyRepair->id,
            'media_id' => $mediaId
        ]);

        try {
            // Verify that the file belongs to the emergency repair
            $media = $emergencyRepair->media()->where('id', $mediaId)->first();

            if (!$media) {
                Log::warning('Media not found', [
                    'repair_id' => $emergencyRepair->id,
                    'media_id' => $mediaId
                ]);
                abort(404, 'File not found');
            }

            Log::info('Media found, deleting', [
                'media_id' => $media->id,
                'media_model_id' => $media->model_id,
                'media_model_type' => $media->model_type
            ]);

            // Delete directly from media table to avoid cascade issues
            DB::table('media')->where('id', $mediaId)->delete();

            Log::info('File deleted successfully');

            return back()->with('success', 'File deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error in deleteFile', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Get vehicles by carrier (AJAX endpoint).
     */
    public function getVehiclesByCarrier($carrierId)
    {
        if (!$carrierId) {
            return response()->json([]);
        }
        
        $vehicles = Vehicle::where('carrier_id', $carrierId)
                          ->orderBy('company_unit_number')
                          ->get(['id', 'company_unit_number', 'make', 'model', 'vin']);
        
        return response()->json($vehicles);
    }

    /**
     * Get drivers by carrier (AJAX endpoint).
     */
    public function getDriversByCarrier(Request $request)
    {
        $carrierId = $request->get('carrier_id');
        
        if (!$carrierId) {
            return response()->json([]);
        }
        
        $drivers = UserDriverDetail::whereHas('vehicles', function ($q) use ($carrierId) {
            $q->where('carrier_id', $carrierId);
        })->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        
        return response()->json($drivers);
    }
}
