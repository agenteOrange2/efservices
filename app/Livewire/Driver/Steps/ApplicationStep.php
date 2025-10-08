<?php
namespace App\Livewire\Driver\Steps;

use App\Helpers\Constants;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\UserDriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use App\Models\CompanyDriverDetail;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;
use App\Models\VehicleDriverAssignment;
use App\Mail\ThirdPartyVehicleVerification;
use Illuminate\Support\Carbon;
use App\Helpers\DateHelper;

class ApplicationStep extends Component
{
    // Application Details
    public $applying_position;
    public $applying_position_other;
    public $applying_location;
    
    // Position options for select
    public $positionOptions = [
        'owner_operator' => 'Owner Operator',
        'third_party_driver' => 'Third Party Driver', 
        'company_driver' => 'Company Driver',
        'other' => 'Other'
    ];
    
    // Vehicle type checkboxes (independent from applying_position)
    public $vehicleTypeCheckboxes = [
        'owner_operator' => false,
        'third_party_driver' => false,
        'company_driver' => false
    ];
    public $eligible_to_work = true;
    public $can_speak_english = true;
    public $has_twic_card = false;
    public $twic_expiration_date;
    public $expected_pay;
    public $how_did_hear = 'internet';
    public $how_did_hear_other;
    public $referral_employee_name;
    
    // Multiple driver types support (deprecated - now using vehicleTypeCheckboxes)
    public $selectedDriverTypes = [];
    public $vehiclesByType = [
        'owner_operator' => [],
        'third_party' => [],
        'company_driver' => []
    ];
    public $currentDriverType = 'owner_operator';
    
    // Owner Operator fields
    public $owner_name;
    public $owner_phone;
    public $owner_email;
    public $owner_dba;
    public $owner_address;
    public $owner_contact_person;
    public $owner_fein;
    public $contract_agreed = false;
    
    // Third Party Company Driver fields
    public $third_party_name;
    public $third_party_phone;
    public $third_party_email;
    public $third_party_dba;
    public $third_party_address;
    public $third_party_contact;
    public $third_party_fein;
    public $email_sent = false;
    
    // Company Driver fields
    public $company_name;
    public $company_phone;
    public $company_email;
    public $company_address;
    public $company_fein;
    public $company_supervisor;
    public $company_employee_id;
    public $company_driver_experience_years;
    public $company_driver_preferred_routes;
    public $company_driver_schedule_preference;
    public $company_driver_additional_certifications;
    
    // Vehicle fields
    public $vehicle_id;
    public $vehicle_make;
    public $vehicle_model;
    public $vehicle_year;
    public $vehicle_vin;
    public $vehicle_company_unit_number;
    public $vehicle_type = 'truck';
    public $vehicle_gvwr;
    public $vehicle_tire_size;
    public $vehicle_fuel_type = 'diesel';
    public $vehicle_irp_apportioned_plate = false;
    public $vehicle_registration_state;
    public $vehicle_registration_number;
    public $vehicle_registration_expiration_date;
    public $vehicle_permanent_tag = false;
    public $vehicle_location;
    public $vehicle_notes;

    // Work History
    public $has_work_history = false;
    public $work_histories = [];

    // References
    public $driverId;
    public $application;
    
    // Existing vehicles
    public $existingVehicles = [];
    public $selectedVehicleId;

