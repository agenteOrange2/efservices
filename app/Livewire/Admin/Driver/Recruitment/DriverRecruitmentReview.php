<?php

namespace App\Livewire\Admin\Driver\Recruitment;


use ZipArchive;
use Carbon\Carbon;
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
use App\Models\Admin\Driver\Training;
use App\Models\Admin\Driver\DriverTraining;
use Livewire\WithFileUploads;

class DriverRecruitmentReview extends Component
{
    use WithFileUploads;
    
    // Define los eventos a escuchar
    protected $listeners = [
        'documentUploaded' => 'loadDriverData',
        'refreshDriverData' => 'loadDriverData',
        'reject-application' => 'rejectApplication',
        'training-school-updated' => 'handleTrainingSchoolUpdated',
        'fileUploaded' => 'handleFileUploaded',
        'licenseImageUploaded' => 'handleLicenseImageUploaded',
        'refreshTrainings' => '$refresh',
    ];
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
    
    // Propiedades para entrenamientos
    public $availableTrainings = [];
    public $selectedTrainingId = null;
    public $trainingDueDate = null;
    public $showTrainingModal = false;

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
    
    // Propiedades para registros de manejo y criminal
    public $drivingRecordFiles = [];
    public $criminalRecordFiles = [];
    
    // Propiedades específicas para licencias
    public $licenseImageType = ''; // license_front, license_back
    public $selectedLicenseId = null;
    
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
        $this->loadAvailableTrainings();
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
            // Nuevos elementos para records de manejo y criminal
            'driving_record' => [
                'checked' => false,
                'label' => 'Driving record uploaded and verified'
            ],
            'criminal_record' => [
                'checked' => false,
                'label' => 'Criminal record uploaded and verified'
            ],
            'clearing_house' => [
                'checked' => false,
                'label' => 'Clearing House uploaded and verified'
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
    
    /**
     * Actualiza el estado de la aplicación basado en el checklist
     */
    public function updateApplicationStatus()
    {
        if (!$this->application) {
            session()->flash('error', 'No application found for this driver');
            return;
        }
        
        // Si todos los elementos del checklist están completos, mostrar botón de aprobación
        if ($this->isChecklistComplete() && ($this->application->status === 'pending' || $this->application->status === 'draft')) {
            // Actualizar el estado de la aplicación a 'approved' y establecer la fecha de completado
            $this->application->status = 'approved';
            $this->application->completed_at = now();
            $this->application->save();
            
            // Registrar la verificación
            DriverRecruitmentVerification::updateOrCreate(
                ['driver_application_id' => $this->application->id],
                [
                    'verified_by_user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'verification_items' => $this->checklistItems,
                    'notes' => $this->verificationNotes
                ]
            );
            
        
            // Mostrar mensaje de éxito
            session()->flash('message', 'La solicitud ha sido aprobada exitosamente.');
            
            // Redireccionar a la lista de conductores
            return redirect()->route('admin.driver-recruitment.index');
        } else {
            session()->flash('error', 'Please complete all checklist items before approving');
        }
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
            'relatedEmployments', // Cargar empleos relacionados
            'courses',            // Cursos de capacitación
            'testings',           // Pruebas de drogas
            'inspections',        // Inspecciones
            'media'               // Precargar medios (importante para las imágenes)
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
        
        // Solo recalcular completion_percentage si la aplicación no está aprobada
        // Si está aprobada, mantener el 100% establecido en approveApplication
        if ($this->application && $this->application->status === DriverApplication::STATUS_APPROVED) {
            $this->completionPercentage = 100;
        } else {
            $this->completionPercentage = $stepService->calculateCompletionPercentage($this->driver);
        }
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
        
        // Cargar el PDF de la aplicación completa si existe
        if ($this->driver->application) {
            $applicationMedia = $this->driver->application->getMedia('application_pdf');
            foreach ($applicationMedia as $media) {
                $fileSize = $this->formatFileSize($media->size);
                $fileDate = $this->formatFileDate(strtotime($media->created_at));
                
                $this->generatedPdfs['complete_application'] = [
                    'name' => 'Complete Application',
                    'url' => $media->getUrl(),
                    'size' => $fileSize,
                    'date' => $fileDate,
                    'category' => 'application',
                    'document_type' => 'complete_application',
                    'id' => $media->id
                ];
            }
            
            // Si no hay media pero existe el archivo en storage público
            if (empty($applicationMedia) && $this->driver->application->pdf_path) {
                $filePath = storage_path('app/public/' . $this->driver->application->pdf_path);
                if (file_exists($filePath)) {
                    $fileSize = $this->formatFileSize(filesize($filePath));
                    $fileDate = $this->formatFileDate(filemtime($filePath));
                    
                    $this->generatedPdfs['complete_application'] = [
                        'name' => 'Complete Application',
                        'url' => asset('storage/' . $this->driver->application->pdf_path) . '?v=' . time(),
                        'size' => $fileSize,
                        'date' => $fileDate,
                        'category' => 'application',
                        'document_type' => 'complete_application'
                    ];
                }
            }
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
        
        // Cargar los PDFs individuales de la aplicación
        $applicationPdfsPath = "driver/{$driverId}/driver_applications/";
        $applicationPdfsFullPath = storage_path("app/public/{$applicationPdfsPath}");
        
        // Verificar si el directorio de aplicaciones existe
        if (file_exists($applicationPdfsFullPath)) {
            // Lista de archivos PDF esperados
            $expectedPdfs = [
                'general.pdf' => 'General Information',
                'address.pdf' => 'Address Information',
                'application.pdf' => 'Application Details',
                'licenses.pdf' => 'Licenses Information',
                'medical.pdf' => 'Medical Qualification',
                'training.pdf' => 'Training Information',
                'traffic_violations.pdf' => 'Traffic Violations',
                'accidents.pdf' => 'Accidents Record',
                'fmcsr_requirements.pdf' => 'FMCSR Requirements',
                'employment_history.pdf' => 'Employment History',
                'certification.pdf' => 'Certification',
            ];
            
            // Cargar cada PDF si existe
            foreach ($expectedPdfs as $filename => $title) {
                $pdfPath = $applicationPdfsFullPath . $filename;
                if (file_exists($pdfPath)) {
                    $fileSize = $this->formatFileSize(filesize($pdfPath));
                    $fileDate = $this->formatFileDate(filemtime($pdfPath));
                    
                    $key = 'application_' . str_replace('.pdf', '', $filename);
                    $this->generatedPdfs[$key] = [
                        'name' => $title,
                        'url' => asset("storage/{$applicationPdfsPath}{$filename}") . "?v=" . time(),
                        'size' => $fileSize,
                        'date' => $fileDate,
                        'category' => 'application',
                        'document_type' => 'application_section'
                    ];
                }
            }
        }
        
        // Definir rutas para los documentos de verificación de vehículos
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
        
        // Reset license image properties
        $this->selectedLicenseId = null;
        $this->licenseImageType = '';
        
        // Emitir evento para cerrar el modal desde Alpine.js
        $this->dispatch('closeUploadModal');
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
                    'uploaded_by' => \Illuminate\Support\Facades\Auth::id(),
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
     * Método auxiliar para el procesamiento de documentos
     * @internal Este método reemplaza la duplicación de saveDocument()
     */
    private function processDocument()
    {
        // Este método se ha renombrado porque duplicaba saveDocument()
        // Usar el método saveDocument() original (línea ~910) en su lugar
        // Esto evita el error: Cannot redeclare App\Livewire\Admin\Driver\Recruitment\DriverRecruitmentReview::saveDocument()
        
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
                    // Buscar la licencia
                    $model = \App\Models\Admin\Driver\DriverLicense::findOrFail($this->selectedRecordId);
                    $mediaCollection = $this->documentType; // license_front, license_back, etc.
                    break;
                    
                case 'medical':
                    // Buscar la calificación médica
                    $model = \App\Models\Admin\Driver\DriverMedicalQualification::findOrFail($this->selectedRecordId);
                    $mediaCollection = 'medical_card'; // Definido en DriverMedicalQualification
                    break;
                    
                case 'record':
                    // Buscar el registro según su tipo
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
                    }
                    $mediaCollection = $this->documentType; // report, certificate, form, etc.
                    break;
                    
                case 'other':
                    // Para documentos generales, adjuntar directamente al driver
                    $model = $this->driver;
                    $mediaCollection = 'other_documents';
                    break;
            }
            
            if (!$model) {
                throw new \Exception('No se pudo encontrar el registro seleccionado');
            }
            
            // Verificar si el modelo implementa HasMedia
            if (!method_exists($model, 'addMedia')) {
                throw new \Exception('El registro seleccionado no soporta archivos adjuntos');
            }
            
            // Guardar el archivo temporal en el servidor
            $tempPath = $this->documentFile->store('temp', 'public');
            $fullTempPath = storage_path('app/public/' . $tempPath);
            
            // Obtener información del archivo para la vista previa
            $this->tempDocumentName = $this->documentFile->getClientOriginalName();
            $this->tempDocumentSize = $this->documentFile->getSize();
            
            // Añadir el archivo a la colección de medios del modelo usando Spatie Media Library
            $media = $model->addMedia($fullTempPath)
                ->usingName($this->documentDescription)
                ->withCustomProperties([
                    'description' => $this->documentDescription,
                    'document_type' => $this->documentType,
                    'uploaded_by' => \Illuminate\Support\Facades\Auth::id(),
                    'category' => $this->documentCategory,
                    'record_type' => $this->documentCategory === 'record' ? $this->selectedRecordType : null,
                ])
                ->toMediaCollection($mediaCollection);
            
            // Actualizar el modelo para indicar que tiene documentos
            if (method_exists($model, 'update')) {
                $model->update(['has_documents' => true]);
            }
            
            // Actualizar la lista de documentos generados
            $fileSize = $this->formatFileSize($this->documentFile->getSize());
            $fileDate = $this->formatFileDate(now()->timestamp);
            
            $mediaId = isset($media) && $media ? $media->id : 0;
            $uniqueKey = \Illuminate\Support\Str::random(10) . '_' . $mediaId;
            $this->generatedPdfs[$uniqueKey] = [
                'name' => $this->documentDescription,
                'url' => isset($media) && $media ? $media->getUrl() : '',
                'size' => $fileSize,
                'date' => $fileDate,
                'category' => $this->documentCategory,
                'record_type' => $this->documentCategory === 'record' ? $this->selectedRecordType : null,
                'record_id' => $this->selectedRecordId,
                'document_type' => $this->documentType,
                'id' => $mediaId
            ];
            
            // Si es un documento médico, actualizar información médica
            if ($this->documentCategory === 'medical') {
                $this->loadDriverData(); // Recargar datos del conductor
                $this->dispatch('medicalDocumentUpdated'); // Emitir evento para actualizar UI
            }
            
            session()->flash('message', 'Documento guardado correctamente');
            $this->closeUploadModal();
            $this->loadGeneratedPdfs(); // Recargar lista de documentos
        }
        catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al guardar documento', [
                'driver_id' => $this->driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Error al guardar documento: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualiza el registro seleccionado para indicar que tiene documentos asociados
     */
    protected function updateRecordWithDocumentInfo()
    {
        if (empty($this->selectedRecordId)) {
            return;
        }
        
        $table = null;
        $updateData = ['has_documents' => true, 'updated_at' => now()];
        
        switch ($this->documentCategory) {
            case 'license':
                $table = 'driver_licenses';
                break;
            case 'medical':
                $table = 'driver_medical_cards';
                break;
            case 'record':
                switch ($this->selectedRecordType) {
                    case 'accident':
                        $table = 'driver_accidents';
                        break;
                    case 'violation':
                        $table = 'driver_traffic_convictions';
                        break;
                    case 'training':
                        $table = 'driver_trainings';
                        break;
                    case 'course':
                        $table = 'driver_courses';
                        break;
                    case 'inspection':
                        $table = 'driver_inspections';
                        break;
                    case 'drug_test':
                        $table = 'driver_drug_tests';
                        break;
                    case 'testing_drugs':
                        $table = 'driver_testings';
                        break;
                }
                break;
        }
        
        if ($table) {
            DB::table($table)
                ->where('id', $this->selectedRecordId)
                ->update($updateData);
        }
    }
    
    /**
     * Registra el documento en la base de datos según su categoría
     */
    protected function registerDocumentInDatabase($filePath, $fileName)
    {
        // ID del documento a devolver
        $documentId = null;
        
        // Crear un registro base del documento
        $documentData = [
            'driver_id' => $this->driver->id,
            'application_id' => isset($this->application) && $this->application ? $this->application->id : null,
            'category' => $this->documentCategory,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'description' => $this->documentDescription,
            'document_type' => $this->documentType,
            'uploaded_by' => \Illuminate\Support\Facades\Auth::id(),
            'record_type' => $this->documentCategory === 'record' ? $this->selectedRecordType : null,
        ];
        
        // Obtener el modelo según la categoría y tipo de registro
        $model = null;
        $mediaCollection = 'documents';
        
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
                }
                $mediaCollection = $this->documentType;
                break;
            default:
                $model = $this->driver;
                $mediaCollection = 'other_documents';
                break;
        }
        
