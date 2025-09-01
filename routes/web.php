<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\VehicleVerificationController;
use App\Http\Controllers\EmploymentVerificationController;
use App\Http\Controllers\Admin\NotificationRecipientsController;

// Rutas públicas (sin autenticación)
/*
Route::middleware('guest')->group(function () {
    Route::get('/register', [CustomLoginController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [CustomLoginController::class, 'register']);
});
*/


Route::redirect('/user-carrier/register', '/carrier/register');
// Ruta de confirmación
Route::get('/confirm/{token}', [CustomLoginController::class, 'confirmEmail'])->name('confirm');

// Rutas que requieren autenticación pero NO son de carrier (estas deben ir en carrier.php)
Route::middleware(['auth'])->group(function () {
    // Aquí solo rutas generales autenticadas que no sean de carrier
});

// Rutas para verificación de vehículos de terceros (sin autenticación)
Route::prefix('vehicle-verification')->name('vehicle.verification.')->group(function () {
    // Mostrar formulario de verificación
    Route::get('/{token}', [VehicleVerificationController::class, 'showVerificationForm'])
        ->name('form');
    
    // Procesar la verificación
    Route::post('/{token}/process', [VehicleVerificationController::class, 'processVerification'])
        ->name('process');
    
    // Página de agradecimiento
    Route::get('/{token}/thank-you', [VehicleVerificationController::class, 'showThankYou'])
        ->name('thank-you');
});

// Rutas para verificación de empleo (sin autenticación)
Route::prefix('employment-verification')->name('employment-verification.')->group(function () {
    // IMPORTANTE: Las rutas específicas deben ir ANTES de las rutas con parámetros
    
    // Página de agradecimiento
    Route::get('/thank-you', [EmploymentVerificationController::class, 'thankYou'])
        ->name('thank-you');
    
    // Página de token expirado
    Route::get('/expired', [EmploymentVerificationController::class, 'expired'])
        ->name('expired');
    
    // Página de error
    Route::get('/error', [EmploymentVerificationController::class, 'error'])
        ->name('error');
    
    // Mostrar formulario de verificación (debe ir después de las rutas específicas)
    Route::get('/{token}', [EmploymentVerificationController::class, 'showVerificationForm'])
        ->name('form');
    
    // Procesar la verificación
    Route::post('/{token}/process', [EmploymentVerificationController::class, 'processVerification'])
        ->name('process');
});

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Ruta temporal para debug del calendario
Route::get('/debug-calendar', function () {
    // Llamar directamente al método calendar del controlador
    $controller = new \App\Http\Controllers\Admin\Vehicles\MaintenanceController();
    
    try {
        // Simular exactamente lo que hace el método calendar
        $maintenances = \App\Models\Admin\Vehicle\VehicleMaintenance::with('vehicle')
            ->orderBy('next_service_date', 'asc')
            ->get();

        // Format data for calendar events usando el mismo código del controlador
        $events = $maintenances->map(function ($maintenance) use ($controller) {
            // Usar reflexión para acceder al método privado getStatusColor
            $reflection = new \ReflectionClass($controller);
            $method = $reflection->getMethod('getStatusColor');
            $method->setAccessible(true);
            
            return [
                'id' => $maintenance->id,
                'title' => $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model . ' - ' . $maintenance->service_tasks,
                'start' => $maintenance->next_service_date->format('Y-m-d'),
                'end' => $maintenance->next_service_date->format('Y-m-d'),
                'backgroundColor' => $method->invoke($controller, $maintenance->status),
                'borderColor' => $method->invoke($controller, $maintenance->status),
                'url' => route('admin.maintenance.edit', $maintenance->id),
                'extendedProps' => [
                    'status' => $maintenance->status ? 'completed' : 'pending',
                    'vehicle' => $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model,
                    'type' => $maintenance->service_tasks,
                    'cost' => $maintenance->cost
                ]
            ];
        });
        
        return response()->json([
            'success' => true,
            'total_maintenances' => $maintenances->count(),
            'events_formatted' => $events,
            'raw_maintenances' => $maintenances->toArray(),
            'calendar_method_works' => true
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Ruta personalizada para cierre de sesión
Route::post('/custom-logout', [LogoutController::class, 'logout'])->name('custom.logout');

// Rutas de administración para destinatarios de notificaciones
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->group(function () {
    Route::get('/notification-recipients', [NotificationRecipientsController::class, 'index'])->name('admin.notification-recipients.index');
    Route::post('/notification-recipients', [NotificationRecipientsController::class, 'store'])->name('admin.notification-recipients.store');
    Route::delete('/notification-recipients/{recipient}', [NotificationRecipientsController::class, 'destroy'])->name('admin.notification-recipients.destroy');
    Route::patch('/notification-recipients/{recipient}/toggle', [NotificationRecipientsController::class, 'toggle'])->name('admin.notification-recipients.toggle');
    Route::get('/notification-recipients/users', [NotificationRecipientsController::class, 'getUsers'])->name('admin.notification-recipients.users');
});