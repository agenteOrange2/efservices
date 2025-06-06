<?php

namespace App\Livewire\Admin\Driver;

use App\Models\Admin\Driver\DriverTrafficConviction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverTrafficModal extends Component
{
    use WithFileUploads;
    
    // Estado del modal
    public $showModal = false;
    public $userDriverDetailId;
    
    // ID del registro de tráfico (para edición)
    public $trafficId;
    
    // Propiedades del formulario
    public $conviction_date;
    public $location = '';
    public $charge = '';
    public $penalty = '';
    public $conviction_type = '';
    public $description = '';
    
    // Para la carga de documentos
    public $tempFiles = [];
    public $existingFiles = [];
    
    // Lista de tipos de infracciones de tráfico
    public $convictionTypes = [
        'speeding',
        'red_light',
        'stop_sign',
        'improper_lane_change',
        'following_too_close',
        'reckless_driving',
        'driving_under_influence',
        'no_insurance',
        'expired_license',
        'cell_phone_use',
        'other'
    ];
    
    // Event listeners
    protected $listeners = [
        'openTrafficModal' => 'openModal',
        'fileUploaded' => 'handleFileUploaded',
        'fileRemoved' => 'handleFileRemoved'
    ];
    
    // Reglas de validación
    protected $rules = [
        'conviction_date' => 'required|date',
        'location' => 'nullable|string|max:255',
        'charge' => 'required|string|max:255',
        'penalty' => 'nullable|string|max:255',
    ];
    
    /**
     * Abre el modal para crear o editar un registro de tráfico
     */
    public function openModal($driverId, $trafficId = null)
    {
        $this->userDriverDetailId = $driverId;
        $this->trafficId = $trafficId;
        
        if ($trafficId) {
            // Es una edición, cargar datos existentes
            $this->loadTrafficData($trafficId);
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
     * Carga los datos del registro de tráfico para edición
     */
    protected function loadTrafficData($trafficId)
    {
        try {
            $traffic = DriverTrafficConviction::findOrFail($trafficId);
            
            $this->conviction_date = $traffic->conviction_date ? 
                (is_string($traffic->conviction_date) ? $traffic->conviction_date : $traffic->conviction_date->format('Y-m-d')) : null;
            $this->location = $traffic->location;
            $this->charge = $traffic->charge;
            $this->penalty = $traffic->penalty;
            
            // Cargar documentos existentes
            $this->loadExistingFiles($traffic);
            
        } catch (\Exception $e) {
            Log::error('Error al cargar datos del registro de tráfico: ' . $e->getMessage(), [
                'exception' => $e,
                'traffic_id' => $trafficId
            ]);
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al cargar datos del registro de tráfico: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Carga archivos existentes del registro de tráfico
     */
    protected function loadExistingFiles($traffic)
    {
        $this->existingFiles = [];
        
        $mediaItems = $traffic->getMedia('traffic_images');
        
        foreach ($mediaItems as $media) {
            $this->existingFiles[] = [
                'id' => $media->id,
                'name' => $media->file_name,
                'size' => $media->size,
                'url' => $media->getUrl(),
                'mime_type' => $media->mime_type,
                'created_at' => $media->created_at ? $media->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            ];
        }
    }
    
    /**
     * Resetea el formulario
     */
    protected function resetForm()
    {
        $this->trafficId = null;
        $this->conviction_date = null;
        $this->location = '';
        $this->charge = '';
        $this->penalty = '';
        $this->tempFiles = [];
        $this->existingFiles = [];
    }
    
    /**
     * Maneja el evento de archivo subido desde el componente FileUploader
     */
    public function handleFileUploaded($event)
    {
        Log::info('Archivo subido en DriverTrafficModal', $event);
        
        // Verificar que el evento tenga los datos necesarios
        if (!isset($event['tempPath']) || !isset($event['originalName'])) {
            Log::error('Evento de archivo subido incompleto', $event);
            return;
        }
        
        // Agregar el archivo a la lista temporal
        $this->tempFiles[] = [
            'path' => $event['tempPath'],
            'name' => $event['originalName'],
            'size' => $event['size'] ?? 0,
            'mime_type' => $event['mimeType'] ?? null,
        ];
        
        Log::info('Archivo agregado a tempFiles', [
            'tempFiles' => $this->tempFiles
        ]);
    }
    
    /**
     * Maneja el evento de archivo eliminado desde el componente FileUploader
     */
    public function handleFileRemoved($event)
    {
        Log::info('Solicitud para eliminar archivo en DriverTrafficModal', $event);
        
        // Si es un archivo existente (tiene ID), eliminarlo de la base de datos
        if (isset($event['id'])) {
            $mediaId = $event['id'];
            
            // Si estamos editando un registro existente
            if ($this->trafficId) {
                try {
                    $traffic = DriverTrafficConviction::find($this->trafficId);
                    
                    if ($traffic) {
                        // Usar el método safeDeleteMedia del modelo
                        $result = $traffic->safeDeleteMedia($mediaId);
                        
                        if ($result) {
                            // Actualizar la lista de archivos existentes
                            $this->existingFiles = array_filter($this->existingFiles, function($file) use ($mediaId) {
                                return $file['id'] != $mediaId;
                            });
                            
                            $this->dispatch('notify', [
                                'type' => 'success',
                                'message' => 'Archivo eliminado correctamente'
                            ]);
                        } else {
                            $this->dispatch('notify', [
                                'type' => 'error',
                                'message' => 'No se pudo eliminar el archivo'
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error al eliminar archivo de tráfico', [
                        'error' => $e->getMessage(),
                        'media_id' => $mediaId
                    ]);
                    
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'Error al eliminar archivo: ' . $e->getMessage()
                    ]);
                }
            }
        } 
        // Si es un archivo temporal (tiene tempId), eliminarlo de la lista temporal
        else if (isset($event['tempId'])) {
            $tempId = $event['tempId'];
            
            // Filtrar la lista de archivos temporales
            $this->tempFiles = array_filter($this->tempFiles, function($file, $index) use ($tempId) {
                return $index != $tempId;
            }, ARRAY_FILTER_USE_BOTH);
            
            Log::info('Archivo temporal eliminado', [
                'tempId' => $tempId,
                'tempFiles' => $this->tempFiles
            ]);
        }
    }
    
    /**
     * Procesa los archivos subidos a través de Livewire y los adjunta al modelo
     * 
     * @param DriverTraffic $model Modelo al que se adjuntarán los archivos
     * @param array $files Array de archivos temporales con sus metadatos
     * @param string $collection Nombre de la colección de archivos
     * @return bool
     */
    protected function processLivewireFiles($model, $files, $collection)
    {
        foreach ($files as $fileData) {
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
     * Guarda el registro de tráfico
     */
    public function save()
    {
        $this->validate();
        
        $data = [
            'user_driver_detail_id' => $this->userDriverDetailId,
            'conviction_date' => $this->conviction_date,
            'location' => $this->location,
            'charge' => $this->charge,
            'penalty' => $this->penalty,
        ];
        
        DB::beginTransaction();
        try {
            if ($this->trafficId) {
                // Actualizar registro existente
                $traffic = DriverTrafficConviction::find($this->trafficId);
                $traffic->update($data);
            } else {
                // Crear nuevo registro
                $traffic = DriverTrafficConviction::create($data);
            }

            // Procesar archivos temporales
            if (count($this->tempFiles) > 0) {
                $this->processLivewireFiles($traffic, $this->tempFiles, 'traffic_images');
            }

            DB::commit();
            
            // Cerrar directamente el modal
            $this->showModal = false;
            
            // Emitir notificación de éxito
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro de tráfico guardado exitosamente.'
            ]);
            
            // Emitir un evento para actualizar la vista principal con los nuevos datos
            $this->dispatch('traffic-updated', [
                'driverId' => $this->userDriverDetailId,
                'trafficId' => $traffic->id,
                'timestamp' => now()->timestamp
            ]);
            
            // Resetear el formulario
            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar registro de tráfico: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $data
            ]);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Ha ocurrido un error al guardar el registro de tráfico: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Renderiza el componente
     */
    public function render()
    {
        return view('livewire.admin.driver.driver-traffic-modal');
    }
}
