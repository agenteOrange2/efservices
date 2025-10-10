<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\FileUploaderController;
use App\Http\Controllers\Admin\CarrierController;
use App\Http\Controllers\Admin\DriversController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\TempUploadController;
use App\Http\Controllers\Admin\UserDriverController;
use App\Http\Controllers\Admin\UserCarrierController;
use App\Http\Controllers\Api\UserDriverApiController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\Driver\CoursesController;
use App\Http\Controllers\Admin\CarrierDocumentController;
use App\Http\Controllers\Admin\Driver\TestingsController;
use App\Http\Controllers\Admin\Driver\AccidentsController;
use App\Http\Controllers\Admin\Driver\DocumentsController;
use App\Http\Controllers\Admin\Vehicles\VehicleController;
use App\Http\Controllers\Admin\Driver\DriverListController;
use App\Http\Controllers\Admin\Driver\InspectionsController;
use App\Http\Controllers\Admin\TrainingAssignmentsController;
use App\Http\Controllers\Admin\UserCarrierDocumentController;
use App\Http\Controllers\Admin\Driver\DriverTestingController;
use App\Http\Controllers\Admin\Vehicles\MaintenanceController;
use App\Http\Controllers\Admin\Vehicles\VehicleMakeController;
use App\Http\Controllers\Admin\Vehicles\VehicleTypeController;
use App\Http\Controllers\Admin\Vehicles\MaintenanceReportController;
use App\Http\Controllers\Admin\Vehicles\MaintenanceCalendarController;
use App\Http\Controllers\Admin\Driver\TrainingSchoolsController;
use App\Http\Controllers\Admin\Driver\DriverLicensesController;
use App\Http\Controllers\Admin\Driver\MedicalRecordsController;
use App\Http\Controllers\Admin\Driver\DriverRecruitmentController;
use App\Http\Controllers\Admin\Vehicles\VehicleDocumentController;
use App\Http\Controllers\Admin\Driver\TrafficConvictionsController;
use App\Http\Controllers\Admin\Vehicles\VehicleMaintenanceController;
use App\Http\Controllers\Admin\Vehicles\EmergencyRepairController;
use App\Http\Controllers\Admin\Vehicles\MaintenanceNotificationController;
use App\Http\Controllers\Admin\Driver\EmploymentVerificationAdminController;
use App\Http\Controllers\Admin\MasterCompanyController;


Route::get('theme-switcher/{activeTheme}', [ThemeController::class, 'switch'])->name('theme-switcher');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/dashboard/export-pdf', [DashboardController::class, 'exportPdf'])->middleware('api.rate.limit:admin_reports,30,1')->name('dashboard.export-pdf');
Route::post('/dashboard/ajax-update', [DashboardController::class, 'ajaxUpdate'])->middleware('api.rate.limit:admin_ajax,120,1')->name('dashboard.ajax-update');

// Dashboard principal
// Aquí solo mantenemos las rutas del dashboard principal

/*
    |--------------------------------------------------------------------------
    | RUTAS PARA GESTIÓN DE VEHÍCULOS Y MANTENIMIENTO
    |--------------------------------------------------------------------------    
*/

/*
|--------------------------------------------------------------------------
| RUTAS PARA GESTIÓN DE VEHÍCULOS Y MANTENIMIENTO
|--------------------------------------------------------------------------
*/

// 1. Rutas específicas de vehículos (DEBEN IR ANTES DEL RESOURCE)
// Ruta para API de obtener conductores por carrier (utilizada en create y edit)
Route::get('vehicles/drivers-by-carrier/{carrierId}', [VehicleController::class, 'getDriversByCarrier']);

// Rutas para reparaciones de emergencia (DEBEN IR ANTES DEL RESOURCE)
Route::prefix('vehicles')->name('vehicles.')->group(function () {
    Route::resource('emergency-repairs', EmergencyRepairController::class);
    Route::get('emergency-repairs/vehicles-by-carrier/{carrierId}', [EmergencyRepairController::class, 'getVehiclesByCarrier'])->name('emergency-repairs.vehicles-by-carrier');
    Route::get('emergency-repairs/drivers-by-carrier/{carrierId}', [EmergencyRepairController::class, 'getDriversByCarrier'])->name('emergency-repairs.drivers-by-carrier');
    Route::delete('emergency-repairs/{emergencyRepair}/files/{mediaId}', [EmergencyRepairController::class, 'deleteFile'])->name('emergency-repairs.delete-file');
});

// 2. Rutas básicas para vehículos (RESOURCE AL FINAL)
Route::resource('vehicles', VehicleController::class);

// Rutas AJAX para crear makes y types dinámicamente
Route::post('vehicles/create-make', [VehicleController::class, 'createMake'])->name('vehicles.create-make');
Route::post('vehicles/create-type', [VehicleController::class, 'createType'])->name('vehicles.create-type');

// Ruta para vehículos sin asignar
Route::get('vehicles/unassigned', [VehicleController::class, 'unassignedVehicles'])->name('vehicles.unassigned');

// Ruta para asignación de tipo de conductor
Route::get('vehicles/{vehicle}/assign-driver-type', [VehicleController::class, 'assignDriverType'])->name('vehicles.assign-driver-type');
Route::post('vehicles/{vehicle}/assign-driver-type', [VehicleController::class, 'storeDriverType'])->name('vehicles.store-driver-type');

// New routes for driver selection
Route::get('vehicles/{vehicle}/select-owner-operator', [VehicleController::class, 'selectOwnerOperator'])->name('vehicles.select-owner-operator');
Route::get('vehicles/{vehicle}/select-third-party', [VehicleController::class, 'selectThirdParty'])->name('vehicles.select-third-party');
Route::post('vehicles/assign-to-driver', [VehicleController::class, 'assignToDriver'])->name('vehicles.assign-to-driver');

// New decoupled vehicle driver assignment routes
Route::post('vehicles/{vehicle}/assign-driver', [VehicleController::class, 'assignDriver'])->name('vehicles.assign-driver');
Route::delete('vehicles/{vehicle}/remove-driver/{assignmentId}', [VehicleController::class, 'removeDriver'])->name('vehicles.remove-driver');

// Direct routes to VehicleDriverAssignmentController
Route::prefix('vehicle-driver-assignments')->name('vehicle-driver-assignments.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\VehicleDriverAssignmentController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Admin\VehicleDriverAssignmentController::class, 'store'])->name('store');
    Route::get('/{assignment}', [\App\Http\Controllers\Admin\VehicleDriverAssignmentController::class, 'show'])->name('show');
    Route::put('/{assignment}', [\App\Http\Controllers\Admin\VehicleDriverAssignmentController::class, 'update'])->name('update');
    Route::delete('/{assignment}', [\App\Http\Controllers\Admin\VehicleDriverAssignmentController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-terminate', [\App\Http\Controllers\Admin\VehicleDriverAssignmentController::class, 'bulkTerminate'])->name('bulk-terminate');
    Route::get('/history/{vehicle}', [\App\Http\Controllers\Admin\VehicleDriverAssignmentController::class, 'history'])->name('history');
    Route::get('/user/{user}', [\App\Http\Controllers\Admin\VehicleDriverAssignmentController::class, 'userAssignments'])->name('user-assignments');
});

/*
    |--------------------------------------------------------------------------
    | RUTAS PARA GESTIÓN DE DRIVER TYPES (CRUD)
    |--------------------------------------------------------------------------    
*/

// Rutas para gestión de Driver Types (sin create - solo listado y edición)
Route::prefix('driver-types')->name('driver-types.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DriverTypeController::class, 'index'])->name('index');
    Route::get('/data', [\App\Http\Controllers\Admin\DriverTypeController::class, 'getData'])->name('data');
    Route::get('/{driverApplication}/edit', [\App\Http\Controllers\Admin\DriverTypeController::class, 'edit'])->name('edit');
    Route::put('/{driverApplication}', [\App\Http\Controllers\Admin\DriverTypeController::class, 'update'])->name('update');
    Route::delete('/{driverApplication}', [\App\Http\Controllers\Admin\DriverTypeController::class, 'destroy'])->name('destroy');
    Route::get('/{driverApplication}', [\App\Http\Controllers\Admin\DriverTypeController::class, 'show'])->name('show');
});

// 2. Rutas para maintenances como recurso anidado
Route::resource('vehicles.maintenances', VehicleMaintenanceController::class);

