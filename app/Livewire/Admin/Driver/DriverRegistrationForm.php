<?php

namespace App\Livewire\Admin\Driver;

use App\Models\User;
use App\Models\Carrier;
use Livewire\Component;
use App\Helpers\Constants;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\LicenseEndorsement;
use App\Services\Admin\TempUploadService;

class DriverRegistrationForm extends Component
{
    use WithFileUploads;

    // Carrier model
    public Carrier $carrier;

    // Current step
    public $currentStep = 1;
    public $isSaving = false;

    // Step 1: Driver Information
    public $photo;
    public $name;
    public $middle_name;
    public $last_name;
    public $email;
    public $phone;
    public $date_of_birth;
    public $password;
    public $password_confirmation;
    public $status = 1; // Default active
    public $terms_accepted = false;

    // Step 2: Address Information
    public $address_line1;
    public $address_line2;
    public $city;
    public $state;
    public $zip_code;
    public $from_date;
    public $to_date;
    public $lived_three_years = false;
    public $previous_addresses = [];

    // Step 3: Application Details
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
    public $has_work_history = false;
    public $work_histories = [];

    // Step 4: License Information
    public $current_license_number = '';
    public $licenses = [];

    // Step 5: Medical Information
    public $social_security_number = '';
    public $hire_date = null;
    public $location = '';
    public $is_suspended = false;
    public $suspension_date = null;
    public $is_terminated = false;
    public $termination_date = null;
    public $medical_examiner_name = '';
    public $medical_examiner_registry_number = '';
    public $medical_card_expiration_date = '';
    public $medical_card_file;
    public $temp_medical_card_token = '';
    public $medical_card_preview_url;
    public $medical_card_filename;

    // Step 6: Training Schools
    public $has_attended_training_school = false;
    public $training_schools = [];

    // Para experiencia de conducción
    public $experiences = [];

    // Success message
    public $successMessage = '';

    // Constructor to initialize carrier
    public function mount(Carrier $carrier)
    {
        $this->carrier = $carrier;
        $this->currentStep = 1;

        // Inicializar direcciones previas
        $this->previous_addresses = [
            [
                'address_line1' => '',
                'address_line2' => '',
                'city' => '',
                'state' => '',
                'zip_code' => '',
                'from_date' => '',
                'to_date' => ''
            ]
        ];

        // Inicializar historiales de trabajo
        $this->work_histories = [
            [
                'previous_company' => '',
                'start_date' => '',
                'end_date' => '',
                'location' => '',
                'position' => '',
                'reason_for_leaving' => '',
                'reference_contact' => ''
            ]
        ];

        // Inicializar licencias
        $this->licenses = [
            [
                'license_number' => '',
                'state_of_issue' => '',
                'license_class' => '',
                'expiration_date' => '',
                'is_cdl' => false,
                'endorsements' => [],
                'temp_front_token' => '',
                'temp_back_token' => '',
                'front_preview' => '',
                'front_filename' => '',
                'back_preview' => '',
                'back_filename' => ''
            ]
        ];

        // Inicializar experiencias
        $this->experiences = [
            [
                'equipment_type' => '',
                'years_experience' => '',
                'miles_driven' => '',
                'requires_cdl' => false
            ]
        ];

        // Inicializar escuelas de capacitación
        $this->training_schools = [
            [
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
                'temp_certificate_tokens' => []
            ]
        ];
    }

