<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use App\Helpers\Constants;
use Illuminate\Support\Carbon;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Admin\Vehicle\Vehicle;
use App\Mail\ThirdPartyVehicleVerification;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverApplicationDetail;

class DriverApplicationStep extends Component
{
    // Application Details
    public $applying_position;
    public $applying_position_other;
    public $applying_location;
    public $eligible_to_work = true;
    public $can_speak_english = true;
    public $has_twic_card = false;
    public $twic_expiration_date;
    public $expected_pay;
    public $how_did_hear = 'internet';
    public $how_did_hear_other;
    public $referral_employee_name;
    
    // Owner Operator fields
    public $owner_name;
    public $owner_phone;
    public $owner_email;
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
        
        // Add rules based on the selected position
        if ($this->applying_position === 'owner_operator') {
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
        } elseif ($this->applying_position === 'third_party_driver') {
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

    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;

        if ($this->driverId) {
            $this->loadExistingData();
            $this->loadExistingVehicles();
        } else {
            // Initialize work history array with one empty record
            $this->work_histories = [
                $this->getEmptyWorkHistory()
            ];
        }
    }
    
    /**
     * Actualiza los campos cuando cambia la posición seleccionada
     */
    public function updatedApplyingPosition($value)
    {
        if ($value === 'owner_operator') {
            // Auto-fill owner fields if driver info is available
            $this->autoFillOwnerFields();
        } else {
            // Clear owner fields if not owner operator
            $this->owner_name = null;
            $this->owner_phone = null;
            $this->owner_email = null;
            $this->contract_agreed = false;
        }
        
        // Reload existing vehicles when position changes
        if ($this->driverId && ($value === 'owner_operator' || $value === 'third_party_driver')) {
            $this->loadExistingVehicles();
        }
    }
    
