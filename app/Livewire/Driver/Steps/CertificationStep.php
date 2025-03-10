<?php
namespace App\Livewire\Driver\Steps;

use Livewire\Component;
use Barryvdh\DomPDF\Facade\PDF;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\Driver\DriverCertification;

class CertificationStep extends Component
{
    // Propiedades
    public $driverId;
    public $employmentHistory = [];
    public $signature = '';
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
            
            // Si la firma es base64, guardarla como imagen
            if (!empty($this->signature) && strpos($this->signature, 'data:image') === 0) {
                // Convertir base64 a archivo
                $signatureData = base64_decode(explode(',', $this->signature)[1]);
                $tempFile = tempnam(sys_get_temp_dir(), 'signature_') . '.png';
                file_put_contents($tempFile, $signatureData);
                
                // Guardar en media library
                $certification->clearMediaCollection('signature');
                $certification->addMedia($tempFile)
                    ->toMediaCollection('signature');
                    
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
                // Actualizar el estado de la aplicación a pendiente
                $userDriverDetail = UserDriverDetail::find($this->driverId);
                if ($userDriverDetail && $userDriverDetail->application) {
                    $userDriverDetail->application->update(['status' => 'pending']);
                    
                    // Generar PDFs solo si existe la firma
                    if (!empty($this->signature)) {
                        $this->generateApplicationPDFs($userDriverDetail);
                    }
                }
                
                // Redireccionar según tipo de registro
                $isReferred = $userDriverDetail->carrier_id != null;
                
                if ($isReferred) {
                    // Si es referido, mostrar mensaje de éxito y esperar
                    return redirect()->route('driver.registration.success')
                        ->with('success', 'Tu solicitud ha sido enviada para revisión.');
                } else {
                    // Si es independiente, redirigir a selección de carrier
                    return redirect()->route('driver.select_carrier')
                        ->with('success', 'Tu solicitud ha sido completada. Ahora puedes seleccionar un carrier.');
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
            ['view' => 'pdf.driver.general', 'filename' => 'informacion_general.pdf', 'title' => 'Información General'],
            ['view' => 'pdf.driver.address', 'filename' => 'informacion_direccion.pdf', 'title' => 'Información de Dirección'],
            ['view' => 'pdf.driver.application', 'filename' => 'detalles_aplicacion.pdf', 'title' => 'Detalles de Aplicación'],
            ['view' => 'pdf.driver.licenses', 'filename' => 'informacion_licencias.pdf', 'title' => 'Licencias de Conducir'],
            ['view' => 'pdf.driver.medical', 'filename' => 'calificacion_medica.pdf', 'title' => 'Calificación Médica'],
            ['view' => 'pdf.driver.training', 'filename' => 'escuelas_entrenamiento.pdf', 'title' => 'Escuelas de Entrenamiento'],
            ['view' => 'pdf.driver.traffic', 'filename' => 'infracciones_trafico.pdf', 'title' => 'Infracciones de Tráfico'],
            ['view' => 'pdf.driver.accident', 'filename' => 'registro_accidentes.pdf', 'title' => 'Registro de Accidentes'],
            ['view' => 'pdf.driver.fmcsr', 'filename' => 'requisitos_fmcsr.pdf', 'title' => 'Requisitos FMCSR'],
            ['view' => 'pdf.driver.employment', 'filename' => 'historial_empleo.pdf', 'title' => 'Historial de Empleo'],
            ['view' => 'pdf.driver.certification', 'filename' => 'certificacion.pdf', 'title' => 'Certificación'],
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