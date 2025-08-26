<?php
namespace App\Livewire\Driver\Steps;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverMedicalQualification;
use App\Services\Admin\TempUploadService;

class MedicalStep extends Component
{
    use WithFileUploads;
    
    // Medical Information
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
    
    // References
    public $driverId;
    
    // Validation rules
    protected function rules()
    {
        $cardRequired = isset($this->medical_card_preview_url) && !empty($this->medical_card_preview_url)
            ? 'nullable|string' : 'required|string';
            
        return [
            'social_security_number' => 'required|string|max:255',
            'medical_examiner_name' => 'required|string|max:255',
            'medical_examiner_registry_number' => 'required|string|max:255',
            'medical_card_expiration_date' => 'required|date',
            'temp_medical_card_token' => $cardRequired,
            'suspension_date' => 'nullable|required_if:is_suspended,true|date',
            'termination_date' => 'nullable|required_if:is_terminated,true|date',
        ];
    }
    
    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'social_security_number' => 'required|string|max:255',
        ];
    }
    
    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        
        if ($this->driverId) {
            $this->loadExistingData();
        }
    }
    
    // Load existing data
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }
        
        $medicalQualification = $userDriverDetail->medicalQualification;
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
            
            // If exists a medical card, store the URL to show it
            if ($medicalQualification->hasMedia('medical_card')) {
                $this->medical_card_preview_url = $medicalQualification->getFirstMediaUrl('medical_card');
                $this->medical_card_filename = $medicalQualification->getFirstMedia('medical_card')->file_name;
            }
        }
    }
    
    // Save medical data to database
    protected function saveMedicalData()
    {
        try {
            DB::beginTransaction();
            
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }
            
            // Update or create medical qualification
            $medical = $userDriverDetail->medicalQualification()->updateOrCreate(
                [],
                [
                    'social_security_number' => $this->social_security_number,
                    'hire_date' => $this->hire_date,
                    'location' => $this->location,
                    'is_suspended' => $this->is_suspended,
                    'suspension_date' => $this->is_suspended ? $this->suspension_date : null,
                    'is_terminated' => $this->is_terminated,
                    'termination_date' => $this->is_terminated ? $this->termination_date : null,
                    'medical_examiner_name' => $this->medical_examiner_name,
                    'medical_examiner_registry_number' => $this->medical_examiner_registry_number,
                    'medical_card_expiration_date' => $this->medical_card_expiration_date
                ]
            );
            
            // Process medical card file
            if (!empty($this->temp_medical_card_token)) {
                $tempUploadService = app(TempUploadService::class);
                
                // Log para depuración
                Log::info('Processing medical card', [
                    'driver_id' => $this->driverId,
                    'token' => $this->temp_medical_card_token,
                    'session_id' => session()->getId(),
                    'temp_files' => array_keys(session('temp_files', []))
                ]);
                
                // Intenta obtener el archivo de la sesión
                $tempPath = $tempUploadService->moveToPermanent($this->temp_medical_card_token);
                
                // Si no se encuentra en la sesión, intenta buscarlo directamente en el almacenamiento
                if (!$tempPath || !file_exists($tempPath)) {
                    // Buscar en el almacenamiento por un patrón que coincida con el token
                    $tempFiles = session('temp_files', []);
                    Log::info('Buscando archivo en temp_files', ['temp_files' => $tempFiles]);
                    
                    // Si no podemos encontrarlo en la sesión, intentamos buscarlo directamente en el storage
                    $possiblePaths = [
                        storage_path('app/temp/medical_card'),
                        storage_path('app/temp')
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
                    $medical->clearMediaCollection('medical_card');
                    $medical->addMedia($tempPath)
                        ->toMediaCollection('medical_card');
                    Log::info('Medical card added to media collection');
                } else {
                    Log::error('Failed to process medical card - file not found');
                }
            } elseif (isset($this->medical_card_file)) {
                $medical->clearMediaCollection('medical_card');
                $medical->addMedia($this->medical_card_file->getRealPath())
                    ->toMediaCollection('medical_card');
                Log::info('Medical card file uploaded directly');
            }
            
            // Update current step
            $userDriverDetail->update(['current_step' => 5]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving medical information: ' . $e->getMessage());
            return false;
        }
    }
    
    // Handle medical card upload
    public function handleMedicalCardUpload($token, $filename, $previewUrl)
    {
        $this->temp_medical_card_token = $token;
        $this->medical_card_preview_url = $previewUrl;
        $this->medical_card_filename = $filename;
    }
    
    // Remove medical card
    public function removeMedicalCard()
    {
        $this->temp_medical_card_token = '';
        $this->medical_card_preview_url = null;
        $this->medical_card_filename = '';
    }
    
    // Next step
    public function next()
    {
        // Full validation
        $this->validate($this->rules());
        
        // Save to database
        if ($this->driverId) {
            $this->saveMedicalData();
        }
        
        // Move to next step
        $this->dispatch('nextStep');
    }
    
    // Previous step
    public function previous()
    {
        // Basic save before going back
        if ($this->driverId) {
            $this->validate($this->partialRules());
            $this->saveMedicalData();
        }
        
        $this->dispatch('prevStep');
    }
    
    // Save and exit
    public function saveAndExit()
    {
        // Basic validation
        $this->validate($this->partialRules());
        
        // Save to database
        if ($this->driverId) {
            $this->saveMedicalData();
        }
        
        $this->dispatch('saveAndExit');
    }
            
    // Render
    public function render()
    {
        return view('livewire.driver.steps.medical-step');
    }
}