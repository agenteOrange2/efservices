<?php

namespace App\Livewire\Admin\Driver;

use App\Helpers\Constants;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\UserDriverDetail;
use App\Services\Admin\TempUploadService;
use Illuminate\Support\Str;

class DriverTrainingStep extends Component
{
    use WithFileUploads;

    // Training Schools
    public $has_attended_training_school = false;
    public $training_schools = [];

    // Courses
    public $has_completed_courses = false;
    public $courses = [];

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
            'has_completed_courses' => 'sometimes|boolean',
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

        if ($this->has_completed_courses) {
            foreach (range(0, count($this->courses) - 1) as $index) {
                $rules["courses.{$index}.organization_name"] = 'required|string|max:255';
                $rules["courses.{$index}.organization_name_other"] = 'nullable|string|max:255';

                // Hacer organization_name_other obligatorio cuando organization_name es 'Other'
                if (isset($this->courses[$index]['organization_name']) && $this->courses[$index]['organization_name'] === 'Other') {
                    $rules["courses.{$index}.organization_name_other"] = 'required|string|max:255';
                }
                $rules["courses.{$index}.city"] = 'required|string|max:255';
                $rules["courses.{$index}.state"] = 'required|string|max:255';
                $rules["courses.{$index}.certification_date"] = 'required|date';
            }
        }

