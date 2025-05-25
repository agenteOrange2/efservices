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

class DriverRecruitmentReview extends Component
{
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
     * Carga los PDFs generados para la aplicación
     */
    protected function loadGeneratedPdfs()
    {
        $this->generatedPdfs = [];

        if ($this->driver && $this->driver->id) {
            $basePath = "driver/{$this->driver->id}/";
            $fullPath = storage_path("app/public/{$basePath}");

            // Comprobar si el directorio existe
            if (file_exists($fullPath)) {
                // Buscar PDF combinado
                if (file_exists("{$fullPath}solicitud_completa.pdf")) {
                    $fileSize = $this->formatFileSize(filesize("{$fullPath}solicitud_completa.pdf"));
                    $fileDate = $this->formatFileDate(filemtime("{$fullPath}solicitud_completa.pdf"));
                    
                    $this->generatedPdfs['combined'] = [
                        'name' => 'Solicitud Completa',
                        'url' => asset("storage/{$basePath}solicitud_completa.pdf"),
                        'size' => $fileSize,
                        'date' => $fileDate
                    ];
                }

                // Buscar PDFs individuales en el subdirectorio
                $appSubPath = "{$basePath}driver_applications/";
                $appFullPath = storage_path("app/public/{$appSubPath}");

                if (file_exists($appFullPath)) {
                    // Definir los archivos a buscar y sus nombres legibles
                    $pdfFiles = [
                        'informacion_general.pdf' => 'Información General',
                        'informacion_direccion.pdf' => 'Información de Dirección',
                        'detalles_aplicacion.pdf' => 'Detalles de Aplicación',
                        'informacion_licencias.pdf' => 'Licencias',
                        'calificacion_medica.pdf' => 'Calificación Médica',
                        'escuelas_entrenamiento.pdf' => 'Entrenamiento',
                        'infracciones_trafico.pdf' => 'Infracciones de Tráfico',
                        'registro_accidentes.pdf' => 'Registro de Accidentes',
                        'requisitos_fmcsr.pdf' => 'Requisitos FMCSR',
                        'historial_empleo.pdf' => 'Historial de Empleo',
                        'certificacion.pdf' => 'Certificación',
                    ];

                    foreach ($pdfFiles as $file => $name) {
                        if (file_exists("{$appFullPath}{$file}")) {
                            $fileSize = $this->formatFileSize(filesize("{$appFullPath}{$file}"));
                            $fileDate = $this->formatFileDate(filemtime("{$appFullPath}{$file}"));
                            
                            $this->generatedPdfs[$file] = [
                                'name' => $name,
                                'url' => asset("storage/{$appSubPath}{$file}") . "?v=" . time(),
                                'size' => $fileSize,
                                'date' => $fileDate
                            ];
                        }
                    }
                }
                
                // Buscar documentos de verificación de vehículos independientemente del tipo de conductor
                $vehicleVerificationsPath = "{$basePath}vehicle_verifications/";
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
                            'name' => 'Consentimiento del Propietario (Third Party)',
                            'url' => asset("storage/{$vehicleVerificationsPath}{$consentFileName}") . "?v=" . time(),
                            'size' => $fileSize,
                            'date' => $fileDate
                        ];
                    }
                    
                    // Buscar los archivos de lease agreement para conductores de terceros (con formato de nombre que incluye timestamp)
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
                            'date' => $fileDate
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
                            'date' => $fileDate
                        ];
                    }
                }

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

            // Generar los PDFs
            $this->generateApplicationPDFs($this->driver, $signaturePath);
            
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
     * Prepara la firma para ser usada en los PDFs
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
            ['view' => 'pdf.driver.general', 'filename' => 'informacion_general.pdf', 'title' => 'Información General'],
            ['view' => 'pdf.driver.address', 'filename' => 'informacion_direccion.pdf', 'title' => 'Información de Dirección'],
            ['view' => 'pdf.driver.application', 'filename' => 'detalles_aplicacion.pdf', 'title' => 'Detalles de Aplicación'],
            ['view' => 'pdf.driver.licenses', 'filename' => 'informacion_licencias.pdf', 'title' => 'Licencias'],
            ['view' => 'pdf.driver.medical', 'filename' => 'calificacion_medica.pdf', 'title' => 'Calificación Médica'],
            ['view' => 'pdf.driver.training', 'filename' => 'escuelas_entrenamiento.pdf', 'title' => 'Entrenamiento'],
            ['view' => 'pdf.driver.traffic', 'filename' => 'infracciones_trafico.pdf', 'title' => 'Infracciones de Tráfico'],
            ['view' => 'pdf.driver.accident', 'filename' => 'registro_accidentes.pdf', 'title' => 'Registro de Accidentes'],
            ['view' => 'pdf.driver.fmcsr', 'filename' => 'requisitos_fmcsr.pdf', 'title' => 'Requisitos FMCSR'],
            ['view' => 'pdf.driver.employment', 'filename' => 'historial_empleo.pdf', 'title' => 'Historial de Empleo'],
            ['view' => 'pdf.driver.certification', 'filename' => 'certificacion.pdf', 'title' => 'Certificación'],
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
            $pdf = \Barryvdh\DomPDF\Facade\PDF::loadView('pdf.driver.solicitud_completa', [
                'userDriverDetail' => $userDriverDetail,
                'signaturePath' => $signaturePath,
                'date' => now()->format('d/m/Y')
            ]);
            
            // Asegurarnos de que estamos usando el ID correcto
            $driverId = $userDriverDetail->id;
            $filePath = 'driver/' . $driverId . '/solicitud_completa.pdf';
            
            Log::info('Guardando PDF combinado para conductor', ['driver_id' => $driverId, 'file_path' => $filePath]);
            
            // Guardar el PDF combinado usando Storage
            $pdfContent = $pdf->output();
            Storage::disk('public')->put($filePath, $pdfContent);
            
            // Guardar PDF temporalmente para adjuntarlo a MediaLibrary
            $tempPath = tempnam(sys_get_temp_dir(), 'solicitud_completa_') . '.pdf';
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
            Log::error('Error generando PDF combinado', [
                'driver_id' => $userDriverDetail->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }


    public function changeTab($tab)
    {
        $this->currentTab = $tab;
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
            $this->addError('checklist', 'Debe completar toda la lista de verificación antes de aprobar.');
            return;
        }

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
    }

    public function rejectApplication()
    {
        // Validar razón de rechazo
        $this->validate([
            'rejectionReason' => 'required|min:10'
        ], [
            'rejectionReason.required' => 'Debe proporcionar una razón para el rechazo.',
            'rejectionReason.min' => 'La razón debe tener al menos 10 caracteres.'
        ]);

        // Actualizar estado de la aplicación a rechazado
        $this->application->update([
            'status' => DriverApplication::STATUS_REJECTED,
            'rejection_reason' => $this->rejectionReason,
            'completed_at' => now()
        ]);

        // Opcional: Enviar notificación al conductor
        // Notification::send($this->driver->user, new DriverApplicationRejectedNotification($this->driver, $this->rejectionReason));

        // Actualizar datos locales
        $this->loadDriverData();

        // Notificar a otros componentes
        $this->dispatch('applicationStatusUpdated');

        // Limpiar el campo de razón
        $this->rejectionReason = '';

        // Mostrar mensaje
        session()->flash('message', 'La solicitud ha sido rechazada.');
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

    public function render()
    {
        return view('livewire.admin.driver.recruitment.driver-recruitment-review');
    }
}
