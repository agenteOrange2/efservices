<?php
// app/Services/TempUploadService.php

namespace App\Services\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TempUploadService
{
    /**
     * Almacena un archivo temporalmente y devuelve su información
     */
    public function store(UploadedFile $file, string $folder = 'temp')
    {
        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');
        
        // Aplicar compresión automática si es una imagen
        $this->compressAndResizeImage($file, $path);
        
        // Crear un token único para este archivo
        $token = Str::random(20);
        
        // En Laravel 11, es recomendable usar sesiones de manera explícita
        $tempFiles = Session::get('temp_files', []);
        $tempFiles[$token] = [
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'created_at' => now()->toDateTimeString(),
        ];
        Session::put('temp_files', $tempFiles);
        
        return [
            'token' => $token,
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'url' => Storage::disk('public')->url($path)
        ];
    }
    
    /**
     * Recupera un archivo temporal por su token
     */
    public function get(string $token)
    {
        $tempFiles = Session::get('temp_files', []);
        return $tempFiles[$token] ?? null;
    }
    
    /**
     * Transfiere un archivo temporal a su destino permanente
     */
    public function moveToPermanent(string $token)
    {
        try {
            // Log detallado al inicio
            Log::info('Iniciando moveToPermanent', ['token' => $token]);
            
            $tempFiles = Session::get('temp_files', []);
            
            Log::info('Estado actual de tempFiles', [
                'token_exists' => isset($tempFiles[$token]),
                'total_temp_files' => count($tempFiles),
                'available_tokens' => array_keys($tempFiles)
            ]);
            
            $tempFile = $tempFiles[$token] ?? null;
            
            if (!$tempFile) {
                Log::error('Token no encontrado en archivos temporales', ['token' => $token]);
                return false;
            }
            
            Log::info('Información del archivo temporal encontrado', [
                'token' => $token,
                'disk' => $tempFile['disk'],
                'path' => $tempFile['path'],
                'original_name' => $tempFile['original_name'] ?? 'unknown'
            ]);
            
            $sourcePath = Storage::disk($tempFile['disk'])->path($tempFile['path']);
            
            Log::info('Ruta completa del archivo', [
                'token' => $token,
                'source_path' => $sourcePath
            ]);
            
            if (!file_exists($sourcePath)) {
                Log::error('Archivo temporal no existe en el disco', [
                    'token' => $token, 
                    'path' => $sourcePath,
                    'disk_exists' => Storage::disk($tempFile['disk'])->exists($tempFile['path']),
                    'storage_path' => storage_path(),
                    'public_path' => public_path()
                ]);
                return false;
            }
            
            Log::info('Archivo encontrado, retornando ruta', [
                'token' => $token,
                'path' => $sourcePath,
                'size' => filesize($sourcePath),
                'mime' => mime_content_type($sourcePath)
            ]);
            
            // Eliminar el token procesado para que no se pueda usar nuevamente
            unset($tempFiles[$token]);
            Session::put('temp_files', $tempFiles);
            
            return $sourcePath;
        } catch (\Exception $e) {
            Log::error('Error en moveToPermanent', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Limpia archivos temporales viejos
     */
    public function cleanOldFiles($hours = 24)
    {
        $tempFiles = Session::get('temp_files', []);
        $cleaned = [];
        
        foreach ($tempFiles as $token => $file) {
            $createdAt = isset($file['created_at']) ? new \DateTime($file['created_at']) : null;
            
            if ($createdAt && (new \DateTime())->diff($createdAt)->h > $hours) {
                Storage::disk($file['disk'])->delete($file['path']);
                // No incluimos en el array limpio
            } else {
                $cleaned[$token] = $file;
            }
        }
        
        Session::put('temp_files', $cleaned);
        
        return count($tempFiles) - count($cleaned);
    }

    /**
     * Comprime y redimensiona una imagen
     */
    private function compressAndResizeImage(UploadedFile $file, string $tempPath): bool
    {
        try {
            // Verificar si es una imagen
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $imageExtensions)) {
                Log::info('Archivo no es imagen, saltando compresión', [
                    'extension' => $extension,
                    'file' => $file->getClientOriginalName()
                ]);
                return true; // No es imagen, no necesita compresión
            }

            Log::info('Iniciando compresión de imagen', [
                'original_name' => $file->getClientOriginalName(),
                'original_size' => $file->getSize(),
                'temp_path' => $tempPath
            ]);

            // Crear manager de imagen
            $manager = new ImageManager(new Driver());
            
            // Leer la imagen desde el archivo temporal
            $fullPath = Storage::disk('public')->path($tempPath);
            $image = $manager->read($fullPath);
            
            // Redimensionar manteniendo proporción (máximo 800px de ancho)
            if ($image->width() > 800) {
                $image->scaleDown(width: 800);
            }
            
            // Comprimir y guardar como JPEG con 80% de calidad
            $image->toJpeg(80)->save($fullPath);
            
            $newSize = filesize($fullPath);
            
            Log::info('Compresión completada', [
                'original_size' => $file->getSize(),
                'new_size' => $newSize,
                'reduction_percentage' => round((($file->getSize() - $newSize) / $file->getSize()) * 100, 2)
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error al comprimir imagen', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}