// 3. Rutas especiales formato maintenances
Route::put('vehicles/{vehicle}/maintenances/{serviceItemId}/toggle-status', 
    [VehicleMaintenanceController::class, 'toggleStatus'])
    ->name('vehicles.maintenances.toggle-status');

Route::delete('vehicles/{vehicle}/maintenances/{serviceItemId}/files/{mediaId}', 
    [VehicleMaintenanceController::class, 'deleteFile'])
    ->name('vehicles.maintenances.delete-file');

// 4. Rutas especiales formato vehicle-maintenances
Route::put('vehicles/{vehicle}/vehicle-maintenances/{serviceItemId}/toggle-status', 
    [VehicleMaintenanceController::class, 'toggleStatus'])
    ->name('vehicles.vehicle-maintenances.toggle-status');

Route::delete('vehicles/{vehicle}/vehicle-maintenances/{serviceItemId}/files/{mediaId}', 
    [VehicleMaintenanceController::class, 'deleteFile'])
    ->name('vehicles.vehicle-maintenances.delete-file');

// 5. Rutas para mantenimientos centralizados (COMENTADO - CONFLICTO CON MAINTENANCE CONTROLLER)
// Route::group(['prefix' => 'maintenance'], function () {
//     Route::get('/', [VehicleMaintenanceController::class, 'index'])->name('maintenance.index');
//     Route::get('/{serviceItem}', [VehicleMaintenanceController::class, 'show'])->name('maintenance.show');
// });



// Rutas para tipos y marcas de vehículos
Route::resource('vehicle-types', VehicleTypeController::class);
Route::resource('vehicle-makes', VehicleMakeController::class);

// Rutas para mantenimiento de vehículos
Route::prefix('maintenance')->name('maintenance.')->group(function () {
    // Rutas principales CRUD
    Route::get('/', [MaintenanceController::class, 'index'])->name('index');
    Route::get('/create', [MaintenanceController::class, 'create'])->name('create');
    Route::post('/', [MaintenanceController::class, 'store'])->name('store');
    
    // Exportar PDF de mantenimiento (DEBE IR ANTES DE LAS RUTAS CON PARÁMETROS)
    Route::post('/export-pdf', [MaintenanceController::class, 'exportPdf'])->name('export-pdf');
    
    // Calendario (DEBE IR ANTES DE LAS RUTAS CON PARÁMETROS)
    Route::get('/calendar', [MaintenanceController::class, 'calendar'])->name('calendar');
    Route::get('/calendar/events', [MaintenanceController::class, 'getEvents'])->name('calendar.events');
    
    // Reportes de mantenimiento (DEBE IR ANTES DE LAS RUTAS CON PARÁMETROS)
    Route::get('/reports', [MaintenanceController::class, 'reports'])->name('reports');
    
    // Rutas con parámetros (DEBEN IR AL FINAL)
    Route::get('/{maintenance}', [MaintenanceController::class, 'show'])->name('show');
    Route::get('/{maintenance}/edit', [MaintenanceController::class, 'edit'])->name('edit');
    Route::put('/{maintenance}', [MaintenanceController::class, 'update'])->name('update');
    Route::delete('/{maintenance}', [MaintenanceController::class, 'destroy'])->name('destroy');
    Route::post('/{maintenance}/reschedule', [MaintenanceController::class, 'reschedule'])->name('reschedule');
});


/*
    |--------------------------------------------------------------------------
    | MASTER COMPANIES MANAGEMENT
    |--------------------------------------------------------------------------    
*/

Route::resource('companies', MasterCompanyController::class);

/*
    |--------------------------------------------------------------------------
    | RUTAS API ADMIN PARA AJAX
    |--------------------------------------------------------------------------    
*/

// API para obtener conductores activos por carrier (para Ajax)
Route::get('/api/drivers/by-carrier/{carrier}', [AccidentsController::class, 'getDriversByCarrier'])->middleware('api.rate.limit:admin_api,60,1')->name('api.drivers.by-carrier');

// Ruta para eliminar documentos de traffic convictions (usada por el formulario)
Route::delete('traffic/documents/{document}', [TrafficConvictionsController::class, 'destroyDocument'])->name('traffic.doc.delete');

// Ruta para eliminar documentos de training schools (usada por el formulario)
Route::delete('training-schools/documents/{document}', [TrainingSchoolsController::class, 'destroyDocument'])->name('training-schools.documents.delete');

// Ruta para eliminar documentos de licenses (usada por el formulario)
Route::delete('licenses/documents/{document}', [DriverLicensesController::class, 'destroyDocument'])->name('licenses.documents.delete');

// Ruta para cargas temporales de archivos
Route::post('/upload-temp', function (\Illuminate\Http\Request $request) {
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $path = $file->store('temp', 'public');
        return response()->json([
            'success' => true,
            'path' => $path
        ]);
    }
    return response()->json([
        'success' => false,
        'message' => 'No file uploaded'
    ]);
});


// Rutas para gestión de documentos de accidentes
Route::prefix('accidents')->name('accidents.')->group(function () {
    // Rutas para todos los documentos de accidentes
    Route::get('documents', [AccidentsController::class, 'documents'])->name('documents.index');

    // Rutas para documentos de un accidente específico
    Route::get('{accident}/documents', [AccidentsController::class, 'showDocuments'])->name('documents.show');
    Route::post('{accident}/documents', [AccidentsController::class, 'storeDocuments'])->name('documents.store');

    // Rutas para operaciones con documentos individuales
    Route::delete('documents/{document}', [AccidentsController::class, 'destroyDocument'])->name('documents.destroy');
    Route::delete('document/{document}', [AccidentsController::class, 'destroyDocument'])->name('document.destroy.alt'); // Nombre único para compatibilidad
    Route::get('documents/{document}/preview', [AccidentsController::class, 'previewDocument'])->name('document.preview');
    Route::get('document/{document}/preview', [AccidentsController::class, 'previewDocument'])->name('document.preview.alt'); // Nombre único para compatibilidad
    Route::get('document/{document}/show', [AccidentsController::class, 'previewDocument'])->name('document.show.alt'); // Nombre único para compatibilidad
    Route::post('documents/ajax-delete', [AccidentsController::class, 'ajaxDestroyDocument'])->name('documents.ajax-destroy');
    Route::delete('ajax-destroy-media/{media}', [AccidentsController::class, 'ajaxDestroyMedia'])->name('ajax-destroy-media');
});

// Rutas para gestión de documentos de infracciones de tráfico
Route::prefix('traffic')->name('traffic.')->group(function () {
    Route::post('{conviction}/documents', [TrafficConvictionsController::class, 'storeDocuments'])->name('docs.store');
    Route::delete('documents/{media}', [TrafficConvictionsController::class, 'destroyDocument'])->name('docs.destroy');
    Route::get('documents/{media}/preview', [TrafficConvictionsController::class, 'previewDocument'])->name('docs.preview');
    Route::delete('ajax-destroy-document/{media}', [TrafficConvictionsController::class, 'ajaxDestroyDocument'])->name('ajax-destroy-document');
});

// Rutas para gestión de documentos de pruebas de conductores
Route::prefix('testings')->name('testings.')->group(function () {
    Route::get('{testing}/documents', [TestingsController::class, 'documents'])->name('docs');
    Route::post('{testing}/documents', [TestingsController::class, 'storeDocuments'])->name('docs.store');
    Route::delete('documents/{media}', [TestingsController::class, 'destroyDocument'])->name('docs.destroy');
    Route::get('documents/{media}/preview', [TestingsController::class, 'previewDocument'])->name('docs.preview');
});

// Rutas para el nuevo sistema de documentos
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('{document}/preview', [\App\Http\Controllers\DocumentAttachmentController::class, 'preview'])->name('preview');
    Route::delete('{document}', [\App\Http\Controllers\DocumentAttachmentController::class, 'destroy'])->name('destroy');
});

Route::prefix('maintenance-notifications')->name('maintenance-notifications.')->group(function () {
    Route::post('/send-test', [MaintenanceNotificationController::class, 'sendTestNotification'])->name('send-test');
    Route::post('/send-to-all', [MaintenanceNotificationController::class, 'sendNotificationsToAll'])->name('send-to-all');
    Route::post('/mark-as-read/{notificationId}', [MaintenanceNotificationController::class, 'markAsRead'])->name('mark-as-read');
    Route::post('/maintenance-documents/ajax-delete/{document}', [MaintenanceController::class, 'ajaxDeleteDocument'])->name('maintenance-documents.ajax-delete');
});

