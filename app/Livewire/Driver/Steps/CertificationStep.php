<?php
namespace App\Livewire\Driver\Steps;

use Livewire\Component;
use Barryvdh\DomPDF\Facade\PDF;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverCertification;

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
        // Primero, asegurémonos de que tenemos la firma
        if (empty($this->signature)) {
            return;
        }
        
        // Asegurarse que los directorios existen
        $driverPath = 'driver/' . $userDriverDetail->id;
        $appSubPath = $driverPath . '/driver_applications';
        
        // Asegúrate de que los directorios existen
        Storage::disk('public')->makeDirectory($driverPath);
        Storage::disk('public')->makeDirectory($appSubPath);
        
        // Configuraciones de pasos - definir la vista y nombre de archivo para cada paso
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
        
        // Generar PDF para cada paso
        foreach ($steps as $step) {
            try {
                $pdf = PDF::loadView($step['view'], [
                    'userDriverDetail' => $userDriverDetail,
                    'signature' => $this->signature,
                    'title' => $step['title'],
                    'date' => now()->format('d/m/Y')
                ]);
                
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
        
        // Generar un PDF combinado con todos los pasos
        $this->generateCombinedPDF($userDriverDetail, $this->signature);
        
        // Generar contrato de arrendamiento para propietarios-operadores si corresponde
        $application = $userDriverDetail->application;
        if ($application && $application->details && $application->details->applying_position === 'owner_operator') {
            $this->generateLeaseAgreementPDF($userDriverDetail);
        }
    }

    /**
     * Generar contrato de arrendamiento para propietarios-operadores
     * @param UserDriverDetail $userDriverDetail
     */
    private function generateLeaseAgreementPDF(UserDriverDetail $userDriverDetail)
    {
        try {
            // Cargar las relaciones necesarias si no están cargadas
            if (!$userDriverDetail->relationLoaded('application')) {
                $userDriverDetail->load(['application.details', 'application.ownerOperatorDetail', 'vehicle']);
            }
            
            $application = $userDriverDetail->application;
            $vehicle = $userDriverDetail->vehicle;
            $carrier = $userDriverDetail->carrier;
            
            if (!$application || !$application->details || !$vehicle) {
                Log::error('Datos insuficientes para generar contrato de arrendamiento de propietario-operador', [
                    'driver_id' => $userDriverDetail->id
                ]);
                return;
            }
            
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
                'signature' => $this->signature // Usar la firma actual
            ];
            
            // Cargar la vista del contrato de arrendamiento para propietarios-operadores
            $pdf = PDF::loadView('pdfs.lease-agreement-owner', $data);
            
            // Asegurarnos de que estamos usando el ID correcto
            $driverId = $userDriverDetail->id;
            $filePath = 'driver/' . $driverId . '/lease_agreement_owner.pdf';
            
            Log::info('Guardando PDF de contrato de arrendamiento para propietario-operador', ['driver_id' => $driverId, 'file_path' => $filePath]);
            
            // Guardar el PDF usando Storage
            $pdfContent = $pdf->output();
            Storage::disk('public')->put($filePath, $pdfContent);
            
            // Guardar PDF temporalmente para adjuntarlo a MediaLibrary
            $tempPath = tempnam(sys_get_temp_dir(), 'lease_agreement_owner_') . '.pdf';
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
     * Generar un PDF combinado con todos los pasos
     */
    private function generateCombinedPDF(UserDriverDetail $userDriverDetail, $signatureImage)
    {
        try {
            $pdf = PDF::loadView('pdf.driver.solicitud_completa', [
                'userDriverDetail' => $userDriverDetail,
                'signature' => $signatureImage,
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
    
    // Renderizar
    public function render()
    {
        return view('livewire.driver.steps.certification-step', [
            'employmentHistory' => $this->employmentHistory
        ]);
    }
}