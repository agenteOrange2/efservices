<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverCertification;
use App\Models\Admin\Vehicle\Vehicle;
use App\Mail\ThirdPartyVehicleVerification;
use App\Models\VehicleVerificationToken;

class DriverCertificationStep extends Component
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
                        storage_path('app/public/temp/signature'),
                        storage_path('app/public/temp')
                    ];
                    
                    foreach ($possiblePaths as $dir) {
                        if (is_dir($dir)) {
                            $files = scandir($dir);
                            Log::info('Archivos en directorio', ['dir' => $dir, 'files' => $files]);
                            
                            // Buscar archivos recientes
                            foreach ($files as $file) {
                                if ($file != '.' && $file != '..' && is_file($dir . '/' . $file)) {
                                    // Si el archivo fue creado en las últimas 24 horas, lo usamos
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
                    
                    // Obtener el driver detail con todas las relaciones necesarias
                    $userDriverDetail = UserDriverDetail::with([
                        'criminalHistory',
                        'carrier',
                        'user',
                        'application.details'
                    ])->find($this->driverId);
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
    
    /**
     * Generar PDFs durante la edición (llamado desde otros pasos)
     * @param int $driverId
     * @param string|null $signature
     */
    public function generatePDFsForEdit($driverId, $signature = null)
    {
        try {
            // Obtener el driver detail con todas las relaciones necesarias
            $userDriverDetail = UserDriverDetail::with([
                'criminalHistory',
                'carrier',
                'user',
                'application.details'
            ])->find($driverId);
            
            if (!$userDriverDetail) {
                Log::error('Driver no encontrado para generar PDFs en edición', ['driver_id' => $driverId]);
                return false;
            }
            
            // Si no se proporciona firma, intentar usar la firma guardada
            if (empty($signature) && !empty($this->signature)) {
                $signature = $this->signature;
            }
            
            // Generar PDFs solo si existe alguna firma
            if (!empty($signature)) {
                $this->generateApplicationPDFs($userDriverDetail, $signature);
                Log::info('PDFs generados exitosamente durante edición', ['driver_id' => $driverId]);
                return true;
            } else {
                Log::warning('No se puede generar PDFs sin firma', ['driver_id' => $driverId]);
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error('Error al generar PDFs durante edición', [
                'driver_id' => $driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
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
     * @param string|null $signature Firma opcional para usar en los PDFs
     */
    private function generateApplicationPDFs(UserDriverDetail $userDriverDetail, $signature = null)
    {
        // Usar la firma pasada como parámetro o la firma de la instancia
        $signatureToUse = $signature ?? $this->signature;
        
        // Primero, asegurémonos de que tenemos la firma
        if (empty($signatureToUse)) {
            return;
        }
        
        // Preparar la firma una sola vez para todos los PDFs
        $signaturePath = $this->prepareSignatureForPDF($signatureToUse);

        if (!$signaturePath) {
            Log::error('No se pudo preparar la firma para PDFs', [
                'driver_id' => $userDriverDetail->id
            ]);
            return;
        }

        Log::info('Firma preparada para PDFs', [
            'driver_id' => $userDriverDetail->id,
            'signature_path' => $signaturePath
        ]);
        
        // Asegurarse que los directorios existen
        $driverPath = 'driver/' . $userDriverDetail->id;
        $appSubPath = $driverPath . '/driver_applications';
        
        // Asegúrate de que los directorios existen
        Storage::disk('public')->makeDirectory($driverPath);
        Storage::disk('public')->makeDirectory($appSubPath);
        
        // Configuraciones de pasos - definir la vista y nombre de archivo para cada paso
        // NOTA: criminal_history se maneja por separado para evitar duplicados
        $steps = [
            ['view' => 'pdf.driver.general', 'filename' => 'general_information.pdf', 'title' => 'General Information'],
            ['view' => 'pdf.driver.address', 'filename' => 'address_information.pdf', 'title' => 'Address Information'],
            ['view' => 'pdf.driver.application', 'filename' => 'application_details.pdf', 'title' => 'Application Details'],
            ['view' => 'pdf.driver.licenses', 'filename' => 'drivers_licenses.pdf', 'title' => 'Drivers Licenses'],
            ['view' => 'pdf.driver.medical', 'filename' => 'calificacion_medica.pdf', 'title' => 'Medical Qualification'],
            ['view' => 'pdf.driver.training', 'filename' => 'training_schools.pdf', 'title' => 'Training Schools'],
            ['view' => 'pdf.driver.traffic', 'filename' => 'traffic_violations.pdf', 'title' => 'Traffic Violations'],
            ['view' => 'pdf.driver.accident', 'filename' => 'accident_record.pdf', 'title' => 'Accident Record '],
            ['view' => 'pdf.driver.fmcsr', 'filename' => 'fmcsr_requirements.pdf', 'title' => 'FMCSR Requirements'],
            ['view' => 'pdf.driver.employment', 'filename' => 'employment_history.pdf', 'title' => 'Employment History'],
            ['view' => 'pdf.driver.certification', 'filename' => 'certification.pdf', 'title' => 'Certification'],
        ];
        
        // Obtener fechas efectivas (personalizadas o del modelo)
        $effectiveDates = $this->getEffectiveDates($userDriverDetail->id);
        
        // Cargar todas las relaciones necesarias para los PDFs
        $userDriverDetail->load([
            'application.addresses',
            'licenses',
            'medicalQualification',
            'criminalHistory',
            'carrier',
            'user',
            'application.details',
            'activeVehicleAssignment.vehicle',
            'vehicleAssignments.vehicle'
        ]);
        
        // Generar PDF para cada paso
        foreach ($steps as $step) {
            try {
                // Preparar datos para el PDF
                $pdfData = [
                    'userDriverDetail' => $userDriverDetail,
                    'signaturePath' => $signaturePath, // Usamos la ruta del archivo, no base64
                    'title' => $step['title'],
                    'date' => now()->format('d/m/Y'),
                    'created_at' => $effectiveDates['created_at'],
                    'updated_at' => $effectiveDates['updated_at'],
                    'custom_created_at' => $effectiveDates['custom_created_at']
                ];
                
                // Preparar formatted_dates con ambas fechas cuando corresponda
                $formattedDates = [
                    'updated_at' => $effectiveDates['updated_at']->format('m/d/Y'),
                    'updated_at_long' => $effectiveDates['updated_at']->format('F j, Y')
                ];
                
                // Siempre incluir created_at (fecha de registro normal)
                if ($effectiveDates['show_created_at'] && $effectiveDates['created_at']) {
                    $formattedDates['created_at'] = $effectiveDates['created_at']->format('m/d/Y');
                    $formattedDates['created_at_long'] = $effectiveDates['created_at']->format('F j, Y');
                }
                
                // Incluir custom_created_at solo si está habilitado y tiene valor
                if ($effectiveDates['show_custom_created_at'] && $effectiveDates['custom_created_at']) {
                    $formattedDates['custom_created_at'] = $effectiveDates['custom_created_at']->format('m/d/Y');
                    $formattedDates['custom_created_at_long'] = $effectiveDates['custom_created_at']->format('F j, Y');
                }
                
                $pdfData['formatted_dates'] = $formattedDates;
                $pdfData['use_custom_dates'] = $effectiveDates['show_custom_created_at'];
                
                $pdf = App::make('dompdf.wrapper')->loadView($step['view'], $pdfData);
                
                // Guardar PDF usando Storage para evitar problemas de permisos
                $pdfContent = $pdf->output();
                Storage::disk('public')->put($appSubPath . '/' . $step['filename'], $pdfContent);
                
                Log::info('PDF individual generado', [
                    'driver_id' => $userDriverDetail->id,
                    'filename' => $step['filename']
                ]);
            } catch (\Exception $e) {
                Log::error('Error generando PDF individual', [
                    'driver_id' => $userDriverDetail->id,
                    'filename' => $step['filename'],
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Generar el PDF combinado primero (completa_aplicacion.pdf o solicitud.pdf)
        if (view()->exists('pdf.driver.complete_application')) {
            $this->generateCombinedPDF($userDriverDetail, $signaturePath);
        } else {
            Log::info('No se generó el PDF combinado porque la vista no existe', [
                'driver_id' => $userDriverDetail->id
            ]);
        }
        
        // Generar el PDF separado de Criminal History Investigation
        if (view()->exists('pdf.driver.criminal_history')) {
            $this->generateCriminalHistoryPDF($userDriverDetail, $signaturePath);
        } else {
            Log::info('No se generó el PDF de Criminal History porque la vista no existe', [
                'driver_id' => $userDriverDetail->id
            ]);
        }
        
        // Generar contrato de arrendamiento para propietarios-operadores si corresponde
        // Ahora obtenemos el tipo de conductor desde vehicle_driver_assignments en lugar de driver_application_details
        
        // Obtener la asignación activa del vehículo para determinar el tipo de conductor
        $activeAssignment = $userDriverDetail->activeVehicleAssignment;
        
        // Si no hay asignación activa, intentar obtener la más reciente
        if (!$activeAssignment) {
            $activeAssignment = $userDriverDetail->vehicleAssignments()
                ->where('status', 'pending')
                ->latest()
                ->first();
        }
        
        // Verificar el tipo de conductor y generar los documentos correspondientes
        if ($activeAssignment) {
            $driverType = $activeAssignment->driver_type ?? 'unknown';
            Log::info('Verificando tipo de conductor para generar documentos desde vehicle_driver_assignments', [
                'driver_id' => $userDriverDetail->id,
                'driver_type' => $driverType,
                'assignment_id' => $activeAssignment->id
            ]);
            
            if ($driverType === 'owner_operator') {
                Log::info('Generando contrato de arrendamiento para propietario-operador', [
                    'driver_id' => $userDriverDetail->id
                ]);
                $this->generateLeaseAgreementPDF($userDriverDetail, $signaturePath);
            } elseif ($driverType === 'third_party') {
                Log::info('Generando documentos para conductor third-party', [
                    'driver_id' => $userDriverDetail->id
                ]);
                $this->generateThirdPartyDocuments($userDriverDetail, $signaturePath);
            } else {
                Log::info('No se generan documentos específicos para este tipo de conductor', [
                    'driver_id' => $userDriverDetail->id,
                    'driver_type' => $driverType
                ]);
            }
        } else {
            Log::warning('No se puede determinar el tipo de conductor, no hay asignación de vehículo', [
                'driver_id' => $userDriverDetail->id,
                'has_active_assignment' => $userDriverDetail->activeVehicleAssignment ? 'yes' : 'no',
                'total_assignments' => $userDriverDetail->vehicleAssignments()->count()
            ]);
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
            
            // Obtener el vehículo a través de la asignación activa
            $vehicle = null;
            $activeAssignment = $userDriverDetail->activeVehicleAssignment;
            if ($activeAssignment) {
                $vehicle = $activeAssignment->vehicle;
            }
            
            // Si no hay asignación activa, buscar la más reciente
            if (!$vehicle) {
                $latestAssignment = $userDriverDetail->vehicleAssignments()->latest()->first();
                if ($latestAssignment) {
                    $vehicle = $latestAssignment->vehicle;
                }
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
            
            // Obtener fechas efectivas (personalizadas o del modelo)
            $effectiveDates = $this->getEffectiveDates($userDriverDetail->id);
            
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
                'signature' => null, // Mantenemos este campo como NULL para compatibilidad
                'created_at' => $effectiveDates['created_at'],
                'updated_at' => $effectiveDates['updated_at'],
                'custom_created_at' => $effectiveDates['custom_created_at']
            ];
            
            // Preparar formatted_dates con ambas fechas cuando corresponda
            $formattedDates = [
                'updated_at' => $effectiveDates['updated_at']->format('m/d/Y'),
                'updated_at_long' => $effectiveDates['updated_at']->format('F j, Y')
            ];
            
            // Siempre incluir created_at (fecha de registro normal)
            if ($effectiveDates['show_created_at'] && $effectiveDates['created_at']) {
                $formattedDates['created_at'] = $effectiveDates['created_at']->format('m/d/Y');
                $formattedDates['created_at_long'] = $effectiveDates['created_at']->format('F j, Y');
            }
            
            // Incluir custom_created_at solo si está habilitado y tiene valor
            if ($effectiveDates['show_custom_created_at'] && $effectiveDates['custom_created_at']) {
                $formattedDates['custom_created_at'] = $effectiveDates['custom_created_at']->format('m/d/Y');
                $formattedDates['custom_created_at_long'] = $effectiveDates['custom_created_at']->format('F j, Y');
            }
            
            $data['formatted_dates'] = $formattedDates;
            $data['use_custom_dates'] = $effectiveDates['show_custom_created_at'];
            
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
            // Cargar los datos del Criminal History Investigation
            $criminalHistory = null;
            if ($userDriverDetail->criminalHistory) {
                $criminalHistory = [
                    'has_criminal_charges' => $userDriverDetail->criminalHistory->has_criminal_charges ?? false,
                    'has_felony_conviction' => $userDriverDetail->criminalHistory->has_felony_conviction ?? false,
                    'has_minister_permit' => $userDriverDetail->criminalHistory->has_minister_permit ?? false,
                    'fcra_consent' => $userDriverDetail->criminalHistory->fcra_consent ?? false,
                    'background_info_consent' => $userDriverDetail->criminalHistory->background_info_consent ?? false,
                ];
            }
            
            // Preparar el nombre completo del usuario
            $fullName = trim(($userDriverDetail->user->name ?? 'N/A') . ' ' . 
                            ($userDriverDetail->middle_name ?? '') . ' ' . 
                            ($userDriverDetail->last_name ?? 'N/A'));
            
            // Obtener fechas efectivas (personalizadas o del modelo)
            $effectiveDates = $this->getEffectiveDates($userDriverDetail->id);
            
            // Preparar datos para el PDF
            $pdfData = [
                'userDriverDetail' => $userDriverDetail,
                'signature' => $signatureImage,
                'date' => now()->format('m/d/Y'),
                'criminalHistory' => $criminalHistory,
                'carrier' => $userDriverDetail->carrier,
                'fullName' => $fullName,
                'created_at' => $effectiveDates['created_at'],
                'updated_at' => $effectiveDates['updated_at'],
                'custom_created_at' => $effectiveDates['custom_created_at']
            ];
            
            // Preparar formatted_dates con ambas fechas cuando corresponda
            $formattedDates = [
                'updated_at' => $effectiveDates['updated_at']->format('m/d/Y'),
                'updated_at_long' => $effectiveDates['updated_at']->format('F j, Y')
            ];
            
            // Siempre incluir created_at (fecha de registro normal)
            if ($effectiveDates['show_created_at'] && $effectiveDates['created_at']) {
                $formattedDates['created_at'] = $effectiveDates['created_at']->format('m/d/Y');
                $formattedDates['created_at_long'] = $effectiveDates['created_at']->format('F j, Y');
            }
            
            // Incluir custom_created_at solo si está habilitado y tiene valor
            if ($effectiveDates['show_custom_created_at'] && $effectiveDates['custom_created_at']) {
                $formattedDates['custom_created_at'] = $effectiveDates['custom_created_at']->format('m/d/Y');
                $formattedDates['custom_created_at_long'] = $effectiveDates['custom_created_at']->format('F j, Y');
            }
            
            $pdfData['formatted_dates'] = $formattedDates;
            $pdfData['use_custom_dates'] = $effectiveDates['show_custom_created_at'];
            
            $pdf = App::make('dompdf.wrapper')->loadView('pdf.driver.complete_application', $pdfData);
            
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
     * Generar un PDF específico para Criminal History Investigation
     */
    private function generateCriminalHistoryPDF(UserDriverDetail $userDriverDetail, $signatureImage)
    {
        try {
            // Cargar los datos del Criminal History Investigation
            $criminalHistory = null;
            if ($userDriverDetail->criminalHistory) {
                $criminalHistory = [
                    'has_criminal_charges' => $userDriverDetail->criminalHistory->has_criminal_charges ?? false,
                    'has_felony_conviction' => $userDriverDetail->criminalHistory->has_felony_conviction ?? false,
                    'has_minister_permit' => $userDriverDetail->criminalHistory->has_minister_permit ?? false,
                    'fcra_consent' => $userDriverDetail->criminalHistory->fcra_consent ?? false,
                    'background_info_consent' => $userDriverDetail->criminalHistory->background_info_consent ?? false,
                ];
            }
            
            // Preparar el nombre completo del usuario
            $fullName = trim(($userDriverDetail->user->name ?? 'N/A') . ' ' . 
                            ($userDriverDetail->middle_name ?? '') . ' ' . 
                            ($userDriverDetail->last_name ?? 'N/A'));
            
            // Obtener fechas efectivas (personalizadas o del modelo)
            $effectiveDates = $this->getEffectiveDates($userDriverDetail->id);
            
            // Preparar datos para el PDF
            $pdfData = [
                'userDriverDetail' => $userDriverDetail,
                'signature' => $signatureImage,
                'date' => now()->format('m/d/Y'),
                'criminalHistory' => $criminalHistory,
                'carrier' => $userDriverDetail->carrier,
                'fullName' => $fullName,
                'created_at' => $effectiveDates['created_at'],
                'updated_at' => $effectiveDates['updated_at'],
                'custom_created_at' => $effectiveDates['custom_created_at']
            ];
            
            // Preparar formatted_dates con ambas fechas cuando corresponda
            $formattedDates = [
                'updated_at' => $effectiveDates['updated_at']->format('m/d/Y'),
                'updated_at_long' => $effectiveDates['updated_at']->format('F j, Y')
            ];
            
            // Siempre incluir created_at (fecha de registro normal)
            if ($effectiveDates['show_created_at'] && $effectiveDates['created_at']) {
                $formattedDates['created_at'] = $effectiveDates['created_at']->format('m/d/Y');
                $formattedDates['created_at_long'] = $effectiveDates['created_at']->format('F j, Y');
            }
            
            // Incluir custom_created_at solo si está habilitado y tiene valor
            if ($effectiveDates['show_custom_created_at'] && $effectiveDates['custom_created_at']) {
                $formattedDates['custom_created_at'] = $effectiveDates['custom_created_at']->format('m/d/Y');
                $formattedDates['custom_created_at_long'] = $effectiveDates['custom_created_at']->format('F j, Y');
            }
            
            $pdfData['formatted_dates'] = $formattedDates;
            $pdfData['use_custom_dates'] = $effectiveDates['show_custom_created_at'];
            
            $pdf = App::make('dompdf.wrapper')->loadView('pdf.driver.criminal_history', $pdfData);
            
            // Asegurarnos de que estamos usando el ID correcto y la ruta correcta
            $driverId = $userDriverDetail->id;
            $driverPath = 'driver/' . $driverId;
            $appSubPath = $driverPath . '/driver_applications';
            $filePath = $appSubPath . '/criminal_history_investigation.pdf';
            
            // Asegurar que los directorios existen
            Storage::disk('public')->makeDirectory($driverPath);
            Storage::disk('public')->makeDirectory($appSubPath);
            
            Log::info('Guardando PDF de Criminal History Investigation para conductor', ['driver_id' => $driverId, 'file_path' => $filePath]);
            
            // Guardar el PDF usando Storage
            $pdfContent = $pdf->output();
            Storage::disk('public')->put($filePath, $pdfContent);
            
            // Guardar PDF temporalmente para adjuntarlo a MediaLibrary
            $tempPath = tempnam(sys_get_temp_dir(), 'criminal_history_') . '.pdf';
            file_put_contents($tempPath, $pdfContent);
            
            // Adjuntar el PDF a la aplicación
            if ($userDriverDetail->application) {
                try {
                    // Agregar el archivo a la colección de criminal history
                    $userDriverDetail->application->addMedia($tempPath)
                        ->toMediaCollection('criminal_history_pdf');
                        
                    // Registrar información para confirmar
                    Log::info('PDF de Criminal History agregado a Media Library', [
                        'driver_id' => $driverId,
                        'application_id' => $userDriverDetail->application->id
                    ]);
                    
                } catch (\Exception $e) {
                    // Si falla, registrar error
                    Log::error('Error adding criminal history media to application', [
                        'error' => $e->getMessage(),
                        'driver_id' => $driverId
                    ]);
                }
                
                // Limpiar archivo temporal
                @unlink($tempPath);
            }
        } catch (\Exception $e) {
            Log::error('Error generando PDF de Criminal History Investigation', [
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
    private function generateThirdPartyDocuments(UserDriverDetail $userDriverDetail, $signaturePath = null)
    {
        try {
            // Cargar todas las relaciones necesarias para asegurar que tenemos los datos completos
            $userDriverDetail->load([
                'application.details', 
                'application.thirdPartyDetail', 
                'user',
                'carrier',
                'activeVehicleAssignment.vehicle',
                'vehicleAssignments.vehicle'
            ]);
            
            // Verificar cada relación individualmente y registrar qué datos faltan
            $missingData = [];
            
            if (!$userDriverDetail->application) {
                $missingData[] = 'application';
            } elseif (!$userDriverDetail->application->details) {
                $missingData[] = 'application.details';
            }
            
            // Obtener el vehículo a través de activeVehicleAssignment
            $vehicle = $userDriverDetail->activeVehicleAssignment?->vehicle;
            
            // Si no hay asignación activa, intentar obtener la más reciente
            if (!$vehicle) {
                $recentAssignment = $userDriverDetail->vehicleAssignments()->with('vehicle')->latest()->first();
                $vehicle = $recentAssignment?->vehicle;
            }
            
            // Log para debugging
            Log::info('Vehicle data retrieval for third-party consent', [
                'driver_id' => $userDriverDetail->id,
                'has_active_assignment' => $userDriverDetail->activeVehicleAssignment !== null,
                'vehicle_found' => $vehicle !== null,
                'vehicle_data' => $vehicle ? [
                    'id' => $vehicle->id,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'year' => $vehicle->year,
                    'vin' => $vehicle->vin
                ] : null
            ]);
            
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
            
            // Obtener fechas efectivas (personalizadas o del modelo)
            $effectiveDates = $this->getEffectiveDates($userDriverDetail->id);
            
            // Preparar formatted_dates con la nueva lógica
            $formattedDates = [
                'updated_at' => $effectiveDates['updated_at']->format('m/d/Y'),
                'updated_at_long' => $effectiveDates['updated_at']->format('F j, Y')
            ];
            
            // Siempre incluir created_at si show_created_at es true
            if ($effectiveDates['show_created_at']) {
                $formattedDates['created_at'] = $effectiveDates['created_at']->format('m/d/Y');
                $formattedDates['created_at_long'] = $effectiveDates['created_at']->format('F j, Y');
            }
            
            // Incluir custom_created_at solo si show_custom_created_at es true y tiene valor
            if ($effectiveDates['show_custom_created_at'] && $effectiveDates['custom_created_at']) {
                $formattedDates['custom_created_at'] = $effectiveDates['custom_created_at']->format('m/d/Y');
                $formattedDates['custom_created_at_long'] = $effectiveDates['custom_created_at']->format('F j, Y');
            }

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
                'vehicle' => $vehicle,
                'date' => now()->format('m/d/Y'),
                'signedDate' => now()->format('m/d/Y'),
                'signaturePath' => $signaturePath,
                'signature' => null, // Mantenemos este campo como NULL para compatibilidad
                'created_at' => $effectiveDates['created_at'],
                'updated_at' => $effectiveDates['updated_at'],
                'custom_created_at' => $effectiveDates['custom_created_at'],
                'formatted_dates' => $formattedDates,
                'use_custom_dates' => $effectiveDates['show_custom_created_at']
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
                
                // Almacenar la ruta del PDF de consentimiento para adjuntar al email
                $consentPdfPath = storage_path('app/public/' . $filePath);
                
                Log::info('PDF de consentimiento de terceros guardado exitosamente', [
                    'driver_id' => $driverId,
                    'file_path' => $filePath,
                    'full_path' => $consentPdfPath,
                    'file_exists' => file_exists($consentPdfPath)
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error al generar PDF de consentimiento de terceros', [
                    'driver_id' => $userDriverDetail->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            // Preparar los datos para el PDF de contrato de arrendamiento para third-party
            $leaseData = [
                'carrierName' => $carrier->name ?? 'ECTS Services',
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
                'signature' => null, // Mantenemos este campo como NULL para compatibilidad
                'created_at' => $effectiveDates['created_at'],
                'updated_at' => $effectiveDates['updated_at'],
                'custom_created_at' => $effectiveDates['custom_created_at'],
                'formatted_dates' => $formattedDates,
                'use_custom_dates' => $effectiveDates['show_custom_created_at']
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
                
                $leasePdfPath = storage_path('app/public/' . $filePath);
                
                Log::info('PDF de contrato de arrendamiento para third-party guardado exitosamente', [
                    'driver_id' => $driverId,
                    'file_path' => $filePath,
                    'full_path' => $leasePdfPath,
                    'file_exists' => file_exists($leasePdfPath)
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error al generar PDF de contrato de arrendamiento para third-party', [
                    'driver_id' => $userDriverDetail->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            // PDFs generados exitosamente
            Log::info('PDFs de third-party generados exitosamente', [
                'driver_id' => $userDriverDetail->id,
                'consent_pdf' => isset($consentPdfPath),
                'lease_pdf' => isset($leasePdfPath)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al generar documentos para third-party', [
                'driver_id' => $userDriverDetail->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Obtiene las fechas efectivas (personalizadas o del modelo)
     * @param int $driverId
     * @return array
     */
    private function getEffectiveDates($driverId)
    {
        $userDriverDetail = UserDriverDetail::find($driverId);
        
        if (!$userDriverDetail) {
            return [
                'created_at' => now(),
                'updated_at' => now(),
                'custom_created_at' => null,
                'show_created_at' => true,
                'show_custom_created_at' => false
            ];
        }
        
        // Lógica correcta:
        // 1. created_at siempre visible (fecha de registro normal)
        // 2. custom_created_at solo visible si use_custom_dates=true Y custom_created_at tiene valor
        
        $showCustomCreatedAt = false;
        $customCreatedAt = null;
        
        if ($userDriverDetail->use_custom_dates && $userDriverDetail->custom_created_at) {
            $showCustomCreatedAt = true;
            $customCreatedAt = $userDriverDetail->custom_created_at;
        }
        
        return [
            'created_at' => $userDriverDetail->created_at,
            'updated_at' => $userDriverDetail->updated_at,
            'custom_created_at' => $customCreatedAt,
            'show_created_at' => true, // Siempre visible
            'show_custom_created_at' => $showCustomCreatedAt
        ];
    }
    
    /**
     * Envía email de notificación a third-party con PDFs adjuntos
     * @param UserDriverDetail $userDriverDetail
     */
    private function sendThirdPartyNotificationEmail($userDriverDetail)
    {
        try {
            $application = $userDriverDetail->application;
            $thirdPartyDetails = $application->thirdPartyDetail;
            
            // Verificar que tenemos email de third-party
            if (!$thirdPartyDetails || !$thirdPartyDetails->third_party_email) {
                Log::warning('No se puede enviar email de notificación: falta email de third-party', [
                    'driver_id' => $userDriverDetail->id
                ]);
                return;
            }
            
            // Crear token de verificación único
            $token = bin2hex(random_bytes(32));
            $expiresAt = now()->addDays(30); // Token válido por 30 días
            
            // Guardar token en la base de datos
            VehicleVerificationToken::create([
                'driver_id' => $userDriverDetail->id,
                'token' => $token,
                'expires_at' => $expiresAt,
                'is_used' => false
            ]);
            
            // Preparar datos para el email
            $vehicle = null;
            if ($userDriverDetail->activeVehicleAssignment && $userDriverDetail->activeVehicleAssignment->vehicle) {
                $vehicle = $userDriverDetail->activeVehicleAssignment->vehicle;
            } elseif ($userDriverDetail->vehicleAssignments->isNotEmpty()) {
                $vehicle = $userDriverDetail->vehicleAssignments->first()->vehicle;
            }
            
            $emailData = [
                'driverName' => $userDriverDetail->user->name ?? '',
                'thirdPartyName' => $thirdPartyDetails->third_party_name ?? '',
                'thirdPartyContact' => $thirdPartyDetails->third_party_contact ?? '',
                'vehicle' => $vehicle,
                'verificationUrl' => url('/vehicle-verification/' . $token),
                'token' => $token
            ];
            
            // Buscar PDFs para adjuntar
            $attachments = [];
            $driverId = $userDriverDetail->id;
            $dirPath = 'driver/' . $driverId . '/vehicle-verifications';
            
            // Buscar PDF de consentimiento
            $consentFiles = Storage::disk('public')->files($dirPath);
            foreach ($consentFiles as $file) {
                if (strpos($file, 'third_party_consent_') !== false) {
                    $attachments[] = [
                        'path' => Storage::disk('public')->path($file),
                        'name' => 'Third_Party_Consent.pdf',
                        'mime' => 'application/pdf'
                    ];
                    break;
                }
            }
            
            // Buscar PDF de lease agreement
            foreach ($consentFiles as $file) {
                if (strpos($file, 'lease_agreement_third_party_') !== false) {
                    $attachments[] = [
                        'path' => Storage::disk('public')->path($file),
                        'name' => 'Lease_Agreement.pdf',
                        'mime' => 'application/pdf'
                    ];
                    break;
                }
            }
            
            // Enviar email
            Mail::to($thirdPartyDetails->third_party_email)
                ->send(new ThirdPartyVehicleVerification($emailData, $attachments));
            
            Log::info('Email de notificación enviado a third-party', [
                'driver_id' => $userDriverDetail->id,
                'third_party_email' => $thirdPartyDetails->third_party_email,
                'token' => $token,
                'attachments_count' => count($attachments)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al enviar email de notificación a third-party', [
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

        // Si es base64, convertir a archivo temporal
        if (is_string($signature) && strpos($signature, 'data:image') === 0) {
            $signatureData = base64_decode(explode(',', $signature)[1]);
            $tempFile = storage_path('app/temp/sig_' . uniqid() . '.png');

            // Asegurar que el directorio existe
            if (!file_exists(dirname($tempFile))) {
                mkdir(dirname($tempFile), 0755, true);
            }

            file_put_contents($tempFile, $signatureData);

            // Registrar la creación para limpieza posterior
            Log::info('Archivo temporal de firma creado', ['path' => $tempFile]);

            return $tempFile;
        }

        return null;
    }
    
    // Renderizar
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-certification-step', [
            'employmentHistory' => $this->employmentHistory
        ]);
    }
}
