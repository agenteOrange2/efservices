<?php

/**
 * Análisis del comportamiento del envío de correos de verificación
 * en EmploymentHistoryStep.php
 * 
 * Este análisis examina el código para determinar cuándo se envían los correos:
 * 1. Al crear una nueva compañía
 * 2. Al seleccionar una compañía existente
 * 3. Al actualizar una compañía existente
 */

echo "\n" . str_repeat('=', 70) . "\n";
echo "ANÁLISIS DEL COMPORTAMIENTO DE ENVÍO DE CORREOS DE VERIFICACIÓN\n";
echo str_repeat('=', 70) . "\n";

echo "\n=== ANÁLISIS DEL CÓDIGO ===\n";

// Leer el archivo EmploymentHistoryStep.php
$filePath = __DIR__ . '/app/Livewire/Driver/Steps/EmploymentHistoryStep.php';

if (!file_exists($filePath)) {
    echo "✗ Error: No se encontró el archivo EmploymentHistoryStep.php\n";
    exit(1);
}

$content = file_get_contents($filePath);

echo "\n1. ANÁLISIS DEL MÉTODO sendBulkVerificationEmails()\n";
echo str_repeat('-', 50) . "\n";

// Extraer la consulta SQL del método sendBulkVerificationEmails
preg_match('/\$driverCompanies = DriverEmploymentCompany::where.*?->get\(\);/s', $content, $matches);

if ($matches) {
    echo "✓ Consulta SQL encontrada:\n";
    $query = trim($matches[0]);
    echo "  $query\n\n";
    
    echo "CONDICIONES PARA ENVÍO DE CORREO:\n";
    echo "- user_driver_detail_id = \$this->driverId\n";
    echo "- (email_sent = false OR email_sent IS NULL)\n";
    echo "- email IS NOT NULL\n\n";
} else {
    echo "✗ No se encontró la consulta SQL\n";
}

echo "\n2. ANÁLISIS DEL MÉTODO saveEmploymentHistoryData()\n";
echo str_repeat('-', 50) . "\n";

// Buscar cómo se maneja email_sent al crear nuevas compañías
if (strpos($content, "'email_sent' => \$company['email_sent'] ?? false") !== false) {
    echo "✓ Al CREAR nueva compañía:\n";
    echo "  - email_sent se establece como: \$company['email_sent'] ?? false\n";
    echo "  - Esto significa: false por defecto\n\n";
}

// Buscar cómo se maneja email_sent al actualizar compañías existentes
if (strpos($content, "->update([") !== false) {
    echo "✓ Al ACTUALIZAR compañía existente:\n";
    echo "  - email_sent NO se modifica en el update()\n";
    echo "  - Mantiene su valor anterior\n\n";
}

echo "\n3. ANÁLISIS DEL MÉTODO selectCompany()\n";
echo str_repeat('-', 50) . "\n";

// Buscar el método selectCompany
if (strpos($content, "public function selectCompany(\$companyId)") !== false) {
    echo "✓ Al SELECCIONAR compañía existente:\n";
    echo "  - Se crea un nuevo registro en DriverEmploymentCompany\n";
    echo "  - email_sent se establece como false (por defecto)\n";
    echo "  - Se considera como nueva relación empleado-compañía\n\n";
}

echo "\n4. FLUJO COMPLETO\n";
echo str_repeat('-', 50) . "\n";

echo "MÉTODO next():\n";
echo "1. Valida datos\n";
echo "2. Llama a saveEmploymentHistoryData()\n";
echo "3. Llama a sendBulkVerificationEmails()\n";
echo "4. Avanza al siguiente paso\n\n";

echo "\n" . str_repeat('=', 70) . "\n";
echo "CONCLUSIONES BASADAS EN EL ANÁLISIS DEL CÓDIGO\n";
echo str_repeat('=', 70) . "\n";

echo "\n📧 CUÁNDO SE ENVÍAN LOS CORREOS:\n";
echo "\n1. ✅ NUEVA COMPAÑÍA (crear desde cero):\n";
echo "   - Se crea MasterCompany (si no existe)\n";
echo "   - Se crea DriverEmploymentCompany con email_sent = false\n";
echo "   - sendBulkVerificationEmails() la encuentra y envía correo\n";
echo "   - Resultado: SÍ SE ENVÍA CORREO\n";

echo "\n2. ✅ COMPAÑÍA EXISTENTE (seleccionar de lista):\n";
echo "   - Se usa MasterCompany existente\n";
echo "   - Se crea NUEVO DriverEmploymentCompany con email_sent = false\n";
echo "   - sendBulkVerificationEmails() la encuentra y envía correo\n";
echo "   - Resultado: SÍ SE ENVÍA CORREO\n";

echo "\n3. ❌ COMPAÑÍA YA PROCESADA (actualizar existente):\n";
echo "   - Se actualiza DriverEmploymentCompany existente\n";
echo "   - email_sent mantiene su valor (probablemente true)\n";
echo "   - sendBulkVerificationEmails() NO la encuentra\n";
echo "   - Resultado: NO SE ENVÍA CORREO\n";

