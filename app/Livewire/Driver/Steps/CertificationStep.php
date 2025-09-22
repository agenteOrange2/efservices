<?php
namespace App\Livewire\Driver\Steps;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverCertification;
use App\Models\Admin\Vehicle\Vehicle;

class CertificationStep extends Component
{
    // Propiedades
    public $driverId;
    public $employmentHistory = [];
    public $signature = '';
    public $signature_token = '';
    public $certificationAccepted = false;
    
    // Validación
    protected function rules()
    {
        return [
            'signature' => 'required|string',
            'certificationAccepted' => 'accepted'
        ];
    }
    
    // Inicialización
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        if ($this->driverId) {
            $this->loadEmploymentData();
        }
    }
    
    // Cargar datos de empleo
    protected function loadEmploymentData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }
        
        // Cargar historial de empleo completo
        $companies = $userDriverDetail->employmentCompanies()
            ->orderBy('employed_from', 'desc')
            ->get();
            
        $this->employmentHistory = [];
        foreach ($companies as $company) {
            $this->employmentHistory[] = [
                'company_name' => $company->company_name ?? ($company->masterCompany ? 
                    $company->masterCompany->company_name : 'N/A'),
                'address' => $company->address ?? ($company->masterCompany ? 
                    $company->masterCompany->address : 'N/A'),
                'city' => $company->city ?? ($company->masterCompany ? 
                    $company->masterCompany->city : 'N/A'),
                'state' => $company->state ?? ($company->masterCompany ? 
                    $company->masterCompany->state : 'N/A'),
                'zip' => $company->zip ?? ($company->masterCompany ? 
                    $company->masterCompany->zip : 'N/A'),
                'employed_from' => $company->employed_from ? $company->employed_from->format('M d, Y') : 'N/A',
                'employed_to' => $company->employed_to ? $company->employed_to->format('M d, Y') : 'Present'
            ];
        }
        
        // Cargar certificación previa si existe
        $certification = $userDriverDetail->certification;
        if ($certification) {
            // Si hay firma en la base de datos
            $this->signature = $certification->signature;
            $this->certificationAccepted = (bool)$certification->is_accepted;
        }
    }
    
    /**
     * Load criminal history data for PDF generation
     * @param UserDriverDetail $userDriverDetail
     * @return array
     */
    protected function loadCriminalHistoryData($userDriverDetail = null)
    {
        // If no parameter provided, use the current driver
        if (!$userDriverDetail) {
            $userDriverDetail = UserDriverDetail::find($this->driverId);
        }
        
        $criminalData = [
            'has_criminal_charges' => false,
            'has_felony_conviction' => false,
            'has_minister_permit' => false,
            'fcra_consent' => false,
            'background_info_consent' => false
        ];

        if ($userDriverDetail && $userDriverDetail->criminalHistory) {
            $criminal = $userDriverDetail->criminalHistory;
            $criminalData = [
                'has_criminal_charges' => $criminal->has_criminal_charges ?? false,
                'has_felony_conviction' => $criminal->has_felony_conviction ?? false,
                'has_minister_permit' => $criminal->has_minister_permit ?? false,
                'fcra_consent' => $criminal->fcra_consent ?? false,
                'background_info_consent' => $criminal->background_info_consent ?? false
            ];
        }

        return $criminalData;
    }
    
    // Guardar certificación
    public function saveCertification()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }
            
            // Guardar certificación
            $certification = $userDriverDetail->certification()->updateOrCreate(
                [],
                [
                    'signature' => $this->signature,
                    'is_accepted' => $this->certificationAccepted,
                    'signed_at' => now()
                ]
            );
            
            // Procesar la firma
            if (!empty($this->signature_token)) {
                Log::info('Procesando firma con token', [
                    'driver_id' => $this->driverId,
                    'token' => $this->signature_token,
                    'session_id' => session()->getId(),
                    'temp_files' => array_keys(session('temp_files', []))
                ]);
                
                // Usar el servicio de carga temporal para mover el archivo
                $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                $tempPath = $tempUploadService->moveToPermanent($this->signature_token);
                
                // Si no se encuentra en la sesión, intentar buscarlo directamente
                if (!$tempPath || !file_exists($tempPath)) {
                    // Buscar en el almacenamiento
                    $tempFiles = session('temp_files', []);
                    Log::info('Buscando archivo en temp_files', ['temp_files' => $tempFiles]);
                    
                    // Si no podemos encontrarlo en la sesión, intentamos buscarlo directamente en el storage
                    $possiblePaths = [
                        storage_path('app/temp/signature'),
                        storage_path('app/temp')
                    ];
                    
                    foreach ($possiblePaths as $dir) {
                        if (is_dir($dir)) {
                            $files = scandir($dir);
                            Log::info('Archivos en directorio', ['dir' => $dir, 'files' => $files]);
                            
                            // Buscar archivos recientes
                            foreach ($files as $file) {
                                if ($file != '.' && $file != '..' && is_file($dir . '/' . $file)) {
                                    // Si el archivo fue creado en las útimas 24 horas, lo usamos
                                    if (filemtime($dir . '/' . $file) > time() - 86400) {
                                        $tempPath = $dir . '/' . $file;
                                        Log::info('Encontrado archivo reciente', ['path' => $tempPath]);
                                        break 2; // Salir de ambos bucles
                                    }
                                }
                            }
                        }
                    }
                }
                
                if ($tempPath && file_exists($tempPath)) {
                    // Guardar en media library
                    $certification->clearMediaCollection('signature');
                    $certification->addMedia($tempPath)
                        ->toMediaCollection('signature');
                    Log::info('Firma añadida a media collection');
                } else {
                    Log::error('No se pudo procesar la firma - archivo no encontrado');
                    
                    // Como respaldo, si tenemos la firma en base64, guardarla directamente
                    if (!empty($this->signature) && strpos($this->signature, 'data:image') === 0) {
                        // Convertir base64 a archivo
                        $signatureData = base64_decode(explode(',', $this->signature)[1]);
                        $tempFile = tempnam(sys_get_temp_dir(), 'signature_') . '.png';
                        file_put_contents($tempFile, $signatureData);
                        
                        // Guardar en media library
                        $certification->clearMediaCollection('signature');
                        $certification->addMedia($tempFile)
                            ->toMediaCollection('signature');
                        Log::info('Firma guardada desde base64 como respaldo');
                    }
                }
            } elseif (!empty($this->signature) && strpos($this->signature, 'data:image') === 0) {
                // Si no tenemos token pero tenemos la firma en base64, guardarla directamente
                $signatureData = base64_decode(explode(',', $this->signature)[1]);
                $tempFile = tempnam(sys_get_temp_dir(), 'signature_') . '.png';
                file_put_contents($tempFile, $signatureData);
                
                // Guardar en media library
                $certification->clearMediaCollection('signature');
                $certification->addMedia($tempFile)
                    ->toMediaCollection('signature');
                Log::info('Firma guardada desde base64');
                @unlink($tempFile);
            }
            
            // Marcar como completado
            $userDriverDetail->update([
                'current_step' => 13,
                'application_completed' => true
            ]);
            
            DB::commit();
            session()->flash('success', 'Application completed successfully!');
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving certification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error saving certification: ' . $e->getMessage());
            return false;
        }
    }
    
    // Método para completar la aplicación
    public function complete()
    {
        $this->validate();
    
        if ($this->driverId) {
            if ($this->saveCertification()) {
                try {
                    DB::beginTransaction();
                    
                    // Obtener el driver detail
                    $userDriverDetail = UserDriverDetail::find($this->driverId);
                    if (!$userDriverDetail) {
                        throw new \Exception('Driver not found');
                    }
                    
                    // Marcar el driver como completado
                    $userDriverDetail->update([
                        'application_completed' => true,
                        'current_step' => 13 // Este es el último paso
                    ]);
                    
                    // Actualizar el estado de la aplicación a pendiente
                    if ($userDriverDetail->application) {
                        $userDriverDetail->application->update([
                            'status' => DriverApplication::STATUS_PENDING,
                            'completed_at' => now() // Asegúrate de haber agregado este campo
                        ]);
                    }
                    
                    // Generar PDFs solo si existe la firma
                    if (!empty($this->signature)) {
                        $this->generateApplicationPDFs($userDriverDetail);
                    }
                    
                    DB::commit();
                    
                    // Avanzar al siguiente paso (en lugar de redireccionar)
                    $this->dispatch('nextStep');
                
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Error al completar la aplicación', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    session()->flash('error', 'Error al completar la solicitud: ' . $e->getMessage());
                }
            }
        }
    }
    
    // Métodos de navegación
    public function previous()
    {
        $this->dispatch('prevStep');
    }
    
    public function saveAndExit()
    {
        if ($this->driverId) {
            $this->saveCertification();
        }
        
        $this->dispatch('saveAndExit');
    }
    
    /**
     * Generar archivos PDF para cada paso de la aplicación
     * @param UserDriverDetail $userDriverDetail
     */
    private function generateApplicationPDFs(UserDriverDetail $userDriverDetail)
    {
        // CORRECCIÓN: Cargar TODAS las relaciones necesarias incluyendo relaciones anidadas
        $userDriverDetail->load([
            'user',
            'carrier',
            'application.details',
            'application.addresses',
            'application.ownerOperatorDetail',
            'licenses.endorsements',
            'medicalQualification',
            'experiences',
            'employmentCompanies.masterCompany', // AGREGADO: Cargar relación anidada masterCompany
            'unemploymentPeriods',
            'workHistories', // AGREGADO: Cargar historial de trabajo (Other Employment)
            'driver_related_employments', // CORREGIDO: Usar driver_related_employments en lugar de relatedEmployments
            'trainingSchools',
            'courses', // AGREGADO: Cargar cursos de driver_courses
            'trafficConvictions',
            'accidents',
            'fmcsrData',
            'criminalHistory',
            'certification',
            'addresses' // AGREGADO: Cargar direcciones directamente del UserDriverDetail
        ]);
        
        // CORRECCIÓN 2: Validar que los datos críticos existen
        $missingData = [];
        
        if (!$userDriverDetail->user) {
            $missingData[] = 'user';
        }
        
        if (!$userDriverDetail->application) {
            $missingData[] = 'application';
        }
        
        if (!$userDriverDetail->carrier) {
            $missingData[] = 'carrier';
        }
        
        // Si faltan datos críticos, registrar error y salir
        if (!empty($missingData)) {
            Log::error('Datos críticos faltantes para generar PDFs', [
                'driver_id' => $userDriverDetail->id,
                'missing_data' => $missingData
            ]);
            return;
        }
        
        // Verificar que tenemos la firma
        if (empty($this->signature)) {
            Log::warning('No se puede generar PDFs sin firma', [
                'driver_id' => $userDriverDetail->id
            ]);
            return;
        }
        
        // Preparar la firma una sola vez para todos los PDFs
        $signaturePath = $this->prepareSignatureForPDF($this->signature);
    
        if (!$signaturePath) {
            Log::error('No se pudo preparar la firma para PDFs', [
                'driver_id' => $userDriverDetail->id
            ]);
            return;
        }
    
        Log::info('Firma preparada para PDFs', [
            'driver_id' => $userDriverDetail->id,
            'signature_path' => $signaturePath,
            'loaded_relations' => array_keys($userDriverDetail->getRelations())
        ]);
        
        // CORRECCIÓN 3: Verificar y crear directorios con manejo de errores
        $driverPath = 'driver/' . $userDriverDetail->id;
        $appSubPath = $driverPath . '/driver_applications';
        
        try {
            // Crear directorios si no existen
            if (!Storage::disk('public')->exists($driverPath)) {
                Storage::disk('public')->makeDirectory($driverPath);
                Log::info('Directorio creado', ['path' => $driverPath]);
            }
            
            if (!Storage::disk('public')->exists($appSubPath)) {
                Storage::disk('public')->makeDirectory($appSubPath);
                Log::info('Subdirectorio creado', ['path' => $appSubPath]);
            }
            
            // Verificar permisos de escritura
            $testFile = $appSubPath . '/test_write.txt';
            Storage::disk('public')->put($testFile, 'test');
            Storage::disk('public')->delete($testFile);
            Log::info('Permisos de escritura verificados', ['path' => $appSubPath]);
            
        } catch (\Exception $e) {
            Log::error('Error creando directorios o verificando permisos', [
                'driver_id' => $userDriverDetail->id,
                'error' => $e->getMessage(),
                'path' => $appSubPath
            ]);
            throw new \Exception('No se pueden crear directorios para PDFs: ' . $e->getMessage());
        }
        
        // Configuraciones de pasos - definir la vista y nombre de archivo para cada paso
        $steps = [
            ['view' => 'pdf.driver.general', 'filename' => 'general_information.pdf', 'title' => 'General Information'],
            ['view' => 'pdf.driver.address', 'filename' => 'address_information.pdf', 'title' => 'Address Information'],
            ['view' => 'pdf.driver.application', 'filename' => 'application_details.pdf', 'title' => 'Application Details'],
            ['view' => 'pdf.driver.licenses', 'filename' => 'drivers_licenses.pdf', 'title' => 'Drivers Licenses'],
            ['view' => 'pdf.driver.medical', 'filename' => 'calificacion_medica.pdf', 'title' => 'Medical Qualification'],
            ['view' => 'pdf.driver.training', 'filename' => 'training_schools.pdf', 'title' => 'Training Schools'],
            ['view' => 'pdf.driver.courses', 'filename' => 'driver_courses.pdf', 'title' => 'Driver Courses'], // AGREGADO: PDF para cursos de driver_courses
            ['view' => 'pdf.driver.traffic', 'filename' => 'traffic_violations.pdf', 'title' => 'Traffic Violations'],
            ['view' => 'pdf.driver.accident', 'filename' => 'accident_record.pdf', 'title' => 'Accident Record'],
            ['view' => 'pdf.driver.criminal_history', 'filename' => 'criminal_history_investigation.pdf', 'title' => 'Criminal History Investigation'], // AGREGADO: PDF para criminal history
            ['view' => 'pdf.driver.fmcsr', 'filename' => 'fmcsr_requirements.pdf', 'title' => 'FMCSR Requirements'],
            ['view' => 'pdf.driver.employment', 'filename' => 'employment_history.pdf', 'title' => 'Employment History'],
            ['view' => 'pdf.driver.certification', 'filename' => 'certification.pdf', 'title' => 'Certification'],
        ];
        
        // CORRECCIÓN 4: Generar PDF para cada paso con validación de vista
        $successfulPdfs = 0;
        $totalPdfs = count($steps);
        
        foreach ($steps as $step) {
            try {
                // Verificar que la vista existe
                if (!view()->exists($step['view'])) {
                    Log::warning('Vista PDF no encontrada', [
                        'driver_id' => $userDriverDetail->id,
                        'view' => $step['view'],
                        'filename' => $step['filename']
                    ]);
                    continue;
                }
                
                // CORRECCIÓN 5: Preparar datos formateados para el PDF
                // Construir el nombre completo usando la misma lógica que general.blade.php
                $fullName = ($userDriverDetail->user->name ?? 'N/A') . ' ' . 
                           ($userDriverDetail->middle_name ?? '') . ' ' . 
                           ($userDriverDetail->last_name ?? 'N/A');
                $fullName = trim(preg_replace('/\s+/', ' ', $fullName)); // Limpiar espacios extra
                
                $pdfData = [
                    'userDriverDetail' => $userDriverDetail,
                    'signaturePath' => $signaturePath,
                    'signature' => $signaturePath,
                    'fullName' => $fullName, // AGREGADO: Nombre completo construido correctamente
                    'title' => $step['title'],
                    'date' => now()->format('d/m/Y'),
                    'currentDate' => now()->format('m/d/Y'),
                    // Formatear fechas específicas para PDFs
                    'formatted_dates' => [
                        'date_of_birth' => $userDriverDetail->date_of_birth ? $userDriverDetail->date_of_birth->format('m/d/Y') : '',
                        'hire_date' => optional($userDriverDetail->application->details)->hire_date ?? '',
                        'medical_expiry' => optional($userDriverDetail->medicalQualification)->expiration_date ?? '' // CORREGIDO: era medicalQualifications->first()
                    ],
                    // Datos adicionales para evitar errores en vistas
                    'user_name' => $userDriverDetail->user->name ?? 'N/A',
                    'user_email' => $userDriverDetail->user->email ?? 'N/A',
                    'carrier_name' => $userDriverDetail->carrier->name ?? 'N/A',
                    // Datos para criminal history
                    'criminalHistory' => [
                        'has_criminal_charges' => $userDriverDetail->has_criminal_charges ?? false,
                        'has_felony_conviction' => $userDriverDetail->has_felony_conviction ?? false,
                        'has_minister_permit' => $userDriverDetail->has_minister_permit ?? false,
                        'fcra_consent' => $userDriverDetail->fcra_consent ?? false,
                        'background_info_consent' => $userDriverDetail->background_info_consent ?? false
                    ],
                    'carrier' => $userDriverDetail->carrier
                ];
                
                $pdf = App::make('dompdf.wrapper')->loadView($step['view'], $pdfData);
                
                // Guardar PDF usando Storage para evitar problemas de permisos
                $pdfContent = $pdf->output();
                Storage::disk('public')->put($appSubPath . '/' . $step['filename'], $pdfContent);
                
                $successfulPdfs++;
                
                Log::info('PDF individual generado exitosamente', [
                    'driver_id' => $userDriverDetail->id,
                    'filename' => $step['filename'],
                    'view' => $step['view'],
                    'file_size' => strlen($pdfContent)
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error generando PDF individual', [
                    'driver_id' => $userDriverDetail->id,
                    'filename' => $step['filename'],
                    'view' => $step['view'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        Log::info('Resumen de generación de PDFs individuales', [
            'driver_id' => $userDriverDetail->id,
            'successful' => $successfulPdfs,
            'total' => $totalPdfs,
            'success_rate' => round(($successfulPdfs / $totalPdfs) * 100, 2) . '%'
        ]);
        // Generar el PDF combinado primero (completa_aplicacion.pdf o solicitud.pdf)
        if (view()->exists('pdf.driver.complete_application')) {
            $this->generateCombinedPDF($userDriverDetail, $signaturePath);
        } else {
            Log::info('No se generó el PDF combinado porque la vista no existe', [
                'driver_id' => $userDriverDetail->id
            ]);
        }
        
        // Generar documentos específicos según el tipo de conductor
        $application = $userDriverDetail->application;
        
        // Obtener los detalles de la aplicación para verificar el tipo
        $applicationDetails = $application ? $application->details : null;
        if (!$applicationDetails) {
            Log::error('No se encontraron detalles de aplicación para generar contrato', [
                'driver_id' => $userDriverDetail->id,
                'application_id' => $application ? $application->id : 'null'
            ]);
        } else {
            $applyingPosition = $applicationDetails->applying_position ?? 'unknown';
            Log::info('Verificando tipo de conductor para generar documentos específicos', [
                'driver_id' => $userDriverDetail->id,
                'applying_position' => $applyingPosition
            ]);
            
            // Verificar el tipo de aplicación y generar el documento correspondiente
            if ($applyingPosition === 'owner_operator') {
                Log::info('Generando contrato de lease agreement para owner operator', [
                    'driver_id' => $userDriverDetail->id,
                    'applying_position' => $applyingPosition
                ]);
                $this->generateLeaseAgreementPDF($userDriverDetail, $signaturePath);
            } elseif ($applyingPosition === 'third_party_driver') {
                Log::info('Generando documentos para third party driver', [
                    'driver_id' => $userDriverDetail->id,
                    'applying_position' => $applyingPosition
                ]);
                $this->generateThirdPartyDocuments($userDriverDetail, $signaturePath);
            } else {
                Log::warning('Tipo de aplicación no reconocido o no especificado para generar contrato', [
                    'driver_id' => $userDriverDetail->id,
                    'applying_position' => $applyingPosition
                ]);
            }
        }
        
        // Limpiar archivo temporal de firma
        if (strpos($signaturePath, 'sig_') !== false && file_exists($signaturePath)) {
            @unlink($signaturePath);
            Log::info('Archivo temporal de firma eliminado', ['path' => $signaturePath]);
        }
    }

    /**
     * Generar contrato de arrendamiento para propietarios-operadores
     * @param UserDriverDetail $userDriverDetail
     * @param string $signaturePath Ruta al archivo de firma
     */
    private function generateLeaseAgreementPDF(UserDriverDetail $userDriverDetail, $signaturePath = null)
    {
        try {
            // Cargar todas las relaciones necesarias para asegurar que tenemos los datos completos
            $userDriverDetail->load([
                'application.details', 
                'application.ownerOperatorDetail', 
                'user',
                'carrier'
            ]);
            
            // El modelo UserDriverDetail no tiene una relación directa con vehicle
            // Intentamos obtener el vehículo a través de la aplicación
            
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
                $vehicle = Vehicle::where('user_driver_detail_id', $userDriverDetail->id)->first();
            }
            
            if (!$vehicle) {
                $missingData[] = 'vehicle';
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
            
            // El vehicle ya lo obtuvimos antes en la validación, no necesitamos volver a obtenerlo
            // (El modelo UserDriverDetail no tiene una relación 'vehicle')
            
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
                'signaturePath' => $signaturePath, // Usar la ruta del archivo de firma
                'signature' => null // Mantenemos este campo como NULL para compatibilidad
            ];
            
            try {
                Log::info('Intentando cargar vista de contrato de propietario-operador', [
                    'driver_id' => $userDriverDetail->id,
                    'view' => 'pdfs.lease-agreement-owner',
                    'data_keys' => array_keys($data)
                ]);
                
                // Cargar la vista del contrato de arrendamiento para propietarios-operadores
                $pdf = App::make('dompdf.wrapper')->loadView('pdfs.lease-agreement-owner', $data);
                
                // Asegurarnos de que estamos usando el ID correcto
                $driverId = $userDriverDetail->id;
                $dirPath = 'driver/' . $driverId . '/vehicle_verifications';
                $filePath = $dirPath . '/lease_agreement_owner.pdf';
                
                Log::info('Guardando PDF de contrato de arrendamiento para propietario-operador', [
                    'driver_id' => $driverId, 
                    'file_path' => $filePath
                ]);
                
                // Asegurarse de que el directorio existe
                Storage::disk('public')->makeDirectory($dirPath);
                
                // Guardar el PDF usando Storage
                $pdfContent = $pdf->output();
                Storage::disk('public')->put($filePath, $pdfContent);
                
                // Guardar PDF temporalmente para adjuntarlo a MediaLibrary
                $tempPath = tempnam(sys_get_temp_dir(), 'lease_agreement_owner_') . '.pdf';
                file_put_contents($tempPath, $pdfContent);
                
                Log::info('PDF de contrato de propietario-operador generado exitosamente', [
                    'driver_id' => $driverId,
                    'size' => strlen($pdfContent)
                ]);
            } catch (\Exception $e) {
                Log::error('Error al cargar la vista o generar el PDF del contrato', [
                    'driver_id' => $userDriverDetail->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return; // Salir del método si hay un error con la vista
            }
            
            // Adjuntar el PDF a la aplicación
            if ($userDriverDetail->application) {
                try {
                    // Limpiar collection previa y agregar el nuevo archivo
                    $userDriverDetail->application->clearMediaCollection('application_pdf');
                    $userDriverDetail->application->addMedia($tempPath)
                        ->toMediaCollection('application_pdf');
                        
                    // Registrar información para confirmar
                    Log::info('PDF agregado a Media Library', [
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
        }
    }
    
    /**
     * Generar un PDF combinado con todos los pasos
     */
    private function generateCombinedPDF(UserDriverDetail $userDriverDetail, $signatureImage)
    {
        try {
            // Construir el nombre completo usando la misma lógica que general.blade.php
            $fullName = ($userDriverDetail->user->name ?? 'N/A') . ' ' . 
                       ($userDriverDetail->middle_name ?? '') . ' ' . 
                       ($userDriverDetail->last_name ?? 'N/A');
            $fullName = trim(preg_replace('/\s+/', ' ', $fullName)); // Limpiar espacios extra
            
            $pdf = App::make('dompdf.wrapper')->loadView('pdf.driver.complete_application', [
                'userDriverDetail' => $userDriverDetail,
                'signature' => $signatureImage,
                'fullName' => $fullName,
                'date' => now()->format('m/d/Y')
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
                    Log::info('PDF agregado a Media Library', [
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
        }
    }
    
    /**
     * Preparar la firma para uso en PDFs
     * @param string $signatureBase64 Firma en formato base64
     * @return string|null Ruta al archivo temporal de firma o null si hay error
     */
    private function prepareSignatureForPDF($signatureBase64)
    {
        try {
            if (empty($signatureBase64)) {
                Log::warning('Firma vacía proporcionada');
                return null;
            }
            
            // Limpiar el base64 - manejar diferentes formatos
            $signatureData = $signatureBase64;
            
            // Remover prefijos comunes de data URL
            $prefixes = [
                'data:image/png;base64,',
                'data:image/jpeg;base64,',
                'data:image/jpg;base64,',
                'data:image/webp;base64,'
            ];
            
            foreach ($prefixes as $prefix) {
                if (strpos($signatureData, $prefix) === 0) {
                    $signatureData = substr($signatureData, strlen($prefix));
                    break;
                }
            }
            
            // Limpiar espacios y caracteres problemáticos
            $signatureData = str_replace([' ', '\n', '\r', '\t'], '', $signatureData);
            $signatureData = str_replace(' ', '+', $signatureData);
            
            // Validar que es base64 válido
            if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $signatureData)) {
                Log::error('Formato de firma base64 inválido');
                return null;
            }
            
            // Decodificar
            $decodedSignature = base64_decode($signatureData, true);
            
            if ($decodedSignature === false) {
                Log::error('Error decodificando firma base64');
                return null;
            }
            
            // Validar que es una imagen válida
            $imageInfo = @getimagesizefromstring($decodedSignature);
            if ($imageInfo === false) {
                Log::error('Los datos decodificados no son una imagen válida');
                return null;
            }
            
            // Crear directorio temporal si no existe
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    Log::error('No se pudo crear directorio temporal', ['path' => $tempDir]);
                    return null;
                }
            }
            
            // Crear archivo temporal con nombre único
            $tempPath = $tempDir . '/sig_' . uniqid() . '_' . time() . '.png';
            
            // Guardar archivo
            if (file_put_contents($tempPath, $decodedSignature) === false) {
                Log::error('Error guardando archivo temporal de firma', ['path' => $tempPath]);
                return null;
            }
            
            // Verificar que el archivo se creó correctamente
            if (!file_exists($tempPath) || filesize($tempPath) === 0) {
                Log::error('Archivo temporal de firma no se creó correctamente', ['path' => $tempPath]);
                return null;
            }
            
            Log::info('Firma preparada exitosamente', [
                'temp_path' => $tempPath,
                'file_size' => filesize($tempPath),
                'image_type' => $imageInfo['mime'] ?? 'unknown'
            ]);
            
            return $tempPath;
            
        } catch (\Exception $e) {
            Log::error('Error preparando firma para PDF', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
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
                $vehicle = Vehicle::where('user_driver_detail_id', $userDriverDetail->id)->first();
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
            // La plantilla third-party-consent.blade.php espera $verification, $driverDetails, $vehicle, $date, $signatureData
            $consentData = [
                'verification' => (object) [
                    'third_party_name' => $thirdPartyDetails->third_party_name ?? '',
                    'third_party_phone' => $thirdPartyDetails->third_party_phone ?? '',
                    'third_party_email' => $thirdPartyDetails->third_party_email ?? '',
                    'token' => 'TP-' . $userDriverDetail->id . '-' . time()
                ],
                'driverDetails' => (object) [
                    'user' => (object) [
                        'name' => $userDriverDetail->user->name,
                        'email' => $userDriverDetail->user->email,
                    ],
                    'middle_name' => $userDriverDetail->middle_name ?? '',
                    'last_name' => $userDriverDetail->last_name ?? '',
                    'phone' => $userDriverDetail->phone ?? 'N/A',
                ],
                'vehicle' => $vehicle,
                'date' => now()->format('F j, Y'),
                'signaturePath' => $signaturePath,
                'signature' => null // Para compatibilidad
            ];
            
            // Generar el PDF de consentimiento de terceros
            try {
                Log::info('Intentando cargar vista de consentimiento de terceros', [
                    'driver_id' => $userDriverDetail->id,
                    'view' => 'pdfs.third-party-consent',
                    'data_keys' => array_keys($consentData)
                ]);
                
                // Cargar la vista del consentimiento de terceros
                $pdf = App::make('dompdf.wrapper')->loadView('pdfs.third-party-consent', $consentData);
                
                // Asegurarnos de que estamos usando el ID correcto
                $driverId = $userDriverDetail->id;
                $dirPath = 'driver/' . $driverId . '/vehicle_verifications';
                $filePath = $dirPath . '/third_party_consent.pdf';
                
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
            // La plantilla lease-agreement.blade.php espera variables específicas para third-party
            $leaseData = [
                'carrierName' => $carrier->name ?? 'EF Services',
                'carrierAddress' => $carrier->address ?? '',
                'carrierMc' => $carrier->mc_number ?? '',
                'carrierUsdot' => $carrier->state_dot ?? '',
                // Para third-party, el "owner" es la empresa third-party
                'ownerName' => $thirdPartyDetails->third_party_name ?? '',
                'ownerDba' => $thirdPartyDetails->third_party_dba ?? '',
                'ownerAddress' => $thirdPartyDetails->third_party_address ?? '',
                'ownerPhone' => $thirdPartyDetails->third_party_phone ?? '',
                'ownerContact' => $thirdPartyDetails->third_party_contact ?? '',
                'ownerFein' => $thirdPartyDetails->third_party_fein ?? '',
                // Datos del vehículo
                'vehicleYear' => $vehicle->year ?? '',
                'vehicleMake' => $vehicle->make ?? '',
                'vehicleVin' => $vehicle->vin ?? '',
                'vehicleUnit' => $vehicle->company_unit_number ?? '',
                'signedDate' => now()->format('m/d/Y'),
                'signaturePath' => $signaturePath,
                'signature' => null // Para compatibilidad
            ];
            
            // Generar el PDF de contrato de arrendamiento para third-party
            try {
                Log::info('Intentando cargar vista de contrato de arrendamiento para third-party', [
                    'driver_id' => $userDriverDetail->id,
                    'view' => 'pdfs.lease-agreement',
                    'data_keys' => array_keys($leaseData)
                ]);
                
                // Cargar la vista del contrato de arrendamiento para third-party
                $pdf = App::make('dompdf.wrapper')->loadView('pdfs.lease-agreement', $leaseData);
                
                // Asegurarnos de que estamos usando el ID correcto
                $driverId = $userDriverDetail->id;
                $dirPath = 'driver/' . $driverId . '/vehicle_verifications';
                $filePath = $dirPath . '/lease_agreement_third_party.pdf';
                
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
            Log::error('Error al generar documentos de third-party', [
                'driver_id' => $userDriverDetail->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    // Renderizar
    public function render()
    {
        return view('livewire.driver.steps.certification-step', [
            'employmentHistory' => $this->employmentHistory
        ]);
    }
}