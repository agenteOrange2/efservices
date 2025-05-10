<?php
namespace App\Livewire\Driver\Steps;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TrafficStep extends Component
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
    protected function saveTrafficData()
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
                
                // Upload ticket files
                $this->uploadTicketFiles($userDriverDetail);
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
        if (empty($this->traffic_convictions)) {
            $this->traffic_convictions = [];
        }
        
        $this->traffic_convictions[] = $this->getEmptyTrafficConviction();
        
        // Inicializar el array de ticket_files si no existe
        if (empty($this->ticket_files)) {
            $this->ticket_files = [];
        }
        
        // Añadir un nuevo espacio en el array de archivos
        $this->ticket_files[] = [];
    }
    
    // Remove traffic conviction
    public function removeTrafficConviction($index)
    {
        if (count($this->traffic_convictions) > 1) {
            unset($this->traffic_convictions[$index]);
            $this->traffic_convictions = array_values($this->traffic_convictions);
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
        
        // Verificar que el modelo y el índice sean correctos
        if ($modelName === 'ticket_files' && isset($this->traffic_convictions[$modelIndex])) {
            // Inicializar el array de documentos si no existe
            if (!isset($this->traffic_convictions[$modelIndex]['documents'])) {
                $this->traffic_convictions[$modelIndex]['documents'] = [];
            }
            
            // Subir el archivo a la convicción correspondiente
            $mediaId = $this->uploadTicketFile($tempPath, $originalName, $mimeType, $size, $modelIndex);
            
            // Si la subida fue exitosa y tenemos un ID de media, actualizar el previewData con el ID real
            if ($mediaId && $previewData && isset($previewData['is_temp']) && $previewData['is_temp']) {
                // Buscar el archivo temporal en la lista de documentos y reemplazarlo con el archivo real
                foreach ($this->traffic_convictions[$modelIndex]['documents'] as $key => $doc) {
                    if (isset($doc['is_temp']) && $doc['is_temp'] && $doc['name'] === $originalName) {
                        // Eliminar el archivo temporal
                        unset($this->traffic_convictions[$modelIndex]['documents'][$key]);
                        break;
                    }
                }
            }
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
        
        // Eliminar el archivo
        $this->deleteTicket($fileId, $modelIndex);
    }
    
    /**
     * Sube un archivo a la convicción correspondiente
     * @return int|null ID del archivo subido o null si hubo un error
     */
    private function uploadTicketFile($tempPath, $originalName, $mimeType, $size, $index)
    {
        try {
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
                
                // Agregar el archivo a la lista de documentos de la convicción
                if (!isset($this->traffic_convictions[$index]['documents'])) {
                    $this->traffic_convictions[$index]['documents'] = [];
                }
                
                $this->traffic_convictions[$index]['documents'][] = [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'url' => $media->getUrl(),
                    'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                ];
                
                // Registrar información de depuración
                \Illuminate\Support\Facades\Log::info('Archivo de ticket subido correctamente', [
                    'conviction_id' => $convictionId,
                    'media_id' => $media->id,
                    'path' => $media->getPath(),
                    'url' => $media->getUrl(),
                    'collection' => $media->collection_name
                ]);
                
                // Devolver el ID del archivo subido
                return $media->id;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al subir archivo de ticket: ' . $e->getMessage(), [
                'exception' => $e,
                'tempPath' => $tempPath ?? 'null',
                'conviction_id' => $convictionId ?? 'null',
                'index' => $index ?? 'null'
            ]);
        }
        
        return null;
    }
    
    /**
     * Guarda una convicción de tráfico en la base de datos
     */
    private function saveTrafficConviction($index)
    {
        $conviction = $this->traffic_convictions[$index];
        
        // Crear la convicción en la base de datos
        $trafficConviction = new \App\Models\Admin\Driver\DriverTrafficConviction();
        $trafficConviction->driver_id = $this->driverId;
        $trafficConviction->conviction_date = $conviction['conviction_date'];
        $trafficConviction->location = $conviction['location'];
        $trafficConviction->charge = $conviction['charge'];
        $trafficConviction->penalty = $conviction['penalty'];
        $trafficConviction->save();
        
        // Actualizar el ID en el array
        $this->traffic_convictions[$index]['id'] = $trafficConviction->id;
        
        return $trafficConviction->id;
    }
    
    // Next step
    public function next()
    {
        // Full validation
        $this->validate($this->rules());
        
        // Save to database
        if ($this->driverId) {
            $this->saveTrafficData();
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
                        
                        // Registrar información de depuración
                        \Illuminate\Support\Facades\Log::info('Cargando archivo de ticket existente', [
                            'media_id' => $media->id,
                            'file_name' => $media->file_name,
                            'url' => $url,
                            'conviction_id' => $convictionId,
                            'index' => $index,
                            'collection' => $media->collection_name
                        ]);
                        
                        $this->traffic_convictions[$index]['documents'][] = [
                            'id' => $media->id,
                            'name' => $media->file_name,
                            'file_name' => $media->file_name,
                            'url' => $url,
                            'mime_type' => $media->mime_type,
                            'size' => $media->size,
                            'created_at' => $media->created_at->format('Y-m-d H:i:s')
                        ];
                    }
                }
            }
        }
    }
    
    /**
     * Upload ticket files to media library for each conviction
     */
    protected function uploadTicketFiles($userDriverDetail)
    {
        foreach ($this->traffic_convictions as $index => $conviction) {
            $convictionId = $conviction['id'] ?? null;
            
            if ($convictionId && !empty($this->ticket_files[$index])) {
                $trafficConviction = $userDriverDetail->trafficConvictions()->find($convictionId);
                
                if ($trafficConviction) {
                    foreach ($this->ticket_files[$index] as $file) {
                        // Add file to media library in the 'traffic-tickets' collection, asociado a la convicción específica
                        $trafficConviction->addMedia($file->getRealPath())
                            ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                            ->usingFileName($file->getClientOriginalName())
                            ->withCustomProperties([
                                'original_filename' => $file->getClientOriginalName(),
                                'mime_type' => $file->getMimeType(),
                                'conviction_id' => $convictionId
                            ])
                            ->toMediaCollection('traffic-tickets');
                    }
                }
            }
        }
        
        // Reset the file upload array
        if (!empty($this->traffic_convictions)) {
            $this->ticket_files = array_fill(0, count($this->traffic_convictions), []);
        } else {
            $this->ticket_files = [];
        }
        
        // Reload existing tickets
        $this->loadExistingTickets($userDriverDetail);
    }
    
    /**
     * Delete a ticket file
     */
    public function deleteTicket($mediaId, $convictionIndex)
    {
        try {
            $media = Media::find($mediaId);
            if ($media) {
                $media->delete();
                
                // Reload existing tickets
                $userDriverDetail = UserDriverDetail::find($this->driverId);
                if ($userDriverDetail) {
                    $this->loadExistingTickets($userDriverDetail);
                }
                
                session()->flash('message', 'Ticket file deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting ticket file: ' . $e->getMessage());
        }
    }
    
    // Previous step
    public function previous()
    {
        // Basic save before going back
        if ($this->driverId) {
            $this->validate($this->partialRules());
            $this->saveTrafficData();
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
            $this->saveTrafficData();
        }
        
        $this->dispatch('saveAndExit');
    }
    
    // Render
    public function render()
    {
        return view('livewire.driver.steps.traffic-step');
    }
}