    public function saveAndExit()
    {
        $this->isSaving = true;
        Log::info('Iniciando saveAndExit en paso ' . $this->currentStep);

        // Validar solo el paso actual
        try {
            if ($this->currentStep == 1) {
                $this->validateStep1();
            } elseif ($this->currentStep == 2) {
                $this->validateStep2();
            } elseif ($this->currentStep == 3) {
                $this->validateStep3();
            } elseif ($this->currentStep == 4) {
                $this->validateStep4();
            } elseif ($this->currentStep == 5) {
                $this->validateStep5();
            } elseif ($this->currentStep == 6) {
                $this->validateStep6();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
            $this->isSaving = false;
            Log::error('Error de validación en saveAndExit', ['errors' => $e->errors()]);
            return;
        }

        DB::beginTransaction();
        try {
            Log::info('Transacción DB iniciada en saveAndExit');

            // Crear usuario
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'status' => $this->status
            ]);
            $user->assignRole('driver');

            // Crear UserDriverDetail
            $userDriverDetail = UserDriverDetail::create([
                'user_id' => $user->id,
                'carrier_id' => $this->carrier->id,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'date_of_birth' => $this->date_of_birth,
                'status' => UserDriverDetail::STATUS_PENDING,
                'terms_accepted' => $this->terms_accepted,
                'confirmation_token' => Str::random(60),
                'current_step' => $this->currentStep
            ]);

            // Subir foto de perfil
            if ($this->photo) {
                try {
                    $fileName = strtolower(str_replace(' ', '_', $this->name)) . '.webp';
                    $userDriverDetail->addMedia($this->photo->getRealPath())
                        ->usingFileName($fileName)
                        ->toMediaCollection('profile_photo_driver');
                } catch (\Exception $e) {
                    Log::warning('No se pudo subir la foto de perfil: ' . $e->getMessage());
                }
            }

            // Crear aplicación
            $application = DriverApplication::create([
                'user_id' => $user->id,
                'status' => 'draft'
            ]);

            // Guardar dirección principal si el paso es ≥ 2
            if ($this->currentStep >= 2 && !empty($this->address_line1)) {
                $address = $application->addresses()->create([
                    'primary' => true,
                    'address_line1' => $this->address_line1,
                    'address_line2' => $this->address_line2,
                    'city' => $this->city,
                    'state' => $this->state,
                    'zip_code' => $this->zip_code,
                    'lived_three_years' => $this->lived_three_years,
                    'from_date' => $this->from_date,
                    'to_date' => $this->to_date,
                ]);
                
                // Crear direcciones anteriores si no ha vivido 3 años allí
                if (!$this->lived_three_years && !empty($this->previous_addresses)) {
                    foreach ($this->previous_addresses as $prevAddress) {
                        if (empty($prevAddress['address_line1'])) continue;
                        
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

            // Guardar detalles de aplicación si el paso es ≥ 3
            if ($this->currentStep >= 3) {
                $applicationDetails = $application->details()->create([
                    'applying_position' => $this->applying_position,
                    'applying_position_other' => $this->applying_position === 'other' ? 
                        $this->applying_position_other : null,
                    'applying_location' => $this->applying_location,
                    'eligible_to_work' => $this->eligible_to_work,
                    'can_speak_english' => $this->can_speak_english,
                    'has_twic_card' => $this->has_twic_card,
                    'twic_expiration_date' => $this->has_twic_card ? $this->twic_expiration_date : null,
                    'expected_pay' => $this->expected_pay,
                    'how_did_hear' => $this->how_did_hear,
                    'how_did_hear_other' => $this->how_did_hear === 'other' ? 
                        $this->how_did_hear_other : null,
                    'referral_employee_name' => $this->how_did_hear === 'employee_referral' ? 
                        $this->referral_employee_name : null,
                    'has_work_history' => $this->has_work_history,
                    'has_attended_training_school' => $this->has_attended_training_school
                ]);
                
                // Crear historiales de trabajo
                if ($this->has_work_history && !empty($this->work_histories)) {
                    foreach ($this->work_histories as $history) {
                        if (empty($history['previous_company'])) continue;
                        
                        $userDriverDetail->workHistories()->create([
                            'previous_company' => $history['previous_company'],
                            'start_date' => $history['start_date'],
                            'end_date' => $history['end_date'],
                            'location' => $history['location'],
                            'position' => $history['position'],
                            'reason_for_leaving' => $history['reason_for_leaving'] ?? null,
                            'reference_contact' => $history['reference_contact'] ?? null
                        ]);
                    }
                }
            }

            // Guardar licencias y experiencias si el paso es ≥ 4
            if ($this->currentStep >= 4) {
                if (!empty($this->current_license_number) && !empty($this->licenses)) {
                    foreach ($this->licenses as $index => $licenseData) {
                        if (empty($licenseData['license_number'])) continue;
                        
                        $license = $userDriverDetail->licenses()->create([
                            'current_license_number' => $this->current_license_number,
                            'license_number' => $licenseData['license_number'],
                            'state_of_issue' => $licenseData['state_of_issue'] ?? '',
                            'license_class' => $licenseData['license_class'] ?? '',
                            'expiration_date' => $licenseData['expiration_date'] ?? now(),
                            'is_cdl' => isset($licenseData['is_cdl']),
                            'is_primary' => $index === 0,
                            'status' => 'active'
                        ]);
                        
                        // Procesar endosos
                        if (isset($licenseData['is_cdl']) && isset($licenseData['endorsements'])) {
                            foreach ($licenseData['endorsements'] as $code) {
                                $endorsement = LicenseEndorsement::firstOrCreate(
                                    ['code' => $code],
                                    [
                                        'name' => $this->getEndorsementName($code),
                                        'description' => null,
                                        'is_active' => true
                                    ]
                                );
                                
                                $license->endorsements()->attach($endorsement->id, [
                                    'issued_date' => now(),
                                    'expiration_date' => $licenseData['expiration_date'] ?? now()
                                ]);
                            }
                        }
                        
                        // Procesar imágenes
                        if (!empty($licenseData['temp_front_token'])) {
                            $tempUploadService = app(TempUploadService::class);
                            $tempPath = $tempUploadService->moveToPermanent($licenseData['temp_front_token']);
                            if ($tempPath && file_exists($tempPath)) {
                                $license->addMedia($tempPath)
                                    ->toMediaCollection('license_front');
                            }
                        }
                        
                        if (!empty($licenseData['temp_back_token'])) {
                            $tempUploadService = app(TempUploadService::class);
                            $tempPath = $tempUploadService->moveToPermanent($licenseData['temp_back_token']);
                            if ($tempPath && file_exists($tempPath)) {
                                $license->addMedia($tempPath)
                                    ->toMediaCollection('license_back');
                            }
                        }
                    }
                }
                
                // Guardar experiencias
                if (!empty($this->experiences)) {
                    foreach ($this->experiences as $expData) {
                        if (empty($expData['equipment_type'])) continue;
                        
                        $userDriverDetail->experiences()->create([
                            'equipment_type' => $expData['equipment_type'],
                            'years_experience' => $expData['years_experience'] ?? 0,
                            'miles_driven' => $expData['miles_driven'] ?? 0,
                            'requires_cdl' => isset($expData['requires_cdl'])
                        ]);
                    }
                }
            }

            // Guardar información médica si el paso es ≥ 5
            if ($this->currentStep >= 5 && !empty($this->social_security_number)) {
                $medical = $userDriverDetail->medicalQualification()->create([
                    'social_security_number' => $this->social_security_number,
                    'hire_date' => $this->hire_date,
                    'location' => $this->location,
                    'is_suspended' => $this->is_suspended ?? false,
                    'suspension_date' => $this->is_suspended ? $this->suspension_date : null,
                    'is_terminated' => $this->is_terminated ?? false,
                    'termination_date' => $this->is_terminated ? $this->termination_date : null,
                    'medical_examiner_name' => $this->medical_examiner_name,
                    'medical_examiner_registry_number' => $this->medical_examiner_registry_number,
                    'medical_card_expiration_date' => $this->medical_card_expiration_date
                ]);
                
                // Procesar archivo médico
                if (!empty($this->temp_medical_card_token)) {
                    $tempUploadService = app(TempUploadService::class);
                    $tempPath = $tempUploadService->moveToPermanent($this->temp_medical_card_token);
                    if ($tempPath && file_exists($tempPath)) {
                        $medical->addMedia($tempPath)
                            ->toMediaCollection('medical_card');
                    }
                }
            }

            // Guardar escuelas de capacitación si el paso es = 6
            if ($this->currentStep >= 6 && $this->has_attended_training_school && !empty($this->training_schools)) {
                foreach ($this->training_schools as $schoolData) {
                    if (empty($schoolData['school_name'])) continue;
                    
                    $school = $userDriverDetail->trainingSchools()->create([
                        'school_name' => $schoolData['school_name'],
                        'city' => $schoolData['city'] ?? '',
                        'state' => $schoolData['state'] ?? '',
                        'phone_number' => $schoolData['phone_number'] ?? '',
                        'date_start' => $schoolData['date_start'] ?? now(),
                        'date_end' => $schoolData['date_end'] ?? now(),
                        'graduated' => isset($schoolData['graduated']),
                        'subject_to_safety_regulations' => isset($schoolData['subject_to_safety_regulations']),
                        'performed_safety_functions' => isset($schoolData['performed_safety_functions']),
                        'training_skills' => $schoolData['training_skills'] ?? []
                    ]);
                    
                    // Procesar certificados
                    if (!empty($schoolData['temp_certificate_tokens'])) {
                        foreach ($schoolData['temp_certificate_tokens'] as $certData) {
                            if (empty($certData['token'])) continue;
                            
                            $tempUploadService = app(TempUploadService::class);
                            $tempPath = $tempUploadService->moveToPermanent($certData['token']);
                            if ($tempPath && file_exists($tempPath)) {
                                $school->addMedia($tempPath)
                                    ->toMediaCollection('school_certificates');
                            }
                        }
                    }
                }
            }

            // Si llegó hasta aquí, todo está bien
            DB::commit();
            Log::info('Transacción completada exitosamente en saveAndExit');

            // Establecer un mensaje flash para la página de listado
            session()->flash('success', 'Driver information saved. You can continue editing later.');

            // Redirigir a la lista de conductores
            return redirect()->route('admin.carrier.user_drivers.index', $this->carrier);
        } catch (\Exception $e) {
            DB::rollBack();

            // Registrar el error
            Log::error('Error en saveAndExit', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'current_step' => $this->currentStep
            ]);

            // Establecer mensaje de error
            session()->flash('error', 'Error saving progress: ' . $e->getMessage());
        }

        $this->isSaving = false;
    }

    public function render()
    {
        return view('livewire.admin.driver.driver-registration-form', [
            'usStates' => Constants::usStates(),
            'driverPositions' => Constants::driverPositions(),
            'referralSources' => Constants::referralSources()
        ]);
    }

    // Validate Step 1
    public function validateStep1()
    {
        // Si solo estamos guardando (no avanzando), las validaciones son más permisivas
        if ($this->isSaving) {
            $this->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'password_confirmation' => 'required|same:password',
            ]);
        } else {
            // Validación completa para avanzar al siguiente paso
            $this->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|string|max:15',
                'date_of_birth' => 'required|date',
                'password' => 'required|min:8',
                'password_confirmation' => 'required|same:password',
                'terms_accepted' => 'accepted',
            ]);
        }
    }

    // Validate Step 2
    public function validateStep2()
    {
        $this->validate([
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date',
        ]);

        // Validate previous addresses if lived less than 3 years
        if (!$this->lived_three_years) {
            foreach ($this->previous_addresses as $index => $address) {
                $this->validate([
                    "previous_addresses.{$index}.address_line1" => 'required|string|max:255',
                    "previous_addresses.{$index}.city" => 'required|string|max:255',
                    "previous_addresses.{$index}.state" => 'required|string|max:255',
                    "previous_addresses.{$index}.zip_code" => 'required|string|max:255',
                    "previous_addresses.{$index}.from_date" => 'required|date',
                    "previous_addresses.{$index}.to_date" => 'required|date',
                ]);
            }
        }
    }

    // Validate Step 3
    public function validateStep3()
    {
        $this->validate([
            'applying_position' => 'required|string',
            'applying_position_other' => 'required_if:applying_position,other',
            'applying_location' => 'required|string',
            'eligible_to_work' => 'accepted',
            'twic_expiration_date' => 'nullable|required_if:has_twic_card,true|date',
            'how_did_hear' => 'required|string',
            'how_did_hear_other' => 'required_if:how_did_hear,other',
            'referral_employee_name' => 'required_if:how_did_hear,employee_referral',
        ]);

        // Validate work history if has work history
        if ($this->has_work_history) {
            foreach ($this->work_histories as $index => $history) {
                $this->validate([
                    "work_histories.{$index}.previous_company" => 'required|string|max:255',
                    "work_histories.{$index}.start_date" => 'required|date',
                    "work_histories.{$index}.end_date" => 'required|date',
                    "work_histories.{$index}.location" => 'required|string|max:255',
                    "work_histories.{$index}.position" => 'required|string|max:255',
                ]);
            }
        }
    }

    // Validate Step 4
    public function validateStep4()
    {
        $this->validate([
            'current_license_number' => 'required|string|max:255',
        ]);

        // Validar licencias
        foreach ($this->licenses as $index => $license) {
            $this->validate([
                "licenses.{$index}.license_number" => 'required|string|max:255',
                "licenses.{$index}.state_of_issue" => 'required|string|max:255',
                "licenses.{$index}.license_class" => 'required|string|max:255',
                "licenses.{$index}.expiration_date" => 'required|date',
            ]);
        }

        // Validar experiencias
        foreach ($this->experiences as $index => $experience) {
            $this->validate([
                "experiences.{$index}.equipment_type" => 'required|string|max:255',
                "experiences.{$index}.years_experience" => 'required|integer|min:0',
                "experiences.{$index}.miles_driven" => 'required|integer|min:0',
            ]);
        }
    }

    // Validate Step 5
    public function validateStep5()
    {
        $this->validate([
            'social_security_number' => 'required|string|max:255',
            'medical_examiner_name' => 'required|string|max:255',
            'medical_examiner_registry_number' => 'required|string|max:255',
            'medical_card_expiration_date' => 'required|date',
            'temp_medical_card_token' => 'required|string',
            'suspension_date' => 'nullable|required_if:is_suspended,true|date',
            'termination_date' => 'nullable|required_if:is_terminated,true|date',
        ]);
    }

    // Validate Step 6
    public function validateStep6()
    {
        if ($this->has_attended_training_school) {
            foreach ($this->training_schools as $index => $school) {
                $this->validate([
                    "training_schools.{$index}.school_name" => 'required|string|max:255',
                    "training_schools.{$index}.city" => 'required|string|max:255',
                    "training_schools.{$index}.state" => 'required|string|max:255',
                    "training_schools.{$index}.date_start" => 'required|date',
                    "training_schools.{$index}.date_end" => 'required|date|after_or_equal:training_schools.' . $index . '.date_start',
                ]);
            }
        }
    }

    // Next Step
    public function nextStep()
    {
        // Validar el paso actual
        if ($this->currentStep == 1) {
            $this->validateStep1();
        } elseif ($this->currentStep == 2) {
            $this->validateStep2();
        } elseif ($this->currentStep == 3) {
            $this->validateStep3();
        } elseif ($this->currentStep == 4) {
            $this->validateStep4();
        } elseif ($this->currentStep == 5) {
            $this->validateStep5();
        } elseif ($this->currentStep == 6) {
            $this->validateStep6();
        }

        // Si hay errores de validación, no continuar
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        // Avanzar al siguiente paso
        $this->currentStep++;
    }

    // Go to previous step
    public function prevStep()
    {
        $this->currentStep--;
    }

    // Add a new previous address field
    public function addPreviousAddress()
    {
        $this->previous_addresses[] = [
            'address_line1' => '',
            'address_line2' => '',
            'city' => '',
            'state' => '',
            'zip_code' => '',
            'from_date' => '',
            'to_date' => ''
        ];
    }

    // Remove a previous address field
    public function removePreviousAddress($index)
    {
        unset($this->previous_addresses[$index]);
        $this->previous_addresses = array_values($this->previous_addresses);
    }

    // Add a new work history field
    public function addWorkHistory()
    {
        $this->work_histories[] = [
            'previous_company' => '',
            'start_date' => '',
            'end_date' => '',
            'location' => '',
            'position' => '',
            'reason_for_leaving' => '',
            'reference_contact' => ''
        ];
    }

    // Remove a work history field
    public function removeWorkHistory($index)
    {
        unset($this->work_histories[$index]);
        $this->work_histories = array_values($this->work_histories);
    }

    // Para licencias
    public function addLicense()
    {
        $this->licenses[] = [
            'license_number' => '',
            'state_of_issue' => '',
            'license_class' => '',
            'expiration_date' => '',
            'is_cdl' => false,
            'endorsements' => [],
            'temp_front_token' => '',
            'temp_back_token' => '',
            'front_preview' => '',
            'front_filename' => '',
            'back_preview' => '',
            'back_filename' => ''
        ];
    }

    public function removeLicense($index)
    {
        if (count($this->licenses) > 1) {
            unset($this->licenses[$index]);
            $this->licenses = array_values($this->licenses);
        }
    }

    // Para experiencias
    public function addExperience()
    {
        $this->experiences[] = [
            'equipment_type' => '',
            'years_experience' => '',
            'miles_driven' => '',
            'requires_cdl' => false
        ];
    }

    public function removeExperience($index)
    {
        if (count($this->experiences) > 1) {
            unset($this->experiences[$index]);
            $this->experiences = array_values($this->experiences);
        }
    }

    // Para escuelas de entrenamiento
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
            'temp_certificate_tokens' => []
        ];
    }

    public function removeTrainingSchool($index)
    {
        if (count($this->training_schools) > 1) {
            unset($this->training_schools[$index]);
            $this->training_schools = array_values($this->training_schools);
        }
    }

    // Para gestionar los certificados de training
    public function removeCertificate($schoolIndex, $tokenIndex)
    {
        unset($this->training_schools[$schoolIndex]['temp_certificate_tokens'][$tokenIndex]);
        $this->training_schools[$schoolIndex]['temp_certificate_tokens'] = array_values($this->training_schools[$schoolIndex]['temp_certificate_tokens']);
    }

    // Para togglear habilidades de entrenamiento
    public function toggleTrainingSkill($schoolIndex, $skill)
    {
        if (!isset($this->training_schools[$schoolIndex]['training_skills'])) {
            $this->training_schools[$schoolIndex]['training_skills'] = [];
        }

        $index = array_search($skill, $this->training_schools[$schoolIndex]['training_skills']);
        if ($index !== false) {
            unset($this->training_schools[$schoolIndex]['training_skills'][$index]);
            $this->training_schools[$schoolIndex]['training_skills'] = array_values($this->training_schools[$schoolIndex]['training_skills']);
        } else {
            $this->training_schools[$schoolIndex]['training_skills'][] = $skill;
        }
    }

    /**
     * Add a certificate to a training school
     */
    public function addCertificate($schoolIndex, $token, $filename, $previewUrl = null, $fileType = null)
    {
        if (!isset($this->training_schools[$schoolIndex]['temp_certificate_tokens'])) {
            $this->training_schools[$schoolIndex]['temp_certificate_tokens'] = [];
        }

        $this->training_schools[$schoolIndex]['temp_certificate_tokens'][] = [
            'token' => $token,
            'filename' => $filename,
            'preview_url' => $previewUrl,
            'file_type' => $fileType
        ];
    }

    /**
     * Check if a file is an image
     */
    private function isImageFile($filename)
    {
        if (!$filename) return false;
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Check if a file is a PDF
     */
    private function isPdfFile($filename)
    {
        if (!$filename) return false;
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return $ext === 'pdf';
    }

    /**
     * Método para obtener el nombre de un endorsement a partir de su código
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
     * Submit form para completar el registro
     */
    public function submitForm()
    {
        // Validar el paso final
        $this->validateStep6();

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        DB::beginTransaction();
        try {
            // Crear usuario
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'status' => $this->status
            ]);

            // Asignar rol de conductor
            $user->assignRole('driver');

            // Crear UserDriverDetail
            $userDriverDetail = UserDriverDetail::create([
                'user_id' => $user->id,
                'carrier_id' => $this->carrier->id,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'phone' => $this->phone,
                'date_of_birth' => $this->date_of_birth,
                'status' => $this->status,
                'terms_accepted' => $this->terms_accepted,
                'confirmation_token' => Str::random(60),
                'current_step' => 1 // Volver al primer paso
            ]);

            // Subir foto de perfil si se proporcionó
            if ($this->photo) {
                $fileName = strtolower(str_replace(' ', '_', $this->name)) . '.webp';
                $userDriverDetail->addMedia($this->photo->getRealPath())
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }

            // Crear aplicación
            $application = DriverApplication::create([
                'user_id' => $user->id,
                'status' => 'draft'
            ]);

            // Crear dirección principal
            if (!empty($this->address_line1)) {
                $application->addresses()->create([
                    'primary' => true,
                    'address_line1' => $this->address_line1,
                    'address_line2' => $this->address_line2,
                    'city' => $this->city,
                    'state' => $this->state,
                    'zip_code' => $this->zip_code,
                    'lived_three_years' => $this->lived_three_years,
                    'from_date' => $this->from_date,
                    'to_date' => $this->to_date
                ]);
            }

            // Crear direcciones previas si no ha vivido 3 años en la actual
            if (!$this->lived_three_years && !empty($this->previous_addresses)) {
                foreach ($this->previous_addresses as $prevAddress) {
                    if (empty($prevAddress['address_line1'])) continue;

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

            // Crear detalles de aplicación
            $application->details()->create([
                'applying_position' => $this->applying_position,
                'applying_position_other' => $this->applying_position === 'other' ? $this->applying_position_other : null,
                'applying_location' => $this->applying_location,
                'eligible_to_work' => $this->eligible_to_work,
                'can_speak_english' => $this->can_speak_english,
                'has_twic_card' => $this->has_twic_card,
                'twic_expiration_date' => $this->has_twic_card ? $this->twic_expiration_date : null,
                'how_did_hear' => $this->how_did_hear,
                'how_did_hear_other' => $this->how_did_hear === 'other' ? $this->how_did_hear_other : null,
                'referral_employee_name' => $this->how_did_hear === 'employee_referral' ? $this->referral_employee_name : null,
                'expected_pay' => $this->expected_pay,
                'has_work_history' => $this->has_work_history,
                'has_attended_training_school' => $this->has_attended_training_school
            ]);

            // Crear historiales de trabajo
            if ($this->has_work_history && !empty($this->work_histories)) {
                foreach ($this->work_histories as $history) {
                    if (empty($history['previous_company'])) continue;

                    $userDriverDetail->workHistories()->create([
                        'previous_company' => $history['previous_company'],
                        'start_date' => $history['start_date'],
                        'end_date' => $history['end_date'],
                        'location' => $history['location'],
                        'position' => $history['position'],
                        'reason_for_leaving' => $history['reason_for_leaving'] ?? null,
                        'reference_contact' => $history['reference_contact'] ?? null
                    ]);
                }
            }

            // Crear licencias
            if (!empty($this->current_license_number) && !empty($this->licenses)) {
                foreach ($this->licenses as $index => $licenseData) {
                    if (empty($licenseData['license_number'])) continue;

                    $license = $userDriverDetail->licenses()->create([
                        'current_license_number' => $this->current_license_number,
                        'license_number' => $licenseData['license_number'],
                        'state_of_issue' => $licenseData['state_of_issue'] ?? '',
                        'license_class' => $licenseData['license_class'] ?? '',
                        'expiration_date' => $licenseData['expiration_date'] ?? now(),
                        'is_cdl' => isset($licenseData['is_cdl']),
                        'is_primary' => $index === 0,
                        'status' => 'active'
                    ]);

                    // Procesar endosos si es CDL
                    if (isset($licenseData['is_cdl']) && isset($licenseData['endorsements'])) {
                        foreach ($licenseData['endorsements'] as $code) {
                            $endorsement = LicenseEndorsement::firstOrCreate(
                                ['code' => $code],
                                [
                                    'name' => $this->getEndorsementName($code),
                                    'description' => null,
                                    'is_active' => true
                                ]
                            );

                            $license->endorsements()->attach($endorsement->id, [
                                'issued_date' => now(),
                                'expiration_date' => $licenseData['expiration_date'] ?? now()
                            ]);
                        }
                    }

                    // Procesar imágenes
                    if (!empty($licenseData['temp_front_token'])) {
                        $tempUploadService = app(TempUploadService::class);
                        $tempPath = $tempUploadService->moveToPermanent($licenseData['temp_front_token']);
                        if ($tempPath && file_exists($tempPath)) {
                            $license->addMedia($tempPath)
                                ->toMediaCollection('license_front');
                        }
                    }

                    if (!empty($licenseData['temp_back_token'])) {
                        $tempUploadService = app(TempUploadService::class);
                        $tempPath = $tempUploadService->moveToPermanent($licenseData['temp_back_token']);
                        if ($tempPath && file_exists($tempPath)) {
                            $license->addMedia($tempPath)
                                ->toMediaCollection('license_back');
                        }
                    }
                }
            }

            // Crear experiencias
            if (!empty($this->experiences)) {
                foreach ($this->experiences as $expData) {
                    if (empty($expData['equipment_type'])) continue;

                    $userDriverDetail->experiences()->create([
                        'equipment_type' => $expData['equipment_type'],
                        'years_experience' => $expData['years_experience'] ?? 0,
                        'miles_driven' => $expData['miles_driven'] ?? 0,
                        'requires_cdl' => isset($expData['requires_cdl'])
                    ]);
                }
            }

            // Crear información médica
            if (!empty($this->social_security_number)) {
                $medical = $userDriverDetail->medicalQualification()->create([
                    'social_security_number' => $this->social_security_number,
                    'hire_date' => $this->hire_date,
                    'location' => $this->location,
                    'is_suspended' => $this->is_suspended ?? false,
                    'suspension_date' => $this->is_suspended ? $this->suspension_date : null,
                    'is_terminated' => $this->is_terminated ?? false,
                    'termination_date' => $this->is_terminated ? $this->termination_date : null,
                    'medical_examiner_name' => $this->medical_examiner_name,
                    'medical_examiner_registry_number' => $this->medical_examiner_registry_number,
                    'medical_card_expiration_date' => $this->medical_card_expiration_date
                ]);

                // Procesar archivo médico
                if (!empty($this->temp_medical_card_token)) {
                    $tempUploadService = app(TempUploadService::class);
                    $tempPath = $tempUploadService->moveToPermanent($this->temp_medical_card_token);
                    if ($tempPath && file_exists($tempPath)) {
                        $medical->addMedia($tempPath)
                            ->toMediaCollection('medical_card');
                    }
                }
            }

            // Crear escuelas de formación
            if ($this->has_attended_training_school && !empty($this->training_schools)) {
                foreach ($this->training_schools as $schoolData) {
                    if (empty($schoolData['school_name'])) continue;

                    $school = $userDriverDetail->trainingSchools()->create([
                        'school_name' => $schoolData['school_name'],
                        'city' => $schoolData['city'] ?? '',
                        'state' => $schoolData['state'] ?? '',
                        'phone_number' => $schoolData['phone_number'] ?? '',
                        'date_start' => $schoolData['date_start'] ?? now(),
                        'date_end' => $schoolData['date_end'] ?? now(),
                        'graduated' => isset($schoolData['graduated']),
                        'subject_to_safety_regulations' => isset($schoolData['subject_to_safety_regulations']),
                        'performed_safety_functions' => isset($schoolData['performed_safety_functions']),
                        'training_skills' => $schoolData['training_skills'] ?? []
                    ]);

                    // Procesar certificados
                    if (!empty($schoolData['temp_certificate_tokens'])) {
                        foreach ($schoolData['temp_certificate_tokens'] as $certData) {
                            if (empty($certData['token'])) continue;

                            $tempUploadService = app(TempUploadService::class);
                            $tempPath = $tempUploadService->moveToPermanent($certData['token']);
                            if ($tempPath && file_exists($tempPath)) {
                                $school->addMedia($tempPath)
                                    ->toMediaCollection('school_certificates');
                            }
                        }
                    }
                }
            }

            // Marcar como completado
            $userDriverDetail->update(['application_completed' => true]);

            DB::commit();
            $this->successMessage = 'Driver created successfully!';

            // Redirigir
            return redirect()->route('admin.carrier.user_drivers.index', [
                'carrier' => $this->carrier
            ])->with('success', 'Driver created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating driver: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error creating driver: ' . $e->getMessage());
        }
    }

    /**
     * Reset form - limpiar todos los campos
     */
    private function resetForm()
    {
        $this->photo = null;
        $this->name = '';
        $this->middle_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->phone = '';
        $this->date_of_birth = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->status = 1;
        $this->terms_accepted = false;

        $this->address_line1 = '';
        $this->address_line2 = '';
        $this->city = '';
        $this->state = '';
        $this->zip_code = '';
        $this->from_date = '';
        $this->to_date = '';
        $this->lived_three_years = false;
        $this->previous_addresses = [
            [
                'address_line1' => '',
                'address_line2' => '',
                'city' => '',
                'state' => '',
                'zip_code' => '',
                'from_date' => '',
                'to_date' => ''
            ]
        ];

        $this->applying_position = '';
        $this->applying_position_other = '';
        $this->applying_location = '';
        $this->eligible_to_work = true;
        $this->can_speak_english = true;
        $this->has_twic_card = false;
        $this->twic_expiration_date = '';
        $this->expected_pay = '';
        $this->how_did_hear = 'internet';
        $this->how_did_hear_other = '';
        $this->referral_employee_name = '';
        $this->has_work_history = false;
        $this->work_histories = [
            [
                'previous_company' => '',
                'start_date' => '',
                'end_date' => '',
                'location' => '',
                'position' => '',
                'reason_for_leaving' => '',
                'reference_contact' => ''
            ]
        ];

        $this->currentStep = 1;
        $this->successMessage = '';
    }
}