<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use App\Models\Vehicle;
use App\Models\VehicleDriverAssignment;
use Carbon\Carbon;

class VehicleManagement extends Component
{
    public $driverId;
    public $userDriverDetail;
    public $vehicles = [];
    public $assignments = [];
    public $selectedDriverTypes = [];
    public $currentDriverType = '';
    public $vehiclesByType = [];
    
    // Vehicle form fields
    public $showAddVehicleModal = false;
    public $editingVehicleId = null;
    public $vehicle_make = '';
    public $vehicle_model = '';
    public $vehicle_year = '';
    public $vehicle_vin = '';
    public $vehicle_company_unit_number = '';
    public $vehicle_type = '';
    public $vehicle_gvwr = '';
    public $vehicle_tire_size = '';
    public $vehicle_fuel_type = '';
    public $vehicle_irp_apportioned_plate = '';
    public $vehicle_registration_state = '';
    public $vehicle_registration_number = '';
    public $vehicle_registration_expiration_date = '';
    public $vehicle_permanent_tag = '';
    public $vehicle_location = '';
    public $vehicle_notes = '';
    public $vehicle_driver_type = 'owner_operator';
    
    // Assignment form fields
    public $showAssignmentModal = false;
    public $assignment_vehicle_id = '';
    public $assignment_start_date = '';
    public $assignment_end_date = '';
    public $assignment_status = 'active';
    public $assignment_notes = '';
    
    protected $rules = [
        'vehicle_make' => 'required|string|max:255',
        'vehicle_model' => 'required|string|max:255',
        'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        'vehicle_vin' => 'required|string|max:17|min:17',
        'vehicle_type' => 'required|string',
        'vehicle_fuel_type' => 'required|string',
        'vehicle_registration_state' => 'required|string',
        'vehicle_registration_number' => 'required|string',
        'vehicle_registration_expiration_date' => 'required|date|after:today',
        'vehicle_driver_type' => 'required|in:owner_operator,third_party_driver,company_driver',
        'assignment_vehicle_id' => 'required|exists:vehicles,id',
        'assignment_start_date' => 'required|date',
        'assignment_status' => 'required|in:active,inactive,pending',
    ];
    
    public function mount($driverId = null)
    {
        $this->driverId = $driverId ?? Auth::id();
        $this->loadDriverData();
        $this->loadVehicles();
        $this->loadAssignments();
    }
    
    protected function loadDriverData()
    {
        $this->userDriverDetail = UserDriverDetail::find($this->driverId);
        
        if ($this->userDriverDetail && $this->userDriverDetail->application) {
            $applyingPosition = $this->userDriverDetail->application->applying_position;
            $this->selectedDriverTypes = explode(',', $applyingPosition);
            $this->currentDriverType = $this->selectedDriverTypes[0] ?? 'owner_operator';
        }
    }
    
    protected function loadVehicles()
    {
        if (!$this->userDriverDetail) return;
        
        $this->vehicles = Vehicle::where('user_driver_detail_id', $this->driverId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
            
        // Group vehicles by driver type
        $this->vehiclesByType = [];
        foreach ($this->vehicles as $vehicle) {
            $driverType = $vehicle['driver_type'] ?? 'owner_operator';
            if (!isset($this->vehiclesByType[$driverType])) {
                $this->vehiclesByType[$driverType] = [];
            }
            $this->vehiclesByType[$driverType][] = $vehicle;
        }
    }
    
    protected function loadAssignments()
    {
        if (!$this->userDriverDetail) return;
        
        $this->assignments = VehicleDriverAssignment::where('user_driver_detail_id', $this->driverId)
            ->with('vehicle')
            ->orderBy('start_date', 'desc')
            ->get()
            ->toArray();
    }
    
    public function openAddVehicleModal($driverType = 'owner_operator')
    {
        $this->resetVehicleForm();
        $this->vehicle_driver_type = $driverType;
        $this->showAddVehicleModal = true;
    }
    
    public function openEditVehicleModal($vehicleId)
    {
        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) return;
        
        $this->editingVehicleId = $vehicleId;
        $this->vehicle_make = $vehicle->make;
        $this->vehicle_model = $vehicle->model;
        $this->vehicle_year = $vehicle->year;
        $this->vehicle_vin = $vehicle->vin;
        $this->vehicle_company_unit_number = $vehicle->company_unit_number;
        $this->vehicle_type = $vehicle->type;
        $this->vehicle_gvwr = $vehicle->gvwr;
        $this->vehicle_tire_size = $vehicle->tire_size;
        $this->vehicle_fuel_type = $vehicle->fuel_type;
        $this->vehicle_irp_apportioned_plate = $vehicle->irp_apportioned_plate;
        $this->vehicle_registration_state = $vehicle->registration_state;
        $this->vehicle_registration_number = $vehicle->registration_number;
        $this->vehicle_registration_expiration_date = $vehicle->registration_expiration_date ? $vehicle->registration_expiration_date->format('Y-m-d') : '';
        $this->vehicle_permanent_tag = $vehicle->permanent_tag;
        $this->vehicle_location = $vehicle->location;
        $this->vehicle_notes = $vehicle->notes;
        $this->vehicle_driver_type = $vehicle->driver_type;
        
        $this->showAddVehicleModal = true;
    }
    