    /**
     * Load existing vehicles for the driver
     */
    protected function loadExistingVehicles()
    {
        // Determinar el tipo de vehículo según la posición seleccionada
        $driverType = $this->applying_position === 'owner_operator' ? 'owner_operator' : 
                      ($this->applying_position === 'third_party_driver' ? 'third_party' : 'company');
        
        // Cargar los vehículos que pertenecen específicamente a este driver
        $driverVehicles = Vehicle::where('user_driver_detail_id', $this->driverId)
                                ->where('driver_type', $driverType)
                                ->get();
        
        // Si no hay vehículos asociados directamente al driver, cargar vehículos disponibles del tipo correcto
        if ($driverVehicles->isEmpty()) {
            // Obtener el carrier_id del driver
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if ($userDriverDetail && $userDriverDetail->carrier_id) {
                // Cargar vehículos del mismo carrier que no estén asignados a otro driver y sean del tipo correcto
                $this->existingVehicles = Vehicle::where('carrier_id', $userDriverDetail->carrier_id)
                    ->where('driver_type', $driverType)
                    ->where(function($query) {
                        $query->whereNull('user_driver_detail_id')
                              ->orWhere('user_driver_detail_id', $this->driverId);
                    })
                    ->get();
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
            $this->vehicle_registration_expiration_date = $vehicle->registration_expiration_date ? $vehicle->registration_expiration_date->format('Y-m-d') : null;
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
            $this->twic_expiration_date = $details->twic_expiration_date ? $details->twic_expiration_date->format('Y-m-d') : null;
            $this->expected_pay = $details->expected_pay;
            $this->how_did_hear = $details->how_did_hear;
            $this->how_did_hear_other = $details->how_did_hear_other;
            $this->referral_employee_name = $details->referral_employee_name;
            $this->has_work_history = (bool)($details->has_work_history ?? false);
            
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
                $this->vehicle_registration_expiration_date = $vehicle->registration_expiration_date ? $vehicle->registration_expiration_date->format('Y-m-d') : null;
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
                    'start_date' => $history->start_date ? $history->start_date->format('Y-m-d') : null,
                    'end_date' => $history->end_date ? $history->end_date->format('Y-m-d') : null,
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
                                ? Carbon::parse($this->vehicle_registration_expiration_date) 
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
                        ? Carbon::parse($this->vehicle_registration_expiration_date) 
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
                        'user_driver_detail_id' => $this->driverId,
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
                    'twic_expiration_date' => $this->has_twic_card ? $this->twic_expiration_date : null,
                    'expected_pay' => $this->expected_pay,
                    'how_did_hear' => $this->how_did_hear,
                    'how_did_hear_other' => $this->how_did_hear === 'other' ? $this->how_did_hear_other : null,
                    'referral_employee_name' => $this->how_did_hear === 'employee_referral' ? $this->referral_employee_name : null,
                    'has_work_history' => $this->has_work_history,
                    // Vehicle relationship
                    'vehicle_id' => $this->vehicle_id,
                ]
            );
            
            // Guardar detalles específicos de Owner Operator en la nueva tabla
            if ($this->applying_position === 'owner_operator') {
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
                
                Log::info('Detalles de Owner Operator guardados', [
                    'application_id' => $application->id,
                    'owner_name' => $this->owner_name
                ]);
            }
            
            // Guardar detalles específicos de Third Party en la nueva tabla
            if ($this->applying_position === 'third_party_driver') {
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
                
                Log::info('Detalles de Third Party guardados', [
                    'application_id' => $application->id,
                    'third_party_name' => $this->third_party_name
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
                                'start_date' => $historyData['start_date'],
                                'end_date' => $historyData['end_date'],
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
                            'start_date' => $historyData['start_date'],
                            'end_date' => $historyData['end_date'],
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
        // Verificar si es third_party_driver y no se ha enviado el correo
        if ($this->applying_position === 'third_party_driver' && !$this->email_sent && 
            $this->third_party_email && $this->third_party_name && $this->third_party_phone) {
            
            // Añadir un error de validación personalizado
            $this->addError('third_party_email', 'You must send the email to the third party company representative before proceeding.');
            return;
        }
        
        // Full validation
        $this->validate($this->rules());

        // Save to database
        if ($this->driverId) {
            $this->saveApplicationDetails();
        }

        // Move to next step
        $this->dispatch('nextStep');
    }

    // Previous step
    public function previous()
    {
        // Basic save before going back
        if ($this->driverId) {
            $this->validate($this->partialRules());
            $this->saveApplicationDetails();
        }

        $this->dispatch('prevStep');
    }

    // Save and exit
    public function saveAndExit()
    {
        // Basic validation
        $this->validate($this->partialRules());

        // Save to database
        if ($this->driverId) {
            $this->saveApplicationDetails();
        }
        
        $this->dispatch('saveAndExit');
    }
    
    /**
     * Envía un correo electrónico al representante de la empresa de terceros
     */
    public function sendThirdPartyEmail()
    {
        Log::info('Iniciando envío de correo a tercero', [
            'third_party_email' => $this->third_party_email,
            'driver_id' => $this->driverId
        ]);
        
        // Validar todos los campos necesarios antes de continuar
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
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }
            
            $application = $userDriverDetail->application;
            if (!$application) {
                throw new \Exception('Driver application not found');
            }
            
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
                        'user_driver_detail_id' => $this->driverId,
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
                        'user_driver_detail_id' => $this->driverId,
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
            
            // Solo actualizamos la tabla third_party_details, no driver_application_details
            // Los datos generales se guardarán cuando el usuario use next/previous step, no aquí
            
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
            
            // Enviar correo electrónico
            Mail::to($this->third_party_email)
                ->send(new ThirdPartyVehicleVerification(
                    $this->third_party_name,
                    $userDriverDetail->user->name . ' ' . $userDriverDetail->last_name,
                    $vehicleData,
                    $token,
                    $this->driverId,
                    $application->id
                ));
            
            // Marcar como enviado
            $this->email_sent = true;
            
            DB::commit();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Vehicle information sent successfully to ' . $this->third_party_email
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al enviar correo a tercero', [
                'error' => $e->getMessage(),
                'email' => $this->third_party_email,
                'driver_id' => $this->driverId,
                'vehicle_id' => $this->vehicle_id,
                'vehicle_vin' => $this->vehicle_vin,
                'third_party_name' => $this->third_party_name,
                'third_party_phone' => $this->third_party_phone,
                'trace' => $e->getTraceAsString()
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
    // Render
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-application-step', [
            'usStates' => Constants::usStates(),
            'driverPositions' => Constants::driverPositions(),
            'referralSources' => Constants::referralSources()
        ]);
    }
}
