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
use App\Services\Admin\DriverStepService;
use App\Services\Admin\TempUploadService;
use App\Models\Admin\Driver\MasterCompany;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\LicenseEndorsement;

class DriverEditForm extends Component
{
    use WithFileUploads;

    // Carrier model
    public Carrier $carrier;
    public UserDriverDetail $userDriverDetail;

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
    public $status = 1;
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

    // Step 7: Traffic
    public $has_traffic_convictions = false;
    public $traffic_convictions = [];

    // Step 8: Accident
    public $has_accidents = false;
    public $accidents = [];

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

    // Driver step service
    protected $driverStepService;

    public function boot(DriverStepService $driverStepService)
    {
        $this->driverStepService = $driverStepService;
    }

    public function mount(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        $this->carrier = $carrier;
        $this->userDriverDetail = $userDriverDetail;

        // Verificar que userDriverDetail exista
        if (!$this->userDriverDetail->exists || !$this->userDriverDetail->user) {
            session()->flash('error', 'No se pudo cargar la información del conductor.');
            return;
        }

        // Cargar datos del usuario
        $this->loadDriverData();

        // Establecer el paso actual
        $this->currentStep = $userDriverDetail->current_step ?: 1;
    }

    protected function loadDriverData()
    {
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
                $this->has_work_history = (bool)($application->details->has_work_history ?? false);
                $this->has_attended_training_school = (bool)($application->details->has_attended_training_school ?? false);
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

        // Si no hay historiales laborales pero tiene work_history, agregar uno vacío
        if (empty($this->work_histories) && $this->has_work_history) {
            $this->work_histories = [[
                'previous_company' => '',
                'start_date' => '',
                'end_date' => '',
                'location' => '',
                'position' => '',
                'reason_for_leaving' => '',
                'reference_contact' => ''
            ]];
        }

        // Si hay historial de trabajo pero el flag no está activo, activarlo
        if (!$this->has_work_history && $this->userDriverDetail->workHistories()->count() > 0) {
            $this->has_work_history = true;
        }

        // Si hay escuelas de capacitación pero el flag no está activo, activarlo
        if (!$this->has_attended_training_school && $this->userDriverDetail->trainingSchools()->count() > 0) {
            $this->has_attended_training_school = true;
        }

        // Si hay infracciones pero el flag no está activo, activarlo
        if (!$this->has_traffic_convictions && $this->userDriverDetail->trafficConvictions()->count() > 0) {
            $this->has_traffic_convictions = true;
        }

        // Si hay accidentes pero el flag no está activo, activarlo
        if (!$this->has_accidents && $this->userDriverDetail->accidents()->count() > 0) {
            $this->has_accidents = true;
        }

        // Cargar licencias
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

        // Si no hay licencias, agregar una vacía
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

        // Si no hay experiencias, agregar una vacía
        if (empty($this->experiences)) {
            $this->experiences = [[
                'equipment_type' => '',
                'years_experience' => '',
                'miles_driven' => '',
                'requires_cdl' => false,
            ]];
        }

        // Cargar información médica
        $medicalQualification = $this->userDriverDetail->medicalQualification;
        if ($medicalQualification) {
            $this->social_security_number = $medicalQualification->social_security_number ?? '';
            $this->hire_date = $medicalQualification->hire_date ?
                $medicalQualification->hire_date->format('Y-m-d') : null;
            $this->location = $medicalQualification->location ?? '';
            $this->is_suspended = $medicalQualification->is_suspended ?? false;
            $this->suspension_date = $medicalQualification->suspension_date ?
                $medicalQualification->suspension_date->format('Y-m-d') : null;
            $this->is_terminated = $medicalQualification->is_terminated ?? false;
            $this->termination_date = $medicalQualification->termination_date ?
                $medicalQualification->termination_date->format('Y-m-d') : null;
            $this->medical_examiner_name = $medicalQualification->medical_examiner_name ?? '';
            $this->medical_examiner_registry_number = $medicalQualification->medical_examiner_registry_number ?? '';
            $this->medical_card_expiration_date = $medicalQualification->medical_card_expiration_date ?
                $medicalQualification->medical_card_expiration_date->format('Y-m-d') : null;

            // Si existe una tarjeta médica, almacenamos la URL para mostrarla
            if ($medicalQualification->hasMedia('medical_card')) {
                $this->medical_card_preview_url = $medicalQualification->getFirstMediaUrl('medical_card');
                $this->medical_card_filename = $medicalQualification->getFirstMedia('medical_card')->file_name;
            }
        }

        // Cargar escuelas de capacitación
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

        // Si no hay escuelas pero ha asistido a alguna, agregar una vacía
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

        // Cargar datos de infracciones de tráfico
        if ($this->userDriverDetail->application && $this->userDriverDetail->application->details) {
            $this->has_traffic_convictions = $this->userDriverDetail->application->details->has_traffic_convictions ?? false;
        }

        $this->traffic_convictions = [];
        foreach ($this->userDriverDetail->trafficConvictions as $conviction) {
            $this->traffic_convictions[] = [
                'id' => $conviction->id,
                'conviction_date' => $conviction->conviction_date ? $conviction->conviction_date->format('Y-m-d') : null,
                'location' => $conviction->location,
                'charge' => $conviction->charge,
                'penalty' => $conviction->penalty,
            ];
        }
        // Si hay infracciones en la base de datos pero el flag no está activo, activarlo
        if (!$this->has_traffic_convictions && $this->userDriverDetail->trafficConvictions()->count() > 0) {
            $this->has_traffic_convictions = true;
        }

        // Si no hay infracciones pero tiene has_traffic_convictions, agregar una vacía
        if (empty($this->traffic_convictions) && $this->has_traffic_convictions) {
            $this->traffic_convictions = [[
                'conviction_date' => '',
                'location' => '',
                'charge' => '',
                'penalty' => '',
            ]];
        }

        // Cargar datos de accidentes
        if ($this->userDriverDetail->application && $this->userDriverDetail->application->details) {
            $this->has_accidents = $this->userDriverDetail->application->details->has_accidents ?? false;
        }
        $this->accidents = [];
        foreach ($this->userDriverDetail->accidents as $accident) {
            $this->accidents[] = [
                'id' => $accident->id,
                'accident_date' => $accident->accident_date ? $accident->accident_date->format('Y-m-d') : null,
                'nature_of_accident' => $accident->nature_of_accident,
                'had_injuries' => $accident->had_injuries,
                'number_of_injuries' => $accident->number_of_injuries,
                'had_fatalities' => $accident->had_fatalities,
                'number_of_fatalities' => $accident->number_of_fatalities,
                'comments' => $accident->comments,
            ];
        }
        // Si hay accidentes en la base de datos pero el flag no está activo, activarlo
        if (!$this->has_accidents && $this->userDriverDetail->accidents()->count() > 0) {
            $this->has_accidents = true;
        }

        // Si no hay accidentes pero tiene has_accidents, agregar uno vacío
        if (empty($this->accidents) && $this->has_accidents) {
            $this->accidents = [[
                'accident_date' => '',
                'nature_of_accident' => '',
                'had_injuries' => false,
                'number_of_injuries' => 0,
                'had_fatalities' => false,
                'number_of_fatalities' => 0,
                'comments' => '',
            ]];
        }

        // Cargar datos de FMCSR
        $fmcsrData = $this->userDriverDetail->fmcsrData;
        if ($fmcsrData) {
            $this->is_disqualified = $fmcsrData->is_disqualified;
            $this->disqualified_details = $fmcsrData->disqualified_details;
            $this->is_license_suspended = $fmcsrData->is_license_suspended;
            $this->suspension_details = $fmcsrData->suspension_details;
            $this->is_license_denied = $fmcsrData->is_license_denied;
            $this->denial_details = $fmcsrData->denial_details;
            $this->has_positive_drug_test = $fmcsrData->has_positive_drug_test;
            $this->substance_abuse_professional = $fmcsrData->substance_abuse_professional;
            $this->sap_phone = $fmcsrData->sap_phone;
            $this->return_duty_agency = $fmcsrData->return_duty_agency;
            $this->consent_to_release = $fmcsrData->consent_to_release;
            $this->has_duty_offenses = $fmcsrData->has_duty_offenses;
            $this->recent_conviction_date = $fmcsrData->recent_conviction_date ? $fmcsrData->recent_conviction_date->format('Y-m-d') : null;
            $this->offense_details = $fmcsrData->offense_details;
            $this->consent_driving_record = $fmcsrData->consent_driving_record;
        }

        // Cargar períodos de desempleo
        if ($this->userDriverDetail->application && $this->userDriverDetail->application->details) {
            $this->has_unemployment_periods = $this->userDriverDetail->application->details->has_unemployment_periods ?? false;
            $this->has_completed_employment_history = $this->userDriverDetail->application->details->has_completed_employment_history ?? false;
        }

        $this->unemployment_periods = [];
        foreach ($this->userDriverDetail->unemploymentPeriods as $period) {
            $this->unemployment_periods[] = [
                'id' => $period->id,
                'start_date' => $period->start_date ? $period->start_date->format('Y-m-d') : null,
                'end_date' => $period->end_date ? $period->end_date->format('Y-m-d') : null,
                'comments' => $period->comments,
            ];
        }

        // Si no hay períodos pero flag está activo, agregar uno vacío
        if (empty($this->unemployment_periods) && $this->has_unemployment_periods) {
            $this->unemployment_periods = [[
                'start_date' => '',
                'end_date' => '',
                'comments' => '',
            ]];
        }

        // Cargar historial de empresas
        $this->employment_companies = [];
        foreach ($this->userDriverDetail->employmentCompanies as $company) {
            $this->employment_companies[] = [
                'id' => $company->id,
                'company_name' => $company->company_name,
                'address' => $company->address,
                'city' => $company->city,
                'state' => $company->state,
                'zip' => $company->zip,
                'contact' => $company->contact,
                'phone' => $company->phone,
                'fax' => $company->fax,
                'employed_from' => $company->employed_from ? $company->employed_from->format('Y-m-d') : null,
                'employed_to' => $company->employed_to ? $company->employed_to->format('Y-m-d') : null,
                'positions_held' => $company->positions_held,
                'subject_to_fmcsr' => $company->subject_to_fmcsr,
                'safety_sensitive_function' => $company->safety_sensitive_function,
                'reason_for_leaving' => $company->reason_for_leaving,
                'other_reason_description' => $company->other_reason_description,
                'explanation' => $company->explanation,
                'status' => 'ACTIVE'
            ];
        }

        $this->calculateYearsOfHistory();
    }

