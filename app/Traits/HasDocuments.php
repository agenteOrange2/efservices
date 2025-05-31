<?php

namespace App\Traits;

use App\Models\DocumentAttachment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasDocuments
{
    /**
     * Obtiene los documentos asociados a este modelo.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(DocumentAttachment::class, 'documentable');
    }
    
    /**
     * Obtiene los documentos de una colección específica.
     */
    public function getDocuments(string $collection = 'default')
    {
        return $this->documents()->where('collection', $collection)->get();
    }
    
    /**
     * Añade un documento al modelo.
     *
     * @param UploadedFile|string $file Archivo subido o ruta a un archivo existente
     * @param string $collection Nombre de la colección
     * @param array $customProperties Propiedades personalizadas para el documento
     * @return DocumentAttachment
     */
    public function addDocument($file, string $collection = 'default', array $customProperties = []): DocumentAttachment
    {
        // Determinar el nombre original del archivo
        $originalName = $file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file);
        
        // Extraer la extensión del archivo
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Formatear el nombre base para eliminar caracteres problemáticos
        $safeBaseName = $this->sanitizeFileName($baseName);
        
        // Crear el nombre final: nombre-seguro.extensión (sin timestamp para respetar el nombre original)
        $fileName = $safeBaseName . '.' . $extension;
        
        // Determinar la ruta base donde se guardará (sin el nombre del archivo)
        $baseDir = $this->getDocumentPath($collection);
        $relativePath = $baseDir . '/' . $fileName;
        
        // Comprobar si ya existe un archivo con ese nombre
        if (Storage::disk('public')->exists($relativePath)) {
            // Añadir un timestamp solo si hay conflicto
            $fileName = time() . '_' . $fileName;
            $relativePath = $baseDir . '/' . $fileName;
        }
        
        // Guardar el archivo en el disco
        if ($file instanceof UploadedFile) {
            Storage::disk('public')->putFileAs(
                $baseDir, // Directorio base sin el nombre del archivo
                $file,
                $fileName // Solo el nombre del archivo
            );
            
            $mimeType = $file->getMimeType();
            $size = $file->getSize();
        } else {
            // Si es una ruta a un archivo temporal, copiarlo
            Storage::disk('public')->put($relativePath, file_get_contents($file));
            
            $mimeType = mime_content_type($file);
            $size = filesize($file);
        }
        
        // Crear el registro en la base de datos
        return $this->documents()->create([
            'file_path' => $relativePath,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'size' => $size,
            'collection' => $collection,
            'custom_properties' => $customProperties,
        ]);
    }
    
    /**
     * Elimina un documento.
     *
     * @param int $documentId ID del documento a eliminar
     * @return bool
     */
    public function deleteDocument(int $documentId): bool
    {
        \Illuminate\Support\Facades\Log::info("Iniciando proceso de eliminación de documento", [
            'document_id' => $documentId,
            'model_type' => get_class($this),
            'model_id' => $this->id
        ]);
        
        $document = $this->documents()->find($documentId);
        
        if (!$document) {
            \Illuminate\Support\Facades\Log::warning("Documento no encontrado para eliminar", ['document_id' => $documentId]);
            return false;
        }
        
        // Obtener la ruta completa del archivo para verificación
        $fullPath = Storage::disk('public')->path($document->file_path);
        $exists = file_exists($fullPath);
        
        // Limpiar la ruta (eliminar prefijo 'public/' si existe)
        $cleanPath = preg_replace('/^public\//', '', $document->file_path);
        
        \Illuminate\Support\Facades\Log::info("Información del archivo a eliminar", [
            'document_id' => $documentId,
            'file_name' => $document->file_name,
            'ruta_original' => $document->file_path,
            'ruta_limpia' => $cleanPath,
            'ruta_completa' => $fullPath,
            'archivo_existe' => $exists ? 'Sí' : 'No'
        ]);
        
        // Intentar eliminar con ambas rutas para asegurar que se elimine el archivo
        $deleted1 = Storage::disk('public')->delete($cleanPath);
        $deleted2 = Storage::disk('public')->delete($document->file_path);
        
        // Registrar el resultado de la eliminación del archivo físico
        \Illuminate\Support\Facades\Log::info("Resultado eliminación archivo físico", [
            'ruta_original_eliminada' => $deleted2 ? 'Sí' : 'No',
            'ruta_limpia_eliminada' => $deleted1 ? 'Sí' : 'No'
        ]);
        
        // Eliminar el registro directamente de la tabla para evitar problemas de eliminación en cascada
        // como ocurría con Spatie Media Library
        $deletedFromDb = \Illuminate\Support\Facades\DB::table('document_attachments')
            ->where('id', $documentId)
            ->delete();
            
        \Illuminate\Support\Facades\Log::info("Registro eliminado de la base de datos", [
            'document_id' => $documentId,
            'eliminado_db' => $deletedFromDb ? 'Sí' : 'No'
        ]);
        
        return $deletedFromDb;
    }
    
    /**
     * Genera la ruta relativa para un documento.
     * 
     * @param string $collection Nombre de la colección
     * @param string|null $fileName Nombre del archivo (opcional)
     * @return string Ruta relativa
     */
    protected function getDocumentPath(string $collection, ?string $fileName = null): string
    {
        // Para DriverAccident, usar exactamente la ruta especificada
        if ($this instanceof \App\Models\Admin\Driver\DriverAccident) {
            $driverId = $this->userDriverDetail->user_id ?? 'unknown';
            $path = "driver/{$driverId}/accidents/{$this->id}";
            return $fileName ? "{$path}/{$fileName}" : $path;
        }
        
        // Para TrafficConviction
        if ($this instanceof \App\Models\Admin\Driver\DriverTrafficConviction) {
            // Usar directamente el user_driver_detail_id que sabemos que es correcto
            $driverId = $this->user_driver_detail_id ?? 'unknown';
            
            // Agregar log para depuración
            \Illuminate\Support\Facades\Log::info('Generando ruta para documento de infracción', [
                'conviction_id' => $this->id,
                'user_driver_detail_id' => $this->user_driver_detail_id,
                'driver_id_usado' => $driverId,
                'path_generada' => "driver/{$driverId}/traffic_convictions/{$this->id}"
            ]);
            
            $path = "driver/{$driverId}/traffic_convictions/{$this->id}";
            return $fileName ? "{$path}/{$fileName}" : $path;
        }
        
        // Ruta por defecto para otros modelos
        $modelName = Str::snake(class_basename($this));
        $modelId = $this->getKey();
        $path = "documents/{$modelName}/{$modelId}/{$collection}";
        
        return $fileName ? "{$path}/{$fileName}" : $path;
    }
    
    /**
     * Elimina todos los documentos asociados al modelo.
     */
    public function deleteAllDocuments(): void
    {
        foreach ($this->documents as $document) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $this->documents()->delete();
    }
    
    /**
     * Sanitiza el nombre del archivo para eliminar caracteres problemáticos
     * 
     * @param string $fileName Nombre original del archivo
     * @return string Nombre sanitizado
     */
    protected function sanitizeFileName(string $fileName): string
    {
        // Lista de caracteres prohibidos en sistemas de archivos
        $forbiddenChars = ['\\', '/', ':', '*', '?', '"', '<', '>', '|'];
        
        // Reemplazar cada caracter prohibido con cadena vacía
        foreach ($forbiddenChars as $char) {
            $fileName = str_replace($char, '', $fileName);
        }
        
        // Reemplazar espacios con guiones bajos
        $fileName = str_replace(' ', '_', $fileName);
        
        // Eliminar caracteres acentuados
        $unwanted = array(
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ñ'=>'n',
            'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ú'=>'U', 'Ñ'=>'N'
        );
        $fileName = strtr($fileName, $unwanted);
        
        // Asegurar que el nombre no sea demasiado largo
        if (mb_strlen($fileName) > 100) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $baseName = pathinfo($fileName, PATHINFO_FILENAME);
            $fileName = mb_substr($baseName, 0, 90) . '.' . $extension;
        }
        
        return $fileName;
    }
}