/*
    |--------------------------------------------------------------------------
    | RUTAS PARA ESCUELAS DE ENTRENAMIENTO DE CONDUCTORES
    |--------------------------------------------------------------------------    
*/

// Rutas estándar de recursos para escuelas de entrenamiento
Route::resource('training-schools', TrainingSchoolsController::class);

/*
    |--------------------------------------------------------------------------
    | RUTAS PARA LICENCIAS DE CONDUCTORES
    |--------------------------------------------------------------------------    
*/

// Rutas estándar de recursos para licencias
Route::resource('licenses', DriverLicensesController::class);

// Ruta temporal para debug de endorsements
Route::get('licenses/{license}/debug-endorsements', [DriverLicensesController::class, 'debugEndorsements'])->name('licenses.debug-endorsements');

/*
    |--------------------------------------------------------------------------
    | RUTAS PARA REGISTROS MÉDICOS DE CONDUCTORES
    |--------------------------------------------------------------------------    
*/

// Rutas estándar de recursos para registros médicos
Route::resource('medical-records', MedicalRecordsController::class);

// Rutas para el nuevo módulo de entrenamientos
Route::resource('trainings', \App\Http\Controllers\Admin\TrainingsController::class);

// Ruta directa para seleccionar entrenamientos para asignar
Route::get('/select-training-for-assignment', [\App\Http\Controllers\Admin\TrainingsController::class, 'assignSelect'])->name('select-training');



// Rutas para asignaciones de entrenamientos
Route::prefix('trainings')->group(function () {
    // Asignación de entrenamientos (usar TrainingAssignmentsController para la lógica de asignación)
    Route::get('/{training}/assign', [\App\Http\Controllers\Admin\TrainingAssignmentsController::class, 'showAssignForm'])->name('trainings.assign.form');
    Route::post('/{training}/assign', [\App\Http\Controllers\Admin\TrainingAssignmentsController::class, 'assign'])->name('trainings.assign');

    // API para obtener conductores filtrados por transportista
    Route::get('carrier/{carrier}/drivers', [\App\Http\Controllers\Admin\TrainingAssignmentsController::class, 'getDrivers'])->name('trainings.drivers.by.carrier');

    // Rutas para gestión de documentos de entrenamientos (permanecen en TrainingsController)
    Route::delete('/documents/{document}', [\App\Http\Controllers\Admin\TrainingsController::class, 'destroyDocument'])->name('trainings.documents.delete');
    Route::get('/documents/{document}/preview', [\App\Http\Controllers\Admin\TrainingsController::class, 'previewDocument'])->name('trainings.preview-document');
});

// Rutas para gestión de asignaciones de entrenamientos
Route::resource('training-assignments', \App\Http\Controllers\Admin\TrainingAssignmentsController::class);

// Rutas adicionales para asignaciones de entrenamientos
Route::prefix('training-assignments')->group(function () {
    Route::post('/{assignment}/mark-complete', [\App\Http\Controllers\Admin\TrainingAssignmentsController::class, 'markComplete'])->name('training-assignments.mark-complete');
    Route::get('/get-drivers/{carrier}', [\App\Http\Controllers\Admin\TrainingAssignmentsController::class, 'getDrivers'])->name('training-assignments.get-drivers');
});

/*
    |--------------------------------------------------------------------------
    | RUTAS PARA GESTION DE DOCUMENTOS DE ESCUELA DE ENTRENAMIENTO
    |--------------------------------------------------------------------------    
*/

Route::prefix('training-schools')->name('training-schools.')->group(function () {
    // Vista de todos los documentos
    Route::get('all/documents', [TrainingSchoolsController::class, 'documents'])->name('docs.all');

    // Rutas para documentos de una escuela específica
    Route::get('{school}/documents', [TrainingSchoolsController::class, 'showDocuments'])->name('docs.show');

    // Rutas para operaciones con documentos individuales
    Route::get('document/{id}/preview', [TrainingSchoolsController::class, 'previewDocument'])->name('doc.preview');
    // Rutas adicionales con los nombres que se usan en las vistas para compatibilidad
    Route::get('documents/{id}/preview', [TrainingSchoolsController::class, 'previewDocument'])->name('docs.preview');
    Route::delete('documents/{id}', [TrainingSchoolsController::class, 'destroyDocument'])->name('docs.delete');

    // Rutas originales - renombradas para evitar duplicados
    Route::delete('document/{id}', [TrainingSchoolsController::class, 'destroyDocument'])->name('doc.delete');
    Route::delete('document/{id}/ajax', [TrainingSchoolsController::class, 'ajaxDestroyDocument'])->name('doc.ajax-delete');
});

/*
    |--------------------------------------------------------------------------
    | RUTAS PARA GESTION DE DOCUMENTOS DE LICENCIAS
    |--------------------------------------------------------------------------    
*/

Route::prefix('licenses')->name('licenses.')->group(function () {
    // Vista de todos los documentos
    Route::get('all/documents', [DriverLicensesController::class, 'documents'])->name('docs.all');

    // Rutas para documentos de una licencia específica
    Route::get('{license}/documents', [DriverLicensesController::class, 'showDocuments'])->name('docs.show');
    Route::post('{license}/upload-documents', [DriverLicensesController::class, 'uploadDocument'])->name('upload.documents');

    // Rutas para operaciones con documentos individuales
    Route::get('document/{id}/preview', [DriverLicensesController::class, 'previewDocument'])->name('doc.preview');
    // Rutas adicionales con los nombres que se usan en las vistas para compatibilidad
    Route::get('documents/{id}/preview', [DriverLicensesController::class, 'previewDocument'])->name('docs.preview');
    Route::delete('documents/{id}', [DriverLicensesController::class, 'destroyDocument'])->name('docs.delete');

    // Rutas originales - renombradas para evitar duplicados
    Route::delete('document/{id}', [DriverLicensesController::class, 'destroyDocument'])->name('doc.delete');
    Route::delete('document/{id}/ajax', [DriverLicensesController::class, 'ajaxDestroyDocument'])->name('doc.ajax-delete');

    // Ruta para obtener conductores por transportista
    Route::get('carrier/{carrier}/drivers', [DriverLicensesController::class, 'getDriversByCarrier'])->name('drivers.by.carrier');
});

/*
    |--------------------------------------------------------------------------
    | RUTAS PARA GESTION DE DOCUMENTOS DE REGISTROS MÉDICOS
    |--------------------------------------------------------------------------    
*/

Route::prefix('medical-records')->name('medical-records.')->group(function () {
    // Vista de todos los documentos
    Route::get('all/documents', [MedicalRecordsController::class, 'documents'])->name('docs.all');

    // Rutas para documentos de un registro médico específico
    Route::get('{medicalRecord}/documents', [MedicalRecordsController::class, 'showDocuments'])->name('docs.show');
    Route::post('{medicalRecord}/upload-documents', [MedicalRecordsController::class, 'uploadDocument'])->name('upload.documents');

    // Rutas para operaciones con documentos individuales
    Route::get('document/{id}/preview', [MedicalRecordsController::class, 'previewDocument'])->name('doc.preview');
    // Rutas adicionales con los nombres que se usan en las vistas para compatibilidad
    Route::get('documents/{id}/preview', [MedicalRecordsController::class, 'previewDocument'])->name('docs.preview');
    Route::delete('documents/{id}', [MedicalRecordsController::class, 'destroyDocument'])->name('docs.delete');

    // Rutas originales - renombradas para evitar duplicados
    Route::delete('document/{id}', [MedicalRecordsController::class, 'destroyDocument'])->name('doc.delete');
    Route::delete('document/{id}/ajax', [MedicalRecordsController::class, 'ajaxDestroyDocument'])->name('doc.ajax-delete');

    // Ruta para obtener conductores por transportista
    Route::get('carrier/{carrier}/drivers', [MedicalRecordsController::class, 'getDriversByCarrier'])->name('drivers.by.carrier');
    
    // Ruta para eliminar medical card
    Route::delete('{medicalRecord}/delete-medical-card', [MedicalRecordsController::class, 'deleteMedicalCard'])->name('delete-medical-card');
});

