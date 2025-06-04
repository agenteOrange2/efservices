<?php

namespace App\Livewire\Admin\Driver;

use App\Models\Admin\Driver\DriverCourse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverCourseModal extends Component
{
    // Propiedades públicas para el formulario
    public $showModal = false;
    public $userDriverDetailId;
    public $courseId = null;
    
    // Campos del formulario
    public $organization_name = '';
    public $phone = '';
    public $city = '';
    public $state = '';
    public $certification_date = null;
    public $experience = '';
    public $expiration_date = null;
    public $status = 'Active';
    
    // Archivos
    public $tempFiles = [];
    public $existingFiles = [];
    
    // Reglas de validación
    protected $rules = [
        'organization_name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:15',
        'city' => 'nullable|string|max:255',
        'state' => 'nullable|string|max:255',
        'certification_date' => 'nullable|date',
        'experience' => 'nullable|string|max:1000',
        'expiration_date' => 'nullable|date',
        'status' => 'nullable|string|in:Active,Expired,Pending',
    ];
    
    protected $listeners = [
        'openDriverCourseModal' => 'openModal',
        'fileUploaded' => 'handleFileUploaded',
        'fileRemoved' => 'handleFileRemoved',
    ];
    
    /**
     * Abre el modal para crear o editar un curso
     */
    public function openModal($driverId, $courseId = null)
    {
        $this->userDriverDetailId = $driverId;
        $this->courseId = $courseId;
        
        if ($courseId) {
            // Es una edición, cargar datos existentes
            $this->loadCourseData($courseId);
        } else {
            // Es una creación, resetear formulario
            $this->resetForm();
        }
        
        $this->showModal = true;
    }
    
    /**
     * Cierra el modal y resetea el formulario
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }
    
    /**
     * Carga los datos del curso para edición
     */
    protected function loadCourseData($courseId)
    {
        try {
            $course = DriverCourse::find($courseId);
            
            if (!$course) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'No se encontró el curso especificado'
                ]);
                return;
            }
            
            // Cargar datos del curso
            $this->organization_name = $course->organization_name;
            $this->phone = $course->phone;
            $this->city = $course->city;
            $this->state = $course->state;
            $this->certification_date = $course->certification_date ? $course->certification_date->format('Y-m-d') : null;
            $this->experience = $course->experience;
            $this->expiration_date = $course->expiration_date ? $course->expiration_date->format('Y-m-d') : null;
            $this->status = $course->status;
            
            // Cargar archivos existentes
            $this->loadExistingFiles($course);
            
        } catch (\Exception $e) {
            Log::error('Error al cargar datos del curso: ' . $e->getMessage(), [
                'exception' => $e,
                'course_id' => $courseId
            ]);
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al cargar datos del curso: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Carga archivos existentes del curso
     */
    protected function loadExistingFiles($course)
    {
        $this->existingFiles = [];
        
        $mediaItems = $course->getMedia('course_certificates');
        
        foreach ($mediaItems as $media) {
            $this->existingFiles[] = [
                'id' => $media->id,
                'name' => $media->file_name,
                'size' => $media->size,
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
                'created_at' => $media->created_at->format('Y-m-d H:i:s'), // Añadido campo created_at
            ];
        }
        
        Log::info('Archivos existentes cargados', [
            'count' => count($this->existingFiles),
            'files' => $this->existingFiles
        ]);
    }
    
    /**
     * Resetea el formulario
     */
    protected function resetForm()
    {
        $this->courseId = null;
        $this->organization_name = '';
        $this->phone = '';
        $this->city = '';
        $this->state = '';
        $this->certification_date = null;
        $this->experience = '';
        $this->expiration_date = null;
        $this->status = 'Active';
        $this->tempFiles = [];
        $this->existingFiles = [];
        
        // Reiniciar validación
        $this->resetValidation();
    }
    
    /**
     * Maneja el evento de archivo subido desde el componente FileUploader
     */
    public function handleFileUploaded($event)
    {
        // Registramos la información recibida para propósitos de depuración
        Log::info('FileUpload event recibido en DriverCourseModal', [
            'event' => $event,
            'modelName' => $event['modelName'] ?? 'no model',
            'tempPath' => $event['tempPath'] ?? 'no path'
        ]);
        
        // Verificar que el evento corresponde a este modelo
        if (!isset($event['modelName']) || $event['modelName'] !== 'course_certificates') {
            return;
        }
        
        // Extraer la ruta del archivo temporal y otros datos
        $tempPath = $event['tempPath'] ?? null;
        $originalName = $event['originalName'] ?? null;
        $mimeType = $event['mimeType'] ?? null;
        
        if ($tempPath) {
            // Guardamos solo la parte del path después de 'temp/' que es lo que necesitamos
            $pathParts = explode('temp/', $tempPath);
            $relativePath = end($pathParts);
            
            // Añadir a los archivos temporales con el formato esperado por processLivewireFiles
            $this->tempFiles[] = [
                'path' => $relativePath,
                'name' => $originalName,
                'mime_type' => $mimeType
            ];
            
            Log::info('Archivo temporal añadido correctamente', [
                'path' => $relativePath,
                'name' => $originalName
            ]);
        }
    }
    
    /**
     * Maneja el evento de archivo eliminado desde el componente FileUploader
     */
    public function handleFileRemoved($event)
    {
        // Registramos la información recibida para propósitos de depuración
        Log::info('FileRemoved event recibido en DriverCourseModal', [
            'event' => $event,
            'fileId' => $event['fileId'] ?? 'no id',
            'modelName' => $event['modelName'] ?? 'no model',
        ]);
        
        // Verificar que el evento corresponde a este modelo
        if (!isset($event['modelName']) || $event['modelName'] !== 'course_certificates') {
            return;
        }
        
        $fileId = $event['fileId'] ?? null;
        $isTemp = $event['isTemp'] ?? true;
        
        if ($isTemp) {
            // Es un archivo temporal, eliminar del array tempFiles
            foreach ($this->tempFiles as $index => $file) {
                if (isset($file['id']) && $file['id'] == $fileId) {
                    unset($this->tempFiles[$index]);
                    $this->tempFiles = array_values($this->tempFiles); // Reindexar
                    Log::info('Archivo temporal eliminado de la interfaz', ['file_id' => $fileId]);
                    break;
                }
            }
        } else {
            // Es un archivo existente (en la base de datos)
            foreach ($this->existingFiles as $index => $file) {
                if (isset($file['id']) && $file['id'] == $fileId) {
                    try {
                        // Buscar el archivo en la base de datos
                        $media = Media::find($fileId);
                        
                        if ($media) {
                            // Eliminar el archivo físico y el registro en la base de datos
                            $media->delete();
                            
                            Log::info('Archivo permanente eliminado correctamente', [
                                'file_id' => $fileId,
                                'file_name' => $file['name'] ?? 'unknown'
                            ]);
                            
                            // Notificar éxito al usuario
                            $this->dispatch('notify', [
                                'type' => 'success',
                                'message' => 'Archivo eliminado correctamente'
                            ]);
                        } else {
                            Log::warning('Media no encontrado para eliminar', ['file_id' => $fileId]);
                            // Notificar error al usuario
                            $this->dispatch('notify', [
                                'type' => 'error',
                                'message' => 'No se encontró el archivo a eliminar'
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al eliminar archivo', [
                            'file_id' => $fileId,
                            'error' => $e->getMessage()
                        ]);
                        
                        // Notificar error al usuario
                        $this->dispatch('notify', [
                            'type' => 'error',
                            'message' => 'Error al eliminar el archivo: ' . $e->getMessage()
                        ]);
                    }
                    
                    // Eliminar de la interfaz (independientemente del resultado)
                    unset($this->existingFiles[$index]);
                    $this->existingFiles = array_values($this->existingFiles); // Reindexar
                    Log::info('Archivo permanente eliminado de la interfaz', ['file_id' => $fileId]);
                    break;
                }
            }
        }
    }
    
    /**
     * Procesa los archivos subidos a través de Livewire y los adjunta al modelo
     * 
     * @param DriverCourse $model Modelo al que se adjuntarán los archivos
     * @param array $files Array de archivos temporales con sus metadatos
     * @param string $collection Nombre de la colección de archivos
     * @return bool
     */
    private function processLivewireFiles($model, $files, $collection)
    {
        // Registrar para depuración lo que estamos intentando procesar
        Log::info('processLivewireFiles en DriverCourseModal', [
            'model_id' => $model->id,
            'collection' => $collection,
            'files_count' => count($files),
            'files' => $files
        ]);
        
        if (empty($files)) {
            Log::warning('No hay archivos para procesar');
            return false;
        }
        
        // Convertir a array si es string JSON
        if (is_string($files)) {
            $files = json_decode($files, true) ?? [];
            Log::info('Convertido de JSON a array', ['files' => $files]);
        }
        
        foreach ($files as $fileData) {
            // Verificar si tenemos la estructura correcta para el evento fileUploaded
            if (isset($fileData['tempPath']) && !isset($fileData['path'])) {
                $pathParts = explode('temp/', $fileData['tempPath']);
                $fileData['path'] = end($pathParts);
                $fileData['name'] = $fileData['originalName'] ?? pathinfo($fileData['path'], PATHINFO_FILENAME);
                $fileData['mime_type'] = $fileData['mimeType'] ?? null;
            }
            
            // Verificar si el archivo existe
            if (empty($fileData['path']) || !Storage::disk('local')->exists('temp/' . $fileData['path'])) {
                Log::warning('Archivo temporal no encontrado', ['path' => $fileData['path'] ?? 'no path']);
                continue;
            }
            
            $tempPath = storage_path('app/temp/' . $fileData['path']);
            $fileName = $fileData['name'] ?? pathinfo($fileData['path'], PATHINFO_FILENAME);
            
            Log::info('Procesando archivo', [
                'temp_path' => $tempPath,
                'file_name' => $fileName
            ]);
            
            try {
                // Adjuntar el archivo al modelo usando Media Library
                $media = $model->addMedia($tempPath)
                      ->usingName($fileName)
                      ->withCustomProperties([
                          'original_name' => $fileData['name'] ?? $fileName,
                          'mime_type' => $fileData['mime_type'] ?? null
                      ])
                      ->withResponsiveImagesIf(in_array(strtolower(pathinfo($fileData['path'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']), ['course_certificate_thumb'])
                      ->toMediaCollection($collection, 'public');
                      
                Log::info('Archivo adjuntado exitosamente', [
                    'media_id' => $media->id,
                    'collection' => $collection,
                    'url' => $media->getUrl()
                ]);
                
                // Eliminar el archivo temporal
                Storage::disk('local')->delete('temp/' . $fileData['path']);
            } catch (\Exception $e) {
                Log::error('Error al procesar archivo', [
                    'error' => $e->getMessage(),
                    'file' => $fileData
                ]);
            }
        }
        
        return true;
    }
    
    /**
     * Guarda el curso
     */
    public function save()
    {
        $this->validate();
        
        $data = [
            'user_driver_detail_id' => $this->userDriverDetailId,
            'organization_name' => $this->organization_name,
            'phone' => $this->phone,
            'city' => $this->city,
            'state' => $this->state,
            'certification_date' => $this->certification_date,
            'experience' => $this->experience,
            'expiration_date' => $this->expiration_date,
            'status' => $this->status,
        ];
        
        DB::beginTransaction();
        try {
            if ($this->courseId) {
                // Actualizar curso existente
                $course = DriverCourse::find($this->courseId);
                $course->update($data);
            } else {
                // Crear nuevo curso
                $course = DriverCourse::create($data);
            }

            // Procesar archivos temporales
            if (count($this->tempFiles) > 0) {
                $this->processLivewireFiles($course, $this->tempFiles, 'course_certificates');
            }

            DB::commit();
            
            // Cerrar directamente el modal
            $this->showModal = false;
            
            // Emitir notificación de éxito
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Curso guardado exitosamente.'
            ]);
            
            // Emitir un evento para actualizar la vista principal con los nuevos datos
            $this->dispatch('course-updated', [
                'driverId' => $this->userDriverDetailId,
                'courseId' => $course->id,
                'timestamp' => now()->timestamp
            ]);
            
            // Resetear el formulario
            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar curso: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $data
            ]);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Ha ocurrido un error al guardar el curso: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Render del componente
     */
    public function render()
    {
        return view('livewire.admin.driver.driver-course-modal');
    }
}
