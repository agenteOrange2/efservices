<?php
// app/Services/TempUploadService.php

namespace App\Services\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class TempUploadService
{
    /**
     * Almacena un archivo temporalmente y devuelve su información
     */
    public function store(UploadedFile $file, string $folder = 'temp')
    {
        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');
        
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
}