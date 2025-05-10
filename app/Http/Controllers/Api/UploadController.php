<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Admin\TempUploadService;

class UploadController extends Controller
{
    private TempUploadService $tempUploadService;
    
    public function __construct(TempUploadService $tempUploadService)
    {
        $this->tempUploadService = $tempUploadService;
    }
    
    public function upload(Request $request)
    {
        try {
            // Validar la solicitud
            $validated = $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
                'type' => 'required|string'
            ]);
            
            $file = $request->file('file');
            $type = $request->input('type');
            
            // Almacenar el archivo
            $result = $this->tempUploadService->store($file, "temp/{$type}");
            
            // Asegurar que el token se guarde en la sesión correcta
            $token = $result['token'];
            $tempFiles = session('temp_files', []);
            
            // Guardar la información del archivo en la sesión
            $tempFiles[$token] = [
                'disk' => 'public',
                'path' => "temp/{$type}/" . basename($result['url']),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'created_at' => now()->toDateTimeString(),
            ];
            
            // Guardar en la sesión
            session(['temp_files' => $tempFiles]);
            
            // Registrar información en el log para depuración
            Log::info('Archivo temporal guardado correctamente', [
                'token' => $token,
                'path' => $tempFiles[$token]['path'],
                'session_id' => session()->getId()
            ]);
            
            // Devolver respuesta JSON
            return response()->json($result);
        } catch (\Exception $e) {
            // Log del error
            Log::error('Error en carga temporal API: ' . $e->getMessage());
            
            // Devolver respuesta de error
            return response()->json([
                'error' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 422);
        }
    }
}
