<?php
namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverMedicalQualification;
use App\Services\Admin\TempUploadService;
use App\Helpers\DateHelper;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DriverMedicalStep extends Component
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
    public $medicalQualificationId;
    
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
    
    /**
     * Convierte una fecha a formato Y-m-d para almacenarla en la base de datos
     * Utiliza el DateHelper unificado
     */
    protected function formatDateForDatabase($date)
    {
        return DateHelper::toDatabase($date);
    }

    /**
     * Convierte una fecha del formato de base de datos a m/d/Y para mostrarla
     * Utiliza el DateHelper unificado
     */
    protected function formatDateForDisplay($date)
    {
        return DateHelper::toDisplay($date);
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
            $this->medicalQualificationId = $medicalQualification->id;
            $this->social_security_number = $medicalQualification->social_security_number ?? '';
            $this->hire_date = $medicalQualification->hire_date ? DateHelper::toDisplay($medicalQualification->hire_date->format('Y-m-d')) : '';
            $this->location = $medicalQualification->location ?? '';
            $this->is_suspended = $medicalQualification->is_suspended ?? false;
            $this->suspension_date = $medicalQualification->suspension_date ? DateHelper::toDisplay($medicalQualification->suspension_date->format('Y-m-d')) : '';
            $this->is_terminated = $medicalQualification->is_terminated ?? false;
            $this->termination_date = $medicalQualification->termination_date ? DateHelper::toDisplay($medicalQualification->termination_date->format('Y-m-d')) : '';
            $this->medical_examiner_name = $medicalQualification->medical_examiner_name ?? '';
            $this->medical_examiner_registry_number = $medicalQualification->medical_examiner_registry_number ?? '';
            $this->medical_card_expiration_date = $medicalQualification->medical_card_expiration_date ? DateHelper::toDisplay($medicalQualification->medical_card_expiration_date->format('Y-m-d')) : '';
            
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
            
            // First, create or update the medical qualification record
            $medical = $userDriverDetail->medicalQualification()->updateOrCreate(
                [],
                [
                    'social_security_number' => $this->social_security_number,
                    'hire_date' => $this->formatDateForDatabase($this->hire_date),
                    'location' => $this->location,
                    'is_suspended' => $this->is_suspended,
                    'suspension_date' => $this->is_suspended ? $this->formatDateForDatabase($this->suspension_date) : null,
                    'is_terminated' => $this->is_terminated,
                    'termination_date' => $this->is_terminated ? $this->formatDateForDatabase($this->termination_date) : null,
                    'medical_examiner_name' => $this->medical_examiner_name,
                    'medical_examiner_registry_number' => $this->medical_examiner_registry_number,
                    'medical_card_expiration_date' => $this->formatDateForDatabase($this->medical_card_expiration_date)
                ]
            );
            
            // Ensure we have the medical qualification ID for image processing
            $this->medicalQualificationId = $medical->id;
            
            Log::info('Medical qualification created/updated', [
                'medical_id' => $medical->id,
                'driver_id' => $this->driverId
            ]);
            
            // Note: Medical card images are now uploaded directly via API to the created record
            // The unified-image-upload component handles the file upload process
            // using the medical qualification ID that was just created/updated
            
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
    
    // Refresh medical card preview after permanent upload
    public function refreshMedicalCardPreview()
    {
        try {
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                return;
            }
            
            $medicalQualification = $userDriverDetail->medicalQualification;
            if ($medicalQualification && $medicalQualification->hasMedia('medical_card')) {
                $this->medical_card_preview_url = $medicalQualification->getFirstMediaUrl('medical_card');
                $this->medical_card_filename = $medicalQualification->getFirstMedia('medical_card')->file_name;
                
                Log::info('Medical card preview refreshed', [
                    'driver_id' => $this->driverId,
                    'preview_url' => $this->medical_card_preview_url,
                    'filename' => $this->medical_card_filename
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error refreshing medical card preview', [
                'error' => $e->getMessage(),
                'driver_id' => $this->driverId
            ]);
        }
    }
    
    // Remove medical card
    public function removeMedicalCard($uniqueId = null)
    {
        try {
            Log::info('Removing medical card', [
                'driver_id' => $this->driverId,
                'unique_id' => $uniqueId
            ]);

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                Log::error('Driver not found', ['driver_id' => $this->driverId]);
                return;
            }

            $medicalQualification = $userDriverDetail->medicalQualification;
            if ($medicalQualification && $medicalQualification->hasMedia('medical_card')) {
                // Delete from database and storage
                $medicalQualification->clearMediaCollection('medical_card');
                Log::info('Medical card deleted from database and storage');
            }

            // Clear temporary data
            $this->temp_medical_card_token = '';
            $this->medical_card_preview_url = null;
            $this->medical_card_filename = '';

            // Clear temp files if any
            $tempFiles = session('temp_files', []);
            foreach ($tempFiles as $token => $fileData) {
                if (isset($fileData['collection']) && $fileData['collection'] === 'medical_card') {
                    $tempPath = storage_path('app/temp/' . $fileData['filename']);
                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                        Log::info('Temporary medical card file deleted', ['path' => $tempPath]);
                    }
                    unset($tempFiles[$token]);
                }
            }
            session(['temp_files' => $tempFiles]);

            session()->flash('success', 'Medical card removed successfully.');
            
        } catch (\Exception $e) {
            Log::error('Error removing medical card', [
                'error' => $e->getMessage(),
                'driver_id' => $this->driverId
            ]);
            session()->flash('error', 'Error removing medical card: ' . $e->getMessage());
        }
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
    
    // Save medical information to create record and enable image upload
    public function saveMedicalInfo()
    {
        try {
            // Validate required fields for medical qualification
            $this->validate([
                'social_security_number' => 'required|string|max:255',
                'medical_examiner_name' => 'required|string|max:255',
                'medical_examiner_registry_number' => 'required|string|max:255',
                'medical_card_expiration_date' => 'required|date'
            ]);
            
            DB::beginTransaction();
            
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }
            
            // Create or update the medical qualification record
            $medical = $userDriverDetail->medicalQualification()->updateOrCreate(
                [],
                [
                    'social_security_number' => $this->social_security_number,
                    'hire_date' => $this->formatDateForDatabase($this->hire_date),
                    'location' => $this->location,
                    'is_suspended' => $this->is_suspended,
                    'suspension_date' => $this->is_suspended ? $this->formatDateForDatabase($this->suspension_date) : null,
                    'is_terminated' => $this->is_terminated,
                    'termination_date' => $this->is_terminated ? $this->formatDateForDatabase($this->termination_date) : null,
                    'medical_examiner_name' => $this->medical_examiner_name,
                    'medical_examiner_registry_number' => $this->medical_examiner_registry_number,
                    'medical_card_expiration_date' => $this->formatDateForDatabase($this->medical_card_expiration_date)
                ]
            );
            
            // Set the medical qualification ID to enable image upload
            $this->medicalQualificationId = $medical->id;
            
            Log::info('Medical qualification saved for image upload', [
                'medical_id' => $medical->id,
                'driver_id' => $this->driverId
            ]);
            
            DB::commit();
            
            session()->flash('success', 'Medical information saved successfully. You can now upload the medical card image.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions to show field errors
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving medical information', [
                'error' => $e->getMessage(),
                'driver_id' => $this->driverId
            ]);
            session()->flash('error', 'Error saving medical information: ' . $e->getMessage());
        }
    }

    /**
     * Compress and resize image to optimize file size
     * @param string $filePath Path to the image file
     * @return bool Success status
     */
    private function compressAndResizeImage($filePath)
    {
        try {
            // Create image manager with GD driver
            $manager = new ImageManager(new Driver());
            
            // Read the image
            $image = $manager->read($filePath);
            
            // Get original dimensions
            $originalWidth = $image->width();
            $originalHeight = $image->height();
            
            // Calculate new dimensions (max width 800px, maintain aspect ratio)
            $maxWidth = 800;
            if ($originalWidth > $maxWidth) {
                $ratio = $maxWidth / $originalWidth;
                $newWidth = $maxWidth;
                $newHeight = (int)($originalHeight * $ratio);
                
                // Resize the image
                $image->resize($newWidth, $newHeight);
                
                Log::info('Image resized', [
                    'original' => $originalWidth . 'x' . $originalHeight,
                    'new' => $newWidth . 'x' . $newHeight,
                    'file' => $filePath
                ]);
            }
            
            // Save with compression (80% quality for JPEG)
            $image->toJpeg(80)->save($filePath);
            
            Log::info('Image compressed successfully', [
                'file' => $filePath,
                'size_after' => filesize($filePath)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error compressing image', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
            
    // Render
    public function render()
    {
        return view('livewire.driver.steps.medical-step');
    }
}