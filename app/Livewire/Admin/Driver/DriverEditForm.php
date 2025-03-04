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
use App\Services\Admin\DriverStepService;
use App\Services\Admin\TempUploadService;

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
                $this->has_work_history = $application->details->has_work_history ?? false;
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

    // Métodos para navegación entre pasos

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
