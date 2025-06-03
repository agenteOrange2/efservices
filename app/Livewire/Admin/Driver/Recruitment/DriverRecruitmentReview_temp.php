<?php

namespace App\Livewire\Admin\Driver\Recruitment;

use ZipArchive;
use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Services\Admin\DriverStepService;
use App\Models\Admin\Driver\DriverApplication;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Admin\Driver\DriverRecruitmentVerification;
use Livewire\WithFileUploads;

class DriverRecruitmentReview extends Component
{
    use WithFileUploads;
    public $driverId;
    public $driver;
    public $application;
    public $steps = [];
    public $stepsStatus = [];
    public $currentTab = 'general';
    public $checklistItems = [];
    public $rejectionReason = '';
    public $historyItems = [];
    public $requestedDocuments = [];
    public $additionalRequirements = '';
    public $documentReasons = [];
    public $selectedDocument = null;
    public $documentReason = '';
    public $completionPercentage = 0;
    public $verificationNotes = '';
    public $savedVerification = null;
    public $totalExperienceYears = 0;

    // Nueva propiedad para PDFs generados
    public $generatedPdfs = [];
    public $isRegeneratingPdfs = false;
    
    // Propiedades para manejo de carga de documentos
    public $documentCategory = ''; // license, medical, record, other
    public $documentDescription = '';
    public $documentFile = null;
    public $tempDocumentPath = null;
    public $tempDocumentName = null;
    public $tempDocumentSize = null;
    public $showUploadModal = false;
    
    // Propiedades para asociar documentos a registros existentes
    public $selectedRecordType = ''; // license, medical_card, accident, violation, training, course, drug_test
    public $selectedRecordId = null; // ID del registro seleccionado
    public $documentType = ''; // tipo de documento (license_front, license_back, etc.)
    
    // Documentos por categoría
    public $licenseDocuments = [];
    public $medicalDocuments = [];
    public $recordDocuments = [];
    public $otherDocuments = [];

    public function mount($driverId)
    {
        $this->driverId = $driverId;
        $this->initializeChecklist(); // Primero inicializa el checklist con valores predeterminados
        $this->loadLastVerification(); // Luego carga los valores guardados en el checklist
        $this->loadDriverData(); // Finalmente carga los datos y actualiza los estados usando el checklist
        $this->loadGeneratedPdfs();
    }

    public function toggleChecklistItem($item)
    {
        if (isset($this->checklistItems[$item])) {
            // Just change the checked value directly - don't toggle since wire:model already did that
            $this->checklistItems[$item]['checked'] = !$this->checklistItems[$item]['checked'];
        }
    }

    // En el método initializeChecklist() de tu DriverRecruitmentReview.php
    public function initializeChecklist()
    {
        // Define the elements the recruiter should verify
        $this->checklistItems = [
            'general_info' => [
                'checked' => false,
                'label' => 'Complete and valid general information'
            ],
            'contact_info' => [
                'checked' => false,
                'label' => 'Verified contact information'
            ],
            'address_info' => [
                'checked' => false,
                'label' => 'Validated current address and history'
            ],
            'license_info' => [
                'checked' => false,
                'label' => 'Valid and current drivers license'
            ],
            'license_image' => [
                'checked' => false,
                'label' => 'Attached, legible license images'
            ],
            'medical_info' => [
                'checked' => false,
                'label' => 'Complete medical information'
            ],
            'medical_image' => [
                'checked' => false,
                'label' => 'Medical card attached and current'
            ],
            'experience_info' => [
                'checked' => false,
                'label' => 'Verified driving experience'
            ],
            // Nuevos elementos para training, traffic y accident
            'training_verified' => [
                'checked' => false,
                'label' => 'Training information verified (or N/A)'
            ],
            'traffic_verified' => [
                'checked' => false,
                'label' => 'Traffic violations verified (or N/A)'
            ],
            'accident_verified' => [
                'checked' => false,
                'label' => 'Accident record verified (or N/A)'
            ],
            'history_info' => [
                'checked' => false,
                'label' => 'Complete work history (10 years)'
            ],
            'criminal_check' => [
                'checked' => false,
                'label' => 'Criminal background check'
            ],
            'drug_test' => [
                'checked' => false,
                'label' => 'Drug test verification'
            ],
            'mvr_check' => [
                'checked' => false,
                'label' => 'MVR check completed'
            ],
            'policy_agreed' => [
                'checked' => false,
                'label' => 'Company policies agreed'
            ],
            'application_certification' => [
                'checked' => false,
                'label' => 'Application Certification'
            ],
            'documents_checked' => [
                'checked' => false,
                'label' => 'All documents reviewed and validated'
            ],
            'vehicle_info' => [
                'checked' => false,
                'label' => 'Vehicle information verified (if applicable)'
            ]
        ];
    }

