<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\UserDriverDetail;
use App\Models\Admin\Driver\DriverApplication;

// Buscar usuario checo@test.com
$user = User::where('email', 'checo@test.com')->first();

if (!$user) {
    echo "Usuario no encontrado\n";
    exit;
}

echo "=== DATOS DEL USUARIO ===\n";
echo "ID: {$user->id}\n";
echo "Email: {$user->email}\n";
echo "Status: {$user->status}\n";
echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
echo "\n";

// Verificar user_driver_details
echo "=== USER_DRIVER_DETAILS ===\n";
$driverDetails = $user->driverDetails;
if ($driverDetails) {
    echo "ID: {$driverDetails->id}\n";
    echo "Status: {$driverDetails->status} (" . $driverDetails->status_name . ")\n";
    echo "Application Completed: " . ($driverDetails->application_completed ? 'YES' : 'NO') . "\n";
    echo "Current Step: {$driverDetails->current_step}\n";
    echo "Carrier ID: {$driverDetails->carrier_id}\n";
    echo "Created At: {$driverDetails->created_at}\n";
    
    // Constantes para referencia
    echo "\nConstantes UserDriverDetail:\n";
    echo "STATUS_INACTIVE = " . UserDriverDetail::STATUS_INACTIVE . "\n";
    echo "STATUS_ACTIVE = " . UserDriverDetail::STATUS_ACTIVE . "\n";
    echo "STATUS_PENDING = " . UserDriverDetail::STATUS_PENDING . "\n";
} else {
    echo "No tiene user_driver_details\n";
}
echo "\n";

// Verificar driver_applications
echo "=== DRIVER_APPLICATIONS ===\n";
$application = $user->driverApplication;
if ($application) {
    echo "ID: {$application->id}\n";
    echo "Status: {$application->status}\n";
    echo "PDF Path: {$application->pdf_path}\n";
    echo "Completed At: {$application->completed_at}\n";
    echo "Rejection Reason: {$application->rejection_reason}\n";
    echo "Created At: {$application->created_at}\n";
    
    // Constantes para referencia
    echo "\nConstantes DriverApplication:\n";
    echo "STATUS_DRAFT = " . DriverApplication::STATUS_DRAFT . "\n";
    echo "STATUS_PENDING = " . DriverApplication::STATUS_PENDING . "\n";
    echo "STATUS_APPROVED = " . DriverApplication::STATUS_APPROVED . "\n";
    echo "STATUS_REJECTED = " . DriverApplication::STATUS_REJECTED . "\n";
} else {
    echo "No tiene driver_application\n";
}
echo "\n";

// Verificar qué condiciones del middleware se cumplen
echo "=== ANÁLISIS DEL MIDDLEWARE ===\n";

if (!$driverDetails) {
    echo "❌ No tiene driverDetails -> Debería redirigir a complete_registration\n";
} else {
    echo "✅ Tiene driverDetails\n";
    
    if ($driverDetails->status != UserDriverDetail::STATUS_ACTIVE) {
        echo "❌ Status no es ACTIVE ({$driverDetails->status}) -> Debería redirigir a pending\n";
    } else {
        echo "✅ Status es ACTIVE\n";
    }
    
    if (!$application) {
        echo "❌ No tiene application -> Se crearía una nueva en DRAFT\n";
    } else {
        echo "✅ Tiene application\n";
        
        if ($application->status === DriverApplication::STATUS_DRAFT && !$driverDetails->application_completed) {
            echo "❌ Application en DRAFT y no completada -> Debería redirigir a registration step {$driverDetails->current_step}\n";
        } else {
            echo "✅ Application no está en DRAFT o está completada\n";
        }
        
        if ($application->status === DriverApplication::STATUS_PENDING) {
            echo "❌ Application en PENDING -> Debería redirigir a pending\n";
        } else {
            echo "✅ Application no está en PENDING\n";
        }
        
        if ($application->status === DriverApplication::STATUS_REJECTED) {
            echo "❌ Application REJECTED -> Debería redirigir a rejected\n";
        } else {
            echo "✅ Application no está REJECTED\n";
        }
        
        if ($application->status === DriverApplication::STATUS_APPROVED && !$driverDetails->hasRequiredDocuments()) {
            echo "❌ Application APPROVED pero faltan documentos -> Debería redirigir a documents\n";
        } else {
            echo "✅ Application no está APPROVED o tiene documentos\n";
        }
    }
}

echo "\n=== CONCLUSIÓN ===\n";
if ($user->status == 1 && 
    $driverDetails && 
    $driverDetails->status == UserDriverDetail::STATUS_ACTIVE && 
    $application && 
    $application->status == DriverApplication::STATUS_APPROVED && 
    $driverDetails->hasRequiredDocuments()) {
    echo "🟢 El usuario DEBERÍA tener acceso al dashboard\n";
} else {
    echo "🔴 El usuario NO debería tener acceso al dashboard\n";
}