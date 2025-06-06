<?php

namespace App\Livewire\Admin\Driver;

use App\Models\Admin\Driver\DriverAccident;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverAccidentModal extends Component
{
    use WithFileUploads;
    
    // Estado del modal
    public $showModal = false;
    public $userDriverDetailId;
    
    // ID del accidente (para edición)
    public $accidentId;
    
    // Propiedades del formulario
    public $accident_date;
    public $nature_of_accident;
    public $had_fatalities = false;
    public $had_injuries = false;
    public $number_of_fatalities = 0;
    public $number_of_injuries = 0;
    public $comments = '';
    
    // Para la carga de documentos
    public $tempFiles = [];
    public $existingFiles = [];
    
    // Lista de tipos de accidentes
    public $accidentTypes = [
        'head_on',
        'rear_end',
        'side_impact',
        'rollover',
        'jackknife',
        'cargo_spill',
        'pedestrian',
        'animal',
        'fixed_object',
        'other'
    ];
    
    // Event listeners
    protected $listeners = [
        'openAccidentModal' => 'openModal',
        'fileUploaded' => 'handleFileUploaded',
        'fileRemoved' => 'handleFileRemoved'
    ];
    
    // Reglas de validación
    protected $rules = [
        'accident_date' => 'required|date',
        'nature_of_accident' => 'required|string|max:255',
        'had_fatalities' => 'boolean',
        'had_injuries' => 'boolean',
        'number_of_fatalities' => 'nullable|integer|min:0',
        'number_of_injuries' => 'nullable|integer|min:0',
        'comments' => 'nullable|string|max:1000',
    ];
    
    /**
     * Abre el modal para crear o editar un accidente
     */
    public function openModal($driverId, $accidentId = null)
    {
        $this->userDriverDetailId = $driverId;
        $this->accidentId = $accidentId;
        
        if ($accidentId) {
            // Es una edición, cargar datos existentes
            $this->loadAccidentData($accidentId);
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
     * Carga los datos del accidente para edición
     */
    protected function loadAccidentData($accidentId)
    {
        try {
            $accident = DriverAccident::findOrFail($accidentId);
            
            $this->accident_date = $accident->accident_date ? 
                (is_string($accident->accident_date) ? $accident->accident_date : $accident->accident_date->format('Y-m-d')) : null;
            $this->nature_of_accident = $accident->nature_of_accident;
            $this->had_fatalities = $accident->had_fatalities;
            $this->had_injuries = $accident->had_injuries;
            $this->number_of_fatalities = $accident->number_of_fatalities;
            $this->number_of_injuries = $accident->number_of_injuries;
            $this->comments = $accident->comments;
            
            // Cargar documentos existentes
            $this->loadExistingFiles($accident);
            
        } catch (\Exception $e) {
            Log::error('Error al cargar datos del accidente: ' . $e->getMessage(), [
                'exception' => $e,
                'accident_id' => $accidentId
            ]);
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al cargar datos del accidente: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Carga archivos existentes del accidente
     */
    protected function loadExistingFiles($accident)
    {
        $this->existingFiles = [];
        
        $mediaItems = $accident->getMedia('accident-images');
        
        foreach ($mediaItems as $media) {
            $this->existingFiles[] = [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'size' => $media->size,
                'mime_type' => $media->mime_type,
                'url' => $media->getUrl(),
                'created_at' => $media->created_at->format('d-m-Y H:i:s'),
            ];
        }
    }
    
    /**
     * Resetea el formulario
     */
    protected function resetForm()
    {
        $this->accidentId = null;
        $this->accident_date = null;
        $this->nature_of_accident = '';
        $this->had_fatalities = false;
        $this->had_injuries = false;
        $this->number_of_fatalities = 0;
        $this->number_of_injuries = 0;
        $this->comments = '';
        $this->tempFiles = [];
        $this->existingFiles = [];
    }
    
    /**
     * Maneja el evento de archivo subido desde el componente FileUploader
     */
    public function handleFileUploaded($event)
    {
        try {
            // Basado en la estructura que realmente envía el componente FileUploader
            $tempPath = $event['tempPath'] ?? null;
            $originalName = $event['originalName'] ?? null;
            $mimeType = $event['mimeType'] ?? null;
            $size = $event['size'] ?? null;
            $modelName = $event['modelName'] ?? null;
            
            if ($modelName === 'accident-images' && $tempPath) {
                $this->tempFiles[] = [
                    'path' => $tempPath,
                    'name' => $originalName,
                    'mime_type' => $mimeType,
                    'size' => $size,
                ];
                
                Log::info('Archivo recibido en DriverAccidentModal', [
                    'tempPath' => $tempPath,
                    'originalName' => $originalName,
                    'temp_files_count' => count($this->tempFiles)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar archivo subido en DriverAccidentModal: ' . $e->getMessage(), [
                'exception' => $e,
                'event' => $event
            ]);
        }
    }
    
    /**
     * Maneja el evento de archivo eliminado desde el componente FileUploader
     */
    public function handleFileRemoved($event)
    {
        try {
            $fileData = $event['fileData'];
            $modelName = $event['modelName'];
            
            if ($modelName === 'accident_documents') {
                // Si es un archivo existente (tiene ID), marcarlo para eliminación
                if (isset($fileData['id'])) {
                    $mediaId = $fileData['id'];
                    
                    // Eliminar el archivo de la base de datos
                    $media = Media::find($mediaId);
                    if ($media) {
                        $media->delete();
                        
                        // Eliminar de la lista de archivos existentes
                        $this->existingFiles = array_filter($this->existingFiles, function($file) use ($mediaId) {
                            return $file['id'] != $mediaId;
                        });
                        
                        Log::info('Archivo existente eliminado en DriverAccidentModal', [
                            'media_id' => $mediaId
                        ]);
                    }
                } 
                // Si es un archivo temporal, eliminarlo de la lista
                else if (isset($fileData['tempPath'])) {
                    $tempPath = $fileData['tempPath'];
                    
                    // Eliminar de la lista de archivos temporales
                    $this->tempFiles = array_filter($this->tempFiles, function($file) use ($tempPath) {
                        return $file['path'] != $tempPath;
                    });
                    
                    Log::info('Archivo temporal eliminado en DriverAccidentModal', [
                        'temp_path' => $tempPath
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar eliminación de archivo en DriverAccidentModal: ' . $e->getMessage(), [
                'exception' => $e,
                'event' => $event
            ]);
        }
    }
    
    /**
     * Procesa los archivos subidos a través de Livewire y los adjunta al modelo
     * 
     * @param DriverAccident $model Modelo al que se adjuntarán los archivos
     * @param array $files Array de archivos temporales con sus metadatos
     * @param string $collection Nombre de la colección de archivos
     * @return bool
     */
    protected function processLivewireFiles($model, $files, $collection)
    {
        if (empty($files)) {
            return true;
        }
        
        foreach ($files as $fileData) {
            // Corregir la ruta del archivo temporal
            // Si la ruta ya incluye 'temp/', no añadir 'app/temp/' nuevamente
            $tempPath = $fileData['path'];
            if (strpos($tempPath, 'temp/') === 0) {
                $tempPath = storage_path('app/' . $tempPath);
            } else {
                $tempPath = storage_path('app/temp/' . $tempPath);
            }
            
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
                      ->toMediaCollection($collection, 'public');
                      
                Log::info('Archivo adjuntado exitosamente', [
                    'media_id' => $media->id,
                    'collection' => $collection,
                    'url' => $media->getUrl()
                ]);
                
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
     * Guarda el accidente
     */
    public function save()
    {
        $this->validate();
        
        $data = [
            'user_driver_detail_id' => $this->userDriverDetailId,
            'accident_date' => $this->accident_date,
            'nature_of_accident' => $this->nature_of_accident,
            'had_fatalities' => $this->had_fatalities,
            'had_injuries' => $this->had_injuries,
            'number_of_fatalities' => $this->number_of_fatalities,
            'number_of_injuries' => $this->number_of_injuries,
            'comments' => $this->comments,
        ];
        
        DB::beginTransaction();
        try {
            if ($this->accidentId) {
                // Actualizar accidente existente
                $accident = DriverAccident::find($this->accidentId);
                $accident->update($data);
            } else {
                // Crear nuevo accidente
                $accident = DriverAccident::create($data);
            }

            // Procesar archivos temporales
            if (count($this->tempFiles) > 0) {
                $this->processLivewireFiles($accident, $this->tempFiles, 'accident-images');
            }

            DB::commit();
            
            // Cerrar directamente el modal
            $this->showModal = false;
            
            // Emitir notificación de éxito
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Accidente guardado exitosamente.'
            ]);
            
            // Emitir un evento para actualizar la vista principal con los nuevos datos
            $this->dispatch('accident-updated', [
                'driverId' => $this->userDriverDetailId,
                'accidentId' => $accident->id,
                'timestamp' => now()->timestamp
            ]);
            
            // Resetear el formulario
            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar accidente: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $data
            ]);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Ha ocurrido un error al guardar el accidente: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Renderiza el componente
     */
    public function render()
    {
        return view('livewire.admin.driver.driver-accident-modal');
    }
}