    public function saveVehicle()
    {
        $this->validate([
            'vehicle_make' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_vin' => 'required|string|max:17|min:17',
            'vehicle_type' => 'required|string',
            'vehicle_fuel_type' => 'required|string',
            'vehicle_registration_state' => 'required|string',
            'vehicle_registration_number' => 'required|string',
            'vehicle_registration_expiration_date' => 'required|date|after:today',
            'vehicle_driver_type' => 'required|in:owner_operator,third_party_driver,company_driver',
        ]);
        
        try {
            DB::beginTransaction();
            
            $vehicleData = [
                'carrier_id' => $this->userDriverDetail->carrier_id,
                'make' => $this->vehicle_make,
                'model' => $this->vehicle_model,
                'year' => $this->vehicle_year,
                'vin' => $this->vehicle_vin,
                'company_unit_number' => $this->vehicle_company_unit_number,
                'type' => $this->vehicle_type,
                'gvwr' => $this->vehicle_gvwr,
                'tire_size' => $this->vehicle_tire_size,
                'fuel_type' => $this->vehicle_fuel_type,
                'irp_apportioned_plate' => $this->vehicle_irp_apportioned_plate,
                'registration_state' => $this->vehicle_registration_state,
                'registration_number' => $this->vehicle_registration_number,
                'registration_expiration_date' => Carbon::parse($this->vehicle_registration_expiration_date),
                'permanent_tag' => $this->vehicle_permanent_tag,
                'location' => $this->vehicle_location,
                'notes' => $this->vehicle_notes,
                'driver_type' => $this->vehicle_driver_type,
                'ownership_type' => $this->getOwnershipType($this->vehicle_driver_type),
                'user_driver_detail_id' => $this->driverId,
                'status' => 'active',
            ];
            
            if ($this->editingVehicleId) {
                Vehicle::where('id', $this->editingVehicleId)->update($vehicleData);
                $message = 'Vehicle updated successfully.';
            } else {
                Vehicle::create($vehicleData);
                $message = 'Vehicle added successfully.';
            }
            
            DB::commit();
            
            $this->loadVehicles();
            $this->closeAddVehicleModal();
            session()->flash('message', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving vehicle', [
                'error' => $e->getMessage(),
                'driver_id' => $this->driverId
            ]);
            session()->flash('error', 'Error saving vehicle: ' . $e->getMessage());
        }
    }
    
    public function deleteVehicle($vehicleId)
    {
        try {
            DB::beginTransaction();
            
            // Check if vehicle has active assignments
            $activeAssignments = VehicleDriverAssignment::where('vehicle_id', $vehicleId)
                ->where('status', 'active')
                ->count();
                
            if ($activeAssignments > 0) {
                session()->flash('error', 'Cannot delete vehicle with active assignments.');
                return;
            }
            
            Vehicle::destroy($vehicleId);
            
            DB::commit();
            
            $this->loadVehicles();
            session()->flash('message', 'Vehicle deleted successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting vehicle', [
                'error' => $e->getMessage(),
                'vehicle_id' => $vehicleId
            ]);
            session()->flash('error', 'Error deleting vehicle: ' . $e->getMessage());
        }
    }
    
    public function openAssignmentModal($vehicleId = null)
    {
        $this->resetAssignmentForm();
        $this->assignment_vehicle_id = $vehicleId;
        $this->assignment_start_date = now()->format('Y-m-d');
        $this->showAssignmentModal = true;
    }
    
    public function saveAssignment()
    {
        $this->validate([
            'assignment_vehicle_id' => 'required|exists:vehicles,id',
            'assignment_start_date' => 'required|date',
            'assignment_status' => 'required|in:active,inactive,pending',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Check for overlapping assignments
            $overlapping = VehicleDriverAssignment::where('vehicle_id', $this->assignment_vehicle_id)
                ->where('user_driver_detail_id', $this->driverId)
                ->where('status', 'active')
                ->where(function($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $this->assignment_start_date);
                })
                ->exists();
                
            if ($overlapping) {
                session()->flash('error', 'There is already an active assignment for this vehicle.');
                return;
            }
            
            VehicleDriverAssignment::create([
                'vehicle_id' => $this->assignment_vehicle_id,
                'user_driver_detail_id' => $this->driverId,
                'start_date' => $this->assignment_start_date,
                'end_date' => $this->assignment_end_date ?: null,
                'status' => $this->assignment_status,
                'notes' => $this->assignment_notes,
            ]);
            
            DB::commit();
            
            $this->loadAssignments();
            $this->closeAssignmentModal();
            session()->flash('message', 'Assignment created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating assignment', [
                'error' => $e->getMessage(),
                'driver_id' => $this->driverId
            ]);
            session()->flash('error', 'Error creating assignment: ' . $e->getMessage());
        }
    }
    
    public function endAssignment($assignmentId)
    {
        try {
            DB::beginTransaction();
            
            VehicleDriverAssignment::where('id', $assignmentId)
                ->update([
                    'end_date' => now(),
                    'status' => 'inactive'
                ]);
            
            DB::commit();
            
            $this->loadAssignments();
            session()->flash('message', 'Assignment ended successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error ending assignment', [
                'error' => $e->getMessage(),
                'assignment_id' => $assignmentId
            ]);
            session()->flash('error', 'Error ending assignment: ' . $e->getMessage());
        }
    }
    
    protected function getOwnershipType($driverType)
    {
        return match($driverType) {
            'owner_operator' => 'owned',
            'third_party_driver' => 'third_party',
            'company_driver' => 'company',
            default => 'owned'
        };
    }
    
    protected function resetVehicleForm()
    {
        $this->editingVehicleId = null;
        $this->vehicle_make = '';
        $this->vehicle_model = '';
        $this->vehicle_year = '';
        $this->vehicle_vin = '';
        $this->vehicle_company_unit_number = '';
        $this->vehicle_type = '';
        $this->vehicle_gvwr = '';
        $this->vehicle_tire_size = '';
        $this->vehicle_fuel_type = '';
        $this->vehicle_irp_apportioned_plate = '';
        $this->vehicle_registration_state = '';
        $this->vehicle_registration_number = '';
        $this->vehicle_registration_expiration_date = '';
        $this->vehicle_permanent_tag = '';
        $this->vehicle_location = '';
        $this->vehicle_notes = '';
        $this->vehicle_driver_type = 'owner_operator';
    }
    
    protected function resetAssignmentForm()
    {
        $this->assignment_vehicle_id = '';
        $this->assignment_start_date = '';
        $this->assignment_end_date = '';
        $this->assignment_status = 'active';
        $this->assignment_notes = '';
    }
    
    public function closeAddVehicleModal()
    {
        $this->showAddVehicleModal = false;
        $this->resetVehicleForm();
    }
    
    public function closeAssignmentModal()
    {
        $this->showAssignmentModal = false;
        $this->resetAssignmentForm();
    }
    
    public function render()
    {
        return view('livewire.driver.vehicle-management');
    }
}