    public function isChecklistComplete()
    {
        foreach ($this->checklistItems as $item) {
            if (!$item['checked']) {
                return false;
            }
        }
        return true;
    }

    public function loadDriverData()
    {
        // Cargar datos del conductor
        $this->driver = UserDriverDetail::with([
            'user',
            'carrier',
            'application.details',
            'licenses',
            'medicalQualification',
            'experiences',
            'trainingSchools',
            'trafficConvictions',
            'accidents',
            'fmcsrData',
            'workHistories',
            'unemploymentPeriods',
            'criminalHistory',
            'companyPolicy',
            'certification',
            'relatedEmployments' // Cargar empleos relacionados
        ])->findOrFail($this->driverId);
    
        // Procesar fechas
        if ($this->driver->date_of_birth && is_string($this->driver->date_of_birth)) {
            $this->driver->date_of_birth = Carbon::parse($this->driver->date_of_birth);
        }
        $this->processDateFields();
    
        $this->application = $this->driver->application;
    
        // Cargar datos de solicitud si existen
        if ($this->application) {
            $this->rejectionReason = $this->application->rejection_reason ?? '';
            $this->requestedDocuments = json_decode($this->application->requested_documents, true) ?: [];
            $this->additionalRequirements = $this->application->additional_requirements ?? '';
        }
    
        // Extraer los valores del checklist para pasarlos al servicio
        $checklistValues = [];
        foreach ($this->checklistItems as $key => $item) {
            $checklistValues[$key] = $item['checked'];
        }
    
        // Cargar estados de los pasos pasando los valores del checklist
        $stepService = new DriverStepService();
        $this->stepsStatus = $stepService->getStepsStatus($this->driver, $checklistValues);
        $this->completionPercentage = $stepService->calculateCompletionPercentage($this->driver);
    }

    /**
     * Procesa las fechas para asegurar que son objetos Carbon
     */
    protected function processDateFields()
    {
        // Procesar fechas en licencias
        if ($this->driver->licenses) {
            foreach ($this->driver->licenses as $license) {
                if (is_string($license->expiration_date)) {
                    $license->expiration_date = Carbon::parse($license->expiration_date);
                }
            }
        }

        // Procesar fechas en calificación médica
        if ($this->driver->medicalQualification) {
            $medical = $this->driver->medicalQualification;

            if (is_string($medical->medical_card_expiration_date)) {
                $medical->medical_card_expiration_date = Carbon::parse($medical->medical_card_expiration_date);
            }

            if ($medical->suspension_date && is_string($medical->suspension_date)) {
                $medical->suspension_date = Carbon::parse($medical->suspension_date);
            }

            if ($medical->termination_date && is_string($medical->termination_date)) {
                $medical->termination_date = Carbon::parse($medical->termination_date);
            }
        }

        // Procesar fechas en escuelas de capacitación
        if ($this->driver->trainingSchools) {
            foreach ($this->driver->trainingSchools as $school) {
                if (is_string($school->date_start)) {
                    $school->date_start = Carbon::parse($school->date_start);
                }
                if (is_string($school->date_end)) {
                    $school->date_end = Carbon::parse($school->date_end);
                }
            }
        }

        // Procesar fechas en infracciones de tráfico
        if ($this->driver->trafficConvictions) {
            foreach ($this->driver->trafficConvictions as $conviction) {
                if (is_string($conviction->conviction_date)) {
                    $conviction->conviction_date = Carbon::parse($conviction->conviction_date);
                }
            }
        }

        // Procesar fechas en accidentes
        if ($this->driver->accidents) {
            foreach ($this->driver->accidents as $accident) {
                if (is_string($accident->accident_date)) {
                    $accident->accident_date = Carbon::parse($accident->accident_date);
                }
            }
        }

        // Procesar fechas en historial de empleo
        if ($this->driver->workHistories) {
            foreach ($this->driver->workHistories as $history) {
                if (is_string($history->start_date)) {
                    $history->start_date = Carbon::parse($history->start_date);
                }
                if (is_string($history->end_date)) {
                    $history->end_date = Carbon::parse($history->end_date);
                }
            }
        }

        // Procesar fechas en periodos de desempleo
        if ($this->driver->unemploymentPeriods) {
            foreach ($this->driver->unemploymentPeriods as $period) {
                if (is_string($period->start_date)) {
                    $period->start_date = Carbon::parse($period->start_date);
                }
                if (is_string($period->end_date)) {
                    $period->end_date = Carbon::parse($period->end_date);
                }
            }
        }

        // Procesar fechas en empresas de empleo
        if ($this->driver->employmentCompanies) {
            foreach ($this->driver->employmentCompanies as $company) {
                if (is_string($company->employed_from)) {
                    $company->employed_from = Carbon::parse($company->employed_from);
                }
                if (is_string($company->employed_to)) {
                    $company->employed_to = Carbon::parse($company->employed_to);
                }
            }
        }

        // Procesar fechas en certificación
        if ($this->driver->certification && $this->driver->certification->signed_at && is_string($this->driver->certification->signed_at)) {
            $this->driver->certification->signed_at = Carbon::parse($this->driver->certification->signed_at);
        }

        // Procesar fecha de completado en la aplicación
        if ($this->application && $this->application->completed_at && is_string($this->application->completed_at)) {
            $this->application->completed_at = Carbon::parse($this->application->completed_at);
        }
    }