/*
    |--------------------------------------------------------------------------
    | RUTAS PARA VERIFICACIÓN DE EMPLEO
    |--------------------------------------------------------------------------    
*/

// Rutas para la verificación de empleo
Route::prefix('drivers/employment-verification')->name('drivers.employment-verification.')->group(function () {
    Route::get('/', [EmploymentVerificationAdminController::class, 'index'])->name('index');
    Route::get('/new', [EmploymentVerificationAdminController::class, 'createNew'])->name('new');
    Route::get('/{id}', [EmploymentVerificationAdminController::class, 'show'])->name('show');
    Route::post('/{id}/resend', [EmploymentVerificationAdminController::class, 'resendVerification'])->name('resend');
    Route::post('/{id}/verify', [EmploymentVerificationAdminController::class, 'verify'])->name('verify');
    Route::post('/{id}/reject', [EmploymentVerificationAdminController::class, 'reject'])->name('reject');
    Route::post('/{id}/mark-verified', [EmploymentVerificationAdminController::class, 'markAsVerified'])->name('mark-verified');
    Route::post('/{id}/mark-rejected', [EmploymentVerificationAdminController::class, 'markAsRejected'])->name('mark-rejected');
    Route::post('/{id}/upload-document', [EmploymentVerificationAdminController::class, 'uploadDocument'])->name('upload-document');
    Route::post('/{id}/upload-manual-verification', [EmploymentVerificationAdminController::class, 'uploadManualVerification'])->name('upload-manual-verification');
});

/*
    |--------------------------------------------------------------------------
    | RUTAS PARA CURSOS DE CONDUCTORES
    |--------------------------------------------------------------------------    
*/

// Rutas estándar de recursos para cursos
Route::resource('courses', CoursesController::class)->except(['show']);

// Rutas para gestión de documentos de cursos
Route::prefix('courses')->name('courses.')->group(function () {
    // Vista de todos los documentos
    Route::get('all/documents', [CoursesController::class, 'getAllDocuments'])->name('all-documents');

    // Rutas para documentos de un curso específico
    Route::get('{course}/documents', [CoursesController::class, 'getFiles'])->name('documents');

    // Rutas para operaciones con documentos individuales
    Route::get('document/{id}/preview', [CoursesController::class, 'previewDocument'])->name('preview.document');
    // Rutas adicionales con los nombres que se usan en las vistas para compatibilidad
    Route::get('documents/preview/{id}', [CoursesController::class, 'previewDocument'])->name('documents.preview');
    Route::delete('document/{document}', [CoursesController::class, 'destroyDocument'])->name('document.delete');

    // Rutas para obtener conductores por transportista
    Route::get('carrier/{carrier}/drivers', [CoursesController::class, 'getDriversByCarrier'])->name('drivers.by.carrier');
});

/*
    |--------------------------------------------------------------------------
    | RUTAS ADMIN USERS
    |--------------------------------------------------------------------------    
*/

