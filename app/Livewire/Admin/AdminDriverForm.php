<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\UserDriverDetail;
use App\Models\Carrier;
use App\Models\Vehicle\VehicleType;
use App\Models\User;
use App\Models\Admin\Vehicle\Vehicle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverAddress;
use App\Models\Admin\Driver\DriverEmploymentHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Helpers\DateHelper;
use App\Traits\DriverValidationTrait;

class AdminDriverForm extends Component
{
    use WithFileUploads, DriverValidationTrait;

    // Main properties        
    public $mode = 'create'; // 'create' or 'edit'
    public $currentTab = 'personal';
    
    // Personal Information (based on StepGeneral.php)
    public $name = '';
    public $email = '';
    public $middle_name = '';
    public $last_name = '';
    public $phone = '';
    public $date_of_birth = '';
    public $password = '';
    public $password_confirmation = '';
    public $status = 2; // Default: Pending
    public $terms_accepted = false;
    public $photo;
    public $photo_preview_url = null;
    
    // Address Information (based on AddressStep.php)    
    public $current_address = [
        'address_line1' => '',
        'address_line2' => '',
        'city' => '',
        'state' => '',
        'zip_code' => '',
        'from_date' => '',
        'to_date' => '',
        'lived_3_years' => false
    ];
    
    // Application Information (based on ApplicationStep.php)
    public $applying_position = '';
    public $applying_position_other = '';
    public $applying_location = '';
    public $eligible_to_work = true;
    public $can_speak_english = true;
    public $has_twic_card = false;
    public $twic_expiration_date = '';
    public $expected_pay = '';
    public $how_did_hear = 'internet';
    public $how_did_hear_other = '';
    public $referral_employee_name = '';
    
    // Owner Operator fields
    public $owner_name = '';
    public $owner_phone = '';
    public $owner_email = '';
    public $contract_agreed = false;
    
    // Third Party Company Driver fields
    public $third_party_name = '';
    public $third_party_phone = '';
    public $third_party_email = '';
    public $third_party_dba = '';
    public $third_party_address = '';
    public $third_party_contact = '';
    public $third_party_fein = '';
    
    // Vehicle fields
    public $vehicle_id = null;
    public $vehicle_make = '';
    public $vehicle_model = '';
    public $vehicle_year = '';
    public $vehicle_vin = '';
    public $vehicle_company_unit_number = '';
    public $vehicle_type = 'truck';
    public $vehicle_gvwr = '';
    public $vehicle_tire_size = '';
    public $vehicle_fuel_type = 'diesel';
    public $vehicle_irp_apportioned_plate = false;
    public $vehicle_registration_state = '';
    public $vehicle_registration_number = '';
    public $vehicle_registration_expiration_date = '';
    public $vehicle_permanent_tag = false;
    public $vehicle_location = '';
    public $vehicle_notes = '';
    
    // Work History
    public $has_work_history = false;
    public $work_history = [];
    
    // Existing vehicles
    public $existingVehicles = [];
    public $selectedVehicleId = null;
    public $current_work = [
        'company_name' => '',
        'position' => '',
        'start_date' => '',
        'end_date' => '',
        'reason_for_leaving' => ''
    ];
    
    // Multiple addresses
    public $addresses = [];
    
    // Individual address properties for current address
    public $current_address_line1 = '';
    public $current_address_line2 = '';
    public $current_city = '';
    public $current_state = '';
    public $current_zip_code = '';
    public $current_from_date = '';
    public $lived_three_years = false;
    
    // Previous addresses array
    public $previous_addresses = [];
    
    // Application info
    public $application = [];
    
    // Multiple licenses
    public $licenses = [];
    
    // Medical info
    public $medical = [];
    
    // Multiple accidents
    public $accidents = [];
    
    // Multiple traffic convictions
    public $trafficConvictions = [];
    
    // Multiple training schools
    public $trainingSchools = [];
    
    // Multiple employment history
    public $employmentHistory = [];
    public $employmentCompanies = [];
    
    // Criminal history
    public $criminalHistory = [];
    
    // Component state
    public $carriers = [];
    public $vehicles = [];
    public $available_vehicles = [];
    public $driver = null;
    public $isEditing = false;
    public $carrier = null;
    public $carrier_id = null;
    
    /**
     * Date formatting methods (from StepGeneral.php)
     */
    protected function formatDateForDatabase($date)
    {
        return DateHelper::toDatabase($date);
    }

    protected function formatDateForDisplay($date)
    {
        return DateHelper::toDisplay($date);
    }
        
    
    /**
     * Validation messages
     */
    protected function messages()
    {
        return $this->getValidationMessages();
    }
    
    public $states = [
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
        'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
        'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
        'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
        'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
        'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
        'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
        'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
        'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
        'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
        'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
        'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
        'WI' => 'Wisconsin', 'WY' => 'Wyoming'
    ];

    protected $listeners = [
        'tabChanged' => 'setActiveTab'
    ];