    /**
     * Cargar los datos del historial del conductor
     */
    protected function loadHistoryData()
    {
        $this->historyItems = [];
        $totalExperienceYears = 0;

        // Agregar experiencia laboral al historial
        if ($this->driver->workHistory) {
            foreach ($this->driver->workHistory as $work) {
                $dateStart = is_string($work->date_from) ? Carbon::parse($work->date_from) : $work->date_from;
                $dateEnd = is_string($work->date_to) ? Carbon::parse($work->date_to) : $work->date_to;
                
                // Calcular duración en años
                $durationYears = $dateStart->diffInDays($dateEnd) / 365.25;
                $totalExperienceYears += $durationYears;

                $this->historyItems[] = [
                    'type' => 'employment',
                    'date_start' => $dateStart,
                    'date_end' => $dateEnd,
                    'duration_years' => $durationYears,
                    'title' => $work->company_name,
                    'subtitle' => $work->position,
                    'details' => [
                        'address' => $work->address,
                        'city' => $work->city,
                        'state' => $work->state,
                        'zip' => $work->zip,
                        'phone' => $work->phone,
                        'reason_for_leaving' => $work->reason_for_leaving,
                        'subject_to_fmcsr' => $work->subject_to_fmcsr ? 'Yes' : 'No',
                        'subject_to_drug_testing' => $work->subject_to_drug_testing ? 'Yes' : 'No'
                    ]
                ];
            }
        }
        
        // Agregar empleos relacionados de la tabla driver_related_employments al historial
        if ($this->driver->relatedEmployments) {
            foreach ($this->driver->relatedEmployments as $relatedEmployment) {
                $dateStart = is_string($relatedEmployment->start_date) ? Carbon::parse($relatedEmployment->start_date) : $relatedEmployment->start_date;
                $dateEnd = is_string($relatedEmployment->end_date) ? Carbon::parse($relatedEmployment->end_date) : $relatedEmployment->end_date;
                
                // Calcular duración en años
                $durationYears = $dateStart->diffInDays($dateEnd) / 365.25;
                $totalExperienceYears += $durationYears;

                $this->historyItems[] = [
                    'type' => 'driver_related_employment',
                    'date_start' => $dateStart,
                    'date_end' => $dateEnd,
                    'duration_years' => $durationYears,
                    'title' => $relatedEmployment->position,
                    'subtitle' => 'Driver Related Employment',
                    'details' => [
                        'position' => $relatedEmployment->position,
                        'comments' => $relatedEmployment->comments
                    ]
                ];
            }
        }
        
        // Agregar períodos de desempleo al historial
        if ($this->driver->unemploymentPeriods) {
            foreach ($this->driver->unemploymentPeriods as $period) {
                $dateStart = is_string($period->date_from) ? Carbon::parse($period->date_from) : $period->date_from;
                $dateEnd = is_string($period->date_to) ? Carbon::parse($period->date_to) : $period->date_to;

                $this->historyItems[] = [
                    'type' => 'unemployment',
                    'date_start' => $dateStart,
                    'date_end' => $dateEnd,
                    'title' => 'Unemployment Period',
                    'subtitle' => $period->reason,
                    'details' => []
                ];
            }
        }

        // Ordenar historial por fecha de inicio (más reciente primero)
        usort($this->historyItems, function ($a, $b) {
            return $b['date_start']->timestamp <=> $a['date_start']->timestamp;
        });
        
        // Guardar los años totales de experiencia
        $this->totalExperienceYears = round($totalExperienceYears, 1);
    }

