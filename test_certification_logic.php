<?php

require_once __DIR__ . '/vendor/autoload.php';

// Configurar el entorno Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserDriverDetail;
use App\Models\DriverApplication;
use App\Models\DriverApplicationDetail;
use App\Livewire\Driver\Steps\CertificationStep;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

echo "=== Test CertificationStep Logic ===\n";

// Test con driver ID 3
$driverId = 3;

echo "\n1. Verificando datos del driver ID: $driverId\n";

$userDriverDetail = UserDriverDetail::find($driverId);
if (!$userDriverDetail) {
    echo "ERROR: Driver no encontrado\n";
    exit(1);
}

echo "Driver encontrado: {$userDriverDetail->user->name}\n";

$application = $userDriverDetail->application;
if (!$application) {
    echo "ERROR: No se encontró aplicación\n";
    exit(1);
}

echo "Aplicación ID: {$application->id}\n";

$applicationDetails = $application->details;
if ($applicationDetails) {
    echo "Applying position actual: {$applicationDetails->applying_position}\n";
} else {
    echo "No hay detalles de aplicación\n";
}

echo "\n2. Probando lógica de CertificationStep\n";

// Crear instancia de CertificationStep
try {
    // Simular la lógica que está en CertificationStep
    $userDriverDetail = UserDriverDetail::find($driverId);
    $application = $userDriverDetail->application;
    
    if (!$application || !$application->details) {
        echo "ERROR: No se encontraron detalles de aplicación\n";
        exit(1);
    }
    
    $applyingPosition = $application->details->applying_position;
    echo "Applying position detectado: $applyingPosition\n";
    
    // Simular la lógica de determinación del tipo de documento
    if ($applyingPosition === 'owner_operator') {
        echo "✓ Debería generar: Lease Agreement (Owner Operator)\n";
        $expectedMethod = 'generateLeaseAgreementPDF';
    } elseif ($applyingPosition === 'third_party_driver') {
        echo "✓ Debería generar: Third Party Documents\n";
        $expectedMethod = 'generateThirdPartyDocumentsPDF';
    } else {
        echo "✓ Debería generar: Company Driver Documents\n";
        $expectedMethod = 'generateCompanyDriverDocumentsPDF';
    }
    
    echo "Método esperado: $expectedMethod\n";
    
    // Verificar si existen los detalles correspondientes
    if ($applyingPosition === 'owner_operator') {
        $ownerDetail = $application->ownerOperatorDetail;
        if ($ownerDetail) {
            echo "✓ Owner operator detail encontrado: {$ownerDetail->owner_name}\n";
        } else {
            echo "⚠️  WARNING: No se encontró owner operator detail\n";
        }
        
        $thirdPartyDetail = $application->thirdPartyDetail;
        if ($thirdPartyDetail) {
            echo "⚠️  WARNING: Third party detail existe cuando no debería: {$thirdPartyDetail->third_party_name}\n";
        } else {
            echo "✓ Third party detail correctamente ausente\n";
        }
    } elseif ($applyingPosition === 'third_party_driver') {
        $thirdPartyDetail = $application->thirdPartyDetail;
        if ($thirdPartyDetail) {
            echo "✓ Third party detail encontrado: {$thirdPartyDetail->third_party_name}\n";
        } else {
            echo "⚠️  WARNING: No se encontró third party detail\n";
        }
        
        $ownerDetail = $application->ownerOperatorDetail;
        if ($ownerDetail) {
            echo "⚠️  WARNING: Owner operator detail existe cuando no debería: {$ownerDetail->owner_name}\n";
        } else {
            echo "✓ Owner operator detail correctamente ausente\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n3. Probando con diferentes posiciones\n";

// Test con owner_operator
echo "\n--- Test Owner Operator ---\n";
DB::beginTransaction();
try {
    $application->details()->updateOrCreate([], ['applying_position' => 'owner_operator']);
    $application->thirdPartyDetail()->delete();
    $application->ownerOperatorDetail()->updateOrCreate([], [
        'owner_name' => 'Test Owner',
        'owner_phone' => '1234567890',
        'owner_email' => 'test@example.com',
        'contract_agreed' => true,
    ]);
    
    $application->refresh();
    $applyingPosition = $application->details->applying_position;
    echo "Position: $applyingPosition\n";
    
    if ($applyingPosition === 'owner_operator') {
        echo "✓ Correcto: Debería generar Lease Agreement\n";
    } else {
        echo "❌ ERROR: Position incorrecta\n";
    }
    
    DB::rollback();
} catch (Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Test con third_party_driver
echo "\n--- Test Third Party Driver ---\n";
DB::beginTransaction();
try {
    $application->details()->updateOrCreate([], ['applying_position' => 'third_party_driver']);
    $application->ownerOperatorDetail()->delete();
    $application->thirdPartyDetail()->updateOrCreate([], [
        'third_party_name' => 'Test Third Party',
        'third_party_phone' => '0987654321',
        'third_party_email' => 'thirdparty@example.com',
    ]);
    
    $application->refresh();
    $applyingPosition = $application->details->applying_position;
    echo "Position: $applyingPosition\n";
    
    if ($applyingPosition === 'third_party_driver') {
        echo "✓ Correcto: Debería generar Third Party Documents\n";
    } else {
        echo "❌ ERROR: Position incorrecta\n";
    }
    
    DB::rollback();
} catch (Exception $e) {
    DB::rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n4. Verificando el archivo CertificationStep.php\n";

$certificationStepPath = app_path('Livewire/Driver/Steps/CertificationStep.php');
if (file_exists($certificationStepPath)) {
    echo "✓ CertificationStep.php encontrado\n";
    
    // Buscar la lógica de determinación del tipo de documento
    $content = file_get_contents($certificationStepPath);
    
    if (strpos($content, 'applying_position') !== false) {
        echo "✓ Referencia a applying_position encontrada\n";
    } else {
        echo "⚠️  WARNING: No se encontró referencia a applying_position\n";
    }
    
    if (strpos($content, 'generateLeaseAgreementPDF') !== false) {
        echo "✓ Método generateLeaseAgreementPDF encontrado\n";
    } else {
        echo "⚠️  WARNING: Método generateLeaseAgreementPDF no encontrado\n";
    }
    
    if (strpos($content, 'generateThirdPartyDocumentsPDF') !== false) {
        echo "✓ Método generateThirdPartyDocumentsPDF encontrado\n";
    } else {
        echo "⚠️  WARNING: Método generateThirdPartyDocumentsPDF no encontrado\n";
    }
    
} else {
    echo "❌ ERROR: CertificationStep.php no encontrado en $certificationStepPath\n";
}

echo "\n=== Test completado ===\n";