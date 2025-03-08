<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;

use App\Models\Carrier;
use App\Helpers\Constants;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Admin\DriverStepService;
use App\Http\Controllers\Api\UserDriverApiController;
use App\Notifications\Admin\Driver\NewUserDriverNotification;
use App\Notifications\Admin\Driver\NewDriverNotificationAdmin;
use App\Notifications\Admin\Driver\NewDriverCreatedNotification;

class UserDriverController extends Controller
{
    protected $driverStepService;
    protected $apiController;    

    public function __construct(DriverStepService $driverStepService, UserDriverApiController $apiController)
    {
        $this->driverStepService = $driverStepService;
        $this->apiController = $apiController;
    }

    /**
     * Mostrar la lista de conductores
     */
    public function index(Carrier $carrier)
    {
        $maxDrivers = $carrier->membership->max_drivers ?? 1;
        $currentDrivers = UserDriverDetail::where('carrier_id', $carrier->id)->count();
        $exceededLimit = $currentDrivers >= $maxDrivers;

        return view('admin.user_driver.index', [
            'carrier' => $carrier,
            'userDrivers' => UserDriverDetail::where('carrier_id', $carrier->id)
                ->with('user')
                ->paginate(10),
            'maxDrivers' => $maxDrivers,
            'currentDrivers' => $currentDrivers,
            'exceeded_limit' => $exceededLimit,
        ]);
    }

