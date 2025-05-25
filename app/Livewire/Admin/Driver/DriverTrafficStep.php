<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverTrafficStep extends Component
{
    use WithFileUploads;
    
    // Traffic Convictions
    public $has_traffic_convictions = false;
    public $traffic_convictions = [];
    
    // Tickets/Documents - Ahora será un array para cada convicción
    public $ticket_files = [];
    
    // References
    public $driverId;
    
    // Listeners para eventos del componente FileUploader
    protected $listeners = ['fileUploaded', 'fileRemoved'];
    
    // Validation rules
    protected function rules()
    {
        $rules = [
            'has_traffic_convictions' => 'sometimes|boolean',
        ];
        
        // Validación para archivos de cada convicción
        if (!empty($this->traffic_convictions)) {
            foreach (range(0, count($this->traffic_convictions) - 1) as $index) {
                $rules["ticket_files.{$index}.*"] = 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx';
            }
        }
        
        if ($this->has_traffic_convictions) {
            foreach (range(0, count($this->traffic_convictions) - 1) as $index) {
                $rules["traffic_convictions.{$index}.conviction_date"] = 'required|date';
                $rules["traffic_convictions.{$index}.location"] = 'required|string|max:255';
                $rules["traffic_convictions.{$index}.charge"] = 'required|string|max:255';
                $rules["traffic_convictions.{$index}.penalty"] = 'required|string|max:255';
            }
        }
        
        return $rules;
    }
    
    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'has_traffic_convictions' => 'sometimes|boolean',
        ];
    }
    
    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        if ($this->driverId) {
            $this->loadExistingData();
        }
        
        // Initialize with empty traffic conviction
        if ($this->has_traffic_convictions && empty($this->traffic_convictions)) {
            $this->traffic_convictions = [$this->getEmptyTrafficConviction()];
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
        $this->has_traffic_convictions = false;
        
        // Check if has traffic convictions from application details
        if ($userDriverDetail->application && $userDriverDetail->application->details) {
            $this->has_traffic_convictions = (bool)(
                $userDriverDetail->application->details->has_traffic_convictions ?? false
            );
        }
        
        // Load traffic convictions
        $trafficConvictions = $userDriverDetail->trafficConvictions;
        if ($trafficConvictions->count() > 0) {
            $this->has_traffic_convictions = true;
            $this->traffic_convictions = [];
            
            foreach ($trafficConvictions as $conviction) {
                $this->traffic_convictions[] = [
                    'id' => $conviction->id,
                    'conviction_date' => $conviction->conviction_date ? 
                        $conviction->conviction_date->format('Y-m-d') : null,
                    'location' => $conviction->location,
                    'charge' => $conviction->charge,
                    'penalty' => $conviction->penalty,
                ];
            }
        }
        
        // Load existing ticket files
        $this->loadExistingTickets($userDriverDetail);
        
        // Initialize with empty traffic conviction if needed
        if ($this->has_traffic_convictions && empty($this->traffic_convictions)) {
            $this->traffic_convictions = [$this->getEmptyTrafficConviction()];
        }
    }
    
    // Save traffic data to database
    protected function saveTrafficData($saveFiles = false)
    {
        try {
            DB::beginTransaction();
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }
            
            // Update application details with traffic conviction flag
            if ($userDriverDetail->application && $userDriverDetail->application->details) {
                $userDriverDetail->application->details->update([
                    'has_traffic_convictions' => $this->has_traffic_convictions
                ]);
            }
            
            if (!$this->has_traffic_convictions) {
                // If no traffic convictions, delete all existing records
                $userDriverDetail->trafficConvictions()->delete();
            } else {
                // Handle traffic convictions
                $existingConvictionIds = $userDriverDetail->trafficConvictions()->pluck('id')->toArray();
                $updatedConvictionIds = [];
                
                foreach ($this->traffic_convictions as $convictionData) {
                    if (empty($convictionData['conviction_date'])) continue;
                    
                    $convictionId = $convictionData['id'] ?? null;
                    if ($convictionId) {
                        // Update existing conviction
                        $conviction = $userDriverDetail->trafficConvictions()->find($convictionId);
                        if ($conviction) {
                            $conviction->update([
                                'conviction_date' => $convictionData['conviction_date'],
                                'location' => $convictionData['location'],
                                'charge' => $convictionData['charge'],
                                'penalty' => $convictionData['penalty'],
                            ]);
                            $updatedConvictionIds[] = $conviction->id;
                        }
                    } else {
                        // Create new conviction
                        $conviction = $userDriverDetail->trafficConvictions()->create([
                            'conviction_date' => $convictionData['conviction_date'],
                            'location' => $convictionData['location'],
                            'charge' => $convictionData['charge'],
                            'penalty' => $convictionData['penalty'],
                        ]);
                        $updatedConvictionIds[] = $conviction->id;
                    }
                }
                
                // Delete convictions that are no longer needed
                $convictionsToDelete = array_diff($existingConvictionIds, $updatedConvictionIds);
                if (!empty($convictionsToDelete)) {
                    $userDriverDetail->trafficConvictions()->whereIn('id', $convictionsToDelete)->delete();
                }
                
                // Upload ticket files solo si se solicita explícitamente
                if ($saveFiles) {
                    Log::info('Guardando archivos permanentemente', [
                        'driver_id' => $this->driverId,
                        'conviction_count' => count($this->traffic_convictions)
                    ]);
                    
                    // Procesar archivos temporales
                    foreach ($this->traffic_convictions as $index => $conviction) {
                        if (isset($conviction['temp_files']) && is_array($conviction['temp_files'])) {
                            foreach ($conviction['temp_files'] as $tempFile) {
                                $this->processTemporaryFile($tempFile, $index);
                            }
                            
                            // Limpiar los archivos temporales después de procesarlos
                            $this->traffic_convictions[$index]['temp_files'] = [];
                        }
                    }
                } else {
                    Log::info('Omitiendo guardado de archivos (solo se guardarán al navegar)', [
                        'driver_id' => $this->driverId
                    ]);
                }
            }
            
            // Update current step
            $userDriverDetail->update(['current_step' => 7]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving traffic conviction information: ' . $e->getMessage());
            return false;
        }
    }
    
    // Add traffic conviction
    public function addTrafficConviction()
    {
        $this->traffic_convictions[] = $this->getEmptyTrafficConviction();
        
        // Add an empty array for ticket files for this conviction
        $this->ticket_files[] = [];
    }
    
    // Remove traffic conviction
    public function removeTrafficConviction($index)
    {
        if (isset($this->traffic_convictions[$index])) {
            unset($this->traffic_convictions[$index]);
            $this->traffic_convictions = array_values($this->traffic_convictions);
            
            // Also remove ticket files for this conviction
            if (isset($this->ticket_files[$index])) {
                unset($this->ticket_files[$index]);
                $this->ticket_files = array_values($this->ticket_files);
            }
        }
    }
    
    // Get empty traffic conviction structure
    protected function getEmptyTrafficConviction()
    {
        return [
            'conviction_date' => '',
            'location' => '',
            'charge' => '',
            'penalty' => '',
            'documents' => [], // Para almacenar los documentos asociados
            'temp_files' => [], // Para almacenar archivos temporales
        ];
    }
    
    /**
     * Maneja el evento fileUploaded del componente FileUploader
     */
    public function fileUploaded($data)
    {
        // Obtener los datos del evento
        $tempPath = $data['tempPath'];
        $originalName = $data['originalName'];
        $mimeType = $data['mimeType'];
        $size = $data['size'];
        $modelName = $data['modelName'];
        $modelIndex = $data['modelIndex'];
        $previewData = $data['previewData'] ?? null;
        
        // Registrar información para depuración
        Log::info('Archivo temporal recibido', [
            'temp_path' => $tempPath,
            'original_name' => $originalName,
            'model_index' => $modelIndex
        ]);
        
        // Verificar que el modelo y el índice sean correctos
        if ($modelName === 'ticket_files' && isset($this->traffic_convictions[$modelIndex])) {
            // Inicializar el array de documentos si no existe
            if (!isset($this->traffic_convictions[$modelIndex]['documents'])) {
                $this->traffic_convictions[$modelIndex]['documents'] = [];
            }
            
            // Inicializar el array de archivos temporales si no existe
            if (!isset($this->traffic_convictions[$modelIndex]['temp_files'])) {
                $this->traffic_convictions[$modelIndex]['temp_files'] = [];
            }
            
            // Generar un ID temporal único
            $tempId = $previewData['id'] ?? ('temp_' . time() . '_' . rand(1000, 9999));
            
            // Guardar la información del archivo temporal para procesarlo más tarde
            $this->traffic_convictions[$modelIndex]['temp_files'][] = [
                'temp_path' => $tempPath,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'size' => $size,
                'temp_id' => $tempId
            ];
            
            // Agregar el archivo a la lista de documentos para mostrar en la interfaz
            $this->traffic_convictions[$modelIndex]['documents'][] = [
                'id' => $tempId,
                'name' => $originalName,
                'file_name' => $originalName,
                'mime_type' => $mimeType,
                'size' => $size,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'url' => '#',
                'is_temp' => true
            ];
            
            Log::info('Archivo temporal almacenado para procesar más tarde', [
                'model_index' => $modelIndex,
                'temp_files_count' => count($this->traffic_convictions[$modelIndex]['temp_files']),
                'documents_count' => count($this->traffic_convictions[$modelIndex]['documents'])
            ]);
        }
    }
    
    /**
     * Maneja el evento fileRemoved del componente FileUploader
     */
    public function fileRemoved($data)
    {
        // Obtener los datos del evento
        $fileId = $data['fileId'];
        $modelIndex = $data['modelIndex'];
        $isTemp = $data['isTemp'] ?? false;
        
        Log::info('Evento fileRemoved recibido', [
            'file_id' => $fileId,
            'model_index' => $modelIndex,
            'is_temp' => $isTemp
        ]);
        
        // Eliminar el archivo
        $this->deleteTicketFile($fileId, $modelIndex);
    }
    
    /**
     * Procesa un archivo temporal y lo guarda permanentemente
     */
    protected function processTemporaryFile($tempFile, $index)
    {
        try {
            $tempPath = $tempFile['temp_path'];
            $originalName = $tempFile['original_name'];
            $mimeType = $tempFile['mime_type'];
            $size = $tempFile['size'];
            $tempId = $tempFile['temp_id'];
            
            Log::info('Procesando archivo temporal', [
                'temp_path' => $tempPath,
                'original_name' => $originalName,
                'conviction_index' => $index
            ]);
            
            // Obtener el ID de la convicción
            $convictionId = isset($this->traffic_convictions[$index]['id']) ? $this->traffic_convictions[$index]['id'] : null;
            
            if (!$convictionId) {
                // Si no existe la convicción en la base de datos, guardarla primero
                $convictionId = $this->saveTrafficConviction($index);
            }
            
            // Buscar el modelo de convicción
            $conviction = \App\Models\Admin\Driver\DriverTrafficConviction::find($convictionId);
            
            if ($conviction) {
                // Obtener la ruta completa del archivo temporal
                $fullPath = storage_path('app/' . $tempPath);
                
                // Verificar que el archivo existe
                if (!file_exists($fullPath)) {
                    throw new \Exception("El archivo temporal no existe: {$fullPath}");
                }
                
                // Subir el archivo a la convicción usando fromFile
                $media = $conviction->addMediaFromDisk($tempPath, 'local')
                    ->usingName($originalName)
                    ->usingFileName($originalName)
                    ->toMediaCollection('traffic_tickets');
                
                Log::info('Archivo guardado permanentemente', [
                    'media_id' => $media->id,
                    'conviction_id' => $convictionId,
                    'original_name' => $originalName
                ]);
                
                // Actualizar el documento en la lista para que ya no sea temporal
                foreach ($this->traffic_convictions[$index]['documents'] as $key => $doc) {
                    if (isset($doc['id']) && $doc['id'] === $tempId) {
                        $this->traffic_convictions[$index]['documents'][$key]['id'] = $media->id;
                        $this->traffic_convictions[$index]['documents'][$key]['is_temp'] = false;
                        $this->traffic_convictions[$index]['documents'][$key]['url'] = $media->getUrl();
                        break;
                    }
                }
                
                return $media->id;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error al procesar archivo temporal: ' . $e->getMessage(), [
                'exception' => $e,
                'temp_path' => $tempFile['temp_path'] ?? null,
                'conviction_index' => $index,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Delete ticket file
     */
    public function deleteTicketFile($mediaId, $convictionIndex)
    {
        try {
            // Verificar si es un archivo temporal (comienza con 'temp_')
            $isTemp = is_string($mediaId) && str_starts_with($mediaId, 'temp_');
            
            Log::info('Iniciando eliminación de archivo', [
                'media_id' => $mediaId,
                'conviction_index' => $convictionIndex,
                'is_temp' => $isTemp
            ]);
            
            // Si es un archivo temporal, solo actualizar la interfaz
            if ($isTemp) {
                Log::info('Eliminando archivo temporal', ['media_id' => $mediaId]);
                
                // Eliminar de la lista de archivos temporales
                if (isset($this->traffic_convictions[$convictionIndex]['temp_files'])) {
                    foreach ($this->traffic_convictions[$convictionIndex]['temp_files'] as $key => $tempFile) {
                        if (isset($tempFile['temp_id']) && $tempFile['temp_id'] == $mediaId) {
                            // Eliminar el archivo temporal del sistema de archivos
                            $tempPath = $tempFile['temp_path'] ?? null;
                            if ($tempPath && file_exists(storage_path('app/' . $tempPath))) {
                                @unlink(storage_path('app/' . $tempPath));
                                Log::info('Archivo temporal eliminado del sistema de archivos', [
                                    'temp_path' => $tempPath
                                ]);
                            }
                            
                            // Eliminar de la lista de archivos temporales
                            unset($this->traffic_convictions[$convictionIndex]['temp_files'][$key]);
                            $this->traffic_convictions[$convictionIndex]['temp_files'] = array_values($this->traffic_convictions[$convictionIndex]['temp_files']);
                            break;
                        }
                    }
                }
                
                // Actualizar la interfaz eliminando el archivo temporal
                if (isset($this->traffic_convictions[$convictionIndex]['documents'])) {
                    foreach ($this->traffic_convictions[$convictionIndex]['documents'] as $key => $doc) {
                        if ($doc['id'] == $mediaId) {
                            unset($this->traffic_convictions[$convictionIndex]['documents'][$key]);
                            $this->traffic_convictions[$convictionIndex]['documents'] = array_values($this->traffic_convictions[$convictionIndex]['documents']);
                            Log::info('Archivo temporal eliminado de la interfaz', ['media_id' => $mediaId]);
                            session()->flash('message', 'Archivo eliminado correctamente.');
                            break;
                        }
                    }
                }
            } else {
                // Para archivos reales (no temporales), eliminar de la base de datos y del disco
                $media = Media::find($mediaId);
                if ($media) {
                    // Registrar información antes de eliminar
                    $filePath = $media->getPath();
                    $collectionName = $media->collection_name;
                    $fileName = $media->file_name;
                    
                    Log::info('Eliminando archivo de la base de datos y disco', [
                        'media_id' => $mediaId,
                        'file_path' => $filePath,
                        'collection' => $collectionName,
                        'file_name' => $fileName
                    ]);
                    
                    // Eliminar el archivo de la base de datos (esto también elimina el archivo del disco)
                    $deleted = $media->delete();
                    Log::info('Resultado de eliminación de base de datos', ['deleted' => $deleted]);
                    
                    // Verificar si el archivo se eliminó correctamente del disco
                    if (file_exists($filePath)) {
                        Log::warning('El archivo no se eliminó del disco, intentando eliminación manual', [
                            'file_path' => $filePath
                        ]);
                        // Intentar eliminar manualmente
                        $unlinkResult = @unlink($filePath);
                        Log::info('Resultado de eliminación manual', ['unlink_result' => $unlinkResult]);
                    }
                    
                    // Eliminar el documento de la lista de documentos en la convicción correspondiente
                    if (isset($this->traffic_convictions[$convictionIndex]['documents'])) {
                        foreach ($this->traffic_convictions[$convictionIndex]['documents'] as $key => $doc) {
                            if ($doc['id'] == $mediaId) {
                                // Eliminar el documento de la lista
                                unset($this->traffic_convictions[$convictionIndex]['documents'][$key]);
                                // Reindexar el array para evitar índices vacíos
                                $this->traffic_convictions[$convictionIndex]['documents'] = array_values($this->traffic_convictions[$convictionIndex]['documents']);
                                break;
                            }
                        }
                    }
                    
                    // Notificar al usuario que el archivo se eliminó correctamente
                    session()->flash('message', 'Archivo eliminado correctamente.');
                } else {
                    Log::warning('No se encontró el archivo en la base de datos', [
                        'media_id' => $mediaId
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Registrar el error y notificar al usuario
            Log::error('Error deleting ticket file: ' . $e->getMessage(), [
                'exception' => $e,
                'media_id' => $mediaId,
                'conviction_index' => $convictionIndex,
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al eliminar el archivo: ' . $e->getMessage());
        }
    }
    
    /**
     * Guarda una convicción de tráfico en la base de datos
     */
    protected function saveTrafficConviction($index)
    {
        $trafficConviction = new \App\Models\Admin\Driver\DriverTrafficConviction([
            'user_driver_detail_id' => $this->driverId,
            'conviction_date' => $this->traffic_convictions[$index]['conviction_date'],
            'location' => $this->traffic_convictions[$index]['location'],
            'charge' => $this->traffic_convictions[$index]['charge'],
            'penalty' => $this->traffic_convictions[$index]['penalty'],
        ]);
        
        $trafficConviction->save();
        
        // Actualizar el ID en el array
        $this->traffic_convictions[$index]['id'] = $trafficConviction->id;
        
        return $trafficConviction->id;
    }
    
    /**
     * Next step
     */
    public function next()
    {
        // Full validation
        $this->validate($this->rules());
        
        // Save to database
        if ($this->driverId) {
            // Guardar los datos de tráfico, incluyendo los archivos temporales
            $this->saveTrafficData(true);
        }
        
        // Move to next step
        $this->dispatch('nextStep');
    }
    
    /**
     * Load existing ticket files from media library for each conviction
     */
    protected function loadExistingTickets($userDriverDetail)
    {
        // Inicializar el array de ticket_files si no existe
        if (empty($this->ticket_files) && !empty($this->traffic_convictions)) {
            $this->ticket_files = array_fill(0, count($this->traffic_convictions), []);
        } elseif (empty($this->ticket_files)) {
            $this->ticket_files = [];
        }
        
        // Cargar documentos para cada convicción
        foreach ($this->traffic_convictions as $index => $conviction) {
            $convictionId = $conviction['id'] ?? null;
            
            if ($convictionId) {
                $trafficConviction = $userDriverDetail->trafficConvictions()->find($convictionId);
                if ($trafficConviction) {
                    // Obtener documentos asociados a esta convicción específica
                    $ticketMedia = $trafficConviction->getMedia('traffic_tickets');
                    
                    // Almacenar información de documentos en el array de convictions
                    $this->traffic_convictions[$index]['documents'] = [];
                    
                    foreach ($ticketMedia as $media) {
                        // Asegurarse de que la URL sea accesible
                        $url = $media->getUrl();
                        
                        $this->traffic_convictions[$index]['documents'][] = [
                            'id' => $media->id,
                            'name' => $media->name,
                            'file_name' => $media->file_name,
                            'mime_type' => $media->mime_type,
                            'size' => $media->size,
                            'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                            'url' => $url,
                            'is_temp' => false
                        ];
                    }
                }
            }
        }
    }
    
    /**
     * Go to previous step
     */
    // Previous step
    public function previous()
    {
        // Basic save before going back
        if ($this->driverId) {
            $this->validate($this->partialRules());
            $this->saveTrafficData(true);
        }

        $this->dispatch('prevStep');
    }
    
    /**
     * Save and exit
     */
    public function saveAndExit()
    {
        // Basic validation
        $this->validate($this->partialRules());
        
        // Save to database
        if ($this->driverId) {
            // Guardar los datos y los archivos permanentemente
            $this->saveTrafficData(true);
        }
        
        $this->dispatch('saveAndExit');
    }
    
    /**
     * Render
     */
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-traffic-step');
    }
}