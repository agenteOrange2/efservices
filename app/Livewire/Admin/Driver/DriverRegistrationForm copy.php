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

class DriverRegistrationForm extends Component
{
    use WithFileUploads;

    // Carrier model
    public Carrier $carrier;
    public $isEditMode = false;
    public $userDriverDetail = null;

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
    public function mount(Carrier $carrier, ?UserDriverDetail $userDriverDetail = null)
    {
        $this->carrier = $carrier;

        if ($userDriverDetail && $userDriverDetail->exists) {
            $this->isEditMode = true;
            $this->userDriverDetail = $userDriverDetail;

            // Solo intentamos cargar datos si realmente tenemos un userDriverDetail
            if ($this->userDriverDetail->user) {
                $this->loadDriverData();
            } else {
                session()->flash('error', 'No se pudo cargar la información del conductor.');
            }

            $this->currentStep = $userDriverDetail->current_step ?: 1;
        } else {
            $this->isEditMode = false;
            $this->userDriverDetail = null;
            $this->currentStep = 1;

            // Inicializar direcciones previas y trabajo
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

            // Inicializar las licencias
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

            // Inicializar escuelas de entrenamiento
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
    }

    protected function loadDriverData()
    {
        // Verificar que userDriverDetail y la relación user existen
        if (!$this->userDriverDetail || !$this->userDriverDetail->user) {
            session()->flash('error', 'No se pudo cargar la información del conductor.');
            return;
        }

        // Cargar datos del usuario
        $this->name = $this->userDriverDetail->user->name ?? '';
        $this->email = $this->userDriverDetail->user->email ?? '';
        $this->middle_name = $this->userDriverDetail->middle_name ?? '';
        $this->last_name = $this->userDriverDetail->last_name ?? '';
        $this->phone = $this->userDriverDetail->phone ?? '';
        $this->date_of_birth = $this->userDriverDetail->date_of_birth ?? null;
        $this->status = $this->userDriverDetail->status ?? 1;
        $this->terms_accepted = $this->userDriverDetail->terms_accepted ?? false;

        // Cargar datos de dirección
        $application = $this->userDriverDetail->application;
        if ($application) {
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

            // Cargar direcciones previas
            $this->previous_addresses = [];
            $previousAddresses = $application->addresses()->where('primary', false)->get();
            foreach ($previousAddresses as $address) {
                $this->previous_addresses[] = [
                    'id' => $address->id,
                    'address_line1' => $address->address_line1 ?? '',
                    'address_line2' => $address->address_line2 ?? '',
                    'city' => $address->city ?? '',
                    'state' => $address->state ?? '',
                    'zip_code' => $address->zip_code ?? '',
                    'from_date' => $address->from_date ? $address->from_date->format('Y-m-d') : null,
                    'to_date' => $address->to_date ? $address->to_date->format('Y-m-d') : null,
                ];
            }

            // Cargar detalles de la aplicación
            if ($application->details) {
                $this->applying_position = $application->details->applying_position ?? '';
                $this->applying_position_other = $application->details->applying_position_other ?? '';
                $this->applying_location = $application->details->applying_location ?? '';
                $this->eligible_to_work = $application->details->eligible_to_work ?? true;
                $this->can_speak_english = $application->details->can_speak_english ?? true;
                $this->has_twic_card = $application->details->has_twic_card ?? false;
                $this->twic_expiration_date = $application->details->twic_expiration_date ?
                    $application->details->twic_expiration_date->format('Y-m-d') : null;
                $this->how_did_hear = $application->details->how_did_hear ?? 'internet';
                $this->how_did_hear_other = $application->details->how_did_hear_other ?? '';
                $this->referral_employee_name = $application->details->referral_employee_name ?? '';
                $this->expected_pay = $application->details->expected_pay ?? '';
                $this->has_work_history = $application->details->has_work_history ?? false;

                // Cargar información adicional
                $this->has_attended_training_school = $application->details->has_attended_training_school ?? false;
            }
        }

        // Cargar historial de trabajo
        $this->work_histories = [];
        foreach ($this->userDriverDetail->workHistories as $history) {
            $this->work_histories[] = [
                'id' => $history->id,
                'previous_company' => $history->previous_company ?? '',
                'start_date' => $history->start_date ? $history->start_date->format('Y-m-d') : null,
                'end_date' => $history->end_date ? $history->end_date->format('Y-m-d') : null,
                'location' => $history->location ?? '',
                'position' => $history->position ?? '',
                'reason_for_leaving' => $history->reason_for_leaving ?? '',
                'reference_contact' => $history->reference_contact ?? '',
            ];
        }

        // Step 4: Cargar licencias
        $this->current_license_number = $this->userDriverDetail->licenses()->where('is_primary', true)->first()?->current_license_number ?? '';

        $this->licenses = [];
        foreach ($this->userDriverDetail->licenses as $license) {
            $this->licenses[] = [
                'id' => $license->id,
                'license_number' => $license->license_number ?? '',
                'state_of_issue' => $license->state_of_issue ?? '',
                'license_class' => $license->license_class ?? '',
                'expiration_date' => $license->expiration_date ? $license->expiration_date->format('Y-m-d') : null,
                'is_cdl' => $license->is_cdl ?? false,
                'is_primary' => $license->is_primary ?? false,
                'endorsements' => $license->endorsements ? $license->endorsements->pluck('code')->toArray() : [],
                'front_preview' => $license->getFirstMediaUrl('license_front') ?: null,
                'back_preview' => $license->getFirstMediaUrl('license_back') ?: null,
                'front_filename' => $license->getFirstMedia('license_front')?->file_name ?? '',
                'back_filename' => $license->getFirstMedia('license_back')?->file_name ?? '',
                'temp_front_token' => '',
                'temp_back_token' => '',
            ];
        }

        // Si no hay licencias, agregamos una vacía
        if (empty($this->licenses)) {
            $this->licenses = [[
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
            ]];
        }

        // Cargar experiencias de conducción
        $this->experiences = [];
        foreach ($this->userDriverDetail->experiences as $exp) {
            $this->experiences[] = [
                'id' => $exp->id,
                'equipment_type' => $exp->equipment_type ?? '',
                'years_experience' => $exp->years_experience ?? '',
                'miles_driven' => $exp->miles_driven ?? '',
                'requires_cdl' => $exp->requires_cdl ?? false,
            ];
        }

        // Si no hay experiencias, agregamos una vacía
        if (empty($this->experiences)) {
            $this->experiences = [[
                'equipment_type' => '',
                'years_experience' => '',
                'miles_driven' => '',
                'requires_cdl' => false,
            ]];
        }

        // Step 5: Cargar información médica
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

            // Si existe una tarjeta médica, almacenamos la URL para mostrarla
            if ($medicalQualification->hasMedia('medical_card')) {
                $this->medical_card_preview_url = $medicalQualification->getFirstMediaUrl('medical_card');
                $this->medical_card_filename = $medicalQualification->getFirstMedia('medical_card')->file_name;
            }
        }

        // Step 6: Cargar escuelas de capacitación
        $this->training_schools = [];
        foreach ($this->userDriverDetail->trainingSchools as $school) {
            $certificates = [];

            if ($school->hasMedia('school_certificates')) {
                foreach ($school->getMedia('school_certificates') as $certificate) {
                    $certificates[] = [
                        'id' => $certificate->id,
                        'filename' => $certificate->file_name,
                        'url' => $certificate->getUrl(),
                        'is_image' => Str::startsWith($certificate->mime_type, 'image/'),
                    ];
                }
            }

            $this->training_schools[] = [
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
                'certificates' => $certificates,
                'temp_certificate_tokens' => []
            ];
        }

        // Si no hay escuelas, agregamos una vacía
        if (empty($this->training_schools) && $this->has_attended_training_school) {
            $this->training_schools = [[
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
                'temp_certificate_tokens' => []
            ]];
        }
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

            // Si estamos creando un nuevo usuario
            if (!$this->isEditMode) {
                // Crear nuevo usuario - código existente
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

                // Resto del código para crear registros según el paso actual...

            } else {
                // Usamos $this->userDriverDetail ya que estamos en modo edición
                $userDriverDetail = $this->userDriverDetail;
                $user = $userDriverDetail->user;

                // Actualizar datos básicos
                if ($this->currentStep >= 1) {
                    $user->update([
                        'name' => $this->name,
                        'email' => $this->email
                    ]);

                    if (!empty($this->password)) {
                        $user->update(['password' => Hash::make($this->password)]);
                    }

                    $userDriverDetail->update([
                        'middle_name' => $this->middle_name,
                        'last_name' => $this->last_name,
                        'phone' => $this->phone,
                        'date_of_birth' => $this->date_of_birth,
                        'status' => $this->status,
                        'terms_accepted' => $this->terms_accepted
                    ]);
                }

                // Actualizar o crear aplicación si no existe
                $application = $userDriverDetail->application;
                if (!$application) {
                    $application = DriverApplication::create([
                        'user_id' => $user->id,
                        'status' => 'draft'
                    ]);
                }

                // Siempre actualizar licencias, experiencias, etc. independientemente del paso actual
                // Esto asegura que todo se guarde al salir

                // Actualizar licencias y experiencias
                if (!empty($this->current_license_number) && !empty($this->licenses)) {
                    Log::info('Guardando licencias en saveAndExit', ['count' => count($this->licenses)]);

                    foreach ($this->licenses as $index => $licenseData) {
                        if (empty($licenseData['license_number'])) continue;

                        $licenseId = isset($licenseData['id']) ? $licenseData['id'] : null;
                        $license = null;

                        if ($licenseId) {
                            $license = $userDriverDetail->licenses()->find($licenseId);
                        }

                        $licenseAttributes = [
                            'current_license_number' => $this->current_license_number,
                            'license_number' => $licenseData['license_number'],
                            'state_of_issue' => $licenseData['state_of_issue'] ?? '',
                            'license_class' => $licenseData['license_class'] ?? '',
                            'expiration_date' => $licenseData['expiration_date'] ?? now(),
                            'is_cdl' => isset($licenseData['is_cdl']),
                            'is_primary' => $index === 0,
                            'status' => 'active'
                        ];

                        if (!$license) {
                            $license = $userDriverDetail->licenses()->create($licenseAttributes);
                            Log::info('Nueva licencia creada', ['license_id' => $license->id]);
                        } else {
                            $license->update($licenseAttributes);
                            Log::info('Licencia actualizada', ['license_id' => $license->id]);
                        }

                        // Procesar endosos
                        if (isset($licenseData['is_cdl']) && isset($licenseData['endorsements'])) {
                            $license->endorsements()->detach();

                            foreach ($licenseData['endorsements'] as $code) {
                                $endorsement = \App\Models\Admin\Driver\LicenseEndorsement::firstOrCreate(
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
                            $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                            $tempPath = $tempUploadService->moveToPermanent($licenseData['temp_front_token']);
                            if ($tempPath && file_exists($tempPath)) {
                                $license->clearMediaCollection('license_front');
                                $license->addMedia($tempPath)
                                    ->toMediaCollection('license_front');
                            }
                        }

                        if (!empty($licenseData['temp_back_token'])) {
                            $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                            $tempPath = $tempUploadService->moveToPermanent($licenseData['temp_back_token']);
                            if ($tempPath && file_exists($tempPath)) {
                                $license->clearMediaCollection('license_back');
                                $license->addMedia($tempPath)
                                    ->toMediaCollection('license_back');
                            }
                        }
                    }
                }

                // Actualizar experiencias
                if (!empty($this->experiences)) {
                    Log::info('Guardando experiencias en saveAndExit', ['count' => count($this->experiences)]);

                    foreach ($this->experiences as $expData) {
                        if (empty($expData['equipment_type'])) continue;

                        $expId = isset($expData['id']) ? $expData['id'] : null;
                        $experience = null;

                        if ($expId) {
                            $experience = $userDriverDetail->experiences()->find($expId);
                        }

                        $expAttributes = [
                            'equipment_type' => $expData['equipment_type'],
                            'years_experience' => $expData['years_experience'] ?? 0,
                            'miles_driven' => $expData['miles_driven'] ?? 0,
                            'requires_cdl' => isset($expData['requires_cdl'])
                        ];

                        if (!$experience) {
                            $experience = $userDriverDetail->experiences()->create($expAttributes);
                            Log::info('Nueva experiencia creada', ['experience_id' => $experience->id]);
                        } else {
                            $experience->update($expAttributes);
                            Log::info('Experiencia actualizada', ['experience_id' => $experience->id]);
                        }
                    }
                }

                // Actualizar información médica
                if (!empty($this->social_security_number) || !empty($this->medical_examiner_name)) {
                    Log::info('Guardando información médica en saveAndExit');

                    $medicalAttributes = [
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
                    ];

                    $medical = $userDriverDetail->medicalQualification()->updateOrCreate([], $medicalAttributes);
                    Log::info('Información médica guardada', ['medical_id' => $medical->id]);

                    // Procesar archivo médico
                    if (!empty($this->temp_medical_card_token)) {
                        $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                        $tempPath = $tempUploadService->moveToPermanent($this->temp_medical_card_token);
                        if ($tempPath && file_exists($tempPath)) {
                            $medical->clearMediaCollection('medical_card');
                            $medical->addMedia($tempPath)
                                ->toMediaCollection('medical_card');
                            Log::info('Tarjeta médica guardada con token temporal');
                        }
                    }
                }

                // Actualizar formación
                if ($application && $application->details) {
                    Log::info('Actualizando detalles de formación en saveAndExit');

                    $application->details->update([
                        'has_attended_training_school' => $this->has_attended_training_school
                    ]);

                    if ($this->has_attended_training_school && !empty($this->training_schools)) {
                        foreach ($this->training_schools as $schoolData) {
                            if (empty($schoolData['school_name'])) continue;

                            $schoolId = isset($schoolData['id']) ? $schoolData['id'] : null;
                            $school = null;

                            if ($schoolId) {
                                $school = $userDriverDetail->trainingSchools()->find($schoolId);
                            }

                            $schoolAttributes = [
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
                            ];

                            if (!$school) {
                                $school = $userDriverDetail->trainingSchools()->create($schoolAttributes);
                                Log::info('Nueva escuela creada', ['school_id' => $school->id]);
                            } else {
                                $school->update($schoolAttributes);
                                Log::info('Escuela actualizada', ['school_id' => $school->id]);
                            }

                            // Procesar certificados
                            if (!empty($schoolData['temp_certificate_tokens'])) {
                                foreach ($schoolData['temp_certificate_tokens'] as $certData) {
                                    if (empty($certData['token'])) continue;

                                    $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                                    $tempPath = $tempUploadService->moveToPermanent($certData['token']);
                                    if ($tempPath && file_exists($tempPath)) {
                                        $school->addMedia($tempPath)
                                            ->toMediaCollection('school_certificates');
                                    }
                                }
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
                'email' => $this->isEditMode
                    ? 'required|email|unique:users,email,' . $this->userDriverDetail->user_id
                    : 'required|email|unique:users,email',
                'password' => $this->isEditMode ? 'nullable|min:8' : 'required|min:8',
                'password_confirmation' => $this->isEditMode ? 'nullable|same:password' : 'required|same:password',
            ]);
        } else {
            // Validación completa para avanzar al siguiente paso
            $this->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => $this->isEditMode
                    ? 'required|email|unique:users,email,' . $this->userDriverDetail->user_id
                    : 'required|email|unique:users,email',
                'phone' => 'required|string|max:15',
                'date_of_birth' => 'required|date',
                'password' => $this->isEditMode ? 'nullable|min:8' : 'required|min:8',
                'password_confirmation' => $this->isEditMode ? 'nullable|same:password' : 'required|same:password',
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

    // Modificar tu método validateStep4
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

    // Validar step 5
    public function validateStep5()
    {

        // Si estamos editando y ya existe una tarjeta médica, no requerimos el token
        $cardRequired = 'required|string';

        if ($this->isEditMode && isset($this->medical_card_preview_url) && !empty($this->medical_card_preview_url)) {
            $cardRequired = 'nullable|string';
        }

        $this->validate([
            'social_security_number' => 'required|string|max:255',
            'medical_examiner_name' => 'required|string|max:255',
            'medical_examiner_registry_number' => 'required|string|max:255',
            'medical_card_expiration_date' => 'required|date',
            'temp_medical_card_token' => $cardRequired,
            'suspension_date' => 'nullable|required_if:is_suspended,true|date',
            'termination_date' => 'nullable|required_if:is_terminated,true|date',
        ]);
    }

    // Validar step 6
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

    // En el método nextStep()
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

        // Si estamos en modo edición, actualizar el paso en la base de datos
        if ($this->isEditMode && $this->userDriverDetail) {
            $this->userDriverDetail->update(['current_step' => $this->currentStep]);
        }
    }

    // Métodos de guardado simples

    private function saveCurrentLicenses()
    {
        if (!$this->userDriverDetail || !$this->isEditMode) return;

        foreach ($this->licenses as $index => $license) {
            if (empty($license['license_number'])) continue;

            $this->userDriverDetail->licenses()->updateOrCreate(
                ['id' => $license['id'] ?? null],
                [
                    'current_license_number' => $this->current_license_number,
                    'license_number' => $license['license_number'],
                    'state_of_issue' => $license['state_of_issue'] ?? '',
                    'license_class' => $license['license_class'] ?? '',
                    'expiration_date' => $license['expiration_date'] ?? now(),
                    'is_cdl' => isset($license['is_cdl']),
                    'is_primary' => $index === 0,
                    'status' => 'active'
                ]
            );
        }

        foreach ($this->experiences as $experience) {
            if (empty($experience['equipment_type'])) continue;

            $this->userDriverDetail->experiences()->updateOrCreate(
                ['id' => $experience['id'] ?? null],
                [
                    'equipment_type' => $experience['equipment_type'],
                    'years_experience' => $experience['years_experience'] ?? 0,
                    'miles_driven' => $experience['miles_driven'] ?? 0,
                    'requires_cdl' => isset($experience['requires_cdl'])
                ]
            );
        }
    }

    private function saveCurrentMedical()
    {
        if (!$this->userDriverDetail || !$this->isEditMode) return;

        $this->userDriverDetail->medicalQualification()->updateOrCreate(
            [],
            [
                'social_security_number' => $this->social_security_number,
                'hire_date' => $this->hire_date,
                'location' => $this->location,
                'is_suspended' => $this->is_suspended ?? false,
                'suspension_date' => $this->suspension_date,
                'is_terminated' => $this->is_terminated ?? false,
                'termination_date' => $this->termination_date,
                'medical_examiner_name' => $this->medical_examiner_name,
                'medical_examiner_registry_number' => $this->medical_examiner_registry_number,
                'medical_card_expiration_date' => $this->medical_card_expiration_date
            ]
        );
    }

    private function saveCurrentTraining()
    {
        if (!$this->userDriverDetail || !$this->isEditMode || !$this->userDriverDetail->application) return;

        if ($this->userDriverDetail->application->details) {
            $this->userDriverDetail->application->details->update([
                'has_attended_training_school' => $this->has_attended_training_school
            ]);
        }

        if ($this->has_attended_training_school) {
            foreach ($this->training_schools as $school) {
                if (empty($school['school_name'])) continue;

                $this->userDriverDetail->trainingSchools()->updateOrCreate(
                    ['id' => $school['id'] ?? null],
                    [
                        'school_name' => $school['school_name'],
                        'city' => $school['city'] ?? '',
                        'state' => $school['state'] ?? '',
                        'phone_number' => $school['phone_number'] ?? '',
                        'date_start' => $school['date_start'] ?? now(),
                        'date_end' => $school['date_end'] ?? now(),
                        'graduated' => isset($school['graduated']),
                        'subject_to_safety_regulations' => isset($school['subject_to_safety_regulations']),
                        'performed_safety_functions' => isset($school['performed_safety_functions']),
                        'training_skills' => $school['training_skills'] ?? []
                    ]
                );
            }
        }
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

    public function submitForm()
    {
        // Validar el paso final
        $this->validateStep6();

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        DB::beginTransaction();
        try {
            // Crear o actualizar Usuario
            if (!$this->isEditMode) {
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
                                $endorsement = \App\Models\Admin\Driver\LicenseEndorsement::firstOrCreate(
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
                            $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                            $tempPath = $tempUploadService->moveToPermanent($licenseData['temp_front_token']);
                            if ($tempPath && file_exists($tempPath)) {
                                $license->addMedia($tempPath)
                                    ->toMediaCollection('license_front');
                            }
                        }

                        if (!empty($licenseData['temp_back_token'])) {
                            $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
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
                        $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
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

                                $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
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
            } else {
                // Actualización de un registro existente
                // Código para actualizar - similar al de saveAndExit
                $user = $this->userDriverDetail->user;

                // Actualizar usuario
                $user->update([
                    'name' => $this->name,
                    'email' => $this->email,
                ]);

                if (!empty($this->password)) {
                    $user->update(['password' => Hash::make($this->password)]);
                }

                // Actualizar UserDriverDetail
                $this->userDriverDetail->update([
                    'middle_name' => $this->middle_name,
                    'last_name' => $this->last_name,
                    'phone' => $this->phone,
                    'date_of_birth' => $this->date_of_birth,
                    'status' => $this->status,
                    'terms_accepted' => $this->terms_accepted,
                    'application_completed' => true
                ]);

                // Procesar el resto de campos como en saveAndExit
                // ...

                // Guardar licencias
                foreach ($this->licenses as $index => $licenseData) {
                    if (empty($licenseData['license_number'])) continue;

                    $licenseId = isset($licenseData['id']) ? $licenseData['id'] : null;
                    $license = null;

                    if ($licenseId) {
                        $license = $this->userDriverDetail->licenses()->find($licenseId);
                    }

                    $licenseAttributes = [
                        'current_license_number' => $this->current_license_number,
                        'license_number' => $licenseData['license_number'],
                        'state_of_issue' => $licenseData['state_of_issue'] ?? '',
                        'license_class' => $licenseData['license_class'] ?? '',
                        'expiration_date' => $licenseData['expiration_date'] ?? now(),
                        'is_cdl' => isset($licenseData['is_cdl']),
                        'is_primary' => $index === 0,
                        'status' => 'active'
                    ];

                    if (!$license) {
                        $license = $this->userDriverDetail->licenses()->create($licenseAttributes);
                    } else {
                        $license->update($licenseAttributes);
                    }

                    // Procesar endosos para CDL
                    if (isset($licenseData['is_cdl']) && isset($licenseData['endorsements'])) {
                        // Eliminar endosos existentes
                        $license->endorsements()->detach();

                        // Agregar nuevos endosos
                        foreach ($licenseData['endorsements'] as $code) {
                            $endorsement = \App\Models\Admin\Driver\LicenseEndorsement::firstOrCreate(
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
                        $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                        $tempPath = $tempUploadService->moveToPermanent($licenseData['temp_front_token']);
                        if ($tempPath && file_exists($tempPath)) {
                            $license->clearMediaCollection('license_front');
                            $license->addMedia($tempPath)
                                ->toMediaCollection('license_front');
                        }
                    }

                    if (!empty($licenseData['temp_back_token'])) {
                        $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                        $tempPath = $tempUploadService->moveToPermanent($licenseData['temp_back_token']);
                        if ($tempPath && file_exists($tempPath)) {
                            $license->clearMediaCollection('license_back');
                            $license->addMedia($tempPath)
                                ->toMediaCollection('license_back');
                        }
                    }
                }

                // Guardar experiencias
                foreach ($this->experiences as $expData) {
                    if (empty($expData['equipment_type'])) continue;

                    $expId = isset($expData['id']) ? $expData['id'] : null;
                    $experience = null;

                    if ($expId) {
                        $experience = $this->userDriverDetail->experiences()->find($expId);
                    }

                    $expAttributes = [
                        'equipment_type' => $expData['equipment_type'],
                        'years_experience' => $expData['years_experience'] ?? 0,
                        'miles_driven' => $expData['miles_driven'] ?? 0,
                        'requires_cdl' => isset($expData['requires_cdl'])
                    ];

                    if (!$experience) {
                        $this->userDriverDetail->experiences()->create($expAttributes);
                    } else {
                        $experience->update($expAttributes);
                    }
                }

                // Guardar información médica
                $medicalAttributes = [
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
                ];

                $medical = $this->userDriverDetail->medicalQualification()->updateOrCreate([], $medicalAttributes);

                // Procesar archivo médico
                if (!empty($this->temp_medical_card_token)) {
                    $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                    $tempPath = $tempUploadService->moveToPermanent($this->temp_medical_card_token);
                    if ($tempPath && file_exists($tempPath)) {
                        $medical->clearMediaCollection('medical_card');
                        $medical->addMedia($tempPath)
                            ->toMediaCollection('medical_card');
                    }
                }

                // Guardar información de formación
                $application = $this->userDriverDetail->application;
                if ($application && $application->details) {
                    $application->details->update([
                        'has_attended_training_school' => $this->has_attended_training_school
                    ]);
                }

                if ($this->has_attended_training_school) {
                    foreach ($this->training_schools as $schoolData) {
                        if (empty($schoolData['school_name'])) continue;

                        $schoolId = isset($schoolData['id']) ? $schoolData['id'] : null;
                        $school = null;

                        if ($schoolId) {
                            $school = $this->userDriverDetail->trainingSchools()->find($schoolId);
                        }

                        $schoolAttributes = [
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
                        ];

                        if (!$school) {
                            $school = $this->userDriverDetail->trainingSchools()->create($schoolAttributes);
                        } else {
                            $school->update($schoolAttributes);
                        }

                        // Procesar certificados
                        if (!empty($schoolData['temp_certificate_tokens'])) {
                            foreach ($schoolData['temp_certificate_tokens'] as $certData) {
                                if (empty($certData['token'])) continue;

                                $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                                $tempPath = $tempUploadService->moveToPermanent($certData['token']);
                                if ($tempPath && file_exists($tempPath)) {
                                    $school->addMedia($tempPath)
                                        ->toMediaCollection('school_certificates');
                                }
                            }
                        }
                    }
                }
            }

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

    // Reset form
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
    }
}
