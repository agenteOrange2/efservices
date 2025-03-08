<?php

namespace App\Livewire\Admin\Driver;

use session;
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
use App\Services\Admin\TempUploadService;
use App\Models\Admin\Driver\MasterCompany;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\LicenseEndorsement;

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
    public $photo_path;
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

    // Step 7: Traffic
    public $has_traffic_convictions = false;
    public $traffic_convictions = [];

    // Step 8: Accident
    public $has_accidents = false;
    public $accidents = [];


    // Para experiencia de conducción
    public $experiences = [];

    // Paso FMCSR (Paso 9)
    public $is_disqualified = false;
    public $disqualified_details;
    public $is_license_suspended = false;
    public $suspension_details;
    public $is_license_denied = false;
    public $denial_details;
    public $has_positive_drug_test = false;
    public $substance_abuse_professional;
    public $sap_phone;
    public $return_duty_agency;
    public $consent_to_release = false;
    public $has_duty_offenses = false;
    public $recent_conviction_date;
    public $offense_details;
    public $consent_driving_record = false;

    // Desempleo (Paso 10)
    public $has_unemployment_periods = false;
    public $unemployment_periods = [];
    public $combinedEmploymentHistory = [];
    public $showUnemploymentForm = false;
    public $editingUnemploymentIndex = null;

    // Historial de empleo
    public $employment_companies = [];
    public $has_completed_employment_history = false;
    public $years_of_history = 0;

    // Para modal de formulario de empresa
    public $showCompanyForm = false;
    public $editing_company_index = null;
    public $company_form = [
        'company_name' => '',
        'address' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        'contact' => '',
        'phone' => '',
        'fax' => '',
        'employed_from' => '',
        'employed_to' => '',
        'positions_held' => '',
        'subject_to_fmcsr' => false,
        'safety_sensitive_function' => false,
        'reason_for_leaving' => '',
        'other_reason_description' => '',
        'explanation' => '',
    ];

    public $showSearchCompanyModal = false;
    public $companySearchTerm = '';
    public $searchResults = [];


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

        // Inicializar infracciones de tráfico
        $this->traffic_convictions = [
            [
                'conviction_date' => '',
                'location' => '',
                'charge' => '',
                'penalty' => '',
            ]
        ];

        // Inicializar accidentes
        $this->accidents = [
            [
                'accident_date' => '',
                'nature_of_accident' => '',
                'had_injuries' => false,
                'number_of_injuries' => 0,
                'had_fatalities' => false,
                'number_of_fatalities' => 0,
                'comments' => '',
            ]
        ];

        // Inicializar períodos de desempleo
        $this->unemployment_periods = [
            [
                'start_date' => '',
                'end_date' => '',
                'comments' => ''
            ]
        ];

        // Inicializar empresas (ya deberías tener algo similar)
        $this->employment_companies = [];
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
            } elseif ($this->currentStep == 7) {
                $this->validateStep7();
            } elseif ($this->currentStep == 8) {
                $this->validateStep8();
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

            // Guardar infracciones de tráfico si el paso es ≥ 7
            if ($this->currentStep >= 7) {
                // Actualizar el flag en los detalles de la aplicación
                if ($application && $application->details) {
                    $application->details->update([
                        'has_traffic_convictions' => $this->has_traffic_convictions
                    ]);
                }

                // Guardar infracciones de tráfico
                if ($this->has_traffic_convictions && !empty($this->traffic_convictions)) {
                    foreach ($this->traffic_convictions as $convictionData) {
                        if (empty($convictionData['conviction_date'])) continue;

                        $userDriverDetail->trafficConvictions()->create([
                            'conviction_date' => $convictionData['conviction_date'],
                            'location' => $convictionData['location'],
                            'charge' => $convictionData['charge'],
                            'penalty' => $convictionData['penalty'],
                        ]);
                    }
                }
            }

            // Guardar accidentes si el paso es ≥ 8
            if ($this->currentStep >= 8) {
                // Actualizar el flag en los detalles de la aplicación
                if ($application && $application->details) {
                    $application->details->update([
                        'has_accidents' => $this->has_accidents,
                    ]);
                }

                // Guardar accidentes
                if ($this->has_accidents && !empty($this->accidents)) {
                    foreach ($this->accidents as $accidentData) {
                        if (empty($accidentData['accident_date'])) continue;

                        $userDriverDetail->accidents()->create([
                            'accident_date' => $accidentData['accident_date'],
                            'nature_of_accident' => $accidentData['nature_of_accident'],
                            'had_injuries' => $accidentData['had_injuries'] ?? false,
                            'number_of_injuries' => $accidentData['had_injuries'] ? ($accidentData['number_of_injuries'] ?? 0) : 0,
                            'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                            'number_of_fatalities' => $accidentData['had_fatalities'] ? ($accidentData['number_of_fatalities'] ?? 0) : 0,
                            'comments' => $accidentData['comments'] ?? null,
                        ]);
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

    // Validate Step 7
    public function validateStep7()
    {
        if ($this->has_traffic_convictions) {
            foreach ($this->traffic_convictions as $index => $conviction) {
                $this->validate([
                    "traffic_convictions.{$index}.conviction_date" => 'required|date',
                    "traffic_convictions.{$index}.location" => 'required|string|max:255',
                    "traffic_convictions.{$index}.charge" => 'required|string|max:255',
                    "traffic_convictions.{$index}.penalty" => 'required|string|max:255',
                ]);
            }
        }
    }

    // Validate Step 8
    public function validateStep8()
    {
        if ($this->has_accidents) {
            foreach ($this->accidents as $index => $accident) {
                $this->validate([
                    "accidents.{$index}.accident_date" => 'required|date',
                    "accidents.{$index}.nature_of_accident" => 'required|string|max:255',
                    "accidents.{$index}.number_of_injuries" => 'required_if:accidents.' . $index . '.had_injuries,true|nullable|integer|min:0',
                    "accidents.{$index}.number_of_fatalities" => 'required_if:accidents.' . $index . '.had_fatalities,true|nullable|integer|min:0',
                ]);
            }
        }
    }

    // Añadir estos métodos de validación siguiendo tu convención
    public function validateStep9()
    {
        $this->validate([
            'is_disqualified' => 'sometimes|boolean',
            'disqualified_details' => 'required_if:is_disqualified,true',
            'is_license_suspended' => 'sometimes|boolean',
            'suspension_details' => 'required_if:is_license_suspended,true',
            'is_license_denied' => 'sometimes|boolean',
            'denial_details' => 'required_if:is_license_denied,true',
            'has_positive_drug_test' => 'sometimes|boolean',
            'substance_abuse_professional' => 'required_if:has_positive_drug_test,true',
            'sap_phone' => 'required_if:has_positive_drug_test,true',
            'return_duty_agency' => 'required_if:has_positive_drug_test,true',
            'consent_to_release' => 'sometimes|boolean',
            'has_duty_offenses' => 'sometimes|boolean',
            'recent_conviction_date' => 'required_if:has_duty_offenses,true|date|nullable',
            'offense_details' => 'required_if:has_duty_offenses,true',
            'consent_driving_record' => 'required|accepted',
        ]);
    }

    public function validateStep10()
    {
        $this->validate([
            'has_unemployment_periods' => 'sometimes|boolean',
            'has_completed_employment_history' => 'accepted',
        ]);

        // Validar períodos de desempleo si existen
        if ($this->has_unemployment_periods && count($this->unemployment_periods) > 0) {
            foreach ($this->unemployment_periods as $index => $period) {
                $this->validate([
                    "unemployment_periods.{$index}.start_date" => 'required|date',
                    "unemployment_periods.{$index}.end_date" => 'required|date|after_or_equal:unemployment_periods.' . $index . '.start_date',
                ]);
            }
        }

        // Validar que haya un mínimo de 10 años de historial
        if ($this->years_of_history < 10) {
            $this->addError('employment_history', 'You must have at least 10 years of employment history.');
            return false;
        }

        return true;
    }


    // Next Step
    public function nextStep()
    {

        Log::info('Intentando avanzar del paso ' . $this->currentStep);
        $isValid = true;

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
        } elseif ($this->currentStep == 7) {
            $this->validateStep7();
        } elseif ($this->currentStep == 8) {
            $this->validateStep8();
        } elseif ($this->currentStep == 9) {
            try {
                $this->validateStep9();
                Log::info('Validación del paso 9 exitosa');
            } catch (\Exception $e) {
                Log::error('Error en validación del paso 9: ' . $e->getMessage());
                $this->addError('validation_error', 'Error en validación: ' . $e->getMessage());
                return;
            }
        } elseif ($this->currentStep == 10) {
            $isValid = $this->validateStep10();
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            Log::info('Errores de validación: ' . json_encode($this->getErrorBag()->toArray()));
            return;
        }

        // Avanzar al siguiente paso
        $this->currentStep++;
        Log::info('Avanzado al paso ' . $this->currentStep);
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

    // Añadir otra infracción de tráfico
    public function addTrafficConviction()
    {
        $this->traffic_convictions[] = [
            'conviction_date' => '',
            'location' => '',
            'charge' => '',
            'penalty' => '',
        ];
    }

    // Eliminar una infracción de tráfico
    public function removeTrafficConviction($index)
    {
        if (count($this->traffic_convictions) > 1) {
            unset($this->traffic_convictions[$index]);
            $this->traffic_convictions = array_values($this->traffic_convictions);
        }
    }
    // Añadir otro accidente
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

    // Eliminar un accidente
    public function removeAccident($index)
    {
        if (count($this->accidents) > 1) {
            unset($this->accidents[$index]);
            $this->accidents = array_values($this->accidents);
        }
    }

    // Métodos para desempleo
    public function addUnemploymentPeriod()
    {
        $this->unemployment_periods[] = [
            'start_date' => '',
            'end_date' => '',
            'comments' => '',
        ];

        // Recalcular la historia
        $this->calculateYearsOfHistory();
    }

    public function editUnemploymentPeriod($index)
    {
        if (isset($this->unemployment_periods[$index])) {
            // Si quieres abrir un modal para editar el período de desempleo
            // puedes implementarlo similar al de las empresas

            // Por ahora, puedes enfocarte directamente en ese índice
            $this->dispatchBrowserEvent('focus-field', [
                'field' => "unemployment_periods.{$index}.start_date"
            ]);
        }
    }

    public function removeUnemploymentPeriod($index)
    {
        if (count($this->unemployment_periods) > 1) {
            unset($this->unemployment_periods[$index]);
            $this->unemployment_periods = array_values($this->unemployment_periods);
            $this->calculateYearsOfHistory();
        }
        // Recalcular la historia
        $this->calculateYearsOfHistory();
    }

    // Métodos para empresas
    public function addEmploymentCompany()
    {
        $this->resetCompanyForm();
        $this->showCompanyForm = true;
        $this->editing_company_index = null;
    }

    public function editEmploymentCompany($index)
    {
        $this->editing_company_index = $index;
        $this->company_form = $this->employment_companies[$index];
        $this->showCompanyForm = true;
    }

    public function closeCompanyForm()
    {
        $this->showCompanyForm = false;
        $this->resetCompanyForm();
    }

    public function resetCompanyForm()
    {
        $this->company_form = [
            'company_name' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'contact' => '',
            'phone' => '',
            'fax' => '',
            'employed_from' => '',
            'employed_to' => '',
            'positions_held' => '',
            'subject_to_fmcsr' => false,
            'safety_sensitive_function' => false,
            'reason_for_leaving' => '',
            'other_reason_description' => '',
            'explanation' => '',
        ];
        $this->editing_company_index = null;
    }

    public function saveCompany()
    {

        Log::info('Guardando empresa: Inicio', [
            'tiene_master_company_id' => isset($this->company_form['master_company_id']),
            'master_company_id' => $this->company_form['master_company_id'] ?? 'NO ASIGNADO',
            'company_name' => $this->company_form['company_name'] ?? 'Sin nombre'
        ]);

        // Validate the form
        $this->validate([
            'company_form.employed_from' => 'required|date',
            'company_form.employed_to' => 'required|date|after_or_equal:company_form.employed_from',
            'company_form.positions_held' => 'required|string|max:255',
            'company_form.reason_for_leaving' => 'required|string|max:255',
            'company_form.other_reason_description' => 'required_if:company_form.reason_for_leaving,other|max:255',
        ]);

        // Verificar si tenemos master_company_id
        if (empty($this->company_form['master_company_id'])) {
            // Crear nueva empresa maestra
            $masterCompany = MasterCompany::create([
                'company_name' => $this->company_form['company_name'],
                'address' => $this->company_form['address'] ?? null,
                'city' => $this->company_form['city'] ?? null,
                'state' => $this->company_form['state'] ?? null,
                'zip' => $this->company_form['zip'] ?? null,
                'contact' => $this->company_form['contact'] ?? null,
                'phone' => $this->company_form['phone'] ?? null,
                'fax' => $this->company_form['fax'] ?? null,
                'subject_to_fmcsr' => $this->company_form['subject_to_fmcsr'] ?? false,
                'safety_sensitive_function' => $this->company_form['safety_sensitive_function'] ?? false,
            ]);

            // Importante: actualizar el formulario con el nuevo master_company_id
            $this->company_form['master_company_id'] = $masterCompany->id;

            Log::info('Nueva empresa maestra creada:', [
                'master_company_id' => $masterCompany->id,
                'company_form_updated' => isset($this->company_form['master_company_id'])
            ]);
        }

        // Agregar o actualizar la lista de empresas
        if ($this->editing_company_index !== null) {
            // Actualizar entrada existente
            $this->employment_companies[$this->editing_company_index] = array_merge(
                $this->company_form,
                ['status' => 'ACTIVE']
            );
        } else {
            // Agregar nueva entrada
            $this->employment_companies[] = array_merge(
                $this->company_form,
                ['status' => 'ACTIVE']
            );
        }

        // Después de guardar en employment_companies
        Log::info('Empresa guardada en historial:', [
            'editing_index' => $this->editing_company_index,
            'company_en_lista' => $this->editing_company_index !== null
                ? isset($this->employment_companies[$this->editing_company_index]['master_company_id'])
                : 'Nueva entrada',
            'master_company_id_en_lista' => $this->editing_company_index !== null
                ? ($this->employment_companies[$this->editing_company_index]['master_company_id'] ?? 'FALTA')
                : 'N/A'
        ]);

        // Cerrar formulario y calcular historial
        $this->showCompanyForm = false;
        $this->resetCompanyForm();
        $this->calculateYearsOfHistory();
    }

    public function calculateYearsOfHistory()
    {
        $totalYears = 0;
        $combinedHistory = [];

        // Procesar los períodos de empleo
        foreach ($this->employment_companies as $index => $company) {
            if (!empty($company['employed_from']) && !empty($company['employed_to'])) {
                $from = \Carbon\Carbon::parse($company['employed_from']);
                $to = \Carbon\Carbon::parse($company['employed_to']);
                $years = $from->diffInDays($to) / 365.25;
                $totalYears += $years;

                $combinedHistory[] = [
                    'type' => 'employed',
                    'status' => 'EMPLOYED',
                    'note' => $company['company_name'],
                    'from_date' => $company['employed_from'],
                    'to_date' => $company['employed_to'],
                    'index' => $index,
                    'original_index' => $index,
                    'years' => $years
                ];
            }
        }

        // Procesar los períodos de desempleo
        if ($this->has_unemployment_periods) {
            foreach ($this->unemployment_periods as $index => $period) {
                if (!empty($period['start_date']) && !empty($period['end_date'])) {
                    $from = \Carbon\Carbon::parse($period['start_date']);
                    $to = \Carbon\Carbon::parse($period['end_date']);
                    $years = $from->diffInDays($to) / 365.25;
                    $totalYears += $years;

                    $combinedHistory[] = [
                        'type' => 'unemployed',
                        'status' => 'UNEMPLOYED',
                        'note' => $period['comments'] ?? 'Unemployment Period',
                        'from_date' => $period['start_date'],
                        'to_date' => $period['end_date'],
                        'index' => $index,
                        'original_index' => $index,
                        'years' => $years
                    ];
                }
            }
        }

        // Ordenar por fecha, más reciente primero
        usort($combinedHistory, function ($a, $b) {
            return strtotime($b['to_date']) - strtotime($a['to_date']);
        });

        // Guardar la historia combinada para la vista
        $this->combinedEmploymentHistory = $combinedHistory;

        // Actualizar los años totales
        $this->years_of_history = round($totalYears, 1);

        return $this->years_of_history;
    }

    public function openSearchCompanyModal()
    {
        $this->showSearchCompanyModal = true;
        $this->searchCompanies();
    }

    /**
     * Cerrar el modal de búsqueda de empresas
     */
    public function closeSearchCompanyModal()
    {
        $this->showSearchCompanyModal = false;
        $this->companySearchTerm = '';
        $this->searchResults = [];
    }

    /**
     * Buscar empresas según el término de búsqueda
     */
    public function searchCompanies()
    {
        // Si el término de búsqueda está vacío, mostrar las empresas más recientes
        if (empty($this->companySearchTerm)) {
            $this->searchResults = \App\Models\Admin\Driver\MasterCompany::orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->toArray();
            return;
        }

        // Buscar empresas que coincidan con el término de búsqueda
        $this->searchResults = \App\Models\Admin\Driver\MasterCompany::where('company_name', 'like', '%' . $this->companySearchTerm . '%')
            ->orWhere('city', 'like', '%' . $this->companySearchTerm . '%')
            ->orWhere('state', 'like', '%' . $this->companySearchTerm . '%')
            ->take(20)
            ->get()
            ->toArray();
    }

    /**
     * Seleccionar una empresa existente
     */
    public function selectCompany($companyId)
    {
        // Buscar la empresa maestra
        $masterCompany = MasterCompany::find($companyId);
        Log::info('Seleccionando empresa:', [
            'company_id' => $companyId,
            'company_found' => $masterCompany ? true : false,
            'company_name' => $masterCompany ? $masterCompany->company_name : 'N/A'
        ]);

        if ($masterCompany) {
            // Llenar el formulario con los datos de la empresa
            $this->company_form = [
                'master_company_id' => $masterCompany->id, // Este es el campo clave
                'company_name' => $masterCompany->company_name,
                'address' => $masterCompany->address,
                'city' => $masterCompany->city,
                'state' => $masterCompany->state,
                'zip' => $masterCompany->zip,
                'contact' => $masterCompany->contact,
                'phone' => $masterCompany->phone,
                'fax' => $masterCompany->fax,
                'subject_to_fmcsr' => $masterCompany->subject_to_fmcsr,
                'safety_sensitive_function' => $masterCompany->safety_sensitive_function,
                // Campos específicos de empleo que el usuario debe llenar
                'employed_from' => null,
                'employed_to' => null,
                'positions_held' => '',
                'reason_for_leaving' => '',
                'other_reason_description' => '',
                'explanation' => '',
            ];

            // Cerrar el modal de búsqueda y mostrar el formulario
            $this->closeSearchCompanyModal();
            $this->showCompanyForm = true;

            Log::info('Formulario actualizado con empresa seleccionada:', [
                'master_company_id' => $this->company_form['master_company_id'] ?? 'NO ASIGNADO',
                'company_name' => $this->company_form['company_name']
            ]);
        }
    }

    public function updatedCompanySearchTerm()
    {
        $this->searchCompanies();
    }

    /**
     * Guardar empresas de historial laboral durante la creación
     */
    private function saveEmploymentCompanies()
    {
        Log::info('Iniciando saveEmploymentCompanies', [
            'cantidad_empresas' => count($this->employment_companies)
        ]);

        if (empty($this->employment_companies)) {
            Log::info('No hay empresas para guardar');
            return;
        }


        foreach ($this->employment_companies as $index => $companyData) {

            Log::info("Procesando empresa #{$index}", [
                'tiene_master_company_id' => isset($companyData['master_company_id']),
                'master_company_id' => $companyData['master_company_id'] ?? 'NO ASIGNADO',
                'company_name' => $companyData['company_name'] ?? 'Sin nombre',
                'employed_from' => $companyData['employed_from'] ?? 'Sin fecha inicio',
                'employed_to' => $companyData['employed_to'] ?? 'Sin fecha fin'
            ]);

            // Validar datos mínimos
            if (empty($companyData['employed_from']) || empty($companyData['employed_to'])) {
                continue;
            }

            // Verificar si ya existe un master_company_id
            $masterCompanyId = $companyData['master_company_id'] ?? null;

            // Si no existe, debemos crear uno 
            if (!$masterCompanyId) {
                // Validar que tenemos el nombre de la empresa
                if (empty($companyData['company_name'])) {
                    continue;
                }

                // Crear una nueva empresa maestra
                $masterCompany = MasterCompany::create([
                    'company_name' => $companyData['company_name'],
                    'address' => $companyData['address'] ?? null,
                    'city' => $companyData['city'] ?? null,
                    'state' => $companyData['state'] ?? null,
                    'zip' => $companyData['zip'] ?? null,
                    'contact' => $companyData['contact'] ?? null,
                    'phone' => $companyData['phone'] ?? null,
                    'fax' => $companyData['fax'] ?? null,
                    'subject_to_fmcsr' => $companyData['subject_to_fmcsr'] ?? false,
                    'safety_sensitive_function' => $companyData['safety_sensitive_function'] ?? false
                ]);

                $masterCompanyId = $masterCompany->id;

                Log::info('Creada nueva empresa maestra en saveEmploymentCompanies', [
                    'new_master_company_id' => $masterCompany->id
                ]);
            }

            // Crear el registro de empleo vinculado a esta empresa maestra
            $employmentRecord = $this->userDriverDetail->employmentCompanies()->create([
                'master_company_id' => $masterCompanyId, // IMPORTANTE: Incluir master_company_id
                'employed_from' => $companyData['employed_from'],
                'employed_to' => $companyData['employed_to'],
                'positions_held' => $companyData['positions_held'] ?? null,
                'reason_for_leaving' => $companyData['reason_for_leaving'] ?? null,
                'other_reason_description' => $companyData['reason_for_leaving'] === 'other' ?
                    $companyData['other_reason_description'] : null,
                'explanation' => $companyData['explanation'] ?? null
            ]);

            Log::info('Registro de empleo creado exitosamente', [
                'employment_id' => $employmentRecord->id,
                'master_company_id_usado' => $masterCompanyId
            ]);
        }
    }

    /**
     * Guardar períodos de desempleo
     */
    private function saveUnemploymentPeriods()
    {
        if (empty($this->unemployment_periods)) {
            return;
        }

        foreach ($this->unemployment_periods as $periodData) {
            // Validar datos mínimos
            if (empty($periodData['start_date']) || empty($periodData['end_date'])) {
                continue;
            }

            // Crear período de desempleo
            $this->userDriverDetail->unemploymentPeriods()->create([
                'start_date' => $periodData['start_date'],
                'end_date' => $periodData['end_date'],
                'comments' => $periodData['comments'] ?? null
            ]);
        }
    }

    /**
     * Submit form para completar el registro
     */
    public function submitForm()
    {
        // Validar el paso final
        $this->validateStep10();

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
                try {
                    $path = $this->photo->getRealPath();
                    if (file_exists($path)) {
                        $fileName = strtolower(str_replace(' ', '_', $this->name)) . '.webp';
                        $userDriverDetail->addMedia($path)
                            ->usingFileName($fileName)
                            ->toMediaCollection('profile_photo_driver');
                    } else {
                        // Si el archivo no existe, continuamos sin la foto
                        Log::warning('La foto de perfil ha expirado, continuando sin foto');
                    }
                } catch (\Exception $e) {
                    // Si hay algún error con la foto, lo registramos pero seguimos sin la foto
                    Log::warning('Error al procesar la foto de perfil: ' . $e->getMessage());
                }
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

            // Actualizar detalles de aplicación para traffic y accidents
            // $application->details()->update([
            //     'has_traffic_convictions' => $this->has_traffic_convictions,
            //     'has_accidents' => $this->has_accidents
            // ]);

            // Guardar infracciones de tráfico
            if ($this->has_traffic_convictions && !empty($this->traffic_convictions)) {
                foreach ($this->traffic_convictions as $convictionData) {
                    if (empty($convictionData['conviction_date'])) continue;

                    $userDriverDetail->trafficConvictions()->create([
                        'conviction_date' => $convictionData['conviction_date'],
                        'location' => $convictionData['location'],
                        'charge' => $convictionData['charge'],
                        'penalty' => $convictionData['penalty'],
                    ]);
                }
            }

            // Guardar accidentes
            if ($this->has_accidents && !empty($this->accidents)) {
                foreach ($this->accidents as $accidentData) {
                    if (empty($accidentData['accident_date'])) continue;

                    $userDriverDetail->accidents()->create([
                        'accident_date' => $accidentData['accident_date'],
                        'nature_of_accident' => $accidentData['nature_of_accident'],
                        'had_injuries' => $accidentData['had_injuries'] ?? false,
                        'number_of_injuries' => $accidentData['had_injuries'] ? ($accidentData['number_of_injuries'] ?? 0) : 0,
                        'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                        'number_of_fatalities' => $accidentData['had_fatalities'] ? ($accidentData['number_of_fatalities'] ?? 0) : 0,
                        'comments' => $accidentData['comments'] ?? null,
                    ]);
                }
            }

            // Creación de FMCSR
            if ($userDriverDetail) {
                $userDriverDetail->fmcsrData()->create([
                    'is_disqualified' => $this->is_disqualified,
                    'disqualified_details' => $this->is_disqualified ? $this->disqualified_details : null,
                    'is_license_suspended' => $this->is_license_suspended,
                    'suspension_details' => $this->is_license_suspended ? $this->suspension_details : null,
                    'is_license_denied' => $this->is_license_denied,
                    'denial_details' => $this->is_license_denied ? $this->denial_details : null,
                    'has_positive_drug_test' => $this->has_positive_drug_test,
                    'substance_abuse_professional' => $this->has_positive_drug_test ? $this->substance_abuse_professional : null,
                    'sap_phone' => $this->has_positive_drug_test ? $this->sap_phone : null,
                    'return_duty_agency' => $this->has_positive_drug_test ? $this->return_duty_agency : null,
                    'consent_to_release' => $this->has_positive_drug_test ? $this->consent_to_release : false,
                    'has_duty_offenses' => $this->has_duty_offenses,
                    'recent_conviction_date' => $this->has_duty_offenses ? $this->recent_conviction_date : null,
                    'offense_details' => $this->has_duty_offenses ? $this->offense_details : null,
                    'consent_driving_record' => $this->consent_driving_record
                ]);

                // Guardar infracciones de tráfico
                if ($this->has_traffic_convictions && !empty($this->traffic_convictions)) {
                    foreach ($this->traffic_convictions as $convictionData) {
                        if (empty($convictionData['conviction_date'])) continue;
                        $userDriverDetail->trafficConvictions()->create([
                            'conviction_date' => $convictionData['conviction_date'],
                            'location' => $convictionData['location'],
                            'charge' => $convictionData['charge'],
                            'penalty' => $convictionData['penalty'],
                        ]);
                    }
                }

                // Guardar accidentes
                if ($this->has_accidents && !empty($this->accidents)) {
                    foreach ($this->accidents as $accidentData) {
                        if (empty($accidentData['accident_date'])) continue;
                        $userDriverDetail->accidents()->create([
                            'accident_date' => $accidentData['accident_date'],
                            'nature_of_accident' => $accidentData['nature_of_accident'],
                            'had_injuries' => $accidentData['had_injuries'] ?? false,
                            'number_of_injuries' => $accidentData['had_injuries'] ?
                                ($accidentData['number_of_injuries'] ?? 0) : 0,
                            'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                            'number_of_fatalities' => $accidentData['had_fatalities'] ?
                                ($accidentData['number_of_fatalities'] ?? 0) : 0,
                            'comments' => $accidentData['comments'] ?? null,
                        ]);
                    }
                }

                // Guardar períodos de desempleo
                if ($this->has_unemployment_periods && !empty($this->unemployment_periods)) {
                    foreach ($this->unemployment_periods as $periodData) {
                        if (empty($periodData['start_date']) || empty($periodData['end_date'])) {
                            continue;
                        }

                        $userDriverDetail->unemploymentPeriods()->create([
                            'start_date' => $periodData['start_date'],
                            'end_date' => $periodData['end_date'],
                            'comments' => $periodData['comments'] ?? null
                        ]);
                    }
                }

                // Guardar empresas de historial laboral
                if (!empty($this->employment_companies)) {
                    foreach ($this->employment_companies as $companyData) {
                        if (empty($companyData['company_name']) || empty($companyData['employed_from']) || empty($companyData['employed_to'])) {
                            continue;
                        }

                        $userDriverDetail->employmentCompanies()->create([
                            'company_name' => $companyData['company_name'],
                            'address' => $companyData['address'] ?? null,
                            'city' => $companyData['city'] ?? null,
                            'state' => $companyData['state'] ?? null,
                            'zip' => $companyData['zip'] ?? null,
                            'contact' => $companyData['contact'] ?? null,
                            'phone' => $companyData['phone'] ?? null,
                            'fax' => $companyData['fax'] ?? null,
                            'employed_from' => $companyData['employed_from'],
                            'employed_to' => $companyData['employed_to'],
                            'positions_held' => $companyData['positions_held'] ?? null,
                            'subject_to_fmcsr' => $companyData['subject_to_fmcsr'] ?? false,
                            'safety_sensitive_function' => $companyData['safety_sensitive_function'] ?? false,
                            'reason_for_leaving' => $companyData['reason_for_leaving'] ?? null,
                            'other_reason_description' => $companyData['reason_for_leaving'] === 'other' ? $companyData['other_reason_description'] : null,
                            'explanation' => $companyData['explanation'] ?? null
                        ]);
                    }
                }

                // Guardar empresas de historial laboral
                $this->saveEmploymentCompanies();
            }

            // Guardar períodos de desempleo
            $this->saveUnemploymentPeriods();

            // Guardar empresas de historial laboral
            $this->saveEmploymentCompanies();

            // Guardar períodos de desempleo
            if ($this->has_unemployment_periods) {
                $this->saveUnemploymentPeriods();
            }

            // Antes de guardar las empresas, loguea el estado
            Log::info('Estado antes de guardar empresas', [
                'tiene_empresas' => !empty($this->employment_companies),
                'cantidad_empresas' => count($this->employment_companies)
            ]);

            // Guardar empresas de historial laboral
            $this->saveEmploymentCompanies();



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
