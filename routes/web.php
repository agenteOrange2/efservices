<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomLoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\VehicleVerificationController;
use App\Http\Controllers\EmploymentVerificationController;
use App\Http\Controllers\Admin\NotificationRecipientsController;
use App\Http\Controllers\Admin\Driver\DriverLicensesController as AdminDriverLicensesController;

// Ruta temporal para debug de endorsements (sin autenticación)
Route::get('/debug-license/{license}', function($licenseId) {
    $license = \App\Models\DriverLicense::with('driverDetail.carrier')->find($licenseId);
    
    if (!$license) {
        return response()->json(['error' => 'License not found'], 404);
    }
    
    return response()->json([
        'license_id' => $license->id,
        'is_cdl' => $license->is_cdl,
        'is_cdl_type' => gettype($license->is_cdl),
        'is_cdl_raw' => $license->getRawOriginal('is_cdl'),
        'endorsements' => [
            'endorsement_n' => [
                'value' => $license->endorsement_n,
                'type' => gettype($license->endorsement_n),
                'raw' => $license->getRawOriginal('endorsement_n')
            ],
            'endorsement_h' => [
                'value' => $license->endorsement_h,
                'type' => gettype($license->endorsement_h),
                'raw' => $license->getRawOriginal('endorsement_h')
            ],
            'endorsement_x' => [
                'value' => $license->endorsement_x,
                'type' => gettype($license->endorsement_x),
                'raw' => $license->getRawOriginal('endorsement_x')
            ],
            'endorsement_t' => [
                'value' => $license->endorsement_t,
                'type' => gettype($license->endorsement_t),
                'raw' => $license->getRawOriginal('endorsement_t')
            ],
            'endorsement_p' => [
                'value' => $license->endorsement_p,
                'type' => gettype($license->endorsement_p),
                'raw' => $license->getRawOriginal('endorsement_p')
            ],
            'endorsement_s' => [
                'value' => $license->endorsement_s,
                'type' => gettype($license->endorsement_s),
                'raw' => $license->getRawOriginal('endorsement_s')
            ]
        ],
        'old_simulation' => [
            'is_cdl' => old('is_cdl', $license->is_cdl),
            'endorsement_n' => old('endorsement_n', $license->endorsement_n),
            'endorsement_h' => old('endorsement_h', $license->endorsement_h),
            'endorsement_x' => old('endorsement_x', $license->endorsement_x),
            'endorsement_t' => old('endorsement_t', $license->endorsement_t),
            'endorsement_p' => old('endorsement_p', $license->endorsement_p),
            'endorsement_s' => old('endorsement_s', $license->endorsement_s)
        ]
    ]);
});

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

// Ruta dashboard genérica que redirige según el rol del usuario
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if (!$user) {
        return redirect()->route('login');
    }
    
    // Redirigir según el rol del usuario
    if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }
    
    if ($user->hasRole('user_carrier')) {
        return redirect()->route('carrier.dashboard');
    }
    
    if ($user->hasRole('user_driver')) {
        return redirect()->route('driver.dashboard');
    }
    
    // Por defecto, redirigir al login
    return redirect()->route('login');
})->middleware('auth')->name('dashboard');

// Debug route for calendar
Route::get('/debug-calendar', function() {
    return view('debug.calendar');
});

// Test route for driver form
Route::get('/test-driver-form', function () {
    return view('test-driver-form');
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