        if (!$model) {
            return null;
        }
        
        // Agregar el archivo a la colección de medios del modelo
        $media = $model->addMedia($filePath)
            ->usingName($fileName)
            ->withCustomProperties($documentData)
            ->toMediaCollection($mediaCollection);
            
        // Actualizar el modelo para indicar que tiene documentos
        if (method_exists($model, 'update')) {
            $model->update(['has_documents' => true]);
        }
            
        // Actualizar la lista de documentos generados
        if (isset($media) && $media) {
            $fileSize = $this->formatFileSize($this->documentFile->getSize());
            $fileDate = $this->formatFileDate(now()->timestamp);
            
            $mediaId = $media->id ?? 0;
            $uniqueKey = Str::random(10) . '_' . $mediaId;
            $this->generatedPdfs[$uniqueKey] = [
                'name' => $this->documentDescription,
                'url' => $media->getUrl(),
                'size' => $fileSize,
                'date' => $fileDate,
                'category' => $this->documentCategory,
                'record_type' => $this->documentCategory === 'record' ? $this->selectedRecordType : null,
                'record_id' => $this->selectedRecordId,
                'document_type' => $this->documentType,
                'id' => $mediaId
            ];
            
            // Si es un documento médico, actualizar información médica
            if ($this->documentCategory === 'medical') {
                $this->loadDriverData(); // Recargar datos del conductor
                $this->dispatch('medicalDocumentUpdated'); // Emitir evento para actualizar UI
            }
        }
        
        // Mostrar mensaje de éxito y limpiar el formulario
        session()->flash('message', 'Documento guardado correctamente');
        $this->closeUploadModal();
        $this->loadGeneratedPdfs(); // Recargar lista de documentos
        
        return $media;
    }  // Fin del método

/**
 * Maneja errores durante el proceso de guardar documentos
 * 
 * @param \Exception $e La excepción capturada
 * @return void
 */
