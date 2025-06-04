<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\Driver\DriverTrainingSchool;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
class DriverTrainingModal extends Component
{
    use WithFileUploads;
    
    // Flag para controlar la visibilidad del modal
    public $showModal = false;
    
    // ID del conductor asociado
    public $userDriverDetailId;
    
    // ID de la escuela (para edición)
    public $trainingSchoolId = null;
    
    // Campos del formulario
    public $school_name;
    public $city;
    public $state;
    public $phone_number;
    public $date_start;
    public $date_end;
    public $graduated = false;
    public $training_skills = [];
    
    // Para la carga de certificados
    public $tempFiles = [];
    public $existingFiles = [];
    
    // Lista de habilidades seleccionables
    public $availableSkills = [
        'cdl_training',
        'hazmat_training',
        'passenger_training',
        'tanker_training',
        'doubles_triples_training',
        'defensive_driving',
        'mountain_driving',
        'winter_driving',
        'emergency_procedures',
        'vehicle_inspection',
    ];
    
    // Event listeners
    protected $listeners = [
        'openTrainingModal' => 'openModal',
        'fileUploaded' => 'handleFileUploaded',
        'fileRemoved' => 'handleFileRemoved'
    ];
    
    // Reglas de validación
    protected $rules = [
        'school_name' => 'required|string|max:255',
        'city' => 'required|string|max:100',
        'state' => 'required|string|max:100',
        'phone_number' => 'required|string|max:20',
        'date_start' => 'required|date',
        'date_end' => 'required|date|after_or_equal:date_start',
    ];
    
    /**
     * Abre el modal para crear o editar
     */
    public function openModal($driverId, $trainingSchoolId = null)
    {
        $this->userDriverDetailId = $driverId;
        $this->trainingSchoolId = $trainingSchoolId;
        $this->resetForm();
        
        if ($this->trainingSchoolId) {
            $this->loadTrainingSchool();
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
     * Carga los datos de la escuela para edición
     */
    public function loadTrainingSchool()
    {
        $school = DriverTrainingSchool::findOrFail($this->trainingSchoolId);
        
        $this->school_name = $school->school_name;
        $this->city = $school->city;
        $this->state = $school->state;
        $this->phone_number = $school->phone_number;
        $this->date_start = optional($school->date_start)->format('Y-m-d');
        $this->date_end = optional($school->date_end)->format('Y-m-d');
        $this->graduated = $school->graduated;
        
        // Cargar habilidades
        if (is_string($school->training_skills)) {
            $this->training_skills = json_decode($school->training_skills, true) ?: [];
        } else {
            $this->training_skills = $school->training_skills ?: [];
        }
        
        // Cargar certificados existentes
        if ($school->hasMedia('school_certificates')) {
            foreach ($school->getMedia('school_certificates') as $media) {
                $this->existingFiles[] = [
                    'id' => $media->id,
                    'name' => $media->name,
                    'url' => $media->getUrl(),
                    'mime_type' => $media->mime_type,
                    'size' => $media->size ?? 0, // Tamaño en bytes
                    'created_at' => $media->created_at->format('Y-m-d H:i:s'), // Fecha de creación
                ];
            }
        }
    }
    
    /**
     * Maneja el evento de archivo subido desde el componente FileUploader
     */
    public function handleFileUploaded($event)
    {
        // Registramos la información recibida para propósitos de depuración
        Log::info('FileUpload event recibido en DriverTrainingModal', [
            'event' => $event,
            'modelName' => $event['modelName'] ?? 'no model',
            'tempPath' => $event['tempPath'] ?? 'no path'
        ]);
        
        // Verificar que el evento corresponde a este modelo
        if (!isset($event['modelName']) || $event['modelName'] !== 'school_certificates') {
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
        }
    }
    
    /**
     * Maneja el evento de archivo eliminado desde el componente FileUploader
     */
    public function handleFileRemoved($event)
    {
        // Registramos la información recibida para propósitos de depuración
        Log::info('FileRemoved event recibido en DriverTrainingModal', [
            'event' => $event,
            'fileId' => $event['fileId'] ?? 'no id',
            'modelName' => $event['modelName'] ?? 'no model',
        ]);
        
        // Verificar que el evento corresponde a este modelo
        if (!isset($event['modelName']) || $event['modelName'] !== 'school_certificates') {
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
                    break;
                }
            }
        } else {
            // Es un archivo existente (en la base de datos)
            foreach ($this->existingFiles as $index => $file) {
                if (isset($file['id']) && $file['id'] == $fileId) {
                    // Marcar para eliminación al guardar utilizando la API documentos
                    // que evita la eliminación en cascada
                    $this->dispatch('documentMarkedForDeletion', ['mediaId' => $fileId]);
                    unset($this->existingFiles[$index]);
                    $this->existingFiles = array_values($this->existingFiles); // Reindexar
                    break;
                }
            }
        }
    }
    
