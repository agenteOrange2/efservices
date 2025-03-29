<?php

namespace App\Livewire\Admin\Driver;

use App\Models\User;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\PDF;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\DriverCertification;

class DriverCertificationStep extends Component
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
                'company_name' => $company->company_name ?? ($company->masterCompany ? $company->masterCompany->company_name : 'N/A'),
                'address' => $company->address ?? ($company->masterCompany ? $company->masterCompany->address : 'N/A'),
                'city' => $company->city ?? ($company->masterCompany ? $company->masterCompany->city : 'N/A'),
                'state' => $company->state ?? ($company->masterCompany ? $company->masterCompany->state : 'N/A'),
                'zip' => $company->zip ?? ($company->masterCompany ? $company->masterCompany->zip : 'N/A'),
                'employed_from' => $company->employed_from ? $company->employed_from->format('M d, Y') : 'N/A',
                'employed_to' => $company->employed_to ? $company->employed_to->format('M d, Y') : 'Present'
            ];
        }

        // Cargar certificación previa si existe
        $certification = $userDriverDetail->certification;
        if ($certification) {
            // Cargar la firma desde la base de datos
            $this->signature = $certification->signature;
            $this->certificationAccepted = (bool)$certification->is_accepted;

            // Si la firma no está en la propiedad pero está guardada como archivo
            if (empty($this->signature) && $certification->getFirstMedia('signature')) {
                // Intentar recuperar la ruta del archivo
                $this->signature = $certification->getFirstMediaUrl('signature');
            }
        }
    }

    // Guardar certificación
    // En tu componente Livewire
    public function saveCertification()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }

            // Guardar certificación en la base de datos (incluye la firma como base64)
            $certification = $userDriverDetail->certification()->updateOrCreate(
                [],
                [
                    'signature' => $this->signature,
                    'is_accepted' => $this->certificationAccepted,
                    'signed_at' => now()
                ]
            );

            // Guardar la firma como archivo físico para usarla en PDFs
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

            // Resto del código...
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving certification', ['error' => $e->getMessage()]);
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

                    try {
                        // Get user and carrier
                        $user = $userDriverDetail->user;
                        $carrier = $userDriverDetail->carrier;
                        
                        if ($user && $carrier) {
                            // Get superadmins to notify
                            $superadmins = User::role('superadmin')->get();
                            
                            // Send notification
                            foreach ($superadmins as $admin) {
                                $admin->notify(new \App\Notifications\Admin\Driver\DriverApplicationCompletedNotification(
                                    $user,
                                    $carrier,
                                    $userDriverDetail
                                ));
                            }
                            
                            Log::info('Driver application completed notification sent', [
                                'driver_id' => $user->id,
                                'carrier_id' => $carrier->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error sending driver application completed notification', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
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

        // Preparar la firma una sola vez para todos los PDFs
        $signaturePath = $this->prepareSignatureForPDF($this->signature);

        if (!$signaturePath) {
            Log::error('No se pudo preparar la firma para PDFs', [
                'driver_id' => $userDriverDetail->id
            ]);
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
            // ... resto de pasos ...
        ];

        // Generar PDF para cada paso
        foreach ($steps as $step) {
            try {
                $pdf = PDF::loadView($step['view'], [
                    'userDriverDetail' => $userDriverDetail,
                    'signaturePath' => $signaturePath, // Usamos la ruta del archivo, no base64
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
        $this->generateCombinedPDF($userDriverDetail, $signaturePath);

        // Limpiar archivo temporal de firma
        if (strpos($signaturePath, 'temp_sig_') !== false) {
            @unlink($signaturePath);
        }
    }

    private function generatePDF(UserDriverDetail $userDriverDetail)
    {
        // Obtener la ruta del archivo de firma
        $signaturePath = null;

        if (
            $userDriverDetail->certification &&
            $userDriverDetail->certification->getFirstMedia('signature')
        ) {
            // Usar la ruta real del archivo en el sistema
            $signaturePath = $userDriverDetail->certification->getFirstMedia('signature')->getPath();
        }

        // Cargar la vista PDF con la firma como ruta de archivo
        $pdf = PDF::loadView('pdf.driver.solicitud', [
            'userDriverDetail' => $userDriverDetail,
            'signaturePath' => $signaturePath,
            'date' => now()->format('d/m/Y')
        ]);

        return $pdf;
    }

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

    /**
     * Generar un PDF combinado con todos los pasos
     */
    private function generateCombinedPDF(UserDriverDetail $userDriverDetail, $signatureImage)
    {
        try {
            // Preparar la firma para el PDF
            $signaturePath = $signatureImage;

            // Si es una URL, convertirla a ruta de archivo
            if (is_string($signatureImage) && strpos($signatureImage, 'http') === 0) {
                $signaturePath = $this->prepareSignatureForPDF($signatureImage);
            }

            // Si es un string base64, convertirlo a archivo
            if (is_string($signatureImage) && strpos($signatureImage, 'data:image') === 0) {
                $signaturePath = $this->prepareSignatureForPDF($signatureImage);
            }

            // Verificar que tengamos una ruta de archivo válida para la firma
            if (!empty($signaturePath)) {
                if (!file_exists($signaturePath)) {
                    Log::warning('No se pudo obtener una ruta de firma válida', [
                        'driver_id' => $userDriverDetail->id,
                        'signature_path' => $signaturePath
                    ]);
                } else {
                    Log::info('Firma preparada correctamente para PDF combinado', [
                        'driver_id' => $userDriverDetail->id,
                        'signature_path' => $signaturePath
                    ]);
                }
            }

            // Asegurarnos de que estamos usando el ID correcto
            $driverId = $userDriverDetail->id;
            $filePath = 'driver/' . $driverId . '/complete_application.pdf';

            // Cargar la vista PDF con la firma como ruta de archivo
            $pdf = PDF::loadView('pdf.driver.complete_application', [
                'userDriverDetail' => $userDriverDetail,
                'signaturePath' => $signaturePath, // Importante: pasar la ruta, no el contenido base64
                'date' => now()->format('d/m/Y')
            ]);

            Log::info('Generando PDF combinado para conductor', [
                'driver_id' => $driverId,
                'file_path' => $filePath,
                'signature_path_exists' => !empty($signaturePath) && file_exists($signaturePath)
            ]);

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

            // No limpiar el archivo temporal de firma aquí, lo haremos después de generar todos los PDFs

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
        return view('livewire.admin.driver.steps.driver-certification-step', [
            'employmentHistory' => $this->employmentHistory
        ]);
    }
}