// Rutas de usuarios con middleware de permisos
Route::middleware('auth')->group(function () {
    // Rutas que requieren permiso para ver usuarios
    Route::middleware('permission:view users')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/export-excel', [UserController::class, 'exportToExcel'])->name('users.export.excel');
        Route::get('users/export-pdf', [UserController::class, 'exportToPdf'])->name('users.export.pdf');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    });

    // Rutas que requieren permiso para crear usuarios
    Route::middleware('permission:create users')->group(function () {
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
    });

    // Rutas que requieren permiso para editar usuarios
    Route::middleware('permission:edit users')->group(function () {
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}', [UserController::class, 'update']);
        Route::post('users/{user}/delete-photo', [UserController::class, 'deletePhoto'])->name('users.delete-photo');
    });

    // Rutas que requieren permiso para eliminar usuarios
    Route::middleware('permission:delete users')->group(function () {
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

/*
    |--------------------------------------------------------------------------
    | RUTAS ADMIN ROLES
    |--------------------------------------------------------------------------    
*/
// Rutas para gestión de permisos con middleware de protección
Route::middleware('auth')->group(function () {
    // Rutas para gestionar roles y permisos
    Route::middleware('permission:view roles')->group(function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });

    Route::middleware('permission:create roles')->group(function () {
        Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
        Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    });

    Route::middleware('permission:edit roles')->group(function () {
        Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::patch('permissions/{permission}', [PermissionController::class, 'update']);
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::patch('roles/{role}', [RoleController::class, 'update']);
    });

    Route::middleware('permission:delete roles')->group(function () {
        Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
});


// Route::resource('roles', RolePermissionController::class)->except(['show']);

/*
|--------------------------------------------------------------------------
| RUTAS ADMIN MEMBERSHIP
|--------------------------------------------------------------------------    
*/
Route::resource('membership', MembershipController::class);
Route::post('membership/{membership}/delete-photo', [MembershipController::class, 'deletePhoto'])->name('membership.delete-photo');

/*
    |--------------------------------------------------------------------------
    | RUTAS ADMIN CARRIER
    |--------------------------------------------------------------------------    
*/

// Gestión de Carriers

Route::resource('carrier', CarrierController::class)->names([
    'index' => 'carriers.index',
    'create' => 'carriers.create',
    'store' => 'carriers.store',
    'show' => 'carriers.show',
    'edit' => 'carriers.edit',
    'update' => 'carriers.update',
    'destroy' => 'carriers.destroy',
]);
Route::get('carrier/export-excel', [CarrierController::class, 'exportToExcel'])->name('carrier.export.excel');
Route::get('carrier/export-pdf', [CarrierController::class, 'exportToPdf'])->name('carrier.export.pdf');
Route::post('carrier/{carrier}/delete-photo', [CarrierController::class, 'deletePhoto'])->name('carrier.delete-photo');

// Gestión de Drivers
Route::resource('drivers', DriversController::class);

/*
Route::post('carrier/{carrier}/delete-photo', [CarrierController::class, 'deletePhoto'])->name('carrier.delete-photo');
*/
/*
    |----------------------------------------------------------------------
    | RUTAS ADMIN DRIVER TESTING (DRUG & ALCOHOL TESTS)
    |----------------------------------------------------------------------
*/

// Rutas para gestión de pruebas de drogas y alcohol (accesible a todos los usuarios autenticados)
Route::middleware('auth')->group(function () {
    Route::prefix('driver-testings')->name('driver-testings.')->group(function () {
        // Listado y filtro de tests
        Route::get('/', [DriverTestingController::class, 'index'])->name('index');

        // Crear y guardar tests
        Route::get('/create', [DriverTestingController::class, 'create'])->name('create');
        Route::post('/', [DriverTestingController::class, 'store'])->name('store');

        // Ver, editar y actualizar tests
        Route::get('/{driverTesting}', [DriverTestingController::class, 'show'])->name('show');
        Route::get('/{driverTesting}/edit', [DriverTestingController::class, 'edit'])->name('edit');
        Route::put('/{driverTesting}', [DriverTestingController::class, 'update'])->name('update');

        // Eliminar test
        Route::delete('/{driverTesting}', [DriverTestingController::class, 'destroy'])->name('destroy');

        // Descargar PDF del test
        Route::get('/{driverTesting}/download-pdf', [DriverTestingController::class, 'downloadPdf'])->name('download-pdf');
    });
});

/*
    |----------------------------------------------------------------------
    | RUTAS ADMIN CARRIERS (CON TABS USERS Y DOCUMENTS)
    |----------------------------------------------------------------------
*/

Route::prefix('carrier')->name('carrier.')->group(function () {
    // Mostrar detalles completos de un carrier (nueva vista)
    Route::get('{carrier:slug}/details', [CarrierController::class, 'show'])->name('show');
    
    // Mostrar usuarios asignados a un Carrier en el tab "Users"
    Route::get('{carrier}/users', [CarrierController::class, 'users'])->name('users');

    // Mostrar documentos relacionados a un Carrier en el tab "Documents"
    Route::get('{carrier}/documents', [CarrierController::class, 'documents'])->name('documents');
});

/*
    |--------------------------------------------------------------------------
    | RUTAS USER CARRIER
    |--------------------------------------------------------------------------    
*/

Route::prefix('carrier')->name('carrier.')->group(function () {
    Route::get('/', [CarrierController::class, 'index'])->name('index');
    Route::get('/create', [CarrierController::class, 'create'])->name('create');
    Route::post('/', [CarrierController::class, 'store'])->name('store');
    Route::get('/{carrier:slug}', [CarrierController::class, 'edit'])->name('edit');
    Route::put('/{carrier:slug}', [CarrierController::class, 'update'])->name('update');
    Route::delete('/{carrier:slug}', [CarrierController::class, 'destroy'])->name('destroy');

    // Ruta para gestionar documentos del carrier
    Route::get('/{carrier:slug}/documents', [CarrierController::class, 'documents'])->name('documents');
    Route::post('/{carrier:slug}/generate-missing-documents', [CarrierController::class, 'generateMissingDocuments'])->name('generate-missing-documents');
    Route::put('/document/{document}/update-status', [CarrierController::class, 'updateDocumentStatus'])->name('document.update-status');
    Route::post('/{carrier}/delete-photo', [CarrierController::class, 'deletePhoto'])->name('delete-photo');

    // Rutas anidadas para UserCarriers
    Route::prefix('{carrier:slug}/user-carriers')->name('user_carriers.')->group(function () {
        Route::get('/', [UserCarrierController::class, 'index'])->name('index'); // Listado
        Route::get('/create', [UserCarrierController::class, 'create'])->name('create'); // Formulario de creación
        Route::post('/', [UserCarrierController::class, 'store'])->name('store'); // Guardar nuevo UserCarrier           
        Route::get('/{userCarrierDetails}/edit', [UserCarrierController::class, 'edit'])->name('edit');
        Route::put('/{userCarrierDetails}', [UserCarrierController::class, 'update'])->name('update');
        Route::delete('/{userCarrier}', [UserCarrierController::class, 'destroy'])->name('destroy'); // Eliminar UserCarrier

        // Ruta para eliminar la foto de perfil del UserCarrier
        Route::post('/{userCarrierDetails}/delete-photo', [UserCarrierController::class, 'deletePhoto'])
            ->name('delete-photo');
    });
});

/*
|--------------------------------------------------------------------------
| RUTAS PARA GESTIONAR INFORMACIÓN BANCARIA DE CARRIERS
|--------------------------------------------------------------------------
*/
Route::post('/carrier/{carrier:slug}/banking/store', [CarrierController::class, 'storeBanking'])->name('carrier.banking.store');
Route::post('/carrier/{carrier:slug}/banking/approve', [CarrierController::class, 'approveBanking'])->name('carrier.banking.approve');
Route::post('/carrier/{carrier:slug}/banking/reject', [CarrierController::class, 'rejectBanking'])->name('carrier.banking.reject');
Route::put('/carrier/{carrier:slug}/banking/update', [CarrierController::class, 'updateBanking'])->name('carrier.banking.update');

/*
|--------------------------------------------------------------------------
| RUTAS PARA SUPERADMIN: ADMIN DRIVERS
|--------------------------------------------------------------------------
*/

// En el grupo existente de user_drivers


// En routes/web.php o admin.php (donde tengas las rutas web)
Route::prefix('carrier/{carrier}/drivers')->name('carrier.user_drivers.')->group(function () {
    Route::get('/', [UserDriverController::class, 'index'])->name('index');
    Route::get('/create', [UserDriverController::class, 'create'])->name('create');
    // Route::post('/', [UserDriverController::class, 'store'])->name('store');
    Route::get('/{userDriverDetail}/edit', [UserDriverController::class, 'edit'])->name('edit');
    // Route::put('/{userDriverDetail}', [UserDriverController::class, 'update'])->name('update');
    Route::delete('/{userDriverDetail}', [UserDriverController::class, 'destroy'])->name('destroy');
    Route::delete('/{userDriverDetail}/photo', [UserDriverController::class, 'deletePhoto'])->name('delete-photo');
});

Route::post('carrier/{carrier}/drivers/autosave/{userDriverDetail?}', [
    UserDriverApiController::class,
    'autosave'
])->name('carrier.user_drivers.autosave');

Route::post('/temp-upload', [TempUploadController::class, 'upload'])->name('temp.upload');


/*
|--------------------------------------------------------------------------
| RUTAS PARA SUPERADMIN: ADMIN DOCUMENTS
|--------------------------------------------------------------------------
*/


// Listado de todos los carriers con estado de archivos
Route::get('carriers-documents', [CarrierDocumentController::class, 'listCarriersForDocuments'])
    ->name('admin_documents.list');

// Ver los documentos subidos por un carrier específico
Route::prefix('carrier/{carrier:slug}')->name('carrier.')->group(function () {
    Route::get('admin-documents', [CarrierDocumentController::class, 'reviewDocuments'])
        ->name('admin_documents.review');
});

Route::post('carrier/{carrier:slug}/admin-documents/upload/{documentType}', [CarrierDocumentController::class, 'upload'])
    ->name('carrier.admin_documents.upload');

/*
|--------------------------------------------------------------------------
| RUTAS PARA USUARIOS: USER DOCUMENTS
|--------------------------------------------------------------------------
*/
Route::prefix('carrier/{carrier:slug}')->name('carrier.user_documents.')->group(function () {
    Route::get('user-documents', [UserCarrierDocumentController::class, 'index'])->name('index');
    Route::post('user-documents/upload/{documentType}', [UserCarrierDocumentController::class, 'upload'])
        ->name('upload');
});

/*
|--------------------------------------------------------------------------
| RUTAS ADMIN DOCUMENTS (CRUD)
|--------------------------------------------------------------------------
*/
Route::resource('carriers.documents', CarrierDocumentController::class)
    ->parameters(['documents' => 'document'])->except('show');


Route::post('/carrier/{carrier}/document/{document}/approve', [CarrierDocumentController::class, 'approveDefaultDocument'])
    ->name('carrier.approve_document');
Route::post('carrier/{carrier}/document/{document}/approve-default', [CarrierDocumentController::class, 'approveDefaultDocument'])
    ->name('carrier.approve_default_document_alt');
// La ruta que se usa en la vista documents.blade.php
Route::post('carrier/{carrier}/document/{document}/approve-default-document', [CarrierDocumentController::class, 'approveDefaultDocument'])
    ->name('carrier.approveDefaultDocument');

Route::get('/carrier/documents/refresh', [CarrierDocumentController::class, 'refresh'])->name('carrier.admin_documents.refresh');




Route::resource('document-types', DocumentTypeController::class)
    ->except('show');

/*
|--------------------------------------------------------------------------
| RUTAS ADMIN INSPECTIONS
|--------------------------------------------------------------------------
*/
Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'store'])->name('store');
        Route::get('/{inspection}/edit', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'edit'])->name('edit');
        Route::put('/{inspection}', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'update'])->name('update');
        Route::delete('/{inspection}', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'destroy'])->name('destroy');

        // Rutas para manejo de documentos
        Route::delete('/{inspection}/files/{document}', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'deleteFile'])->name('delete-file');
        Route::get('/{inspection}/files', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'getFiles'])->name('files');
        Route::post('/document/delete', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'ajaxDestroyDocument'])->name('document.delete.ajax');
        Route::delete('/document/{document}', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'destroyDocument'])->name('document.delete');
        Route::get('/documents', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'allDocuments'])->name('documents');
        Route::get('/driver/{driver}/documents', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'driverDocuments'])->name('driver.documents');

        // Rutas para obtener vehículos y conductores por transportista
        Route::get('/carrier/{carrier}/drivers', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'getDriversByCarrier'])->name('drivers.by.carrier');
        Route::get('/carrier/{carrier}/vehicles', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'getVehiclesByCarrier'])->name('vehicles.by.carrier');
        Route::get('/driver/{driver}/vehicles', [\App\Http\Controllers\Admin\Driver\InspectionsController::class, 'getVehiclesByDriver'])->name('vehicles.by.driver');
    });



// Route::resource('user_carrier', UserCarrierController::class);
Route::post('user_carrier/{user_carrier}/delete-photo', [UserCarrierController::class, 'deletePhoto'])->name('user_carrier.delete-photo');



/*
|--------------------------------------------------------------------------
| RUTAS ADMIN DRIVERS
|--------------------------------------------------------------------------    
*/

