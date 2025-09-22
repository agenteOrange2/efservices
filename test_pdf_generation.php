<?php

require_once 'vendor/autoload.php';

// Cargar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Livewire\Admin\Driver\DriverCertificationStep;

echo "Verificando generación de PDFs...\n";

// Buscar un conductor de prueba
$driver = UserDriverDetail::with(['user', 'application', 'criminalHistory', 'carrier'])->first();

if ($driver) {
    echo "Driver encontrado: ID {$driver->id}\n";
    echo "Nombre: " . ($driver->user->first_name ?? 'N/A') . " " . ($driver->middle_name ?? '') . " " . ($driver->last_name ?? 'N/A') . "\n";
    
    // Verificar que el nombre completo se construya correctamente
    $fullName = trim(($driver->user->first_name ?? '') . ' ' . 
                    ($driver->middle_name ?? '') . ' ' . 
                    ($driver->last_name ?? ''));
    
    echo "Nombre completo construido: '{$fullName}'\n";
    
    // Verificar que las vistas existen
    $completeAppView = view()->exists('pdf.driver.complete_application');
    $criminalHistoryView = view()->exists('pdf.driver.criminal_history');
    
    echo "Vista complete_application existe: " . ($completeAppView ? 'Sí' : 'No') . "\n";
    echo "Vista criminal_history existe: " . ($criminalHistoryView ? 'Sí' : 'No') . "\n";
    
    echo "\nVerificación completada exitosamente.\n";
} else {
    echo "No se encontraron conductores en la base de datos.\n";
}