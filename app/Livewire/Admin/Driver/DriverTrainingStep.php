<?php

namespace App\Livewire\Admin\Driver;

use App\Helpers\Constants;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use App\Services\Admin\TempUploadService;
use Illuminate\Support\Str;

class DriverTrainingStep extends Component
{
    use WithFileUploads;

    // Training Schools
    public $has_attended_training_school = false;
    public $training_schools = [];

    // References
    public $driverId;

    protected $listeners = [
        'certificates-updated' => '$refresh'
    ];

    // Validation rules
    protected function rules()
    {
        $rules = [
            'has_attended_training_school' => 'sometimes|boolean',
        ];

        if ($this->has_attended_training_school) {
            foreach (range(0, count($this->training_schools) - 1) as $index) {
                $rules["training_schools.{$index}.school_name"] = 'required|string|max:255';
                $rules["training_schools.{$index}.city"] = 'required|string|max:255';
                $rules["training_schools.{$index}.state"] = 'required|string|max:255';
                $rules["training_schools.{$index}.date_start"] = 'required|date';
                $rules["training_schools.{$index}.date_end"] =
                    "required|date|after_or_equal:training_schools.{$index}.date_start";
            }
        }

        return $rules;
    }

    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'has_attended_training_school' => 'sometimes|boolean',
        ];
    }

    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;

        if ($this->driverId) {
            $this->loadExistingData();
        }

        // Initialize with empty training school
        if ($this->has_attended_training_school && empty($this->training_schools)) {
            $this->training_schools = [$this->getEmptyTrainingSchool()];
        }
    }

    // Load existing data
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }

        // Default value
        $this->has_attended_training_school = false;

        // Check if attended training school from application details
        if ($userDriverDetail->application && $userDriverDetail->application->details) {
            $this->has_attended_training_school = (bool)(
                $userDriverDetail->application->details->has_attended_training_school ?? false
            );
        }

        // Load training schools
        $trainingSchools = $userDriverDetail->trainingSchools;
        if ($trainingSchools->count() > 0) {
            $this->has_attended_training_school = true;
            $this->training_schools = [];
            foreach ($trainingSchools as $school) {
                $certificates = [];
                if ($school->hasMedia('school_certificates')) {
                    foreach ($school->getMedia('school_certificates') as $certificate) {
                        $certificates[] = [
                            'id' => $certificate->id,
                            'filename' => $certificate->file_name,
                            'url' => $certificate->getUrl(),
                            'is_image' => Str::startsWith($certificate->mime_type, 'image/'),
                        ];
                    }
                }

                $this->training_schools[] = [
                    'id' => $school->id,
                    'school_name' => $school->school_name ?? '',
                    'city' => $school->city ?? '',
                    'state' => $school->state ?? '',
                    'phone_number' => $school->phone_number ?? '',
                    'date_start' => $school->date_start ? $school->date_start->format('Y-m-d') : null,
                    'date_end' => $school->date_end ? $school->date_end->format('Y-m-d') : null,
                    'graduated' => $school->graduated ?? false,
                    'subject_to_safety_regulations' => $school->subject_to_safety_regulations ?? false,
                    'performed_safety_functions' => $school->performed_safety_functions ?? false,
                    'training_skills' => $school->training_skills ?? [],
                    'certificates' => $certificates,
                    'temp_certificate_tokens' => []
                ];
            }
        }

        // Initialize with empty training school if needed
        if ($this->has_attended_training_school && empty($this->training_schools)) {
            $this->training_schools = [$this->getEmptyTrainingSchool()];
        }
    }

    // Save training data to database
    protected function saveTrainingData()
    {
        try {
            DB::beginTransaction();

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }

            // Update application details with training school flag
            if ($userDriverDetail->application && $userDriverDetail->application->details) {
                $userDriverDetail->application->details->update([
                    'has_attended_training_school' => $this->has_attended_training_school // Esto guardará true o false explícitamente
                ]);
            }

            if (!$this->has_attended_training_school) {
                // If no training schools, delete all existing records
                $userDriverDetail->trainingSchools->each(function ($school) {
                    $school->getMedia('school_certificates')->each->delete();
                    $school->delete();
                });
            } else {
                // Handle training schools
                $existingSchoolIds = $userDriverDetail->trainingSchools()->pluck('id')->toArray();
                $updatedSchoolIds = [];

                foreach ($this->training_schools as $schoolData) {
                    if (empty($schoolData['school_name'])) continue;

                    $schoolId = $schoolData['id'] ?? null;
                    if ($schoolId) {
                        // Update existing school
                        $school = $userDriverDetail->trainingSchools()->find($schoolId);
                        if ($school) {
                            $school->update([
                                'school_name' => $schoolData['school_name'],
                                'city' => $schoolData['city'] ?? '',
                                'state' => $schoolData['state'] ?? '',
                                'phone_number' => $schoolData['phone_number'] ?? '',
                                'date_start' => $schoolData['date_start'] ?? now(),
                                'date_end' => $schoolData['date_end'] ?? now(),
                                'graduated' => isset($schoolData['graduated']),
                                'subject_to_safety_regulations' => isset($schoolData['subject_to_safety_regulations']),
                                'performed_safety_functions' => isset($schoolData['performed_safety_functions']),
                                'training_skills' => $schoolData['training_skills'] ?? []
                            ]);
                            $updatedSchoolIds[] = $school->id;

                            // Process certificates
                            $this->processSchoolCertificates($school, $schoolData);
                        }
                    } else {
                        // Create new school
                        $school = $userDriverDetail->trainingSchools()->create([
                            'school_name' => $schoolData['school_name'],
                            'city' => $schoolData['city'] ?? '',
                            'state' => $schoolData['state'] ?? '',
                            'phone_number' => $schoolData['phone_number'] ?? '',
                            'date_start' => $schoolData['date_start'] ?? now(),
                            'date_end' => $schoolData['date_end'] ?? now(),
                            'graduated' => isset($schoolData['graduated']),
                            'subject_to_safety_regulations' => isset($schoolData['subject_to_safety_regulations']),
                            'performed_safety_functions' => isset($schoolData['performed_safety_functions']),
                            'training_skills' => $schoolData['training_skills'] ?? []
                        ]);
                        $updatedSchoolIds[] = $school->id;

                        // Process certificates
                        $this->processSchoolCertificates($school, $schoolData);
                    }
                }

                // Delete schools that are no longer needed
                foreach (array_diff($existingSchoolIds, $updatedSchoolIds) as $schoolId) {
                    $school = $userDriverDetail->trainingSchools()->find($schoolId);
                    if ($school) {
                        $school->getMedia('school_certificates')->each->delete();
                        $school->delete();
                    }
                }
            }

            // Update current step
            $userDriverDetail->update(['current_step' => 6]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving training information: ' . $e->getMessage());
            return false;
        }
    }

    // Process school certificates
    protected function processSchoolCertificates($school, $schoolData)
    {
        $tempUploadService = app(TempUploadService::class);

        // Ensure relationship is loaded
        $school->load('userDriverDetail');

        if (!empty($schoolData['temp_certificate_tokens'])) {
            foreach ($schoolData['temp_certificate_tokens'] as $certData) {
                if (empty($certData['token'])) continue;

                $tempPath = $tempUploadService->moveToPermanent($certData['token']);
                if ($tempPath && file_exists($tempPath)) {
                    $school->addMedia($tempPath)
                        ->toMediaCollection('school_certificates');
                }
            }
        }
    }

    // Add training school
    public function addTrainingSchool()
    {
        $this->training_schools[] = $this->getEmptyTrainingSchool();
    }

    // Remove training school
    public function removeTrainingSchool($index)
    {
        if (count($this->training_schools) > 1) {
            unset($this->training_schools[$index]);
            $this->training_schools = array_values($this->training_schools);
        }
    }

    // Toggle training skill
    public function toggleTrainingSkill($schoolIndex, $skill)
    {
        if (!isset($this->training_schools[$schoolIndex]['training_skills'])) {
            $this->training_schools[$schoolIndex]['training_skills'] = [];
        }

        $index = array_search($skill, $this->training_schools[$schoolIndex]['training_skills']);
        if ($index !== false) {
            // Remove skill if already exists
            unset($this->training_schools[$schoolIndex]['training_skills'][$index]);
            $this->training_schools[$schoolIndex]['training_skills'] = array_values(
                $this->training_schools[$schoolIndex]['training_skills']
            );
        } else {
            // Add skill
            $this->training_schools[$schoolIndex]['training_skills'][] = $skill;
        }
    }

    // Add certificate
    public function addCertificate($schoolIndex, $token, $filename, $previewUrl = null, $fileType = null)
    {
        if (!isset($this->training_schools[$schoolIndex]['temp_certificate_tokens'])) {
            $this->training_schools[$schoolIndex]['temp_certificate_tokens'] = [];
        }
    
        $this->training_schools[$schoolIndex]['temp_certificate_tokens'][] = [
            'token' => $token,
            'filename' => $filename,
            'preview_url' => $previewUrl,
            'file_type' => $fileType
        ];
        
        // Forzar actualización completa
        $this->dispatch('certificates-updated');
    }

    // Remove certificate
    public function removeCertificate($schoolIndex, $tokenIndex)
    {
        unset($this->training_schools[$schoolIndex]['temp_certificate_tokens'][$tokenIndex]);
        $this->training_schools[$schoolIndex]['temp_certificate_tokens'] = array_values(
            $this->training_schools[$schoolIndex]['temp_certificate_tokens']
        );
        
        // Forzar actualización completa
        $this->dispatch('certificates-updated');
    }

    // Remove certificate by ID (for existing certificates)
    public function removeCertificateById($schoolIndex, $certificateId)
    {
        try {
            if (!$this->driverId) return false;

            $schoolData = $this->training_schools[$schoolIndex] ?? null;
            if (!$schoolData || empty($schoolData['id'])) return false;

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) return false;

            $school = $userDriverDetail->trainingSchools()->find($schoolData['id']);
            if (!$school) return false;

            $mediaItem = $school->getMedia('school_certificates')->firstWhere('id', $certificateId);
            if ($mediaItem) {
                // Log de eliminación para debugging
                Log::info('Eliminando certificado', [
                    'media_id' => $certificateId,
                    'school_id' => $school->id,
                    'driver_id' => $this->driverId
                ]);

                // Eliminar el archivo
                $mediaItem->delete();

                // Actualizar la lista de certificados en el componente
                $this->refreshSchoolData($schoolIndex, $school);

                // Forzar actualización completa
                $this->dispatch('certificates-updated');

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error removiendo certificado', [
                'message' => $e->getMessage(),
                'schoolIndex' => $schoolIndex,
                'certificateId' => $certificateId
            ]);
            return false;
        }
    }

    private function refreshCertificates($schoolIndex, $school)
    {
        // Actualizar la lista de certificados en el componente
        $certificates = [];

        if ($school->hasMedia('school_certificates')) {
            foreach ($school->getMedia('school_certificates') as $certificate) {
                $certificates[] = [
                    'id' => $certificate->id,
                    'filename' => $certificate->file_name,
                    'url' => $certificate->getUrl(),
                    'is_image' => Str::startsWith($certificate->mime_type, 'image/'),
                ];
            }
        }

        $this->training_schools[$schoolIndex]['certificates'] = $certificates;
    }

    public function clearAllCertificates($schoolIndex)
    {
        try {
            if (!$this->driverId) return false;

            $schoolData = $this->training_schools[$schoolIndex] ?? null;
            if (!$schoolData || empty($schoolData['id'])) return false;

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) return false;

            $school = $userDriverDetail->trainingSchools()->find($schoolData['id']);
            if (!$school) return false;

            // Eliminar todos los certificados
            $school->clearMediaCollection('school_certificates');

            // Actualizar el componente por completo
            $this->refreshSchoolData($schoolIndex, $school);

            // Forzar actualización completa
            $this->dispatch('certificates-updated');

            return true;
        } catch (\Exception $e) {
            Log::error('Error eliminando todos los certificados', [
                'message' => $e->getMessage(),
                'schoolIndex' => $schoolIndex
            ]);
            return false;
        }
    }

    private function refreshSchoolData($schoolIndex, $school) 
{
    // Asegúrate que la escuela esté recargada con sus relaciones
    $school->refresh();
    
    // Actualiza los certificados
    $certificates = [];
    if ($school->hasMedia('school_certificates')) {
        foreach ($school->getMedia('school_certificates') as $certificate) {
            $certificates[] = [
                'id' => $certificate->id,
                'filename' => $certificate->file_name,
                'url' => $certificate->getUrl(),
                'is_image' => Str::startsWith($certificate->mime_type, 'image/'),
            ];
        }
    }
    
    // Actualiza la escuela completa en el array
    $this->training_schools[$schoolIndex]['certificates'] = $certificates;
}
    // Get empty training school structure
    protected function getEmptyTrainingSchool()
    {
        return [
            'school_name' => '',
            'city' => '',
            'state' => '',
            'phone_number' => '',
            'date_start' => '',
            'date_end' => '',
            'graduated' => false,
            'subject_to_safety_regulations' => false,
            'performed_safety_functions' => false,
            'training_skills' => [],
            'temp_certificate_tokens' => []
        ];
    }

    // Next step
    public function next()
    {
        // Full validation
        $this->validate($this->rules());

        // Save to database
        if ($this->driverId) {
            $this->saveTrainingData();
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
            $this->saveTrainingData();
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
            $this->saveTrainingData();
        }

        $this->dispatch('saveAndExit');
    }

    // Render
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-training-step', [
            'usStates' => Constants::usStates(),
        ]);
    }
}
