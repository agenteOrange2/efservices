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
            
            // Emitir evento al componente padre con el archivo cargado
            $this->dispatch('fileUploaded', [
                'file' => $file,
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
        // Emitir evento al componente padre para eliminar el archivo
        $this->dispatch('fileRemoved', [
            'fileId' => $fileId,
            'modelName' => $this->modelName,
            'modelIndex' => $this->modelIndex,
        ]);
    }
    
    /**
     * Renderizar el componente
     */
    public function render()
    {
        return view('livewire.components.file-uploader');
    }
}