    /**
     * Mostrar el formulario de creación
     */
    public function create(Carrier $carrier)
    {
        // Verificar el límite de drivers para este carrier
        $maxDrivers = $carrier->membership->max_drivers ?? 1;
        $currentDriversCount = UserDriverDetail::where('carrier_id', $carrier->id)->count();

        Log::info('Verificando límite de drivers para carrier', [
            'carrier_id' => $carrier->id,
            'carrier_name' => $carrier->name,
            'max_drivers' => $maxDrivers,
            'current_drivers_count' => $currentDriversCount
        ]);

        if ($currentDriversCount >= $maxDrivers) {
            Log::warning('Límite de drivers excedido para carrier específico', [
                'carrier_id' => $carrier->id,
                'max_drivers' => $maxDrivers,
                'current_count' => $currentDriversCount
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('exceeded_limit', true)
                ->with('error', 'No puedes agregar más conductores a este carrier. Actualiza tu plan o contacta al administrador.');
        }

        // Cargar datos necesarios para la vista
        $usStates = Constants::usStates();
        $driverPositions = Constants::driverPositions();
        $referralSources = Constants::referralSources();

        return view('admin.user_driver.create', compact(
            'carrier',
            'usStates',
            'driverPositions',
            'referralSources'
        ));
    }

    /**
     * Almacenar un nuevo conductor (delegado a la API)
     */
    /**
     * Almacenar un nuevo conductor (delegado a la API)
     */
    /**
     * Almacenar un nuevo conductor (delegado a la API)
     */
    public function store(Request $request, Carrier $carrier)
    {
        try {
            // Identificar qué tab está activo
            $tab = $request->input('active_tab', 'general');
            $driverId = $request->input('user_driver_id');
            
            Log::info('Procesando tab', ['tab' => $tab, 'driver_id' => $driverId]);
            
            // Si tenemos ID de driver, usamos métodos de actualización
            if ($driverId) {
                $userDriverDetail = UserDriverDetail::find($driverId);
                
                if ($userDriverDetail) {
                    // Según la pestaña, llamar al método correspondiente
                    switch ($tab) {
                        case 'licenses':
                            $apiResponse = $this->apiController->updateLicenses($request, $carrier, $userDriverDetail);
                            break;
                        case 'medical':
                            $apiResponse = $this->apiController->updateMedical($request, $carrier, $userDriverDetail);
                            break;
                        case 'training':
                            $apiResponse = $this->apiController->updateTraining($request, $carrier, $userDriverDetail);
                            break;
                        case 'traffic':
                            $apiResponse = $this->apiController->updateTraffic($request, $carrier, $userDriverDetail);
                            break;
                        case 'accident':
                            $apiResponse = $this->apiController->updateAccident($request, $carrier, $userDriverDetail);
                            break;
                        default:
                            $apiResponse = $this->apiController->updateGeneral($request, $carrier, $userDriverDetail);
                    }
                } else {
                    // Driver ID inválido, crear nuevo
                    $apiResponse = $this->apiController->store($request, $carrier);
                }
            } else {
                // Sin ID, crear nuevo driver y luego actualizar con datos específicos
                $createResponse = $this->apiController->store($request, $carrier);
                $createData = json_decode($createResponse->getContent(), true);
                
                // Si la creación fue exitosa y tenemos tab específico
                if ($createResponse->getStatusCode() < 300 && isset($createData['data']['id']) && $tab != 'general') {
                    $userDriverDetail = UserDriverDetail::find($createData['data']['id']);
                    
                    // Ahora actualizar con datos específicos según la pestaña
                    switch ($tab) {
                        case 'licenses':
                            $apiResponse = $this->apiController->updateLicenses($request, $carrier, $userDriverDetail);
                            break;
                        case 'medical':
                            $apiResponse = $this->apiController->updateMedical($request, $carrier, $userDriverDetail);
                            break;
                        case 'training':
                            $apiResponse = $this->apiController->updateTraining($request, $carrier, $userDriverDetail);
                            break;
                        case 'traffic':
                            $apiResponse = $this->apiController->updateTraffic($request, $carrier, $userDriverDetail);
                            break;
                        case 'accident':
                            $apiResponse = $this->apiController->updateAccident($request, $carrier, $userDriverDetail);
                            break;
                    }
                } else {
                    $apiResponse = $createResponse;
                }
            }
            
            $responseData = json_decode($apiResponse->getContent(), true);
            
            // Redirigir según resultado
            if ($apiResponse->getStatusCode() < 300) {
                if (isset($responseData['data']['id'])) {
                    return redirect()->route('admin.carrier.user_drivers.edit', [
                        'carrier' => $carrier,
                        'userDriverDetail' => $responseData['data']['id'],
                        'active_tab' => $tab
                    ])->with('success', 'Conductor procesado correctamente');
                }
                
                return redirect()->route('admin.carrier.user_drivers.index', $carrier)
                    ->with('success', $responseData['message'] ?? 'Conductor procesado correctamente');
            }
            
            // Error handling
            if ($apiResponse->getStatusCode() === 422 && isset($responseData['errors'])) {
                return back()->withErrors($responseData['errors'])->withInput();
            }
            
            return back()->withErrors(['error' => $responseData['message'] ?? 'Error al procesar conductor'])->withInput();
        } catch (\Exception $e) {
            Log::error('Error en store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        try {
            Log::info('Iniciando edición de driver', [
                'carrier_id' => $carrier->id,
                'user_driver_id' => $userDriverDetail->id,
            ]);

            // Verificar que el conductor pertenece al carrier
            if ($userDriverDetail->carrier_id !== $carrier->id) {
                return redirect()->route('admin.carrier.user_drivers.index', $carrier)
                    ->with('error', 'El conductor no pertenece a este transportista');
            }

            // Obtener datos para los selects
            $usStates = Constants::usStates();
            $driverPositions = Constants::driverPositions();
            $referralSources = Constants::referralSources();

            // Determinar qué pestaña está activa
            $activeTab = request()->query('active_tab', 'general');

            // Cargar el usuario driver
            $driver = $userDriverDetail->user;

            // Cargar relaciones según pestaña activa
            $userDriverDetail->load([
                'application',
                'application.details',
                'application.addresses',
                'workHistories',
                'licenses.endorsements',
                'experiences',
                'medicalQualification',
                'trainingSchools',
                'trafficConvictions',
                'accidents'
            ]);

            // Obtener dirección principal y direcciones previas
            $mainAddress = null;
            $previousAddresses = collect();

            if ($userDriverDetail->application) {
                $mainAddress = $userDriverDetail->application->addresses()
                    ->where('primary', true)
                    ->first();

                $previousAddresses = $userDriverDetail->application->addresses()
                    ->where('primary', false)
                    ->orderBy('from_date', 'desc')
                    ->get();
            }

            // Verificar si tiene foto de perfil
            $profilePhotoUrl = $userDriverDetail->getFirstMediaUrl('profile_photo_driver');
            if (empty($profilePhotoUrl)) {
                $profilePhotoUrl = asset('build/default_profile.png');
            }

            Log::info('Preparando para renderizar vista de edición', [
                'activeTab' => $activeTab
            ]);

            // Enviar a la vista
            return view('admin.user_driver.edit', compact(
                'carrier',
                'userDriverDetail',
                'driver',
                'usStates',
                'driverPositions',
                'referralSources',
                'mainAddress',
                'previousAddresses',
                'profilePhotoUrl',
                'activeTab'
            ));
        } catch (\Exception $e) {
            Log::error('Error en edit de driver', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.carrier.user_drivers.index', $carrier)
                ->with('error', 'Error al cargar el driver: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar conductor existente (delegado a la API)
     */
    public function update(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        try {
            // Determinar qué método de API usar según la pestaña activa
            $tab = $request->input('active_tab', 'general');
            $apiMethod = 'update' . ucfirst($tab);

            // Verificar si el método API existe
            if (!method_exists($this->apiController, $apiMethod)) {
                $apiMethod = 'updateGeneral'; // Método por defecto
            }

            // Llamar al método API correspondiente
            $apiResponse = $this->apiController->$apiMethod($request, $carrier, $userDriverDetail);
            $responseData = json_decode($apiResponse->getContent(), true);

            // Verificar si la API tuvo éxito
            if ($apiResponse->getStatusCode() >= 200 && $apiResponse->getStatusCode() < 300) {
                // Si la API devuelve una URL de redirección, usarla
                if (isset($responseData['data']['redirect_url'])) {
                    return redirect($responseData['data']['redirect_url'])
                        ->with('success', $responseData['message'] ?? 'Conductor actualizado correctamente');
                }

                // Redireccionar según la pestaña actual
                $nextTab = $this->getNextTab($tab);

                return redirect()->route('admin.carrier.user_drivers.edit', [
                    'carrier' => $carrier,
                    'userDriverDetail' => $userDriverDetail->id,
                    'active_tab' => $nextTab
                ])->with('success', $responseData['message'] ?? 'Información actualizada correctamente');
            }

            // Si hubo errores de validación
            if ($apiResponse->getStatusCode() === 422 && isset($responseData['errors'])) {
                return back()->withErrors($responseData['errors'])->withInput();
            }

            // Otros errores
            return back()->withErrors(['error' => $responseData['message'] ?? 'Error al actualizar conductor'])->withInput();
        } catch (\Exception $e) {
            Log::error('Error delegando a API en update', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Error del sistema: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Eliminar conductor (delegado a la API)
     */
    public function destroy(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        try {
            // Delegar a la API
            $apiResponse = $this->apiController->destroy(request(), $carrier, $userDriverDetail);
            $responseData = json_decode($apiResponse->getContent(), true);

            if ($apiResponse->getStatusCode() >= 200 && $apiResponse->getStatusCode() < 300) {
                return redirect()->route('admin.carrier.user_drivers.index', $carrier)
                    ->with('success', $responseData['message'] ?? 'Conductor eliminado correctamente');
            }

            // Si hubo error
            return redirect()->route('admin.carrier.user_drivers.index', $carrier)
                ->with('error', $responseData['message'] ?? 'Error al eliminar conductor');
        } catch (\Exception $e) {
            Log::error('Error delegando a API en destroy', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('admin.carrier.user_drivers.index', $carrier)
                ->with('error', 'Error del sistema: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar foto de perfil (delegado a la API)
     */
    public function deletePhoto(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        try {
            // Delegar a la API
            $apiResponse = $this->apiController->deletePhoto(request(), $carrier, $userDriverDetail);
            $responseData = json_decode($apiResponse->getContent(), true);

            return response()->json($responseData, $apiResponse->getStatusCode());
        } catch (\Exception $e) {
            Log::error('Error delegando a API en deletePhoto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error del sistema: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Autoguardado (delegado a la API)
     */
    public function autosave(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail = null)
    {
        try {
            // Delegar a la API
            $apiResponse = $this->apiController->autosave($request, $carrier);
            $responseData = json_decode($apiResponse->getContent(), true);

            return response()->json($responseData, $apiResponse->getStatusCode());
        } catch (\Exception $e) {
            Log::error('Error delegando a API en autosave', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error del sistema: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista de debugging
     */
    public function debug_edit(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        return view('admin.user_driver.debug_edit', [
            'carrier' => $carrier,
            'userDriverDetail' => $userDriverDetail
        ]);
    }

    /**
     * Obtener la siguiente pestaña en la secuencia
     */
    private function getNextTab(string $currentTab): string
    {
        $tabs = [
            'general' => 'licenses',
            'licenses' => 'medical',
            'medical' => 'training',
            'training' => 'traffic',
            'traffic' => 'accident',
            'accident' => 'general'
        ];

        return $tabs[$currentTab] ?? 'general';
    }
}