        return $rules;
    }

    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'has_attended_training_school' => 'sometimes|boolean',
            'has_completed_courses' => 'sometimes|boolean',
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

        // Initialize with empty course
        if ($this->has_completed_courses && empty($this->courses)) {
            $this->courses = [$this->getEmptyCourse()];
        }
    }

    // Load existing data
    protected function loadExistingData()
    {
        $userDriverDetail = UserDriverDetail::find($this->driverId);
        if (!$userDriverDetail) {
            return;
        }

        // Default values
        $this->has_attended_training_school = false;
        $this->has_completed_courses = false;

        // Check if attended training school from application details
        if ($userDriverDetail->application && $userDriverDetail->application->details) {
            $this->has_attended_training_school = (bool)(
                $userDriverDetail->application->details->has_attended_training_school ?? false
            );
            // También podríamos cargar los cursos desde application details si fuera necesario
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
                    'date_start' => $school->date_start ? $school->date_start->format('Y-m-d') : null,
                    'date_end' => $school->date_end ? $school->date_end->format('Y-m-d') : null,
                    'graduated' => (bool)($school->graduated ?? false),
                    'subject_to_safety_regulations' => (bool)($school->subject_to_safety_regulations ?? false),
                    'performed_safety_functions' => (bool)($school->performed_safety_functions ?? false),
                    'training_skills' => is_array($school->training_skills) ? $school->training_skills : (json_decode($school->training_skills) ?: []),
                    'certificates' => $certificates,
                    'temp_certificate_tokens' => []
                ];
            }
        }

        // Load courses
        $courses = $userDriverDetail->courses;
        if ($courses->count() > 0) {
            $this->has_completed_courses = true;
            $this->courses = [];
            foreach ($courses as $course) {
                $certificates = [];
                if ($course->hasMedia('course_certificates')) {
                    foreach ($course->getMedia('course_certificates') as $certificate) {
                        $certificates[] = [
                            'id' => $certificate->id,
                            'filename' => $certificate->file_name,
                            'url' => $certificate->getUrl(),
                            'is_image' => Str::startsWith($certificate->mime_type, 'image/'),
                        ];
                    }
                }

                $this->courses[] = [
                    'id' => $course->id,
                    'organization_name' => $course->organization_name ?? '',
                    'city' => $course->city ?? '',
                    'state' => $course->state ?? '',
                    'certification_date' => $course->certification_date ? $course->certification_date->format('Y-m-d') : null,
                    'experience' => $course->experience ?? '',
                    'expiration_date' => $course->expiration_date ? $course->expiration_date->format('Y-m-d') : null,
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
    public function saveTrainingData()
    {
        try {
            DB::beginTransaction();

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                return false;
            }

            // Save training school flag in application details
            if ($userDriverDetail->application && $userDriverDetail->application->details) {
                $userDriverDetail->application->details->update([
                    'has_attended_training_school' => $this->has_attended_training_school
                ]);
            }

            // Handle training schools - sync
            if ($this->has_attended_training_school) {
                // Get existing schools
                $existingSchools = $userDriverDetail->trainingSchools->pluck('id')->toArray();
                $currentSchools = [];

                foreach ($this->training_schools as $schoolData) {
                    // Use existing school ID if available, otherwise create new
                    if (!empty($schoolData['id'])) {
                        $school = $userDriverDetail->trainingSchools()->find($schoolData['id']);
                        if ($school) {
                            $school->update([
                                'school_name' => $schoolData['school_name'] ?? null,
                                'city' => $schoolData['city'] ?? null,
                                'state' => $schoolData['state'] ?? null,
                                'date_start' => $schoolData['date_start'] ?? null,
                                'date_end' => $schoolData['date_end'] ?? null,
                                'graduated' => $schoolData['graduated'] ?? false,
                                'subject_to_safety_regulations' => $schoolData['subject_to_safety_regulations'] ?? false,
                                'performed_safety_functions' => $schoolData['performed_safety_functions'] ?? false,
                                'training_skills' => json_encode($schoolData['training_skills'] ?? [])
                            ]);
                            $currentSchools[] = $school->id;
                        }
                    } else {
                        // Create new school
                        $school = $userDriverDetail->trainingSchools()->create([
                            'school_name' => $schoolData['school_name'] ?? null,
                            'city' => $schoolData['city'] ?? null,
                            'state' => $schoolData['state'] ?? null,
                            'date_start' => $schoolData['date_start'] ?? null,
                            'date_end' => $schoolData['date_end'] ?? null,
                            'graduated' => $schoolData['graduated'] ?? false,
                            'subject_to_safety_regulations' => $schoolData['subject_to_safety_regulations'] ?? false,
                            'performed_safety_functions' => $schoolData['performed_safety_functions'] ?? false,
                            'training_skills' => json_encode($schoolData['training_skills'] ?? [])
                        ]);

                        if ($school) {
                            $currentSchools[] = $school->id;
                        }
                    }

                    // Process certificates for this school
                    if (isset($school) && $school) {
                        $this->processSchoolCertificates($school, $schoolData);
                    }
                }

                // Delete schools that are no longer in the list
                $schoolsToDelete = array_diff($existingSchools, $currentSchools);
                foreach ($schoolsToDelete as $schoolId) {
                    $schoolToDelete = $userDriverDetail->trainingSchools()->find($schoolId);
                    if ($schoolToDelete) {
                        // Delete certificates first
                        $schoolToDelete->clearMediaCollection('school_certificates');
                        $schoolToDelete->delete();
                    }
                }
            } else {
                // If no training schools, delete all existing
                foreach ($userDriverDetail->trainingSchools as $school) {
                    $school->clearMediaCollection('school_certificates');
                }
                $userDriverDetail->trainingSchools()->delete();
            }

            // Handle courses - sync
            if ($this->has_completed_courses) {
                // Get existing courses
                $existingCourses = $userDriverDetail->courses->pluck('id')->toArray();
                $currentCourses = [];

                // Log para depuración
                Log::info('Saving courses', [
                    'total_courses' => count($this->courses),
                    'courses' => $this->courses
                ]);

                foreach ($this->courses as $index => $courseData) {
                    // Log de cada curso individual para depurar
                    Log::info('Processing course', [
                        'index' => $index,
                        'course_data' => $courseData,
                        'has_id' => !empty($courseData['id'])
                    ]);

                    // Use existing course ID if available, otherwise create new
                    if (!empty($courseData['id'])) {
                        $course = $userDriverDetail->courses()->find($courseData['id']);
                        if ($course) {
                            Log::info('Updating existing course', ['course_id' => $course->id]);
                            // Determinar el valor correcto para organization_name
                            $organizationName = $courseData['organization_name'] ?? null;
                            if ($organizationName === 'Other' && !empty($courseData['organization_name_other'])) {
                                $organizationName = $courseData['organization_name_other'];
                            }

                            $course->update([
                                'organization_name' => $organizationName,
                                'city' => $courseData['city'] ?? null,
                                'state' => $courseData['state'] ?? null,
                                'certification_date' => $courseData['certification_date'] ?? null,
                                'experience' => $courseData['experience'] ?? null,
                                'expiration_date' => !empty($courseData['expiration_date']) ? $courseData['expiration_date'] : null,
                                'status' => 'Active',
                            ]);
                            $currentCourses[] = $course->id;
                        }
                    } else {
                        // Create new course
                        Log::info('Creating new course', [
                            'data' => [
                                'organization_name' => $courseData['organization_name'] ?? null,
                                'city' => $courseData['city'] ?? null,
                                'state' => $courseData['state'] ?? null,
                                'certification_date' => $courseData['certification_date'] ?? null,
                                'experience' => $courseData['experience'] ?? null,
                                'expiration_date' => $courseData['expiration_date'] ?? null,
                            ]
                        ]);

                        // Determinar el valor correcto para organization_name
                        $organizationName = $courseData['organization_name'] ?? null;
                        if ($organizationName === 'Other' && !empty($courseData['organization_name_other'])) {
                            $organizationName = $courseData['organization_name_other'];
                        }

                        $course = $userDriverDetail->courses()->create([
                            'organization_name' => $organizationName,
                            'city' => $courseData['city'] ?? null,
                            'state' => $courseData['state'] ?? null,
                            'certification_date' => $courseData['certification_date'] ?? null,
                            'experience' => $courseData['experience'] ?? null,
                            'expiration_date' => !empty($courseData['expiration_date']) ? $courseData['expiration_date'] : null,
                            'status' => 'Active',
                        ]);

                        if ($course) {
                            Log::info('New course created', ['course_id' => $course->id]);
                            $currentCourses[] = $course->id;
                        } else {
                            Log::error('Failed to create course', ['index' => $index]);
                        }
                    }

                    // Process certificates for this course
                    if (isset($course) && $course) {
                        $this->processCourseCertificates($course, $courseData);
                    }
                }

                // Delete courses that are no longer in the list
                $coursesToDelete = array_diff($existingCourses, $currentCourses);
                foreach ($coursesToDelete as $courseId) {
                    $courseToDelete = $userDriverDetail->courses()->find($courseId);
                    if ($courseToDelete) {
                        // Delete certificates first
                        $courseToDelete->clearMediaCollection('certificates');
                        $courseToDelete->delete();
                    }
                }
            } else {
                // If no courses, delete all existing
                foreach ($userDriverDetail->courses as $course) {
                    $course->clearMediaCollection('certificates');
                }
                $userDriverDetail->courses()->delete();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving training data', [
                'driver_id' => $this->driverId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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

                // Log para depuración
                Log::info('Processing school certificate', [
                    'school_id' => $school->id,
                    'token' => $certData['token'],
                    'session_id' => session()->getId(),
                    'temp_files' => array_keys(session('temp_files', []))
                ]);

                // Intenta obtener el archivo de la sesión
                $tempPath = $tempUploadService->moveToPermanent($certData['token']);

                // Si no se encuentra en la sesión, intenta buscarlo directamente en el almacenamiento
                if (!$tempPath || !file_exists($tempPath)) {
                    // Buscar en el almacenamiento por un patrón que coincida con el token
                    $tempFiles = session('temp_files', []);
                    Log::info('Buscando archivo en temp_files', ['temp_files' => $tempFiles]);

                    // Si no podemos encontrarlo en la sesión, intentamos buscarlo directamente en el storage
                    $possiblePaths = [
                        storage_path('app/temp/school_certificates'),
                        storage_path('app/temp/school_certificate'),
                        storage_path('app/temp')
                    ];

                    // Primero intentamos buscar por nombre de archivo si lo tenemos
                    if (!empty($certData['filename'])) {
                        foreach ($possiblePaths as $dir) {
                            if (is_dir($dir)) {
                                $files = scandir($dir);
                                foreach ($files as $file) {
                                    // Buscar coincidencias parciales con el nombre del archivo
                                    if (
                                        $file != '.' && $file != '..' &&
                                        is_file($dir . '/' . $file) &&
                                        (strpos($file, pathinfo($certData['filename'], PATHINFO_FILENAME)) !== false ||
                                            strpos($certData['filename'], pathinfo($file, PATHINFO_FILENAME)) !== false)
                                    ) {

                                        $tempPath = $dir . '/' . $file;
                                        Log::info('Encontrado archivo por coincidencia de nombre', [
                                            'path' => $tempPath,
                                            'filename' => $certData['filename'],
                                            'file_found' => $file
                                        ]);
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    // Si no encontramos por nombre, buscamos archivos recientes
                    if (!$tempPath || !file_exists($tempPath)) {
                        foreach ($possiblePaths as $dir) {
                            if (is_dir($dir)) {
                                $files = scandir($dir);
                                Log::info('Archivos en directorio', ['dir' => $dir, 'files' => $files]);

                                // Ordenar archivos por fecha de modificación (más recientes primero)
                                $recentFiles = [];
                                foreach ($files as $file) {
                                    if ($file != '.' && $file != '..' && is_file($dir . '/' . $file)) {
                                        $recentFiles[$file] = filemtime($dir . '/' . $file);
                                    }
                                }
                                arsort($recentFiles); // Ordenar por tiempo de modificación (más reciente primero)

                                // Tomar el archivo más reciente
                                foreach ($recentFiles as $file => $mtime) {
                                    // Si el archivo fue creado en las últimas 24 horas, lo usamos
                                    if ($mtime > time() - 86400) {
                                        $tempPath = $dir . '/' . $file;
                                        Log::info('Encontrado archivo reciente', ['path' => $tempPath, 'mtime' => date('Y-m-d H:i:s', $mtime)]);
                                        break 2; // Salir de ambos bucles
                                    }
                                }
                            }
                        }
                    }
                }

                if ($tempPath && file_exists($tempPath)) {
                    $school->addMedia($tempPath)
                        ->toMediaCollection('school_certificates');
                    Log::info('Certificate added to media collection', [
                        'school_id' => $school->id,
                        'path' => $tempPath
                    ]);
                } else {
                    Log::error('Failed to process school certificate - file not found', [
                        'school_id' => $school->id,
                        'token' => $certData['token']
                    ]);
                }
            }
        }
    }

    // Process course certificates
    protected function processCourseCertificates($course, $courseData)
    {
        $tempUploadService = app(TempUploadService::class);

        // Ensure relationship is loaded
        $course->load('driverDetail');

        if (!empty($courseData['temp_certificate_tokens'])) {
            foreach ($courseData['temp_certificate_tokens'] as $certData) {
                if (empty($certData['token'])) continue;

                // Log para depuración
                Log::info('Processing course certificate', [
                    'course_id' => $course->id,
                    'token' => $certData['token'],
                    'session_id' => session()->getId(),
                    'temp_files' => array_keys(session('temp_files', []))
                ]);

                // Intenta obtener el archivo de la sesión
                $tempPath = $tempUploadService->moveToPermanent($certData['token']);

                // Si no se encuentra en la sesión, intenta buscarlo directamente en el almacenamiento
                if (!$tempPath || !file_exists($tempPath)) {
                    // Buscar en el almacenamiento por un patrón que coincida con el token
                    $tempFiles = session('temp_files', []);
                    Log::info('Buscando archivo en temp_files', ['temp_files' => $tempFiles]);

                    // Si no podemos encontrarlo en la sesión, intentamos buscarlo directamente en el storage
                    $possiblePaths = [
                        storage_path('app/temp/course_certificates'),
                        storage_path('app/temp/certificates'),
                        storage_path('app/temp')
                    ];

                    // Primero intentamos buscar por nombre de archivo si lo tenemos
                    if (!empty($certData['filename'])) {
                        foreach ($possiblePaths as $dir) {
                            if (is_dir($dir)) {
                                $files = scandir($dir);
                                foreach ($files as $file) {
                                    // Buscar coincidencias parciales con el nombre del archivo
                                    if (
                                        $file != '.' && $file != '..' &&
                                        is_file($dir . '/' . $file) &&
                                        (strpos($file, pathinfo($certData['filename'], PATHINFO_FILENAME)) !== false ||
                                            strpos($certData['filename'], pathinfo($file, PATHINFO_FILENAME)) !== false)
                                    ) {

                                        $tempPath = $dir . '/' . $file;
                                        Log::info('Encontrado archivo por coincidencia de nombre', [
                                            'path' => $tempPath,
                                            'filename' => $certData['filename'],
                                            'file_found' => $file
                                        ]);
                                        break 2;
                                    }
                                }
                            }
                        }
                    }

                    // Si no encontramos por nombre, buscamos archivos recientes
                    if (!$tempPath || !file_exists($tempPath)) {
                        foreach ($possiblePaths as $dir) {
                            if (is_dir($dir)) {
                                $files = scandir($dir);
                                Log::info('Archivos en directorio', ['dir' => $dir, 'files' => $files]);

                                // Ordenar archivos por fecha de modificación (más recientes primero)
                                $recentFiles = [];
                                foreach ($files as $file) {
                                    if ($file != '.' && $file != '..' && is_file($dir . '/' . $file)) {
                                        $recentFiles[$file] = filemtime($dir . '/' . $file);
                                    }
                                }
                                arsort($recentFiles); // Ordenar por tiempo de modificación (más reciente primero)

                                // Tomar el archivo más reciente
                                foreach ($recentFiles as $file => $mtime) {
                                    // Si el archivo fue creado en las últimas 24 horas, lo usamos
                                    if ($mtime > time() - 86400) {
                                        $tempPath = $dir . '/' . $file;
                                        Log::info('Encontrado archivo reciente', ['path' => $tempPath, 'mtime' => date('Y-m-d H:i:s', $mtime)]);
                                        break 2; // Salir de ambos bucles
                                    }
                                }
                            }
                        }
                    }
                }

                if ($tempPath && file_exists($tempPath)) {
                    // Obtener el nombre del archivo original
                    $originalFileName = basename($tempPath);
                    $fileName = $certData['filename'] ?? $originalFileName;

                    $course->addMedia($tempPath)
                        ->usingName(pathinfo($fileName, PATHINFO_FILENAME))
                        ->usingFileName($fileName)
                        ->toMediaCollection('certificates');

                    Log::info('Certificate added to course', [
                        'course_id' => $course->id,
                        'path' => $tempPath,
                        'filename' => $fileName,
                        'collection' => 'certificates'
                    ]);
                }
            }
        }

        return true;
    }

    // Add training school
    public function addTrainingSchool()
    {
        $this->training_schools[] = $this->getEmptyTrainingSchool();
    }

    // Add course
    public function addCourse()
    {
        $this->courses[] = $this->getEmptyCourse();
    }

    // Remove training school
    public function removeTrainingSchool($index)
    {
        if (isset($this->training_schools[$index])) {
            // Eliminar cualquier token temporal pendiente
            unset($this->training_schools[$index]);
            $this->training_schools = array_values($this->training_schools);
        }
    }

    // Remove course
    public function removeCourse($index)
    {
        if (isset($this->courses[$index])) {
            // Eliminar cualquier token temporal pendiente
            unset($this->courses[$index]);
            $this->courses = array_values($this->courses);
        }
    }

    // Toggle training skill
    public function toggleTrainingSkill($schoolIndex, $skill)
    {
        if (!isset($this->training_schools[$schoolIndex])) {
            return;
        }

        // Ensure training_skills array exists
        if (!isset($this->training_schools[$schoolIndex]['training_skills'])) {
            $this->training_schools[$schoolIndex]['training_skills'] = [];
        }

        // Add or remove skill
        $skills = $this->training_schools[$schoolIndex]['training_skills'];
        $keyIndex = array_search($skill, $skills);

        if ($keyIndex !== false) {
            // Remove skill
            unset($skills[$keyIndex]);
            $skills = array_values($skills); // Reindex array
        } else {
            // Add skill
            $skills[] = $skill;
        }

        $this->training_schools[$schoolIndex]['training_skills'] = $skills;
    }

    // Add certificate to course
    public function addCourseCertificate($courseIndex, $token, $filename, $previewUrl = null, $fileType = null)
    {
        if (!isset($this->courses[$courseIndex])) {
            return;
        }

        // Initialize if not exists
        if (!isset($this->courses[$courseIndex]['temp_certificate_tokens'])) {
            $this->courses[$courseIndex]['temp_certificate_tokens'] = [];
        }

        // Add certificate token
        $this->courses[$courseIndex]['temp_certificate_tokens'][] = [
            'token' => $token,
            'filename' => $filename,
            'preview_url' => $previewUrl,
            'file_type' => $fileType
        ];

        // Force refresh
        $this->dispatch('certificates-updated');
    }

    // Remove course certificate
    public function removeCourseCertificate($courseIndex, $tokenIndex)
    {
        if (
            !isset($this->courses[$courseIndex]) ||
            !isset($this->courses[$courseIndex]['temp_certificate_tokens'][$tokenIndex])
        ) {
            return;
        }

        // Remove the certificate token
        unset($this->courses[$courseIndex]['temp_certificate_tokens'][$tokenIndex]);
        $this->courses[$courseIndex]['temp_certificate_tokens'] =
            array_values($this->courses[$courseIndex]['temp_certificate_tokens']);

        // Force refresh
        $this->dispatch('certificates-updated');
    }

    // Remove course certificate by ID (for existing certificates)
    public function removeCertificateByIdFromCourse($courseIndex, $certificateId)
    {
        try {
            if (!$this->driverId || !isset($this->courses[$courseIndex])) {
                return false;
            }

            $courseData = $this->courses[$courseIndex];
            if (empty($courseData['id'])) {
                return false;
            }

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                return false;
            }

            $course = $userDriverDetail->courses()->find($courseData['id']);
            if (!$course) {
                return false;
            }

            // Buscar y eliminar el certificado específico
            $media = $course->getMedia('course_certificates')->find($certificateId);
            if ($media) {
                $media->delete();

                // Recargar los certificados
                $this->refreshCourseCertificates($courseIndex, $course);

                // Forzar actualización
                $this->dispatch('certificates-updated');

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error eliminando certificado de curso', [
                'error' => $e->getMessage(),
                'course_index' => $courseIndex,
                'certificate_id' => $certificateId
            ]);
            return false;
        }
    }

    // Refresh course certificates
    public function refreshCourseCertificates($courseIndex, $course = null)
    {
        // Si no se proporciona el modelo, lo obtenemos
        if (!$course) {
            $courseData = $this->courses[$courseIndex] ?? null;
            if (!$courseData || empty($courseData['id'])) return;
            
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) return;
            
            $course = $userDriverDetail->courses()->find($courseData['id']);
            if (!$course) return;
        }

        // Asegúrate que el curso esté recargado con sus relaciones
        $course->refresh();

        // Actualiza los certificados
        $certificates = [];
        if ($course->hasMedia('course_certificates')) {
            foreach ($course->getMedia('course_certificates') as $certificate) {
                $certificates[] = [
                    'id' => $certificate->id,
                    'filename' => $certificate->file_name,
                    'url' => $certificate->getUrl(),
                    'is_image' => Str::startsWith($certificate->mime_type, 'image/'),
                ];
            }
        }

        // Actualiza el curso completo en el array
        $this->courses[$courseIndex]['certificates'] = $certificates;
    }

    public function refreshTrainingSchoolCertificates($schoolIndex, $school = null)
    {
        // Si no se proporciona el modelo, lo obtenemos
        if (!$school) {
            $schoolData = $this->training_schools[$schoolIndex] ?? null;
            if (!$schoolData || empty($schoolData['id'])) return;
            
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) return;
            
            $school = $userDriverDetail->trainingSchools()->find($schoolData['id']);
            if (!$school) return;
        }

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

    // Clear all course certificates
    public function clearAllCourseCertificates($courseIndex)
    {
        try {
            if (!$this->driverId) return false;

            $courseData = $this->courses[$courseIndex] ?? null;
            if (!$courseData || empty($courseData['id'])) return false;

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) return false;

            $course = $userDriverDetail->courses()->find($courseData['id']);
            if (!$course) return false;

            // Eliminar todos los certificados
            $course->clearMediaCollection('course_certificates');

            // Actualizar el componente
            $this->refreshCourseCertificates($courseIndex, $course);

            // Forzar actualización completa
            $this->dispatch('certificates-updated');

            return true;
        } catch (\Exception $e) {
            Log::error('Error eliminando todos los certificados del curso', [
                'message' => $e->getMessage(),
                'courseIndex' => $courseIndex
            ]);
            return false;
        }
    }

    // Add certificate
    public function addCertificate($schoolIndex, $token, $filename, $previewUrl = null, $fileType = null)
    {
        if (!isset($this->training_schools[$schoolIndex]['temp_certificate_tokens'])) {
            $this->training_schools[$schoolIndex]['temp_certificate_tokens'] = [];
        }

        // Guardar el token en la sesión para asegurar que esté disponible cuando se procese
        $tempFiles = session('temp_files', []);

        // Verificar si el token ya existe en la sesión
        if (!isset($tempFiles[$token])) {
            // Si no existe, intentar recrearlo con la información disponible
            $tempFiles[$token] = [
                'disk' => 'public',
                'path' => "temp/school_certificates/" . basename($previewUrl ?? ''),
                'original_name' => $filename,
                'mime_type' => $fileType,
                'size' => 0, // No tenemos el tamaño exacto
                'created_at' => now()->toDateTimeString(),
            ];

            // Guardar en la sesión
            session(['temp_files' => $tempFiles]);

            // Registrar en el log
            Log::info('Token recreado en la sesión', [
                'token' => $token,
                'filename' => $filename,
                'session_id' => session()->getId()
            ]);
        }

        // Guardar en el componente Livewire
        $this->training_schools[$schoolIndex]['temp_certificate_tokens'][] = [
            'token' => $token,
            'filename' => $filename,
            'preview_url' => $previewUrl,
            'file_type' => $fileType
        ];

        // Registrar en el log
        Log::info('Certificado añadido al componente', [
            'school_index' => $schoolIndex,
            'token' => $token,
            'filename' => $filename,
            'session_id' => session()->getId(),
            'temp_files' => array_keys(session('temp_files', []))
        ]);

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

    public function refreshCertificates($schoolIndex, $schoolModel = null)
    {
        // Si no se proporciona el modelo, lo obtenemos
        if (!$schoolModel) {
            $schoolData = $this->training_schools[$schoolIndex] ?? null;
            if (!$schoolData || empty($schoolData['id'])) return;
            
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) return;
            
            $schoolModel = $userDriverDetail->trainingSchools()->find($schoolData['id']);
            if (!$schoolModel) return;
        }
        
        // Actualizar la lista de certificados en el componente
        $certificates = [];

        if ($schoolModel->hasMedia('school_certificates')) {
            foreach ($schoolModel->getMedia('school_certificates') as $certificate) {
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
            'date_start' => '',
            'date_end' => '',
            'graduated' => false,
            'subject_to_safety_regulations' => false,
            'performed_safety_functions' => false,
            'training_skills' => [],
            'temp_certificate_tokens' => []
        ];
    }

    // Get empty course structure
    protected function getEmptyCourse()
    {
        return [
            'organization_name' => '',
            'organization_name_other' => '',
            'city' => '',
            'state' => '',
            'certification_date' => '',
            'experience' => '',
            'expiration_date' => '',
            'certificates' => [],
            'temp_certificate_tokens' => []
        ];
    }

    // Create training school
    public function createTrainingSchool($index)
    {
        try {
            if (!$this->driverId || !isset($this->training_schools[$index])) {
                return false;
            }

            $schoolData = $this->training_schools[$index];
            
            // Validate required fields
            $this->validate([
                "training_schools.{$index}.school_name" => 'required|string|max:255',
                "training_schools.{$index}.city" => 'required|string|max:255',
                "training_schools.{$index}.state" => 'required|string|max:2',
                "training_schools.{$index}.date_start" => 'required|date',
                "training_schools.{$index}.date_end" => 'required|date|after_or_equal:training_schools.{$index}.date_start',
            ]);

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                return false;
            }

            // Create new training school
            $trainingSchool = $userDriverDetail->trainingSchools()->create([
                'school_name' => $schoolData['school_name'],
                'city' => $schoolData['city'],
                'state' => $schoolData['state'],
                'date_start' => $schoolData['date_start'],
                'date_end' => $schoolData['date_end'],
                'graduated' => $schoolData['graduated'] ?? false,
                'subject_to_safety_regulations' => $schoolData['subject_to_safety_regulations'] ?? false,
                'performed_safety_functions' => $schoolData['performed_safety_functions'] ?? false,
                'training_skills' => $schoolData['training_skills'] ?? [],
            ]);

            // Update the array with the new ID
            $this->training_schools[$index]['id'] = $trainingSchool->id;
            $this->training_schools[$index]['certificates'] = [];

            // Process certificates if any
            if (!empty($schoolData['temp_certificate_tokens'])) {
                $this->processSchoolCertificates($trainingSchool, $schoolData);
                $this->refreshSchoolData($index, $trainingSchool);
            }

            session()->flash('success', 'Escuela de entrenamiento creada exitosamente.');
            return true;

        } catch (\Exception $e) {
            Log::error('Error creating training school', [
                'error' => $e->getMessage(),
                'index' => $index,
                'driver_id' => $this->driverId
            ]);
            session()->flash('error', 'Error al crear la escuela de entrenamiento.');
            return false;
        }
    }

    // Update training school
    public function updateTrainingSchool($index)
    {
        try {
            if (!$this->driverId || !isset($this->training_schools[$index]) || empty($this->training_schools[$index]['id'])) {
                return false;
            }

            $schoolData = $this->training_schools[$index];
            
            // Validate required fields
            $this->validate([
                "training_schools.{$index}.school_name" => 'required|string|max:255',
                "training_schools.{$index}.city" => 'required|string|max:255',
                "training_schools.{$index}.state" => 'required|string|max:2',
                "training_schools.{$index}.date_start" => 'required|date',
                "training_schools.{$index}.date_end" => 'required|date|after_or_equal:training_schools.{$index}.date_start',
            ]);

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                return false;
            }

            $trainingSchool = $userDriverDetail->trainingSchools()->find($schoolData['id']);
            if (!$trainingSchool) {
                return false;
            }

            // Update training school
            $trainingSchool->update([
                'school_name' => $schoolData['school_name'],
                'city' => $schoolData['city'],
                'state' => $schoolData['state'],
                'date_start' => $schoolData['date_start'],
                'date_end' => $schoolData['date_end'],
                'graduated' => $schoolData['graduated'] ?? false,
                'subject_to_safety_regulations' => $schoolData['subject_to_safety_regulations'] ?? false,
                'performed_safety_functions' => $schoolData['performed_safety_functions'] ?? false,
                'training_skills' => $schoolData['training_skills'] ?? [],
            ]);

            // Process certificates if any
            if (!empty($schoolData['temp_certificate_tokens'])) {
                $this->processSchoolCertificates($trainingSchool, $schoolData);
                $this->refreshSchoolData($index, $trainingSchool);
            }

            session()->flash('success', 'Escuela de entrenamiento actualizada exitosamente.');
            return true;

        } catch (\Exception $e) {
            Log::error('Error updating training school', [
                'error' => $e->getMessage(),
                'index' => $index,
                'driver_id' => $this->driverId
            ]);
            session()->flash('error', 'Error al actualizar la escuela de entrenamiento.');
            return false;
        }
    }

    // Create course
    public function createCourse($index)
    {
        try {
            if (!$this->driverId || !isset($this->courses[$index])) {
                return false;
            }

            $courseData = $this->courses[$index];
            
            // Validate required fields
            $this->validate([
                "courses.{$index}.organization_name" => 'required|string|max:255',
                "courses.{$index}.city" => 'required|string|max:255',
                "courses.{$index}.state" => 'required|string|max:2',
                "courses.{$index}.certification_date" => 'required|date',
            ]);

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                return false;
            }

            // Handle organization name
            $organizationName = $courseData['organization_name'];
            if ($organizationName === 'Other' && !empty($courseData['organization_name_other'])) {
                $organizationName = $courseData['organization_name_other'];
            }

            // Create new course
            $course = $userDriverDetail->courses()->create([
                'organization_name' => $organizationName,
                'city' => $courseData['city'],
                'state' => $courseData['state'],
                'certification_date' => $courseData['certification_date'],
                'experience' => $courseData['experience'] ?? '',
                'expiration_date' => $courseData['expiration_date'] ?? null,
            ]);

            // Update the array with the new ID
            $this->courses[$index]['id'] = $course->id;
            $this->courses[$index]['certificates'] = [];

            // Process certificates if any
            if (!empty($courseData['temp_certificate_tokens'])) {
                $this->processCourseCertificates($course, $courseData);
                $this->refreshCourseCertificates($index, $course);
            }

            session()->flash('success', 'Curso creado exitosamente.');
            return true;

        } catch (\Exception $e) {
            Log::error('Error creating course', [
                'error' => $e->getMessage(),
                'index' => $index,
                'driver_id' => $this->driverId
            ]);
            session()->flash('error', 'Error al crear el curso.');
            return false;
        }
    }

    // Update course
    public function updateCourse($index)
    {
        try {
            if (!$this->driverId || !isset($this->courses[$index]) || empty($this->courses[$index]['id'])) {
                return false;
            }

            $courseData = $this->courses[$index];
            
            // Validate required fields
            $this->validate([
                "courses.{$index}.organization_name" => 'required|string|max:255',
                "courses.{$index}.city" => 'required|string|max:255',
                "courses.{$index}.state" => 'required|string|max:2',
                "courses.{$index}.certification_date" => 'required|date',
            ]);

            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                return false;
            }

            $course = $userDriverDetail->courses()->find($courseData['id']);
            if (!$course) {
                return false;
            }

            // Handle organization name
            $organizationName = $courseData['organization_name'];
            if ($organizationName === 'Other' && !empty($courseData['organization_name_other'])) {
                $organizationName = $courseData['organization_name_other'];
            }

            // Update course
            $course->update([
                'organization_name' => $organizationName,
                'city' => $courseData['city'],
                'state' => $courseData['state'],
                'certification_date' => $courseData['certification_date'],
                'experience' => $courseData['experience'] ?? '',
                'expiration_date' => $courseData['expiration_date'] ?? null,
            ]);

            // Process certificates if any
            if (!empty($courseData['temp_certificate_tokens'])) {
                $this->processCourseCertificates($course, $courseData);
                $this->refreshCourseCertificates($index, $course);
            }

            session()->flash('success', 'Curso actualizado exitosamente.');
            return true;

        } catch (\Exception $e) {
            Log::error('Error updating course', [
                'error' => $e->getMessage(),
                'index' => $index,
                'driver_id' => $this->driverId
            ]);
            session()->flash('error', 'Error al actualizar el curso.');
            return false;
        }
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
        return view('livewire.driver.steps.training-step', [
            'usStates' => Constants::usStates(),
        ]);
    }
}
