<?php


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CarrierController;
use App\Http\Controllers\Admin\DriversController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\TempUploadController;
use App\Http\Controllers\Admin\UserDriverController;
use App\Http\Controllers\Admin\UserCarrierController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\Admin\CarrierDocumentController;
use App\Http\Controllers\Admin\Vehicles\VehicleController;
use App\Http\Controllers\Admin\UserCarrierDocumentController;
use App\Http\Controllers\Admin\Vehicles\VehicleMakeController;
use App\Http\Controllers\Admin\Vehicles\VehicleServiceItemController;
use App\Http\Controllers\Admin\Vehicles\VehicleTypeController;


Route::get('theme-switcher/{activeTheme}', [ThemeController::class, 'switch'])->name('theme-switcher');

Route::get('/', function () {
    return view('admin.dashboard');
})->name('dashboard');

/*
    |--------------------------------------------------------------------------
    | RUTAS ADMIN NOTIFICATION
    |--------------------------------------------------------------------------    
*/

/*
    |--------------------------------------------------------------------------
    | RUTAS ADMIN USERS
    |--------------------------------------------------------------------------    
*/

Route::get('users/export-excel', [UserController::class, 'exportToExcel'])->name('users.export.excel');
Route::get('users/export-pdf', [UserController::class, 'exportToPdf'])->name('users.export.pdf');
Route::post('users/{user}/delete-photo', [UserController::class, 'deletePhoto'])->name('users.delete-photo');
Route::resource('users', UserController::class);

/*
    |--------------------------------------------------------------------------
    | RUTAS ADMIN ROLES
    |--------------------------------------------------------------------------    
*/
Route::resource('permissions', PermissionController::class);
Route::resource('roles', RoleController::class);


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

Route::resource('carrier', CarrierController::class);
Route::get('carrier/export-excel', [CarrierController::class, 'exportToExcel'])->name('carrier.export.excel');
Route::get('carrier/export-pdf', [CarrierController::class, 'exportToPdf'])->name('carrier.export.pdf');
Route::post('carrier/{carrier}/delete-photo', [CarrierController::class, 'deletePhoto'])->name('carrier.delete-photo');


/*
Route::post('carrier/{carrier}/delete-photo', [CarrierController::class, 'deletePhoto'])->name('carrier.delete-photo');
*/
/*
    |----------------------------------------------------------------------
    | RUTAS ADMIN CARRIERS (CON TABS USERS Y DOCUMENTS)
    |----------------------------------------------------------------------
*/

Route::prefix('carrier')->name('carrier.')->group(function () {
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
| RUTAS PARA SUPERADMIN: ADMIN DRIVERS
|--------------------------------------------------------------------------
*/

// En el grupo existente de user_drivers



Route::prefix('carrier/{carrier}/drivers')->name('carrier.user_drivers.')->group(function () {
    // Rutas existentes...
    Route::get('/', [UserDriverController::class, 'index'])->name('index');
    Route::get('/create', [UserDriverController::class, 'create'])->name('create');
    Route::post('/', [UserDriverController::class, 'store'])->name('store');
    Route::get('/{userDriverDetail}/edit', [UserDriverController::class, 'edit'])->name('edit');
    Route::put('/{userDriverDetail}', [UserDriverController::class, 'update'])->name('update');
    Route::delete('/{userDriverDetail}', [UserDriverController::class, 'destroy'])->name('destroy');
    Route::delete('/{userDriverDetail}/photo', [UserDriverController::class, 'deletePhoto'])->name('delete-photo');
});


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
    ->name('carrier.approveDefaultDocument');
Route::post('carrier/{carrier}/document/{document}/approve-default', [CarrierDocumentController::class, 'approveDefaultDocument'])
    ->name('admin.carrier.approveDefaultDocument');

Route::get('/carrier/documents/refresh', [CarrierDocumentController::class, 'refresh'])->name('carrier.admin_documents.refresh');




Route::resource('document-types', DocumentTypeController::class)
    ->except('show');


// Route::resource('user_carrier', UserCarrierController::class);
Route::post('user_carrier/{user_carrier}/delete-photo', [UserCarrierController::class, 'deletePhoto'])->name('user_carrier.delete-photo');



/*
    |--------------------------------------------------------------------------
    | RUTAS ADMIN DRIVERS
    |--------------------------------------------------------------------------    
*/

// Rutas para la vista general de drivers
Route::get('drivers', [DriversController::class, 'index'])->name('drivers.index');
Route::put('drivers/{driver}/toggle-status', [DriversController::class, 'toggleStatus'])->name('drivers.toggle-status');

/*
|--------------------------------------------------------------------------
| RUTAS VEHICLES
|--------------------------------------------------------------------------    
*/

// Rutas principales agrupadas bajo el prefijo 'vehicles'
Route::prefix('vehicles')->name('vehicles.')->group(function () {
    // Ruta principal de vehículos (sin prefijo adicional)
    Route::get('/', [VehicleController::class, 'index'])->name('index');
    Route::get('/create', [VehicleController::class, 'create'])->name('create');
    Route::post('/', [VehicleController::class, 'store'])->name('store');
    Route::get('/{vehicle}', [VehicleController::class, 'show'])->name('show');
    Route::get('/{vehicle}/edit', [VehicleController::class, 'edit'])->name('edit');
    Route::put('/{vehicle}', [VehicleController::class, 'update'])->name('update');
    Route::delete('/{vehicle}', [VehicleController::class, 'destroy'])->name('destroy');

    // Rutas para ítems de servicio anidadas bajo un vehículo específico
    Route::prefix('{vehicle}/service-items')->name('service-items.')->group(function () {
        Route::get('/', [VehicleServiceItemController::class, 'index'])->name('index');
        Route::get('/create', [VehicleServiceItemController::class, 'create'])->name('create');
        Route::post('/', [VehicleServiceItemController::class, 'store'])->name('store');
        Route::get('/{serviceItem}', [VehicleServiceItemController::class, 'show'])->name('show');
        Route::get('/{serviceItem}/edit', [VehicleServiceItemController::class, 'edit'])->name('edit');
        Route::put('/{serviceItem}', [VehicleServiceItemController::class, 'update'])->name('update');
        Route::delete('/{serviceItem}', [VehicleServiceItemController::class, 'destroy'])->name('destroy');
    });
});

// Rutas para administrar marcas de vehículos (como entidad separada)
Route::resource('vehicle-makes', VehicleMakeController::class);
Route::get('vehicle-makes/search', [VehicleMakeController::class, 'search'])->name('vehicle-makes.search');

// Rutas para administrar tipos de vehículos (como entidad separada)
Route::resource('vehicle-types', VehicleTypeController::class);
Route::get('vehicle-types/search', [VehicleTypeController::class, 'search'])->name('vehicle-types.search');

/*
Route::prefix('user-carrier')->name('user_carrier.')->group(function () {
    Route::get('/dashboard', function () {
        return view('user_carrier.dashboard');
    })->middleware('auth:user_carrier')->name('dashboard');

    Route::get('/register', [CustomLoginController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [CustomLoginController::class, 'register']);
    Route::get('/confirm/{token}', [CustomLoginController::class, 'confirmEmail'])->name('confirm');    
    Route::get('/complete-registration', [CustomLoginController::class, 'showCompleteRegistrationForm'])->name('complete_registration');
    Route::post('/complete-registration', [CustomLoginController::class, 'completeRegistration']);
});
*/



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
