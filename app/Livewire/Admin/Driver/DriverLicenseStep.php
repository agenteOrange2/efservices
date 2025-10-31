<?php
namespace App\Livewire\Admin\Driver;

use App\Helpers\Constants;
use App\Helpers\DateHelper;
use App\Traits\DriverValidationTrait;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;    
use App\Models\Admin\Driver\DriverLicense;
use App\Models\Admin\Driver\LicenseEndorsement;
use App\Services\Admin\TempUploadService;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class DriverLicenseStep extends Component
{
    use WithFileUploads, DriverValidationTrait;

    // License Information
    public $current_license_number = '';
    public $licenses = [];

    // Driving Experience
    public $experiences = [];

    // File uploads
    public $front_image;
    public $back_image;
    public $current_license_index = 0;

    // References
    public $driverId;

    // Validation rules
    protected function rules()
    {
        return array_merge(
            $this->getDriverRegistrationRules('license'),
            [
                'current_license_number' => 'required|string|max:255',
                'licenses.*.license_number' => 'required|string|max:255',
                'licenses.*.state_of_issue' => 'required|string|max:255',
                'licenses.*.license_class' => 'required|string|max:255',
                'licenses.*.expiration_date' => $this->getExpirationDateValidationRules(),
                'experiences.*.equipment_type' => 'required|string|max:255',
                'experiences.*.years_experience' => $this->getYearsExperienceValidationRules(),
                'experiences.*.miles_driven' => 'required|integer|min:0',
            ]
        );
    }

    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'current_license_number' => 'required|string|max:255',
        ];
    }

    // Custom validation messages
    protected function messages()
    {
        return $this->getValidationMessages();
    }

    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;

        if ($this->driverId) {
            $this->loadExistingData();
        }

        // Initialize with empty license and experience
        if (empty($this->licenses)) {
            $this->licenses = [$this->getEmptyLicense()];
        }

        if (empty($this->experiences)) {
            $this->experiences = [$this->getEmptyExperience()];
        }
    }

    // Load existing data
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }

        // Load current license number
        $primaryLicense = $userDriverDetail->licenses()->where('is_primary', true)->first();
        $this->current_license_number = $primaryLicense ? $primaryLicense->current_license_number : '';

        // Load licenses
        $licenses = $userDriverDetail->licenses;
        if ($licenses->count() > 0) {
            $this->licenses = [];
            foreach ($licenses as $license) {
                $this->licenses[] = [
                    'unique_id' => 'license_' . $license->id . '_' . uniqid(),
                    'id' => $license->id,
                    'license_number' => $license->license_number,
                    'state_of_issue' => $license->state_of_issue,
                    'license_class' => $license->license_class,
                    'expiration_date' => $license->expiration_date ? DateHelper::toDisplay($license->expiration_date->format('Y-m-d')) : '',
                    'is_cdl' => $license->is_cdl,
                    'is_primary' => $license->is_primary,
                    'endorsements' => $license->endorsements ? $license->endorsements->pluck('code')->toArray() : [],
                    'front_preview' => $license->getFirstMediaUrl('license_front') ?: null,
                    'back_preview' => $license->getFirstMediaUrl('license_back') ?: null,
                    'front_filename' => $license->getFirstMedia('license_front')?->file_name ?? '',
                    'back_filename' => $license->getFirstMedia('license_back')?->file_name ?? ''
                ];
            }
        }

        // Load experiences
        $experiences = $userDriverDetail->experiences;
        if ($experiences->count() > 0) {
            $this->experiences = [];
            foreach ($experiences as $exp) {
                $this->experiences[] = [
                    'id' => $exp->id,
                    'equipment_type' => $exp->equipment_type,
                    'years_experience' => $exp->years_experience,
                    'miles_driven' => $exp->miles_driven,
                    'requires_cdl' => $exp->requires_cdl,
                ];
            }
        }
    }

    // Save license data to database
    protected function saveLicenseData()
    {
        try {

            Log::info('Starting license save', ['driver_id' => $this->driverId]);
            DB::beginTransaction();

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            Log::info('Found user driver detail', ['exists' => $userDriverDetail ? true : false]);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }

            // Update licenses
            $existingLicenseIds = $userDriverDetail->licenses()->pluck('id')->toArray();
            $updatedLicenseIds = [];

            foreach ($this->licenses as $index => $licenseInfo) {
                if (empty($licenseInfo['license_number'])) continue;

                $licenseId = $licenseInfo['id'] ?? null;
                if ($licenseId) {
                    // Update existing license
                    $license = $userDriverDetail->licenses()->find($licenseId);
                    if ($license) {
                        $license->update([
                            'current_license_number' => $this->current_license_number,
                            'license_number' => $licenseInfo['license_number'],
                            'state_of_issue' => $licenseInfo['state_of_issue'] ?? '',
                            'license_class' => $licenseInfo['license_class'] ?? '',
                            'expiration_date' => DateHelper::toDatabase($licenseInfo['expiration_date']) ?? now(),
                            'is_cdl' => isset($licenseInfo['is_cdl']),
                            'is_primary' => $index === 0,
                            'status' => 'active'
                        ]);
                        $updatedLicenseIds[] = $license->id;

                        // Update endorsements
                        $this->updateLicenseEndorsements($license, $licenseInfo);

                        // Images are now uploaded directly to existing licenses, no migration needed
                        
                        // Update license array with current license ID
                        $this->licenses[$index]['id'] = $license->id;
                    }
                } else {
                    // Create new license
                    $license = $userDriverDetail->licenses()->create([
                        'current_license_number' => $this->current_license_number,
                        'license_number' => $licenseInfo['license_number'],
                        'state_of_issue' => $licenseInfo['state_of_issue'] ?? '',
                        'license_class' => $licenseInfo['license_class'] ?? '',
                        'expiration_date' => DateHelper::toDatabase($licenseInfo['expiration_date']) ?? now(),
                        'is_cdl' => isset($licenseInfo['is_cdl']),
                        'is_primary' => $index === 0,
                        'status' => 'active'
                    ]);
                    $updatedLicenseIds[] = $license->id;

                    // Add endorsements
                    if (isset($licenseInfo['is_cdl']) && isset($licenseInfo['endorsements'])) {
                        foreach ($licenseInfo['endorsements'] as $code) {
                            $endorsement = LicenseEndorsement::firstOrCreate(
                                ['code' => $code],
                                [
                                    'name' => $this->getEndorsementName($code),
                                    'description' => null,
                                    'is_active' => true
                                ]
                            );
                            $license->endorsements()->attach($endorsement->id, [
                                'issued_date' => now(),
                                'expiration_date' => DateHelper::toDatabase($licenseInfo['expiration_date']) ?? now()
                            ]);
                        }
                    }

                    // Images are now uploaded directly to existing licenses, no migration needed
                }
            }
            Log::info('Updated license IDs', ['ids' => $updatedLicenseIds]);

            // Delete licenses that are no longer needed
            $licensesToDelete = array_diff($existingLicenseIds, $updatedLicenseIds);
            if (!empty($licensesToDelete)) {
                $userDriverDetail->licenses()->whereIn('id', $licensesToDelete)->delete();
            }

            // Update experiences
            $existingExpIds = $userDriverDetail->experiences()->pluck('id')->toArray();
            $updatedExpIds = [];

            foreach ($this->experiences as $expData) {
                if (empty($expData['equipment_type'])) continue;

                $expId = $expData['id'] ?? null;
                if ($expId) {
                    // Update existing experience
                    $experience = $userDriverDetail->experiences()->find($expId);
                    if ($experience) {
                        $experience->update([
                            'equipment_type' => $expData['equipment_type'],
                            'years_experience' => $expData['years_experience'] ?? 0,
                            'miles_driven' => $expData['miles_driven'] ?? 0,
                            'requires_cdl' => $expData['requires_cdl'] ?? false
                        ]);
                        $updatedExpIds[] = $experience->id;
                    }
                } else {
                    // Create new experience
                    $experience = $userDriverDetail->experiences()->create([
                        'equipment_type' => $expData['equipment_type'],
                        'years_experience' => $expData['years_experience'] ?? 0,
                        'miles_driven' => $expData['miles_driven'] ?? 0,
                        'requires_cdl' => $expData['requires_cdl'] ?? false
                    ]);
                    $updatedExpIds[] = $experience->id;
                }
            }

            Log::info('Updated experience IDs', ['ids' => $updatedExpIds]);
            // Delete experiences that are no longer needed
            $expsToDelete = array_diff($existingExpIds, $updatedExpIds);
            if (!empty($expsToDelete)) {
                $userDriverDetail->experiences()->whereIn('id', $expsToDelete)->delete();
            }

            // Update current step
            $userDriverDetail->update(['current_step' => 4]);

            DB::commit();
            Log::info('License data saved successfully');
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License save error', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error saving license information: ' . $e->getMessage());
            return false;
        }
    }

    // Update license endorsements
    protected function updateLicenseEndorsements($license, $licenseInfo)
    {
        if (isset($licenseInfo['is_cdl']) && isset($licenseInfo['endorsements'])) {
            // Remove existing endorsements
            $license->endorsements()->detach();

            // Add new endorsements
            foreach ($licenseInfo['endorsements'] as $code) {
                $endorsement = LicenseEndorsement::firstOrCreate(
                    ['code' => $code],
                    [
                        'name' => $this->getEndorsementName($code),
                        'description' => null,
                        'is_active' => true
                    ]
                );
                $license->endorsements()->attach($endorsement->id, [
                    'issued_date' => now(),
                    'expiration_date' => DateHelper::toDatabase($licenseInfo['expiration_date']) ?? now()
                ]);
            }
        }
    }

    // Note: Temporary image methods removed - licenses now created before image upload

    // Get endorsement name from code
    private function getEndorsementName($code)
    {
        $endorsements = [
            'H' => 'Hazardous Materials',
            'N' => 'Tank Vehicle',
            'P' => 'Passenger',
            'T' => 'Double/Triple Trailers',
            'X' => 'Combination of tank vehicle and hazardous materials',
            'S' => 'School Bus'
        ];
        return $endorsements[$code] ?? 'Unknown Endorsement';
    }

    // Add license
    public function addLicense()
    {
        $this->licenses[] = $this->getEmptyLicense();
    }

    // Remove license
    public function removeLicense($index)
    {
        if (count($this->licenses) > 1) {
            unset($this->licenses[$index]);
            $this->licenses = array_values($this->licenses);
        }
    }

    // Add experience
    public function addExperience()
    {
        $this->experiences[] = $this->getEmptyExperience();
    }

    // Remove experience
    public function removeExperience($index)
    {
        if (count($this->experiences) > 1) {
            unset($this->experiences[$index]);
            $this->experiences = array_values($this->experiences);
        }
    }

    // Get empty license structure
    protected function getEmptyLicense()
    {
        return [
            'unique_id' => uniqid('license_', true),
            'license_number' => '',
            'state_of_issue' => '',
            'license_class' => '',
            'expiration_date' => '',
            'is_cdl' => false,
            'endorsements' => [],
            'front_preview' => '',
            'front_filename' => '',
            'back_preview' => '',
            'back_filename' => ''
        ];
    }

    // Get empty experience structure
    protected function getEmptyExperience()
    {
        return [
            'equipment_type' => '',
            'years_experience' => '',
            'miles_driven' => '',
            'requires_cdl' => false
        ];
    }

    // Handle temporary upload for license images
    public function handleLicenseImageUpload($index, $side, $token, $filename, $previewUrl)
    {
        $this->licenses[$index]['temp_' . $side . '_token'] = $token;
        $this->licenses[$index][$side . '_preview'] = $previewUrl;
        $this->licenses[$index][$side . '_filename'] = $filename;
    }

    // Remove license image (both temporary and permanent)
    public function removeLicenseImage($uniqueId, $side)
    {
        try {
            // Find the license index by unique_id
            $index = null;
            foreach ($this->licenses as $i => $license) {
                if (isset($license['unique_id']) && $license['unique_id'] === $uniqueId) {
                    $index = $i;
                    break;
                }
            }
            
            // If license not found, log error and return
            if ($index === null) {
                Log::error('License not found for deletion', [
                    'unique_id' => $uniqueId,
                    'side' => $side,
                    'available_licenses' => array_column($this->licenses, 'unique_id')
                ]);
                $this->dispatch('imageDeleteError', 'No se pudo encontrar la licencia para eliminar la imagen.');
                return;
            }
            
            $license = $this->licenses[$index];
            
            // If it's a permanent image (has license ID), delete from Media Library
            if (isset($license['id']) && $license['id']) {
                $licenseModel = DriverLicense::find($license['id']);
                if ($licenseModel) {
                    $collection = 'license_' . $side;
                    $media = $licenseModel->getFirstMedia($collection);
                    if ($media) {
                        $media->delete();
                        Log::info('Deleted permanent license image', [
                            'license_id' => $license['id'],
                            'collection' => $collection,
                            'media_id' => $media->id
                        ]);
                    }
                }
            }
            
            // If it's a temporary image, remove from temp storage
            if (!empty($license['temp_' . $side . '_token'])) {
                $tempFiles = session('temp_files', []);
                $token = $license['temp_' . $side . '_token'];
                if (isset($tempFiles[$token])) {
                    $tempPath = storage_path('app/' . $tempFiles[$token]['path']);
                    $publicTempPath = public_path('storage/temp/' . basename($tempFiles[$token]['path']));
                    
                    // Delete temp files
                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                    if (file_exists($publicTempPath)) {
                        unlink($publicTempPath);
                    }
                    
                    // Remove from session
                    unset($tempFiles[$token]);
                    session(['temp_files' => $tempFiles]);
                }
            }
            
            // Clear the license data
            $this->licenses[$index]['temp_' . $side . '_token'] = '';
            $this->licenses[$index][$side . '_preview'] = '';
            $this->licenses[$index][$side . '_filename'] = '';
            
            // Emit success message
            $this->dispatch('imageDeleted', 'Imagen eliminada correctamente');
            
        } catch (\Exception $e) {
            Log::error('Error removing license image', [
                'error' => $e->getMessage(),
                'unique_id' => $uniqueId,
                'index' => $index ?? 'not_found',
                'side' => $side
            ]);
            $this->dispatch('imageDeleteError', 'Error al eliminar la imagen: ' . $e->getMessage());
        }
    }

    // Refresh license preview after permanent upload
    public function refreshLicensePreview($licenseIndex = null, $side = null)
    {
        try {
            // Validate that both parameters are provided
            if ($licenseIndex === null || $side === null) {
                Log::error('Missing required parameters for license preview refresh', [
                    'licenseIndex' => $licenseIndex,
                    'side' => $side
                ]);
                return;
            }
            
            if (!isset($this->licenses[$licenseIndex])) {
                Log::error('License index not found for preview refresh', [
                    'index' => $licenseIndex,
                    'available_licenses' => count($this->licenses)
                ]);
                return;
            }

            $license = $this->licenses[$licenseIndex];
            
            // Only refresh if license has an ID (exists in database)
            if (isset($license['id']) && $license['id']) {
                $licenseModel = DriverLicense::find($license['id']);
                if ($licenseModel) {
                    $collection = 'license_' . $side;
                    if ($licenseModel->hasMedia($collection)) {
                        $this->licenses[$licenseIndex][$side . '_preview'] = $licenseModel->getFirstMediaUrl($collection);
                        $this->licenses[$licenseIndex][$side . '_filename'] = $licenseModel->getFirstMedia($collection)->file_name;
                        
                        Log::info('License preview refreshed', [
                            'license_id' => $license['id'],
                            'side' => $side,
                            'preview_url' => $this->licenses[$licenseIndex][$side . '_preview'],
                            'filename' => $this->licenses[$licenseIndex][$side . '_filename']
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error refreshing license preview', [
                'error' => $e->getMessage(),
                'license_index' => $licenseIndex,
                'side' => $side
            ]);
        }
    }

    // Next step
    public function next()
    {
        // Full validation
        $this->validate($this->rules());

        // Save to database
        if ($this->driverId) {
            $this->saveLicenseData();
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
            $this->saveLicenseData();
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
            $this->saveLicenseData();
        }

        $this->dispatch('saveAndExit');
    }

    // Note: Temporary image upload methods removed - images now uploaded directly to existing licenses

    // Set current license index for uploads
    public function setCurrentLicenseIndex($index)
    {
        $this->current_license_index = $index;
    }

    // Create individual license
    public function createLicense($index)
    {
        try {
            // Validate the specific license data
            $this->validateLicenseAtIndex($index);
            
            $licenseData = $this->licenses[$index];
            
            // Create the license record
            $license = DriverLicense::create([
                'user_driver_detail_id' => $this->driverId,
                'current_license_number' => $licenseData['license_number'],
                'license_number' => $licenseData['license_number'],
                'state_of_issue' => $licenseData['state_of_issue'],
                'license_class' => $licenseData['license_class'],
                'expiration_date' => Carbon::parse($licenseData['expiration_date'])->format('Y-m-d'),
                'is_cdl' => $licenseData['is_cdl'] ?? false,
            ]);
            
            // Update the license array with the new ID and unique_id
            $this->licenses[$index]['id'] = $license->id;
            $this->licenses[$index]['unique_id'] = 'license_' . $license->id . '_' . substr(md5($license->id . time()), 0, 8);
            
            Log::info('License created successfully', [
                'license_id' => $license->id,
                'driver_id' => $this->driverId,
                'index' => $index
            ]);
            
            session()->flash('success', 'Licencia creada exitosamente. Ahora puede subir las imágenes.');
            
        } catch (\ValidationException $e) {
            // Re-throw validation exceptions to show field errors
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error creating license', [
                'error' => $e->getMessage(),
                'index' => $index,
                'driver_id' => $this->driverId
            ]);
            session()->flash('error', 'Error al crear la licencia: ' . $e->getMessage());
        }
    }
    
    // Update individual license
    public function updateLicense($index)
    {
        try {
            // Validate the specific license data
            $this->validateLicenseAtIndex($index);
            
            $licenseData = $this->licenses[$index];
            
            if (empty($licenseData['id'])) {
                throw new \Exception('No se puede actualizar una licencia sin ID');
            }
            
            // Find and update the license record
            $license = DriverLicense::findOrFail($licenseData['id']);
            $license->update([
                'current_license_number' => $licenseData['license_number'],
                'license_number' => $licenseData['license_number'],
                'state_of_issue' => $licenseData['state_of_issue'],
                'license_class' => $licenseData['license_class'],
                'expiration_date' => Carbon::parse($licenseData['expiration_date'])->format('Y-m-d'),
                'is_cdl' => $licenseData['is_cdl'] ?? false,
                'endorsements' => $licenseData['endorsements'] ?? [],
            ]);
            
            Log::info('License updated successfully', [
                'license_id' => $license->id,
                'driver_id' => $this->driverId,
                'index' => $index
            ]);
            
            session()->flash('success', 'Licencia actualizada exitosamente.');
            
        } catch (\ValidationException $e) {
            // Re-throw validation exceptions to show field errors
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating license', [
                'error' => $e->getMessage(),
                'index' => $index,
                'license_id' => $licenseData['id'] ?? 'unknown'
            ]);
            session()->flash('error', 'Error al actualizar la licencia: ' . $e->getMessage());
        }
    }
    
    // Validate license data at specific index
    private function validateLicenseAtIndex($index)
    {
        $rules = [
            "licenses.{$index}.license_number" => 'required|string|max:50',
            "licenses.{$index}.state_of_issue" => 'required|string|size:2',
            "licenses.{$index}.license_class" => 'required|string|in:A,B,C',
            "licenses.{$index}.expiration_date" => 'required|date|after:today',
            "licenses.{$index}.is_cdl" => 'boolean',
            "licenses.{$index}.endorsements" => 'array',
            "licenses.{$index}.endorsements.*" => 'string|in:N,H,X,T,P,S',
        ];
        
        $messages = [
            "licenses.{$index}.license_number.required" => 'El número de licencia es requerido.',
            "licenses.{$index}.state_of_issue.required" => 'El estado de emisión es requerido.',
            "licenses.{$index}.license_class.required" => 'La clase de licencia es requerida.',
            "licenses.{$index}.expiration_date.required" => 'La fecha de expiración es requerida.',
            "licenses.{$index}.expiration_date.after" => 'La fecha de expiración debe ser posterior a hoy.',
        ];
        
        $this->validate($rules, $messages);
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
        return view('livewire.admin.driver.steps.driver-license-step', [
            'usStates' => Constants::usStates()
        ]);
    }
}