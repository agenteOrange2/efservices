<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserDriverDetail;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriverAccidentStep extends Component
{
    use WithFileUploads;
    
    // Accident Records
    public $has_accidents = false;
    public $accidents = [];
    
    // Accident Documents - Ahora será un array para cada accidente
    public $accident_files = [];
    
    // References
    public $driverId;
    
    // Listeners para eventos del componente FileUploader
    protected $listeners = ['fileUploaded', 'fileRemoved'];
    
    // Validation rules
    protected function rules()
    {
        $rules = [
            'has_accidents' => 'sometimes|boolean',
        ];
        
        // Validación para archivos de cada accidente
        if (!empty($this->accidents)) {
            foreach (range(0, count($this->accidents) - 1) as $index) {
                $rules["accident_files.{$index}.*"] = 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx';
            }
        }
        
        if ($this->has_accidents) {
            foreach (range(0, count($this->accidents) - 1) as $index) {
                $rules["accidents.{$index}.accident_date"] = 'required|date';
                $rules["accidents.{$index}.nature_of_accident"] = 'required|string|max:255';
                $rules["accidents.{$index}.number_of_injuries"] = 
                    "required_if:accidents.{$index}.had_injuries,true|nullable|integer|min:0";
                $rules["accidents.{$index}.number_of_fatalities"] = 
                    "required_if:accidents.{$index}.had_fatalities,true|nullable|integer|min:0";
            }
        }
        
        return $rules;
    }
    
    // Rules for partial saves
    protected function partialRules()
    {
        return [
            'has_accidents' => 'sometimes|boolean',
        ];
    }
    
    // Initialize
    public function mount($driverId = null)
    {
        $this->driverId = $driverId;
        if ($this->driverId) {
            $this->loadExistingData();
        }
        
        // Initialize with empty accident
        if ($this->has_accidents && empty($this->accidents)) {
            $this->accidents = [$this->getEmptyAccident()];
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
        $this->has_accidents = false;
        
        // Check if has accidents from application details
        if ($userDriverDetail->application && $userDriverDetail->application->details) {
            $this->has_accidents = (bool)(
                $userDriverDetail->application->details->has_accidents ?? false
            );
        }
        
        // Load accidents
        $accidents = $userDriverDetail->accidents;
        if ($accidents->count() > 0) {
            $this->has_accidents = true;
            $this->accidents = [];
            
            foreach ($accidents as $accident) {
                $this->accidents[] = [
                    'id' => $accident->id,
                    'accident_date' => $accident->accident_date ? 
                        $accident->accident_date->format('Y-m-d') : null,
                    'nature_of_accident' => $accident->nature_of_accident,
                    'had_injuries' => $accident->had_injuries,
                    'number_of_injuries' => $accident->number_of_injuries,
                    'had_fatalities' => $accident->had_fatalities,
                    'number_of_fatalities' => $accident->number_of_fatalities,
                    'comments' => $accident->comments,
                ];
            }
        }
        
        // Load existing accident documents
        $this->loadExistingAccidentDocs($userDriverDetail);
        
        // Initialize with empty accident if needed
        if ($this->has_accidents && empty($this->accidents)) {
            $this->accidents = [$this->getEmptyAccident()];
        }
    }
    
    // Save accident data to database
    protected function saveAccidentData()
    {
        try {
            DB::beginTransaction();
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                throw new \Exception('Driver not found');
            }
            
            // Update application details with accident flag
            if ($userDriverDetail->application && $userDriverDetail->application->details) {
                $userDriverDetail->application->details->update([
                    'has_accidents' => $this->has_accidents
                ]);
            }
            
            if (!$this->has_accidents) {
                // If no accidents, delete all existing records
                $userDriverDetail->accidents()->delete();
            } else {
                // Handle accidents
                $existingAccidentIds = $userDriverDetail->accidents()->pluck('id')->toArray();
                $updatedAccidentIds = [];
                
                foreach ($this->accidents as $accidentData) {
                    if (empty($accidentData['accident_date'])) continue;
                    
                    $accidentId = $accidentData['id'] ?? null;
                    if ($accidentId) {
                        // Update existing accident
                        $accident = $userDriverDetail->accidents()->find($accidentId);
                        if ($accident) {
                            $accident->update([
                                'accident_date' => $accidentData['accident_date'],
                                'nature_of_accident' => $accidentData['nature_of_accident'],
                                'had_injuries' => $accidentData['had_injuries'] ?? false,
                                'number_of_injuries' => $accidentData['had_injuries'] ? 
                                    ($accidentData['number_of_injuries'] ?? 0) : 0,
                                'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                                'number_of_fatalities' => $accidentData['had_fatalities'] ? 
                                    ($accidentData['number_of_fatalities'] ?? 0) : 0,
                                'comments' => $accidentData['comments'] ?? null,
                            ]);
                            $updatedAccidentIds[] = $accident->id;
                        }
                    } else {
                        // Create new accident
                        $accident = $userDriverDetail->accidents()->create([
                            'accident_date' => $accidentData['accident_date'],
                            'nature_of_accident' => $accidentData['nature_of_accident'],
                            'had_injuries' => $accidentData['had_injuries'] ?? false,
                            'number_of_injuries' => $accidentData['had_injuries'] ? 
                                ($accidentData['number_of_injuries'] ?? 0) : 0,
                            'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                            'number_of_fatalities' => $accidentData['had_fatalities'] ? 
                                ($accidentData['number_of_fatalities'] ?? 0) : 0,
                            'comments' => $accidentData['comments'] ?? null,
                        ]);
                        $updatedAccidentIds[] = $accident->id;
                    }
                }
                
                // Delete accidents that are no longer needed
                $accidentsToDelete = array_diff($existingAccidentIds, $updatedAccidentIds);
                if (!empty($accidentsToDelete)) {
                    $userDriverDetail->accidents()->whereIn('id', $accidentsToDelete)->delete();
                }
            }
            
            // Upload accident documents
            $this->uploadAccidentFiles($userDriverDetail);
            
            // Update current step
            $userDriverDetail->update(['current_step' => 8]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving accident information: ' . $e->getMessage());
            return false;
        }
    }
    
    // Add accident
    public function addAccident()
    {
        if (empty($this->accidents)) {
            $this->accidents = [];
        }
        
        $this->accidents[] = $this->getEmptyAccident();
        
        // Inicializar el array de accident_files si no existe
        if (empty($this->accident_files)) {
            $this->accident_files = [];
        }
        
        // Añadir un nuevo espacio en el array de archivos
        $this->accident_files[] = [];
    }
    
    // Remove accident
    public function removeAccident($index)
    {
        if (count($this->accidents) > 1) {
            unset($this->accidents[$index]);
            $this->accidents = array_values($this->accidents);
        }
    }
    
    // Get empty accident structure
    protected function getEmptyAccident()
    {
        return [
            'accident_date' => '',
            'nature_of_accident' => '',
            'had_injuries' => false,
            'number_of_injuries' => 0,
            'had_fatalities' => false,
            'number_of_fatalities' => 0,
            'comments' => '',
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
        if ($modelName === 'accident_files' && isset($this->accidents[$modelIndex])) {
            // Inicializar el array de documentos si no existe
            if (!isset($this->accidents[$modelIndex]['documents'])) {
                $this->accidents[$modelIndex]['documents'] = [];
            }
            
            // Subir el archivo al accidente correspondiente
            $mediaId = $this->uploadAccidentFile($tempPath, $originalName, $mimeType, $size, $modelIndex);
            
            // Si la subida fue exitosa y tenemos un ID de media, actualizar el previewData con el ID real
            if ($mediaId && $previewData && isset($previewData['is_temp']) && $previewData['is_temp']) {
                // Buscar el archivo temporal en la lista de documentos y reemplazarlo con el archivo real
                foreach ($this->accidents[$modelIndex]['documents'] as $key => $doc) {
                    if (isset($doc['is_temp']) && $doc['is_temp'] && $doc['name'] === $originalName) {
                        // Eliminar el archivo temporal
                        unset($this->accidents[$modelIndex]['documents'][$key]);
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
        $this->deleteAccidentDoc($fileId, $modelIndex);
    }
    
    /**
     * Sube un archivo al accidente correspondiente
     * @return int|null ID del archivo subido o null si hubo un error
     */
    private function uploadAccidentFile($tempPath, $originalName, $mimeType, $size, $index)
    {
        try {
            // Obtener el ID del accidente
            $accidentId = isset($this->accidents[$index]['id']) ? $this->accidents[$index]['id'] : null;
            
            if (!$accidentId) {
                // Si no existe el accidente en la base de datos, guardarlo primero
                $accidentId = $this->saveAccident($index);
            }
            
            // Buscar el modelo de accidente
            $accident = \App\Models\Admin\Driver\DriverAccident::find($accidentId);
            
            if ($accident) {
                // Obtener la ruta completa del archivo temporal
                $fullPath = storage_path('app/' . $tempPath);
                
                // Verificar que el archivo existe
                if (!file_exists($fullPath)) {
                    throw new \Exception("El archivo temporal no existe: {$fullPath}");
                }
                
                // Subir el archivo al accidente usando fromFile
                $media = $accident->addMediaFromDisk($tempPath, 'local')
                    ->usingName($originalName)
                    ->usingFileName($originalName)
                    ->toMediaCollection('accident_documents');
                
                // Agregar el archivo a la lista de documentos del accidente
                if (!isset($this->accidents[$index]['documents'])) {
                    $this->accidents[$index]['documents'] = [];
                }
                
                $this->accidents[$index]['documents'][] = [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'url' => $media->getUrl(),
                    'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                ];
                
                // Registrar información de depuración
                \Illuminate\Support\Facades\Log::info('Archivo de accidente subido correctamente', [
                    'accident_id' => $accidentId,
                    'media_id' => $media->id,
                    'path' => $media->getPath(),
                    'url' => $media->getUrl(),
                    'collection' => $media->collection_name
                ]);
                
                // Devolver el ID del archivo subido
                return $media->id;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al subir archivo de accidente: ' . $e->getMessage(), [
                'exception' => $e,
                'tempPath' => $tempPath ?? 'null',
                'accident_id' => $accidentId ?? 'null',
                'index' => $index ?? 'null'
            ]);
        }
        
        return null;
    }
    
    /**
     * Guarda un accidente en la base de datos
     */
    private function saveAccident($index)
    {
        $accident = $this->accidents[$index];
        
        // Crear el accidente en la base de datos
        $driverAccident = new \App\Models\Admin\Driver\DriverAccident();
        $driverAccident->driver_id = $this->driverId;
        $driverAccident->accident_date = $accident['accident_date'];
        $driverAccident->nature_of_accident = $accident['nature_of_accident'];
        $driverAccident->had_injuries = $accident['had_injuries'];
        $driverAccident->number_of_injuries = $accident['number_of_injuries'];
        $driverAccident->had_fatalities = $accident['had_fatalities'];
        $driverAccident->number_of_fatalities = $accident['number_of_fatalities'];
        $driverAccident->comments = $accident['comments'];
        $driverAccident->save();
        
        // Actualizar el ID en el array
        $this->accidents[$index]['id'] = $driverAccident->id;
        
        return $driverAccident->id;
    }
    
    // Next step
    public function next()
    {
        // Full validation
        $this->validate($this->rules());
        
        // Save to database
        if ($this->driverId) {
            $this->saveAccidentData();
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
            $this->saveAccidentData();
        }
        
        $this->dispatch('prevStep');
    }
    
    /**
     * Load existing accident documents from media library for each accident
     */
    protected function loadExistingAccidentDocs($userDriverDetail)
    {
        // Inicializar el array de accident_files si no existe
        if (empty($this->accident_files) && !empty($this->accidents)) {
            $this->accident_files = array_fill(0, count($this->accidents), []);
        } elseif (empty($this->accident_files)) {
            $this->accident_files = [];
        }
        
        // Cargar documentos para cada accidente
        foreach ($this->accidents as $index => $accident) {
            $accidentId = $accident['id'] ?? null;
            
            if ($accidentId) {
                $driverAccident = $userDriverDetail->accidents()->find($accidentId);
                if ($driverAccident) {
                    // Obtener documentos asociados a este accidente específico
                    $accidentMedia = $driverAccident->getMedia('accident_documents');
                    
                    // Almacenar información de documentos en el array de accidents
                    $this->accidents[$index]['documents'] = [];
                    
                    foreach ($accidentMedia as $media) {
                        // Asegurarse de que la URL sea accesible
                        $url = $media->getUrl();
                        
                        // Registrar información de depuración
                        \Illuminate\Support\Facades\Log::info('Cargando archivo de accidente existente', [
                            'media_id' => $media->id,
                            'file_name' => $media->file_name,
                            'url' => $url,
                            'accident_id' => $accidentId,
                            'index' => $index,
                            'collection' => $media->collection_name
                        ]);
                        
                        $this->accidents[$index]['documents'][] = [
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
     * Upload accident documents to media library for each accident
     */
    protected function uploadAccidentFiles($userDriverDetail)
    {
        foreach ($this->accidents as $index => $accident) {
            $accidentId = $accident['id'] ?? null;
            
            if ($accidentId && !empty($this->accident_files[$index])) {
                $driverAccident = $userDriverDetail->accidents()->find($accidentId);
                
                if ($driverAccident) {
                    foreach ($this->accident_files[$index] as $file) {
                        // Add file to media library in the 'accident-documents' collection, asociado al accidente específico
                        $driverAccident->addMedia($file->getRealPath())
                            ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                            ->usingFileName($file->getClientOriginalName())
                            ->withCustomProperties([
                                'original_filename' => $file->getClientOriginalName(),
                                'mime_type' => $file->getMimeType(),
                                'accident_id' => $accidentId
                            ])
                            ->toMediaCollection('accident-documents');
                    }
                }
            }
        }
        
        // Reset the file upload array
        if (!empty($this->accidents)) {
            $this->accident_files = array_fill(0, count($this->accidents), []);
        } else {
            $this->accident_files = [];
        }
        
        // Reload existing accident documents
        $this->loadExistingAccidentDocs($userDriverDetail);
    }
    
    /**
     * Delete an accident document
     */
    public function deleteAccidentDoc($mediaId, $accidentIndex)
    {
        try {
            $media = Media::find($mediaId);
            if ($media) {
                $media->delete();
                
                // Reload existing accident documents
                $userDriverDetail = UserDriverDetail::find($this->driverId);
                if ($userDriverDetail) {
                    $this->loadExistingAccidentDocs($userDriverDetail);
                }
                
                session()->flash('message', 'Accident document deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting accident document: ' . $e->getMessage());
        }
    }
    
    // Save and exit
    public function saveAndExit()
    {
        // Basic validation
        $this->validate($this->partialRules());
        
        // Save to database
        if ($this->driverId) {
            $this->saveAccidentData();
        }
        
        $this->dispatch('saveAndExit');
    }
    // Render
    public function render()
    {
        return view('livewire.admin.driver.steps.driver-accident-step');
    }
}
