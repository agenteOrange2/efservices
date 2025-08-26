<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\MediaLibrary\MediaCollections\Models\Media;

try {
    echo "=== Verificación de rutas de Media Library ===\n";
    
    // Verificar media ID 31 (license_front)
    $media31 = Media::find(31);
    if ($media31) {
        echo "\nMedia ID 31 (license_front):\n";
        echo "  File name: {$media31->file_name}\n";
        echo "  Collection: {$media31->collection_name}\n";
        echo "  Disk: {$media31->disk}\n";
        echo "  Path: {$media31->getPath()}\n";
        echo "  URL: {$media31->getUrl()}\n";
        echo "  Path relative to root: {$media31->getPathRelativeToRoot()}\n";
        echo "  File exists: " . (file_exists($media31->getPath()) ? 'SÍ' : 'NO') . "\n";
        
        // Verificar si existe en la ruta del CustomPathGenerator
        $customPath = storage_path('app/public/driver/1/licenses/front/card_front.jpg');
        echo "  Custom path exists: " . (file_exists($customPath) ? 'SÍ' : 'NO') . "\n";
        echo "  Custom path: {$customPath}\n";
    } else {
        echo "Media ID 31 no encontrado\n";
    }
    
    // Verificar media ID 32 (license_back)
    $media32 = Media::find(32);
    if ($media32) {
        echo "\nMedia ID 32 (license_back):\n";
        echo "  File name: {$media32->file_name}\n";
        echo "  Collection: {$media32->collection_name}\n";
        echo "  Disk: {$media32->disk}\n";
        echo "  Path: {$media32->getPath()}\n";
        echo "  URL: {$media32->getUrl()}\n";
        echo "  Path relative to root: {$media32->getPathRelativeToRoot()}\n";
        echo "  File exists: " . (file_exists($media32->getPath()) ? 'SÍ' : 'NO') . "\n";
        
        // Verificar si existe en la ruta del CustomPathGenerator
        $customPath = storage_path('app/public/driver/1/licenses/back/card_back.jpg');
        echo "  Custom path exists: " . (file_exists($customPath) ? 'SÍ' : 'NO') . "\n";
        echo "  Custom path: {$customPath}\n";
    } else {
        echo "Media ID 32 no encontrado\n";
    }
    
    // Listar todos los archivos en storage/app/public
    echo "\n=== Archivos en storage/app/public ===\n";
    $publicPath = storage_path('app/public');
    if (is_dir($publicPath)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($publicPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($publicPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                echo "  {$relativePath}\n";
            }
        }
    } else {
        echo "Directorio storage/app/public no existe\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}