Route::prefix('drivers')->name('drivers.')->group(function () {
    Route::get('/', [DriverListController::class, 'index'])->name('index');
    Route::get('/{driver}', [DriverListController::class, 'show'])->name('show');
    Route::get('/{driver}/accident-history', [AccidentsController::class, 'driverHistory'])->name('accident-history');
    Route::get('/{driver}/traffic-history', [TrafficConvictionsController::class, 'driverHistory'])->name('traffic-history');
    Route::put('/{driver}/activate', [DriverListController::class, 'activate'])->name('activate');
    Route::put('/{driver}/deactivate', [DriverListController::class, 'deactivate'])->name('deactivate');
    Route::get('/{id}/documents/download', [DriverListController::class, 'downloadDocuments'])->name('documents.download');
    Route::get('/{id}/regenerate-application-forms', [DriverListController::class, 'regenerateApplicationForms'])->name('regenerate-application-forms');
    Route::get('/export', [DriverListController::class, 'export'])->name('export');
});

// Rutas para accidentes
Route::prefix('accidents')->name('accidents.')->group(function () {
    Route::get('/', [AccidentsController::class, 'index'])->name('index');
    Route::get('/create', [AccidentsController::class, 'create'])->name('create');
    Route::post('/', [AccidentsController::class, 'store'])->name('store');
    Route::get('/{accident}/edit', [AccidentsController::class, 'edit'])->name('edit');
    Route::put('/{accident}', [AccidentsController::class, 'update'])->name('update');
    Route::delete('/{accident}', [AccidentsController::class, 'destroy'])->name('destroy');

    // Documentos específicos de un accidente
    Route::get('{accident}/documents', [AccidentsController::class, 'showDocuments'])->name('documents.show');
    Route::post('{accident}/documents', [AccidentsController::class, 'storeDocuments'])->name('documents.store');
    Route::get('document/{documentId}/preview', [AccidentsController::class, 'previewDocument'])->name('document.preview.single');
    // Obtener conductores por transportista (ruta legacy para compatibilidad)
    Route::get('/carriers/{carrier}/drivers', [AccidentsController::class, 'getDriversByCarrier'])->name('drivers.by.carrier');
});

// Vista general de todos los documentos de accidentes
Route::get('/accidents/documents', [AccidentsController::class, 'documents'])->name('accidents.documents.index');
Route::delete('/accidents/document/{media}', [AccidentsController::class, 'deleteDocumentDirectly'])->name('accidents.document.destroy');

// Rutas para infracciones de tráfico
Route::prefix('traffic')->name('traffic.')->group(function () {
    Route::get('/', [TrafficConvictionsController::class, 'index'])->name('index');
    Route::get('/create', [TrafficConvictionsController::class, 'create'])->name('create');
    Route::post('/', [TrafficConvictionsController::class, 'store'])->name('store');
    Route::get('/{conviction}/edit', [TrafficConvictionsController::class, 'edit'])->name('edit');
    Route::put('/{conviction}', [TrafficConvictionsController::class, 'update'])->name('update');
    Route::delete('/{conviction}', [TrafficConvictionsController::class, 'destroy'])->name('destroy');
    Route::get('/{conviction}/documents', [TrafficConvictionsController::class, 'showDocuments'])->name('documents');
    Route::get('/{conviction}/download-documents', [TrafficConvictionsController::class, 'downloadDocuments'])->name('documents.download');
    Route::get('/export', [TrafficConvictionsController::class, 'export'])->name('export');
    Route::get('/carriers/{carrier}/drivers', [TrafficConvictionsController::class, 'getDriversByCarrier'])->name('drivers.by.carrier');
    Route::delete('/documents/{mediaId}', [TrafficConvictionsController::class, 'deleteDocument'])->name('documents.delete');
});

// Rutas para el reclutamiento de conductores
Route::prefix('driver-recruitment')->name('driver-recruitment.')->group(function () {
    Route::get('/', [DriverRecruitmentController::class, 'index'])->name('index');
    Route::get('/{driverId}', [DriverRecruitmentController::class, 'show'])->name('show');
});

Route::get('drivers/{driver}/course-history', [CoursesController::class, 'driverHistory'])->name('drivers.course-history');

// Eliminadas las rutas antiguas de testings para evitar conflictos
// Ahora todas las rutas de pruebas de conductores están en el grupo driver-testings
Route::get('drivers/{driver}/testing-history', [TestingsController::class, 'driverHistory'])->name('drivers.testing-history');

/*
|--------------------------------------------------------------------------
| RUTAS PARA DRIVER TESTINGS (PRUEBAS DE CONDUCTORES)
|--------------------------------------------------------------------------
*/
Route::prefix('driver-testings')->name('driver-testings.')->group(function () {
    Route::get('/', [DriverTestingController::class, 'index'])->name('index');
    Route::get('/create', [DriverTestingController::class, 'create'])->name('create');
    Route::post('/', [DriverTestingController::class, 'store'])->name('store');
    Route::get('/{driverTesting}', [DriverTestingController::class, 'show'])->name('show');
    Route::get('/{driverTesting}/edit', [DriverTestingController::class, 'edit'])->name('edit');
    Route::put('/{driverTesting}', [DriverTestingController::class, 'update'])->name('update');
    Route::delete('/{driverTesting}', [DriverTestingController::class, 'destroy'])->name('destroy');
    Route::get('/{driverTesting}/download-pdf', [DriverTestingController::class, 'downloadPdf'])->name('download-pdf');
    Route::get('/{driverTesting}/regenerate-pdf', [DriverTestingController::class, 'regeneratePdf'])->name('regenerate-pdf');

    // Rutas API para búsqueda dinámica
    Route::get('/api/search-carriers', [DriverTestingController::class, 'searchCarriers'])->name('search-carriers');
    Route::get('/api/get-drivers/{carrier}', [DriverTestingController::class, 'getDriversByCarrier'])->name('get-drivers');
    Route::get('/api/get-driver-details/{driverDetail}', [DriverTestingController::class, 'getDriverDetails'])->name('get-driver-details');
});

/*
|--------------------------------------------------------------------------
| RUTAS PARA INSPECTIONS (INSPECCIONES DE CONDUCTORES)
|--------------------------------------------------------------------------
*/

// Rutas para todas las inspecciones de vehículos
Route::prefix('vehicle-inspections')->name('vehicle-inspections.')->group(function () {
    Route::get('/', [InspectionsController::class, 'index'])->name('index');
    Route::post('/', [InspectionsController::class, 'store'])->name('store');
    Route::put('/{inspection}', [InspectionsController::class, 'update'])->name('update');
    Route::delete('/{inspection}', [InspectionsController::class, 'destroy'])->name('destroy');

    // Rutas para eliminar archivos adjuntos
    Route::delete('/{inspection}/files/{mediaId}', [InspectionsController::class, 'deleteFile'])->name('delete-file');
    Route::get('/{inspection}/files', [InspectionsController::class, 'getFiles'])->name('files');

    // Rutas para obtener vehículos y conductores
    Route::get('/carriers/{carrier}/vehicles', [InspectionsController::class, 'getVehiclesByCarrier'])->name('vehicles.by.carrier');
    Route::get('/drivers/{driver}/vehicles', [InspectionsController::class, 'getVehiclesByDriver'])->name('vehicles.by.driver');
    Route::get('/carriers/{carrier}/drivers', [InspectionsController::class, 'getDriversByCarrier'])->name('drivers.by.carrier');
});

// Añadir esta ruta a las rutas existentes de conductores (drivers)
// Historia de inspecciones específica para un conductor
Route::get('drivers/{driver}/inspection-history', [InspectionsController::class, 'driverHistory'])->name('drivers.inspection-history');

/*
|--------------------------------------------------------------------------
| RUTAS VEHICLES
|--------------------------------------------------------------------------    
*/

