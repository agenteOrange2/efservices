<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\TempUploadService;

class TempUploadController extends Controller
{
    public function __construct(
        private TempUploadService $tempUploadService
    ) {}
    
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'required|string' // Identificador del tipo de archivo
        ]);
        
        $file = $request->file('file');
        $type = $request->input('type');
        
        // Usar una subcarpeta según el tipo
        $result = $this->tempUploadService->store($file, "temp/{$type}");
        
        return response()->json($result);
    }
}