    /**
     * Calcular el tiempo de residencia en la dirección actual
     */
    protected function calculateTimeAtAddress($address)
    {
        if (!$address || !$address->date_from) {
            return 'N/A';
        }

        $dateFrom = is_string($address->date_from) ? Carbon::parse($address->date_from) : $address->date_from;
        $now = Carbon::now();
        $diff = $dateFrom->diffInMonths($now);
        
        $years = (int)floor($diff / 12);
        $months = (int)floor($diff % 12);
        
        return $years . ' year(s) ' . $months . ' month(s)';
    }
    
    /**
     * Carga la verificación más reciente del reclutador
     */
    protected function loadLastVerification()
    {
        if (!$this->driverId) return;
    
        $application = UserDriverDetail::find($this->driverId)->application;
        if (!$application) return;
    
        $verification = DriverRecruitmentVerification::where('driver_application_id', $application->id)
            ->latest('verified_at')
            ->first();
    
        if ($verification) {
            $this->savedVerification = $verification;
    
            // If there's a saved verification, use its values to initialize the checklist
            if (is_array($verification->verification_items)) {
                foreach ($verification->verification_items as $key => $value) {
                    if (isset($this->checklistItems[$key])) {
                        $this->checklistItems[$key]['checked'] = (bool)$value;
                    }
                }
            }
            
            $this->verificationNotes = $verification->notes;
        }
    }

    /**
     * Carga los documentos PDF generados para este conductor/solicitud usando Spatie Media Library
     */
    protected function loadGeneratedPdfs()
    {
        $this->generatedPdfs = [];

        if (!$this->driver || !$this->driver->id) {
            return;
        }
        
        // Cargar documentos de licencia
        $licenses = \App\Models\Admin\Driver\DriverLicense::where('user_driver_detail_id', $this->driver->id)->get();
        foreach ($licenses as $license) {
            $this->loadMediaFromModel($license, 'license');
        }
        
        // Cargar documentos de calificación médica
        $medicalCards = \App\Models\Admin\Driver\DriverMedicalQualification::where('user_driver_detail_id', $this->driver->id)->get();
        foreach ($medicalCards as $medicalCard) {
            $this->loadMediaFromModel($medicalCard, 'medical');
        }
        
        // Cargar documentos de accidentes
        $accidents = \App\Models\Admin\Driver\DriverAccident::where('user_driver_detail_id', $this->driver->id)->get();
        foreach ($accidents as $accident) {
            $this->loadMediaFromModel($accident, 'record', 'accident');
        }
        
        // Cargar documentos de infracciones de tráfico
        $violations = \App\Models\Admin\Driver\DriverTrafficConviction::where('user_driver_detail_id', $this->driver->id)->get();
        foreach ($violations as $violation) {
            $this->loadMediaFromModel($violation, 'record', 'violation');
        }
        
        // Cargar documentos de escuelas de entrenamiento
        $trainings = \App\Models\Admin\Driver\DriverTrainingSchool::where('user_driver_detail_id', $this->driver->id)->get();
        foreach ($trainings as $training) {
            $this->loadMediaFromModel($training, 'record', 'training');
        }
        
        // Cargar documentos de cursos
        $courses = \App\Models\Admin\Driver\DriverCourse::where('user_driver_detail_id', $this->driver->id)->get();
        foreach ($courses as $course) {
            $this->loadMediaFromModel($course, 'record', 'course');
        }
        
        // Cargar documentos de inspecciones
        $inspections = \App\Models\Admin\Driver\DriverInspection::where('user_driver_detail_id', $this->driver->id)->get();
        foreach ($inspections as $inspection) {
            $this->loadMediaFromModel($inspection, 'record', 'inspection');
        }
        
        // Cargar documentos de pruebas de drogas
        $drugTests = \App\Models\Admin\Driver\DriverTesting::where('user_driver_detail_id', $this->driver->id)->get();
        foreach ($drugTests as $drugTest) {
            $this->loadMediaFromModel($drugTest, 'record', 'drug_test');
        }
        
        // SOPORTE PARA CÓDIGO LEGADO - Cargar documentos desde el sistema de archivos
        // Solo se utiliza mientras se completa la migración a Spatie Media Library
        $this->loadLegacyDocuments();
    }
    
