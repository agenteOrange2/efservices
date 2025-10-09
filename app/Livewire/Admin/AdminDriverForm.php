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
    public $social_security_number = '';
    
    // Application additional fields
    public $expected_pay_rate = '';
    public $referral_source = '';
    public $location_preference = '';
    public $eligibility_information = '';
    
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
    public function mount($driverId = null, $carrier = null, $userDriverDetail = null, $mode = 'create')
    {
        $this->carriers = Carrier::all();
        $this->vehicles = Vehicle::all();
        $this->carrier = $carrier;
        $this->carrier_id = $carrier ? $carrier->id : null;
        $this->mode = $mode;
        
        // Initialize empty arrays for multiple records
        $this->initializeArrays();
        
        // Handle edit mode - prioritize userDriverDetail parameter
        if ($userDriverDetail) {
            $this->isEditing = true;
            $this->mode = 'edit';
            $this->driver = $userDriverDetail;
            // Load the user relationship if not already loaded
            if (!$this->driver->relationLoaded('user')) {
                $this->driver->load('user');
            }
            $this->loadDriverData();
        } elseif ($driverId) {
            $this->isEditing = true;
            $this->mode = 'edit';
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
            'driver_id' => $driverId,
            'userDriverDetail_id' => $userDriverDetail ? $userDriverDetail->id : null,
            'isEditing' => $this->isEditing
        ]);
    }
    
    /**
     * Load driver data for edit mode
     */
    private function loadDriverData()
    {
        if (!$this->driver) return;
        
        Log::info('Loading driver data', ['driver_id' => $this->driver->id]);
        
        $this->loadExistingData();
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
            $this->name = $user->name;
            $this->middle_name = $this->driver->middle_name;
            $this->last_name = $this->driver->last_name;
            $this->email = $user->email;
            $this->phone = $this->driver->phone;
        }
        
        $this->date_of_birth = $this->formatDateForDisplay($this->driver->date_of_birth);
        $this->photo_url = $this->driver->photo_url;
        
        // Load address info
        $currentAddress = null;
        if ($this->driver->application && $this->driver->application->addresses()) {
            $currentAddress = $this->driver->application->addresses()->where('primary', true)->first();
        }
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
        $this->previous_addresses = [];
        if ($this->driver->application && $this->driver->application->addresses()) {
            $this->previous_addresses = $this->driver->application->addresses()
                ->where('primary', false)
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
        }
        
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
        $this->work_history = ($this->driver->employmentHistory ?? collect())
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
        Log::info('AdminDriverForm: switchTab called', [
            'from_tab' => $this->currentTab,
            'to_tab' => $tab,
            'is_editing' => $this->isEditing,
            'driver_id' => $this->driver ? $this->driver->id : null
        ]);
        
        // Auto-save current tab data before switching
        $this->autoSaveCurrentTab();
        
        // If switching from personal to address and not editing, register user first
        if ($this->currentTab === 'personal' && $tab === 'address' && !$this->isEditing) {
            Log::info('AdminDriverForm: Attempting to register user from personal info');
            $this->registerUserFromPersonalInfo();
        }
        
        $this->currentTab = $tab;
        $this->resetErrorBag();
        
        Log::info('AdminDriverForm: switchTab completed', [
            'current_tab' => $this->currentTab
        ]);
    }
    
    /**
     * Auto-save current tab data
     */
    private function autoSaveCurrentTab()
    {
        Log::info('AdminDriverForm: autoSaveCurrentTab called', [
            'current_tab' => $this->currentTab,
            'is_editing' => $this->isEditing,
            'driver_id' => $this->driver ? $this->driver->id : null
        ]);
        
        try {
            switch ($this->currentTab) {
                case 'personal':
                    if ($this->isEditing) {
                        Log::info('AdminDriverForm: Saving personal info');
                        $this->savePersonalInfo();
                        session()->flash('auto_save_message', 'Información personal guardada automáticamente.');
                    } else {
                        Log::info('AdminDriverForm: Skipping personal info save - not in editing mode');
                    }
                    break;
                    
                case 'address':
                    if ($this->isEditing) {
                        Log::info('AdminDriverForm: Saving addresses');
                        $this->saveAddresses();
                        session()->flash('auto_save_message', 'Información de direcciones guardada automáticamente.');
                    } else {
                        Log::info('AdminDriverForm: Skipping addresses save - not in editing mode');
                    }
                    break;
                    
                case 'application':
                    if ($this->isEditing) {
                        Log::info('AdminDriverForm: Saving application info');
                        $this->saveApplicationInfo();
                        session()->flash('auto_save_message', 'Información de aplicación guardada automáticamente.');
                    } else {
                        Log::info('AdminDriverForm: Skipping application info save - not in editing mode');
                    }
                    break;
                    
                default:
                    Log::info('AdminDriverForm: Unknown tab for auto-save', ['tab' => $this->currentTab]);
            }
        } catch (\Exception $e) {
            Log::error('AdminDriverForm: Auto-save error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'current_tab' => $this->currentTab
            ]);
            session()->flash('auto_save_error', 'Error al guardar automáticamente: ' . $e->getMessage());
        }
    }
    
    /**
     * Save personal information
     */
    private function savePersonalInfo()
    {
        if (!$this->driver) return;
        
        // Update user info (only fields that exist in users table)
        $this->driver->user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);
        
        // Update driver details (fields that exist in user_driver_details table)
        $this->driver->update([
            'date_of_birth' => $this->formatDateForDatabase($this->date_of_birth),
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
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
        Log::info('AdminDriverForm: registerUserFromPersonalInfo started', [
            'name' => $this->name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth,
            'carrier_id' => $this->carrier ? $this->carrier->id : null
        ]);
        
        // Validate personal info first
        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'phone' => 'required|string',
                'date_of_birth' => $this->getDateOfBirthValidationRules()
            ]);
            
            Log::info('AdminDriverForm: Validation passed');
        } catch (\Exception $e) {
            Log::error('AdminDriverForm: Validation failed', [
                'error' => $e->getMessage(),
                'validation_errors' => $this->getErrorBag()->toArray()
            ]);
            throw $e;
        }
        
        try {
            DB::beginTransaction();
            
            Log::info('AdminDriverForm: Creating user');
            // Create user
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'email_verified_at' => now(),
            ]);
            
            Log::info('AdminDriverForm: User created', ['user_id' => $user->id]);
            
            // Assign driver role
            $user->assignRole('driver');
            Log::info('AdminDriverForm: Driver role assigned');
            
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
            
            Log::info('AdminDriverForm: Driver detail created', ['driver_id' => $this->driver->id]);
            
            // Mark as editing mode now
            $this->isEditing = true;
            
            DB::commit();
            
            Log::info('AdminDriverForm: Transaction committed successfully');
            
            session()->flash('message', 'Usuario registrado exitosamente. Ahora puede continuar con la información de direcciones.');
            
            // Redirect to edit mode
            $redirectUrl = route('admin.carriers.drivers.edit', [
                'carrier' => $this->carrier->id,
                'driver' => $this->driver->id
            ]);
            
            Log::info('AdminDriverForm: Redirecting to', ['url' => $redirectUrl]);
            
            $this->redirect($redirectUrl);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('AdminDriverForm: User registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al registrar usuario: ' . $e->getMessage());
            
            // Don't switch tab if registration failed
            return;
        }
    }
    

    

    
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
     * Create new driver (based on StepGeneral.php logic)
     */
    private function createDriver()
    {
        // Create user first
        $user = User::create([
            'name' => $this->name,
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
            'name' => $this->name,
            'email' => $this->email,
        ]);
        
        // Update driver details
        $this->driver->update([
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
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
        // Ensure driver has an application
        if (!$this->driver->application) {
            return;
        }
        
        // Delete existing addresses if editing
        if ($this->isEditing) {
            $this->driver->application->addresses()->delete();
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
            
            $this->driver->application->addresses()->create($addressData);
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
                
                $this->driver->application->addresses()->create($addressData);
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
         if ($this->isEditing && $driverDetail->application) {
             $driverDetail->application->addresses()->delete();
         }
         
         // Save new addresses
         if ($driverDetail->application) {
             foreach ($this->addresses as $addressData) {
                 if (!empty($addressData['address_line_1'])) {
                     $driverDetail->application->addresses()->create($addressData);
                 }
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
        Log::info('AdminDriverForm: Iniciando método save', [
            'isEditing' => $this->isEditing,
            'currentTab' => $this->currentTab,
            'driver_id' => $this->driver ? $this->driver->id : null,
            'name' => $this->name,
            'email' => $this->email
        ]);
        
        try {
            // Determine which tab-specific save method to call
            switch ($this->currentTab) {
                case 'personal':
                    if (!$this->isEditing) {
                        // For new drivers, create user and driver detail on personal tab
                        $this->saveNewDriverPersonalInfo();
                    } else {
                        // For existing drivers, just update personal info
                        $this->savePersonalInfoOnly();
                    }
                    break;
                    
                case 'address':
                    if ($this->isEditing) {
                        $this->saveAddressInfoOnly();
                    } else {
                        session()->flash('error', 'Debe completar la información personal primero.');
                        return;
                    }
                    break;
                    
                case 'application':
                    if ($this->isEditing) {
                        $this->saveApplicationInfoOnly();
                    } else {
                        session()->flash('error', 'Debe completar la información personal primero.');
                        return;
                    }
                    break;
                    
                default:
                    Log::error('AdminDriverForm: Tab desconocido', ['tab' => $this->currentTab]);
                    session()->flash('error', 'Tab no válido.');
                    return;
            }
            
            Log::info('AdminDriverForm: Guardado exitoso para tab', ['tab' => $this->currentTab]);
            session()->flash('message', 'Información guardada exitosamente.');
            
        } catch (\Exception $e) {
            Log::error('AdminDriverForm: Error en método save', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'currentTab' => $this->currentTab,
                'isEditing' => $this->isEditing,
                'driver_id' => $this->driver ? $this->driver->id : null
            ]);
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }
    
    /**
     * Save personal info for new drivers (creates User and UserDriverDetail)
     */
    private function saveNewDriverPersonalInfo()
    {
        // Validate only personal tab fields
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'phone' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);
        
        Log::info('AdminDriverForm: Validación personal completada');
        
        DB::beginTransaction();
        
        try {
            // Create user with status false (pending)
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'email_verified_at' => now(),
                'status' => false, // Mark user as pending
            ]);
            
            Log::info('AdminDriverForm: Usuario creado como pendiente', ['user_id' => $user->id]);
            
            // Assign driver role
            $user->assignRole('user_driver');
            
            // Create driver detail with status 0 (pending)
            $driverData = [
                'user_id' => $user->id,
                'carrier_id' => $this->carrier->id,
                'status' => 0, // Always set as pending for new drivers
            ];
            
            // Add optional fields only if they have values
            if ($this->phone) $driverData['phone'] = $this->phone;
            if ($this->date_of_birth) $driverData['date_of_birth'] = $this->formatDateForDatabase($this->date_of_birth);
            if ($this->middle_name) $driverData['middle_name'] = $this->middle_name;
            if ($this->last_name) $driverData['last_name'] = $this->last_name;
            
            $this->driver = UserDriverDetail::create($driverData);
            
            Log::info('AdminDriverForm: Driver detail creado como pendiente', ['driver_id' => $this->driver->id]);
            
            // Mark as editing mode
            $this->isEditing = true;
            
            DB::commit();
            
            Log::info('AdminDriverForm: Transacción personal completada exitosamente');
            
            // Redirect to edit page to continue completing the driver information
            return $this->redirect(route('admin.carrier.user_drivers.edit', [
                'carrier' => $this->carrier->slug,
                'userDriverDetail' => $this->driver->id
            ]));
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    /**
     * Save personal info for existing drivers (updates only)
     */
    private function savePersonalInfoOnly()
    {
        if (!$this->driver) {
            throw new \Exception('Driver no encontrado para actualización.');
        }
        
        // Validate only personal tab fields
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->driver->user->id,
            'phone' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update user info
            $this->driver->user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);
            
            // Update driver details
            $updateData = [];
            if ($this->phone !== null) $updateData['phone'] = $this->phone;
            if ($this->date_of_birth) $updateData['date_of_birth'] = $this->formatDateForDatabase($this->date_of_birth);
            if ($this->middle_name !== null) $updateData['middle_name'] = $this->middle_name;
            if ($this->last_name !== null) $updateData['last_name'] = $this->last_name;
            if ($this->status !== null) $updateData['status'] = $this->status;
            
            if (!empty($updateData)) {
                $this->driver->update($updateData);
            }
            
            DB::commit();
            
            Log::info('AdminDriverForm: Información personal actualizada exitosamente');
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    /**
     * Save address info only
     */
    private function saveAddressInfoOnly()
    {
        if (!$this->driver) {
            throw new \Exception('Driver no encontrado para guardar direcciones.');
        }
        
        Log::info('AdminDriverForm: Guardando solo información de direcciones');
        $this->saveAddresses();
    }
    
    /**
     * Save application info only
     */
    private function saveApplicationInfoOnly()
    {
        if (!$this->driver) {
            throw new \Exception('Driver no encontrado para guardar información de aplicación.');
        }
        
        Log::info('AdminDriverForm: Guardando solo información de aplicación');
        
        // Validate application fields
        $this->validate([
            'applying_position' => 'nullable|string',
            'expected_pay' => 'nullable|numeric',
            'eligible_to_work' => 'nullable|boolean',
            'can_speak_english' => 'nullable|boolean',
            'has_twic_card' => 'nullable|boolean',
        ]);
        
        DB::beginTransaction();
        
        try {
            $updateData = [];
            if ($this->applying_position !== null) $updateData['applying_position'] = $this->applying_position;
            if ($this->applying_position_other !== null) $updateData['applying_position_other'] = $this->applying_position_other;
            if ($this->applying_location !== null) $updateData['applying_location'] = $this->applying_location;
            if ($this->eligible_to_work !== null) $updateData['eligible_to_work'] = $this->eligible_to_work;
            if ($this->can_speak_english !== null) $updateData['can_speak_english'] = $this->can_speak_english;
            if ($this->has_twic_card !== null) $updateData['has_twic_card'] = $this->has_twic_card;
            if ($this->twic_expiration_date) $updateData['twic_expiration_date'] = $this->formatDateForDatabase($this->twic_expiration_date);
            if ($this->expected_pay !== null) $updateData['expected_pay'] = $this->expected_pay;
            if ($this->how_did_hear !== null) $updateData['how_did_hear'] = $this->how_did_hear;
            if ($this->how_did_hear_other !== null) $updateData['how_did_hear_other'] = $this->how_did_hear_other;
            if ($this->referral_employee_name !== null) $updateData['referral_employee_name'] = $this->referral_employee_name;
            
            if (!empty($updateData)) {
                $this->driver->update($updateData);
            }
            
            DB::commit();
            
            Log::info('AdminDriverForm: Información de aplicación actualizada exitosamente');
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /*private function createDriver()
    {
        // Create user
        $user = User::create([
            'name' => $this->name,
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
            'name' => $this->name,
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

            if ($driverDetail->application) {
                $address = $driverDetail->application->addresses()->first();
                if ($address) {
                    $address->update($addressData);
                } else {
                    $driverDetail->application->addresses()->create($addressData);
                }
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

        // Update vehicle assignment using new VehicleDriverAssignmentController
        if ($this->assigned_vehicle_id) {
            // Remove any existing assignments for this driver
            \App\Models\VehicleDriverAssignment::where('user_id', $driverDetail->user_id)
                ->where('status', 'active')
                ->update(['status' => 'inactive', 'end_date' => now()]);
            
            // Create new assignment
            \App\Models\VehicleDriverAssignment::create([
                'vehicle_id' => $this->assigned_vehicle_id,
                'user_id' => $driverDetail->user_id,
                'assignment_type' => 'company_driver', // Default type, can be adjusted based on applying_position
                'start_date' => now(),
                'status' => 'active',
                'assigned_by' => auth()->id()
            ]);
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
            $this->owner_name = trim($this->name . ' ' . $this->last_name);
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