    /**
     * Guarda la escuela de capacitación
     */
    public function save()
    {
        $this->validate();
        
        $data = [
            'user_driver_detail_id' => $this->userDriverDetailId,
            'school_name' => $this->school_name,
            'city' => $this->city,
            'state' => $this->state,
            'phone_number' => $this->phone_number,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'graduated' => $this->graduated ? 1 : 0,
            'training_skills' => json_encode($this->training_skills),
        ];
        
        DB::beginTransaction();
        try {
            if ($this->trainingSchoolId) {
                // Actualizar escuela existente
                $school = DriverTrainingSchool::find($this->trainingSchoolId);
                $school->update($data);
            } else {
                // Crear nueva escuela
                $school = DriverTrainingSchool::create($data);
            }

            // Procesar archivos temporales
            if (count($this->tempFiles) > 0) {
                $this->processLivewireFiles($school, $this->tempFiles, 'school_certificates');
            }

            DB::commit();
            
            // Emitir eventos para cerrar modal y notificar éxito
            $this->dispatch('close-modal');
            $this->dispatch('success', 'Escuela de capacitación guardada exitosamente.');
            
            // Emitir un evento para actualizar la vista principal con los nuevos datos
            // Este evento será escuchado por el componente DriverRecruitmentReview
            $this->dispatch('training-school-updated', [
                'driverId' => $this->userDriverDetailId,
                'schoolId' => $school->id,
                'timestamp' => now()->timestamp
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar escuela de capacitación: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $data
            ]);
            $this->dispatch('error', 'Ha ocurrido un error al guardar la escuela de capacitación: ' . $e->getMessage());
        }
    }
    
    /**
     * Resetea el formulario
     */
    private function resetForm()
    {
        $this->reset([
            'school_name',
            'city',
            'state',
            'phone_number',
            'date_start',
            'date_end',
            'graduated',
            'training_skills',
            'tempFiles',
            'existingFiles',
        ]);
        
        // Valores predeterminados
        $this->graduated = false;
        $this->training_skills = [];
    }
    
    /**
     * Renderiza el componente
     */
    public function render()
    {
        return view('livewire.admin.driver.driver-training-modal');
    }
    
    /**
     * Procesa los archivos subidos a través de Livewire y los adjunta al modelo
     * 
     * @param DriverTrainingSchool $model Modelo al que se adjuntarán los archivos
     * @param array $files Array de archivos temporales con sus metadatos
     * @param string $collection Nombre de la colección de archivos
     * @return bool
     */
    private function processLivewireFiles($model, $files, $collection)
    {
        // Registrar para depuración lo que estamos intentando procesar
        Log::info('processLivewireFiles en DriverTrainingModal', [
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
                      ->withResponsiveImagesIf(in_array(strtolower(pathinfo($fileData['path'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']), ['school_certificate_thumb'])
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
}