// Rutas principales agrupadas bajo el prefijo 'admin-vehicles'
Route::prefix('admin-vehicles')->name('admin-vehicles.')->group(function () {
    // Ruta principal de vehículos (sin prefijo adicional)
    Route::get('/', [VehicleController::class, 'index'])->name('index');
    Route::get('/create', [VehicleController::class, 'create'])->name('create');
    Route::post('/', [VehicleController::class, 'store'])->name('store');
    Route::get('/{vehicle}', [VehicleController::class, 'show'])->name('show');
    Route::get('/{vehicle}/edit', [VehicleController::class, 'edit'])->name('edit');
    Route::put('/{vehicle}', [VehicleController::class, 'update'])->name('update');
    Route::delete('/{vehicle}', [VehicleController::class, 'destroy'])->name('destroy');
    Route::post('/{vehicle}/delete-photo', [VehicleController::class, 'deletePhoto'])->name('delete-photo');

    // API para obtener conductores por carrier
    Route::get('/driver-details/{userDriverDetail}', [VehicleController::class, 'getDriverDetails'])->name('driver-details');
    Route::get('/drivers-by-carrier/{carrierId}', [VehicleController::class, 'getDriversByCarrier'])->name('drivers-by-carrier');
    Route::get('/get-driver-info/{userDriverDetailId}', [VehicleController::class, 'getDriverInfo'])->name('get-driver-info');
    
    // Rutas para documentos de vehículos
    Route::get('/{vehicle}/documents', [VehicleDocumentController::class, 'index'])->name('documents.index');
    Route::get('/{vehicle}/documents/create', [VehicleDocumentController::class, 'create'])->name('documents.create');
    Route::post('/{vehicle}/documents', [VehicleDocumentController::class, 'store'])->name('documents.store');
    Route::get('/{vehicle}/documents/{document}', [VehicleDocumentController::class, 'show'])->name('documents.show');
    Route::get('/{vehicle}/documents/{document}/edit', [VehicleDocumentController::class, 'edit'])->name('documents.edit');
    Route::put('/{vehicle}/documents/{document}', [VehicleDocumentController::class, 'update'])->name('documents.update');
    Route::delete('/{vehicle}/documents/{document}', [VehicleDocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/{vehicle}/documents/{document}/download', [VehicleDocumentController::class, 'download'])->name('documents.download');
    Route::get('/{vehicle}/documents/{document}/preview', [VehicleDocumentController::class, 'preview'])->name('documents.preview');

    // Rutas para mantenimientos anidadas bajo un vehículo específico
    Route::prefix('{vehicle}/maintenance-records')->name('maintenance-records.')->group(function () {
        Route::get('/', [VehicleMaintenanceController::class, 'index'])->name('index');
        Route::get('/create', [VehicleMaintenanceController::class, 'create'])->name('create');
        Route::post('/', [VehicleMaintenanceController::class, 'store'])->name('store');
        Route::get('/{maintenance}', [VehicleMaintenanceController::class, 'show'])->name('show');
        Route::get('/{maintenance}/edit', [VehicleMaintenanceController::class, 'edit'])->name('edit');
        Route::put('/{maintenance}', [VehicleMaintenanceController::class, 'update'])->name('update');
        Route::delete('/{maintenance}', [VehicleMaintenanceController::class, 'destroy'])->name('destroy');
        Route::put('/{maintenance}/toggle-status', [VehicleMaintenanceController::class, 'toggleStatus'])->name('toggle-status');
    });
});

// Rutas de compatibilidad con el nombre antiguo (para código legacy)
Route::prefix('vehicles')->name('vehicles.')->group(function () {
    // Rutas para documentos de vehículos - compatibilidad
    Route::get('/{vehicle}/documents', [VehicleDocumentController::class, 'index'])->name('documents.index');
    Route::get('/{vehicle}/documents/create', [VehicleDocumentController::class, 'create'])->name('documents.create');
    Route::post('/{vehicle}/documents', [VehicleDocumentController::class, 'store'])->name('documents.store');
    Route::get('/{vehicle}/documents/{document}', [VehicleDocumentController::class, 'show'])->name('documents.show');
    Route::get('/{vehicle}/documents/{document}/edit', [VehicleDocumentController::class, 'edit'])->name('documents.edit');
    Route::put('/{vehicle}/documents/{document}', [VehicleDocumentController::class, 'update'])->name('documents.update');
    Route::delete('/{vehicle}/documents/{document}', [VehicleDocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/{vehicle}/documents/{document}/download', [VehicleDocumentController::class, 'download'])->name('documents.download');
    Route::get('/{vehicle}/documents/{document}/preview', [VehicleDocumentController::class, 'preview'])->name('documents.preview');
    
    // Rutas para mantenimientos - eliminado código duplicado
});

// Rutas para administrar marcas de vehículos (como entidad separada)
Route::resource('vehicle-makes', VehicleMakeController::class)->names('vehicle-makes');

// Rutas para administrar tipos de vehículos (como entidad separada)
Route::resource('vehicle-types', VehicleTypeController::class)->names('vehicle-types');
Route::get('vehicle-types/search', [VehicleTypeController::class, 'search'])->name('vehicle-types.search');

// Ruta para la vista global de documentos de vehículos
Route::get('vehicles-documents', [App\Http\Controllers\Admin\Vehicles\VehicleDocumentsOverviewController::class, 'index'])
    ->name('vehicles-documents.index');

/*
|--------------------------------------------------------------------------
| RUTAS MAINTENANCE
|--------------------------------------------------------------------------    
*/
Route::prefix('maintenance-system')->name('maintenance-system.')->group(function () {
    Route::get('/', [MaintenanceController::class, 'index'])->name('index');
    Route::get('/create', [MaintenanceController::class, 'create'])->name('create');
    Route::post('/', [MaintenanceController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [MaintenanceController::class, 'edit'])->name('edit');
    Route::put('/{id}', [MaintenanceController::class, 'update'])->name('update');
    Route::get('/{id}', [MaintenanceController::class, 'show'])->name('show');
    Route::put('/{id}/toggle-status', [MaintenanceController::class, 'toggleStatus'])->name('toggle-status');
    Route::delete('/{id}', [MaintenanceController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/reschedule', [MaintenanceController::class, 'reschedule'])->name('reschedule');

    // Rutas adicionales para funcionalidades extendidas (opcionales)
    Route::get('/export', [MaintenanceController::class, 'export'])->name('export');
    // Nota: Las rutas de reports y calendar están definidas fuera de este grupo
    // apuntando a MaintenanceReportController y MaintenanceCalendarController
});



/*
|--------------------------------------------------------------------------
| RUTAS ADMIN NOTIFICATIONS
|--------------------------------------------------------------------------
*/
Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationsController::class, 'index'])->name('index');
    Route::post('/{notification}/mark-as-read', [NotificationsController::class, 'markAsRead'])->name('mark-as-read');
    Route::post('/{notification}/mark-as-unread', [NotificationsController::class, 'markAsUnread'])->name('mark-as-unread');
    Route::post('/mark-all-read', [NotificationsController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{notification}', [NotificationsController::class, 'destroy'])->name('destroy');
    Route::delete('/', [NotificationsController::class, 'deleteAll'])->name('delete-all');
});

/*
|--------------------------------------------------------------------------
| RUTAS NOTIFICATION SETTINGS
|--------------------------------------------------------------------------
*/
Route::prefix('notification-settings')->name('notification-settings.')->group(function () {
    Route::get('/', [\App\Http\Controllers\NotificationSettingsController::class, 'index'])->name('index');
    Route::get('/{eventType}', [\App\Http\Controllers\NotificationSettingsController::class, 'show'])->name('show');
    Route::put('/{eventType}', [\App\Http\Controllers\NotificationSettingsController::class, 'update'])->name('update');
    Route::get('/logs/index', [\App\Http\Controllers\NotificationSettingsController::class, 'logs'])->name('logs');
    Route::get('/logs/data', [\App\Http\Controllers\NotificationSettingsController::class, 'getLogsData'])->name('logs.data');
    Route::post('/test', [\App\Http\Controllers\NotificationSettingsController::class, 'testNotification'])->name('test');
});

/*
|--------------------------------------------------------------------------
| RUTAS NOTIFICATION RECIPIENTS
|--------------------------------------------------------------------------
*/
Route::prefix('notification-recipients')->name('notification-recipients.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\NotificationRecipientsController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Admin\NotificationRecipientsController::class, 'store'])->name('store');
    Route::delete('/{recipient}', [\App\Http\Controllers\Admin\NotificationRecipientsController::class, 'destroy'])->name('destroy');
    Route::put('/{recipient}/toggle', [\App\Http\Controllers\Admin\NotificationRecipientsController::class, 'toggle'])->name('toggle');
    Route::get('/users/search', [\App\Http\Controllers\Admin\NotificationRecipientsController::class, 'getUsers'])->name('users.search');
});


Route::controller(PageController::class)->group(function () {
    //Route::get('/', 'dashboardOverview1')->name('dashboard-overview-1');

    Route::get('dashboard-overview-4', 'dashboardOverview4')->name('dashboard-overview-4');
    Route::get('dashboard-overview-2', 'dashboardOverview2')->name('dashboard-overview-2');
    Route::get('dashboard-overview-3', 'dashboardOverview3')->name('dashboard-overview-3');
    Route::get('dashboard-overview-5', 'dashboardOverview5')->name('dashboard-overview-5');
    Route::get('dashboard-overview-6', 'dashboardOverview6')->name('dashboard-overview-6');
    Route::get('dashboard-overview-7', 'dashboardOverview7')->name('dashboard-overview-7');
    Route::get('dashboard-overview-8', 'dashboardOverview8')->name('dashboard-overview-8');
    Route::get('userstemplate', 'users')->name('users');
    Route::get('departments', 'departments')->name('departments');
    Route::get('add-user', 'addUser')->name('add-user');
    Route::get('profile-overview', 'profileOverview')->name('profile-overview');
    Route::get('profile-overview?page=events', 'profileOverview')->name('profile-overview-events');
    Route::get('profile-overview?page=achievements', 'profileOverview')->name('profile-overview-achievements');
    Route::get('profile-overview?page=contacts', 'profileOverview')->name('profile-overview-contacts');
    Route::get('profile-overview?page=default', 'profileOverview')->name('profile-overview-default');
    Route::get('settings', 'settings')->name('settings');
    Route::get('settings?page=email-settings', 'settings')->name('settings-email-settings');
    Route::get('settings?page=security', 'settings')->name('settings-security');
    Route::get('settings?page=preferences', 'settings')->name('settings-preferences');
    Route::get('settings?page=two-factor-authentication', 'settings')->name('settings-two-factor-authentication');
    Route::get('settings?page=device-history', 'settings')->name('settings-device-history');
    Route::get('settings?page=notification-settings', 'settings')->name('settings-notification-settings');
    Route::get('settings?page=connected-services', 'settings')->name('settings-connected-services');
    Route::get('settings?page=social-media-links', 'settings')->name('settings-social-media-links');
    Route::get('settings?page=account-deactivation', 'settings')->name('settings-account-deactivation');
    Route::get('billing', 'billing')->name('billing');
    Route::get('invoice', 'invoice')->name('invoice');
    Route::get('categories', 'categories')->name('categories');
    Route::get('add-product', 'addProduct')->name('add-product');
    Route::get('product-list', 'productList')->name('product-list');
    Route::get('product-grid', 'productGrid')->name('product-grid');
    Route::get('transaction-list', 'transactionList')->name('transaction-list');
    Route::get('transaction-detail', 'transactionDetail')->name('transaction-detail');
    Route::get('seller-list', 'sellerList')->name('seller-list');
    Route::get('seller-detail', 'sellerDetail')->name('seller-detail');
    Route::get('reviews', 'reviews')->name('reviews');
    Route::get('inbox', 'inbox')->name('inbox');
    Route::get('file-manager-list', 'fileManagerList')->name('file-manager-list');
    Route::get('file-manager-grid', 'fileManagerGrid')->name('file-manager-grid');
    Route::get('chat', 'chat')->name('chat');
    Route::get('calendar', 'calendar')->name('calendar');
    Route::get('point-of-sale', 'pointOfSale')->name('point-of-sale');
    Route::get('creative', 'creative')->name('creative');
    Route::get('dynamic', 'dynamic')->name('dynamic');
    Route::get('interactive', 'interactive')->name('interactive');
    Route::get('regular-table', 'regularTable')->name('regular-table');
    Route::get('tabulator', 'tabulator')->name('tabulator');
    Route::get('modal', 'modal')->name('modal');
    Route::get('slideover', 'slideover')->name('slideover');
    Route::get('notification', 'notification')->name('notification');
    Route::get('tab', 'tab')->name('tab');
    Route::get('accordion', 'accordion')->name('accordion');
    Route::get('button', 'button')->name('button');
    Route::get('alert', 'alert')->name('alert');
    Route::get('progress-bar', 'progressBar')->name('progress-bar');
    Route::get('tooltip', 'tooltip')->name('tooltip');
    Route::get('dropdown', 'dropdown')->name('dropdown');
    Route::get('typography', 'typography')->name('typography');
    Route::get('icon', 'icon')->name('icon');
    Route::get('loading-icon', 'loadingIcon')->name('loading-icon');
    Route::get('regular-form', 'regularForm')->name('regular-form');
    Route::get('datepicker', 'datepicker')->name('datepicker');
    Route::get('tom-select', 'tomSelect')->name('tom-select');
    Route::get('file-upload', 'fileUpload')->name('file-upload');
    Route::get('wysiwyg-editor', 'wysiwygEditor')->name('wysiwyg-editor');
    Route::get('validation', 'validation')->name('validation');
    Route::get('chart', 'chart')->name('chart');
    Route::get('slider', 'slider')->name('slider');
    Route::get('image-zoom', 'imageZoom')->name('image-zoom');
    Route::get('landing-page', 'landingPage')->name('landing-page');
    Route::get('login', 'login')->name('login');
    Route::get('register', 'register')->name('register');
});

/*
|--------------------------------------------------------------------------
| RUTA TEMPORAL PARA ACTUALIZAR FECHAS DE APLICACIONES APROBADAS
|--------------------------------------------------------------------------
*/
Route::get('fix-application-dates', [\App\Http\Controllers\Admin\Driver\UpdateApplicationDatesController::class, 'updateCompletedDates'])->name('fix-application-dates');

/*
|--------------------------------------------------------------------------
| RUTAS PARA EL MÓDULO DE REPORTES ADMINISTRATIVOS
|--------------------------------------------------------------------------
*/
Route::prefix('reports')->name('reports.')->group(function () {
    // Página principal de reportes
    Route::get('/', [\App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('index');
    
    // Reporte de conductores activos
    Route::get('/active-drivers', [\App\Http\Controllers\Admin\ReportsController::class, 'activeDrivers'])->name('active-drivers');
    Route::get('/active-drivers/pdf', [\App\Http\Controllers\Admin\ReportsController::class, 'activeDriversPdf'])->name('active-drivers.pdf');
    
    // Reporte de prospectos de conductores
    Route::get('/driver-prospects', [\App\Http\Controllers\Admin\ReportsController::class, 'driverProspects'])->name('driver-prospects');
    Route::get('/driver-prospects/pdf', [\App\Http\Controllers\Admin\ReportsController::class, 'driverProspectsPdf'])->name('driver-prospects.pdf');
    
    // Reporte de equipamiento/vehículos
    Route::get('/equipment-list', [\App\Http\Controllers\Admin\ReportsController::class, 'equipmentList'])->name('equipment-list');
    Route::get('/equipment-list/pdf', [\App\Http\Controllers\Admin\ReportsController::class, 'equipmentListPdf'])->name('equipment-list.pdf');
    
    // Descarga de documentos por carrier
    Route::get('/carrier-documents', [\App\Http\Controllers\Admin\ReportsController::class, 'carrierDocuments'])->name('carrier-documents');
    Route::get('/carrier-documents/pdf', [\App\Http\Controllers\Admin\ReportsController::class, 'carrierDocumentsPdf'])->name('carrier-documents.pdf');
    Route::get('/download-carrier-documents/{carrier}', [\App\Http\Controllers\Admin\ReportsController::class, 'downloadCarrierDocuments'])->name('download-carrier-documents');
    
    // Gestión de accidentes
    Route::get('/accidents', [\App\Http\Controllers\Admin\ReportsController::class, 'accidents'])->name('accidents');
    Route::get('/register-accident', [\App\Http\Controllers\Admin\ReportsController::class, 'registerAccident'])->name('register-accident');
    Route::post('/store-accident', [\App\Http\Controllers\Admin\ReportsController::class, 'storeAccident'])->name('store-accident');
    Route::get('/accidents-list', [\App\Http\Controllers\Admin\ReportsController::class, 'accidentsList'])->name('accidents-list');
});

// API route for getting active drivers by carrier (used in accident registration)
Route::get('/api/active-drivers-by-carrier/{carrierId}', [\App\Http\Controllers\Admin\ReportsController::class, 'getActiveDriversByCarrier']);