private function handleDocumentError(\Exception $e)
{
    // Registrar el error en los logs
    \Illuminate\Support\Facades\Log::error('Error al guardar documento', [
        'driver_id' => $this->driver->id ?? 'unknown',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
            
    session()->flash('error', 'Error al guardar documento: ' . $e->getMessage());
}

/**
 * Carga los documentos PDF generados para este conductor/solicitud usando Spatie Media Library
 */
/**
 * Método auxiliar para el procesamiento de documentos (reemplaza loadGeneratedPdfs duplicado)
 * @internal Este método se ha renombrado para evitar duplicación
 */
private function loadAllDocuments()
{
    // Este método se ha renombrado porque duplicaba loadGeneratedPdfs
    if (!$this->driver || !$this->driver->id) {
        return;
    }
            
    $this->generatedPdfs = [];
    $collections = ['license_front', 'license_back', 'medical_card', 'other_documents', 'report', 'certificate', 'form'];
            
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
            
    // Cargar documentos de violaciones
    $violations = \App\Models\Admin\Driver\DriverTrafficConviction::where('user_driver_detail_id', $this->driver->id)->get();
    foreach ($violations as $violation) {
        $this->loadMediaFromModel($violation, 'record', 'violation');
    }
            
    // Cargar documentos de entrenamientos
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
    $drugTests = \App\Models\Admin\Driver\DriverTesting::where('user_driver_detail_id', $this->driver->id)
        ->where('test_type', 'drug_test')
        ->get();
    foreach ($drugTests as $drugTest) {
        $this->loadMediaFromModel($drugTest, 'record', 'drug_test');
    }
            
    // Cargar documentos generales del conductor
    if (method_exists($this->driver, 'getMedia')) {
        $this->loadMediaFromModel($this->driver, 'other');
    }
}

/**
 * Método auxiliar para cargar los medios (documentos) de un modelo (segunda versión)
 * @internal Este método se ha renombrado para evitar duplicación
 */
private function loadModelMedia($model, $category, $recordType = null)
{
    // Este método se ha renombrado porque duplicaba loadMediaFromModel
    if (!method_exists($model, 'getMedia')) {
        return;
    }
            
    // Obtener todas las colecciones de medios del modelo
    $allMedia = $model->getMedia();
            
    foreach ($allMedia as $media) {
        $fileSize = $this->formatFileSize($media->size);
        $fileDate = $this->formatFileDate(strtotime($media->created_at));
        $documentType = $media->getCustomProperty('document_type') ?? $media->collection_name;
                
        $uniqueKey = Str::random(10) . '_' . $media->id;
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
        
        // Procesar acciones especiales según categoría
        if ($category === 'medical') {
            // Actualizar fecha de vencimiento médica si es necesario
            // Código para actualizar información médica
        } elseif ($category === 'record') {
            // Actualizar estado del récord relacionado si existe
            if (!empty($this->recordSubtype) && !empty($this->relatedRecordId)) {
                $recordTable = null;
                
                if ($this->recordSubtype === 'accident') {
                        $recordTable = 'driver_accidents';
                    } elseif ($this->recordSubtype === 'violation') {
                        $recordTable = 'driver_traffic_convictions';
                    } elseif ($this->recordSubtype === 'course') {
                        $recordTable = 'driver_courses';
                    } elseif ($this->recordSubtype === 'inspection') {
                        $recordTable = 'driver_inspections';
                    } elseif ($this->recordSubtype === 'drug_test') {
                        $recordTable = 'driver_testings';
                    } elseif ($this->recordSubtype === 'training') {
                        $recordTable = 'driver_trainings';
                    }
                    
                    if ($recordTable) {
                        DB::table($recordTable)
                            ->where('id', $this->relatedRecordId)
                            ->update(['has_documents' => true]);
                    }
                }
            }
        }
        
        // No se necesita devolver nada ya que este método solo actualiza registros
        return true;
    }
    
    /**
     * Regenera todos los documentos PDF para la aplicación del conductor
     */
    public function regenerateDocuments()
    {
        if (!$this->driver || !$this->driver->certification) {
            session()->flash('error', 'No se puede regenerar los documentos: falta la certificación del conductor');
            return;
        }

        $this->isRegeneratingPdfs = true;

        try {
            // Obtener la firma del conductor
            $signature = $this->driver->certification->signature;
            
            // Si no hay firma, intentar obtenerla de la colección de medios
            if (empty($signature) && $this->driver->certification->getFirstMedia('signature')) {
                $signature = $this->driver->certification->getFirstMediaUrl('signature');
            }
            
            if (empty($signature)) {
                session()->flash('error', 'No se puede regenerar los documentos: no se encontró la firma del conductor');
                $this->isRegeneratingPdfs = false;
                return;
            }

            // Preparar la firma para PDF
            $signaturePath = $this->prepareSignatureForPDF($signature);
            
            if (!$signaturePath) {
                session()->flash('error', 'No se pudo preparar la firma para los documentos');
                $this->isRegeneratingPdfs = false;
                return;
            }

            // Generar los PDFs de la aplicación
            $this->generateApplicationPDFs($this->driver, $signaturePath);
            
            // Generar documentos específicos según el tipo de conductor
            $this->generateSpecificDocuments($this->driver, $signaturePath);
            
            // Recargar la lista de PDFs generados
            $this->loadGeneratedPdfs();
            
            session()->flash('message', 'Documentos regenerados correctamente');
        } catch (\Exception $e) {
            Log::error('Error al regenerar documentos', [
                'driver_id' => $this->driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Error al regenerar documentos: ' . $e->getMessage());
        }
        
        $this->isRegeneratingPdfs = false;
    }

    /**
     * Genera documentos específicos según el tipo de conductor (owner_operator o third_party_driver)
     * @param UserDriverDetail $userDriverDetail
     * @param string $signaturePath Ruta al archivo de firma
     */
    private function generateSpecificDocuments($userDriverDetail, $signaturePath)
    {
        try {
            // Verificar si el conductor tiene una aplicación con detalles
            if (!$userDriverDetail->application || !$userDriverDetail->application->details) {
                Log::warning('No se pueden generar documentos específicos: faltan datos de aplicación', [
                    'driver_id' => $userDriverDetail->id,
                    'has_application' => $userDriverDetail->application ? 'yes' : 'no',
                    'has_details' => ($userDriverDetail->application && $userDriverDetail->application->details) ? 'yes' : 'no'
                ]);
                return;
            }
            
            // Obtener el tipo de conductor
            $applyingPosition = $userDriverDetail->application->details->applying_position ?? 'unknown';
            
            Log::info('Verificando tipo de conductor para generar documentos específicos', [
                'driver_id' => $userDriverDetail->id,
                'applying_position' => $applyingPosition
            ]);
            
            // Generar documentos según el tipo de conductor
            if ($applyingPosition === 'owner_operator') {
                $this->generateLeaseAgreementOwner($userDriverDetail, $signaturePath);
            } elseif ($applyingPosition === 'third_party_driver') {
                $this->generateThirdPartyDocuments($userDriverDetail, $signaturePath);
            } else {
                Log::info('No se generan documentos específicos para este tipo de conductor', [
                    'driver_id' => $userDriverDetail->id,
                    'applying_position' => $applyingPosition
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al generar documentos específicos', [
                'driver_id' => $userDriverDetail->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Genera el contrato de arrendamiento para propietarios-operadores
     * @param UserDriverDetail $userDriverDetail
     * @param string $signaturePath Ruta al archivo de firma
     */
    private function generateLeaseAgreementOwner($userDriverDetail, $signaturePath)
    {
        try {
            // Cargar todas las relaciones necesarias para asegurar que tenemos los datos completos
            $userDriverDetail->load([
                'application.details', 
                'application.ownerOperatorDetail', 
                'user',
                'carrier'
            ]);
            
            // Verificar cada relación individualmente y registrar qué datos faltan
            $missingData = [];
            
            if (!$userDriverDetail->application) {
                $missingData[] = 'application';
            } elseif (!$userDriverDetail->application->details) {
                $missingData[] = 'application.details';
            }
            
            // Intentar obtener el vehículo a través de la aplicación o buscar por driver_id
            $vehicle = null;
            if ($userDriverDetail->application && method_exists($userDriverDetail->application, 'vehicle')) {
                $vehicle = $userDriverDetail->application->vehicle;
            }
            
            // Si no se encuentra, buscar en la tabla de vehículos directamente
            if (!$vehicle) {
                $vehicle = \App\Models\Admin\Vehicle\Vehicle::where('user_driver_detail_id', $userDriverDetail->id)->first();
            }
            
            if (!$userDriverDetail->carrier) {
                $missingData[] = 'carrier';
            }
            
            if (!$userDriverDetail->user) {
                $missingData[] = 'user';
            }
            
            // Si faltan datos críticos, registrar el error y salir
            if (!empty($missingData)) {
                Log::error('Datos insuficientes para generar contrato de arrendamiento de propietario-operador', [
                    'driver_id' => $userDriverDetail->id,
                    'missing_data' => $missingData
                ]);
                return;
            }
            
            $application = $userDriverDetail->application;
            $carrier = $userDriverDetail->carrier;
            $user = $userDriverDetail->user;
            
            // Preparar los datos para el PDF
            $ownerDetails = $application->ownerOperatorDetail;
            $applicationDetails = $application->details;
            
            $data = [
                'carrierName' => $carrier->name ?? 'EF Services',
                'carrierAddress' => $carrier->address ?? '',
                'ownerName' => $applicationDetails->owner_name ?? $userDriverDetail->user->name ?? '',
                'ownerDba' => $ownerDetails->business_name ?? '',
                'ownerAddress' => $ownerDetails->address ?? $userDriverDetail->current_address ?? '',
                'ownerPhone' => $ownerDetails->phone ?? $userDriverDetail->phone ?? '',
                'ownerEmail' => $ownerDetails->email ?? $userDriverDetail->user->email ?? '',
                'ownerFein' => $ownerDetails->tax_id ?? '',
                'ownerLicense' => $userDriverDetail->license_number ?? '',
                'ownerCdlExpiry' => $userDriverDetail->license_expiry_date ? $userDriverDetail->license_expiry_date->format('m/d/Y') : '',
                'vehicleYear' => $vehicle->year ?? '',
                'vehicleMake' => $vehicle->make ?? '',
                'vehicleVin' => $vehicle->vin ?? '',
                'vehicleUnit' => $vehicle->company_unit_number ?? '',
                'signedDate' => now()->format('m/d/Y'),
                'carrierMc' => $carrier->mc_number ?? '',
                'carrierUsdot' => $carrier->state_dot ?? '',
                'signaturePath' => $signaturePath,
                'signature' => null // Mantenemos este campo como NULL para compatibilidad
            ];
            
            try {
                Log::info('Intentando cargar vista de contrato de propietario-operador', [
                    'driver_id' => $userDriverDetail->id,
                    'view' => 'pdfs.lease-agreement-owner',
                    'data_keys' => array_keys($data)
                ]);
                
                // Cargar la vista del contrato de arrendamiento para propietarios-operadores
                $pdf = \Barryvdh\DomPDF\Facade\PDF::loadView('pdfs.lease-agreement-owner', $data);
                
                // Asegurarnos de que estamos usando el ID correcto
                $driverId = $userDriverDetail->id;
                $dirPath = 'driver/' . $driverId . '/vehicle-verifications';
                $filePath = $dirPath . '/lease_agreement_owner_operator_' . time() . '.pdf';
                
                Log::info('Guardando PDF de contrato de arrendamiento para propietario-operador', [
                    'driver_id' => $driverId,
                    'file_path' => $filePath
                ]);
                
                // Asegurarnos de que el directorio existe
                Storage::disk('public')->makeDirectory($dirPath);
                
                // Guardar el PDF
                $pdfContent = $pdf->output();
                Storage::disk('public')->put($filePath, $pdfContent);
                
            } catch (\Exception $e) {
                Log::error('Error al generar PDF de contrato de arrendamiento para propietario-operador', [
                    'driver_id' => $userDriverDetail->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al generar contrato de arrendamiento para propietario-operador', [
                'driver_id' => $userDriverDetail->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Genera documentos específicos para conductores third-party
     * @param UserDriverDetail $userDriverDetail
     * @param string $signaturePath Ruta al archivo de firma
     */
    private function generateThirdPartyDocuments($userDriverDetail, $signaturePath)
    {
        try {
            // Cargar todas las relaciones necesarias para asegurar que tenemos los datos completos
            $userDriverDetail->load([
                'application.details', 
                'application.thirdPartyDetail', 
                'user',
                'carrier'
            ]);
            
            // Verificar cada relación individualmente y registrar qué datos faltan
            $missingData = [];
            
            if (!$userDriverDetail->application) {
                $missingData[] = 'application';
            } elseif (!$userDriverDetail->application->details) {
                $missingData[] = 'application.details';
            }
            
            // Intentar obtener el vehículo a través de la aplicación o buscar por driver_id
            $vehicle = null;
            if ($userDriverDetail->application && method_exists($userDriverDetail->application, 'vehicle')) {
                $vehicle = $userDriverDetail->application->vehicle;
            }
            
            // Si no se encuentra, buscar en la tabla de vehículos directamente
            if (!$vehicle) {
                $vehicle = \App\Models\Admin\Vehicle\Vehicle::where('user_driver_detail_id', $userDriverDetail->id)->first();
            }
            
            if (!$userDriverDetail->carrier) {
                $missingData[] = 'carrier';
            }
            
            if (!$userDriverDetail->user) {
                $missingData[] = 'user';
            }
            
            // Si faltan datos críticos, registrar el error y salir
            if (!empty($missingData)) {
                Log::error('Datos insuficientes para generar documentos de third-party', [
                    'driver_id' => $userDriverDetail->id,
                    'missing_data' => $missingData
                ]);
                return;
            }
            
            $application = $userDriverDetail->application;
            $carrier = $userDriverDetail->carrier;
            $user = $userDriverDetail->user;
            $thirdPartyDetails = $application->thirdPartyDetail;
            $applicationDetails = $application->details;
            
            // Preparar los datos para el PDF de consentimiento de terceros
            $consentData = [
                'carrierName' => $carrier->name ?? 'EF Services',
                'carrierAddress' => $carrier->address ?? '',
                'driverName' => $user->name ?? '',
                'driverAddress' => $userDriverDetail->current_address ?? '',
                'driverPhone' => $userDriverDetail->phone ?? '',
                'driverEmail' => $user->email ?? '',
                'thirdPartyName' => $thirdPartyDetails->third_party_name ?? '',
                'thirdPartyDba' => $thirdPartyDetails->third_party_dba ?? '',
                'thirdPartyAddress' => $thirdPartyDetails->third_party_address ?? '',
                'thirdPartyPhone' => $thirdPartyDetails->third_party_phone ?? '',
                'thirdPartyEmail' => $thirdPartyDetails->third_party_email ?? '',
                'thirdPartyContact' => $thirdPartyDetails->third_party_contact ?? '',
                'thirdPartyFein' => $thirdPartyDetails->third_party_fein ?? '',
                'signedDate' => now()->format('m/d/Y'),
                'signaturePath' => $signaturePath,
                'signature' => null // Mantenemos este campo como NULL para compatibilidad
            ];
            
            // Generar el PDF de consentimiento de terceros
            try {
                Log::info('Intentando cargar vista de consentimiento de terceros', [
                    'driver_id' => $userDriverDetail->id,
                    'view' => 'pdfs.third-party-consent',
                    'data_keys' => array_keys($consentData)
                ]);
                
                // Cargar la vista del consentimiento de terceros
                $pdf = \Barryvdh\DomPDF\Facade\PDF::loadView('pdfs.third-party-consent', $consentData);
                
                // Asegurarnos de que estamos usando el ID correcto
                $driverId = $userDriverDetail->id;
                $dirPath = 'driver/' . $driverId . '/vehicle-verifications';
                $filePath = $dirPath . '/third_party_consent_' . time() . '.pdf';
                
                Log::info('Guardando PDF de consentimiento de terceros', [
                    'driver_id' => $driverId,
                    'file_path' => $filePath
                ]);
                
                // Asegurarnos de que el directorio existe
                Storage::disk('public')->makeDirectory($dirPath);
                
                // Guardar el PDF
                $pdfContent = $pdf->output();
                Storage::disk('public')->put($filePath, $pdfContent);
                
            } catch (\Exception $e) {
                Log::error('Error al generar PDF de consentimiento de terceros', [
                    'driver_id' => $userDriverDetail->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            // Preparar los datos para el PDF de contrato de arrendamiento para third-party
            $leaseData = [
                'carrierName' => $carrier->name ?? 'EF Services',
                'carrierAddress' => $carrier->address ?? '',
                'driverName' => $user->name ?? '',
                'driverAddress' => $userDriverDetail->current_address ?? '',
                'driverPhone' => $userDriverDetail->phone ?? '',
                'driverEmail' => $user->email ?? '',
                'thirdPartyName' => $thirdPartyDetails->third_party_name ?? '',
                'thirdPartyDba' => $thirdPartyDetails->third_party_dba ?? '',
                'thirdPartyAddress' => $thirdPartyDetails->third_party_address ?? '',
                'thirdPartyPhone' => $thirdPartyDetails->third_party_phone ?? '',
                'thirdPartyEmail' => $thirdPartyDetails->third_party_email ?? '',
                'thirdPartyContact' => $thirdPartyDetails->third_party_contact ?? '',
                'thirdPartyFein' => $thirdPartyDetails->third_party_fein ?? '',
                'vehicleYear' => $vehicle->year ?? '',
                'vehicleMake' => $vehicle->make ?? '',
                'vehicleVin' => $vehicle->vin ?? '',
                'vehicleUnit' => $vehicle->company_unit_number ?? '',
                'signedDate' => now()->format('m/d/Y'),
                'carrierMc' => $carrier->mc_number ?? '',
                'carrierUsdot' => $carrier->state_dot ?? '',
                'signaturePath' => $signaturePath,
                'signature' => null // Mantenemos este campo como NULL para compatibilidad
            ];
            
            // Generar el PDF de contrato de arrendamiento para third-party
            try {
                Log::info('Intentando cargar vista de contrato de arrendamiento para third-party', [
                    'driver_id' => $userDriverDetail->id,
                    'view' => 'pdfs.lease-agreement',
                    'data_keys' => array_keys($leaseData)
                ]);
                
                // Cargar la vista del contrato de arrendamiento para third-party
                $pdf = \Barryvdh\DomPDF\Facade\PDF::loadView('pdfs.lease-agreement', $leaseData);
                
                // Asegurarnos de que estamos usando el ID correcto
                $driverId = $userDriverDetail->id;
                $dirPath = 'driver/' . $driverId . '/vehicle-verifications';
                $filePath = $dirPath . '/lease_agreement_third_party_' . time() . '.pdf';
                
                Log::info('Guardando PDF de contrato de arrendamiento para third-party', [
                    'driver_id' => $driverId,
                    'file_path' => $filePath
                ]);
                
                // Asegurarnos de que el directorio existe
                Storage::disk('public')->makeDirectory($dirPath);
                
                // Guardar el PDF
                $pdfContent = $pdf->output();
                Storage::disk('public')->put($filePath, $pdfContent);
                
            } catch (\Exception $e) {
                Log::error('Error al generar PDF de contrato de arrendamiento para third-party', [
                    'driver_id' => $userDriverDetail->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error al generar documentos para third-party', [
                'driver_id' => $userDriverDetail->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Prepara la firma para usarla en PDFs
     * @param string $signature La firma en formato base64 o ruta de archivo
     * @return string|null La ruta al archivo de firma
     */
    private function prepareSignatureForPDF($signature)
    {
        // Si no hay firma, retornar null
        if (empty($signature)) {
            return null;
        }

        // Si ya es una ruta de archivo, verificar que existe
        if (is_string($signature) && file_exists($signature)) {
            return $signature;
        }

        // Si es una URL, intentar descargar la imagen
        if (is_string($signature) && strpos($signature, 'http') === 0) {
            try {
                $tempFile = storage_path('app/temp/sig_' . uniqid() . '.png');
                
                // Asegurar que el directorio existe
                if (!file_exists(dirname($tempFile))) {
                    mkdir(dirname($tempFile), 0755, true);
                }
                
                // Descargar la imagen
                $imageContent = file_get_contents($signature);
                file_put_contents($tempFile, $imageContent);
                
                return $tempFile;
            } catch (\Exception $e) {
                Log::error('Error al descargar firma desde URL', [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        }

        // Si es base64, convertir a archivo temporal
        if (is_string($signature) && strpos($signature, 'data:image') === 0) {
            try {
                $signatureData = base64_decode(explode(',', $signature)[1]);
                $tempFile = storage_path('app/temp/sig_' . uniqid() . '.png');

                // Asegurar que el directorio existe
                if (!file_exists(dirname($tempFile))) {
                    mkdir(dirname($tempFile), 0755, true);
                }

                file_put_contents($tempFile, $signatureData);
                return $tempFile;
            } catch (\Exception $e) {
                Log::error('Error al convertir firma base64', [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        }

        return null;
    }

    /**
     * Genera los PDFs de la aplicación
     */
    private function generateApplicationPDFs($userDriverDetail, $signaturePath)
    {
        // Importar la clase PDF
        $pdf = app('dompdf.wrapper');
        
        // Asegurarse que los directorios existen
        $driverPath = 'driver/' . $userDriverDetail->id;
        $appSubPath = $driverPath . '/driver_applications';
        
        // Asegúrate de que los directorios existen
        Storage::disk('public')->makeDirectory($driverPath);
        Storage::disk('public')->makeDirectory($appSubPath);
        
        // Configuraciones de pasos - definir la vista y nombre de archivo para cada paso
        $steps = [
            ['view' => 'pdf.driver.general', 'filename' => 'general.pdf', 'title' => 'General'],
            ['view' => 'pdf.driver.address', 'filename' => 'address.pdf', 'title' => 'Address'],
            ['view' => 'pdf.driver.application', 'filename' => 'application.pdf', 'title' => 'Application'],
            ['view' => 'pdf.driver.licenses', 'filename' => 'licenses.pdf', 'title' => 'Licenses'],
            ['view' => 'pdf.driver.medical', 'filename' => 'medical.pdf', 'title' => 'Medical'],
            ['view' => 'pdf.driver.training', 'filename' => 'training.pdf', 'title' => 'Training'],
            ['view' => 'pdf.driver.traffic', 'filename' => 'traffic_violations.pdf', 'title' => 'Traffic Violations'],
            ['view' => 'pdf.driver.accident', 'filename' => 'accidents.pdf', 'title' => 'Accidents'],
            ['view' => 'pdf.driver.fmcsr', 'filename' => 'fmcsr_requirements.pdf', 'title' => 'FMCSR Requirements'],
            ['view' => 'pdf.driver.employment', 'filename' => 'employment_history.pdf', 'title' => 'Employment History'],
            ['view' => 'pdf.driver.certification', 'filename' => 'certification.pdf', 'title' => 'Certification'],
        ];
        
        // Generar PDF para cada paso
        foreach ($steps as $step) {
            try {
                $pdf = \Barryvdh\DomPDF\Facade\PDF::loadView($step['view'], [
                    'userDriverDetail' => $userDriverDetail,
                    'signaturePath' => $signaturePath,
                    'title' => $step['title'],
                    'date' => now()->format('d/m/Y')
                ]);
                
                // Guardar PDF usando Storage para evitar problemas de permisos
                $pdfContent = $pdf->output();
                Storage::disk('public')->put($appSubPath . '/' . $step['filename'], $pdfContent);
                
                Log::info('PDF individual regenerado', [
                    'driver_id' => $userDriverDetail->id,
                    'filename' => $step['filename']
                ]);
            } catch (\Exception $e) {
                Log::error('Error generando PDF individual', [
                    'driver_id' => $userDriverDetail->id,
                    'filename' => $step['filename'],
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
        
        // Generar un PDF combinado con todos los pasos
        $this->generateCombinedPDF($userDriverDetail, $signaturePath);
        
        // Limpiar archivo temporal de firma si es necesario
        if (strpos($signaturePath, 'temp/sig_') !== false) {
            @unlink($signaturePath);
        }
    }

    /**
     * Genera el PDF combinado
     */
    private function generateCombinedPDF($userDriverDetail, $signaturePath)
    {
        try {
            $pdf = \Barryvdh\DomPDF\Facade\PDF::loadView('pdf.driver.complete_application', [
                'userDriverDetail' => $userDriverDetail,
                'signaturePath' => $signaturePath,
                'date' => now()->format('d/m/Y')
            ]);
            
            // Asegurarnos de que estamos usando el ID correcto
            $driverId = $userDriverDetail->id;
            $filePath = 'driver/' . $driverId . '/complete_application.pdf';
            
            Log::info('Guardando PDF combinado para conductor', ['driver_id' => $driverId, 'file_path' => $filePath]);
            
            // Guardar el PDF combinado usando Storage
            $pdfContent = $pdf->output();
            Storage::disk('public')->put($filePath, $pdfContent);
            
            // Guardar PDF temporalmente para adjuntarlo a MediaLibrary
            $tempPath = tempnam(sys_get_temp_dir(), 'complete_application_') . '.pdf';
            file_put_contents($tempPath, $pdfContent);
            
            // Adjuntar el PDF a la aplicación
            if ($userDriverDetail->application) {
                try {
                    // Limpiar collection previa y agregar el nuevo archivo
                    $userDriverDetail->application->clearMediaCollection('application_pdf');
                    $userDriverDetail->application->addMedia($tempPath)
                        ->toMediaCollection('application_pdf');
                        
                    // Registrar información para confirmar
                    Log::info('PDF combinado agregado a Media Library', [
                        'driver_id' => $driverId,
                        'application_id' => $userDriverDetail->application->id
                    ]);
                    
                    // Si el modelo tiene columna pdf_path, también actualizar ahí
                    if (Schema::hasColumn('driver_applications', 'pdf_path')) {
                        $userDriverDetail->application->update([
                            'pdf_path' => $filePath
                        ]);
                    }
                } catch (\Exception $e) {
                    // Si falla, registrar error
                    Log::error('Error adding media to application', [
                        'error' => $e->getMessage(),
                        'driver_id' => $driverId
                    ]);
                }
                
                // Limpiar archivo temporal
                @unlink($tempPath);
            }
        } catch (\Exception $e) {
            Log::error('Error generando PDF individual', [
                'driver_id' => $userDriverDetail->id,
                'filename' => $step['filename'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    /**
     * Cambia la pestaña actual
     */
    public function changeTab($tab)
    {
        $this->currentTab = $tab;
        
        // Si cambiamos a la pestaña de documentos, cargamos los PDFs generados y documentos por categoría
        if ($tab === 'documents') {
            $this->loadGeneratedPdfs();
            $this->loadCategorizedDocuments();
        }
    }
    
    /**
     * Carga los documentos por categoría
     */
    protected function loadCategorizedDocuments()
    {
        if (!$this->driver) return;
        
        // Reiniciar las listas de documentos
        $this->licenseDocuments = [];
        $this->medicalDocuments = [];
        $this->recordDocuments = [];
        $this->otherDocuments = [];
        
        try {
            $driverPath = 'driver/' . $this->driver->id;
            
            // Mapeo de directorios por categoría
            $categoryDirs = [
                'license' => $driverPath . '/licenses',
                'medical' => $driverPath . '/medical',
                'record' => $driverPath . '/records',
                'other' => $driverPath . '/documents'
            ];
            
            // Para cada categoría, buscar archivos en el directorio correspondiente
            foreach ($categoryDirs as $category => $dir) {
                if (!Storage::disk('public')->exists($dir)) {
                    continue; // Si el directorio no existe, saltar a la siguiente categoría
                }
                
                $files = Storage::disk('public')->files($dir);
                
                // Filtrar solo archivos PDF
                $pdfFiles = array_filter($files, function($file) {
                    return Str::endsWith(strtolower($file), '.pdf');
                });
                
                // Procesar cada archivo
                foreach ($pdfFiles as $file) {
                    $fileName = basename($file);
                    $fileSize = Storage::disk('public')->size($file);
                    $lastModified = Storage::disk('public')->lastModified($file);
                    
                    // Obtener descripción del nombre del archivo (quitar timestamp y extensión)
                    $description = preg_replace('/_[0-9]+\.pdf$/', '', $fileName);
                    $description = str_replace('_', ' ', $description);
                    $description = ucfirst($description);
                    
                    $documentInfo = [
                        'name' => $fileName,
                        'description' => $description,
                        'url' => asset('storage/' . $file),
                        'size' => $this->formatFileSize($fileSize),
                        'date' => $this->formatFileDate($lastModified)
                    ];
                    
                    // Agregar a la lista correspondiente
                    switch ($category) {
                        case 'license':
                            $this->licenseDocuments[] = $documentInfo;
                            break;
                        case 'medical':
                            $this->medicalDocuments[] = $documentInfo;
                            break;
                        case 'record':
                            $this->recordDocuments[] = $documentInfo;
                            break;
                        case 'other':
                            $this->otherDocuments[] = $documentInfo;
                            break;
                    }
                }
            }
            
            // Ordenar documentos por fecha (más recientes primero)
            $sortFunction = function($a, $b) {
                $dateA = strtotime(str_replace('/', '-', $a['date']));
                $dateB = strtotime(str_replace('/', '-', $b['date']));
                return $dateB - $dateA;
            };
            
            usort($this->licenseDocuments, $sortFunction);
            usort($this->medicalDocuments, $sortFunction);
            usort($this->recordDocuments, $sortFunction);
            usort($this->otherDocuments, $sortFunction);
            
        } catch (\Exception $e) {
            Log::error('Error al cargar documentos por categoría', [
                'driver_id' => $this->driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    public function saveVerification()
    {
        // Prepare verification data
        $verificationItems = [];
        foreach ($this->checklistItems as $key => $item) {
            $verificationItems[$key] = $item['checked'];
        }
    
        // Update or create verification in database
        DriverRecruitmentVerification::updateOrCreate(
            [
                'driver_application_id' => $this->application->id
            ],
            [
                'verified_by_user_id' => Auth::id(),
                'verification_items' => $verificationItems,
                'notes' => $this->verificationNotes,
                'verified_at' => now()
            ]
        );
    
        // Refresh data
        $this->loadLastVerification();
    
        // Obtener los estados base desde el servicio
        $stepService = new DriverStepService();
        $baseSteps = $stepService->getStepsStatus($this->driver);
        
        // FORZAR actualización de los estados según checklist directamente
        if ($this->checklistItems['training_verified']['checked']) {
            $baseSteps[DriverStepService::STEP_TRAINING] = DriverStepService::STATUS_COMPLETED;
        }
        
        if ($this->checklistItems['traffic_verified']['checked']) {
            $baseSteps[DriverStepService::STEP_TRAFFIC] = DriverStepService::STATUS_COMPLETED;
        }
        
        if ($this->checklistItems['accident_verified']['checked']) {
            $baseSteps[DriverStepService::STEP_ACCIDENT] = DriverStepService::STATUS_COMPLETED;
        }
        
        // Actualizar estados y calcular porcentaje
        $this->stepsStatus = $baseSteps;
        $this->completionPercentage = $stepService->calculateCompletionPercentage($this->driver);
    
        session()->flash('message', 'Verificación guardada correctamente.');
    
    // Emitir evento para que otros componentes se actualicen
    // Emitir tanto local como al componente específico
    $this->dispatch('verification_updated', driverApplicationId: $this->driver->application->id);
    $this->dispatch('verification_updated')->to('admin.driver.recruitment.driver-recruitment-list');
    }

    /**
     * Seleccionar un documento para solicitar y abrir el modal
     */
    public function selectDocument($document)
    {
        $this->selectedDocument = $document;
        $this->documentReason = $this->documentReasons[$document] ?? '';
        $this->dispatch('open-document-reason-modal');
    }

    /**
     * Guardar la razón para un documento solicitado
     */
    public function saveDocumentReason()
    {
        $this->validate([
            'documentReason' => 'required|min:5|max:500',
        ], [
            'documentReason.required' => 'Por favor, indique el motivo por el que solicita este documento.',
            'documentReason.min' => 'El motivo debe tener al menos 5 caracteres.',
            'documentReason.max' => 'El motivo no puede exceder los 500 caracteres.'
        ]);

        // Guardar la razón para este documento
        $this->documentReasons[$this->selectedDocument] = $this->documentReason;

        // Añadir el documento a la lista de documentos solicitados si no está ya
        if (!in_array($this->selectedDocument, $this->requestedDocuments)) {
            $this->requestedDocuments[] = $this->selectedDocument;
        }

        // Limpiar el formulario
        $this->selectedDocument = null;
        $this->documentReason = '';

        // Cerrar el modal
        $this->dispatch('close-document-reason-modal');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Documento añadido a la solicitud.'
        ]);
    }

    /**
     * Cancelar la solicitud de documento
     */
    public function cancelDocumentReason()
    {
        $this->selectedDocument = null;
        $this->documentReason = '';
        $this->dispatch('close-document-reason-modal');
    }

    /**
     * Eliminar un documento de la lista de solicitados
     */
    public function removeRequestedDocument($document)
    {
        $this->requestedDocuments = array_values(array_filter($this->requestedDocuments, function($item) use ($document) {
            return $item !== $document;
        }));

        // Eliminar también la razón si existe
        if (isset($this->documentReasons[$document])) {
            unset($this->documentReasons[$document]);
        }

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Documento eliminado de la solicitud.'
        ]);
    }

    /**
     * Enviar la solicitud de documentos adicionales
     */
    public function requestAdditionalDocuments()
    {
        $this->validate([
            'requestedDocuments' => 'required|array|min:1',
            'additionalRequirements' => 'nullable|string|max:1000'
        ], [
            'requestedDocuments.required' => 'Debe seleccionar al menos un documento para solicitar.',
            'requestedDocuments.min' => 'Debe seleccionar al menos un documento para solicitar.'
        ]);

        try {
            // Iniciar transacción
            DB::beginTransaction();

            // Actualizar la aplicación con los documentos solicitados
            $this->application->update([
                'requested_documents' => json_encode($this->requestedDocuments),
                'additional_requirements' => $this->additionalRequirements,
                'document_reasons' => json_encode($this->documentReasons),
                'status' => 'pending' // Mantener en pendiente hasta que se completen los requisitos
            ]);

            // Enviar notificación al conductor
            if ($this->driver && $this->driver->user) {
                $this->driver->user->notify(new \App\Notifications\DocumentsRequiredNotification(
                    $this->driver,
                    $this->application,
                    $this->requestedDocuments,
                    $this->additionalRequirements,
                    $this->documentReasons
                ));
            }

            // Enviar notificación al transportista si existe
            if ($this->driver && $this->driver->carrier && $this->driver->carrier->user) {
                $this->driver->carrier->user->notify(new \App\Notifications\DocumentsRequiredNotification(
                    $this->driver,
                    $this->application,
                    $this->requestedDocuments,
                    $this->additionalRequirements,
                    $this->documentReasons
                ));
            }

            // Guardar un registro de la solicitud
            \App\Models\Admin\Driver\DriverRecruitmentVerification::create([
                'driver_application_id' => $this->application->id,
                'verified_by_user_id' => Auth::id(),
                'verification_items' => json_encode([
                    'requested_documents' => $this->requestedDocuments,
                    'document_reasons' => $this->documentReasons,
                    'additional_requirements' => $this->additionalRequirements
                ]),
                'notes' => 'Documentos solicitados: ' . implode(', ', $this->requestedDocuments) . 
                           ($this->additionalRequirements ? '. Requisitos adicionales: ' . $this->additionalRequirements : ''),
                'verified_at' => now()
            ]);

            DB::commit();

            // Limpiar el formulario
            $this->requestedDocuments = [];
            $this->documentReasons = [];
            $this->additionalRequirements = '';

            session()->flash('message', 'Solicitud de documentos adicionales enviada al conductor y al transportista.');
            
            // Recargar los datos
            $this->loadDriverData();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al solicitar documentos adicionales', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', 'Error al enviar la solicitud: ' . $e->getMessage());
        }
    }

    public function approveApplication()
    {
        if (!$this->isChecklistComplete()) {
            $this->addError('checklist', ' You must complete the entire checklist prior to approval.');
            return;
        }

        try {
            \DB::transaction(function () {
                // Guardar la verificación final
                $this->saveVerification();

                // Actualizar estado de la aplicación a aprobado
                $this->application->update([
                    'status' => DriverApplication::STATUS_APPROVED,
                    'completed_at' => now()
                ]);

                // Actualizar estado del driver y establecer porcentaje de completado a 100%
                $this->driver->update([
                    'status' => UserDriverDetail::STATUS_ACTIVE,
                    'completion_percentage' => 100 // Establecer el porcentaje de completado a 100%
                ]);
                
                // Refrescar el modelo desde la base de datos para asegurar que los cambios se guardaron
                $this->driver->refresh();
                
                // Log para verificar que los cambios se guardaron correctamente
                \Log::info('Driver approved successfully', [
                    'driver_id' => $this->driver->id,
                    'status' => $this->driver->status,
                    'completion_percentage' => $this->driver->completion_percentage
                ]);
            });

            // Actualizar la propiedad local para reflejar el cambio inmediatamente
            $this->completionPercentage = 100;

            // Opcional: Enviar notificación al conductor
            // Notification::send($this->driver->user, new DriverApplicationApprovedNotification($this->driver));

            // Actualizar datos locales
            $this->loadDriverData();

            // Notificar a otros componentes
            $this->dispatch('applicationStatusUpdated');

            // Mostrar mensaje de éxito
            session()->flash('message', 'La solicitud ha sido aprobada correctamente.');
            
        } catch (\Exception $e) {
            \Log::error('Error approving driver application', [
                'driver_id' => $this->driver->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Error al aprobar la solicitud: ' . $e->getMessage());
        }
    }

    public function rejectApplication()
    {
        return $this->processRejection();
    }

    public function processRejection()
    {
        try {
            // Validar razón de rechazo
            $this->validate([
                'rejectionReason' => 'required|min:10'
            ], [
                'rejectionReason.required' => 'You must provide a reason for rejection.',
                'rejectionReason.min' => 'The reason must have at least 10 characters.'
            ]);

            // Log para depuración
            Log::info('Procesando rechazo con razón: ' . $this->rejectionReason);

            // Actualizar estado de la aplicación a rechazado
            $this->application->update([
                'status' => DriverApplication::STATUS_REJECTED,
                'rejection_reason' => $this->rejectionReason,
                'completed_at' => now()
            ]);

            // Actualizar datos locales
            $this->loadDriverData();

            // Notificar a otros componentes
            $this->dispatch('applicationStatusUpdated');
            
            // Limpiar el campo de razón
            $this->rejectionReason = '';

            // Mostrar mensaje
            session()->flash('message', 'La solicitud ha sido rechazada correctamente.');

            Log::info('Rechazo procesado correctamente');
            return true;
        } catch (\Exception $e) {
            Log::error('Error al procesar el rechazo: ' . $e->getMessage());
            session()->flash('error', 'Ocurrió un error al procesar el rechazo: ' . $e->getMessage());
            return false;
        }
    }

    public function downloadAllDocuments()
    {
        if (!$this->driver || !$this->driver->id) {
            session()->flash('error', 'Driver not found');
            return;
        }

        $driverId = $this->driver->id;
        $driverName = $this->driver->user->name . ' ' . $this->driver->last_name;
        $zipFileName = Str::slug($driverName) . '-documents.zip';
        $zipFilePath = storage_path('app/public/temp/' . $zipFileName);

        // Asegúrate de que el directorio de temp exista
        if (!Storage::disk('public')->exists('temp')) {
            Storage::disk('public')->makeDirectory('temp');
        }

        // Ruta al directorio del driver
        $driverPath = 'driver/' . $driverId;
        $fullDriverPath = storage_path('app/public/' . $driverPath);

        // Verificar si el directorio existe
        if (!file_exists($fullDriverPath)) {
            session()->flash('error', 'No documents found for this driver');
            return;
        }

        // Crear un nuevo archivo ZIP
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            session()->flash('error', 'Could not create ZIP file');
            return;
        }

        // Función para agregar archivos recursivamente
        $addFilesToZip = function ($dir, $zipBasePath = '') use ($zip, &$addFilesToZip) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = $zipBasePath . substr($filePath, strlen($dir) + 1);

                    $zip->addFile($filePath, $relativePath);
                }
            }
        };

        // Agregar todos los archivos del directorio del driver
        $addFilesToZip($fullDriverPath, 'driver-documents/');

        // Cerrar el ZIP
        $zip->close();

        // Devolver respuesta de descarga
        return response()->download($zipFilePath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    // Resto de los métodos igual que antes...

    /**
     * Maneja el evento cuando se actualiza una escuela de capacitación
     * Este método se llama cuando el componente DriverTrainingModal emite el evento 'training-school-updated'
     *
     * @param array $data Los datos del evento que incluyen driverId, schoolId y timestamp
     * @return void
     */
    public function handleTrainingSchoolUpdated($data)
    {
        // Verificar que los datos del evento corresponden al driver actual
        if (isset($data['driverId']) && $data['driverId'] == $this->driverId) {
            // Recargar los datos del driver para actualizar la información en tiempo real
            Log::info('Recargando datos del driver después de actualizar escuela de capacitación', [
                'driver_id' => $this->driverId,
                'updated_school_id' => $data['schoolId'] ?? null,
                'timestamp' => $data['timestamp'] ?? now()->timestamp
            ]);
            
            // Recargar los datos del driver
            $this->loadDriverData();
        }
    }
    
    /**
     * Abre el modal para editar la foto de la licencia
     * 
     * @return void
     */
    public function editLicensePhoto()
    {
        $this->documentCategory = 'license';
        $this->documentType = 'license_photo';
        $this->documentDescription = 'Foto de Licencia de Conducir';
        $this->selectedRecordType = 'license';
        
        // Si el conductor tiene licencias, seleccionar la primera por defecto
        if ($this->driver && $this->driver->licenses && count($this->driver->licenses) > 0) {
            $this->selectedRecordId = $this->driver->licenses[0]->id;
        }
        
        $this->showUploadModal = true;
        $this->dispatch('open-upload-modal');
    }
    
    /**
     * Abre el modal para subir o actualizar la imagen frontal de una licencia específica
     * 
     * @param int $licenseId ID de la licencia
     * @return void
     */
    public function editLicenseFrontImage($licenseId)
    {
        $this->selectedLicenseId = $licenseId;
        $this->licenseImageType = 'license_front';
        $this->documentCategory = 'license';
        $this->documentDescription = 'Imagen Frontal de Licencia';
        
        $this->showUploadModal = true;
        $this->dispatch('open-license-image-modal');
    }
    
    /**
     * Abre el modal para subir o actualizar la imagen trasera de una licencia específica
     * 
     * @param int $licenseId ID de la licencia
     * @return void
     */
    public function editLicenseBackImage($licenseId)
    {
        $this->selectedLicenseId = $licenseId;
        $this->licenseImageType = 'license_back';
        $this->documentCategory = 'license';
        $this->documentDescription = 'Imagen Trasera de Licencia';
        
        $this->showUploadModal = true;
        $this->dispatch('open-license-image-modal');
    }
    
    /**
     * Maneja el evento cuando se sube un archivo
     * 
     * @param array $data Los datos del archivo subido
     * @return void
     */
    public function handleFileUploaded($data)
    {
        // Guardar información del archivo temporal
        $this->tempDocumentPath = $data['tempPath'] ?? null;
        $this->tempDocumentName = $data['originalName'] ?? null;
        $this->tempDocumentSize = $data['size'] ?? null;
        
        if ($this->tempDocumentPath) {
            // Si estamos editando una foto de licencia
            if ($this->documentCategory === 'license' && $this->documentType === 'license_photo' && $this->selectedRecordId) {
                try {
                    // Buscar la licencia seleccionada
                    $license = DB::table('driver_licenses')->where('id', $this->selectedRecordId)->first();
                    
                    if ($license) {
                        // Guardar el archivo en la ubicación correcta
                        $storagePath = 'public/driver/' . $this->driverId . '/licenses';
                        $fileName = 'license_' . $this->selectedRecordId . '_' . time() . '.' . pathinfo($this->tempDocumentName, PATHINFO_EXTENSION);
                        
                        // Mover el archivo desde la ubicación temporal a la ubicación final
                        if (Storage::exists('public/' . $this->tempDocumentPath)) {
                            Storage::move('public/' . $this->tempDocumentPath, $storagePath . '/' . $fileName);
                            
                            // Actualizar el registro de la licencia con la nueva ruta de la imagen
                            DB::table('driver_licenses')
                                ->where('id', $this->selectedRecordId)
                                ->update([
                                    'license_image' => 'driver/' . $this->driverId . '/licenses/' . $fileName,
                                    'updated_at' => now()
                                ]);
                                
                            // Cerrar el modal y mostrar mensaje de éxito
                            $this->closeUploadModal();
                            session()->flash('message', 'La foto de la licencia ha sido actualizada correctamente.');
                            
                            // Recargar los documentos
                            $this->loadDriverData();
                        } else {
                            session()->flash('error', 'No se encontró el archivo temporal.');
                        }
                    } else {
                        session()->flash('error', 'No se encontró la licencia seleccionada.');
                    }
                } catch (\Exception $e) {
                    Log::error('Error al actualizar la foto de la licencia', [
                        'driver_id' => $this->driverId,
                        'license_id' => $this->selectedRecordId,
                        'error' => $e->getMessage()
                    ]);
                    session()->flash('error', 'Error al actualizar la foto de la licencia: ' . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Maneja el evento cuando se sube una imagen de licencia (frontal o trasera)
     * 
     * @param array $data Los datos del archivo subido
     * @return void
     */
    public function handleLicenseImageUploaded($data)
    {
        // Guardar información del archivo temporal
        $this->tempDocumentPath = $data['tempPath'] ?? null;
        $this->tempDocumentName = $data['originalName'] ?? null;
        $this->tempDocumentSize = $data['size'] ?? null;
        
        if ($this->tempDocumentPath && $this->selectedLicenseId) {
            try {
                // Buscar la licencia por ID
                $license = \App\Models\Admin\Driver\DriverLicense::find($this->selectedLicenseId);
                
                if ($license) {
                    // Verificar si el archivo temporal existe
                    if (Storage::exists('public/' . $this->tempDocumentPath)) {
                        // Obtener el contenido del archivo
                        $fileContents = Storage::get('public/' . $this->tempDocumentPath);
                        
                        // Usar Spatie Media Library para guardar la imagen en la colección correspondiente
                        $license->addMediaFromString($fileContents)
                               ->usingFileName($this->tempDocumentName)
                               ->toMediaCollection($this->licenseImageType);
                        
                        // Eliminar el archivo temporal
                        Storage::delete('public/' . $this->tempDocumentPath);
                        
                        // Cerrar el modal y mostrar mensaje de éxito
                        $this->closeUploadModal();
                        
                        $imageTypeText = $this->licenseImageType === 'license_front' ? 'frontal' : 'trasera';
                        session()->flash('message', "La imagen {$imageTypeText} de la licencia ha sido actualizada correctamente.");
                        
                        // Recargar los datos del conductor
                        $this->loadDriverData();
                    } else {
                        session()->flash('error', 'No se encontró el archivo temporal.');
                    }
                } else {
                    session()->flash('error', 'No se encontró la licencia seleccionada.');
                }
            } catch (\Exception $e) {
                Log::error('Error al actualizar la imagen de la licencia', [
                    'driver_id' => $this->driverId,
                    'license_id' => $this->selectedLicenseId,
                    'image_type' => $this->licenseImageType,
                    'error' => $e->getMessage()
                ]);
                session()->flash('error', 'Error al actualizar la imagen de la licencia: ' . $e->getMessage());
            }
        }
    }

    /**
     * Maneja el evento cuando se sube una imagen de tarjeta médica
     * 
     * @param array $data Los datos del archivo subido
     * @return void
     */
    public function handleMedicalImageUploaded($data)
    {
        // Guardar información del archivo temporal
        $this->tempDocumentPath = $data['tempPath'] ?? null;
        $this->tempDocumentName = $data['originalName'] ?? null;
        $this->tempDocumentSize = $data['size'] ?? null;
        
        if ($this->tempDocumentPath && $this->driverId) {
            try {
                // Obtener la calificación médica del conductor
                $medical = $this->driver->medicalQualification;
                
                if ($medical) {
                    // Verificar si el archivo temporal existe
                    if (Storage::exists('public/' . $this->tempDocumentPath)) {
                        // Obtener el contenido del archivo
                        $fileContents = Storage::get('public/' . $this->tempDocumentPath);
                        
                        // Usar Spatie Media Library para guardar la imagen en la colección 'medical_card'
                        $medical->addMediaFromString($fileContents)
                               ->usingFileName($this->tempDocumentName)
                               ->toMediaCollection('medical_card');
                        
                        // Eliminar el archivo temporal
                        Storage::delete('public/' . $this->tempDocumentPath);
                        
                        // Cerrar el modal y mostrar mensaje de éxito
                        $this->closeUploadModal();
                        
                        session()->flash('message', "La imagen de la tarjeta médica ha sido actualizada correctamente.");
                        
                        // Recargar los datos del conductor
                        $this->loadDriverData();
                    } else {
                        session()->flash('error', 'No se encontró el archivo temporal.');
                    }
                } else {
                    session()->flash('error', 'El conductor no tiene una calificación médica registrada.');
                }
            } catch (\Exception $e) {
                Log::error('Error al actualizar la imagen de la tarjeta médica', [
                    'driver_id' => $this->driverId,
                    'error' => $e->getMessage()
                ]);
                session()->flash('error', 'Error al actualizar la imagen de la tarjeta médica: ' . $e->getMessage());
            }
        }
    }

    /**
     * Abre el modal para subir o actualizar la imagen de la tarjeta médica
     * 
     * @return void
     */
    public function editMedicalImage()
    {
        $this->licenseImageType = 'medical_card';
        $this->documentCategory = 'medical';
        $this->documentDescription = 'Imagen de Tarjeta Médica';
        
        $this->showUploadModal = true;
        $this->dispatch('open-license-image-modal');
    }

    // Este componente ya tiene definido el listener para licenseImageUploaded al inicio del archivo
    
    /**
     * Carga los entrenamientos disponibles para asignar al conductor
     */
    public function loadAvailableTrainings()
    {
        $this->availableTrainings = Training::where('status', 'active')->get();
    }

    /**
     * Abre el modal para asignar un nuevo entrenamiento
     */
    public function openTrainingModal()
    {
        $this->selectedTrainingId = null;
        $this->trainingDueDate = null;
        $this->showTrainingModal = true;
        $this->dispatch('open-training-modal');
    }

    /**
     * Cierra el modal de asignación de entrenamiento
     */
    public function closeTrainingModal()
    {
        $this->showTrainingModal = false;
        $this->dispatch('close-training-modal');
    }

    /**
     * Asigna un entrenamiento al conductor
     */
    public function assignTraining()
    {
        // Log al inicio - solo para diagnóstico 
        Log::info('DriverRecruitmentReview::assignTraining - Inicio', [
            'driver_id' => $this->driverId,
            'training_id' => $this->selectedTrainingId,
            'domain' => request()->getHost(),
            'app_url' => config('app.url'),
            'session_domain' => config('session.domain')
        ]);

        $this->validate([
            'selectedTrainingId' => 'required|exists:trainings,id',
            'trainingDueDate' => 'required|date|after:today',
        ]);

        try {
            // Verificar si el entrenamiento ya está asignado al conductor
            $existingAssignment = DriverTraining::where('user_driver_detail_id', $this->driverId)
                ->where('training_id', $this->selectedTrainingId)
                ->where('status', '!=', 'completed')
                ->first();

            if ($existingAssignment) {
                Log::info('DriverRecruitmentReview::assignTraining - Entrenamiento ya asignado', [
                    'driver_id' => $this->driverId,
                    'training_id' => $this->selectedTrainingId
                ]);
                session()->flash('error', 'Este entrenamiento ya está asignado al conductor.');
                return;
            }

            // Crear la asignación de entrenamiento
            Log::info('DriverRecruitmentReview::assignTraining - Creando asignación', [
                'driver_id' => $this->driverId,
                'training_id' => $this->selectedTrainingId,
                'due_date' => $this->trainingDueDate,
                'user_id' => Auth::id() // Registro del ID del usuario autenticado actual
            ]);
            
            DriverTraining::create([
                'user_driver_detail_id' => $this->driverId,
                'training_id' => $this->selectedTrainingId,
                'assigned_date' => now(),
                'due_date' => $this->trainingDueDate,
                'status' => 'assigned',
                // Usar el ID del usuario autenticado actual
                'assigned_by' => Auth::id(),
            ]);

            // Log de éxito
            Log::info('DriverRecruitmentReview::assignTraining - Asignación creada exitosamente', [
                'driver_id' => $this->driverId,
                'training_id' => $this->selectedTrainingId
            ]);

            // Cerrar el modal y mostrar mensaje de éxito
            $this->closeTrainingModal();
            session()->flash('message', 'Entrenamiento asignado correctamente.');
            
            // Recargar los datos del conductor
            $this->loadDriverData();
        } catch (\Exception $e) {
            Log::error('DriverRecruitmentReview::assignTraining - Error', [
                'driver_id' => $this->driverId,
                'training_id' => $this->selectedTrainingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al asignar el entrenamiento: ' . $e->getMessage());
        }
    }

    /**
     * Marca un entrenamiento como completado
     */
    public function completeTraining($trainingAssignmentId)
    {
        try {
            $trainingAssignment = DriverTraining::findOrFail($trainingAssignmentId);
            
            if ($trainingAssignment->user_driver_detail_id != $this->driverId) {
                session()->flash('error', 'No tienes permiso para modificar este entrenamiento.');
                return;
            }
            
            $trainingAssignment->markAsCompleted('Completado desde revisión de reclutamiento');
            
            session()->flash('message', 'Entrenamiento marcado como completado.');
            
            // Recargar los datos del conductor
            $this->loadDriverData();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al completar el entrenamiento: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.driver.recruitment.driver-recruitment-review');        
    }

/**
 * Abre el modal para subir un Record de Manejo
 */
public function editDrivingRecord()
{
    $this->resetErrorBag();
    $this->resetValidation();
    $this->documentFile = null;
    $this->documentDescription = '';
    $this->documentCategory = 'driving_record';
    $this->dispatch('open-driving-record-modal');
}

/**
 * Abre el modal para subir un Record Médico
 */
public function openMedicalRecordModal()
{
    $this->resetErrorBag();
    $this->resetValidation();
    $this->documentFile = null;
    $this->documentDescription = '';
    $this->documentCategory = 'medical_record';
    $this->dispatch('open-medical-record-modal');
}

/**
 * Sube un nuevo Record de Manejo
 */
public function uploadDrivingRecord()
{
    if (!$this->documentFile) {
        session()->flash('error', 'Por favor selecciona un archivo para subir');
        return;
    }

    try {
        // Obtener la extensión original del archivo
        $extension = $this->documentFile->getClientOriginalExtension();
        $customFileName = 'driving_records.' . $extension;
        
        // Guardar el documento con el nombre personalizado
        $media = $this->driver->addMedia($this->documentFile->path())
            ->usingFileName($customFileName) // Especifica el nombre completo del archivo
            ->withCustomProperties([
                'description' => $this->documentDescription,
                'upload_date' => now()->toDateTimeString(),
                'original_name' => $this->documentFile->getClientOriginalName() // Guardamos el nombre original como referencia
            ])
            ->toMediaCollection('driving_records');
        
        // Actualizar checklist
        if (isset($this->checklistItems['driving_record'])) {
            $this->checklistItems['driving_record']['checked'] = true;
        }

        // Limpiar
        $this->documentFile = null;
        $this->documentDescription = '';
        $this->loadDriverData();

        $this->dispatch('close-driving-record-modal');
        session()->flash('message', 'Record de manejo subido exitosamente.');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error al subir el record de manejo', ['error' => $e->getMessage()]);
        session()->flash('error', 'Error al subir el record de manejo: ' . $e->getMessage());
    }
}

/**
 * Elimina un Record de Manejo
 */
public function deleteDrivingRecord($mediaId)
{
    try {
        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
        if ($media) {
            $media->delete();
            $this->loadDriverData();
            
            // Si no quedan documentos, actualizar checklist
            if ($this->driver->getMedia('driving_records')->isEmpty()) {
                $this->checklistItems['driving_record']['checked'] = false;
            }
            
            session()->flash('message', 'Record de manejo eliminado exitosamente.');
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error al eliminar el record de manejo', ['error' => $e->getMessage()]);
        session()->flash('error', 'Error al eliminar el record de manejo: ' . $e->getMessage());
    }
}

/**
 * Abre el modal para subir un Record Criminal
 */
public function editCriminalRecord()
{
    $this->resetErrorBag();
    $this->resetValidation();
    $this->documentFile = null;
    $this->documentDescription = '';
    $this->documentCategory = 'criminal_record';
    $this->dispatch('open-criminal-record-modal');
}

/**
 * Sube un nuevo Record Criminal
 */
public function uploadCriminalRecord()
{
    if (!$this->documentFile) {
        session()->flash('error', 'Por favor selecciona un archivo para subir');
        return;
    }

    try {
        // Obtener la extensión original del archivo
        $extension = $this->documentFile->getClientOriginalExtension();
        $customFileName = 'criminal_records.' . $extension;
        
        // Guardar el documento con el nombre personalizado
        $media = $this->driver->addMedia($this->documentFile->path())
            ->usingFileName($customFileName) // Especifica el nombre completo del archivo
            ->withCustomProperties([
                'description' => $this->documentDescription,
                'upload_date' => now()->toDateTimeString(),
                'original_name' => $this->documentFile->getClientOriginalName() // Guardamos el nombre original como referencia
            ])
            ->toMediaCollection('criminal_records');
        
        // Actualizar checklist
        if (isset($this->checklistItems['criminal_record'])) {
            $this->checklistItems['criminal_record']['checked'] = true;
        }

        // Limpiar
        $this->documentFile = null;
        $this->documentDescription = '';
        $this->loadDriverData();
        
        $this->dispatch('close-criminal-record-modal');
        session()->flash('message', 'Record criminal subido exitosamente.');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error al subir el record criminal', ['error' => $e->getMessage()]);
        session()->flash('error', 'Error al subir el record criminal: ' . $e->getMessage());
    }
}

/**
 * Sube un nuevo Record Médico
 */
public function uploadMedicalRecord()
{
    if (!$this->documentFile) {
        session()->flash('error', 'Por favor selecciona un archivo para subir');
        return;
    }

    try {
        // Obtener la extensión original del archivo
        $extension = $this->documentFile->getClientOriginalExtension();
        $customFileName = 'medical_records.' . $extension;
        
        // Guardar el documento con el nombre personalizado
        $media = $this->driver->addMedia($this->documentFile->path())
            ->usingFileName($customFileName) // Especifica el nombre completo del archivo
            ->withCustomProperties([
                'description' => $this->documentDescription,
                'upload_date' => now()->toDateTimeString(),
                'original_name' => $this->documentFile->getClientOriginalName() // Guardamos el nombre original como referencia
            ])
            ->toMediaCollection('medical_records');
        
        // Actualizar checklist
        if (isset($this->checklistItems['medical_record'])) {
            $this->checklistItems['medical_record']['checked'] = true;
        }

        // Limpiar
        $this->documentFile = null;
        $this->documentDescription = '';
        $this->loadDriverData();
        
        $this->dispatch('close-medical-record-modal');
        session()->flash('message', 'Record médico subido exitosamente.');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error al subir el record médico', ['error' => $e->getMessage()]);
        session()->flash('error', 'Error al subir el record médico: ' . $e->getMessage());
    }
}

/**
 * Elimina un Record Criminal
 */
public function deleteCriminalRecord($mediaId)
{
    try {
        DB::table('media')->where('id', $mediaId)->delete();
        
        // Actualizar checklist
        if (isset($this->checklistItems['criminal_record'])) {
            $this->checklistItems['criminal_record']['checked'] = false;
        }

        $this->loadDriverData();
        session()->flash('message', 'Record criminal eliminado exitosamente.');
    } catch (\Exception $e) {
        Log::error('Error al eliminar el record criminal', ['error' => $e->getMessage()]);
        session()->flash('error', 'Error al eliminar el record criminal: ' . $e->getMessage());
    }
}

/**
 * Elimina un Record Médico
 */
public function deleteMedicalRecord($mediaId)
{
    try {
        DB::table('media')->where('id', $mediaId)->delete();
        
        // Actualizar checklist
        if (isset($this->checklistItems['medical_record'])) {
            $this->checklistItems['medical_record']['checked'] = false;
        }

        $this->loadDriverData();
        session()->flash('message', 'Record médico eliminado exitosamente.');
    } catch (\Exception $e) {
        Log::error('Error al eliminar el record médico', ['error' => $e->getMessage()]);
        session()->flash('error', 'Error al eliminar el record médico: ' . $e->getMessage());
    }
}

/**
 * Sube un nuevo Clearing House
 */
public function uploadClearingHouse()
{
    if (!$this->documentFile) {
        session()->flash('error', 'Por favor selecciona un archivo para subir');
        return;
    }

    try {
        // Obtener la extensión original del archivo
        $extension = $this->documentFile->getClientOriginalExtension();
        $customFileName = 'clearing_house.' . $extension;
        
        // Guardar el documento con el nombre personalizado
        $media = $this->driver->addMedia($this->documentFile->path())
            ->usingFileName($customFileName) // Especifica el nombre completo del archivo
            ->withCustomProperties([
                'description' => $this->documentDescription,
                'upload_date' => now()->toDateTimeString(),
                'original_name' => $this->documentFile->getClientOriginalName() // Guardamos el nombre original como referencia
            ])
            ->toMediaCollection('clearing_house');
        
        // Actualizar checklist
        if (isset($this->checklistItems['clearing_house'])) {
            $this->checklistItems['clearing_house']['checked'] = true;
        }

        // Limpiar
        $this->documentFile = null;
        $this->documentDescription = '';
        $this->loadDriverData();
        
        $this->dispatch('close-clearing-house-modal');
        session()->flash('message', 'Clearing House subido exitosamente.');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error al subir el Clearing House', ['error' => $e->getMessage()]);
        session()->flash('error', 'Error al subir el Clearing House: ' . $e->getMessage());
    }
}

/**
 * Elimina un Clearing House
 */
public function deleteClearingHouse($mediaId)
{
    try {
        DB::table('media')->where('id', $mediaId)->delete();
        
        // Actualizar checklist
        if (isset($this->checklistItems['clearing_house'])) {
            $this->checklistItems['clearing_house']['checked'] = false;
        }

        $this->loadDriverData();
        session()->flash('message', 'Clearing House eliminado exitosamente.');
    } catch (\Exception $e) {
        Log::error('Error al eliminar el Clearing House', ['error' => $e->getMessage()]);
        session()->flash('error', 'Error al eliminar el Clearing House: ' . $e->getMessage());
    }
}

/**
 * Abre el modal para subir Clearing House
 */
public function openClearingHouseModal()
{
    $this->documentFile = null;
    $this->documentDescription = '';
    $this->dispatch('open-clearing-house-modal');
}

}