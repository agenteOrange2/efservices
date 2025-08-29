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

echo "=== ANÁLISIS COMPLETO DEL USUARIO ===\n";
echo "Email: {$user->email}\n";
echo "ID: {$user->id}\n";
echo "Name: {$user->name}\n";
echo "User Status: {$user->status}\n";
echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
echo "\n";

// Verificar constantes de User (si existen)
echo "=== CONSTANTES DE STATUS PARA USERS ===\n";
echo "Según el código encontrado:\n";
echo "0 = Inactive\n";
echo "1 = Active\n";
echo "2 = Pending\n";
echo "\n";

// Verificar UserDriverDetail
echo "=== USER_DRIVER_DETAILS ===\n";
$driverDetails = $user->driverDetails;
if ($driverDetails) {
    echo "ID: {$driverDetails->id}\n";
    echo "Status: {$driverDetails->status}\n";
    echo "Application Completed: " . ($driverDetails->application_completed ? 'YES' : 'NO') . "\n";
    echo "Current Step: {$driverDetails->current_step}\n";
    echo "Created At: {$driverDetails->created_at}\n";
    
    echo "\nConstantes UserDriverDetail:\n";
    echo "STATUS_INACTIVE = " . UserDriverDetail::STATUS_INACTIVE . "\n";
    echo "STATUS_ACTIVE = " . UserDriverDetail::STATUS_ACTIVE . "\n";
    echo "STATUS_PENDING = " . UserDriverDetail::STATUS_PENDING . "\n";
} else {
    echo "No tiene user_driver_details\n";
}
echo "\n";

// Verificar DriverApplication
echo "=== DRIVER_APPLICATIONS ===\n";
$application = $user->driverApplication;
if ($application) {
    echo "ID: {$application->id}\n";
    echo "Status: {$application->status}\n";
    echo "PDF Path: {$application->pdf_path}\n";
    echo "Completed At: {$application->completed_at}\n";
    echo "Rejection Reason: {$application->rejection_reason}\n";
    echo "Created At: {$application->created_at}\n";
    
    echo "\nConstantes DriverApplication:\n";
    echo "STATUS_DRAFT = " . DriverApplication::STATUS_DRAFT . "\n";
    echo "STATUS_PENDING = " . DriverApplication::STATUS_PENDING . "\n";
    echo "STATUS_APPROVED = " . DriverApplication::STATUS_APPROVED . "\n";
    echo "STATUS_REJECTED = " . DriverApplication::STATUS_REJECTED . "\n";
} else {
    echo "No tiene driver_application\n";
}
echo "\n";

// Análisis del middleware paso a paso
echo "=== ANÁLISIS DEL MIDDLEWARE PASO A PASO ===\n";
echo "\n1. VERIFICACIÓN DE USUARIO ACTIVO (línea 206 del middleware):\n";
if ($user->status != 1) {
    echo "❌ PROBLEMA ENCONTRADO: User status = {$user->status} (no es 1=Active)\n";
    echo "   El middleware debería cerrar sesión y redirigir a login\n";
    echo "   Línea del código: if (\$user->status != 1) { Auth::logout(); }\n";
} else {
    echo "✅ User status = 1 (Active)\n";
}

echo "\n2. VERIFICACIÓN DE DRIVER DETAILS:\n";
if (!$driverDetails) {
    echo "❌ No tiene driverDetails -> Debería redirigir a complete_registration\n";
} else {
    echo "✅ Tiene driverDetails\n";
    
    echo "\n3. VERIFICACIÓN DE DRIVER DETAILS STATUS:\n";
    if ($driverDetails->status != UserDriverDetail::STATUS_ACTIVE) {
        echo "❌ UserDriverDetail status = {$driverDetails->status} (no es " . UserDriverDetail::STATUS_ACTIVE . "=Active)\n";
        echo "   Debería redirigir a driver.pending\n";
    } else {
        echo "✅ UserDriverDetail status es ACTIVE\n";
    }
    
    if (!$application) {
        echo "❌ No tiene application -> Se crearía una nueva en DRAFT\n";
    } else {
        echo "✅ Tiene application\n";
        
        echo "\n4. VERIFICACIÓN DE APPLICATION STATUS:\n";
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
            echo "✅ Application no está APPROVED o tiene todos los documentos\n";
        }
    }
}

echo "\n=== CONCLUSIÓN ===\n";
if ($user->status == 1 &&
    $driverDetails &&
    $driverDetails->status == UserDriverDetail::STATUS_ACTIVE &&
    $application &&
    $application->status == DriverApplication::STATUS_APPROVED) {
    echo "✅ El usuario DEBERÍA tener acceso al dashboard\n";
} else {
    echo "❌ El usuario NO DEBERÍA tener acceso al dashboard\n";
    echo "\nRazones:\n";
    if ($user->status != 1) {
        echo "- User status no es Active (es {$user->status})\n";
    }
    if (!$driverDetails) {
        echo "- No tiene driverDetails\n";
    } elseif ($driverDetails->status != UserDriverDetail::STATUS_ACTIVE) {
        echo "- UserDriverDetail status no es Active (es {$driverDetails->status})\n";
    }
    if (!$application) {
        echo "- No tiene application\n";
    } elseif ($application->status != DriverApplication::STATUS_APPROVED) {
        echo "- DriverApplication status no es Approved (es {$application->status})\n";
    }
}

echo "\n=== RECOMENDACIÓN ===\n";
if ($user->status != 1) {
    echo "🔧 SOLUCIÓN: Actualizar user status a 1 (Active)\n";
    echo "   UPDATE users SET status = 1 WHERE email = 'checo@test.com';\n";
}
if ($driverDetails && $driverDetails->status != UserDriverDetail::STATUS_ACTIVE) {
    echo "🔧 SOLUCIÓN: Actualizar user_driver_details status a 1 (Active)\n";
    echo "   UPDATE user_driver_details SET status = 1 WHERE user_id = {$user->id};\n";
}
if ($application && $application->status != DriverApplication::STATUS_APPROVED) {
    echo "🔧 SOLUCIÓN: Actualizar driver_applications status a 'approved'\n";
    echo "   UPDATE driver_applications SET status = 'approved' WHERE user_id = {$user->id};\n";
}