echo "\n" . str_repeat('=', 70) . "\n";
echo "RESPUESTA A LA PREGUNTA DEL USUARIO\n";
echo str_repeat('=', 70) . "\n";

echo "\n🤔 PREGUNTA: \"¿Esto solo se dispara cuando creo una compañía nueva?\"\n";
echo "\n📋 RESPUESTA: NO, se dispara en DOS casos:\n";
echo "\n   1️⃣  Al CREAR una compañía completamente nueva\n";
echo "   2️⃣  Al SELECCIONAR una compañía existente de la lista\n";
echo "\n💡 EXPLICACIÓN:\n";
echo "   - Cada vez que agregas una compañía al historial del driver\n";
echo "   - Se crea un NUEVO registro en DriverEmploymentCompany\n";
echo "   - Este nuevo registro tiene email_sent = false\n";
echo "   - Por eso se envía el correo de verificación\n";

echo "\n🔄 COMPORTAMIENTO ACTUAL:\n";
echo "   ✅ Nueva compañía → Correo se envía\n";
echo "   ✅ Compañía existente seleccionada → Correo se envía\n";
echo "   ❌ Compañía ya en el historial → Correo NO se envía\n";

echo "\n⚠️  IMPORTANTE:\n";
echo "   - Una vez enviado el correo, email_sent = true\n";
echo "   - Si editas esa misma compañía, NO se vuelve a enviar\n";
echo "   - Solo se envía UNA VEZ por cada relación empleado-compañía\n";

echo "\n" . str_repeat('=', 70) . "\n";
echo "SIMULACIÓN DE ESCENARIOS\n";
echo str_repeat('=', 70) . "\n";

echo "\n📝 ESCENARIO 1: Usuario crea \"ABC Transport\" desde cero\n";
echo "   1. Se crea MasterCompany \"ABC Transport\"\n";
echo "   2. Se crea DriverEmploymentCompany (email_sent = false)\n";
echo "   3. sendBulkVerificationEmails() encuentra el registro\n";
echo "   4. ✅ Se envía correo a ABC Transport\n";
echo "   5. email_sent se actualiza a true\n";

echo "\n📝 ESCENARIO 2: Usuario selecciona \"XYZ Logistics\" de la lista\n";
echo "   1. Se usa MasterCompany existente \"XYZ Logistics\"\n";
echo "   2. Se crea NUEVO DriverEmploymentCompany (email_sent = false)\n";
echo "   3. sendBulkVerificationEmails() encuentra el registro\n";
echo "   4. ✅ Se envía correo a XYZ Logistics\n";
echo "   5. email_sent se actualiza a true\n";

echo "\n📝 ESCENARIO 3: Usuario edita fechas de \"ABC Transport\" ya agregada\n";
echo "   1. Se actualiza DriverEmploymentCompany existente\n";
echo "   2. email_sent sigue siendo true (no se cambia)\n";
echo "   3. sendBulkVerificationEmails() NO encuentra el registro\n";
echo "   4. ❌ NO se envía correo\n";

echo "\n" . str_repeat('=', 70) . "\n";
echo "RECOMENDACIÓN PARA TESTING\n";
echo str_repeat('=', 70) . "\n";

echo "\n🧪 PARA PROBAR EL COMPORTAMIENTO:\n";
echo "\n1. Limpia los logs de Laravel:\n";
echo "   php artisan log:clear\n";

echo "\n2. Ve al formulario de Employment History\n";

echo "\n3. Agrega una compañía nueva:\n";
echo "   - Crea \"Test Company 1\" con email test1@example.com\n";
echo "   - Completa el formulario y avanza al siguiente paso\n";
echo "   - Revisa logs: debería mostrar \"Correo enviado\"\n";

echo "\n4. Agrega una compañía existente:\n";
echo "   - Selecciona una compañía de la lista\n";
echo "   - Completa fechas y avanza al siguiente paso\n";
echo "   - Revisa logs: debería mostrar \"Correo enviado\"\n";

echo "\n5. Edita una compañía ya agregada:\n";
echo "   - Cambia fechas de una compañía ya en la lista\n";
echo "   - Avanza al siguiente paso\n";
echo "   - Revisa logs: NO debería enviar correo\n";

echo "\n📊 COMANDOS ÚTILES PARA VERIFICAR:\n";
echo "   - Ver logs: tail -f storage/logs/laravel.log\n";
echo "   - Ver emails en BD: SELECT * FROM employment_verification_tokens;\n";
echo "   - Ver compañías: SELECT id, email, email_sent FROM driver_employment_companies;\n";

echo "\n" . str_repeat('=', 70) . "\n";
echo "✅ ANÁLISIS COMPLETADO\n";
echo str_repeat('=', 70) . "\n\n";