    /**
     * Carga documentos usando el método antiguo del sistema de archivos
     * Este método se eliminará una vez completada la migración a Spatie Media Library
     */
    private function loadLegacyDocuments()
    {
        if (!$this->driver || !$this->driver->id) {
            return;
        }
        
        // Definir rutas para los documentos
        $driverId = $this->driver->id;
        $vehicleVerificationsPath = "driver/{$driverId}/vehicle-verifications/";
        $vehicleVerificationsFullPath = storage_path("app/public/{$vehicleVerificationsPath}");
        
        // Verificar si el directorio de verificaciones de vehículos existe
        if (file_exists($vehicleVerificationsFullPath)) {
            // Buscar los archivos de consentimiento de terceros (con formato de nombre que incluye timestamp)
            $consentFiles = glob("{$vehicleVerificationsFullPath}third_party_consent_*.pdf");
            
            // Si no hay archivos con el nuevo formato, buscar con el nombre antiguo
            if (empty($consentFiles)) {
                $oldConsentFile = "{$vehicleVerificationsFullPath}consentimiento_propietario_third_party.pdf";
                if (file_exists($oldConsentFile)) {
                    $consentFiles[] = $oldConsentFile;
                }
            }
            
            // Tomar el archivo más reciente (si existe)
            if (!empty($consentFiles)) {
                // Ordenar por fecha de modificación (más reciente primero)
                usort($consentFiles, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                
                $latestConsentFile = $consentFiles[0];
                $consentFileName = basename($latestConsentFile);
                $fileSize = $this->formatFileSize(filesize($latestConsentFile));
                $fileDate = $this->formatFileDate(filemtime($latestConsentFile));
                
                $this->generatedPdfs['third_party_consent'] = [
                    'name' => 'Third Party Consent',
                    'url' => asset("storage/{$vehicleVerificationsPath}{$consentFileName}") . "?v=" . time(),
                    'size' => $fileSize,
                    'date' => $fileDate,
                    'category' => 'other',
                    'document_type' => 'third_party_consent'
                ];
            }
            
            // Buscar los archivos de lease agreement para third party (con formato de nombre que incluye timestamp)
            $leaseFiles = glob("{$vehicleVerificationsFullPath}lease_agreement_third_party_*.pdf");
            
            // Si no hay archivos con el nuevo formato, buscar con el nombre antiguo
            if (empty($leaseFiles)) {
                $oldLeaseFile = "{$vehicleVerificationsFullPath}lease_agreement_third_party.pdf";
                if (file_exists($oldLeaseFile)) {
                    $leaseFiles[] = $oldLeaseFile;
                }
            }
            
            // Tomar el archivo más reciente (si existe)
            if (!empty($leaseFiles)) {
                // Ordenar por fecha de modificación (más reciente primero)
                usort($leaseFiles, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                
                $latestLeaseFile = $leaseFiles[0];
                $leaseFileName = basename($latestLeaseFile);
                $fileSize = $this->formatFileSize(filesize($latestLeaseFile));
                $fileDate = $this->formatFileDate(filemtime($latestLeaseFile));
                
                $this->generatedPdfs['lease_agreement_third_party'] = [
                    'name' => 'Lease Agreement (Third Party)',
                    'url' => asset("storage/{$vehicleVerificationsPath}{$leaseFileName}") . "?v=" . time(),
                    'size' => $fileSize,
                    'date' => $fileDate,
                    'category' => 'other',
                    'document_type' => 'lease_agreement'
                ];
            }
            
            // Buscar los archivos de lease agreement para owner operators (con formato de nombre que incluye timestamp)
            $ownerLeaseFiles = glob("{$vehicleVerificationsFullPath}lease_agreement_owner_operator_*.pdf");
            
            // Si no hay archivos con el nuevo formato, buscar con el nombre antiguo
            if (empty($ownerLeaseFiles)) {
                $oldOwnerLeaseFile = "{$vehicleVerificationsFullPath}lease_agreement_owner_operator.pdf";
                if (file_exists($oldOwnerLeaseFile)) {
                    $ownerLeaseFiles[] = $oldOwnerLeaseFile;
                }
            }
            
            // Tomar el archivo más reciente (si existe)
            if (!empty($ownerLeaseFiles)) {
                // Ordenar por fecha de modificación (más reciente primero)
                usort($ownerLeaseFiles, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                
                $latestOwnerLeaseFile = $ownerLeaseFiles[0];
                $ownerLeaseFileName = basename($latestOwnerLeaseFile);
                $fileSize = $this->formatFileSize(filesize($latestOwnerLeaseFile));
                $fileDate = $this->formatFileDate(filemtime($latestOwnerLeaseFile));
                
                $this->generatedPdfs['lease_agreement_owner'] = [
                    'name' => 'Lease Agreement (Owner Operator)',
                    'url' => asset("storage/{$vehicleVerificationsPath}{$ownerLeaseFileName}") . "?v=" . time(),
                    'size' => $fileSize,
                    'date' => $fileDate,
                    'category' => 'other',
                    'document_type' => 'lease_agreement'
                ];
            }
        }
    }
    
    /**
     * Formatea el tamaño del archivo en una forma legible
     *
     * @param int $bytes
     * @return string
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Formatea la fecha de modificación del archivo
     *
     * @param int $timestamp
     * @return string
     */
    private function formatFileDate($timestamp)
    {
        return date('d/m/Y H:i', $timestamp);
    }
    
    /**
     * Método auxiliar para cargar los medios (documentos) de un modelo
     * 
     * @param mixed $model El modelo del que cargar los medios
     * @param string $category Categoría del documento (license, medical, record, other)
     * @param string|null $recordType Tipo de registro (solo para category=record)
     * @return void
     */
    protected function loadMediaFromModel($model, $category, $recordType = null)
    {
        if (!method_exists($model, 'getMedia')) {
            return;
        }
        
        // Obtener todas las colecciones de medios del modelo
        $allMedia = $model->getMedia();
        
        foreach ($allMedia as $media) {
            $fileSize = $this->formatFileSize($media->size);
            $fileDate = $this->formatFileDate(strtotime($media->created_at));
            $documentType = $media->getCustomProperty('document_type') ?? $media->collection_name;
            
            $uniqueKey = \Illuminate\Support\Str::random(10) . '_' . $media->id;
            $this->generatedPdfs[$uniqueKey] = [
                'name' => $media->name,
                'url' => $media->getUrl(),
                'size' => $fileSize,
                'date' => $fileDate,
                'category' => $category,
                'record_type' => $recordType,
                'record_id' => $model->id,
                'document_type' => $documentType,
                'id' => $media->id
            ];
        }
    }
    
    /**
     * Abre el modal para subir un documento
     */
    public function openUploadModal($category)
    {
        $this->resetDocumentUpload();
        $this->documentCategory = $category;
        
        // Cada categoría ahora requiere un selector específico primero
        if ($category == 'license') {
            $this->selectedRecordType = 'license';
        } elseif ($category == 'medical') {
            $this->selectedRecordType = 'medical_card';
        } elseif ($category == 'record') {
            // No preseleccionamos el tipo de registro para records - el usuario debe elegir
        } elseif ($category == 'other') {
            // Para documentos generales no necesitamos asociarlo a un registro
        }
        
        $this->showUploadModal = true;
    }
    
    /**
     * Cierra el modal de subir documentos
     */
    public function closeUploadModal()
    {
        $this->resetDocumentUpload();
        $this->showUploadModal = false;
    }
    
    /**
     * Obtiene la lista de accidentes del conductor para el selector
     */
    public function getAccidentsProperty()
    {
        if (!$this->driver || !$this->driver->id) {
            return [];
        }
        
        return DB::table('driver_accidents')
            ->where('user_driver_detail_id', $this->driver->id)
            ->select('id', 'date', 'description')
            ->orderBy('date', 'desc')
            ->get();
    }
    
    /**
     * Obtiene la lista de violaciones/infracciones del conductor para el selector
     */
    public function getViolationsProperty()
    {
        if (!$this->driver || !$this->driver->id) {
            return [];
        }
        
        return DB::table('driver_traffic_convictions')
            ->where('user_driver_detail_id', $this->driver->id)
            ->select('id', 'date', 'description')
            ->orderBy('date', 'desc')
            ->get();
    }
    
    /**
     * Obtiene la lista de licencias del conductor para el selector
     */
    public function getDriverLicensesProperty()
    {
        if (!$this->driver || !$this->driver->id) {
            return [];
        }
        
        return DB::table('driver_licenses')
            ->where('user_driver_detail_id', $this->driver->id)
            ->select('id', 'license_number', 'license_class', 'expiration_date')
            ->orderBy('expiration_date', 'desc')
            ->get();
    }
    
    /**
     * Obtiene la calificación médica del conductor para el selector
     */
    public function getMedicalCardsProperty()
    {
        if (!$this->driver || !$this->driver->id) {
            return [];
        }
        
        // La tabla es driver_medical_qualifications, no driver_medical_cards
        $medicalQualification = DB::table('driver_medical_qualifications')
            ->where('user_driver_detail_id', $this->driver->id)
            ->select('id', 'medical_examiner_name as card_number', 'medical_card_expiration_date as expiration_date')
            ->get();
            
        return $medicalQualification;
    }
    
    /**
     * Obtiene la lista de entrenamientos del conductor para el selector
     */
    public function getTrainingsProperty()
    {
        if (!$this->driver || !$this->driver->id) {
            return [];
        }
        
        return DB::table('driver_training_schools')
            ->where('user_driver_detail_id', $this->driver->id)
            ->select('id', 'date_from as date', 'school_name as description')
            ->orderBy('date', 'desc')
            ->get();
    }
    
    /**
     * Obtiene la lista de cursos del conductor para el selector
     */
    public function getCoursesProperty()
    {
        if (!$this->driver || !$this->driver->id) {
            return [];
        }
        
        return DB::table('driver_courses')
            ->where('user_driver_detail_id', $this->driver->id)
            ->select('id', DB::raw('created_at as date'), 'course_name as description')
            ->orderBy('date', 'desc')
            ->get();
    }
    
    /**
     * Obtiene la lista de inspecciones del conductor para el selector
     */
    public function getInspectionsProperty()
    {
        if (!$this->driver || !$this->driver->id) {
            return [];
        }
        
        return DB::table('driver_inspections')
            ->where('user_driver_detail_id', $this->driver->id)
            ->select('id', 'inspection_date as date', 'description')
            ->orderBy('date', 'desc')
            ->get();
    }
    
    /**
     * Obtiene la lista de pruebas de drogas del conductor para el selector
     */
    public function getDrugTestsProperty()
    {
        if (!$this->driver || !$this->driver->id) {
            return [];
        }
        
        // Pruebas de drogas desde DriverTesting
        $driverTestings = DB::table('driver_testings')
            ->where('user_driver_detail_id', $this->driver->id)
            ->where('test_type', 'drug_test')
            ->select('id', 'test_date as date', 'test_type')
            ->get();
            
        return $driverTestings;
    }
    
    /**
     * Resetea el estado del formulario de carga de documentos
     */
    protected function resetDocumentUpload()
    {
        $this->documentCategory = '';
        $this->documentDescription = '';
        $this->documentFile = null;
        $this->tempDocumentPath = null;
        $this->tempDocumentName = null;
        $this->tempDocumentSize = null;
        $this->selectedRecordType = '';
        $this->selectedRecordId = null;
        $this->documentType = '';
    }
    
    /**
     * Guarda un documento en Spatie Media Library y lo asocia con el registro correcto
     * 
     * @return void
     */
    public function saveDocument()
    {
        // Validación básica
        if (empty($this->documentFile) || empty($this->documentCategory)) {
            session()->flash('error', 'Falta el archivo o la categoría');
            return;
        }
        
        // Validar que se haya seleccionado un registro existente (excepto para documentos generales)
        if ($this->documentCategory !== 'other' && empty($this->selectedRecordId)) {
            session()->flash('error', 'Debe seleccionar un registro existente primero');
            return;
        }
        
        // Validar que se haya seleccionado un tipo de documento
        if (empty($this->documentType)) {
            session()->flash('error', 'Debe seleccionar un tipo de documento');
            return;
        }
        
        // Construir reglas de validación
        $rules = [
            'documentFile' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png', // 10MB máx, permitir PDF e imágenes
            'documentDescription' => 'required|string|min:3|max:100',
            'documentCategory' => 'required|string|in:license,medical,record,other',
            'documentType' => 'required|string'
        ];
        
        if ($this->documentCategory !== 'other') {
            $rules['selectedRecordId'] = 'required|integer|min:1';
            
            if ($this->documentCategory === 'record') {
                $rules['selectedRecordType'] = 'required|string|in:accident,violation,training,course,inspection,drug_test,testing_drugs';
            }
        }
        
        $messages = [
            'documentFile.required' => 'Debe seleccionar un archivo',
            'documentFile.file' => 'El archivo no es válido',
            'documentFile.max' => 'El archivo no debe exceder los 10MB',
            'documentFile.mimes' => 'El archivo debe ser PDF, JPG o PNG',
            'documentDescription.required' => 'La descripción es obligatoria',
            'documentDescription.min' => 'La descripción debe tener al menos 3 caracteres',
            'documentCategory.required' => 'Debe seleccionar una categoría',
            'documentCategory.in' => 'La categoría seleccionada no es válida',
            'documentType.required' => 'Debe seleccionar un tipo de documento',
            'selectedRecordId.required' => 'Debe seleccionar un registro existente',
            'selectedRecordId.integer' => 'El registro seleccionado no es válido',
            'selectedRecordType.required' => 'Debe seleccionar un tipo de registro',
            'selectedRecordType.in' => 'El tipo de registro seleccionado no es válido'
        ];
        
        // Validar el archivo
        $this->validate($rules, $messages);
        
        try {
            // Obtener el modelo correspondiente según la categoría
            $model = null;
            $mediaCollection = null;
            
            switch ($this->documentCategory) {
                case 'license':
                    $model = \App\Models\Admin\Driver\DriverLicense::findOrFail($this->selectedRecordId);
                    $mediaCollection = $this->documentType;
                    break;
                case 'medical':
                    $model = \App\Models\Admin\Driver\DriverMedicalQualification::findOrFail($this->selectedRecordId);
                    $mediaCollection = 'medical_card';
                    break;
                case 'record':
                    switch ($this->selectedRecordType) {
                        case 'accident':
                            $model = \App\Models\Admin\Driver\DriverAccident::findOrFail($this->selectedRecordId);
                            break;
                        case 'violation':
                            $model = \App\Models\Admin\Driver\DriverTrafficConviction::findOrFail($this->selectedRecordId);
                            break;
                        case 'training':
                            $model = \App\Models\Admin\Driver\DriverTrainingSchool::findOrFail($this->selectedRecordId);
                            break;
                        case 'course':
                            $model = \App\Models\Admin\Driver\DriverCourse::findOrFail($this->selectedRecordId);
                            break;
                        case 'inspection':
                            $model = \App\Models\Admin\Driver\DriverInspection::findOrFail($this->selectedRecordId);
                            break;
                        case 'drug_test':
                        case 'testing_drugs':
                            $model = \App\Models\Admin\Driver\DriverTesting::findOrFail($this->selectedRecordId);
                            break;
                        default:
                            session()->flash('error', 'Tipo de registro no válido');
                            return;
                    }
                    $mediaCollection = $this->selectedRecordType;
                    break;
                case 'other':
                    // Para documentos generales, asociar directamente al driver
                    $model = $this->driver;
                    $mediaCollection = 'other_documents';
                    break;
                default:
                    session()->flash('error', 'Categoría no válida');
                    return;
            }
            
            if (!$model) {
                session()->flash('error', 'No se pudo encontrar el registro seleccionado');
                return;
            }
            
            // Asegurarse que el modelo utiliza el trait HasMedia
            if (!method_exists($model, 'addMedia')) {
                session()->flash('error', 'Este tipo de registro no soporta la carga de documentos');
                return;
            }
            
            // Agregar el archivo al modelo usando Spatie Media Library
            $media = $model->addMedia($this->documentFile->getRealPath())
                ->usingName($this->documentDescription)
                ->withCustomProperties([
                    'description' => $this->documentDescription,
                    'document_type' => $this->documentType,
                    'category' => $this->documentCategory,
                    'uploaded_by' => auth()->id(),
                    'record_type' => $this->selectedRecordType ?? null
                ])
                ->toMediaCollection($mediaCollection);
            
            // Actualizar el estado del registro para indicar que tiene documentos
            if (Schema::hasColumn($model->getTable(), 'has_documents')) {
                $model->update(['has_documents' => true]);
            }
            
            // Limpiar el formulario
            $this->resetDocumentUpload();
            $this->showUploadModal = false;
            
            // Recargar documentos
            $this->loadGeneratedPdfs();
            
            // Mostrar mensaje de éxito
            session()->flash('message', 'Documento guardado correctamente');
            
            // Emitir evento para actualizar la vista
            $this->dispatch('documentUploaded');
            
        } catch (\Exception $e) {
            // Registrar el error
            \Illuminate\Support\Facades\Log::error('Error al guardar documento: ' . $e->getMessage());
            
            // Mostrar mensaje de error
            session()->flash('error', 'Error al guardar el documento: ' . $e->getMessage());
        }
    }
    
    /**
     * Maneja la carga temporal de un documento
     */
    public function documentUploaded($fileData)
    {
        $this->tempDocumentPath = $fileData['tempPath'] ?? null;
        $this->tempDocumentName = $fileData['originalName'] ?? null;
        $this->tempDocumentSize = $fileData['size'] ?? null;
        
        // Emitir evento para el frontend
        $this->dispatch('fileUploaded', [
            'tempPath' => $this->tempDocumentPath,
            'originalName' => $this->tempDocumentName,
            'size' => $this->tempDocumentSize
        ]);
    }
    
    /**
     * Guarda el documento subido
     */
    public function saveDocument()
    {
        // Validación básica
        if (empty($this->documentFile) || empty($this->documentCategory)) {
            session()->flash('error', 'Falta el archivo o la categoría');
