<?php

namespace App\Livewire\Admin\Driver\Recruitment;

use ZipArchive;
use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
    public $requestedDocuments = [];
    public $additionalRequirements = '';
    public $completionPercentage = 0;
    public $verificationNotes = '';
    public $savedVerification = null;

    // Nueva propiedad para PDFs generados
    public $generatedPdfs = [];

    public function mount($driverId)
    {
        $this->driverId = $driverId;
        $this->loadDriverData();
        $this->initializeChecklist(); // Primero inicializa con valores predeterminados
        $this->loadLastVerification(); // Luego carga y aplica los valores guardados
        $this->loadGeneratedPdfs();
    }

    public function toggleChecklistItem($item)
    {
        if (isset($this->checklistItems[$item])) {
            // Just change the checked value directly - don't toggle since wire:model already did that
            $this->checklistItems[$item]['checked'] = !$this->checklistItems[$item]['checked'];
        }
    }

    public function initializeChecklist()
    {
        // Define the elements the recruiter should verify
        $this->checklistItems = [
            'general_info' => [
                'checked' => false,
                'label' => 'Información general completa y válida'
            ],
            'contact_info' => [
                'checked' => false,
                'label' => 'Información de contacto verificada'
            ],
            'address_info' => [
                'checked' => false,
                'label' => 'Dirección actual y historial validados'
            ],
            'license_info' => [
                'checked' => false,
                'label' => 'Licencia de conducir válida y vigente'
            ],
            'license_image' => [
                'checked' => false,
                'label' => 'Imágenes de licencia adjuntas y legibles'
            ],
            'medical_info' => [
                'checked' => false,
                'label' => 'Información médica completa'
            ],
            'medical_image' => [
                'checked' => false,
                'label' => 'Tarjeta médica adjunta y vigente'
            ],
            'experience_info' => [
                'checked' => false,
                'label' => 'Experiencia de conducción verificada'
            ],
            'history_info' => [
                'checked' => false,
                'label' => 'Historial laboral completo (10 años)'
            ],
            'criminal_check' => [
                'checked' => false,
                'label' => 'Verificación de antecedentes penales'
            ],
            'documents_checked' => [
                'checked' => false,
                'label' => 'Todos los documentos revisados y validados'
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
        $this->driver = UserDriverDetail::with([
            'user',
            'carrier',
            'application.details',
            'licenses',
            'medicalQualification',
            'experiences',
            'trainingSchools', // Verifica que este relationship esté definido correctamente
            'trafficConvictions', // Verifica que este relationship esté definido correctamente
            'accidents', 
            'fmcsrData',
            'workHistories',
            'unemploymentPeriods',
            'criminalHistory',
            'companyPolicy',
            'certification'
        ])->findOrFail($this->driverId);

        Log::info('Driver data loaded', [
            'driver_id' => $this->driver->id,
            'training_schools_count' => $this->driver->trainingSchools->count(),
            'training_schools' => $this->driver->trainingSchools->toArray(),
            'traffic_convictions_count' => $this->driver->trafficConvictions->count(),
            'traffic_convictions' => $this->driver->trafficConvictions->toArray(),
            'accidents_count' => $this->driver->accidents->count(),
            'accidents' => $this->driver->accidents->toArray(),
            'application_details' => $this->driver->application ? $this->driver->application->details : null
        ]);

        // Convertir date_of_birth a objeto Carbon si es una string
        if ($this->driver->date_of_birth && is_string($this->driver->date_of_birth)) {
            $this->driver->date_of_birth = Carbon::parse($this->driver->date_of_birth);
        }

        // Procesar otras fechas que puedan necesitar conversión a Carbon
        $this->processDateFields();

        $this->application = $this->driver->application;

        // Cargar datos de solicitud si existen
        if ($this->application) {
            $this->rejectionReason = $this->application->rejection_reason ?? '';
            $this->requestedDocuments = json_decode($this->application->requested_documents, true) ?: [];
            $this->additionalRequirements = $this->application->additional_requirements ?? '';
        }

        // Cargar estados de los pasos
        $stepService = new DriverStepService();
        $this->stepsStatus = $stepService->getStepsStatus($this->driver);
        $this->completionPercentage = $stepService->calculateCompletionPercentage($this->driver);

        // Cargar la verificación más reciente si existe
        $this->loadLastVerification();

        // Extraer los valores del checklistItems
        $checklistValues = [];
        foreach ($this->checklistItems as $key => $item) {
            $checklistValues[$key] = $item['checked'];
        }
    
        // Cargar estados de los pasos considerando el checklist
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
     * Carga la verificación más reciente del reclutador
     */
    protected function loadLastVerification()
    {
        if ($this->application) {
            $verification = DriverRecruitmentVerification::where('driver_application_id', $this->application->id)
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
                    $this->generatedPdfs['combined'] = [
                        'name' => 'Solicitud Completa',
                        'url' => asset("storage/{$basePath}solicitud_completa.pdf")
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
                            $this->generatedPdfs[$file] = [
                                'name' => $name,
                                'url' => asset("storage/{$appSubPath}{$file}")
                            ];
                        }
                    }
                }
            }
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
    
        // Update or create verification in database (instead of always creating new records)
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
    
        // Recargar los estados para reflejar la nueva verificación
        $stepService = new DriverStepService();
        $checklistValues = [];
        foreach ($this->checklistItems as $key => $item) {
            $checklistValues[$key] = $item['checked'];
        }
        $this->stepsStatus = $stepService->getStepsStatus($this->driver, $checklistValues);
        $this->completionPercentage = $stepService->calculateCompletionPercentage($this->driver);
    
        session()->flash('message', 'Verificación guardada correctamente.');
    }

    public function requestAdditionalDocuments()
    {
        $this->validate([
            'requestedDocuments' => 'array',
            'additionalRequirements' => 'nullable|string'
        ]);

        // Actualizar la aplicación con los documentos solicitados
        $this->application->update([
            'requested_documents' => json_encode($this->requestedDocuments),
            'additional_requirements' => $this->additionalRequirements,
            'status' => 'pending' // Mantener en pendiente hasta que se completen los requisitos
        ]);

        // Opcionalmente, enviar notificación al conductor
        // Notification::send($this->driver->user, new AdditionalDocumentsRequestedNotification(...));

        session()->flash('message', 'Solicitud de documentos adicionales enviada al conductor.');
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

        // Actualizar estado del driver
        $this->driver->update([
            'status' => UserDriverDetail::STATUS_ACTIVE
        ]);

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
    $addFilesToZip = function($dir, $zipBasePath = '') use ($zip, &$addFilesToZip) {
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