    /**
     * Photo upload handler (from StepGeneral.php)
     */
    public function updatedPhoto()
    {
        Log::info('=== INICIO updatedPhoto() ===', [
            'photo_exists' => !is_null($this->photo),
            'photo_type' => $this->photo ? get_class($this->photo) : 'null',
            'session_id' => session()->getId()
        ]);
        
        if ($this->photo) {
            try {
                // Validate file type
                $this->validate([
                    'photo' => 'image|mimes:jpg,jpeg,png,gif,webp|max:10240',
                ]);

                // Check if image needs compression
                if (\App\Helpers\ImageCompressionHelper::needsCompression($this->photo)) {
                    $originalSize = \App\Helpers\ImageCompressionHelper::formatFileSize($this->photo->getSize());
                    
                    // Compress the image
                    $compressedFile = \App\Helpers\ImageCompressionHelper::compressImage($this->photo);
                    
                    if ($compressedFile) {
                        $this->photo = $compressedFile;
                        $newSize = \App\Helpers\ImageCompressionHelper::formatFileSize($this->photo->getSize());
                        
                        // Send flash message about compression
                        session()->flash('photo_compressed', "Imagen optimizada automáticamente de {$originalSize} a {$newSize}");
                        
                        // Dispatch compression event
                        $this->dispatch('photo-compressed', [
                            'original_size' => $originalSize,
                            'new_size' => $newSize
                        ]);
                    }
                }

                // Generate temporary preview URL
                $this->photo_preview_url = $this->photo->temporaryUrl();
                
                // Save temp file info in session
                $tempFileName = 'temp_photo_' . uniqid() . '.' . $this->photo->getClientOriginalExtension();
                session([
                    'temp_photo_file' => $tempFileName,
                    'temp_photo_original_name' => $this->photo->getClientOriginalName(),
                ]);
                
                // Dispatch event for frontend handling
                $this->dispatch('photo-uploaded', [
                    'url' => $this->photo_preview_url,
                    'name' => $this->photo->getClientOriginalName(),
                    'temp_file' => $tempFileName
                ]);
                
                Log::info('Photo uploaded successfully');
                
            } catch (\Exception $e) {
                $this->reset('photo');
                $this->addError('photo', 'Error processing image: ' . $e->getMessage());
                Log::error('Photo upload error: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Handle date of birth updates
     */
    public function updatedDateOfBirth($value)
    {
        // No conversion needed - keep original MM/DD/YYYY format
    }
    
    private function getUserId()
    {
        return $this->driver ? $this->driver->user_id : null;
    }
    
    /**
     * Mount component (based on StepGeneral.php)
     */
    public function mount($driverId = null, $carrier = null)
    {
        $this->carriers = Carrier::all();
        $this->vehicles = Vehicle::all();
        $this->carrier = $carrier;
        $this->carrier_id = $carrier ? $carrier->id : null;
        
        // Initialize empty arrays for multiple records
        $this->initializeArrays();
        
        if ($driverId) {
            $this->isEditing = true;
            $this->driver = UserDriverDetail::with([
                'user', 'addresses', 'licenses', 'application', 'accidents', 
                'trafficConvictions', 'medicalQualification', 'trainingSchools',
                'relatedEmployments', 'employmentCompanies', 'criminalHistory'
            ])->findOrFail($driverId);
            $this->loadDriverData();
        }
        
        // Load available vehicles
        $this->loadAvailableVehicles();
        
        Log::info('AdminDriverForm mounted', [
            'mode' => $this->mode,
            'carrier_id' => $this->carrier ? $this->carrier->id : null,
            'driver_id' => $driverId
        ]);
    }
    
    /**
     * Load existing driver data for edit mode
     */
    private function loadExistingData()
    {
        if (!$this->driver) return;
        
        // Load personal info
        $user = $this->driver->user;
        if ($user) {
            $this->first_name = $user->first_name;
            $this->middle_name = $user->middle_name;
            $this->last_name = $user->last_name;
            $this->email = $user->email;
            $this->phone = $user->phone;
        }
        
        $this->date_of_birth = $this->formatDateForDisplay($this->driver->date_of_birth);
        $this->photo_url = $this->driver->photo_url;
        
        // Load address info
        $currentAddress = $this->driver->addresses()->where('is_current', true)->first();
        if ($currentAddress) {
            $this->current_address = [
                'address_line1' => $currentAddress->address_line1,
                'address_line2' => $currentAddress->address_line2,
                'city' => $currentAddress->city,
                'state' => $currentAddress->state,
                'zip_code' => $currentAddress->zip_code,
                'from_date' => $this->formatDateForDisplay($currentAddress->from_date),
                'to_date' => $currentAddress->to_date ? $this->formatDateForDisplay($currentAddress->to_date) : null,
                'lived_3_years' => $currentAddress->lived_3_years ?? false,
            ];
            
            // Load individual address properties
            $this->current_address_line1 = $currentAddress->address_line1;
            $this->current_address_line2 = $currentAddress->address_line2;
            $this->current_city = $currentAddress->city;
            $this->current_state = $currentAddress->state;
            $this->current_zip_code = $currentAddress->zip_code;
            $this->current_from_date = $this->formatDateForDisplay($currentAddress->from_date);
            $this->lived_three_years = $currentAddress->lived_3_years ?? false;
        }
        
        // Load previous addresses
        $this->previous_addresses = $this->driver->addresses()
            ->where('is_current', false)
            ->orderBy('from_date', 'desc')
            ->get()
            ->map(function ($address) {
                return [
                    'id' => $address->id,
                    'address_line1' => $address->address_line1,
                    'address_line2' => $address->address_line2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'zip_code' => $address->zip_code,
                    'from_date' => $this->formatDateForDisplay($address->from_date),
                    'to_date' => $this->formatDateForDisplay($address->to_date),
                ];
            })->toArray();
        
        // Load application info
        $this->applying_position = $this->driver->applying_position ?? '';
        $this->applying_position_other = $this->driver->applying_position_other ?? '';
        $this->applying_location = $this->driver->applying_location ?? '';
        $this->eligible_to_work = $this->driver->eligible_to_work ?? true;
        $this->can_speak_english = $this->driver->can_speak_english ?? true;
        $this->has_twic_card = $this->driver->has_twic_card ?? false;
        $this->twic_expiration_date = $this->driver->twic_expiration_date ? DateHelper::toDisplay($this->driver->twic_expiration_date) : '';
        $this->expected_pay = $this->driver->expected_pay ?? '';
        $this->how_did_hear = $this->driver->how_did_hear ?? 'internet';
        $this->how_did_hear_other = $this->driver->how_did_hear_other ?? '';
        $this->referral_employee_name = $this->driver->referral_employee_name ?? '';
        $this->has_work_history = !empty($this->driver->employmentHistory) && $this->driver->employmentHistory->count() > 0;
        
        // Load Owner Operator details if applicable
        if ($this->applying_position === 'owner_operator') {
            $this->owner_name = $this->driver->owner_name ?? '';
            $this->owner_phone = $this->driver->owner_phone ?? '';
            $this->owner_email = $this->driver->owner_email ?? '';
            $this->contract_agreed = $this->driver->contract_agreed ?? false;
        }
        
        // Load Third Party details if applicable
        if ($this->applying_position === 'third_party_driver') {
            $this->third_party_name = $this->driver->third_party_name ?? '';
            $this->third_party_phone = $this->driver->third_party_phone ?? '';
            $this->third_party_email = $this->driver->third_party_email ?? '';
            $this->third_party_dba = $this->driver->third_party_dba ?? '';
            $this->third_party_address = $this->driver->third_party_address ?? '';
            $this->third_party_contact = $this->driver->third_party_contact ?? '';
            $this->third_party_fein = $this->driver->third_party_fein ?? '';
        }
        
        // Load vehicle information if applicable
        if (in_array($this->applying_position, ['owner_operator', 'third_party_driver'])) {
            $this->vehicle_make = $this->driver->vehicle_make ?? '';
            $this->vehicle_model = $this->driver->vehicle_model ?? '';
            $this->vehicle_year = $this->driver->vehicle_year ?? '';
            $this->vehicle_vin = $this->driver->vehicle_vin ?? '';
            $this->vehicle_company_unit_number = $this->driver->vehicle_company_unit_number ?? '';
            $this->vehicle_type = $this->driver->vehicle_type ?? 'truck';
            $this->vehicle_gvwr = $this->driver->vehicle_gvwr ?? '';
            $this->vehicle_tire_size = $this->driver->vehicle_tire_size ?? '';
            $this->vehicle_fuel_type = $this->driver->vehicle_fuel_type ?? 'diesel';
            $this->vehicle_irp_apportioned_plate = $this->driver->vehicle_irp_apportioned_plate ?? false;
            $this->vehicle_registration_state = $this->driver->vehicle_registration_state ?? '';
            $this->vehicle_registration_number = $this->driver->vehicle_registration_number ?? '';
            $this->vehicle_registration_expiration_date = $this->driver->vehicle_registration_expiration_date ? DateHelper::toDisplay($this->driver->vehicle_registration_expiration_date) : '';
            $this->vehicle_permanent_tag = $this->driver->vehicle_permanent_tag ?? false;
            $this->vehicle_location = $this->driver->vehicle_location ?? '';
            $this->vehicle_notes = $this->driver->vehicle_notes ?? '';
        }
        
        // Load work history
        $this->work_history = $this->driver->employmentHistory
            ->map(function ($employment) {
                return [
                    'id' => $employment->id,
                    'company_name' => $employment->company_name,
                    'position' => $employment->position,
                    'from_date' => $this->formatDateForDisplay($employment->from_date),
                    'to_date' => $employment->to_date ? $this->formatDateForDisplay($employment->to_date) : null,
                    'reason_for_leaving' => $employment->reason_for_leaving,
                    'contact_person' => $employment->contact_person,
                    'contact_phone' => $employment->contact_phone,
                ];
            })->toArray();
    }
    
    /**
     * Switch between tabs with auto-save functionality
     */
    public function switchTab($tab)
    {
        // Auto-save current tab data before switching
        $this->autoSaveCurrentTab();
        
        // If switching from personal to address and not editing, register user first
        if ($this->currentTab === 'personal' && $tab === 'address' && !$this->isEditing) {
            $this->registerUserFromPersonalInfo();
        }
        
        $this->currentTab = $tab;
        $this->resetErrorBag();
    }
    
    /**
     * Auto-save current tab data
     */
    private function autoSaveCurrentTab()
    {
        try {
            switch ($this->currentTab) {
                case 'personal':
                    if ($this->isEditing) {
                        $this->savePersonalInfo();
                        session()->flash('auto_save_message', 'Información personal guardada automáticamente.');
                    }
                    break;
                    
                case 'address':
                    if ($this->isEditing) {
                        $this->saveAddresses();
                        session()->flash('auto_save_message', 'Información de direcciones guardada automáticamente.');
                    }
                    break;
                    
                case 'application':
                    if ($this->isEditing) {
                        $this->saveApplicationInfo();
                        session()->flash('auto_save_message', 'Información de aplicación guardada automáticamente.');
                    }
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Auto-save error: ' . $e->getMessage());
            session()->flash('auto_save_error', 'Error al guardar automáticamente: ' . $e->getMessage());
        }
    }
    
    /**
     * Save personal information
     */
    private function savePersonalInfo()
    {
        if (!$this->driver) return;
        
        // Update user info
        $this->driver->user->update([
            'name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);
        
        // Update driver details
        $this->driver->update([
            'date_of_birth' => $this->formatDateForDatabase($this->date_of_birth),
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'status' => $this->status,
        ]);
        
        // Emit auto-save success event
        $this->dispatch('autoSaveSuccess');
    }
    
    /**
     * Save application information
     */
    private function saveApplicationInfo()
    {
        if (!$this->driver) return;
        
        $this->driver->update([
            'applying_position' => $this->applying_position,
            'applying_position_other' => $this->applying_position_other,
            'applying_location' => $this->applying_location,
            'eligible_to_work' => $this->eligible_to_work,
            'can_speak_english' => $this->can_speak_english,
            'has_twic_card' => $this->has_twic_card,
            'twic_expiration_date' => $this->twic_expiration_date ? $this->formatDateForDatabase($this->twic_expiration_date) : null,
            'expected_pay' => $this->expected_pay,
            'how_did_hear' => $this->how_did_hear,
            'how_did_hear_other' => $this->how_did_hear_other,
            'referral_employee_name' => $this->referral_employee_name,
        ]);
        
        // Emit auto-save success event
        $this->dispatch('autoSaveSuccess');
    }
    
    /**
     * Register user from personal info when switching to address tab
     */
    private function registerUserFromPersonalInfo()
    {
        // Validate personal info first
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'phone' => 'required|string',
            'date_of_birth' => $this->getDateOfBirthValidationRules()
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create user
            $user = User::create([
                'name' => $this->first_name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'email_verified_at' => now(),
            ]);
            
            // Assign driver role
            $user->assignRole('driver');
            
            // Create driver detail
            $this->driver = UserDriverDetail::create([
                'user_id' => $user->id,
                'carrier_id' => $this->carrier->id,
                'date_of_birth' => $this->formatDateForDatabase($this->date_of_birth),
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'status' => $this->status ?? 0,
            ]);
            
            // Mark as editing mode now
            $this->isEditing = true;
            
            DB::commit();
            
            session()->flash('message', 'Usuario registrado exitosamente. Ahora puede continuar con la información de direcciones.');
            
            // Redirect to edit mode
            $this->redirect(route('admin.carriers.drivers.edit', [
                'carrier' => $this->carrier->id,
                'driver' => $this->driver->id
            ]));
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'Error al registrar usuario: ' . $e->getMessage());
            Log::error('User registration error: ' . $e->getMessage());
            
            // Don't switch tab if registration failed
            return;
        }
    }
    
    /**
     * Add new address
     */
    /*
    public function addAddress()
    {
        $this->previous_addresses[] = [
            'address_line1' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
            'from_date' => '',
            'to_date' => '',
        ];
    }
    */
    
    /**
     * Remove address
     */
    /*public function removeAddress($index)
    {
        unset($this->previous_addresses[$index]);
        $this->previous_addresses = array_values($this->previous_addresses);
    }
    */
    
    /**
     * Add work history entry
     */
    public function addWorkHistory()
    {
        $this->work_history[] = [
            'company_name' => '',
            'position' => '',
            'from_date' => '',
            'to_date' => '',
            'reason_for_leaving' => '',
            'contact_person' => '',
            'contact_phone' => '',
        ];
    }
    
    /**
     * Remove work history entry
     */
    public function removeWorkHistory($index)
    {
        unset($this->work_history[$index]);
        $this->work_history = array_values($this->work_history);
    }
    
    /**
     * Save driver data (main method)
     */
  /*  public function save()
    {
        try {
            DB::beginTransaction();
            
            // Validate current tab
            $this->validate();
            
            if ($this->mode === 'create') {
                $this->createDriver();
            } else {
                $this->updateDriver();
            }
            
            DB::commit();
            
            session()->flash('success', 'Driver information saved successfully!');
            
            // Redirect based on mode
            if ($this->mode === 'create') {
                return redirect()->route('admin.carriers.drivers.index', $this->carrier->id);
            } else {
                return redirect()->route('admin.carriers.drivers.show', [
                    'carrier' => $this->carrier->id,
                    'driver' => $this->driver->id
                ]);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving driver: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Error saving driver information: ' . $e->getMessage());
        }
    }
    */
    /**
     * Create new driver (based on StepGeneral.php logic)
     */
    private function createDriver()
    {
        // Create user first
        $user = User::create([
            'name' => $this->first_name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'email_verified_at' => now(),
        ]);
        
        // Assign driver role
        $user->assignRole('driver');
        
        // Handle photo upload
        $photoUrl = null;
        if ($this->photo) {
            $photoUrl = $this->photo->store('driver-photos', 'public');
        }
        
        // Create driver details
        $this->driver = UserDriverDetail::create([
            'user_id' => $user->id,
            'carrier_id' => $this->carrier->id,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'date_of_birth' => DateHelper::toDatabase($this->date_of_birth),
            'photo_url' => $photoUrl,
            'applying_position' => $this->applying_position,
            'applying_position_other' => $this->applying_position_other,
            'applying_location' => $this->applying_location,
            'eligible_to_work' => $this->eligible_to_work,
            'can_speak_english' => $this->can_speak_english,
            'has_twic_card' => $this->has_twic_card,
            'twic_expiration_date' => $this->twic_expiration_date ? DateHelper::toDatabase($this->twic_expiration_date) : null,
            'expected_pay' => $this->expected_pay,
            'how_did_hear' => $this->how_did_hear,
            'how_did_hear_other' => $this->how_did_hear_other,
            'referral_employee_name' => $this->referral_employee_name,
            'has_work_history' => $this->has_work_history,
            
            // Owner Operator fields
            'owner_name' => $this->applying_position === 'owner_operator' ? $this->owner_name : null,
            'owner_phone' => $this->applying_position === 'owner_operator' ? $this->owner_phone : null,
            'owner_email' => $this->applying_position === 'owner_operator' ? $this->owner_email : null,
            'contract_agreed' => $this->applying_position === 'owner_operator' ? $this->contract_agreed : false,
            
            // Third Party fields
            'third_party_name' => $this->applying_position === 'third_party_driver' ? $this->third_party_name : null,
            'third_party_phone' => $this->applying_position === 'third_party_driver' ? $this->third_party_phone : null,
            'third_party_email' => $this->applying_position === 'third_party_driver' ? $this->third_party_email : null,
            'third_party_dba' => $this->applying_position === 'third_party_driver' ? $this->third_party_dba : null,
            'third_party_address' => $this->applying_position === 'third_party_driver' ? $this->third_party_address : null,
            'third_party_contact' => $this->applying_position === 'third_party_driver' ? $this->third_party_contact : null,
            'third_party_fein' => $this->applying_position === 'third_party_driver' ? $this->third_party_fein : null,
            
            // Vehicle fields
            'vehicle_make' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_make : null,
            'vehicle_model' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_model : null,
            'vehicle_year' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_year : null,
            'vehicle_vin' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_vin : null,
            'vehicle_company_unit_number' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_company_unit_number : null,
            'vehicle_type' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_type : null,
            'vehicle_gvwr' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_gvwr : null,
            'vehicle_tire_size' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_tire_size : null,
            'vehicle_fuel_type' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_fuel_type : null,
            'vehicle_irp_apportioned_plate' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_irp_apportioned_plate : false,
            'vehicle_registration_state' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_registration_state : null,
            'vehicle_registration_number' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_registration_number : null,
            'vehicle_registration_expiration_date' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) && $this->vehicle_registration_expiration_date ? DateHelper::toDatabase($this->vehicle_registration_expiration_date) : null,
            'vehicle_permanent_tag' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_permanent_tag : false,
            'vehicle_location' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_location : null,
            'vehicle_notes' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_notes : null,
        ]);
        
        // Save addresses
        $this->saveAddresses();
        
        // Save work history
        $this->saveWorkHistory();
        
        Log::info('Driver created successfully', ['driver_id' => $this->driver->id]);
    }
    
    /**
     * Update existing driver
     */
    /*private function updateDriver()
    {
        // Update user info
        $this->driver->user->update([
            'name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);
        
        // Update password if provided
        if ($this->password) {
            $this->driver->user->update([
                'password' => Hash::make($this->password)
            ]);
        }
        
        // Handle photo upload
        $updateData = [
            'date_of_birth' => DateHelper::toDatabase($this->date_of_birth),
            'applying_position' => $this->applying_position,
            'applying_position_other' => $this->applying_position_other,
            'applying_location' => $this->applying_location,
            'eligible_to_work' => $this->eligible_to_work,
            'can_speak_english' => $this->can_speak_english,
            'has_twic_card' => $this->has_twic_card,
            'twic_expiration_date' => $this->twic_expiration_date ? DateHelper::toDatabase($this->twic_expiration_date) : null,
            'expected_pay' => $this->expected_pay,
            'how_did_hear' => $this->how_did_hear,
            'how_did_hear_other' => $this->how_did_hear_other,
            'referral_employee_name' => $this->referral_employee_name,
            'has_work_history' => $this->has_work_history,
            
            // Owner Operator fields
            'owner_name' => $this->applying_position === 'owner_operator' ? $this->owner_name : null,
            'owner_phone' => $this->applying_position === 'owner_operator' ? $this->owner_phone : null,
            'owner_email' => $this->applying_position === 'owner_operator' ? $this->owner_email : null,
            'contract_agreed' => $this->applying_position === 'owner_operator' ? $this->contract_agreed : false,
            
            // Third Party fields
            'third_party_name' => $this->applying_position === 'third_party_driver' ? $this->third_party_name : null,
            'third_party_phone' => $this->applying_position === 'third_party_driver' ? $this->third_party_phone : null,
            'third_party_email' => $this->applying_position === 'third_party_driver' ? $this->third_party_email : null,
            'third_party_dba' => $this->applying_position === 'third_party_driver' ? $this->third_party_dba : null,
            'third_party_address' => $this->applying_position === 'third_party_driver' ? $this->third_party_address : null,
            'third_party_contact' => $this->applying_position === 'third_party_driver' ? $this->third_party_contact : null,
            'third_party_fein' => $this->applying_position === 'third_party_driver' ? $this->third_party_fein : null,
            
            // Vehicle fields
            'vehicle_make' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_make : null,
            'vehicle_model' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_model : null,
            'vehicle_year' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_year : null,
            'vehicle_vin' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_vin : null,
            'vehicle_company_unit_number' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_company_unit_number : null,
            'vehicle_type' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_type : null,
            'vehicle_gvwr' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_gvwr : null,
            'vehicle_tire_size' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_tire_size : null,
            'vehicle_fuel_type' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_fuel_type : null,
            'vehicle_irp_apportioned_plate' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_irp_apportioned_plate : false,
            'vehicle_registration_state' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_registration_state : null,
            'vehicle_registration_number' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_registration_number : null,
            'vehicle_registration_expiration_date' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) && $this->vehicle_registration_expiration_date ? DateHelper::toDatabase($this->vehicle_registration_expiration_date) : null,
            'vehicle_permanent_tag' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_permanent_tag : false,
            'vehicle_location' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_location : null,
            'vehicle_notes' => in_array($this->applying_position, ['owner_operator', 'third_party_driver']) ? $this->vehicle_notes : null,
        ];
        
        if ($this->photo) {
            $updateData['photo_url'] = $this->photo->store('driver-photos', 'public');
        }
        
        $this->driver->update($updateData);
        
        // Update addresses
        $this->saveAddresses();
        
        // Update work history
        $this->saveWorkHistory();
        
        Log::info('Driver updated successfully', ['driver_id' => $this->driver->id]);
    }
    */
    /**
     * Save addresses (based on AddressStep.php)
     */
    private function saveAddresses()
    {
        // Delete existing addresses if editing
        if ($this->isEditing) {
            $this->driver->addresses()->delete();
        }
        
        // Save current address
        if (!empty($this->current_address_line1)) {
            $addressData = [
                'address_line1' => $this->current_address_line1,
                'address_line2' => $this->current_address_line2 ?? null,
                'city' => $this->current_city,
                'state' => $this->current_state,
                'zip_code' => $this->current_zip_code,
                'from_date' => !empty($this->current_from_date) ? $this->formatDateForDatabase($this->current_from_date) : null,
                'lived_3_years' => $this->lived_three_years ?? false,
                'is_current' => true,
                'type' => 'current'
            ];
            
            $this->driver->addresses()->create($addressData);
        }
        
        // Save previous addresses
        foreach ($this->previous_addresses as $index => $address) {
            if (!empty($address['address_line1'])) {
                $addressData = [
                    'address_line1' => $address['address_line1'],
                    'address_line2' => $address['address_line2'] ?? null,
                    'city' => $address['city'],
                    'state' => $address['state'],
                    'zip_code' => $address['zip_code'],
                    'from_date' => !empty($address['from_date']) ? $this->formatDateForDatabase($address['from_date']) : null,
                    'to_date' => !empty($address['to_date']) ? $this->formatDateForDatabase($address['to_date']) : null,
                    'is_current' => false,
                    'type' => 'previous'
                ];
                
                $this->driver->addresses()->create($addressData);
            }
        }
        
        // Emit auto-save success event
        $this->dispatch('autoSaveSuccess');
    }
    
    /**
     * Save work history
     */
    private function saveWorkHistory()
    {
        foreach ($this->work_history as $index => $employment) {
            if (!empty($employment['company_name'])) {
                $employmentData = [
                    'company_name' => $employment['company_name'],
                    'position' => $employment['position'],
                    'from_date' => DateHelper::toDatabase($employment['from_date']),
                    'to_date' => !empty($employment['to_date']) ? DateHelper::toDatabase($employment['to_date']) : null,
                    'reason_for_leaving' => $employment['reason_for_leaving'] ?? null,
                    'contact_person' => $employment['contact_person'] ?? null,
                    'contact_phone' => $employment['contact_phone'] ?? null,
                ];
                
                if (isset($employment['id']) && $employment['id']) {
                    // Update existing employment
                    $existingEmployment = $this->driver->employmentHistory()->find($employment['id']);
                    if ($existingEmployment) {
                        $existingEmployment->update($employmentData);
                    }
                } else {
                    // Create new employment
                    $this->driver->employmentHistory()->create($employmentData);
                }
            }
        }
    }

    private function loadAccidents()
     {
         if ($this->driver->accidents->count() > 0) {
             $this->accidents = $this->driver->accidents->map(function($accident) {
                 return [
                     'id' => $accident->id,
                     'accident_date' => $accident->accident_date ? $accident->accident_date->format('Y-m-d') : '',
                     'nature_of_accident' => $accident->nature_of_accident,
                     'fatalities' => $accident->fatalities,
                     'injuries' => $accident->injuries,
                     'hazmat_spill' => $accident->hazmat_spill,
                     'citation_issued' => $accident->citation_issued
                 ];
             })->toArray();
         }
     }
     
     private function loadTrafficConvictions()
     {
         if ($this->driver->trafficConvictions->count() > 0) {
             $this->trafficConvictions = $this->driver->trafficConvictions->map(function($conviction) {
                 return [
                     'id' => $conviction->id,
                     'conviction_date' => $conviction->conviction_date ? $conviction->conviction_date->format('Y-m-d') : '',
                     'location' => $conviction->location,
                     'charge' => $conviction->charge,
                     'penalty' => $conviction->penalty
                 ];
             })->toArray();
         }
     }
     
     private function loadMedicalData()
     {
         if ($this->driver->medicalQualification) {
             $medical = $this->driver->medicalQualification;
             $this->medical = [
                 'medical_examiner_name' => $medical->medical_examiner_name ?? '',
                 'medical_examiner_registry_number' => $medical->medical_examiner_registry_number ?? '',
                 'medical_card_expiration_date' => $medical->medical_card_expiration_date ? $medical->medical_card_expiration_date->format('Y-m-d') : '',
                 'hire_date' => $medical->hire_date ? $medical->hire_date->format('Y-m-d') : '',
                 'location' => $medical->location ?? ''
             ];
         }
     }
     
     private function loadTrainingSchools()
     {
         if ($this->driver->trainingSchools->count() > 0) {
             $this->trainingSchools = $this->driver->trainingSchools->map(function($school) {
                 return [
                     'id' => $school->id,
                     'date_start' => $school->date_start ? $school->date_start->format('Y-m-d') : '',
                     'date_end' => $school->date_end ? $school->date_end->format('Y-m-d') : '',
                     'school_name' => $school->school_name,
                     'city' => $school->city,
                     'state' => $school->state,
                     'graduated' => $school->graduated
                 ];
             })->toArray();
         }
     }
     
     private function loadEmploymentData()
     {
         if ($this->driver->relatedEmployments->count() > 0) {
             $this->employmentHistory = $this->driver->relatedEmployments->map(function($employment) {
                 return [
                     'id' => $employment->id,
                     'start_date' => $employment->start_date ? $employment->start_date->format('Y-m-d') : '',
                     'end_date' => $employment->end_date ? $employment->end_date->format('Y-m-d') : '',
                     'position' => $employment->position,
                     'comments' => $employment->comments
                 ];
             })->toArray();
         }
         
         if ($this->driver->employmentCompanies->count() > 0) {
             $this->employmentCompanies = $this->driver->employmentCompanies->map(function($company) {
                 return [
                     'id' => $company->id,
                     'employed_from' => $company->employed_from ? $company->employed_from->format('Y-m-d') : '',
                     'employed_to' => $company->employed_to ? $company->employed_to->format('Y-m-d') : '',
                     'positions_held' => $company->positions_held,
                     'reason_for_leaving' => $company->reason_for_leaving,
                     'subject_to_fmcsr' => $company->subject_to_fmcsr
                 ];
             })->toArray();
         }
     }
     
     private function loadCriminalHistory()
     {
         if ($this->driver->criminalHistory) {
             $criminal = $this->driver->criminalHistory;
             $this->criminalHistory = [
                 'has_criminal_charges' => $criminal->has_criminal_charges ?? false,
                 'has_felony_conviction' => $criminal->has_felony_conviction ?? false,
                 'has_minister_permit' => $criminal->has_minister_permit ?? false,
                 'fcra_consent' => $criminal->fcra_consent ?? false
             ];
         }
     }
     
     /*private function saveAddresses($driverDetail)
     {
         // Delete existing addresses if editing
         if ($this->isEditing) {
             $driverDetail->addresses()->delete();
         }
         
         // Save new addresses
         foreach ($this->addresses as $addressData) {
             if (!empty($addressData['address_line_1'])) {
                 $driverDetail->addresses()->create($addressData);
             }
         }
     }
     */
     private function saveLicenses($driverDetail)
     {
         // Delete existing licenses if editing
         if ($this->isEditing) {
             $driverDetail->licenses()->delete();
         }
         
         // Save new licenses
         foreach ($this->licenses as $licenseData) {
             if (!empty($licenseData['license_number'])) {
                 $driverDetail->licenses()->create($licenseData);
             }
         }
     }
     
     private function saveApplication($driverDetail)
     {
         if ($this->isEditing && $driverDetail->application) {
             $driverDetail->application()->update($this->application);
         } else {
             $driverDetail->application()->create($this->application);
         }
     }
     
     private function saveAccidents($driverDetail)
     {
         // Delete existing accidents if editing
         if ($this->isEditing) {
             $driverDetail->accidents()->delete();
         }
         
         // Save new accidents
         foreach ($this->accidents as $accidentData) {
             if (!empty($accidentData['accident_date'])) {
                 $driverDetail->accidents()->create($accidentData);
             }
         }
     }
     
     private function saveTrafficConvictions($driverDetail)
     {
         // Delete existing traffic convictions if editing
         if ($this->isEditing) {
             $driverDetail->trafficConvictions()->delete();
         }
         
         // Save new traffic convictions
         foreach ($this->trafficConvictions as $convictionData) {
             if (!empty($convictionData['conviction_date'])) {
                 $driverDetail->trafficConvictions()->create($convictionData);
             }
         }
     }
     
     private function saveMedicalData($driverDetail)
     {
         if ($this->isEditing && $driverDetail->medicalQualification) {
             $driverDetail->medicalQualification()->update($this->medical);
         } else {
             $driverDetail->medicalQualification()->create($this->medical);
         }
     }
     
     private function saveTrainingSchools($driverDetail)
     {
         // Delete existing training schools if editing
         if ($this->isEditing) {
             $driverDetail->trainingSchools()->delete();
         }
         
         // Save new training schools
         foreach ($this->trainingSchools as $schoolData) {
             if (!empty($schoolData['school_name'])) {
                 $driverDetail->trainingSchools()->create($schoolData);
             }
         }
     }
     
     private function saveEmploymentData($driverDetail)
     {
         // Delete existing employment data if editing
         if ($this->isEditing) {
             $driverDetail->relatedEmployments()->delete();
             $driverDetail->employmentCompanies()->delete();
         }
         
         // Save employment history
         foreach ($this->employmentHistory as $employmentData) {
             if (!empty($employmentData['position'])) {
                 $driverDetail->relatedEmployments()->create($employmentData);
             }
         }
         
         // Save employment companies
         foreach ($this->employmentCompanies as $companyData) {
             if (!empty($companyData['positions_held'])) {
                 $driverDetail->employmentCompanies()->create($companyData);
             }
         }
     }
     
     private function saveCriminalHistory($driverDetail)
     {
         if ($this->isEditing && $driverDetail->criminalHistory) {
             $driverDetail->criminalHistory()->update($this->criminalHistory);
         } else {
             $driverDetail->criminalHistory()->create($this->criminalHistory);
         }
     }
    
    private function initializeArrays()
    {
        // Initialize with one empty record for each section
        $this->addresses = [[
            'address_line_1' => '', 'address_line_2' => '', 'city' => '',
            'state' => '', 'zip_code' => '', 'country' => 'US', 'address_type' => 'home'
        ]];
        
        // Initialize previous addresses array
        $this->previous_addresses = [];
        
        $this->licenses = [[
            'license_number' => '', 'state' => '', 'expiration_date' => '',
            'license_class' => '', 'endorsements' => '', 'restrictions' => ''
        ]];
        
        $this->accidents = [[
            'accident_date' => '', 'nature_of_accident' => '', 'fatalities' => 0,
            'injuries' => 0, 'hazmat_spill' => false, 'citation_issued' => false
        ]];
        
        $this->trafficConvictions = [[
            'conviction_date' => '', 'location' => '', 'charge' => '', 'penalty' => ''
        ]];
        
        $this->trainingSchools = [[
            'date_start' => '', 'date_end' => '', 'school_name' => '',
            'city' => '', 'state' => '', 'graduated' => false
        ]];
        
        $this->employmentHistory = [[
            'start_date' => '', 'end_date' => '', 'position' => '', 'comments' => ''
        ]];
        
        $this->employmentCompanies = [[
            'employed_from' => '', 'employed_to' => '', 'positions_held' => '',
            'reason_for_leaving' => '', 'subject_to_fmcsr' => false
        ]];
        
        // Initialize single record objects
        $this->application = [
            'has_traffic_convictions' => false, 'has_accidents' => false,
            'has_drug_alcohol_violations' => false, 'has_refused_drug_test' => false
        ];
        
        $this->medical = [
            'medical_examiner_name' => '', 'medical_examiner_registry_number' => '',
            'medical_card_expiration_date' => '', 'hire_date' => '', 'location' => ''
        ];
        
        $this->criminalHistory = [
            'has_criminal_charges' => false, 'has_felony_conviction' => false,
            'has_minister_permit' => false, 'fcra_consent' => false
        ];
    }

    public function loadAvailableVehicles()
    {
        if ($this->carrier_id) {
            $this->available_vehicles = Vehicle::where('carrier_id', $this->carrier_id)
                ->where(function($query) {
                    $query->whereNull('user_driver_detail_id')
                          ->orWhere('user_driver_detail_id', $this->driver?->id ?? 0);
                })
                ->get()
                ->map(function($vehicle) {
                    return [
                        'id' => $vehicle->id,
                        'display_name' => $vehicle->year . ' ' . $vehicle->make . ' ' . $vehicle->model . ' (' . $vehicle->vin . ')'
                    ];
                })
                ->toArray();
        } else {
            $this->available_vehicles = [];
        }
    }

    private function loadDriverData()
    {
        if ($this->driver) {
            // Load user data
            $this->name = $this->driver->user->name;
            $this->email = $this->driver->user->email;
            $this->phone = $this->driver->phone;
            $this->date_of_birth = $this->driver->date_of_birth ? $this->driver->date_of_birth->format('m/d/Y') : null;
            $this->social_security_number = $this->driver->social_security_number;
            $this->carrier_id = $this->driver->carrier_id;
            $this->vehicle_id = $this->driver->vehicle_id;
            $this->status = $this->driver->status;
            
            // Load addresses
            $this->addresses = $this->driver->addresses->map(function($address) {
                return [
                    'id' => $address->id,
                    'address_line_1' => $address->address_line_1,
                    'address_line_2' => $address->address_line_2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'zip_code' => $address->zip_code,
                    'country' => $address->country,
                    'address_type' => $address->address_type
                ];
            })->toArray();
            
            // Load licenses
            $this->licenses = $this->driver->licenses->map(function($license) {
                return [
                    'id' => $license->id,
                    'license_number' => $license->license_number,
                    'state' => $license->state,
                    'expiration_date' => $license->expiration_date ? $license->expiration_date->format('Y-m-d') : '',
                    'license_class' => $license->license_class,
                    'endorsements' => $license->endorsements,
                    'restrictions' => $license->restrictions
                ];
            })->toArray();
            
            // Load application data
            if ($this->driver->application) {
                $app = $this->driver->application;
                $this->application = [
                    'has_traffic_convictions' => $app->has_traffic_convictions ?? false,
                    'has_accidents' => $app->has_accidents ?? false,
                    'has_drug_alcohol_violations' => $app->has_drug_alcohol_violations ?? false,
                    'has_refused_drug_test' => $app->has_refused_drug_test ?? false
                ];
            }
            
            // Load other sections...
             $this->loadAccidents();
             $this->loadTrafficConvictions();
             $this->loadMedicalData();
             $this->loadTrainingSchools();
             $this->loadEmploymentData();
             $this->loadCriminalHistory();
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    public function updatedCarrierId()
    {
        $this->loadAvailableVehicles();
    }

    public function rules()
    {
        if ($this->currentTab === 'personal') {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($this->driver->user->id ?? null)
                ],
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'date_of_birth' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        // Si ya es un objeto Carbon/DateTime, convertirlo a string
                        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTime) {
                            $value = $value->format('m/d/Y');
                        }
                        
                        $formats = ['m/d/Y', 'n/j/Y', 'M/d/Y', 'MM/DD/YYYY'];
                        $valid = false;
                        $parsedDate = null;
                        
                        foreach ($formats as $format) {
                            $parsed = date_parse_from_format($format, $value);
                            if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
                                $parsedDate = \Carbon\Carbon::createFromFormat($format, $value);
                                $valid = true;
                                break;
                            }
                        }
                        
                        if (!$valid) {
                            $fail('The date of birth field must be a valid date in MM/DD/YYYY format.');
                            return;
                        }
                        
                        // Verificar que sea mayor de 18 años
                        $eighteenYearsAgo = now()->subYears(18);
                        if ($parsedDate->isAfter($eighteenYearsAgo)) {
                            $fail('You must be at least 18 years old.');
                        }
                        
                        // Verificar que no sea mayor de 100 años
                        $hundredYearsAgo = now()->subYears(100);
                        if ($parsedDate->isBefore($hundredYearsAgo)) {
                            $fail('Please enter a valid date of birth.');
                        }
                    }
                ],
                'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10240',
            ];
            
            // Password validation only for new drivers
            if (!$this->isEditing) {
                $rules['password'] = 'required|string|min:8|confirmed';
            }
            
            return $rules;
        } elseif ($this->currentTab === 'address') {
            return [
                'current_address.address_line1' => 'required|string|max:255',
                'current_address.city' => 'required|string|max:100',
                'current_address.state' => 'required|string|max:2',
                'current_address.zip_code' => 'required|string|max:10',
                'current_address.from_date' => 'required|date',
                'current_address.to_date' => 'nullable|date|after_or_equal:current_address.from_date',
            ];
        } elseif ($this->currentTab === 'application') {
            $rules = [
                'applying_position' => 'required|string',
                'applying_position_other' => 'required_if:applying_position,other',
                'applying_location' => 'required|string',
                'eligible_to_work' => 'accepted',
                'twic_expiration_date' => 'nullable|required_if:has_twic_card,true|date',
                'how_did_hear' => 'required|string',
                'how_did_hear_other' => 'required_if:how_did_hear,other',
                'referral_employee_name' => 'required_if:how_did_hear,employee_referral',
                'work_history.*.company_name' => 'required_if:has_work_history,true|string|max:255',
                'work_history.*.position' => 'required_if:has_work_history,true|string|max:255',
                'work_history.*.from_date' => 'required_if:has_work_history,true|date',
                'work_history.*.to_date' => 'required_if:has_work_history,true|date|after_or_equal:work_history.*.from_date',
                'work_history.*.reason_for_leaving' => 'nullable|string|max:500',
            ];
            
            // Add rules based on the selected position
            if ($this->applying_position === 'owner_operator') {
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
                ]);
            } elseif ($this->applying_position === 'third_party_driver') {
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
                ]);
            }
            
            return $rules;
        }
        
        // Default rules for other tabs
        return [];
    }

    public function save()
    {
        $this->validate();
        
        DB::beginTransaction();
        
        try {
            // Create or update user
            if ($this->isEditing) {
                $user = $this->driver->user;
                $user->update([
                    'name' => $this->name,
                    'email' => $this->email,
                ]);
            } else {
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'role' => 'driver',
                ]);
            }
            
            // Create or update driver detail
            $driverData = [
                'phone' => $this->phone,
                'date_of_birth' => $this->date_of_birth,
                'social_security_number' => $this->social_security_number,
                'carrier_id' => $this->carrier_id,
                'vehicle_id' => $this->vehicle_id,
                'status' => $this->status,
            ];
            
            if ($this->isEditing) {
                $this->driver->update($driverData);
                $driverDetail = $this->driver;
            } else {
                $driverDetail = UserDriverDetail::create(array_merge($driverData, [
                    'user_id' => $user->id,
                ]));
            }
            
            // Set the driver for saving methods
            $this->driver = $driverDetail;
            
            // Save all sections
            $this->saveAddresses();
            $this->saveWorkHistory();
            // Add other save methods as needed
            
            DB::commit();
            
            session()->flash('message', $this->isEditing ? 'Driver updated successfully!' : 'Driver created successfully!');
            
            return redirect()->route('admin.drivers.index');
            
        } catch (\Exception $e) {
            DB::rollback();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /*private function createDriver()
    {
        // Create user
        $user = User::create([
            'name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => 'driver',
        ]);

        // Create driver detail
        $driverDetail = UserDriverDetail::create([
            'user_id' => $user->id,
            'carrier_id' => $this->carrier->id,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth,
            'status' => $this->status,
            'assigned_vehicle_id' => $this->assigned_vehicle_id ?: null,
            'employee_id' => $this->employee_id,
            'hire_date' => $this->hire_date,
            'department' => $this->department,
            'position' => $this->position,
            'supervisor' => $this->supervisor,
            'work_phone' => $this->work_phone,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
        ]);

        $this->saveAdditionalData($driverDetail);
    }
    */
    private function updateDriver()
    {
        // Update user
        $updateData = [
            'name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $updateData['password'] = Hash::make($this->password);
        }

        $this->userDriverDetail->user->update($updateData);

        // Update driver detail
        $this->userDriverDetail->update([
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth,
            'status' => $this->status,
            'assigned_vehicle_id' => $this->assigned_vehicle_id ?: null,
            'employee_id' => $this->employee_id,
            'hire_date' => $this->hire_date,
            'department' => $this->department,
            'position' => $this->position,
            'supervisor' => $this->supervisor,
            'work_phone' => $this->work_phone,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
        ]);

        $this->saveAdditionalData($this->userDriverDetail);
    }

    private function saveAdditionalData($driverDetail)
    {
        // Save profile photo
        if ($this->profile_photo) {
            $driverDetail->clearMediaCollection('profile_photo_driver');
            $driverDetail->addMediaFromRequest('profile_photo')
                ->toMediaCollection('profile_photo_driver');
        }

        // Save or update address
        if ($this->street_address || $this->city || $this->state || $this->zip_code) {
            $addressData = [
                'street_address' => $this->street_address,
                'city' => $this->city,
                'state' => $this->state,
                'zip_code' => $this->zip_code,
                'country' => $this->country,
            ];

            $address = $driverDetail->addresses()->first();
            if ($address) {
                $address->update($addressData);
            } else {
                $driverDetail->addresses()->create($addressData);
            }
        }

        // Save or update license
        if ($this->license_number) {
            $licenseData = [
                'license_number' => $this->license_number,
                'state' => $this->license_state,
                'class' => $this->license_class,
                'expiration_date' => $this->license_expiration,
                'endorsements' => $this->license_endorsements,
                'restrictions' => $this->license_restrictions,
                'is_primary' => true,
            ];

            $license = $driverDetail->primaryLicense();
            if ($license) {
                $license->update($licenseData);
            } else {
                $driverDetail->licenses()->create($licenseData);
            }
        }

        // Update vehicle assignment
        if ($this->assigned_vehicle_id) {
            Vehicle::where('user_driver_detail_id', $driverDetail->id)->update(['user_driver_detail_id' => null]);
            Vehicle::where('id', $this->assigned_vehicle_id)->update(['user_driver_detail_id' => $driverDetail->id]);
        }
    }
    
    // Methods to add/remove multiple records
    public function addAddress()
    {
        $this->addresses[] = [
            'address_line_1' => '', 'address_line_2' => '', 'city' => '',
            'state' => '', 'zip_code' => '', 'country' => 'US', 'address_type' => 'home'
        ];
    }
    
    public function removeAddress($index)
    {
        if (count($this->addresses) > 1) {
            unset($this->addresses[$index]);
            $this->addresses = array_values($this->addresses);
        }
    }
    
    /**
     * Add previous address
     */
    public function addPreviousAddress()
    {
        $this->previous_addresses[] = [
            'address_line1' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
            'from_date' => '',
            'to_date' => '',
        ];
    }
    
    /**
     * Remove previous address
     */
    public function removePreviousAddress($index)
    {
        if (count($this->previous_addresses) > 1) {
            unset($this->previous_addresses[$index]);
            $this->previous_addresses = array_values($this->previous_addresses);
        }
    }
    
    public function addLicense()
    {
        $this->licenses[] = [
            'license_number' => '', 'state' => '', 'expiration_date' => '',
            'license_class' => '', 'endorsements' => '', 'restrictions' => ''
        ];
    }
    
    public function removeLicense($index)
    {
        if (count($this->licenses) > 1) {
            unset($this->licenses[$index]);
            $this->licenses = array_values($this->licenses);
        }
    }
    
    public function addAccident()
    {
        $this->accidents[] = [
            'accident_date' => '', 'nature_of_accident' => '', 'fatalities' => 0,
            'injuries' => 0, 'hazmat_spill' => false, 'citation_issued' => false
        ];
    }
    
    public function removeAccident($index)
    {
        unset($this->accidents[$index]);
        $this->accidents = array_values($this->accidents);
    }
    
    public function addTrafficConviction()
    {
        $this->trafficConvictions[] = [
            'conviction_date' => '', 'location' => '', 'charge' => '', 'penalty' => ''
        ];
    }
    
    public function removeTrafficConviction($index)
    {
        unset($this->trafficConvictions[$index]);
        $this->trafficConvictions = array_values($this->trafficConvictions);
    }
    
    public function addTrainingSchool()
    {
        $this->trainingSchools[] = [
            'school_name' => '',
            'date_start' => '',
            'date_end' => '',
            'graduated' => false,
            'training_skills' => '',
        ];
    }

    public function removeTrainingSchool($index)
    {
        unset($this->trainingSchools[$index]);
        $this->trainingSchools = array_values($this->trainingSchools);
    }

    public function addEmployment()
    {
        $this->employmentHistory[] = [
            'company_name' => '',
            'position' => '',
            'start_date' => '',
            'end_date' => '',
            'reason_for_leaving' => '',
        ];
    }

    public function removeEmployment($index)
    {
        unset($this->employmentHistory[$index]);
        $this->employmentHistory = array_values($this->employmentHistory);
    }
    
    public function addEmploymentCompany()
    {
        $this->employmentCompanies[] = [
            'employed_from' => '', 'employed_to' => '', 'positions_held' => '',
            'reason_for_leaving' => '', 'subject_to_fmcsr' => false
        ];
    }
    
    public function removeEmploymentCompany($index)
    {
        unset($this->employmentCompanies[$index]);
        $this->employmentCompanies = array_values($this->employmentCompanies);
    }
    
    /**
     * Handle applying position changes
     */
    public function updatedApplyingPosition($value)
    {
        // Auto-fill owner fields if owner operator is selected
        if ($value === 'owner_operator') {
            $this->owner_name = trim($this->first_name . ' ' . $this->last_name);
            $this->owner_email = $this->email;
            $this->owner_phone = $this->phone;
        }
        
        // Clear conditional fields when position changes
        if ($value !== 'other') {
            $this->applying_position_other = '';
        }
        
        if ($value !== 'owner_operator') {
            $this->owner_name = '';
            $this->owner_phone = '';
            $this->owner_email = '';
            $this->contract_agreed = false;
        }
        
        if ($value !== 'third_party_driver') {
            $this->third_party_name = '';
            $this->third_party_phone = '';
            $this->third_party_email = '';
            $this->third_party_dba = '';
            $this->third_party_address = '';
            $this->third_party_contact = '';
            $this->third_party_fein = '';
        }
        
        if (!in_array($value, ['owner_operator', 'third_party_driver'])) {
            $this->vehicle_make = '';
            $this->vehicle_model = '';
            $this->vehicle_year = '';
            $this->vehicle_vin = '';
        }
    }
    
    /**
     * Handle TWIC card checkbox changes
     */
    public function updatedHasTwicCard($value)
    {
        if (!$value) {
            $this->twic_expiration_date = '';
        }
    }
    
    /**
     * Handle how did hear changes
     */
    public function updatedHowDidHear($value)
    {
        if ($value !== 'other') {
            $this->how_did_hear_other = '';
        }
        
        if ($value !== 'employee') {
            $this->referral_employee_name = '';
        }
    }
    
    public function render()
    {
        return view('livewire.admin.admin-driver-form');
    }
}