    // Métodos de validación para cada paso

    public function validateStep1()
    {
        // Si solo estamos guardando (no avanzando), las validaciones son más permisivas
        if ($this->isSaving) {
            $this->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $this->userDriverDetail->user_id,
                'password' => 'nullable|min:8',
                'password_confirmation' => 'nullable|same:password',
            ]);
        } else {
            // Validación completa para avanzar al siguiente paso
            $this->validate([
                'name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $this->userDriverDetail->user_id,
                'phone' => 'required|string|max:15',
                'date_of_birth' => 'required|date',
                'password' => 'nullable|min:8',
                'password_confirmation' => 'nullable|same:password',
                'terms_accepted' => 'accepted',
            ]);
        }
    }

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

    public function validateStep5()
    {
        // Si estamos editando y ya existe una tarjeta médica, no requerimos el token
        $cardRequired = 'required|string';

        if (isset($this->medical_card_preview_url) && !empty($this->medical_card_preview_url)) {
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

    public function validateStep9()
    {
        try {
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
                'consent_to_release' => 'required_if:has_positive_drug_test,true',
                'has_duty_offenses' => 'sometimes|boolean',
                'recent_conviction_date' => 'required_if:has_duty_offenses,true|date|nullable',
                'offense_details' => 'required_if:has_duty_offenses,true',
                'consent_driving_record' => 'required',
            ]);
        } catch (\Exception $e) {
            // Registrar el error para debugging
            Log::error('Error en validación step 9: ' . $e->getMessage());
            // Agregar el mensaje de error para mostrarlo en la vista
            $this->addError('validation_error', 'Error en validación: ' . $e->getMessage());
            return false;
        }

        return true;
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

    // Métodos para navegación entre pasos

    public function nextStep()
    {
        // Validar el paso actual
        $isValid = true;

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
            $this->validateStep9();
        } elseif ($this->currentStep == 10) {
            $isValid = $this->validateStep10();
        }

        // Si hay errores de validación, no continuar
        if ($this->getErrorBag()->isNotEmpty() || !$isValid) {
            return;
        }

        // Avanzar al siguiente paso
        $this->currentStep++;

        // Actualizar el paso actual en la base de datos
        $this->userDriverDetail->update(['current_step' => $this->currentStep]);
    }

    public function prevStep()
    {
        $this->currentStep--;
    }

    // Métodos para agregar y eliminar elementos dinámicos

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

    public function removePreviousAddress($index)
    {
        unset($this->previous_addresses[$index]);
        $this->previous_addresses = array_values($this->previous_addresses);
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
            'reference_contact' => ''
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

    // Métodos para gestionar certificados y habilidades

    public function removeCertificate($schoolIndex, $tokenIndex)
    {
        unset($this->training_schools[$schoolIndex]['temp_certificate_tokens'][$tokenIndex]);
        $this->training_schools[$schoolIndex]['temp_certificate_tokens'] = array_values($this->training_schools[$schoolIndex]['temp_certificate_tokens']);
    }

    public function removeCertificateById($schoolIndex, $certificateId)
    {
        // Este método se utiliza para eliminar certificados existentes en la base de datos
        try {
            $school = $this->userDriverDetail->trainingSchools()->find($this->training_schools[$schoolIndex]['id']);
            if ($school) {
                // Buscar el media item por ID y eliminarlo
                $mediaItem = $school->getMedia('school_certificates')->firstWhere('id', $certificateId);
                if ($mediaItem) {
                    $mediaItem->delete();

                    // Actualizar certificados en el componente
                    if (isset($this->training_schools[$schoolIndex]['certificates'])) {
                        foreach ($this->training_schools[$schoolIndex]['certificates'] as $key => $cert) {
                            if ($cert['id'] == $certificateId) {
                                unset($this->training_schools[$schoolIndex]['certificates'][$key]);
                                $this->training_schools[$schoolIndex]['certificates'] = array_values($this->training_schools[$schoolIndex]['certificates']);
                                break;
                            }
                        }
                    }

                    return;
                }
            }

            session()->flash('error', 'No se pudo encontrar el certificado para eliminar.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar certificado', [
                'error' => $e->getMessage(),
                'certificateId' => $certificateId
            ]);
            session()->flash('error', 'Error al eliminar el certificado: ' . $e->getMessage());
        }
    }

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
     * Verificar si la aplicación está completa
     */
    private function checkApplicationCompleted()
    {
        // Verificar si tiene al menos:
        // - Una licencia registrada
        // - Al menos una experiencia de conducción
        // - Información médica básica
        $hasLicense = $this->userDriverDetail->licenses()->exists();
        $hasExperience = $this->userDriverDetail->experiences()->exists();
        $hasMedical = $this->userDriverDetail->medicalQualification()->exists();
        return $hasLicense && $hasExperience && $hasMedical;
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
            } elseif ($this->currentStep == 9) {
                $this->validateStep9();
            } elseif ($this->currentStep == 10) {
                $this->validateStep10();
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

            // Actualizar User
            $user = $this->userDriverDetail->user;
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
            ]);

            // Actualizar foto de perfil
            if ($this->photo) {
                $this->userDriverDetail->clearMediaCollection('profile_photo_driver');
                $fileName = strtolower(str_replace(' ', '_', $this->name)) . '.webp';
                $this->userDriverDetail->addMedia($this->photo->getRealPath())
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }

            // Obtener o crear la aplicación si no existe
            $application = $this->userDriverDetail->application;
            if (!$application) {
                $application = DriverApplication::create([
                    'user_id' => $user->id,
                    'status' => 'draft'
                ]);
            }

            // Actualizar dirección principal
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
                    'to_date' => $this->to_date,
                ]
            );

            // Manejo de direcciones previas
            if (!$this->lived_three_years && !empty($this->previous_addresses)) {
                // Guardar direcciones anteriores
                $existingAddressIds = $application->addresses()->where('primary', false)->pluck('id')->toArray();
                $updatedAddressIds = [];

                foreach ($this->previous_addresses as $prevAddressData) {
                    if (empty($prevAddressData['address_line1'])) continue;

                    $addressId = $prevAddressData['id'] ?? null;
                    if ($addressId) {
                        // Actualizar dirección existente
                        $address = $application->addresses()->find($addressId);
                        if ($address) {
                            $address->update([
                                'address_line1' => $prevAddressData['address_line1'],
                                'address_line2' => $prevAddressData['address_line2'] ?? null,
                                'city' => $prevAddressData['city'],
                                'state' => $prevAddressData['state'],
                                'zip_code' => $prevAddressData['zip_code'],
                                'from_date' => $prevAddressData['from_date'],
                                'to_date' => $prevAddressData['to_date'],
                            ]);
                            $updatedAddressIds[] = $address->id;
                        }
                    } else {
                        // Crear nueva dirección
                        $address = $application->addresses()->create([
                            'primary' => false,
                            'address_line1' => $prevAddressData['address_line1'],
                            'address_line2' => $prevAddressData['address_line2'] ?? null,
                            'city' => $prevAddressData['city'],
                            'state' => $prevAddressData['state'],
                            'zip_code' => $prevAddressData['zip_code'],
                            'from_date' => $prevAddressData['from_date'],
                            'to_date' => $prevAddressData['to_date'],
                            'lived_three_years' => false,
                        ]);
                        $updatedAddressIds[] = $address->id;
                    }
                }

                // Eliminar direcciones previas que ya no existen
                $addressesToDelete = array_diff($existingAddressIds, $updatedAddressIds);
                if (!empty($addressesToDelete)) {
                    $application->addresses()->whereIn('id', $addressesToDelete)->delete();
                }
            } else if ($this->lived_three_years) {
                // Si ahora vive más de 3 años, eliminar todas las direcciones previas
                $application->addresses()->where('primary', false)->delete();
            }

            // Actualizar detalles de la aplicación
            $application->details()->updateOrCreate(
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
                    'has_attended_training_school' => $this->has_attended_training_school,
                ]
            );

            // Actualizar historiales de trabajo
            if ($this->has_work_history) {
                $existingWorkHistoryIds = $this->userDriverDetail->workHistories()->pluck('id')->toArray();
                $updatedWorkHistoryIds = [];

                foreach ($this->work_histories as $historyData) {
                    if (empty($historyData['previous_company'])) continue;

                    $historyId = $historyData['id'] ?? null;
                    if ($historyId) {
                        // Actualizar historial existente
                        $history = $this->userDriverDetail->workHistories()->find($historyId);
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
                        }
                    } else {
                        // Crear nuevo historial
                        $history = $this->userDriverDetail->workHistories()->create([
                            'previous_company' => $historyData['previous_company'],
                            'start_date' => $historyData['start_date'],
                            'end_date' => $historyData['end_date'],
                            'location' => $historyData['location'],
                            'position' => $historyData['position'],
                            'reason_for_leaving' => $historyData['reason_for_leaving'] ?? null,
                            'reference_contact' => $historyData['reference_contact'] ?? null,
                        ]);
                        $updatedWorkHistoryIds[] = $history->id;
                    }
                }

                // Eliminar historiales que ya no existen
                $historiesToDelete = array_diff($existingWorkHistoryIds, $updatedWorkHistoryIds);
                if (!empty($historiesToDelete)) {
                    $this->userDriverDetail->workHistories()->whereIn('id', $historiesToDelete)->delete();
                }
            } else {
                // Si ya no tiene historial laboral, eliminar todos los registros
                $this->userDriverDetail->workHistories()->delete();
            }

            // Actualizar licencias y experiencias si estamos en Step 4 o superior
            if ($this->currentStep >= 4) {
                $this->saveCurrentLicenses();
            }

            // Actualizar información médica si estamos en Step 5 o superior
            if ($this->currentStep >= 5) {
                $this->saveCurrentMedical();
            }

            // Actualizar escuelas de capacitación si estamos en Step 6
            if ($this->currentStep >= 6) {
                $this->saveCurrentTraining();
            }

            if ($this->currentStep >= 7) {
                $this->saveTrafficConvictions();
            }

            // Actualizar accidentes si estamos en Step 8 o superior
            if ($this->currentStep >= 8) {
                $this->saveAccidents();
            }

            if ($this->currentStep >= 9) {
                $this->saveFmcsrData();
            }

            if ($this->currentStep >= 10) {
                $this->saveUnemploymentPeriods();
                $this->saveEmploymentCompanies();
            }

            // Verificar si la aplicación está completa
            $isCompleted = $this->checkApplicationCompleted();
            $this->userDriverDetail->update(['application_completed' => $isCompleted]);


            DB::commit();

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

    /**
     * Método para actualizar el driver
     */
    public function updateDriver()
    {
        // Validar todos los pasos
        $this->validateAllSteps();

        // Si hay errores de validación, no continuar
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        DB::beginTransaction();
        try {
            Log::info('Iniciando actualización de driver', [
                'driver_id' => $this->userDriverDetail->id,
            ]);

            // Actualizar User
            $user = $this->userDriverDetail->user;
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
            ]);

            // Actualizar foto de perfil
            if ($this->photo) {
                $this->userDriverDetail->clearMediaCollection('profile_photo_driver');
                $fileName = strtolower(str_replace(' ', '_', $this->name)) . '.webp';
                $this->userDriverDetail->addMedia($this->photo->getRealPath())
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }

            // Obtener o crear la aplicación si no existe
            $application = $this->userDriverDetail->application;
            if (!$application) {
                $application = DriverApplication::create([
                    'user_id' => $user->id,
                    'status' => 'draft'
                ]);
            }

            // Actualizar dirección principal
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
                    'to_date' => $this->to_date,
                ]
            );

            // Manejo de direcciones previas
            if (!$this->lived_three_years && !empty($this->previous_addresses)) {
                // Guardar direcciones anteriores
                $existingAddressIds = $application->addresses()->where('primary', false)->pluck('id')->toArray();
                $updatedAddressIds = [];

                foreach ($this->previous_addresses as $prevAddressData) {
                    if (empty($prevAddressData['address_line1'])) continue;

                    $addressId = $prevAddressData['id'] ?? null;
                    if ($addressId) {
                        // Actualizar dirección existente
                        $address = $application->addresses()->find($addressId);
                        if ($address) {
                            $address->update([
                                'address_line1' => $prevAddressData['address_line1'],
                                'address_line2' => $prevAddressData['address_line2'] ?? null,
                                'city' => $prevAddressData['city'],
                                'state' => $prevAddressData['state'],
                                'zip_code' => $prevAddressData['zip_code'],
                                'from_date' => $prevAddressData['from_date'],
                                'to_date' => $prevAddressData['to_date'],
                            ]);
                            $updatedAddressIds[] = $address->id;
                        }
                    } else {
                        // Crear nueva dirección
                        $address = $application->addresses()->create([
                            'primary' => false,
                            'address_line1' => $prevAddressData['address_line1'],
                            'address_line2' => $prevAddressData['address_line2'] ?? null,
                            'city' => $prevAddressData['city'],
                            'state' => $prevAddressData['state'],
                            'zip_code' => $prevAddressData['zip_code'],
                            'from_date' => $prevAddressData['from_date'],
                            'to_date' => $prevAddressData['to_date'],
                            'lived_three_years' => false,
                        ]);
                        $updatedAddressIds[] = $address->id;
                    }
                }

                // Eliminar direcciones previas que ya no existen
                $addressesToDelete = array_diff($existingAddressIds, $updatedAddressIds);
                if (!empty($addressesToDelete)) {
                    $application->addresses()->whereIn('id', $addressesToDelete)->delete();
                }
            } else if ($this->lived_three_years) {
                // Si ahora vive más de 3 años, eliminar todas las direcciones previas
                $application->addresses()->where('primary', false)->delete();
            }

            // Actualizar detalles de la aplicación
            $application->details()->updateOrCreate(
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
                    'has_attended_training_school' => $this->has_attended_training_school,
                ]
            );

            // Actualizar historiales de trabajo
            if ($this->has_work_history) {
                $existingWorkHistoryIds = $this->userDriverDetail->workHistories()->pluck('id')->toArray();
                $updatedWorkHistoryIds = [];

                foreach ($this->work_histories as $historyData) {
                    if (empty($historyData['previous_company'])) continue;

                    $historyId = $historyData['id'] ?? null;
                    if ($historyId) {
                        // Actualizar historial existente
                        $history = $this->userDriverDetail->workHistories()->find($historyId);
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
                        }
                    } else {
                        // Crear nuevo historial
                        $history = $this->userDriverDetail->workHistories()->create([
                            'previous_company' => $historyData['previous_company'],
                            'start_date' => $historyData['start_date'],
                            'end_date' => $historyData['end_date'],
                            'location' => $historyData['location'],
                            'position' => $historyData['position'],
                            'reason_for_leaving' => $historyData['reason_for_leaving'] ?? null,
                            'reference_contact' => $historyData['reference_contact'] ?? null,
                        ]);
                        $updatedWorkHistoryIds[] = $history->id;
                    }
                }

                // Eliminar historiales que ya no existen
                $historiesToDelete = array_diff($existingWorkHistoryIds, $updatedWorkHistoryIds);
                if (!empty($historiesToDelete)) {
                    $this->userDriverDetail->workHistories()->whereIn('id', $historiesToDelete)->delete();
                }
            } else {
                // Si ya no tiene historial laboral, eliminar todos los registros
                $this->userDriverDetail->workHistories()->delete();
            }

            // Actualizar licencias y experiencias
            $this->saveCurrentLicenses();

            // Actualizar información médica
            $this->saveCurrentMedical();

            // Actualizar escuelas de capacitación
            $this->saveCurrentTraining();

            // Verificar si la aplicación está completa
            $isCompleted = $this->checkApplicationCompleted();
            $this->userDriverDetail->update(['application_completed' => $isCompleted]);

            // Guardar infracciones de tráfico y accidentes
            $this->saveTrafficConvictions();
            $this->saveAccidents();

            // Actualizar datos de FMCSR
            $this->saveFmcsrData();

            // Actualizar historial de empleo y desempleo
            $this->saveUnemploymentPeriods();
            $this->saveEmploymentCompanies();

            DB::commit();

            $this->successMessage = 'Driver updated successfully!';

            // Redirigir a la lista de conductores
            return redirect()->route('admin.carrier.user_drivers.index', $this->carrier)
                ->with('success', 'Driver updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating driver: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Error updating driver: ' . $e->getMessage());
        }
    }

    /**
     * Validar todos los pasos para la actualización completa
     */
    private function validateAllSteps()
    {
        // Step 1
        $this->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userDriverDetail->user_id,
            'phone' => 'required|string|max:15',
            'date_of_birth' => 'required|date',
            'password' => 'nullable|min:8',
            'password_confirmation' => 'nullable|same:password',
            'terms_accepted' => 'accepted',
        ]);

        // Step 2
        $this->validate([
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'nullable|date',
        ]);

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

        // Step 3
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

        // Step 4
        $this->validate([
            'current_license_number' => 'required|string|max:255',
        ]);

        foreach ($this->licenses as $index => $license) {
            $this->validate([
                "licenses.{$index}.license_number" => 'required|string|max:255',
                "licenses.{$index}.state_of_issue" => 'required|string|max:255',
                "licenses.{$index}.license_class" => 'required|string|max:255',
                "licenses.{$index}.expiration_date" => 'required|date',
            ]);
        }

        foreach ($this->experiences as $index => $experience) {
            $this->validate([
                "experiences.{$index}.equipment_type" => 'required|string|max:255',
                "experiences.{$index}.years_experience" => 'required|integer|min:0',
                "experiences.{$index}.miles_driven" => 'required|integer|min:0',
            ]);
        }

        // Step 5
        $cardRequired = isset($this->medical_card_preview_url) && !empty($this->medical_card_preview_url)
            ? 'nullable|string' : 'required|string';

        $this->validate([
            'social_security_number' => 'required|string|max:255',
            'medical_examiner_name' => 'required|string|max:255',
            'medical_examiner_registry_number' => 'required|string|max:255',
            'medical_card_expiration_date' => 'required|date',
            'temp_medical_card_token' => $cardRequired,
            'suspension_date' => 'nullable|required_if:is_suspended,true|date',
            'termination_date' => 'nullable|required_if:is_terminated,true|date',
        ]);

        // Step 6
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


        // Paso 9 - FMCSR
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
            'consent_to_release' => 'required_if:has_positive_drug_test,true|accepted',
            'has_duty_offenses' => 'sometimes|boolean',
            'recent_conviction_date' => 'required_if:has_duty_offenses,true|date|nullable',
            'offense_details' => 'required_if:has_duty_offenses,true',
            'consent_driving_record' => 'required|boolean',
        ]);

        // Paso 10 - Historial de empleo
        $this->validate([
            'has_unemployment_periods' => 'sometimes|boolean',
            'has_completed_employment_history' => 'boolean',
        ]);

        // Validar que haya suficiente historial
        if ($this->years_of_history < 10) {
            $this->addError('employment_history', 'You must have at least 10 years of employment history without gaps.');
        }
    }

    /**
     * Guardar licencias actuales
     */
    private function saveCurrentLicenses()
    {
        if (!$this->userDriverDetail) return;

        // Guardar licencias
        $existingLicenseIds = $this->userDriverDetail->licenses()->pluck('id')->toArray();
        $updatedLicenseIds = [];

        // Continuación del método saveCurrentLicenses()
        foreach ($this->licenses as $index => $license) {
            if (empty($license['license_number'])) continue;

            $licenseId = $license['id'] ?? null;
            if ($licenseId) {
                // Actualizar licencia existente
                $licenseObj = $this->userDriverDetail->licenses()->find($licenseId);
                if ($licenseObj) {
                    $licenseObj->update([
                        'current_license_number' => $this->current_license_number,
                        'license_number' => $license['license_number'],
                        'state_of_issue' => $license['state_of_issue'] ?? '',
                        'license_class' => $license['license_class'] ?? '',
                        'expiration_date' => $license['expiration_date'] ?? now(),
                        'is_cdl' => isset($license['is_cdl']),
                        'is_primary' => $index === 0,
                        'status' => 'active'
                    ]);
                    $updatedLicenseIds[] = $licenseObj->id;

                    // Gestionar endorsements si es CDL
                    if (isset($license['is_cdl']) && isset($license['endorsements'])) {
                        // Eliminar endorsements existentes
                        $licenseObj->endorsements()->detach();

                        // Crear nuevos endorsements
                        foreach ($license['endorsements'] as $code) {
                            $endorsement = LicenseEndorsement::firstOrCreate(
                                ['code' => $code],
                                [
                                    'name' => $this->getEndorsementName($code),
                                    'description' => null,
                                    'is_active' => true
                                ]
                            );
                            $licenseObj->endorsements()->attach($endorsement->id, [
                                'issued_date' => now(),
                                'expiration_date' => $license['expiration_date'] ?? now()
                            ]);
                        }
                    }

                    // Procesar imágenes si hay tokens
                    if (!empty($license['temp_front_token'])) {
                        $tempUploadService = app(TempUploadService::class);
                        $tempPath = $tempUploadService->moveToPermanent($license['temp_front_token']);
                        if ($tempPath && file_exists($tempPath)) {
                            $licenseObj->clearMediaCollection('license_front');
                            $licenseObj->addMedia($tempPath)
                                ->toMediaCollection('license_front');
                        }
                    }

                    if (!empty($license['temp_back_token'])) {
                        $tempUploadService = app(TempUploadService::class);
                        $tempPath = $tempUploadService->moveToPermanent($license['temp_back_token']);
                        if ($tempPath && file_exists($tempPath)) {
                            $licenseObj->clearMediaCollection('license_back');
                            $licenseObj->addMedia($tempPath)
                                ->toMediaCollection('license_back');
                        }
                    }
                }
            } else {
                // Crear nueva licencia
                $licenseObj = $this->userDriverDetail->licenses()->create([
                    'current_license_number' => $this->current_license_number,
                    'license_number' => $license['license_number'],
                    'state_of_issue' => $license['state_of_issue'] ?? '',
                    'license_class' => $license['license_class'] ?? '',
                    'expiration_date' => $license['expiration_date'] ?? now(),
                    'is_cdl' => isset($license['is_cdl']),
                    'is_primary' => $index === 0,
                    'status' => 'active'
                ]);
                $updatedLicenseIds[] = $licenseObj->id;

                // Añadir endorsements para nuevas licencias CDL
                if (isset($license['is_cdl']) && isset($license['endorsements'])) {
                    foreach ($license['endorsements'] as $code) {
                        $endorsement = LicenseEndorsement::firstOrCreate(
                            ['code' => $code],
                            [
                                'name' => $this->getEndorsementName($code),
                                'description' => null,
                                'is_active' => true
                            ]
                        );
                        $licenseObj->endorsements()->attach($endorsement->id, [
                            'issued_date' => now(),
                            'expiration_date' => $license['expiration_date'] ?? now()
                        ]);
                    }
                }

                // Procesar imágenes para nuevas licencias
                if (!empty($license['temp_front_token'])) {
                    $tempUploadService = app(TempUploadService::class);
                    $tempPath = $tempUploadService->moveToPermanent($license['temp_front_token']);
                    if ($tempPath && file_exists($tempPath)) {
                        $licenseObj->addMedia($tempPath)
                            ->toMediaCollection('license_front');
                    }
                }

                if (!empty($license['temp_back_token'])) {
                    $tempUploadService = app(TempUploadService::class);
                    $tempPath = $tempUploadService->moveToPermanent($license['temp_back_token']);
                    if ($tempPath && file_exists($tempPath)) {
                        $licenseObj->addMedia($tempPath)
                            ->toMediaCollection('license_back');
                    }
                }
            }
        }

        // Eliminar licencias que ya no existen
        $licensesToDelete = array_diff($existingLicenseIds, $updatedLicenseIds);
        if (!empty($licensesToDelete)) {
            $this->userDriverDetail->licenses()->whereIn('id', $licensesToDelete)->delete();
        }

        // Guardar experiencias
        $existingExpIds = $this->userDriverDetail->experiences()->pluck('id')->toArray();
        $updatedExpIds = [];

        foreach ($this->experiences as $experience) {
            if (empty($experience['equipment_type'])) continue;

            $expId = $experience['id'] ?? null;
            if ($expId) {
                // Actualizar experiencia existente
                $experienceObj = $this->userDriverDetail->experiences()->find($expId);
                if ($experienceObj) {
                    $experienceObj->update([
                        'equipment_type' => $experience['equipment_type'],
                        'years_experience' => $experience['years_experience'] ?? 0,
                        'miles_driven' => $experience['miles_driven'] ?? 0,
                        'requires_cdl' => isset($experience['requires_cdl'])
                    ]);
                    $updatedExpIds[] = $experienceObj->id;
                }
            } else {
                // Crear nueva experiencia
                $experienceObj = $this->userDriverDetail->experiences()->create([
                    'equipment_type' => $experience['equipment_type'],
                    'years_experience' => $experience['years_experience'] ?? 0,
                    'miles_driven' => $experience['miles_driven'] ?? 0,
                    'requires_cdl' => isset($experience['requires_cdl'])
                ]);
                $updatedExpIds[] = $experienceObj->id;
            }
        }

        // Eliminar experiencias que ya no existen
        $expsToDelete = array_diff($existingExpIds, $updatedExpIds);
        if (!empty($expsToDelete)) {
            $this->userDriverDetail->experiences()->whereIn('id', $expsToDelete)->delete();
        }
    }

    /**
     * Guardar información médica
     */
    private function saveCurrentMedical()
    {
        if (!$this->userDriverDetail) return;

        $medical = $this->userDriverDetail->medicalQualification()->updateOrCreate(
            [],
            [
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
            ]
        );

        // Procesar archivo médico
        if (!empty($this->temp_medical_card_token)) {
            $tempUploadService = app(TempUploadService::class);
            $tempPath = $tempUploadService->moveToPermanent($this->temp_medical_card_token);
            if ($tempPath && file_exists($tempPath)) {
                $medical->clearMediaCollection('medical_card');
                $medical->addMedia($tempPath)
                    ->toMediaCollection('medical_card');
            }
        } elseif ($this->medical_card_file) {
            $medical->clearMediaCollection('medical_card');
            $medical->addMedia($this->medical_card_file->getRealPath())
                ->toMediaCollection('medical_card');
        }
    }

    /**
     * Guardar información de capacitación
     */
    private function saveCurrentTraining()
    {
        if (!$this->userDriverDetail || !$this->userDriverDetail->application) return;

        if ($this->userDriverDetail->application->details) {
            $this->userDriverDetail->application->details->update([
                'has_attended_training_school' => $this->has_attended_training_school
            ]);
        }

        if ($this->has_attended_training_school) {
            $existingSchoolIds = $this->userDriverDetail->trainingSchools()->pluck('id')->toArray();
            $updatedSchoolIds = [];

            foreach ($this->training_schools as $school) {
                if (empty($school['school_name'])) continue;

                $schoolId = $school['id'] ?? null;
                if ($schoolId) {
                    // Actualizar escuela existente
                    $schoolObj = $this->userDriverDetail->trainingSchools()->find($schoolId);
                    if ($schoolObj) {
                        $schoolObj->update([
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
                        ]);
                        $updatedSchoolIds[] = $schoolObj->id;

                        // Procesar certificados temporales
                        if (!empty($school['temp_certificate_tokens'])) {
                            foreach ($school['temp_certificate_tokens'] as $certData) {
                                if (empty($certData['token'])) continue;

                                $tempUploadService = app(TempUploadService::class);
                                $tempPath = $tempUploadService->moveToPermanent($certData['token']);
                                if ($tempPath && file_exists($tempPath)) {
                                    $schoolObj->addMedia($tempPath)
                                        ->toMediaCollection('school_certificates');
                                }
                            }
                        }
                    }
                } else {
                    // Crear nueva escuela
                    $schoolObj = $this->userDriverDetail->trainingSchools()->create([
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
                    ]);
                    $updatedSchoolIds[] = $schoolObj->id;

                    // Procesar certificados para nuevas escuelas
                    if (!empty($school['temp_certificate_tokens'])) {
                        foreach ($school['temp_certificate_tokens'] as $certData) {
                            if (empty($certData['token'])) continue;

                            $tempUploadService = app(TempUploadService::class);
                            $tempPath = $tempUploadService->moveToPermanent($certData['token']);
                            if ($tempPath && file_exists($tempPath)) {
                                $schoolObj->addMedia($tempPath)
                                    ->toMediaCollection('school_certificates');
                            }
                        }
                    }
                }
            }

            // Eliminar escuelas que ya no existen
            $schoolsToDelete = array_diff($existingSchoolIds, $updatedSchoolIds);
            if (!empty($schoolsToDelete)) {
                $this->userDriverDetail->trainingSchools()->whereIn('id', $schoolsToDelete)->delete();
            }
        } else {
            // Si no asistió a ninguna escuela, eliminar todos los registros
            $this->userDriverDetail->trainingSchools()->delete();
        }
    }

    private function saveTrafficConvictions()
    {
        if ($this->userDriverDetail && $this->userDriverDetail->application) {
            // Actualizar el flag en los detalles de la aplicación
            if ($this->userDriverDetail->application->details) {
                $this->userDriverDetail->application->details->update([
                    'has_traffic_convictions' => $this->has_traffic_convictions
                ]);
            }

            if ($this->has_traffic_convictions) {
                // Guardar infracciones de tráfico
                $existingConvictionIds = $this->userDriverDetail->trafficConvictions()->pluck('id')->toArray();
                $updatedConvictionIds = [];

                foreach ($this->traffic_convictions as $convictionData) {
                    if (empty($convictionData['conviction_date'])) continue;

                    $convictionId = $convictionData['id'] ?? null;
                    if ($convictionId) {
                        // Actualizar existente
                        $conviction = $this->userDriverDetail->trafficConvictions()->find($convictionId);
                        if ($conviction) {
                            $conviction->update([
                                'conviction_date' => $convictionData['conviction_date'],
                                'location' => $convictionData['location'],
                                'charge' => $convictionData['charge'],
                                'penalty' => $convictionData['penalty'],
                            ]);
                            $updatedConvictionIds[] = $conviction->id;
                        }
                    } else {
                        // Crear nuevo
                        $conviction = $this->userDriverDetail->trafficConvictions()->create([
                            'conviction_date' => $convictionData['conviction_date'],
                            'location' => $convictionData['location'],
                            'charge' => $convictionData['charge'],
                            'penalty' => $convictionData['penalty'],
                        ]);
                        $updatedConvictionIds[] = $conviction->id;
                    }
                }

                // Eliminar infracciones que ya no existen
                $convictionsToDelete = array_diff($existingConvictionIds, $updatedConvictionIds);
                if (!empty($convictionsToDelete)) {
                    $this->userDriverDetail->trafficConvictions()->whereIn('id', $convictionsToDelete)->delete();
                }
            } else {
                // Si no tiene infracciones, eliminar todos los registros
                $this->userDriverDetail->trafficConvictions()->delete();
            }
        }
    }

    private function saveAccidents()
    {
        if ($this->userDriverDetail && $this->userDriverDetail->application) {
            // Actualizar el flag en los detalles de la aplicación
            if ($this->userDriverDetail->application->details) {
                $this->userDriverDetail->application->details->update([
                    'has_accidents' => $this->has_accidents
                ]);
            }

            if ($this->has_accidents) {
                // Guardar accidentes
                $existingAccidentIds = $this->userDriverDetail->accidents()->pluck('id')->toArray();
                $updatedAccidentIds = [];

                foreach ($this->accidents as $accidentData) {
                    if (empty($accidentData['accident_date'])) continue;

                    $accidentId = $accidentData['id'] ?? null;
                    if ($accidentId) {
                        // Actualizar existente
                        $accident = $this->userDriverDetail->accidents()->find($accidentId);
                        if ($accident) {
                            $accident->update([
                                'accident_date' => $accidentData['accident_date'],
                                'nature_of_accident' => $accidentData['nature_of_accident'],
                                'had_injuries' => $accidentData['had_injuries'] ?? false,
                                'number_of_injuries' => $accidentData['had_injuries'] ? ($accidentData['number_of_injuries'] ?? 0) : 0,
                                'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                                'number_of_fatalities' => $accidentData['had_fatalities'] ? ($accidentData['number_of_fatalities'] ?? 0) : 0,
                                'comments' => $accidentData['comments'] ?? null,
                            ]);
                            $updatedAccidentIds[] = $accident->id;
                        }
                    } else {
                        // Crear nuevo
                        $accident = $this->userDriverDetail->accidents()->create([
                            'accident_date' => $accidentData['accident_date'],
                            'nature_of_accident' => $accidentData['nature_of_accident'],
                            'had_injuries' => $accidentData['had_injuries'] ?? false,
                            'number_of_injuries' => $accidentData['had_injuries'] ? ($accidentData['number_of_injuries'] ?? 0) : 0,
                            'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                            'number_of_fatalities' => $accidentData['had_fatalities'] ? ($accidentData['number_of_fatalities'] ?? 0) : 0,
                            'comments' => $accidentData['comments'] ?? null,
                        ]);
                        $updatedAccidentIds[] = $accident->id;
                    }
                }

                // Eliminar accidentes que ya no existen
                $accidentsToDelete = array_diff($existingAccidentIds, $updatedAccidentIds);
                if (!empty($accidentsToDelete)) {
                    $this->userDriverDetail->accidents()->whereIn('id', $accidentsToDelete)->delete();
                }
            } else {
                // Si no tiene accidentes, eliminar todos los registros
                $this->userDriverDetail->accidents()->delete();
            }
        }
    }


    // Métodos para administrar períodos de desempleo
    public function addUnemploymentPeriod()
    {
        $this->unemployment_periods[] = [
            'start_date' => '',
            'end_date' => '',
            'comments' => '',
        ];
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

        $this->calculateYearsOfHistory();
    }

    // Métodos para empresas de empleo
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
        // Validate the form
        $this->validate([
            'company_form.employed_from' => 'required|date',
            'company_form.employed_to' => 'required|date|after_or_equal:company_form.employed_from',
            'company_form.positions_held' => 'required|string|max:255',
            'company_form.reason_for_leaving' => 'required|string|max:255',
            'company_form.other_reason_description' => 'required_if:company_form.reason_for_leaving,other|max:255',
        ]);

        // Prepare the employment history data
        $employmentData = [
            'master_company_id' => $this->company_form['master_company_id'] ?? null,
            'employed_from' => $this->company_form['employed_from'],
            'employed_to' => $this->company_form['employed_to'],
            'positions_held' => $this->company_form['positions_held'],
            'reason_for_leaving' => $this->company_form['reason_for_leaving'],
            'other_reason_description' => $this->company_form['reason_for_leaving'] === 'other'
                ? $this->company_form['other_reason_description']
                : null,
            'explanation' => $this->company_form['explanation'] ?? null,
        ];

        // If we don't have a master_company_id, create a new master company
        if (empty($this->company_form['master_company_id'])) {
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

            $employmentData['master_company_id'] = $masterCompany->id;
        }

        // Add to or update the employment companies list
        if ($this->editing_company_index !== null) {
            // Update existing entry
            $this->employment_companies[$this->editing_company_index] = array_merge(
                $this->company_form,
                ['status' => 'ACTIVE']
            );
        } else {
            // Add new entry
            $this->employment_companies[] = array_merge(
                $this->company_form,
                ['status' => 'ACTIVE']
            );
        }

        // Close form and calculate history
        $this->showCompanyForm = false;
        $this->resetCompanyForm();
        $this->calculateYearsOfHistory();
    }

    // Calcular años totales de historial
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


    // Métodos para manejar Traffic Convictions
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
        if (count($this->traffic_convictions) > 1) {
            unset($this->traffic_convictions[$index]);
            $this->traffic_convictions = array_values($this->traffic_convictions);
        }
    }

    // Métodos para manejar Accidents
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
        if (count($this->accidents) > 1) {
            unset($this->accidents[$index]);
            $this->accidents = array_values($this->accidents);
        }
    }

    // Guardar datos FMCSR
    private function saveFmcsrData()
    {
        // Crear o actualizar datos FMCSR
        $this->userDriverDetail->fmcsrData()->updateOrCreate(
            [], // Solo un registro por conductor
            [
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
            ]
        );
    }

    // Guardar períodos de desempleo
    private function saveUnemploymentPeriods()
    {
        // Actualizar flag en detalles de aplicación
        if ($this->userDriverDetail->application && $this->userDriverDetail->application->details) {
            $this->userDriverDetail->application->details->update([
                'has_unemployment_periods' => $this->has_unemployment_periods,
                'has_completed_employment_history' => $this->has_completed_employment_history
            ]);
        }

        if ($this->has_unemployment_periods) {
            // Obtener IDs existentes para detectar eliminaciones
            $existingPeriodIds = $this->userDriverDetail->unemploymentPeriods()->pluck('id')->toArray();
            $updatedPeriodIds = [];

            foreach ($this->unemployment_periods as $periodData) {
                // Validar datos mínimos
                if (empty($periodData['start_date']) || empty($periodData['end_date'])) {
                    continue;
                }

                // Si tiene ID, es un período existente
                $periodId = $periodData['id'] ?? null;
                $period = null;

                if ($periodId) {
                    $period = $this->userDriverDetail->unemploymentPeriods()->find($periodId);
                }

                if (!$period) {
                    // Crear nuevo período
                    $period = $this->userDriverDetail->unemploymentPeriods()->create([
                        'start_date' => $periodData['start_date'],
                        'end_date' => $periodData['end_date'],
                        'comments' => $periodData['comments'] ?? null
                    ]);
                } else {
                    // Actualizar período existente
                    $period->update([
                        'start_date' => $periodData['start_date'],
                        'end_date' => $periodData['end_date'],
                        'comments' => $periodData['comments'] ?? null
                    ]);
                }

                $updatedPeriodIds[] = $period->id;
            }

            // Eliminar períodos que ya no existen
            $periodsToDelete = array_diff($existingPeriodIds, $updatedPeriodIds);
            if (!empty($periodsToDelete)) {
                $this->userDriverDetail->unemploymentPeriods()->whereIn('id', $periodsToDelete)->delete();
            }
        } else {
            // Si no hay períodos de desempleo, eliminar todos
            $this->userDriverDetail->unemploymentPeriods()->delete();
        }
    }

    // Guardar empresas de historial laboral
    private function saveEmploymentCompanies()
    {
        // Obtener IDs existentes para detectar eliminaciones
        $existingCompanyIds = $this->userDriverDetail->employmentCompanies()->pluck('id')->toArray();
        $updatedCompanyIds = [];

        foreach ($this->employment_companies as $companyData) {
            // Validar datos mínimos
            if (empty($companyData['company_name']) || empty($companyData['employed_from']) || empty($companyData['employed_to'])) {
                continue;
            }

            // Si tiene ID, es una empresa existente
            $companyId = $companyData['id'] ?? null;
            $company = null;

            if ($companyId) {
                $company = $this->userDriverDetail->employmentCompanies()->find($companyId);
            }

            $companyAttributes = [
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
            ];

            if (!$company) {
                // Crear nueva empresa
                $company = $this->userDriverDetail->employmentCompanies()->create($companyAttributes);
            } else {
                // Actualizar empresa existente
                $company->update($companyAttributes);
            }

            $updatedCompanyIds[] = $company->id;
        }

        // Eliminar empresas que ya no existen
        $companiesToDelete = array_diff($existingCompanyIds, $updatedCompanyIds);
        if (!empty($companiesToDelete)) {
            $this->userDriverDetail->employmentCompanies()->whereIn('id', $companiesToDelete)->delete();
        }
    }

    /**
     * Abrir el modal de búsqueda de empresas
     */
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
            $this->searchResults = \App\Models\Admin\Driver\DriverEmploymentCompany::orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->toArray();
            return;
        }

        // Buscar empresas que coincidan con el término de búsqueda
        $this->searchResults = \App\Models\Admin\Driver\DriverEmploymentCompany::where('company_name', 'like', '%' . $this->companySearchTerm . '%')
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
        // Fetch the master company
        $masterCompany = MasterCompany::find($companyId);

        if ($masterCompany) {
            // Just populate the form with company data
            $this->company_form = [
                'master_company_id' => $masterCompany->id,
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

                // Employment-specific fields remain empty for user to fill
                'employed_from' => null,
                'employed_to' => null,
                'positions_held' => '',
                'reason_for_leaving' => '',
                'other_reason_description' => '',
                'explanation' => '',
            ];

            // Show the form for completing employment-specific details
            $this->closeSearchCompanyModal();
            $this->showCompanyForm = true;
        }
    }

    // Actualiza el método updatedCompanySearchTerm para búsqueda dinámica
    public function updatedCompanySearchTerm()
    {
        $this->searchCompanies();
    }

    /**
     * Renderizar el componente
     */
    public function render()
    {
        return view('livewire.admin.driver.driver-edit-form', [
            'usStates' => Constants::usStates(),
            'driverPositions' => Constants::driverPositions(),
            'referralSources' => Constants::referralSources()
        ]);
    }
}
