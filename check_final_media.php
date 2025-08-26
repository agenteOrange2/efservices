<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== Estado actual de Media Library ===\n";
    
    // Contar todos los registros de media
    $totalMedia = DB::table('media')->count();
    echo "Total de registros de media: {$totalMedia}\n\n";
    
    // Obtener todos los registros de media
    $mediaRecords = DB::table('media')
        ->select('id', 'model_type', 'model_id', 'collection_name', 'file_name', 'disk', 'created_at')
        ->orderBy('id', 'desc')
        ->get();
    
    if ($mediaRecords->count() > 0) {
        echo "Registros de media encontrados:\n";
        echo str_repeat('-', 100) . "\n";
        printf("%-4s %-30s %-8s %-20s %-25s %-8s %-20s\n", 
            'ID', 'Model', 'Model ID', 'Collection', 'File Name', 'Disk', 'Created At');
        echo str_repeat('-', 100) . "\n";
        
        foreach ($mediaRecords as $media) {
            $modelShort = str_replace('App\\Models\\', '', $media->model_type);
            printf("%-4s %-30s %-8s %-20s %-25s %-8s %-20s\n",
                $media->id,
                $modelShort,
                $media->model_id,
                $media->collection_name,
                substr($media->file_name, 0, 24),
                $media->disk,
                substr($media->created_at, 0, 19)
            );
        }
        echo str_repeat('-', 100) . "\n";
    } else {
        echo "No se encontraron registros de media.\n";
    }
    
    // Verificar archivos físicos para licencias
    echo "\n=== Verificación de archivos físicos ===\n";
    
    $licenseRecords = DB::table('media')
        ->where('collection_name', 'LIKE', 'license_%')
        ->get();
    
    if ($licenseRecords->count() > 0) {
        echo "Verificando archivos de licencias:\n";
        foreach ($licenseRecords as $license) {
            // Check if file_name is already a full path or relative path
            if (strpos($license->file_name, storage_path('app/public')) === 0) {
                // It's already a full path
                $fullPath = $license->file_name;
            } else {
                // It's a relative path, construct full path
                $fullPath = storage_path('app/public/' . ltrim($license->file_name, '/'));
            }
            
            $exists = file_exists($fullPath) ? 'SÍ' : 'NO';
            echo "  ID {$license->id}: {$license->file_name} - Existe: {$exists}\n";
            echo "    Ruta completa: {$fullPath}\n";
        }
    } else {
        echo "No se encontraron registros de licencias.\n";
    }
    
    echo "\n=== Verificación completada ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}