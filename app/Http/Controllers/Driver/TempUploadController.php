<?php

namespace App\Http\Controllers\Driver;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Admin\TempUploadService;
use Illuminate\Support\Facades\Auth;

class TempUploadController extends Controller
{
    private TempUploadService $tempUploadService;
    
    public function __construct(TempUploadService $tempUploadService)
    {
        $this->tempUploadService = $tempUploadService;
        // No necesitamos aplicar middleware aquí ya que la ruta está fuera del grupo de autenticación
    }

    public function upload(Request $request)
    {
        // Asegurar que siempre devuelva JSON incluso si hay problemas
        // Verificamos si hay una sesión activa pero no requerimos autenticación
        if (!Auth::check()) {
            Log::info('Carga de archivo sin autenticación');
        }

        try {
            $validated = $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
                'type' => 'required|string' // Identificador del tipo de archivo
            ]);
            
            $file = $request->file('file');
            $type = $request->input('type');
            
            // Usar una subcarpeta según el tipo
            $result = $this->tempUploadService->store($file, "temp/{$type}");
            
            return response()->json($result);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error en carga temporal: ' . $e->getMessage());
            
            // Return a JSON error response
            return response()->json([
                'error' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 422);
        }
    }
}
