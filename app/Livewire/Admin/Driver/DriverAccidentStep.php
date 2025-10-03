<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
                        (is_string($accident->accident_date) ? $accident->accident_date : $accident->accident_date->format('Y-m-d')) : null,
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
    protected function saveAccidentData($saveFiles = false)
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
                                'number_of_injuries' => $accidentData['had_injuries'] ? $accidentData['number_of_injuries'] : null,
                                'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                                'number_of_fatalities' => $accidentData['had_fatalities'] ? $accidentData['number_of_fatalities'] : null,
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
                            'number_of_injuries' => $accidentData['had_injuries'] ? $accidentData['number_of_injuries'] : null,
                            'had_fatalities' => $accidentData['had_fatalities'] ?? false,
                            'number_of_fatalities' => $accidentData['had_fatalities'] ? $accidentData['number_of_fatalities'] : null,
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
                
                // Upload accident files solo si se solicita explícitamente
                if ($saveFiles) {
                    Log::info('Guardando archivos de accidentes permanentemente', [
                        'driver_id' => $this->driverId,
                        'accident_count' => count($this->accidents)
                    ]);
                    
                    // Procesar archivos temporales
                    foreach ($this->accidents as $index => $accident) {
                        if (isset($accident['temp_files']) && is_array($accident['temp_files'])) {
                            foreach ($accident['temp_files'] as $tempFile) {
                                $this->processTemporaryFile($tempFile, $index);
                            }
                            
                            // Limpiar los archivos temporales después de procesarlos
                            $this->accidents[$index]['temp_files'] = [];
                        }
                    }
                } else {
                    Log::info('Omitiendo guardado de archivos de accidentes (solo se guardarán al navegar)', [
                        'driver_id' => $this->driverId
                    ]);
                }
            }
            
            // Update current step
            $userDriverDetail->update(['current_step' => 6]);
            
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

    // Create accident in database
    public function createAccident($index)
    {
        try {
            // Validar que el índice existe
            if (!isset($this->accidents[$index])) {
                session()->flash('error', 'Accident not found.');
                return;
            }

            $accident = $this->accidents[$index];

            // Validar que los campos requeridos estén completos
            if (empty($accident['accident_date']) || empty($accident['nature_of_accident'])) {
                session()->flash('error', 'Please complete the accident date and nature of accident before creating the record.');
                return;
            }

            // Crear el accidente en la base de datos
            $driverAccident = \App\Models\Admin\Driver\DriverAccident::create([
                'user_driver_detail_id' => $this->driverId,
                'accident_date' => $accident['accident_date'],
                'nature_of_accident' => $accident['nature_of_accident'],
                'had_injuries' => $accident['had_injuries'] ?? false,
                'number_of_injuries' => $accident['number_of_injuries'] ?? 0,
                'had_fatalities' => $accident['had_fatalities'] ?? false,
                'number_of_fatalities' => $accident['number_of_fatalities'] ?? 0,
                'comments' => $accident['comments'] ?? '',
            ]);

            // Actualizar el array local con el ID
            $this->accidents[$index]['id'] = $driverAccident->id;

            // Actualizar has_accidents en driver_details
            $userDriverDetail = \App\Models\UserDriverDetail::where('driver_id', $this->driverId)->first();
            if ($userDriverDetail) {
                $userDriverDetail->update(['has_accidents' => true]);
            }

            session()->flash('success', 'Accident record created successfully. You can now upload documents.');
            
            \Log::info('Accident created successfully', [
                'driver_id' => $this->driverId,
                'accident_id' => $driverAccident->id,
                'accident_index' => $index
            ]);

        } catch (\Exception $e) {
            session()->flash('error', 'Error creating accident record: ' . $e->getMessage());
            \Log::error('Error creating accident: ' . $e->getMessage(), [
                'driver_id' => $this->driverId,
                'accident_index' => $index,
                'exception' => $e
            ]);
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
        
        // Registrar información para depuración
        Log::info('Archivo temporal recibido', [
            'temp_path' => $tempPath,
            'original_name' => $originalName,
            'model_index' => $modelIndex
        ]);
        
        // Verificar que el modelo y el índice sean correctos
        if ($modelName === 'accident_files' && isset($this->accidents[$modelIndex])) {
            // Inicializar el array de documentos si no existe
            if (!isset($this->accidents[$modelIndex]['documents'])) {
                $this->accidents[$modelIndex]['documents'] = [];
            }
            
            // Inicializar el array de archivos temporales si no existe
            if (!isset($this->accidents[$modelIndex]['temp_files'])) {
                $this->accidents[$modelIndex]['temp_files'] = [];
            }
            
            // Generar un ID temporal único
            $tempId = $previewData['id'] ?? ('temp_' . time() . '_' . rand(1000, 9999));
            
            // Guardar la información del archivo temporal para procesarlo más tarde
            $this->accidents[$modelIndex]['temp_files'][] = [
                'temp_path' => $tempPath,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'size' => $size,
                'temp_id' => $tempId
            ];
            
            // Agregar el archivo a la lista de documentos para mostrar en la interfaz
            $this->accidents[$modelIndex]['documents'][] = [
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
                'temp_files_count' => count($this->accidents[$modelIndex]['temp_files']),
                'documents_count' => count($this->accidents[$modelIndex]['documents'])
            ]);
        }
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
                'accident_index' => $index
            ]);
            
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
                
                // Subir el archivo al accidente usando fromFile con la colección correcta 'accident-images'
                $media = $accident->addMediaFromDisk($tempPath, 'local')
                    ->usingName($originalName)
                    ->usingFileName($originalName)
                    ->toMediaCollection('accident-images');
                
                Log::info('Archivo guardado permanentemente', [
                    'media_id' => $media->id,
                    'accident_id' => $accidentId,
                    'original_name' => $originalName
                ]);
                
                // Actualizar el documento en la lista para que ya no sea temporal
                foreach ($this->accidents[$index]['documents'] as $key => $doc) {
                    if (isset($doc['id']) && $doc['id'] === $tempId) {
                        $this->accidents[$index]['documents'][$key]['id'] = $media->id;
                        $this->accidents[$index]['documents'][$key]['is_temp'] = false;
                        $this->accidents[$index]['documents'][$key]['url'] = $media->getUrl();
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
                'accident_index' => $index,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
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
                // Usamos la colección 'accident-documents' para que coincida con el CustomPathGenerator
                $media = $accident->addMediaFromDisk($tempPath, 'local')
                    ->usingName($originalName)
                    ->usingFileName($originalName)
                    ->withCustomProperties([
                        'original_filename' => $originalName,
                        'mime_type' => $mimeType,
                        'accident_id' => $accidentId
                    ])
                    ->toMediaCollection('accident-documents'); // Cambiado a 'accident-documents' para coincidir con el CustomPathGenerator
                
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
                
                \Illuminate\Support\Facades\Log::info('Archivo de accidente subido correctamente', [
                    'accident_id' => $accidentId,
                    'media_id' => $media->id,
                    'path' => $media->getPath(),
                    'url' => $media->getUrl(),
                    'collection' => $media->collection_name,
                    'driver_id' => $accident->userDriverDetail->id ?? 'unknown'
                ]);
                
                // Devolver el ID del archivo subido
                return $media->id;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al subir archivo de accidente: ' . $e->getMessage(), [
                'exception' => $e,
                'tempPath' => $tempPath ?? 'null',
                'originalName' => $originalName ?? 'null',
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
        $driverAccident->user_driver_detail_id = $this->driverId; // Nombre correcto del campo
        $driverAccident->accident_date = $accident['accident_date'];
        $driverAccident->nature_of_accident = $accident['nature_of_accident'];
        $driverAccident->had_injuries = $accident['had_injuries'];
        $driverAccident->number_of_injuries = $accident['number_of_injuries'];
        $driverAccident->had_fatalities = $accident['had_fatalities'];
        $driverAccident->number_of_fatalities = $accident['number_of_fatalities'];
        $driverAccident->comments = $accident['comments'];
        
        // Registrar información de depuración
        \Illuminate\Support\Facades\Log::info('Guardando accidente', [
            'user_driver_detail_id' => $this->driverId,
            'accident_date' => $accident['accident_date'],
            'nature_of_accident' => $accident['nature_of_accident']
        ]);
        
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
        
        // Save to database with files
        if ($this->driverId) {
            Log::info('Guardando datos de accidentes y archivos al avanzar al siguiente paso', [
                'driver_id' => $this->driverId
            ]);
            $this->saveAccidentData(true); // true para guardar los archivos permanentemente
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
            Log::info('Guardando datos de accidentes y archivos al volver al paso anterior', [
                'driver_id' => $this->driverId
            ]);
            $this->saveAccidentData(true); // true para guardar los archivos permanentemente
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
                    // Obtener documentos asociados a este accidente específico usando la colección correcta
                    // Buscar archivos tanto en la colección nueva 'accident-images' como en la antigua 'accidents' para compatibilidad
                    $accidentMedia = $driverAccident->getMedia('accident-images');
                    
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
                            'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                            'is_image' => Str::startsWith($media->mime_type, 'image/'),
                            'is_temp' => false,
                            'collection' => $media->collection_name
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
                        // Registrar información de debug
                        \Illuminate\Support\Facades\Log::info('Subiendo archivo de accidente', [
                            'accident_id' => $accidentId,
                            'driver_id' => $userDriverDetail->id,
                            'file_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType()
                        ]);
                        
                        // Add file to media library usando la colección correcta 'accident-images'
                        // para que coincida con lo definido en el modelo DriverAccident
                        $driverAccident->addMedia($file->getRealPath())
                            ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                            ->usingFileName($file->getClientOriginalName())
                            ->withCustomProperties([
                                'original_filename' => $file->getClientOriginalName(),
                                'mime_type' => $file->getMimeType(),
                                'accident_id' => $accidentId
                            ])
                            ->toMediaCollection('accident-images');
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
            // Obtener información del accidente para ese índice
            $accidentData = $this->accidents[$accidentIndex] ?? null;
            if (!$accidentData || empty($accidentData['id'])) {
                session()->flash('error', 'No se encontró el accidente asociado al documento.');
                return;
            }
            
            $accidentId = $accidentData['id'];
            Log::info('Eliminando documento de accidente', [
                'media_id' => $mediaId,
                'accident_id' => $accidentId,
                'accident_index' => $accidentIndex
            ]);
            
            // Obtener el accidente desde la base de datos
            $userDriverDetail = UserDriverDetail::find($this->driverId);
            if (!$userDriverDetail) {
                session()->flash('error', 'No se encontró el conductor.');
                return;
            }
            
            $accident = $userDriverDetail->accidents()->find($accidentId);
            if (!$accident) {
                session()->flash('error', 'No se encontró el accidente en la base de datos.');
                return;
            }
            
            // Usar el método safeDeleteMedia para evitar la eliminación en cascada
            $result = $accident->safeDeleteMedia($mediaId);
            
            if ($result) {
                // Actualizar la vista
                $this->loadExistingAccidentDocs($userDriverDetail);
                session()->flash('message', 'Documento de accidente eliminado correctamente.');
            } else {
                session()->flash('error', 'No se pudo eliminar el documento.');
            }
        } catch (\Exception $e) {
            Log::error('Error eliminando documento de accidente', [
                'media_id' => $mediaId,
                'accident_index' => $accidentIndex,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al eliminar documento de accidente: ' . $e->getMessage());
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
        return view('livewire.driver.steps.accident-step');
    }
}