    // Validation rules
    protected function rules()
    {
        $rules = [
            'applying_position' => 'required|string',
            'applying_position_other' => 'required_if:applying_position,other',
            'applying_location' => 'required|string',
            'eligible_to_work' => 'accepted',
            'twic_expiration_date' => 'nullable|required_if:has_twic_card,true|date',
            'how_did_hear' => 'required|string',
            'how_did_hear_other' => 'required_if:how_did_hear,other',
            'referral_employee_name' => 'required_if:how_did_hear,employee_referral',
            'work_histories.*.previous_company' => 'required_if:has_work_history,true|string|max:255',
            'work_histories.*.start_date' => 'required_if:has_work_history,true|date',
            'work_histories.*.end_date' =>
            'required_if:has_work_history,true|date|after_or_equal:work_histories.*.start_date',
            'work_histories.*.location' => 'required_if:has_work_history,true|string|max:255',
            'work_histories.*.position' => 'required_if:has_work_history,true|string|max:255',
        ];
        
        // Add validation rules based on vehicle type checkboxes (independent from applying_position)
        if (isset($this->vehicleTypeCheckboxes['owner_operator']) && $this->vehicleTypeCheckboxes['owner_operator']) {
            // Owner Operator validation rules
            $rules = array_merge($rules, [
                'owner_name' => 'required|string|max:255',
                'owner_phone' => 'required|string|max:20',
                'owner_email' => 'required|email|max:255',
                'contract_agreed' => 'accepted',
                
                // Vehicle validation rules for Owner Operator
                'vehicle_make' => 'required|string|max:100',
                'vehicle_model' => 'required|string|max:100',
                'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'vehicle_vin' => 'required|string|max:17',
                'vehicle_type' => 'required|string',
                'vehicle_fuel_type' => 'required|string',
                'vehicle_registration_state' => 'required|string',
                'vehicle_registration_number' => 'required|string',
                'vehicle_registration_expiration_date' => 'required|date',
                'vehicle_company_unit_number' => 'nullable|string|max:50',
                'vehicle_gvwr' => 'nullable|string|max:50',
                'vehicle_tire_size' => 'nullable|string|max:50',
                'vehicle_irp_apportioned_plate' => 'boolean',
                'vehicle_permanent_tag' => 'boolean',
                'vehicle_location' => 'nullable|string|max:255',
                'vehicle_notes' => 'nullable|string',
            ]);
        }
        
        if (isset($this->vehicleTypeCheckboxes['third_party_driver']) && $this->vehicleTypeCheckboxes['third_party_driver']) {
            // Third Party Company Driver validation rules
            $rules = array_merge($rules, [
                'third_party_name' => 'required|string|max:255',
                'third_party_phone' => 'required|string|max:20',
                'third_party_email' => 'required|email|max:255',
                
                // Vehicle validation rules for Third Party
                'vehicle_make' => 'required|string|max:100',
                'vehicle_model' => 'required|string|max:100',
                'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'vehicle_vin' => 'required|string|max:17',
                'vehicle_type' => 'required|string',
                'vehicle_fuel_type' => 'required|string',
                'vehicle_registration_state' => 'required|string',
                'vehicle_registration_number' => 'required|string',
                'vehicle_registration_expiration_date' => 'required|date',
                'vehicle_company_unit_number' => 'nullable|string|max:50',
                'vehicle_gvwr' => 'nullable|string|max:50',
                'vehicle_tire_size' => 'nullable|string|max:50',
                'vehicle_irp_apportioned_plate' => 'boolean',
                'vehicle_permanent_tag' => 'boolean',
                'vehicle_location' => 'nullable|string|max:255',
                'vehicle_notes' => 'nullable|string',
            ]);
        }
        
        if (isset($this->vehicleTypeCheckboxes['company_driver']) && $this->vehicleTypeCheckboxes['company_driver']) {
            // Company Driver validation rules
            $rules = array_merge($rules, [
                'company_driver_experience_years' => 'required|string',
                'company_driver_schedule_preference' => 'required|string',
                'company_driver_preferred_routes' => 'nullable|string|max:1000',
                'company_driver_additional_certifications' => 'nullable|string|max:1000',
            ]);
        }
        
        return $rules;
    }

    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'applying_position' => 'required|string',
            'applying_location' => 'required|string',
            'eligible_to_work' => 'accepted',
        ];
    }
    
    /**
     * Unified validation method for consistency across all navigation methods
     */
    protected function validateStep($partial = false)
    {
        if ($partial) {
            $this->validate($this->partialRules());
        } else {
            $this->validate($this->rules());
        }
    }
    
    /**
     * Validate that previous steps are completed before advancing
     */
    protected function validateStepCompletion()
    {
        if (!$this->driverId) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Driver information is required to proceed.'
            ]);
            return false;
        }
        
        // Check if driver has completed previous steps (step 1 and 2)
        $driver = UserDriverDetail::find($this->driverId);
        if (!$driver || $driver->current_step < 2) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please complete previous steps before proceeding.'
            ]);
            return false;
        }
        
        return true;
    }

    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        
        // Ensure selectedDriverTypes is always an array (deprecated)
        if (!is_array($this->selectedDriverTypes)) {
            $this->selectedDriverTypes = [];
        }
        
        // Ensure vehiclesByType is always properly initialized
        if (!is_array($this->vehiclesByType)) {
            $this->vehiclesByType = [
                'owner_operator' => [],
                'third_party' => [],
                'company_driver' => []
            ];
        }
        
        // Initialize vehicleTypeCheckboxes
        if (!is_array($this->vehicleTypeCheckboxes)) {
            $this->vehicleTypeCheckboxes = [
                'owner_operator' => false,
                'third_party_driver' => false,
                'company_driver' => false
            ];
        }
        
        Log::info('ApplicationStep mounted', [
            'driver_id' => $this->driverId,
            'selectedDriverTypes' => $this->selectedDriverTypes,
            'vehiclesByType' => array_keys($this->vehiclesByType),
            'vehicleTypeCheckboxes' => $this->vehicleTypeCheckboxes
        ]);
        
        if ($driverId) {
            $this->loadExistingData();
            $this->loadExistingVehicles();
            $this->loadExistingVehicleAssignments();
        } else {
            // Initialize work history array with one empty record
            $this->work_histories = [
                $this->getEmptyWorkHistory()
            ];
        }
        
        Log::info('ApplicationStep mount completed', [
            'driver_id' => $this->driverId,
            'selectedDriverTypes_after_load' => $this->selectedDriverTypes,
            'applying_position' => $this->applying_position ?? 'null',
            'vehicleTypeCheckboxes_after_load' => $this->vehicleTypeCheckboxes
        ]);
    }
    
    /**
     * Actualiza los campos cuando cambia la posición seleccionada
     */
    public function updatedApplyingPosition($value)
    {
        if ($value === 'owner_operator') {
            // Clear third party fields
            $this->third_party_name = null;
            $this->third_party_phone = null;
            $this->third_party_email = null;
            $this->third_party_dba = null;
            $this->third_party_address = null;
            $this->third_party_contact = null;
            $this->third_party_fein = null;
            $this->email_sent = false;
            
            // Auto-fill owner fields if driver info is available
            $this->autoFillOwnerFields();
        } elseif ($value === 'third_party_driver') {
            // Clear owner fields
            $this->owner_name = null;
            $this->owner_phone = null;
            $this->owner_email = null;
            $this->contract_agreed = false;
        } else {
            // Clear both owner and third party fields for other positions
            $this->owner_name = null;
            $this->owner_phone = null;
            $this->owner_email = null;
            $this->contract_agreed = false;
            
            $this->third_party_name = null;
            $this->third_party_phone = null;
            $this->third_party_email = null;
            $this->third_party_dba = null;
            $this->third_party_address = null;
            $this->third_party_contact = null;
            $this->third_party_fein = null;
            $this->email_sent = false;
        }
        
        // Synchronize with vehicle ownership_type using Constants mapping
        $this->syncApplyingPositionWithOwnership($value);
        
        // Reload existing vehicles when position changes
        if ($this->driverId && ($value === 'owner_operator' || $value === 'third_party_driver')) {
            $this->loadExistingVehicles();
        }
        
        Log::info('Applying position cambiado', [
            'driver_id' => $this->driverId,
            'new_position' => $value,
            'previous_position' => $this->applying_position ?? 'none'
        ]);
    }
    
    /**
     * Load existing vehicles for the driver
     */
    protected function loadExistingVehicles()
    {
        // Obtener el detalle del driver
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            $this->existingVehicles = collect();
            return;
        }
        
        // Determinar el tipo de vehículo según la posición seleccionada
        $driverType = $this->applying_position === 'owner_operator' ? 'owner_operator' : 
                      ($this->applying_position === 'third_party_driver' ? 'third_party' : 'company');
        
        // Cargar los vehículos que pertenecen específicamente a este driver
        $driverVehicles = Vehicle::where('user_driver_detail_id', $userDriverDetail->id)
                                ->get();
        
        Log::info('Loading driver vehicles', [
            'driver_id' => $userDriverDetail->id,
            'driver_type' => $driverType,
            'vehicles_found' => $driverVehicles->count()
        ]);
        
        // Si no hay vehículos asociados directamente al driver, cargar vehículos disponibles del tipo correcto
        if ($driverVehicles->isEmpty()) {
            if ($userDriverDetail && $userDriverDetail->carrier_id) {
                // Cargar vehículos del mismo carrier que no estén asignados a otro driver y sean del tipo correcto
                $userDriverDetailId = $userDriverDetail->id;
                $this->existingVehicles = Vehicle::where('carrier_id', $userDriverDetail->carrier_id)
                    ->where(function($query) use ($userDriverDetailId) {
                        $query->whereNull('user_driver_detail_id')
                ->orWhere('user_driver_detail_id', $userDriverDetailId);
                    })
                    ->get();
                    
                Log::info('Loading carrier vehicles', [
                    'carrier_id' => $userDriverDetail->carrier_id,
                    'driver_type' => $driverType,
                    'vehicles_found' => $this->existingVehicles->count()
                ]);
            } else {
                // Si no se puede obtener el carrier_id, inicializar como colección vacía
                $this->existingVehicles = collect();
            }
        } else {
            // Si hay vehículos asociados directamente al driver, usarlos
            $this->existingVehicles = $driverVehicles;
        }
    }
    
    /**
     * Select an existing vehicle
     */
    public function selectVehicle($vehicleId)
    {
        $this->selectedVehicleId = $vehicleId;
        $vehicle = Vehicle::find($vehicleId);
        
        if ($vehicle) {
            $this->vehicle_id = $vehicle->id;
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
            $this->vehicle_registration_expiration_date = DateHelper::toDisplay($vehicle->registration_expiration_date);
            $this->vehicle_permanent_tag = $vehicle->permanent_tag;
            $this->vehicle_location = $vehicle->location;
            $this->vehicle_notes = $vehicle->notes;
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Vehicle selected successfully'
            ]);
        }
    }
    
    /**
     * Clear vehicle form to add a new one
     */
    public function clearVehicleForm()
    {
        $this->selectedVehicleId = null;
        $this->vehicle_id = null;
        $this->vehicle_make = null;
        $this->vehicle_model = null;
        $this->vehicle_year = null;
        $this->vehicle_vin = null;
        $this->vehicle_company_unit_number = null;
        $this->vehicle_type = 'truck';
        $this->vehicle_gvwr = null;
        $this->vehicle_tire_size = null;
        $this->vehicle_fuel_type = 'diesel';
        $this->vehicle_irp_apportioned_plate = false;
        $this->vehicle_registration_state = null;
        $this->vehicle_registration_number = null;
        $this->vehicle_registration_expiration_date = null;
        $this->vehicle_permanent_tag = false;
        $this->vehicle_location = null;
        $this->vehicle_notes = null;
    }
    
    /**
     * Redirect to vehicle detail page
     */
    public function viewVehicleDetails($vehicleId)
    {
        return redirect()->route('admin.vehicles.show', $vehicleId);
    }
    
    /**
     * Auto-rellena los campos del propietario con la información del conductor
     */
    protected function autoFillOwnerFields()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail || !$userDriverDetail->user) {
            return;
        }
        
        $user = $userDriverDetail->user;
        
        // Construir nombre completo con first_name, middle_name y last_name
        $fullName = $user->name;
        if ($userDriverDetail->middle_name) {
            $fullName .= ' ' . $userDriverDetail->middle_name;
        }
        if ($userDriverDetail->last_name) {
            $fullName .= ' ' . $userDriverDetail->last_name;
        }
        
        $this->owner_name = $fullName;
        $this->owner_phone = $userDriverDetail->phone;
        $this->owner_email = $user->email;
    }
    
    /**
     * Add a driver type to the selection
     */
    public function addDriverType($type)
    {
        // Ensure selectedDriverTypes is always an array
        if (!is_array($this->selectedDriverTypes)) {
            $this->selectedDriverTypes = [];
        }
        
        // Ensure vehiclesByType is always an array
        if (!is_array($this->vehiclesByType)) {
            $this->vehiclesByType = [
                'owner_operator' => [],
                'third_party' => [],
                'company_driver' => []
            ];
        }
        
        if (!in_array($type, $this->selectedDriverTypes)) {
            $this->selectedDriverTypes[] = $type;
            $this->currentDriverType = $type;
            
            // Initialize vehicles array for this type if not exists
            if (!isset($this->vehiclesByType[$type])) {
                $this->vehiclesByType[$type] = [];
            }
            
            Log::info('Driver type added', [
                'driver_id' => $this->driverId,
                'type' => $type,
                'selected_types' => $this->selectedDriverTypes
            ]);
        }
    }
    
    /**
     * Handle updates to selectedDriverTypes property (called automatically by Livewire)
     */
    public function updatedSelectedDriverTypes($value)
    {
        Log::info('updatedSelectedDriverTypes called', [
            'value' => $value,
            'selectedDriverTypes_before' => $this->selectedDriverTypes,
            'is_array' => is_array($this->selectedDriverTypes)
        ]);
        
        // Ensure selectedDriverTypes is always an array
        if (!is_array($this->selectedDriverTypes)) {
            $this->selectedDriverTypes = [];
        }
        
        // Ensure vehiclesByType is always an array
        if (!is_array($this->vehiclesByType)) {
            $this->vehiclesByType = [
                'owner_operator' => [],
                'third_party' => [],
                'company_driver' => []
            ];
        }
        
        // Initialize vehiclesByType for newly selected types
        foreach ($this->selectedDriverTypes as $type) {
            if (!isset($this->vehiclesByType[$type])) {
                $this->vehiclesByType[$type] = [];
            }
        }
        
        // Set currentDriverType if not set or if current type is no longer selected
        if (!$this->currentDriverType || !in_array($this->currentDriverType, $this->selectedDriverTypes)) {
            $this->currentDriverType = !empty($this->selectedDriverTypes) ? $this->selectedDriverTypes[0] : null;
        }
        
        Log::info('Driver types updated', [
            'driver_id' => $this->driverId,
            'selected_types' => $this->selectedDriverTypes,
            'current_type' => $this->currentDriverType
        ]);
    }
    
    /**
     * Toggle driver type selection (add if not selected, remove if selected)
     */
    public function toggleDriverType($type)
    {
        // Ensure selectedDriverTypes is always an array
        if (!is_array($this->selectedDriverTypes)) {
            $this->selectedDriverTypes = [];
        }
        
        // Ensure vehiclesByType is always an array
        if (!is_array($this->vehiclesByType)) {
            $this->vehiclesByType = [
                'owner_operator' => [],
                'third_party' => [],
                'company_driver' => []
            ];
        }
        
        if (in_array($type, $this->selectedDriverTypes)) {
            // Remove the type if it's already selected
            $this->selectedDriverTypes = array_values(array_filter($this->selectedDriverTypes, function($selectedType) use ($type) {
                return $selectedType !== $type;
            }));
            
            // Clear vehicles for this type when deselected
            if (isset($this->vehiclesByType[$type])) {
                $this->vehiclesByType[$type] = [];
            }
            
            // Update currentDriverType if needed
            if ($this->currentDriverType === $type) {
                $this->currentDriverType = !empty($this->selectedDriverTypes) ? $this->selectedDriverTypes[0] : null;
            }
            
            Log::info('Driver type removed via toggle', [
                'driver_id' => $this->driverId,
                'type' => $type,
                'selected_types' => $this->selectedDriverTypes
            ]);
        } else {
            // Add the type if it's not selected
            $this->selectedDriverTypes[] = $type;
            
            // Initialize vehicles array for this type if not exists
            if (!isset($this->vehiclesByType[$type])) {
                $this->vehiclesByType[$type] = [];
            }
            
            // Set as current if no current type is set
            if (!$this->currentDriverType) {
                $this->currentDriverType = $type;
            }
            
            Log::info('Driver type added via toggle', [
                'driver_id' => $this->driverId,
                'type' => $type,
                'selected_types' => $this->selectedDriverTypes
            ]);
        }
    }
    
    /**
     * Remove a driver type from the selection
     */
    public function removeDriverType($type)
    {
        // Ensure selectedDriverTypes is always an array
        if (!is_array($this->selectedDriverTypes)) {
            $this->selectedDriverTypes = [];
        }
        
        // Ensure vehiclesByType is always an array
        if (!is_array($this->vehiclesByType)) {
            $this->vehiclesByType = [
                'owner_operator' => [],
                'third_party' => [],
                'company_driver' => []
            ];
        }
        
        $this->selectedDriverTypes = array_filter($this->selectedDriverTypes, function($t) use ($type) {
            return $t !== $type;
        });
        
        // Reindex array to avoid gaps
        $this->selectedDriverTypes = array_values($this->selectedDriverTypes);
        
        // Clear vehicles for this type
        if (isset($this->vehiclesByType[$type])) {
            $this->vehiclesByType[$type] = [];
        }
        
        // Switch to first available type or default
        if ($this->currentDriverType === $type) {
            $this->currentDriverType = !empty($this->selectedDriverTypes) ? $this->selectedDriverTypes[0] : 'owner_operator';
        }
        
        Log::info('Driver type removed', [
            'driver_id' => $this->driverId,
            'type' => $type,
            'selected_types' => $this->selectedDriverTypes
        ]);
    }
    
    /**
     * Switch current driver type
     */
    public function switchDriverType($type)
    {
        // Ensure selectedDriverTypes is always an array
        if (!is_array($this->selectedDriverTypes)) {
            $this->selectedDriverTypes = [];
        }
        
        if (in_array($type, $this->selectedDriverTypes)) {
            $this->currentDriverType = $type;
            
            Log::info('Driver type switched', [
                'driver_id' => $this->driverId,
                'type' => $type
            ]);
        }
    }
    
    /**
     * Handle vehicle type checkbox changes
     */
    public function updatedVehicleTypeCheckboxes($value, $type)
    {
        Log::info('Vehicle type checkbox updated', [
            'type' => $type,
            'value' => $value,
            'driver_id' => $this->driverId
        ]);
        
        if ($value) {
            $this->createVehicleDriverAssignment($type);
            
            // Auto-fill owner fields when owner_operator is selected
            if ($type === 'owner_operator') {
                $this->autoFillOwnerFields();
            }
        } else {
            $this->deleteVehicleDriverAssignment($type);
        }
    }
    
    /**
     * Create VehicleDriverAssignment record for the selected type
     */
    private function createVehicleDriverAssignment($type)
    {
        try {
            // Check if assignment already exists
            $existingAssignment = VehicleDriverAssignment::where('user_driver_detail_id', $this->driverId)
                ->where('status', 'pending')
                ->first();
                
            if (!$existingAssignment) {
                $assignmentData = [
                    'user_driver_detail_id' => $this->driverId,
                    'status' => 'pending',
                    'start_date' => now()->format('Y-m-d')
                ];
                
                // Para company_driver: vehicle_id = NULL (se asigna después)
                // Para owner_operator y third_party_driver: necesitamos el vehicle_id del vehículo
                if ($type === 'company_driver') {
                    $assignmentData['vehicle_id'] = null;
                } else {
                    // Para owner_operator y third_party_driver, buscar el vehículo asociado
                    $vehicle = null;
                    if (isset($this->vehiclesByType[$type]) && !empty($this->vehiclesByType[$type])) {
                        // Si hay vehículos en el array, usar el primero que tenga ID
                        foreach ($this->vehiclesByType[$type] as $vehicleData) {
                            if (!empty($vehicleData['id'])) {
                                $vehicle = Vehicle::find($vehicleData['id']);
                                break;
                            }
                        }
                    }
                    
                    $assignmentData['vehicle_id'] = $vehicle ? $vehicle->id : null;
                }
                
                $assignment = VehicleDriverAssignment::create($assignmentData);
                
                Log::info('VehicleDriverAssignment created', [
                    'assignment_id' => $assignment->id,
                    'driver_id' => $this->driverId,
                    'type' => $type,
                    'vehicle_id' => $assignmentData['vehicle_id'],
                    'start_date' => $assignmentData['start_date']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating VehicleDriverAssignment', [
                'error' => $e->getMessage(),
                'driver_id' => $this->driverId,
                'type' => $type
            ]);
        }
    }
    
    /**
     * Delete VehicleDriverAssignment record for the deselected type
     */
    private function deleteVehicleDriverAssignment($type)
    {
        try {
            $deleted = VehicleDriverAssignment::where('user_driver_detail_id', $this->driverId)
                ->where('status', 'pending')
                ->delete();
                
            Log::info('VehicleDriverAssignment deleted', [
                'deleted_count' => $deleted,
                'driver_id' => $this->driverId,
                'type' => $type
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting VehicleDriverAssignment', [
                'error' => $e->getMessage(),
                'driver_id' => $this->driverId,
                'type' => $type
            ]);
        }
    }
    
    /**
     * Load existing vehicle assignments from database
     */
    private function loadExistingVehicleAssignments()
    {
        if (!$this->driverId) {
            return;
        }
        
        try {
            $assignments = VehicleDriverAssignment::where('user_driver_detail_id', $this->driverId)->get();
            
            // Since VehicleDriverAssignment doesn't have driver_type column,
            // we'll load assignments but won't set checkboxes based on driver_type
            // The checkboxes will be managed by the applying_position instead
            
            Log::info('Loaded existing vehicle assignments', [
                'driver_id' => $this->driverId,
                'assignments_count' => $assignments->count(),
                'vehicleTypeCheckboxes' => $this->vehicleTypeCheckboxes
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading vehicle assignments', [
                'error' => $e->getMessage(),
                'driver_id' => $this->driverId
            ]);
        }
    }
    
    /**
     * Add vehicle to specific driver type
     */
    public function addVehicleToType($type)
    {
        if (!isset($this->vehiclesByType[$type])) {
            $this->vehiclesByType[$type] = [];
        }
        
        $this->vehiclesByType[$type][] = [
            'id' => null,
            'make' => '',
            'model' => '',
            'year' => '',
            'vin' => '',
            'company_unit_number' => '',
            'type' => 'truck',
            'gvwr' => '',
            'tire_size' => '',
            'fuel_type' => 'diesel',
            'irp_apportioned_plate' => false,
            'registration_state' => '',
            'registration_number' => '',
            'registration_expiration_date' => '',
            'permanent_tag' => false,
            'location' => '',
            'notes' => ''
        ];
        
        Log::info('Vehicle added to type', [
            'driver_id' => $this->driverId,
            'type' => $type,
            'vehicle_count' => count($this->vehiclesByType[$type])
        ]);
    }
    
    /**
     * Remove vehicle from specific driver type
     */
    public function removeVehicleFromType($type, $index)
    {
        if (isset($this->vehiclesByType[$type][$index])) {
            unset($this->vehiclesByType[$type][$index]);
            $this->vehiclesByType[$type] = array_values($this->vehiclesByType[$type]);
            
            Log::info('Vehicle removed from type', [
                'driver_id' => $this->driverId,
                'type' => $type,
                'index' => $index
            ]);
        }
    }

    // Load existing data
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }

        $this->application = $userDriverDetail->application;
        if ($this->application && $this->application->details) {
            $details = $this->application->details;

            $this->applying_position = $details->applying_position;
            $this->applying_position_other = $details->applying_position_other;
            $this->applying_location = $details->applying_location;
            $this->eligible_to_work = $details->eligible_to_work;
            $this->can_speak_english = $details->can_speak_english;
            $this->has_twic_card = $details->has_twic_card;
            $this->twic_expiration_date = DateHelper::toDisplay($details->twic_expiration_date);
            $this->expected_pay = $details->expected_pay;
            $this->how_did_hear = $details->how_did_hear;
            $this->how_did_hear_other = $details->how_did_hear_other;
            $this->referral_employee_name = $details->referral_employee_name;
            $this->has_work_history = (bool)($details->has_work_history ?? false);
            
            // Initialize selectedDriverTypes based on applying_position
            if ($this->applying_position) {
                $this->selectedDriverTypes = [$this->applying_position];
                $this->currentDriverType = $this->applying_position;
                
                // Initialize vehiclesByType for the selected type
                if (!isset($this->vehiclesByType[$this->applying_position])) {
                    $this->vehiclesByType[$this->applying_position] = [];
                }
            }
            
            // Cargar datos de Owner Operator desde la nueva tabla
            if ($this->applying_position === 'owner_operator' && $this->application->ownerOperatorDetail) {
                $ownerDetails = $this->application->ownerOperatorDetail;
                $this->owner_name = $ownerDetails->owner_name;
                $this->owner_phone = $ownerDetails->owner_phone;
                $this->owner_email = $ownerDetails->owner_email;
                $this->contract_agreed = (bool)($ownerDetails->contract_agreed ?? false);
                
                Log::info('Cargados datos de Owner Operator', [
                    'application_id' => $this->application->id,
                    'owner_name' => $this->owner_name
                ]);
            }
            
            // Cargar datos de Third Party desde la nueva tabla
            if ($this->applying_position === 'third_party_driver' && $this->application->thirdPartyDetail) {
                $thirdPartyDetails = $this->application->thirdPartyDetail;
                $this->third_party_name = $thirdPartyDetails->third_party_name;
                $this->third_party_phone = $thirdPartyDetails->third_party_phone;
                $this->third_party_email = $thirdPartyDetails->third_party_email;
                $this->third_party_dba = $thirdPartyDetails->third_party_dba;
                $this->third_party_address = $thirdPartyDetails->third_party_address;
                $this->third_party_contact = $thirdPartyDetails->third_party_contact;
                $this->third_party_fein = $thirdPartyDetails->third_party_fein;
                $this->email_sent = (bool)($thirdPartyDetails->email_sent ?? false);
                
                Log::info('Cargados datos de Third Party', [
                    'application_id' => $this->application->id,
                    'third_party_name' => $this->third_party_name
                ]);
            }
            
            // Si hay un vehículo asociado a la aplicación, cargar sus datos
            if ($details->vehicle_id && $details->vehicle) {
                $vehicle = $details->vehicle;
                $this->vehicle_id = $vehicle->id;
                $this->vehicle_make = $vehicle->make;
                $this->vehicle_model = $vehicle->model;
                $this->vehicle_year = $vehicle->year;
                $this->vehicle_vin = $vehicle->vin;
                $this->vehicle_company_unit_number = $vehicle->company_unit_number;
                $this->vehicle_type = $vehicle->type;
                $this->vehicle_gvwr = $vehicle->gvwr;
                $this->vehicle_tire_size = $vehicle->tire_size;
                $this->vehicle_fuel_type = $vehicle->fuel_type;
                $this->vehicle_irp_apportioned_plate = (bool)$vehicle->irp_apportioned_plate;
                $this->vehicle_registration_state = $vehicle->registration_state;
                $this->vehicle_registration_number = $vehicle->registration_number;
                $this->vehicle_registration_expiration_date = $vehicle->registration_expiration_date ? DateHelper::toDisplay($vehicle->registration_expiration_date) : null;
                $this->vehicle_permanent_tag = (bool)$vehicle->permanent_tag;
                $this->vehicle_location = $vehicle->location;
                $this->vehicle_notes = $vehicle->notes;
            }
        }

        // Load work histories
        $workHistories = $userDriverDetail->workHistories;
        if ($workHistories->count() > 0) {
            $this->has_work_history = true;
            $this->work_histories = [];
            foreach ($workHistories as $history) {
                $this->work_histories[] = [
                    'id' => $history->id,
                    'previous_company' => $history->previous_company,
                    'start_date' => DateHelper::toDisplay($history->start_date),
                    'end_date' => DateHelper::toDisplay($history->end_date),
                    'location' => $history->location,
                    'position' => $history->position,
                    'reason_for_leaving' => $history->reason_for_leaving,
                    'reference_contact' => $history->reference_contact,
                ];
            }

            // También actualiza el campo en los detalles de la aplicación si es necesario
            if ($this->application && $this->application->details && !$this->application->details->has_work_history) {
                $this->application->details->update(['has_work_history' => true]);
            }
        }
    }

    protected function saveApplicationDetails()
    {
        try {
            Log::info('ApplicationStep: Iniciando transacción de base de datos');
            DB::beginTransaction();

            Log::info('Guardando detalles de aplicación', ['driverId' => $this->driverId]);

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                Log::error('Driver no encontrado', ['driverId' => $this->driverId]);
                throw new \Exception('Driver not found');
            }

            // Get or create application
            $application = $userDriverDetail->application;
            if (!$application) {
                Log::info('Creando nueva aplicación para el driver', ['userId' => $userDriverDetail->user_id]);
                $application = DriverApplication::create([
                    'user_id' => $userDriverDetail->user_id,
                    'status' => 'draft'
                ]);
            }
            
            // Process vehicles by type
            $this->processOwnerOperatorVehicles($application, $userDriverDetail);
            $this->processThirdPartyVehicles($application, $userDriverDetail);
            $this->processCompanyDriverInfo($application, $userDriverDetail);
            
            // Crear vehículo si es necesario
            if (($this->applying_position === 'owner_operator' || $this->applying_position === 'third_party_driver') && 
                $this->vehicle_make && $this->vehicle_model && $this->vehicle_year && $this->vehicle_vin) {
                
                $carrierId = $userDriverDetail->carrier_id;
                if (!$carrierId) {
                    throw new \Exception('No carrier found for this driver');
                }
                
                // Verificar si ya existe un vehículo con el mismo VIN o si se seleccionó uno existente
                if ($this->vehicle_id) {
                    $vehicle = Vehicle::find($this->vehicle_id);
                    if ($vehicle) {
                        // Determinar el tipo de vehículo según la posición seleccionada
                        $driverType = $this->applying_position === 'owner_operator' ? 'owner_operator' : 
                                      ($this->applying_position === 'third_party_driver' ? 'third_party' : 'company');
                        
                        // Update existing vehicle
                        $vehicle->update([
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
                            'registration_state' => $this->vehicle_registration_state ?: $this->applying_location,
                            'registration_number' => $this->vehicle_registration_number ?: 'Pending',
                            'registration_expiration_date' => $this->vehicle_registration_expiration_date 
                                ? Carbon::parse(DateHelper::toDatabase($this->vehicle_registration_expiration_date)) 
                                : now()->addYear(),
                            'permanent_tag' => $this->vehicle_permanent_tag,
                            'location' => $this->vehicle_location,
                            'driver_type' => $driverType,
                            'ownership_type' => $this->applying_position === 'owner_operator' ? 'owned' : 'leased',
                            'status' => 'pending',
                            'notes' => $this->vehicle_notes,
                        ]);
                        
                        Log::info('Vehículo actualizado exitosamente', ['id' => $vehicle->id]);
                    }
                } else {
                    // Verificar si ya existe un vehículo con el mismo VIN
                    $existingVehicle = Vehicle::where('vin', $this->vehicle_vin)->first();
                    
                    if (!$existingVehicle) {
                    // Preparar datos para el registro de vehículo
                    $registrationDate = $this->vehicle_registration_expiration_date 
                        ? Carbon::parse(DateHelper::toDatabase($this->vehicle_registration_expiration_date)) 
                        : now()->addYear();
                    
                    // Determinar el tipo de vehículo según la posición seleccionada
                    $driverType = $this->applying_position === 'owner_operator' ? 'owner_operator' : 
                                  ($this->applying_position === 'third_party_driver' ? 'third_party' : 'company');
                    
                    // Crear nuevo vehículo
                    $vehicle = Vehicle::create([
                        'carrier_id' => $carrierId,
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
                        'registration_state' => $this->vehicle_registration_state ?: $this->applying_location,
                        'registration_number' => $this->vehicle_registration_number ?: 'Pending',
                        'registration_expiration_date' => $registrationDate,
                        'permanent_tag' => $this->vehicle_permanent_tag,
                        'location' => $this->vehicle_location,
                        'ownership_type' => $this->applying_position === 'owner_operator' ? 'owned' : 'leased',
                        'driver_type' => $driverType,
                        'user_id' => $userDriverDetail->user_id,
                        'status' => 'pending',
                        'notes' => $this->vehicle_notes,
                    ]);
                    
                    Log::info('Vehículo creado exitosamente', ['id' => $vehicle->id]);
                    // Set the vehicle_id property so it gets saved in the application details
                    $this->vehicle_id = $vehicle->id;
                }
            }
            }

            // Update application details
            Log::info('Actualizando detalles de aplicación', [
                'position' => $this->applying_position,
                'location' => $this->applying_location
            ]);

            $applicationDetails = $application->details()->updateOrCreate(
                [],
                [
                    'applying_position' => $this->applying_position,
                    'applying_position_other' => $this->applying_position === 'other' ? $this->applying_position_other : null,
                    'applying_location' => $this->applying_location,
                    'eligible_to_work' => $this->eligible_to_work,
                    'can_speak_english' => $this->can_speak_english,
                    'has_twic_card' => $this->has_twic_card,
                    'twic_expiration_date' => $this->has_twic_card ? DateHelper::toDatabase($this->twic_expiration_date) : null,
                    'expected_pay' => $this->expected_pay,
                    'how_did_hear' => $this->how_did_hear,
                    'how_did_hear_other' => $this->how_did_hear === 'other' ? $this->how_did_hear_other : null,
                    'referral_employee_name' => $this->how_did_hear === 'employee_referral' ? $this->referral_employee_name : null,
                    'has_work_history' => $this->has_work_history,
                    // Vehicle relationship
                    'vehicle_id' => $this->vehicle_id,
                ]
            );
            
            // Limpiar detalles del tipo anterior y guardar detalles del tipo actual
            if ($this->applying_position === 'owner_operator') {
                // Eliminar detalles de Third Party si existen
                $application->thirdPartyDetail()->delete();
                
                // Guardar detalles de Owner Operator
                $application->ownerOperatorDetail()->updateOrCreate(
                    [],
                    [
                        'owner_name' => $this->owner_name,
                        'owner_phone' => $this->owner_phone,
                        'owner_email' => $this->owner_email,
                        'contract_agreed' => $this->contract_agreed,
                        'vehicle_id' => $this->vehicle_id,
                    ]
                );
                
                Log::info('Detalles de Owner Operator guardados y Third Party eliminados', [
                    'application_id' => $application->id,
                    'owner_name' => $this->owner_name
                ]);
            } elseif ($this->applying_position === 'third_party_driver') {
                // Eliminar detalles de Owner Operator si existen
                $application->ownerOperatorDetail()->delete();
                
                // Guardar detalles de Third Party
                $application->thirdPartyDetail()->updateOrCreate(
                    [],
                    [
                        'third_party_name' => $this->third_party_name,
                        'third_party_phone' => $this->third_party_phone,
                        'third_party_email' => $this->third_party_email,
                        'third_party_dba' => $this->third_party_dba,
                        'third_party_address' => $this->third_party_address,
                        'third_party_contact' => $this->third_party_contact,
                        'third_party_fein' => $this->third_party_fein,
                        'email_sent' => $this->email_sent,
                        'vehicle_id' => $this->vehicle_id,
                    ]
                );
                
                Log::info('Detalles de Third Party guardados y Owner Operator eliminados', [
                    'application_id' => $application->id,
                    'third_party_name' => $this->third_party_name
                ]);
            } else {
                // Si no es owner_operator ni third_party_driver, eliminar ambos tipos de detalles
                $application->ownerOperatorDetail()->delete();
                $application->thirdPartyDetail()->delete();
                
                Log::info('Detalles de Owner Operator y Third Party eliminados para tipo: ' . $this->applying_position, [
                    'application_id' => $application->id
                ]);
            }

            // Handle work histories
            if ($this->has_work_history) {
                Log::info('Procesando historiales de trabajo', ['count' => count($this->work_histories)]);

                $existingWorkHistoryIds = $userDriverDetail->workHistories()->pluck('id')->toArray();
                $updatedWorkHistoryIds = [];

                foreach ($this->work_histories as $historyData) {
                    $historyId = $historyData['id'] ?? null;

                    if ($historyId) {
                        // Update existing history
                        $history = $userDriverDetail->workHistories()->find($historyId);
                        if ($history) {
                            $history->update([
                                'previous_company' => $historyData['previous_company'],
                                'start_date' => DateHelper::toDatabase($historyData['start_date']),
                                'end_date' => DateHelper::toDatabase($historyData['end_date']),
                                'location' => $historyData['location'],
                                'position' => $historyData['position'],
                                'reason_for_leaving' => $historyData['reason_for_leaving'] ?? null,
                                'reference_contact' => $historyData['reference_contact'] ?? null,
                            ]);
                            $updatedWorkHistoryIds[] = $history->id;
                            Log::info('Actualizado historial de trabajo existente', ['id' => $history->id]);
                        }
                    } else {
                        // Create new history
                        $history = $userDriverDetail->workHistories()->create([
                            'previous_company' => $historyData['previous_company'],
                            'start_date' => DateHelper::toDatabase($historyData['start_date']),
                            'end_date' => DateHelper::toDatabase($historyData['end_date']),
                            'location' => $historyData['location'],
                            'position' => $historyData['position'],
                            'reason_for_leaving' => $historyData['reason_for_leaving'] ?? null,
                            'reference_contact' => $historyData['reference_contact'] ?? null,
                        ]);
                        $updatedWorkHistoryIds[] = $history->id;
                        Log::info('Creado nuevo historial de trabajo', ['id' => $history->id]);
                    }
                }

                // Delete histories that are no longer needed
                $historiesToDelete = array_diff($existingWorkHistoryIds, $updatedWorkHistoryIds);
                if (!empty($historiesToDelete)) {
                    $userDriverDetail->workHistories()->whereIn('id', $historiesToDelete)->delete();
                    Log::info('Eliminados historiales de trabajo no necesarios', ['ids' => $historiesToDelete]);
                }
            } else {
                // If no work history, delete all existing records
                $userDriverDetail->workHistories()->delete();
                Log::info('Eliminados todos los historiales de trabajo (no hay historial)');
            }

            // Update current step
            $userDriverDetail->update(['current_step' => 3]);

            Log::info('Actualización de aplicación completada con éxito');
            DB::commit();

            session()->flash('message', 'Información de aplicación guardada correctamente.');
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error guardando aplicación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving application details: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process Owner Operator vehicles
     */
    protected function processOwnerOperatorVehicles($application, $userDriverDetail)
    {
        Log::info('ApplicationStep: Iniciando processOwnerOperatorVehicles', [
            'user_driver_detail_id' => $userDriverDetail->id,
            'application_id' => $application->id,
            'owner_name' => $this->owner_name ?? null,
            'owner_phone' => $this->owner_phone ?? null,
            'owner_email' => $this->owner_email ?? null,
            'vehicle_make' => $this->vehicle_make ?? null,
            'vehicle_model' => $this->vehicle_model ?? null,
            'vehicle_vin' => $this->vehicle_vin ?? null
        ]);
        
        // Create or update owner operator details in the dedicated table
        $ownerOperatorDetail = OwnerOperatorDetail::updateOrCreate(
            ['user_driver_detail_id' => $userDriverDetail->id],
            [
                'owner_name' => $this->owner_name ?? null,
                'owner_phone' => $this->owner_phone ?? null,
                'owner_email' => $this->owner_email ?? null,
                'owner_dba' => $this->owner_dba ?? null,
                'owner_address' => $this->owner_address ?? null,
                'owner_contact_person' => $this->owner_contact_person ?? null,
                'owner_fein' => $this->owner_fein ?? null,
                'contract_agreed' => $this->contract_agreed ?? false,
            ]
        );
        
        Log::info('ApplicationStep: OwnerOperatorDetail guardado', [
            'owner_operator_detail_id' => $ownerOperatorDetail->id,
            'user_driver_detail_id' => $ownerOperatorDetail->user_driver_detail_id,
            'was_recently_created' => $ownerOperatorDetail->wasRecentlyCreated
        ]);
        
        // Create application detail for owner operator
        $applicationDetail = $application->details()->updateOrCreate(
            ['detail_type' => 'owner_operator'],
            [
                'detail_id' => $ownerOperatorDetail->id,
                'detail_type' => 'owner_operator',
            ]
        );
        
        Log::info('ApplicationStep: DriverApplicationDetail para owner_operator guardado', [
            'application_detail_id' => $applicationDetail->id,
            'detail_type' => $applicationDetail->detail_type,
            'detail_id' => $applicationDetail->detail_id,
            'was_recently_created' => $applicationDetail->wasRecentlyCreated
        ]);
    }
    
    /**
     * Process Third Party vehicles
     */
    protected function processThirdPartyVehicles($application, $userDriverDetail)
    {
        Log::info('ApplicationStep: Iniciando processThirdPartyVehicles', [
            'user_driver_detail_id' => $userDriverDetail->id,
            'application_id' => $application->id,
            'third_party_name' => $this->third_party_name ?? null,
            'third_party_phone' => $this->third_party_phone ?? null,
            'third_party_email' => $this->third_party_email ?? null
        ]);
        
        // Create or update third party details in the dedicated table
        $thirdPartyDetail = ThirdPartyDetail::updateOrCreate(
            ['user_driver_detail_id' => $userDriverDetail->id],
            [
                'third_party_name' => $this->third_party_name ?? null,
                'third_party_phone' => $this->third_party_phone ?? null,
                'third_party_email' => $this->third_party_email ?? null,
                'third_party_dba' => $this->third_party_dba ?? null,
                'third_party_address' => $this->third_party_address ?? null,
                'third_party_contact' => $this->third_party_contact ?? null,
                'third_party_fein' => $this->third_party_fein ?? null,
                'email_sent' => $this->email_sent ?? false,
            ]
        );
        
        Log::info('ApplicationStep: ThirdPartyDetail guardado', [
            'third_party_detail_id' => $thirdPartyDetail->id,
            'user_driver_detail_id' => $thirdPartyDetail->user_driver_detail_id,
            'was_recently_created' => $thirdPartyDetail->wasRecentlyCreated
        ]);
        
        // Create application detail for third party
        $applicationDetail = $application->details()->updateOrCreate(
            ['detail_type' => 'third_party_driver'],
            [
                'detail_id' => $thirdPartyDetail->id,
                'detail_type' => 'third_party_driver',
            ]
        );
        
        Log::info('ApplicationStep: DriverApplicationDetail para third_party_driver guardado', [
            'application_detail_id' => $applicationDetail->id,
            'detail_type' => $applicationDetail->detail_type,
            'detail_id' => $applicationDetail->detail_id,
            'was_recently_created' => $applicationDetail->wasRecentlyCreated
        ]);
    }
    
    /**
     * Process Company Driver information
     */
    protected function processCompanyDriverInfo($application, $userDriverDetail)
    {
        Log::info('ApplicationStep: Iniciando processCompanyDriverInfo', [
            'user_driver_detail_id' => $userDriverDetail->id,
            'application_id' => $application->id,
            'preferred_routes' => $this->preferred_routes ?? null,
            'willing_to_relocate' => $this->willing_to_relocate ?? null,
            'max_time_away' => $this->max_time_away ?? null
        ]);
        
        // Create or update company driver details in the dedicated table
        // First, get or create the VehicleDriverAssignment for this company driver
        $assignment = \App\Models\VehicleDriverAssignment::where('user_driver_detail_id', $userDriverDetail->id)
            ->where('status', 'pending')
            ->whereNull('vehicle_id') // Company drivers have NULL vehicle_id initially
            ->first();
            
        if (!$assignment) {
            Log::info('ApplicationStep: No se encontró assignment para company driver, creando uno nuevo', [
                'user_driver_detail_id' => $userDriverDetail->id
            ]);
            
            // Create a new VehicleDriverAssignment for company driver
            $assignment = \App\Models\VehicleDriverAssignment::create([
                'user_driver_detail_id' => $userDriverDetail->id,
                'vehicle_id' => null, // Company drivers don't have vehicles initially
                'status' => 'pending',
                'start_date' => now()->format('Y-m-d'),
            ]);
            
            Log::info('ApplicationStep: VehicleDriverAssignment creado para company driver', [
                'assignment_id' => $assignment->id,
                'user_driver_detail_id' => $userDriverDetail->id
            ]);
        }
        
        $companyDriverDetail = CompanyDriverDetail::updateOrCreate(
            ['assignment_id' => $assignment->id],
            [
                'preferred_routes' => $this->preferred_routes ?? null,
                'willing_to_relocate' => $this->willing_to_relocate ?? false,
                'max_time_away' => $this->max_time_away ?? null,
            ]
        );
        
        Log::info('ApplicationStep: CompanyDriverDetail guardado', [
            'company_driver_detail_id' => $companyDriverDetail->id,
            'user_driver_detail_id' => $companyDriverDetail->user_driver_detail_id,
            'was_recently_created' => $companyDriverDetail->wasRecentlyCreated
        ]);
        
        // Create or update application detail for company driver
        $applicationDetail = $application->details()->updateOrCreate(
            ['driver_application_id' => $application->id],
            [
                'applying_position' => 'company_driver',
                'applying_location' => $this->applying_location,
                'eligible_to_work' => $this->eligible_to_work,
                'can_speak_english' => $this->can_speak_english,
                'has_twic_card' => $this->has_twic_card,
                'twic_expiration_date' => $this->has_twic_card ? DateHelper::toDatabase($this->twic_expiration_date) : null,
                'expected_pay' => $this->expected_pay,
                'how_did_hear' => $this->how_did_hear,
                'how_did_hear_other' => $this->how_did_hear_other,
                'referral_employee_name' => $this->referral_employee_name,
            ]
        );
        
        Log::info('ApplicationStep: DriverApplicationDetail para company_driver guardado', [
            'application_detail_id' => $applicationDetail->id,
            'applying_position' => $applicationDetail->applying_position,
            'driver_application_id' => $applicationDetail->driver_application_id,
            'was_recently_created' => $applicationDetail->wasRecentlyCreated
        ]);
    }
    
    /**
     * Updated save method to handle multiple driver types
     */
    protected function saveApplicationWithMultipleTypes()
    {
        try {
            Log::info('ApplicationStep: Iniciando saveApplicationWithMultipleTypes', [
                'driver_id' => $this->driverId,
                'applying_position' => $this->applying_position,
                'vehicle_checkboxes' => $this->vehicleTypeCheckboxes
            ]);
            
            DB::beginTransaction();
            
            if (!$this->validateStepCompletion()) {
                Log::warning('ApplicationStep: Validación de paso fallida');
                return false;
            }
            
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                Log::error('ApplicationStep: Driver no encontrado', ['driver_id' => $this->driverId]);
                throw new \Exception('Driver not found');
            }
            
            Log::info('ApplicationStep: Driver encontrado', [
                'driver_id' => $userDriverDetail->id,
                'user_id' => $userDriverDetail->user_id,
                'carrier_id' => $userDriverDetail->carrier_id
            ]);
            
            // Create or update application with new structure
            $applicationData = [
                'applying_position' => $this->applying_position,
                'applying_position_other' => $this->applying_position_other,
                'applying_location' => $this->applying_location,
                'eligible_to_work' => $this->eligible_to_work,
                'can_speak_english' => $this->can_speak_english,
                'has_twic_card' => $this->has_twic_card,
                'twic_expiration_date' => $this->has_twic_card ? DateHelper::toDatabase($this->twic_expiration_date) : null,
                'expected_pay' => $this->expected_pay,
                'how_did_hear' => $this->how_did_hear,
                'how_did_hear_other' => $this->how_did_hear_other,
                'referral_employee_name' => $this->referral_employee_name,
                'has_work_history' => $this->has_work_history,
            ];
            
            Log::info('ApplicationStep: Datos de aplicación a guardar', $applicationData);
            
            $application = DriverApplication::updateOrCreate(
                ['user_id' => $userDriverDetail->user_id],
                $applicationData
            );
            
            Log::info('ApplicationStep: DriverApplication guardada', [
                'application_id' => $application->id,
                'user_id' => $application->user_id,
                'was_recently_created' => $application->wasRecentlyCreated
            ]);
            
            // Process vehicle types based on checkboxes (independent from applying_position)
            Log::info('ApplicationStep: Procesando tipos de vehículos', [
                'owner_operator_checked' => $this->vehicleTypeCheckboxes['owner_operator'],
                'third_party_checked' => $this->vehicleTypeCheckboxes['third_party_driver'],
                'company_driver_checked' => $this->vehicleTypeCheckboxes['company_driver']
            ]);
            
            if ($this->vehicleTypeCheckboxes['owner_operator']) {
                Log::info('ApplicationStep: Procesando owner operator vehicles');
                $this->processOwnerOperatorVehicles($application, $userDriverDetail);
            }
            
            if ($this->vehicleTypeCheckboxes['third_party_driver']) {
                Log::info('ApplicationStep: Procesando third party vehicles');
                $this->processThirdPartyVehicles($application, $userDriverDetail);
            }
            
            if ($this->vehicleTypeCheckboxes['company_driver']) {
                Log::info('ApplicationStep: Procesando company driver info');
                $this->processCompanyDriverInfo($application, $userDriverDetail);
            }
            
            // Handle work histories
            if ($this->has_work_history) {
                Log::info('ApplicationStep: Procesando historiales de trabajo', [
                    'work_histories_count' => count($this->work_histories ?? [])
                ]);
                $this->processWorkHistories($userDriverDetail);
            } else {
                Log::info('ApplicationStep: Eliminando historiales de trabajo existentes');
                $userDriverDetail->workHistories()->delete();
            }
            
            // Update current step
            Log::info('ApplicationStep: Actualizando paso actual', [
                'driver_id' => $userDriverDetail->id,
                'new_step' => 3
            ]);
            $userDriverDetail->update(['current_step' => 3]);
            
            DB::commit();
            Log::info('ApplicationStep: Transacción completada exitosamente');
            
            session()->flash('message', 'Application information saved successfully.');
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ApplicationStep: Error guardando aplicación con múltiples tipos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'driver_id' => $this->driverId,
                'applying_position' => $this->applying_position,
                'vehicle_checkboxes' => $this->vehicleTypeCheckboxes,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            session()->flash('error', 'Error saving application: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process work histories
     */
    protected function processWorkHistories($userDriverDetail)
    {
        $existingWorkHistoryIds = $userDriverDetail->workHistories()->pluck('id')->toArray();
        $updatedWorkHistoryIds = [];
        
        foreach ($this->work_histories as $historyData) {
            $historyId = $historyData['id'] ?? null;
            
            if ($historyId) {
                $history = $userDriverDetail->workHistories()->find($historyId);
                if ($history) {
                    $history->update([
                        'previous_company' => $historyData['previous_company'],
                        'start_date' => DateHelper::toDatabase($historyData['start_date']),
                        'end_date' => DateHelper::toDatabase($historyData['end_date']),
                        'location' => $historyData['location'],
                        'position' => $historyData['position'],
                        'reason_for_leaving' => $historyData['reason_for_leaving'] ?? null,
                        'reference_contact' => $historyData['reference_contact'] ?? null,
                    ]);
                    $updatedWorkHistoryIds[] = $history->id;
                }
            } else {
                $history = $userDriverDetail->workHistories()->create([
                    'previous_company' => $historyData['previous_company'],
                    'start_date' => DateHelper::toDatabase($historyData['start_date']),
                    'end_date' => DateHelper::toDatabase($historyData['end_date']),
                    'location' => $historyData['location'],
                    'position' => $historyData['position'],
                    'reason_for_leaving' => $historyData['reason_for_leaving'] ?? null,
                    'reference_contact' => $historyData['reference_contact'] ?? null,
                ]);
                $updatedWorkHistoryIds[] = $history->id;
            }
        }
        
        // Delete histories that are no longer needed
        $historiesToDelete = array_diff($existingWorkHistoryIds, $updatedWorkHistoryIds);
        if (!empty($historiesToDelete)) {
            $userDriverDetail->workHistories()->whereIn('id', $historiesToDelete)->delete();
        }
    }

    
    // Add work history
    public function addWorkHistory()
    {
        $this->work_histories[] = $this->getEmptyWorkHistory();
    }

    // Remove work history
    public function removeWorkHistory($index)
    {
        if (count($this->work_histories) > 1) {
            unset($this->work_histories[$index]);
            $this->work_histories = array_values($this->work_histories);
        }
    }

    // Get empty work history structure
    protected function getEmptyWorkHistory()
    {
        return [
            'previous_company' => '',
            'start_date' => '',
            'end_date' => '',
            'location' => '',
            'position' => '',
            'reason_for_leaving' => '',
            'reference_contact' => ''
        ];
    }

    // Next step
    public function next()
    {
        // Debug statement removed
        // Step completion validation - ensure previous steps are completed
        if (!$this->validateStepCompletion()) {
            return;
        }
        
        // Verificar si tiene third_party_driver seleccionado y no se ha enviado el correo
        if (isset($this->vehicleTypeCheckboxes['third_party_driver']) && 
            $this->vehicleTypeCheckboxes['third_party_driver'] && 
            !$this->email_sent && 
            $this->third_party_email && $this->third_party_name && $this->third_party_phone) {
            
            // Añadir un error de validación personalizado
            $this->addError('third_party_email', 'You must send the email to the third party company representative before proceeding.');
            return;
        }
        
        // Full validation using unified validation method
        $this->validateStep();

        // Save to database
        if ($this->driverId) {
            if (!$this->saveApplicationWithMultipleTypes()) {
                return; // Stop if save failed
            }
        }

        // Move to next step
        $this->dispatch('nextStep');
    }
    
    // Previous step
    public function previous()
    {
        // Use unified validation method for consistency
        $this->validateStep(true); // partial validation
        
        // Save to database
        if ($this->driverId) {
            $this->saveApplicationWithMultipleTypes();
        }

        $this->dispatch('prevStep');
    }
   
    // Save and exit
    public function saveAndExit()
    {
        // Use unified validation method for consistency
        $this->validateStep(true); // partial validation

        // Save to database
        if ($this->driverId) {
            $this->saveApplicationWithMultipleTypes();
        }
        
        $this->dispatch('saveAndExit');
    }
    
    /**
     * Envía un correo electrónico al representante de la empresa de terceros
     */
    public function sendThirdPartyEmail()
    {
        Log::info('ApplicationStep: Iniciando envío de correo a tercero', [
            'third_party_email' => $this->third_party_email,
            'driver_id' => $this->driverId,
            'third_party_name' => $this->third_party_name,
            'third_party_phone' => $this->third_party_phone,
            'vehicle_vin' => $this->vehicle_vin
        ]);
        
        // Validar todos los campos necesarios antes de continuar
        Log::info('ApplicationStep: Iniciando validación de campos');
        
        $this->validate([
            // Campos del tercero
            'third_party_email' => 'required|email',
            'third_party_name' => 'required|string',
            'third_party_phone' => 'required|string',
            'third_party_dba' => 'nullable|string',
            'third_party_address' => 'nullable|string',
            'third_party_contact' => 'nullable|string',
            'third_party_fein' => 'nullable|string',
            
            // Campos del vehículo
            'vehicle_make' => 'required|string',
            'vehicle_model' => 'required|string',
            'vehicle_year' => 'required|integer',
            'vehicle_vin' => 'required|string',
            'vehicle_type' => 'required|string',
            'vehicle_fuel_type' => 'required|string',
            'vehicle_registration_state' => 'required|string',
            'vehicle_registration_number' => 'required|string',
            'vehicle_registration_expiration_date' => 'required|date'
        ]);

        try {
            DB::beginTransaction();
            
            // Obtener el usuario y la aplicación del conductor
            Log::info('ApplicationStep: Buscando datos del conductor', ['driver_id' => $this->driverId]);
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                Log::error('ApplicationStep: Driver no encontrado', ['driver_id' => $this->driverId]);
                throw new \Exception('Driver not found');
            }
            
            Log::info('ApplicationStep: Driver encontrado, buscando aplicación', ['driver_id' => $this->driverId]);
            $application = $userDriverDetail->application;
            if (!$application) {
                Log::error('ApplicationStep: Aplicación del driver no encontrada', ['driver_id' => $this->driverId]);
                throw new \Exception('Driver application not found');
            }
            
            Log::info('ApplicationStep: Aplicación encontrada', ['application_id' => $application->id]);
            
            // Verificar si ya existe un vehículo con el mismo VIN o si se seleccionó uno existente
            $vehicle = null;
            
            if ($this->vehicle_id) {
                // Si ya tenemos un ID de vehículo, intentamos obtenerlo
                $vehicle = Vehicle::find($this->vehicle_id);
                
                if ($vehicle) {
                    // Determinar el tipo de vehículo (third_party en este caso)
                    $driverType = 'third_party';
                    
                    // Actualizar el vehículo existente
                    $vehicle->update([
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
                        'registration_state' => $this->vehicle_registration_state ?: $this->applying_location,
                        'registration_number' => $this->vehicle_registration_number ?: 'Pending',
                        'registration_expiration_date' => $this->vehicle_registration_expiration_date 
                            ? Carbon::parse($this->vehicle_registration_expiration_date) 
                            : now()->addYear(),
                        'permanent_tag' => $this->vehicle_permanent_tag,
                        'location' => $this->vehicle_location,
                        'driver_type' => $driverType,
                        'ownership_type' => 'third-party',
                        'user_id' => $userDriverDetail->user_id,
                        'status' => 'pending',
                        'notes' => $this->vehicle_notes,
                    ]);
                    
                    Log::info('Vehículo actualizado exitosamente para third party', ['id' => $vehicle->id]);
                }
            }
            
            // Si no tenemos un vehículo válido, creamos uno nuevo
            if (!$vehicle) {
                // Verificar si ya existe un vehículo con el mismo VIN
                $existingVehicle = Vehicle::where('vin', $this->vehicle_vin)->first();
                
                if (!$existingVehicle) {
                    // Preparar datos para el registro de vehículo
                    $registrationDate = $this->vehicle_registration_expiration_date 
                        ? Carbon::parse($this->vehicle_registration_expiration_date) 
                        : now()->addYear();
                    
                    // Determinar el tipo de vehículo (third_party en este caso)
                    $driverType = 'third_party';
                    
                    // Crear nuevo vehículo
                    $vehicle = Vehicle::create([
                        'carrier_id' => $userDriverDetail->carrier_id,
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
                        'registration_state' => $this->vehicle_registration_state ?: $this->applying_location,
                        'registration_number' => $this->vehicle_registration_number ?: 'Pending',
                        'registration_expiration_date' => $registrationDate,
                        'permanent_tag' => $this->vehicle_permanent_tag,
                        'location' => $this->vehicle_location,
                        'ownership_type' => 'third-party',
                        'driver_type' => $driverType,
                        'user_id' => $userDriverDetail->user_id,
                        'status' => 'pending',
                        'notes' => $this->vehicle_notes,
                    ]);
                    
                    Log::info('Vehículo creado exitosamente para third party', ['id' => $vehicle->id]);
                } else {
                    // Si ya existe un vehículo con el mismo VIN, lo usamos
                    $vehicle = $existingVehicle;
                    Log::info('Usando vehículo existente con el mismo VIN', ['id' => $vehicle->id]);
                }
            }
            
            // Verificar que tenemos un vehículo válido
            if (!$vehicle || !$vehicle->id) {
                throw new \Exception('No se pudo crear o encontrar el vehículo');
            }
            
            // Guardar el ID del vehículo
            $this->vehicle_id = $vehicle->id;
            
            // Los driver_application_details se crearán cuando el usuario complete el formulario principal
            // No se crean aquí porque los campos requeridos no están disponibles en el formulario de terceros
            
            // Actualizar los detalles específicos de Third Party en la tabla correspondiente
            $thirdPartyDetails = $application->thirdPartyDetail()->updateOrCreate(
                [],
                [
                    'third_party_name' => $this->third_party_name,
                    'third_party_phone' => $this->third_party_phone,
                    'third_party_email' => $this->third_party_email,
                    'third_party_dba' => $this->third_party_dba,
                    'third_party_address' => $this->third_party_address,
                    'third_party_contact' => $this->third_party_contact,
                    'third_party_fein' => $this->third_party_fein,
                    'email_sent' => true,
                    'vehicle_id' => $vehicle->id,
                ]
            );
            
            Log::info('Detalles de aplicación actualizados con éxito', [
                'application_id' => $application->id,
                'vehicle_id' => $vehicle->id,
                'third_party_name' => $this->third_party_name,
                'third_party_email' => $this->third_party_email
            ]);
            
            // Preparar los datos del vehículo para el correo
            $vehicleData = [
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'vin' => $vehicle->vin,
                'type' => $vehicle->type,
                'registration_number' => $vehicle->registration_number,
                'registration_state' => $vehicle->registration_state,
            ];
            
            // Generar token de verificación
            $token = \App\Models\VehicleVerificationToken::generateToken();
            $expiresAt = now()->addDays(7);
            
            // Guardar el token de verificación
            $verification = \App\Models\VehicleVerificationToken::create([
                'token' => $token,
                'driver_application_id' => $application->id,
                'vehicle_id' => $vehicle->id,
                'third_party_name' => $this->third_party_name,
                'third_party_email' => $this->third_party_email,
                'third_party_phone' => $this->third_party_phone,
                'expires_at' => $expiresAt,
            ]);
            
            // Verificar que el token se guardó correctamente antes de continuar
            if (!$verification || !$verification->id) {
                throw new \Exception('No se pudo crear el token de verificación');
            }
            
            Log::info('Token de verificación creado exitosamente', [
                'token' => $token,
                'verification_id' => $verification->id,
                'application_id' => $application->id,
                'vehicle_id' => $vehicle->id
            ]);
            
            // Marcar como enviado antes del commit
            $this->email_sent = true;
            
            Log::info('ApplicationStep: Realizando commit de la transacción antes del envío de correo');
            // Commit de la transacción ANTES de enviar el correo
            DB::commit();
            
            Log::info('ApplicationStep: Transacción completada exitosamente, preparando envío de correo');
            
            // Log configuración de correo antes del envío
            Log::info('Configuración de correo SMTP', [
                'mail_mailer' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host'),
                'mail_port' => config('mail.mailers.smtp.port'),
                'mail_encryption' => config('mail.mailers.smtp.encryption'),
                'mail_username' => config('mail.mailers.smtp.username'),
                'mail_from_address' => config('mail.from.address'),
                'recipient_email' => $this->third_party_email
            ]);
            
            // Enviar correo electrónico DESPUÉS del commit para evitar problemas de timing
            Log::info('ApplicationStep: Iniciando envío de correo electrónico', [
                'recipient' => $this->third_party_email,
                'driver_name' => $userDriverDetail->user->name . ' ' . $userDriverDetail->last_name,
                'token' => $token,
                'vehicle_data' => $vehicleData
            ]);
            
            try {
                Mail::to($this->third_party_email)
                    ->send(new ThirdPartyVehicleVerification(
                        $this->third_party_name,
                        $userDriverDetail->user->name . ' ' . $userDriverDetail->last_name,
                        $vehicleData,
                        $token,
                        $this->driverId,
                        $application->id
                    ));
                    
                Log::info('ApplicationStep: Correo enviado exitosamente después del commit', [
                    'recipient' => $this->third_party_email,
                    'token' => $token,
                    'verification_id' => $verification->id
                ]);
            } catch (\Swift_TransportException $e) {
                Log::error('ApplicationStep: Error de transporte SMTP', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'recipient' => $this->third_party_email,
                    'token' => $token,
                    'mail_config' => [
                        'mailer' => config('mail.default'),
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port')
                    ]
                ]);
                // No lanzar excepción aquí ya que los datos ya están guardados
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Los datos se guardaron correctamente, pero hubo un error al enviar el correo: ' . $e->getMessage()
                ]);
                return;
            } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
                Log::error('ApplicationStep: Error de transporte Symfony Mailer', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'recipient' => $this->third_party_email,
                    'token' => $token,
                    'mail_config' => [
                        'mailer' => config('mail.default'),
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port')
                    ]
                ]);
                // No lanzar excepción aquí ya que los datos ya están guardados
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Los datos se guardaron correctamente, pero hubo un error al enviar el correo: ' . $e->getMessage()
                ]);
                return;
            } catch (\Exception $e) {
                Log::error('ApplicationStep: Error general al enviar correo', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'recipient' => $this->third_party_email,
                    'token' => $token,
                    'trace' => $e->getTraceAsString(),
                    'mail_config' => [
                        'mailer' => config('mail.default'),
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port')
                    ]
                ]);
                // No lanzar excepción aquí ya que los datos ya están guardados
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Los datos se guardaron correctamente, pero hubo un error al enviar el correo: ' . $e->getMessage()
                ]);
                return;
            }
            
            Log::info('ApplicationStep: Proceso de envío de correo completado exitosamente');
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Vehicle information sent successfully to ' . $this->third_party_email
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ApplicationStep: Error crítico al enviar correo a tercero', [
                'error' => $e->getMessage(),
                'email' => $this->third_party_email,
                'driver_id' => $this->driverId,
                'vehicle_id' => $this->vehicle_id,
                'vehicle_vin' => $this->vehicle_vin,
                'third_party_name' => $this->third_party_name,
                'third_party_phone' => $this->third_party_phone,
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            // Mostrar mensaje de error más detallado para facilitar la depuración
            $errorMessage = 'Error sending email: ' . $e->getMessage();
            if (app()->environment('local', 'development', 'staging')) {
                $errorMessage .= ' (Check logs for more details)';
            }
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $errorMessage
            ]);
            
            // Mostrar mensaje en la consola para depuración
            Log::debug('Datos del correo a tercero:', [
                'third_party_email' => $this->third_party_email,
                'third_party_name' => $this->third_party_name,
                'vehicle_data' => isset($vehicleData) ? $vehicleData : null,
                'token' => isset($token) ? $token : null
            ]);
        }
    }
    /**
     * Synchronize applying_position with vehicle ownership_type
     */
    protected function syncApplyingPositionWithOwnership($applyingPosition)
    {
        try {
            // Get corresponding ownership_type using Constants mapping
            $ownershipType = Constants::mapApplyingPositionToOwnership($applyingPosition);
            
            // If there's a vehicle associated, update its ownership_type
            if ($this->vehicle_id) {
                $vehicle = \App\Models\Admin\Vehicle\Vehicle::find($this->vehicle_id);
                if ($vehicle && $vehicle->ownership_type !== $ownershipType) {
                    $vehicle->ownership_type = $ownershipType;
                    $vehicle->save();
                    
                    Log::info('Vehicle ownership_type synchronized with applying_position', [
                        'vehicle_id' => $this->vehicle_id,
                        'applying_position' => $applyingPosition,
                        'ownership_type' => $ownershipType
                    ]);
                }
            }
            
            // Update all vehicles associated with this driver if no specific vehicle is selected
            if (!$this->vehicle_id && $this->driverId) {
                $userDriverDetail = UserDriverDetail::find($this->driverId);
                if ($userDriverDetail) {
                    $vehicles = \App\Models\Admin\Vehicle\Vehicle::where('user_driver_detail_id', $userDriverDetail->id)->get();
                    foreach ($vehicles as $vehicle) {
                        if ($vehicle->ownership_type !== $ownershipType) {
                            $vehicle->ownership_type = $ownershipType;
                            $vehicle->save();
                            
                            Log::info('Driver vehicle ownership_type synchronized', [
                                'vehicle_id' => $vehicle->id,
                                'driver_id' => $this->driverId,
                                'applying_position' => $applyingPosition,
                                'ownership_type' => $ownershipType
                            ]);
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error synchronizing applying_position with ownership_type', [
                'applying_position' => $applyingPosition,
                'vehicle_id' => $this->vehicle_id,
                'driver_id' => $this->driverId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Map applying_position to ownership_type using Constants helper
     */
    public function mapApplyingPositionToOwnership($applyingPosition)
    {
        return Constants::mapApplyingPositionToOwnership($applyingPosition);
    }
    
    /**
     * Map ownership_type to applying_position using Constants helper
     */
    public function mapOwnershipToApplyingPosition($ownershipType)
    {
        return Constants::mapOwnershipToApplyingPosition($ownershipType);
    }
    
    /**
     * Validate consistency between ownership_type and applying_position
     */
    public function validateOwnershipConsistency($ownershipType = null, $applyingPosition = null)
    {
        $ownershipType = $ownershipType ?? ($this->vehicle_id ? \App\Models\Admin\Vehicle\Vehicle::find($this->vehicle_id)->ownership_type ?? null : null);
        $applyingPosition = $applyingPosition ?? $this->applying_position;
        
        if (!$ownershipType || !$applyingPosition) {
            return [
                'is_consistent' => false,
                'error' => 'Missing ownership_type or applying_position for validation'
            ];
        }
        
        $expectedApplyingPosition = Constants::mapOwnershipToApplyingPosition($ownershipType);
        $expectedOwnershipType = Constants::mapApplyingPositionToOwnership($applyingPosition);
        
        return [
            'is_consistent' => ($expectedApplyingPosition === $applyingPosition && $expectedOwnershipType === $ownershipType),
            'expected_applying_position' => $expectedApplyingPosition,
            'expected_ownership_type' => $expectedOwnershipType,
            'current_applying_position' => $applyingPosition,
            'current_ownership_type' => $ownershipType
        ];
    }

    // Render
    public function render()
    {
        return view('livewire.driver.steps.application-step', [
            'usStates' => Constants::usStates(),
            'driverPositions' => Constants::driverPositions(),
            'referralSources' => Constants::referralSources()
        ]);
    }

}
            