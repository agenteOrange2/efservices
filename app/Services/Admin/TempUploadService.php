<?php
// app/Services/TempUploadService.php

namespace App\Services\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

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
        $tempFile = $this->get($token);
        
        if (!$tempFile) {
            return false;
        }
        
        $sourcePath = Storage::disk($tempFile['disk'])->path($tempFile['path']);
        
        if (!file_exists($sourcePath)) {
            return false;
        }
        
        // Devolvemos la ruta física al archivo
        return $sourcePath;
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