<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class FileUploader extends Component
{
    use WithFileUploads;
    
    public $files = [];
    public $modelName;
    public $modelIndex;
    public $label;
    public $existingFiles = [];
    public $isUploading = false;
    public $progress = 0;
    public $accept = '.jpg,.jpeg,.png,.pdf,.doc,.docx';
    public $maxFileSize = 10240; // 10MB en KB
    
    protected $listeners = ['fileUploaded', 'removeFile'];
    
    /**
     * Mount the component
     *
     * @param string $modelName - Nombre del modelo en el componente padre (ej: 'ticket_files')
     * @param int $modelIndex - Índice del modelo en el componente padre (ej: 0, 1, 2...)
     * @param string $label - Etiqueta para mostrar en el componente
     * @param array $existingFiles - Archivos existentes para mostrar
     */
    public function mount($modelName, $modelIndex, $label = 'Upload Files', $existingFiles = [])
    {
        $this->modelName = $modelName;
        $this->modelIndex = $modelIndex;
        $this->label = $label;
        $this->existingFiles = $existingFiles;
        
        // Registrar información de depuración sobre los archivos existentes
        if (!empty($existingFiles)) {
            \Illuminate\Support\Facades\Log::info('FileUploader: Archivos existentes cargados', [
                'model_name' => $modelName,
                'model_index' => $modelIndex,
                'count' => count($existingFiles),
                'files' => $existingFiles
            ]);
        }
    }
    
    /**
     * Actualiza el progreso de carga
     */
    public function updatedFiles()
    {
        $this->validate([
            'files.*' => 'file|max:' . $this->maxFileSize . '|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);
        
        $this->isUploading = true;
        
        // Procesar cada archivo individualmente
        foreach ($this->files as $file) {
            // Almacenar temporalmente el archivo
            $tempPath = $file->store('temp');
            
            // Crear una vista previa temporal del archivo
            $previewData = [
                'id' => 'temp_' . time() . '_' . rand(1000, 9999),
                'name' => $file->getClientOriginalName(),
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'is_temp' => true
            ];
            
            // Si es una imagen, generar una URL temporal para la vista previa
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $previewData['url'] = $file->temporaryUrl();
            } else {
                // Para otros tipos de archivos, no tenemos una URL temporal
                $previewData['url'] = '#';
            }
            
            // Agregar el archivo a la lista de archivos existentes para mostrar inmediatamente
            $this->existingFiles[] = $previewData;
            
            // Registrar información para depuración
            Log::info('Archivo cargado en FileUploader', [
                'file_name' => $file->getClientOriginalName(),
                'temp_path' => $tempPath,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'model_name' => $this->modelName
            ]);
            
            // Emitir evento al componente padre con el archivo cargado
            $this->dispatch('fileUploaded', [
                'file' => null, // No enviamos el objeto completo por seguridad
                'tempPath' => $tempPath,
                'originalName' => $file->getClientOriginalName(),
                'mimeType' => $file->getMimeType(),
                'size' => $file->getSize(),
                'modelName' => $this->modelName,
                'modelIndex' => $this->modelIndex,
                'previewData' => $previewData
            ]);
        }
        
        // Resetear el input de archivos para permitir subir el mismo archivo nuevamente
        $this->files = [];
    }
    
    /**
     * Solicitar eliminar un archivo
     */
    public function removeFile($fileId)
    {
        // Verificar si es un archivo temporal (comienza con 'temp_')
        $isTemp = is_string($fileId) && str_starts_with($fileId, 'temp_');
        
        // Registrar información para depuración
        \Illuminate\Support\Facades\Log::info('Iniciando eliminación de archivo', [
            'file_id' => $fileId,
            'is_temp' => $isTemp,
            'model_name' => $this->modelName,
            'model_index' => $this->modelIndex
        ]);
        
        // Si es un archivo temporal, eliminar el archivo físico y actualizar la interfaz
        if ($isTemp) {
            // Buscar el archivo temporal en la lista de existingFiles
            $tempFilePath = null;
            $tempFileName = null;
            $realMediaId = null;
            
            foreach ($this->existingFiles as $key => $file) {
                if (isset($file['id']) && $file['id'] == $fileId) {
                    // Guardar información del archivo antes de eliminarlo de la lista
                    $tempFileName = $file['name'] ?? null;
                    $realMediaId = $file['media_id'] ?? null; // ID real en la base de datos si ya fue guardado
                    
                    // Eliminar el archivo de la lista
                    unset($this->existingFiles[$key]);
                    // Reindexar el array
                    $this->existingFiles = array_values($this->existingFiles);
                    break;
                }
            }
            
            // Si el archivo ya fue guardado en la base de datos, eliminarlo de allí también
            if ($realMediaId) {
                // Buscar y eliminar el archivo de la base de datos
                $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($realMediaId);
                if ($media) {
                    try {
                        // Registrar información antes de eliminar
                        $filePath = $media->getPath();
                        
                        // Eliminar el archivo de la base de datos
                        $deleted = $media->delete();
                        
                        \Illuminate\Support\Facades\Log::info('Archivo temporal ya guardado eliminado de la base de datos', [
                            'file_id' => $fileId,
                            'real_media_id' => $realMediaId,
                            'deleted' => $deleted,
                            'file_path' => $filePath
                        ]);
                        
                        // Verificar si el archivo se eliminó correctamente del disco
                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Error al eliminar archivo de la base de datos', [
                            'error' => $e->getMessage(),
                            'file_id' => $fileId,
                            'real_media_id' => $realMediaId
                        ]);
                    }
                }
            }
            
            // Buscar y eliminar el archivo en el directorio temp
            $tempDir = storage_path('app/temp');
            if (is_dir($tempDir)) {
                $files = scandir($tempDir);
                foreach ($files as $file) {
                    // Buscar por nombre de archivo si lo tenemos
                    if ($tempFileName && $file == $tempFileName) {
                        $fullPath = $tempDir . '/' . $file;
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                            \Illuminate\Support\Facades\Log::info('Archivo temporal eliminado por nombre', [
                                'file_id' => $fileId,
                                'file_name' => $file,
                                'full_path' => $fullPath
                            ]);
                        }
                    }
                }
            }
            
            // Buscar en todos los directorios temporales posibles
            $tempDirs = [
                storage_path('app/temp'),
                storage_path('app/livewire-tmp'),
                storage_path('app/public/temp')
            ];
            
            foreach ($tempDirs as $dir) {
                if (is_dir($dir)) {
                    $files = scandir($dir);
                    foreach ($files as $file) {
                        // Eliminar archivos que coincidan con el tiempo aproximado de creación
                        // El ID temporal contiene un timestamp
                        $idParts = explode('_', $fileId);
                        if (count($idParts) > 1) {
                            $timestamp = $idParts[1];
                            if (file_exists($dir . '/' . $file)) {
                                $fileTime = filemtime($dir . '/' . $file);
                                
                                // Si el archivo fue creado cerca del timestamp del ID temporal
                                if (abs($fileTime - $timestamp) < 60) { // 60 segundos de margen
                                    @unlink($dir . '/' . $file);
                                    \Illuminate\Support\Facades\Log::info('Archivo temporal eliminado por timestamp', [
                                        'file_id' => $fileId,
                                        'file_name' => $file,
                                        'timestamp' => $timestamp,
                                        'file_time' => $fileTime,
                                        'directory' => $dir
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            
            // Emitir evento al componente padre para que elimine cualquier referencia en la base de datos
            $this->dispatch('fileRemoved', [
                'fileId' => $fileId,
                'modelName' => $this->modelName,
                'modelIndex' => $this->modelIndex,
                'isTemp' => true,
                'realMediaId' => $realMediaId
            ]);
            
            // Registrar información para depuración
            \Illuminate\Support\Facades\Log::info('Archivo temporal eliminado completamente', [
                'file_id' => $fileId,
                'model_name' => $this->modelName,
                'model_index' => $this->modelIndex
            ]);
            
            return;
        }
        
        // Para archivos no temporales, emitir evento al componente padre
        $this->dispatch('fileRemoved', [
            'fileId' => $fileId,
            'modelName' => $this->modelName,
            'modelIndex' => $this->modelIndex,
            'isTemp' => false
        ]);
        
        // Inmediatamente actualizar la interfaz para reflejar la eliminación
        foreach ($this->existingFiles as $key => $file) {
            if (isset($file['id']) && $file['id'] == $fileId) {
                // Eliminar el archivo de la lista
                unset($this->existingFiles[$key]);
                // Reindexar el array
                $this->existingFiles = array_values($this->existingFiles);
                \Illuminate\Support\Facades\Log::info('Archivo permanente eliminado de la interfaz', [
                    'file_id' => $fileId
                ]);
                break;
            }
        }
    }
    
    /**
     * Renderizar el componente
     */
    public function render()
    {
        return view('livewire.components.file-uploader');
    }
}
