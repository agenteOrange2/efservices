<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use App\Models\DocumentAttachment;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\Driver\TrainingSchool;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        
        // Obtener el documento de la base de datos
        $document = \App\Models\DocumentAttachment::find($documentId);
        
        if (!$document) {
            \Illuminate\Support\Facades\Log::warning("Documento no encontrado para eliminar", ['document_id' => $documentId]);
            return false;
        }
        
        // Información de la configuración del almacenamiento
        $diskConfig = config('filesystems.disks.public');
        \Illuminate\Support\Facades\Log::debug("Configuración del disco public", [
            'root' => $diskConfig['root'] ?? 'No definido',
            'url' => $diskConfig['url'] ?? 'No definido',
        ]);
        
        // Intentar diferentes rutas para asegurar la eliminación
        $filePath = $document->file_path;
        
        // Corregir rutas con prefijo 'public/' duplicado
        // Las rutas se pueden guardar de formas diferentes según el contexto
        $normalizedPath = $filePath;
        if (strpos($filePath, 'public/') === 0) {
            // Si comienza con 'public/', eliminar ese prefijo para la ruta real
            $normalizedPath = substr($filePath, 7); // quitar 'public/'
            \Illuminate\Support\Facades\Log::debug("Ruta normalizada sin prefijo public/", [
                'original' => $filePath,
                'normalizada' => $normalizedPath
            ]);
        }
        
        // Generar múltiples variantes de la ruta para probar
        $pathVariants = [
            $filePath,                       // Ruta original de la DB
            $normalizedPath,                 // Ruta sin prefijo 'public/'
            'public/' . $normalizedPath,     // Ruta con prefijo 'public/'
            'driver/' . $normalizedPath,     // Variante por si falta prefijo
        ];
        
        // Intentar cada variante con Storage::path y public_path
        $possiblePaths = [];
        foreach ($pathVariants as $variant) {
            $possiblePaths[] = Storage::disk('public')->path($variant);
            $possiblePaths[] = public_path('storage/' . $variant);
        }
        
        // Rutas principales para eliminar y verificar
        $fullPathFromStorage = Storage::disk('public')->path($filePath);
        $alternativePath = public_path('storage/' . $filePath);
        $normalizedStoragePath = Storage::disk('public')->path($normalizedPath);
        $normalizedPublicPath = public_path('storage/' . $normalizedPath);
        
        // Verificar la existencia en todas las rutas posibles
        $existingPaths = [];
        foreach ($possiblePaths as $index => $path) {
            if (file_exists($path)) {
                $existingPaths[$index] = $path;
                \Illuminate\Support\Facades\Log::debug("Archivo encontrado en ruta alternativa", [
                    'index' => $index,
                    'ruta' => $path
                ]);
            }
        }
        
        // Para compatibilidad con código existente
        $existsInStorage = file_exists($fullPathFromStorage);
        $existsInPublic = file_exists($alternativePath);
        $existsInNormalizedStorage = file_exists($normalizedStoragePath);
        $existsInNormalizedPublic = file_exists($normalizedPublicPath);
        
        // Registrar toda la información para análisis detallado
        \Illuminate\Support\Facades\Log::info("Información detallada del archivo a eliminar", [
            'document_id' => $documentId,
            'file_name' => $document->file_name,
            'original_name' => $document->original_name,
            'ruta_db' => $filePath,
            'ruta_normalizada' => $normalizedPath,
            'ruta_storage' => $fullPathFromStorage,
            'ruta_publica' => $alternativePath,
            'ruta_normalizada_storage' => $normalizedStoragePath,
            'ruta_normalizada_publica' => $normalizedPublicPath,
            'existe_en_storage' => $existsInStorage ? 'Sí' : 'No',
            'existe_en_public' => $existsInPublic ? 'Sí' : 'No',
            'existe_en_normalizada_storage' => $existsInNormalizedStorage ? 'Sí' : 'No',
            'existe_en_normalizada_publica' => $existsInNormalizedPublic ? 'Sí' : 'No',
            'rutas_existentes' => count($existingPaths),
            'permisos_storage' => $existsInStorage ? substr(sprintf('%o', fileperms($fullPathFromStorage)), -4) : 'N/A',
            'permisos_public' => $existsInPublic ? substr(sprintf('%o', fileperms($alternativePath)), -4) : 'N/A'
        ]);
        
        // Intentar eliminar el archivo físico usando múltiples métodos
        $fileDeleted = false;
        
        try {
            // 1. Intentar con Storage facade en diferentes variantes de ruta
            foreach ($pathVariants as $variant) {
                if (Storage::disk('public')->exists($variant)) {
                    $storageDelete = Storage::disk('public')->delete($variant);
                    \Illuminate\Support\Facades\Log::debug("Intento de eliminación con Storage::delete", [
                        'variante' => $variant,
                        'resultado' => $storageDelete ? 'Éxito' : 'Fallo'
                    ]);
                    if ($storageDelete) {
                        $fileDeleted = true;
                        break; // Si tuvo éxito, no seguir intentando
                    }
                }
            }
            
            // 2. Si no se pudo eliminar con Storage, intentar con unlink en todas las rutas existentes
            if (!$fileDeleted && !empty($existingPaths)) {
                foreach ($existingPaths as $path) {
                    $unlinkResult = @unlink($path);
                    \Illuminate\Support\Facades\Log::debug("Intento de eliminación con unlink", [
                        'ruta' => $path,
                        'resultado' => $unlinkResult ? 'Éxito' : 'Fallo',
                        'error' => $unlinkResult ? null : error_get_last()
                    ]);
                    if ($unlinkResult) {
                        $fileDeleted = true;
                        break; // Si tuvo éxito, no seguir intentando
                    }
                }
            }
            
            // 3. Intentos específicos con las rutas principales para compatibilidad
            if (!$fileDeleted) {
                // Intentar con rutas originales
                if ($existsInStorage) {
                    $unlinkStorage = @unlink($fullPathFromStorage);
                    \Illuminate\Support\Facades\Log::debug("Intento de eliminación con unlink en ruta de storage original", [
                        'resultado' => $unlinkStorage ? 'Éxito' : 'Fallo',
                        'error' => $unlinkStorage ? null : error_get_last()
                    ]);
                    $fileDeleted = $fileDeleted || $unlinkStorage;
                }
                
                // Intentar con ruta pública
                if (!$fileDeleted && $existsInPublic) {
                    $unlinkPublic = @unlink($alternativePath);
                    \Illuminate\Support\Facades\Log::debug("Intento de eliminación con unlink en ruta pública", [
                        'resultado' => $unlinkPublic ? 'Éxito' : 'Fallo',
                        'error' => $unlinkPublic ? null : error_get_last()
                    ]);
                    $fileDeleted = $fileDeleted || $unlinkPublic;
                }
                
                // Intentar con rutas normalizadas
                if (!$fileDeleted && $existsInNormalizedStorage) {
                    $unlinkNormStorage = @unlink($normalizedStoragePath);
                    \Illuminate\Support\Facades\Log::debug("Intento de eliminación con unlink en ruta storage normalizada", [
                        'resultado' => $unlinkNormStorage ? 'Éxito' : 'Fallo',
                        'error' => $unlinkNormStorage ? null : error_get_last()
                    ]);
                    $fileDeleted = $fileDeleted || $unlinkNormStorage;
                }
                
                if (!$fileDeleted && $existsInNormalizedPublic) {
                    $unlinkNormPublic = @unlink($normalizedPublicPath);
                    \Illuminate\Support\Facades\Log::debug("Intento de eliminación con unlink en ruta pública normalizada", [
                        'resultado' => $unlinkNormPublic ? 'Éxito' : 'Fallo',
                        'error' => $unlinkNormPublic ? null : error_get_last()
                    ]);
                    $fileDeleted = $fileDeleted || $unlinkNormPublic;
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Excepción al eliminar archivo físico", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // Si no existe el archivo, consideramos la eliminación como exitosa
        if (!$existsInStorage && !$existsInPublic) {
            \Illuminate\Support\Facades\Log::warning("El archivo físico no existe en ninguna ubicación");
            $fileDeleted = true;
        }
        
        \Illuminate\Support\Facades\Log::info("Resultado final de eliminación de archivo físico", [
            'archivo_eliminado' => $fileDeleted ? 'Sí' : 'No'
        ]);
        
        // Eliminar el registro de la base de datos
        $deletedFromDb = \Illuminate\Support\Facades\DB::table('document_attachments')
            ->where('id', $documentId)
            ->delete();
            
        \Illuminate\Support\Facades\Log::info("Registro eliminado de la base de datos", [
            'document_id' => $documentId,
            'eliminado_db' => $deletedFromDb ? 'Sí' : 'No'
        ]);
        
        // La operación es exitosa si se eliminó el registro de la base de datos y
        // se eliminó el archivo físico (o ya no existía)
        return $deletedFromDb && $fileDeleted;
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
        // Para DriverTrainingSchool, usar la ruta especificada
        if ($this instanceof \App\Models\Admin\Driver\DriverTrainingSchool) {
            // La escuela de entrenamiento puede estar asociada a un conductor o no
            $driverId = $this->user_driver_detail_id ?? 'general';
            $path = "driver/training_schools/{$this->id}";
            
            // Agregar log para depuración
            \Illuminate\Support\Facades\Log::info('Generando ruta para documento de escuela de entrenamiento', [
                'training_school_id' => $this->id,
                'driver_id' => $driverId,
                'path_generada' => $path
            ]);
            
            return $fileName ? "{$path}/{$fileName}" : $path;
        }
        
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
