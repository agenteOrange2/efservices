<?php

namespace App\Livewire\Admin\Driver;

use App\Models\User;
use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\LicenseEndorsement;
use App\Helpers\Constants;
use App\Services\Admin\DriverStepService;
use App\Services\Admin\TempUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;

class DriverRegistrationWizard extends Component
{
    use WithFileUploads;

    // Carrier model
    public Carrier $carrier;

    // UserDriverDetail model (null when creating a new driver)
    public ?UserDriverDetail $userDriverDetail = null;

    // Current step (1-6)
    public int $currentStep = 1;

    // Progress and status tracking
    public array $stepsStatus = [];
    public int $completionPercentage = 0;

    // General form fields (Step 1)
    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('nullable|string|max:255')]
    public $middle_name = '';

    #[Rule('required|string|max:255')]
    public $last_name = '';

    #[Rule('required|email|max:255')]
    public $email = '';

    #[Rule('required|string|max:15')]
    public $phone = '';

    #[Rule('required|date')]
    public $date_of_birth = '';

    #[Rule('required_if:isEditMode,false|min:8')]
    public $password = '';

    #[Rule('required_if:isEditMode,false|same:password')]
    public $password_confirmation = '';

    public $status = 1; // Default: active
    public $terms_accepted = false;
    public $photo; // For profile photo upload

    // Address fields (Step 1)
    #[Rule('required|string|max:255')]
    public $address_line1 = '';

    #[Rule('nullable|string|max:255')]
    public $address_line2 = '';

    #[Rule('required|string|max:255')]
    public $city = '';

    #[Rule('required|string|max:255')]
    public $state = '';

    #[Rule('required|string|max:255')]
    public $zip_code = '';

    #[Rule('required|date')]
    public $from_date = '';

    #[Rule('nullable|date')]
    public $to_date = '';

    public $lived_three_years = false;
    public $previous_addresses = [];
    public $total_years = 0;

    // Application Details (Step 1)
    #[Rule('required|string')]
    public $applying_position = '';

    #[Rule('required_if:applying_position,other')]
    public $applying_position_other = '';

    #[Rule('required|string')]
    public $applying_location = '';

    public $eligible_to_work = true;
    public $can_speak_english = true;
    public $has_twic_card = false;

    #[Rule('required_if:has_twic_card,true|nullable|date')]
    public $twic_expiration_date = null;

    public $how_did_hear = 'internet';
    public $how_did_hear_other = '';
    public $referral_employee_name = '';
    public $expected_pay = '';

    // Work History (Step 1)
    public $has_work_history = false;
    public $work_histories = [];

    // License fields (Step 2)
    #[Rule('required|string|max:255')]
    public $current_license_number = '';

    public $licenses = [];
    public $experiences = [];

    // Medical Information (Step 3)
    #[Rule('required|string|max:255')]
    public $social_security_number = '';

    public $hire_date = null;
    public $location = '';
    public $is_suspended = false;
    public $suspension_date = null;
    public $is_terminated = false;
    public $termination_date = null;

    #[Rule('required|string|max:255')]
    public $medical_examiner_name = '';

    #[Rule('required|string|max:255')]
    public $medical_examiner_registry_number = '';

    #[Rule('required|date')]
    public $medical_card_expiration_date = '';

    public $medical_card_file;

    // Training History (Step 4)
    public $has_attended_training_school = false;
    public $training_schools = [];

    // Traffic Record (Step 5)
    public $has_traffic_convictions = false;
    public $traffic_convictions = [];

    // Accident History (Step 6)
    public $has_accidents = false;
    public $accidents = [];

    // Helper properties
    public $isEditMode = false;
    protected $usStates;
    protected $driverPositions;
    protected $referralSources;

    public function mount(Carrier $carrier, ?UserDriverDetail $userDriverDetail = null)
    {
        $this->carrier = $carrier;
        $this->userDriverDetail = $userDriverDetail;
        $this->isEditMode = $userDriverDetail !== null;

        // Load helper data
        $this->usStates = Constants::usStates();
        $this->driverPositions = Constants::driverPositions();
        $this->referralSources = Constants::referralSources();

        // Initialize steps status
        $driverStepService = app(DriverStepService::class);

        if ($this->isEditMode) {
            // Load driver data for edit mode
            $this->loadDriverData();

            // Calculate steps status and completion
            $this->stepsStatus = $driverStepService->getStepsStatus($this->userDriverDetail);
            $this->completionPercentage = $driverStepService->calculateCompletionPercentage($this->userDriverDetail);
            $this->currentStep = $this->userDriverDetail->current_step ?: 1;
        } else {
            // Initialize default values for new driver
            $this->initializeDefaultValues();

            // For new drivers, set all steps to missing initially
            $this->stepsStatus = array_fill(1, 6, DriverStepService::STATUS_MISSING);
            $this->completionPercentage = 0;
        }
    }

    /**
     * Proceed to the next step
     */
    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        // Save current step data
        $this->saveCurrentStep();

        // Move to the next step
        if ($this->currentStep < 6) {
            $this->currentStep++;
        }

        // Update step in DB if in edit mode
        if ($this->isEditMode && $this->userDriverDetail) {
            $this->userDriverDetail->update(['current_step' => $this->currentStep]);
        }
    }

    /**
     * Go back to the previous step
     */
    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }

        // Update step in DB if in edit mode
        if ($this->isEditMode && $this->userDriverDetail) {
            $this->userDriverDetail->update(['current_step' => $this->currentStep]);
        }
    }



    protected function initializeDefaultValues()
    {

        // Default dates to current date
        $this->date_of_birth = '';
        $this->from_date = now()->format('Y-m-d');
        $this->to_date = null; // Current date by default

        $this->total_years = 0;
        $this->lived_three_years = false;
        $this->date_of_birth = null;
        $this->from_date = now()->format('Y-m-d');
        $this->to_date = null;

        // Default licenses
        $this->licenses = [
            [
                'license_number' => '',
                'state_of_issue' => '',
                'license_class' => '',
                'expiration_date' => '',
                'is_cdl' => false,
                'endorsements' => [],
            ]
        ];

        // Default experiences
        $this->experiences = [
            [
                'equipment_type' => '',
                'years_experience' => '',
                'miles_driven' => '',
                'requires_cdl' => false,
            ]
        ];

        // Empty work histories
        $this->work_histories = [];

        // Empty training schools
        $this->training_schools = [];

        // Empty traffic convictions
        $this->traffic_convictions = [];

        // Empty accidents
        $this->accidents = [];
    }

    protected function loadDriverData()
    {
        // Verificar que userDriverDetail existe y tiene el usuario relacionado cargado
        if (!$this->userDriverDetail || !$this->userDriverDetail->user) {
            // Log para depuración
            Log::error('Error loading driver data: userDriverDetail or user is null', [
                'userDriverDetail_exists' => !is_null($this->userDriverDetail),
                'user_relation_exists' => $this->userDriverDetail && !is_null($this->userDriverDetail->user),
                'userDriverDetail_id' => $this->userDriverDetail ? $this->userDriverDetail->id : null
            ]);

            // Puedes redirigir o simplemente retornar sin cargar datos
            session()->flash('error', 'No se pudo cargar la información del conductor. Por favor, inténtelo de nuevo.');
            return;
        }

        // User basic info - Ahora con verificaciones de seguridad
        $this->name = $this->userDriverDetail->user->name ?? '';
        $this->email = $this->userDriverDetail->user->email ?? '';
        $this->middle_name = $this->userDriverDetail->middle_name ?? '';
        $this->last_name = $this->userDriverDetail->last_name ?? '';
        $this->phone = $this->userDriverDetail->phone ?? '';
        $this->date_of_birth = $this->userDriverDetail->date_of_birth ?? null;
        $this->status = $this->userDriverDetail->status ?? 1;
        $this->terms_accepted = $this->userDriverDetail->terms_accepted ?? false;

        // Load application and details
        $application = $this->userDriverDetail->application;
        if ($application) {
            // Load addresses
            $mainAddress = $application->addresses()->where('primary', true)->first();
            if ($mainAddress) {
                $this->address_line1 = $mainAddress->address_line1 ?? '';
                $this->address_line2 = $mainAddress->address_line2 ?? '';
                $this->city = $mainAddress->city ?? '';
                $this->state = $mainAddress->state ?? '';
                $this->zip_code = $mainAddress->zip_code ?? '';
                $this->from_date = $mainAddress->from_date ? $mainAddress->from_date->format('Y-m-d') : null;
                $this->to_date = $mainAddress->to_date ? $mainAddress->to_date->format('Y-m-d') : null;
                $this->lived_three_years = $mainAddress->lived_three_years ?? false;
            }

            // Load previous addresses
            $previousAddresses = $application->addresses()->where('primary', false)->get();
            $this->previous_addresses = $previousAddresses->map(function ($address) {
                return [
                    'id' => $address->id,
                    'address_line1' => $address->address_line1 ?? '',
                    'address_line2' => $address->address_line2 ?? '',
                    'city' => $address->city ?? '',
                    'state' => $address->state ?? '',
                    'zip_code' => $address->zip_code ?? '',
                    'from_date' => $address->from_date ? $address->from_date->format('Y-m-d') : null,
                    'to_date' => $address->to_date ? $address->to_date->format('Y-m-d') : null,
                ];
            })->toArray();

            // Load application details
            if ($application->details) {
                $this->applying_position = $application->details->applying_position ?? '';
                $this->applying_position_other = $application->details->applying_position_other ?? '';
                $this->applying_location = $application->details->applying_location ?? '';
                $this->eligible_to_work = $application->details->eligible_to_work ?? true;
                $this->can_speak_english = $application->details->can_speak_english ?? true;
                $this->has_twic_card = $application->details->has_twic_card ?? false;
                $this->twic_expiration_date = $application->details->twic_expiration_date ? $application->details->twic_expiration_date->format('Y-m-d') : null;
                $this->how_did_hear = $application->details->how_did_hear ?? 'internet';
                $this->how_did_hear_other = $application->details->how_did_hear_other ?? '';
                $this->referral_employee_name = $application->details->referral_employee_name ?? '';
                $this->expected_pay = $application->details->expected_pay ?? '';
                $this->has_attended_training_school = $application->details->has_attended_training_school ?? false;
                $this->has_traffic_convictions = $application->details->has_traffic_convictions ?? false;
                $this->has_accidents = $application->details->has_accidents ?? false;
                $this->has_work_history = $application->details->has_work_history ?? false;
            }
        }

        // Load work history
        $this->work_histories = $this->userDriverDetail->workHistories->map(function ($history) {
            return [
                'id' => $history->id,
                'previous_company' => $history->previous_company ?? '',
                'start_date' => $history->start_date ? $history->start_date->format('Y-m-d') : null,
                'end_date' => $history->end_date ? $history->end_date->format('Y-m-d') : null,
                'location' => $history->location ?? '',
                'position' => $history->position ?? '',
                'reason_for_leaving' => $history->reason_for_leaving ?? '',
                'reference_contact' => $history->reference_contact ?? '',
            ];
        })->toArray();

        // Load licenses
        $this->licenses = $this->userDriverDetail->licenses->map(function ($license) {
            return [
                'id' => $license->id,
                'license_number' => $license->license_number ?? '',
                'state_of_issue' => $license->state_of_issue ?? '',
                'license_class' => $license->license_class ?? '',
                'expiration_date' => $license->expiration_date ? $license->expiration_date->format('Y-m-d') : null,
                'is_cdl' => $license->is_cdl ?? false,
                'is_primary' => $license->is_primary ?? false,
                'endorsements' => $license->endorsements ? $license->endorsements->pluck('code')->toArray() : [],
                'front_url' => $license->getFirstMediaUrl('license_front') ?: null,
                'back_url' => $license->getFirstMediaUrl('license_back') ?: null,
            ];
        })->toArray();

        if (!empty($this->licenses)) {
            $primaryLicense = collect($this->licenses)->firstWhere('is_primary', true);
            if ($primaryLicense) {
                $this->current_license_number = $primaryLicense['license_number'] ?? '';
            }
        }

        // If no licenses, add an empty one
        if (empty($this->licenses)) {
            $this->licenses = [[
                'license_number' => '',
                'state_of_issue' => '',
                'license_class' => '',
                'expiration_date' => '',
                'is_cdl' => false,
                'endorsements' => [],
            ]];
        }

        // Load driving experiences
        $this->experiences = $this->userDriverDetail->experiences->map(function ($exp) {
            return [
                'id' => $exp->id,
                'equipment_type' => $exp->equipment_type ?? '',
                'years_experience' => $exp->years_experience ?? '',
                'miles_driven' => $exp->miles_driven ?? '',
                'requires_cdl' => $exp->requires_cdl ?? false,
            ];
        })->toArray();

        // If no experiences, add an empty one
        if (empty($this->experiences)) {
            $this->experiences = [[
                'equipment_type' => '',
                'years_experience' => '',
                'miles_driven' => '',
                'requires_cdl' => false,
            ]];
        }

        // Load medical information
        $medicalQualification = $this->userDriverDetail->medicalQualification;
        if ($medicalQualification) {
            $this->social_security_number = $medicalQualification->social_security_number ?? '';
            $this->hire_date = $medicalQualification->hire_date ? $medicalQualification->hire_date->format('Y-m-d') : null;
            $this->location = $medicalQualification->location ?? '';
            $this->is_suspended = $medicalQualification->is_suspended ?? false;
            $this->suspension_date = $medicalQualification->suspension_date ? $medicalQualification->suspension_date->format('Y-m-d') : null;
            $this->is_terminated = $medicalQualification->is_terminated ?? false;
            $this->termination_date = $medicalQualification->termination_date ? $medicalQualification->termination_date->format('Y-m-d') : null;
            $this->medical_examiner_name = $medicalQualification->medical_examiner_name ?? '';
            $this->medical_examiner_registry_number = $medicalQualification->medical_examiner_registry_number ?? '';
            $this->medical_card_expiration_date = $medicalQualification->medical_card_expiration_date ? $medicalQualification->medical_card_expiration_date->format('Y-m-d') : null;
        }

        // Load training schools
        $this->training_schools = $this->userDriverDetail->trainingSchools->map(function ($school) {
            return [
                'id' => $school->id,
                'school_name' => $school->school_name ?? '',
                'city' => $school->city ?? '',
                'state' => $school->state ?? '',
                'phone_number' => $school->phone_number ?? '',
                'date_start' => $school->date_start ? $school->date_start->format('Y-m-d') : null,
                'date_end' => $school->date_end ? $school->date_end->format('Y-m-d') : null,
                'graduated' => $school->graduated ?? false,
                'subject_to_safety_regulations' => $school->subject_to_safety_regulations ?? false,
                'performed_safety_functions' => $school->performed_safety_functions ?? false,
                'training_skills' => $school->training_skills ?? [],
                'certificates_urls' => $school->getMedia('school_certificates') ? $school->getMedia('school_certificates')->map->getUrl()->toArray() : [],
            ];
        })->toArray();

        // Load traffic convictions
        $this->traffic_convictions = $this->userDriverDetail->trafficConvictions->map(function ($conviction) {
            return [
                'id' => $conviction->id,
                'conviction_date' => $conviction->conviction_date ? $conviction->conviction_date->format('Y-m-d') : null,
                'location' => $conviction->location ?? '',
                'charge' => $conviction->charge ?? '',
                'penalty' => $conviction->penalty ?? '',
            ];
        })->toArray();

        // Load accidents
        $this->accidents = $this->userDriverDetail->accidents->map(function ($accident) {
            return [
                'id' => $accident->id,
                'accident_date' => $accident->accident_date ? $accident->accident_date->format('Y-m-d') : null,
                'nature_of_accident' => $accident->nature_of_accident ?? '',
                'had_injuries' => $accident->had_injuries ?? false,
                'number_of_injuries' => $accident->number_of_injuries ?? 0,
                'had_fatalities' => $accident->had_fatalities ?? false,
                'number_of_fatalities' => $accident->number_of_fatalities ?? 0,
                'comments' => $accident->comments ?? '',
            ];
        })->toArray();

        // Calculate total years for address validation
        $this->calculateTotalYears();
    }

    public function calculateTotalYears()
    {
        if ($this->lived_three_years) {
            $this->total_years = 3;
            return;
        }

        $totalYears = 0;

        // Calculate years for the current address
        if ($this->from_date) {
            $fromDate = new \DateTime($this->from_date);
            $toDate = $this->to_date ? new \DateTime($this->to_date) : new \DateTime();

            if ($fromDate && $toDate && $fromDate <= $toDate) {
                $diff = $fromDate->diff($toDate);
                $years = $diff->y + $diff->m / 12 + $diff->d / 365;
                $totalYears += $years;
            }
        }

        // Add years from previous addresses
        foreach ($this->previous_addresses as $address) {
            if (!empty($address['from_date']) && !empty($address['to_date'])) {
                $fromDate = new \DateTime($address['from_date']);
                $toDate = new \DateTime($address['to_date']);

                if ($fromDate && $toDate && $fromDate <= $toDate) {
                    $diff = $fromDate->diff($toDate);
                    $years = $diff->y + $diff->m / 12 + $diff->d / 365;
                    $totalYears += $years;
                }
                break;
            }
        }

        $this->total_years = min($totalYears, 3);
    }

    public function addPreviousAddress()
    {
        $newAddress = [
            'id' => time(),
            'address_line1' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
            'from_date' => '',
            'to_date' => '',
        ];
        
        if (!is_array($this->previous_addresses)) {
            $this->previous_addresses = [];
        }
        
        $this->previous_addresses[] = $newAddress;
        
        // Dispatch un evento para que Alpine detecte el cambio
        $this->dispatch('address-added');
    }

    public function removePreviousAddress($index)
    {
        if (isset($this->previous_addresses[$index])) {
            unset($this->previous_addresses[$index]);
            $this->previous_addresses = array_values($this->previous_addresses);
        }
    }

    public function addWorkHistory()
    {
        $this->work_histories[] = [
            'previous_company' => '',
            'start_date' => '',
            'end_date' => '',
            'location' => '',
            'position' => '',
            'reason_for_leaving' => '',
            'reference_contact' => '',
        ];
    }

    public function removeWorkHistory($index)
    {
        unset($this->work_histories[$index]);
        $this->work_histories = array_values($this->work_histories);
    }

    public function addLicense()
    {
        $this->licenses[] = [
            'license_number' => '',
            'state_of_issue' => '',
            'license_class' => '',
            'expiration_date' => '',
            'is_cdl' => false,
            'endorsements' => [],
        ];
    }

    public function removeLicense($index)
    {
        if (count($this->licenses) > 1) {
            unset($this->licenses[$index]);
            $this->licenses = array_values($this->licenses);
        }
    }

    public function toggleEndorsement($licenseIndex, $endorsement)
    {
        if (!isset($this->licenses[$licenseIndex]['endorsements'])) {
            $this->licenses[$licenseIndex]['endorsements'] = [];
        }

        $endorsements = $this->licenses[$licenseIndex]['endorsements'];

        if (in_array($endorsement, $endorsements)) {
            $this->licenses[$licenseIndex]['endorsements'] = array_diff($endorsements, [$endorsement]);
        } else {
            $this->licenses[$licenseIndex]['endorsements'][] = $endorsement;
        }
    }

    public function addExperience()
    {
        $this->experiences[] = [
            'equipment_type' => '',
            'years_experience' => '',
            'miles_driven' => '',
            'requires_cdl' => false,
        ];
    }

    public function removeExperience($index)
    {
        if (count($this->experiences) > 1) {
            unset($this->experiences[$index]);
            $this->experiences = array_values($this->experiences);
        }
    }

    public function addTrainingSchool()
    {
        $this->training_schools[] = [
            'school_name' => '',
            'city' => '',
            'state' => '',
            'phone_number' => '',
            'date_start' => '',
            'date_end' => '',
            'graduated' => false,
            'subject_to_safety_regulations' => false,
            'performed_safety_functions' => false,
            'training_skills' => [],
            'certificates' => [],
        ];
    }

    public function removeTrainingSchool($index)
    {
        unset($this->training_schools[$index]);
        $this->training_schools = array_values($this->training_schools);
    }

    public function addTrafficConviction()
    {
        $this->traffic_convictions[] = [
            'conviction_date' => '',
            'location' => '',
            'charge' => '',
            'penalty' => '',
        ];
    }

    public function removeTrafficConviction($index)
    {
        unset($this->traffic_convictions[$index]);
        $this->traffic_convictions = array_values($this->traffic_convictions);
    }

    public function addAccident()
    {
        $this->accidents[] = [
            'accident_date' => '',
            'nature_of_accident' => '',
            'had_injuries' => false,
            'number_of_injuries' => 0,
            'had_fatalities' => false,
            'number_of_fatalities' => 0,
            'comments' => '',
        ];
    }

    public function removeAccident($index)
    {
        unset($this->accidents[$index]);
        $this->accidents = array_values($this->accidents);
    }

    public function updatedLivedThreeYears()
    {
        if ($this->lived_three_years) {
            $this->total_years = 3;
        } else {
            $this->calculateTotalYears();
        }
    }

    public function updatedFromDate()
    {
        $this->calculateTotalYears();
    }

    public function updatedToDate()
    {
        $this->calculateTotalYears();
    }

    public function updatedPreviousAddresses()
    {
        $this->calculateTotalYears();
    }

    /**
     * Validate the current step
     */
    public function validateCurrentStep()
    {
        switch ($this->currentStep) {
            case 1: // General Information
                $this->validate([
                    'name' => 'required|string|max:255',
                    'email' => $this->isEditMode
                        ? 'required|email|max:255|unique:users,email,' . $this->userDriverDetail->user_id
                        : 'required|email|max:255|unique:users,email',
                    'password' => $this->isEditMode ? 'nullable|min:8' : 'required|min:8',
                    'password_confirmation' => $this->isEditMode ? 'nullable|same:password' : 'required|same:password',
                    'middle_name' => 'nullable|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'phone' => 'required|string|max:15',
                    'date_of_birth' => 'required|date',
                    'terms_accepted' => 'accepted',
                    'address_line1' => 'required|string|max:255',
                    'city' => 'required|string|max:255',
                    'state' => 'required|string|max:255',
                    'zip_code' => 'required|string|max:255',
                    'from_date' => 'required|date',
                    'applying_position' => 'required|string',
                    'applying_position_other' => 'required_if:applying_position,other',
                    'applying_location' => 'required|string',
                ]);

                // Validate "lived three years" or previous addresses
                if (!$this->lived_three_years && $this->total_years < 3) {
                    $this->addError('total_years', 'Address history must cover at least 3 years. Please add previous addresses.');
                }

                // Validate work history if selected
                if ($this->has_work_history && !empty($this->work_histories)) {
                    foreach ($this->work_histories as $index => $history) {
                        if (empty($history['previous_company'])) {
                            $this->addError("work_histories.{$index}.previous_company", 'Company name is required');
                        }
                        if (empty($history['start_date'])) {
                            $this->addError("work_histories.{$index}.start_date", 'Start date is required');
                        }
                        if (empty($history['end_date'])) {
                            $this->addError("work_histories.{$index}.end_date", 'End date is required');
                        }
                        if (empty($history['location'])) {
                            $this->addError("work_histories.{$index}.location", 'Location is required');
                        }
                        if (empty($history['position'])) {
                            $this->addError("work_histories.{$index}.position", 'Position is required');
                        }
                    }
                }
                break;

            case 2: // Licenses & Experience
                $this->validate([
                    'current_license_number' => 'required|string|max:255',
                ]);

                // Validate licenses
                foreach ($this->licenses as $index => $license) {
                    if (empty($license['license_number'])) {
                        $this->addError("licenses.{$index}.license_number", 'License number is required');
                    }
                    if (empty($license['state_of_issue'])) {
                        $this->addError("licenses.{$index}.state_of_issue", 'State is required');
                    }
                    if (empty($license['license_class'])) {
                        $this->addError("licenses.{$index}.license_class", 'License class is required');
                    }
                    if (empty($license['expiration_date'])) {
                        $this->addError("licenses.{$index}.expiration_date", 'Expiration date is required');
                    }
                }

                // Validate experiences
                foreach ($this->experiences as $index => $experience) {
                    if (empty($experience['equipment_type'])) {
                        $this->addError("experiences.{$index}.equipment_type", 'Equipment type is required');
                    }
                    if ($experience['years_experience'] === '') {
                        $this->addError("experiences.{$index}.years_experience", 'Years of experience is required');
                    }
                    if ($experience['miles_driven'] === '') {
                        $this->addError("experiences.{$index}.miles_driven", 'Miles driven is required');
                    }
                }
                break;

            case 3: // Medical Information
                $this->validate([
                    'social_security_number' => 'required|string|max:255',
                    'medical_examiner_name' => 'required|string|max:255',
                    'medical_examiner_registry_number' => 'required|string|max:255',
                    'medical_card_expiration_date' => 'required|date',
                    'suspension_date' => 'nullable|required_if:is_suspended,true|date',
                    'termination_date' => 'nullable|required_if:is_terminated,true|date',
                ]);
                break;

            case 4: // Training History
                if ($this->has_attended_training_school && !empty($this->training_schools)) {
                    foreach ($this->training_schools as $index => $school) {
                        if (empty($school['school_name'])) {
                            $this->addError("training_schools.{$index}.school_name", 'School name is required');
                        }
                        if (empty($school['city'])) {
                            $this->addError("training_schools.{$index}.city", 'City is required');
                        }
                        if (empty($school['state'])) {
                            $this->addError("training_schools.{$index}.state", 'State is required');
                        }
                        if (empty($school['date_start'])) {
                            $this->addError("training_schools.{$index}.date_start", 'Start date is required');
                        }
                        if (empty($school['date_end'])) {
                            $this->addError("training_schools.{$index}.date_end", 'End date is required');
                        }

                        // Validate date range
                        if (!empty($school['date_start']) && !empty($school['date_end'])) {
                            if (strtotime($school['date_end']) < strtotime($school['date_start'])) {
                                $this->addError("training_schools.{$index}.date_end", 'End date must be after start date');
                            }
                        }
                    }
                }
                break;

            case 5: // Traffic Record
                if ($this->has_traffic_convictions && !empty($this->traffic_convictions)) {
                    foreach ($this->traffic_convictions as $index => $conviction) {
                        if (empty($conviction['conviction_date'])) {
                            $this->addError("traffic_convictions.{$index}.conviction_date", 'Date is required');
                        }
                        if (empty($conviction['location'])) {
                            $this->addError("traffic_convictions.{$index}.location", 'Location is required');
                        }
                        if (empty($conviction['charge'])) {
                            $this->addError("traffic_convictions.{$index}.charge", 'Charge is required');
                        }
                        if (empty($conviction['penalty'])) {
                            $this->addError("traffic_convictions.{$index}.penalty", 'Penalty is required');
                        }
                    }
                }
                break;

            case 6: // Accident History
                if ($this->has_accidents && !empty($this->accidents)) {
                    foreach ($this->accidents as $index => $accident) {
                        if (empty($accident['accident_date'])) {
                            $this->addError("accidents.{$index}.accident_date", 'Date is required');
                        }
                        if (empty($accident['nature_of_accident'])) {
                            $this->addError("accidents.{$index}.nature_of_accident", 'Nature of accident is required');
                        }
                        if ($accident['had_injuries'] && (!isset($accident['number_of_injuries']) || $accident['number_of_injuries'] < 1)) {
                            $this->addError("accidents.{$index}.number_of_injuries", 'Number of injuries is required');
                        }
                        if ($accident['had_fatalities'] && (!isset($accident['number_of_fatalities']) || $accident['number_of_fatalities'] < 1)) {
                            $this->addError("accidents.{$index}.number_of_fatalities", 'Number of fatalities is required');
                        }
                    }
                }
                break;
        }
    }





    /**
     * Save the current step data
     */
    protected function saveCurrentStep()
    {
        try {
            DB::beginTransaction();

            // If it's a new driver, create the records
            if (!$this->isEditMode) {
                if ($this->currentStep === 1) {
                    $this->createNewDriver();
                }
            }

            // Save data based on the current step
            switch ($this->currentStep) {
                case 1:
                    $this->saveGeneralInfo();
                    break;
                case 2:
                    $this->saveLicensesAndExperience();
                    break;
                case 3:
                    $this->saveMedicalInfo();
                    break;
                case 4:
                    $this->saveTrainingInfo();
                    break;
                case 5:
                    $this->saveTrafficInfo();
                    break;
                case 6:
                    $this->saveAccidentInfo();
                    break;
            }

            // Update status and completion percentage
            $driverStepService = app(DriverStepService::class);
            $this->stepsStatus = $driverStepService->getStepsStatus($this->userDriverDetail);
            $this->completionPercentage = $driverStepService->calculateCompletionPercentage($this->userDriverDetail);

            DB::commit();

            // Show success message
            session()->flash('message', 'Successfully saved step ' . $this->currentStep);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving driver step ' . $this->currentStep, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving data: ' . $e->getMessage());
        }
    }

    /**
     * Create a new driver record (first step)
     */
    protected function createNewDriver()
    {
        // Create user
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'status' => $this->status
        ]);

        // Assign driver role
        $user->assignRole('driver');

        // Create driver detail
        $this->userDriverDetail = UserDriverDetail::create([
            'user_id' => $user->id,
            'carrier_id' => $this->carrier->id,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth,
            'status' => $this->status,
            'terms_accepted' => $this->terms_accepted,
            'confirmation_token' => Str::random(60),
            'current_step' => 1
        ]);

        // Set edit mode to true now that we have a driver
        $this->isEditMode = true;
    }

    /**
     * Save general information (Step 1)
     */
    protected function saveGeneralInfo()
    {
        // Update user information
        $this->userDriverDetail->user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        // Update password if provided
        if (!empty($this->password)) {
            $this->userDriverDetail->user->update([
                'password' => Hash::make($this->password),
            ]);
        }

        // Update driver details
        $this->userDriverDetail->update([
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth,
            'status' => $this->status,
            'terms_accepted' => $this->terms_accepted,
        ]);

        // Upload profile photo if provided
        if ($this->photo) {
            $this->userDriverDetail->clearMediaCollection('profile_photo_driver');
            $fileName = strtolower(str_replace(' ', '_', $this->userDriverDetail->user->name)) . '.webp';
            $this->userDriverDetail->addMedia($this->photo->getRealPath())
                ->usingFileName($fileName)
                ->toMediaCollection('profile_photo_driver');
        }

        // Create or update application
        $application = $this->userDriverDetail->application;
        if (!$application) {
            $application = DriverApplication::create([
                'user_id' => $this->userDriverDetail->user_id,
                'status' => 'draft'
            ]);
        }

        // Update main address
        $application->addresses()->updateOrCreate(
            ['primary' => true],
            [
                'address_line1' => $this->address_line1,
                'address_line2' => $this->address_line2,
                'city' => $this->city,
                'state' => $this->state,
                'zip_code' => $this->zip_code,
                'lived_three_years' => $this->lived_three_years,
                'from_date' => $this->from_date,
                'to_date' => $this->to_date
            ]
        );

        // Handle previous addresses
        $application->addresses()->where('primary', false)->delete();

        if (!$this->lived_three_years && !empty($this->previous_addresses)) {
            foreach ($this->previous_addresses as $prevAddress) {
                if (
                    !empty($prevAddress['address_line1']) &&
                    !empty($prevAddress['city']) &&
                    !empty($prevAddress['state']) &&
                    !empty($prevAddress['zip_code']) &&
                    !empty($prevAddress['from_date']) &&
                    !empty($prevAddress['to_date'])
                ) {
                    $application->addresses()->create([
                        'primary' => false,
                        'address_line1' => $prevAddress['address_line1'],
                        'address_line2' => $prevAddress['address_line2'] ?? null,
                        'city' => $prevAddress['city'],
                        'state' => $prevAddress['state'],
                        'zip_code' => $prevAddress['zip_code'],
                        'from_date' => $prevAddress['from_date'],
                        'to_date' => $prevAddress['to_date'],
                        'lived_three_years' => false
                    ]);
                }
            }
        }

        // Update application details
        $application->details()->updateOrCreate(
            [],
            [
                'applying_position' => $this->applying_position,
                'applying_position_other' => $this->applying_position === 'other'
                    ? $this->applying_position_other
                    : null,
                'applying_location' => $this->applying_location,
                'eligible_to_work' => $this->eligible_to_work,
                'can_speak_english' => $this->can_speak_english,
                'has_twic_card' => $this->has_twic_card,
                'twic_expiration_date' => $this->has_twic_card ? $this->twic_expiration_date : null,
                'how_did_hear' => $this->how_did_hear,
                'how_did_hear_other' => $this->how_did_hear === 'other'
                    ? $this->how_did_hear_other
                    : null,
                'referral_employee_name' => $this->how_did_hear === 'employee_referral'
                    ? $this->referral_employee_name
                    : null,
                'expected_pay' => $this->expected_pay,
                'has_work_history' => $this->has_work_history
            ]
        );

        // Handle work history
        if ($this->has_work_history) {
            $existingWorkHistoryIds = $this->userDriverDetail->workHistories()->pluck('id')->toArray();
            $updatedWorkHistoryIds = [];

            foreach ($this->work_histories as $workHistory) {
                if (
                    empty($workHistory['previous_company']) ||
                    empty($workHistory['start_date']) ||
                    empty($workHistory['end_date']) ||
                    empty($workHistory['location']) ||
                    empty($workHistory['position'])
                ) {
                    continue;
                }

                $workHistoryId = $workHistory['id'] ?? null;
                $workHistoryRecord = null;

                if ($workHistoryId) {
                    $workHistoryRecord = $this->userDriverDetail->workHistories()->find($workHistoryId);
                }

                if (!$workHistoryRecord) {
                    $workHistoryRecord = $this->userDriverDetail->workHistories()->create([
                        'previous_company' => $workHistory['previous_company'],
                        'start_date' => $workHistory['start_date'],
                        'end_date' => $workHistory['end_date'],
                        'location' => $workHistory['location'],
                        'position' => $workHistory['position'],
                        'reason_for_leaving' => $workHistory['reason_for_leaving'] ?? null,
                        'reference_contact' => $workHistory['reference_contact'] ?? null,
                    ]);
                } else {
                    $workHistoryRecord->update([
                        'previous_company' => $workHistory['previous_company'],
                        'start_date' => $workHistory['start_date'],
                        'end_date' => $workHistory['end_date'],
                        'location' => $workHistory['location'],
                        'position' => $workHistory['position'],
                        'reason_for_leaving' => $workHistory['reason_for_leaving'] ?? null,
                        'reference_contact' => $workHistory['reference_contact'] ?? null,
                    ]);
                }

                $updatedWorkHistoryIds[] = $workHistoryRecord->id;
            }

            // Delete work histories that were removed
            $workHistoriesToDelete = array_diff($existingWorkHistoryIds, $updatedWorkHistoryIds);
            if (!empty($workHistoriesToDelete)) {
                $this->userDriverDetail->workHistories()->whereIn('id', $workHistoriesToDelete)->delete();
            }
        } else {
            // If no work history, delete all records
            $this->userDriverDetail->workHistories()->delete();
        }
    }

    /**
     * Save licenses and experience information (Step 2)
     */
    protected function saveLicensesAndExperience()
    {
        // Update licenses
        $existingLicenseIds = $this->userDriverDetail->licenses()->pluck('id')->toArray();
        $updatedLicenseIds = [];

        foreach ($this->licenses as $index => $licenseData) {
            $licenseId = $licenseData['id'] ?? null;
            $license = null;

            if ($licenseId) {
                $license = $this->userDriverDetail->licenses()->find($licenseId);
            }

            if (!$license) {
                // Create new license
                $license = $this->userDriverDetail->licenses()->create([
                    'current_license_number' => $this->current_license_number,
                    'license_number' => $licenseData['license_number'],
                    'state_of_issue' => $licenseData['state_of_issue'],
                    'license_class' => $licenseData['license_class'],
                    'expiration_date' => $licenseData['expiration_date'],
                    'is_cdl' => $licenseData['is_cdl'] ?? false,
                    'is_primary' => $index === 0, // First license is primary
                    'status' => 'active',
                ]);
            } else {
                // Update existing license
                $license->update([
                    'license_number' => $licenseData['license_number'],
                    'state_of_issue' => $licenseData['state_of_issue'],
                    'license_class' => $licenseData['license_class'],
                    'expiration_date' => $licenseData['expiration_date'],
                    'is_cdl' => $licenseData['is_cdl'] ?? false,
                    'is_primary' => $index === 0,
                ]);
            }

            $updatedLicenseIds[] = $license->id;

            // Handle endorsements
            if (isset($licenseData['is_cdl']) && $licenseData['is_cdl'] && isset($licenseData['endorsements'])) {
                // Remove existing endorsements
                $license->endorsements()->detach();

                // Add new endorsements
                foreach ($licenseData['endorsements'] as $endorsementCode) {
                    $endorsement = LicenseEndorsement::firstOrCreate(
                        ['code' => $endorsementCode],
                        [
                            'name' => $this->getEndorsementName($endorsementCode),
                            'description' => null,
                            'is_active' => true
                        ]
                    );

                    $license->endorsements()->attach($endorsement->id, [
                        'issued_date' => now(),
                        'expiration_date' => $licenseData['expiration_date']
                    ]);
                }
            }

            // Handle license images
            if (!empty($licenseData['front_image'])) {
                $license->clearMediaCollection('license_front');
                $license->addMedia($licenseData['front_image']->getRealPath())
                    ->toMediaCollection('license_front');
            }

            if (!empty($licenseData['back_image'])) {
                $license->clearMediaCollection('license_back');
                $license->addMedia($licenseData['back_image']->getRealPath())
                    ->toMediaCollection('license_back');
            }
        }

        // Delete licenses that were removed
        $licensesToDelete = array_diff($existingLicenseIds, $updatedLicenseIds);
        if (!empty($licensesToDelete)) {
            foreach ($licensesToDelete as $licenseId) {
                $license = $this->userDriverDetail->licenses()->find($licenseId);
                if ($license) {
                    $license->clearMediaCollection('license_front');
                    $license->clearMediaCollection('license_back');
                    $license->endorsements()->detach();
                    $license->delete();
                }
            }
        }

        // Update experiences
        $existingExpIds = $this->userDriverDetail->experiences()->pluck('id')->toArray();
        $updatedExpIds = [];

        foreach ($this->experiences as $expData) {
            if (
                $expData['equipment_type'] === '' ||
                $expData['years_experience'] === '' ||
                $expData['miles_driven'] === ''
            ) {
                continue;
            }

            $expId = $expData['id'] ?? null;
            $experience = null;

            if ($expId) {
                $experience = $this->userDriverDetail->experiences()->find($expId);
            }

            if (!$experience) {
                $experience = $this->userDriverDetail->experiences()->create([
                    'equipment_type' => $expData['equipment_type'],
                    'years_experience' => $expData['years_experience'],
                    'miles_driven' => $expData['miles_driven'],
                    'requires_cdl' => $expData['requires_cdl'] ?? false,
                ]);
            } else {
                $experience->update([
                    'equipment_type' => $expData['equipment_type'],
                    'years_experience' => $expData['years_experience'],
                    'miles_driven' => $expData['miles_driven'],
                    'requires_cdl' => $expData['requires_cdl'] ?? false,
                ]);
            }

            $updatedExpIds[] = $experience->id;
        }

        // Delete experiences that were removed
        $expsToDelete = array_diff($existingExpIds, $updatedExpIds);
        if (!empty($expsToDelete)) {
            $this->userDriverDetail->experiences()->whereIn('id', $expsToDelete)->delete();
        }
    }

    /**
     * Save medical information (Step 3)
     */
    protected function saveMedicalInfo()
    {
        // Create or update medical qualification
        $medical = $this->userDriverDetail->medicalQualification()->updateOrCreate(
            [],
            [
                'social_security_number' => $this->social_security_number,
                'hire_date' => $this->hire_date,
                'location' => $this->location,
                'is_suspended' => $this->is_suspended,
                'suspension_date' => $this->is_suspended ? $this->suspension_date : null,
                'is_terminated' => $this->is_terminated,
                'termination_date' => $this->is_terminated ? $this->termination_date : null,
                'medical_examiner_name' => $this->medical_examiner_name,
                'medical_examiner_registry_number' => $this->medical_examiner_registry_number,
                'medical_card_expiration_date' => $this->medical_card_expiration_date,
            ]
        );

        // Handle medical card file
        if ($this->medical_card_file) {
            $medical->clearMediaCollection('medical_card');
            $medical->addMedia($this->medical_card_file->getRealPath())
                ->toMediaCollection('medical_card');
        }
    }

    /**
     * Save training information (Step 4)
     */
    protected function saveTrainingInfo()
    {
        // Update application details for training
        if ($this->userDriverDetail->application && $this->userDriverDetail->application->details) {
            $this->userDriverDetail->application->details->update([
                'has_attended_training_school' => $this->has_attended_training_school
            ]);
        }

        // Handle training schools
        if ($this->has_attended_training_school) {
            $existingTrainingIds = $this->userDriverDetail->trainingSchools()->pluck('id')->toArray();
            $updatedTrainingIds = [];

            foreach ($this->training_schools as $schoolData) {
                if (
                    empty($schoolData['school_name']) ||
                    empty($schoolData['city']) ||
                    empty($schoolData['state']) ||
                    empty($schoolData['date_start']) ||
                    empty($schoolData['date_end'])
                ) {
                    continue;
                }

                $schoolId = $schoolData['id'] ?? null;
                $trainingSchool = null;

                if ($schoolId) {
                    $trainingSchool = $this->userDriverDetail->trainingSchools()->find($schoolId);
                }

                if (!$trainingSchool) {
                    $trainingSchool = $this->userDriverDetail->trainingSchools()->create([
                        'school_name' => $schoolData['school_name'],
                        'city' => $schoolData['city'],
                        'state' => $schoolData['state'],
                        'phone_number' => $schoolData['phone_number'] ?? null,
                        'date_start' => $schoolData['date_start'],
                        'date_end' => $schoolData['date_end'],
                        'graduated' => $schoolData['graduated'] ?? false,
                        'subject_to_safety_regulations' => $schoolData['subject_to_safety_regulations'] ?? false,
                        'performed_safety_functions' => $schoolData['performed_safety_functions'] ?? false,
                        'training_skills' => $schoolData['training_skills'] ?? [],
                    ]);
                } else {
                    $trainingSchool->update([
                        'school_name' => $schoolData['school_name'],
                        'city' => $schoolData['city'],
                        'state' => $schoolData['state'],
                        'phone_number' => $schoolData['phone_number'] ?? null,
                        'date_start' => $schoolData['date_start'],
                        'date_end' => $schoolData['date_end'],
                        'graduated' => $schoolData['graduated'] ?? false,
                        'subject_to_safety_regulations' => $schoolData['subject_to_safety_regulations'] ?? false,
                        'performed_safety_functions' => $schoolData['performed_safety_functions'] ?? false,
                        'training_skills' => $schoolData['training_skills'] ?? [],
                    ]);
                }

                $updatedTrainingIds[] = $trainingSchool->id;

                // Handle certificate files
                if (!empty($schoolData['certificates'])) {
                    foreach ($schoolData['certificates'] as $certificate) {
                        $trainingSchool->addMedia($certificate->getRealPath())
                            ->toMediaCollection('school_certificates');
                    }
                }
            }

            // Delete training schools that were removed
            $schoolsToDelete = array_diff($existingTrainingIds, $updatedTrainingIds);
            if (!empty($schoolsToDelete)) {
                foreach ($schoolsToDelete as $schoolId) {
                    $school = $this->userDriverDetail->trainingSchools()->find($schoolId);
                    if ($school) {
                        $school->clearMediaCollection('school_certificates');
                        $school->delete();
                    }
                }
            }
        } else {
            // If no training schools, delete all records
            foreach ($this->userDriverDetail->trainingSchools as $school) {
                $school->clearMediaCollection('school_certificates');
                $school->delete();
            }
        }
    }

    /**
     * Save traffic information (Step 5)
     */
    protected function saveTrafficInfo()
    {
        // Update application details for traffic convictions
        if ($this->userDriverDetail->application && $this->userDriverDetail->application->details) {
            $this->userDriverDetail->application->details->update([
                'has_traffic_convictions' => $this->has_traffic_convictions
            ]);
        }

        // Handle traffic convictions
        if ($this->has_traffic_convictions) {
            $existingConvictionIds = $this->userDriverDetail->trafficConvictions()->pluck('id')->toArray();
            $updatedConvictionIds = [];

            foreach ($this->traffic_convictions as $convictionData) {
                if (
                    empty($convictionData['conviction_date']) ||
                    empty($convictionData['location']) ||
                    empty($convictionData['charge']) ||
                    empty($convictionData['penalty'])
                ) {
                    continue;
                }

                $convictionId = $convictionData['id'] ?? null;
                $trafficConviction = null;

                if ($convictionId) {
                    $trafficConviction = $this->userDriverDetail->trafficConvictions()->find($convictionId);
                }

                if (!$trafficConviction) {
                    $trafficConviction = $this->userDriverDetail->trafficConvictions()->create([
                        'conviction_date' => $convictionData['conviction_date'],
                        'location' => $convictionData['location'],
                        'charge' => $convictionData['charge'],
                        'penalty' => $convictionData['penalty'],
                    ]);
                } else {
                    $trafficConviction->update([
                        'conviction_date' => $convictionData['conviction_date'],
                        'location' => $convictionData['location'],
                        'charge' => $convictionData['charge'],
                        'penalty' => $convictionData['penalty'],
                    ]);
                }

                $updatedConvictionIds[] = $trafficConviction->id;
            }

            // Delete convictions that were removed
            $convictionsToDelete = array_diff($existingConvictionIds, $updatedConvictionIds);
            if (!empty($convictionsToDelete)) {
                $this->userDriverDetail->trafficConvictions()->whereIn('id', $convictionsToDelete)->delete();
            }
        } else {
            // If no traffic convictions, delete all records
            $this->userDriverDetail->trafficConvictions()->delete();
        }
    }

    /**
     * Save accident information (Step 6)
     */
    protected function saveAccidentInfo()
    {
        // Update application details for accidents
        if ($this->userDriverDetail->application && $this->userDriverDetail->application->details) {
            $this->userDriverDetail->application->details->update([
                'has_accidents' => $this->has_accidents
            ]);
        }

        // Handle accidents
        if ($this->has_accidents) {
            $existingAccidentIds = $this->userDriverDetail->accidents()->pluck('id')->toArray();
            $updatedAccidentIds = [];

            foreach ($this->accidents as $accidentData) {
                if (
                    empty($accidentData['accident_date']) ||
                    empty($accidentData['nature_of_accident'])
                ) {
                    continue;
                }

                $accidentId = $accidentData['id'] ?? null;
                $accident = null;

                if ($accidentId) {
                    $accident = $this->userDriverDetail->accidents()->find($accidentId);
                }

                if (!$accident) {
                    $accident = $this->userDriverDetail->accidents()->create([
                        'accident_date' => $accidentData['accident_date'],
                        'nature_of_accident' => $accidentData['nature_of_accident'],
                        'had_injuries' => $accidentData['had_injuries'] ?? false,
                        'number_of_injuries' => $accidentData['had_injuries'] ? ($accidentData['number_of_injuries'] ?? 0) : 0,
                        'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                        'number_of_fatalities' => $accidentData['had_fatalities'] ? ($accidentData['number_of_fatalities'] ?? 0) : 0,
                        'comments' => $accidentData['comments'] ?? null,
                    ]);
                } else {
                    $accident->update([
                        'accident_date' => $accidentData['accident_date'],
                        'nature_of_accident' => $accidentData['nature_of_accident'],
                        'had_injuries' => $accidentData['had_injuries'] ?? false,
                        'number_of_injuries' => $accidentData['had_injuries'] ? ($accidentData['number_of_injuries'] ?? 0) : 0,
                        'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                        'number_of_fatalities' => $accidentData['had_fatalities'] ? ($accidentData['number_of_fatalities'] ?? 0) : 0,
                        'comments' => $accidentData['comments'] ?? null,
                    ]);
                }

                $updatedAccidentIds[] = $accident->id;
            }

            // Delete accidents that were removed
            $accidentsToDelete = array_diff($existingAccidentIds, $updatedAccidentIds);
            if (!empty($accidentsToDelete)) {
                $this->userDriverDetail->accidents()->whereIn('id', $accidentsToDelete)->delete();
            }
        } else {
            // If no accidents, delete all records
            $this->userDriverDetail->accidents()->delete();
        }

        // Check if application is completed
        $this->checkApplicationCompleted();
    }

    /**
     * Check if all required steps are completed
     */
    protected function checkApplicationCompleted()
    {
        // Verify if it has at least:
        // - A license registered
        // - At least one driving experience
        // - Basic medical information            
        $hasLicense = $this->userDriverDetail->licenses()->exists();
        $hasExperience = $this->userDriverDetail->experiences()->exists();
        $hasMedical = $this->userDriverDetail->medicalQualification()->exists();

        $isCompleted = $hasLicense && $hasExperience && $hasMedical;

        // Update completion status
        $this->userDriverDetail->update([
            'application_completed' => $isCompleted
        ]);

        return $isCompleted;
    }

    /**
     * Save and finish the registration process
     */
    public function saveAndFinish()
    {
        // Save current step
        $this->saveCurrentStep();

        // Check if application is completed
        $isCompleted = $this->checkApplicationCompleted();

        // Reset to first step for future editing
        $this->userDriverDetail->update([
            'current_step' => DriverStepService::STEP_GENERAL
        ]);

        // Redirect to drivers list with success message
        $message = $isCompleted
            ? 'Driver registration completed successfully!'
            : 'Driver information saved. Some sections are still incomplete.';

        session()->flash('message', $message);

        // Redirect to the drivers list
        return redirect()->route('admin.carrier.user_drivers.index', $this->carrier);
    }

    /**
     * Get the name of an endorsement from its code
     */
    private function getEndorsementName($code)
    {
        $endorsements = [
            'H' => 'Hazardous Materials',
            'N' => 'Tank Vehicle',
            'P' => 'Passenger',
            'T' => 'Double/Triple Trailers',
            'X' => 'Combination of tank vehicle and hazardous materials',
            'S' => 'School Bus'
        ];

        return $endorsements[$code] ?? 'Unknown Endorsement';
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.admin.driver.driver-registration-wizard', [
            'carrier' => $this->carrier,
            'userDriverDetail' => $this->userDriverDetail,
            'stepsStatus' => $this->stepsStatus,
            'completionPercentage' => $this->completionPercentage,
            'usStates' => $this->usStates,
            'driverPositions' => $this->driverPositions,
            'referralSources' => $this->referralSources,
            'mainAddress' => [
                'address_line1' => $this->address_line1,
                'address_line2' => $this->address_line2,
                'city' => $this->city,
                'state' => $this->state,
                'zip_code' => $this->zip_code,
                'from_date' => $this->from_date,
                'to_date' => $this->to_date,
                'lived_three_years' => $this->lived_three_years
            ],
            'totalYears' => $this->total_years,
        ])->layout('layouts.admin');
    }
}
