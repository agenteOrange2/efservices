<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\UserDriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\ThirdPartyDetail;
use App\Models\Admin\Vehicle\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DriverTypeController extends Controller
{
    /**
     * Display a listing of the driver types.
     */
    public function index(Request $request)
    {
        try {
            $query = DriverApplication::with([
                'details',
                'ownerOperatorDetail',
                'thirdPartyDetail'
            ]);
            
            // Aplicar filtros
            if ($request->filled('search_term')) {
                $searchTerm = '%' . $request->search_term . '%';
                $query->where(function($q) use ($searchTerm) {
                    $q->whereHas('details.vehicle', function($vehicleQuery) use ($searchTerm) {
                        $vehicleQuery->where('unit_number', 'like', $searchTerm)
                                   ->orWhere('vin', 'like', $searchTerm)
                                   ->orWhere('make', 'like', $searchTerm)
                                   ->orWhere('model', 'like', $searchTerm);
                    })
                    ->orWhereHas('ownerOperatorDetail', function($ownerQuery) use ($searchTerm) {
                        $ownerQuery->where('first_name', 'like', $searchTerm)
                                 ->orWhere('last_name', 'like', $searchTerm)
                                 ->orWhere('license_number', 'like', $searchTerm);
                    })
                    ->orWhereHas('thirdPartyDetail', function($thirdPartyQuery) use ($searchTerm) {
                        $thirdPartyQuery->where('first_name', 'like', $searchTerm)
                                      ->orWhere('last_name', 'like', $searchTerm)
                                      ->orWhere('license_number', 'like', $searchTerm);
                    });
                });
            }
            
            if ($request->filled('ownership_filter')) {
                $query->whereHas('details.vehicle', function($vehicleQuery) use ($request) {
                    $vehicleQuery->where('ownership_type', $request->ownership_filter);
                });
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Ordenar resultados
            $sortField = $request->get('sort_field', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            if (in_array($sortField, ['created_at', 'updated_at'])) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $driverTypes = $query->paginate(15);
            
            return view('admin.driver-types.index', compact('driverTypes'));
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@index: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Error loading driver types: ' . $e->getMessage());
        }
    }

    /**
     * Get driver types data for AJAX requests (Tabulator)
     */
    public function getData(Request $request)
    {
        return $this->getDriverTypesData($request);
    }

    /**
     * Get driver types data for AJAX requests (Tabulator)
     */
    private function getDriverTypesData(Request $request)
    {
        try {
            // Obtener solo las aplicaciones de conductores que tienen detalles asociados
            $query = DriverApplication::with([
                'details',
                'details.vehicle',
                'details.vehicle.carrier',
                'ownerOperatorDetail',
                'thirdPartyDetail'
            ])->whereHas('details'); // Solo mostrar aplicaciones que tienen detalles

            // Aplicar filtros si existen
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('ownerOperatorDetail', function($subQ) use ($search) {
                        $subQ->where('owner_name', 'like', "%{$search}%")
                             ->orWhere('company_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('thirdPartyDetail', function($subQ) use ($search) {
                        $subQ->where('driver_name', 'like', "%{$search}%")
                             ->orWhere('company_name', 'like', "%{$search}%");
                    });
                });
            }

            $driverApplications = $query->orderBy('created_at', 'desc')->get();

            // Formatear los datos para Tabulator
            $data = $driverApplications->map(function($application) {
                // Determinar el tipo de ownership basado en los detalles existentes
                $ownershipType = 'other';
                $userDriverDetail = null;
                $ownerOperatorDetail = null;
                $thirdPartyDetail = null;

                if ($application->ownerOperatorDetail) {
                    $ownershipType = 'owner_operator';
                    $ownerOperatorDetail = $application->ownerOperatorDetail;
                } elseif ($application->thirdPartyDetail) {
                    $ownershipType = 'third_party';
                    $thirdPartyDetail = $application->thirdPartyDetail;
                }

                // Preparar información del vehículo
                $vehicle = null;
                if ($application->details && $application->details->vehicle) {
                    $vehicleData = $application->details->vehicle;
                    $vehicle = [
                        'id' => $vehicleData->id,
                        'unit_number' => $vehicleData->company_unit_number ?? 'N/A',
                        'make' => $vehicleData->make ?? '',
                        'model' => $vehicleData->model ?? '',
                        'carrier' => $vehicleData->carrier ? ['name' => $vehicleData->carrier->name] : null
                    ];
                }

                return [
                    'id' => $application->id,
                    'ownership_type' => $ownershipType,
                    'vehicle' => $vehicle,
                    'user_driver_detail' => $userDriverDetail,
                    'owner_operator_detail' => $ownerOperatorDetail,
                    'third_party_detail' => $thirdPartyDetail,
                    'created_at' => $application->created_at->format('Y-m-d H:i:s'),
                    'actions' => $application->id
                ];
            });

            Log::info('DriverTypes AJAX: Retrieved ' . $data->count() . ' driver applications');

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error in getDriverTypesData: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Error al cargar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified driver type.
     */
    public function show(DriverApplication $driverApplication)
    {
        try {
            $driverApplication->load([
                'userDriverDetail',
                'ownerOperatorDetail',
                'thirdPartyDetail',
                'details'
            ]);

            return view('admin.driver-types.show', compact('driverApplication'));
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@show: ' . $e->getMessage());
            return redirect()->route('admin.driver-types.index')->with('error', 'Error al mostrar el tipo de conductor: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified driver type.
     */
    public function edit(DriverApplication $driverApplication)
    {
        try {
            $driverApplication->load([
                'userDriverDetail',
                'ownerOperatorDetail',
                'thirdPartyDetail',
                'details'
            ]);

            // Obtener todos los vehículos para el select
            $vehicles = Vehicle::with('carrier')->orderBy('company_unit_number')->get();

            return view('admin.driver-types.edit', compact('driverApplication', 'vehicles'));
        } catch (\Exception $e) {
            Log::error('Error in DriverTypeController@edit: ' . $e->getMessage());
            return redirect()->route('admin.driver-types.index')->with('error', 'Error al cargar el formulario de edición: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified driver type in storage.
     */
    public function update(Request $request, DriverApplication $driverApplication)
    {
        try {
            Log::info('DriverTypeController@update: Starting update for driver application ID: ' . $driverApplication->id);
            Log::info('DriverTypeController@update: Request data: ', $request->all());

            // Validación básica
            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'ownership_type' => 'required|in:company_driver,owner_operator,third_party,other',
            ]);

            DB::beginTransaction();

            // Mapear ownership_type a applying_position
            $ownershipMapping = [
                'company_driver' => 'driver',
                'owner_operator' => 'owned',
                'third_party' => 'third_party_driver',
                'other' => 'other'
            ];

            // Actualizar los detalles de la aplicación
            if (!$driverApplication->details) {
                $driverApplication->details()->create([
                    'vehicle_id' => $request->vehicle_id,
                    'applying_position' => $ownershipMapping[$request->ownership_type] ?? $request->ownership_type,
                ]);
            } else {
                $driverApplication->details->update([
                    'vehicle_id' => $request->vehicle_id,
                    'applying_position' => $ownershipMapping[$request->ownership_type] ?? $request->ownership_type,
                ]);
            }

            // Actualizar detalles específicos según el tipo
            switch ($request->ownership_type) {
                case 'company_driver':
                    $this->updateCompanyDriverDetails($request, $driverApplication);
                    break;
                case 'owner_operator':
                    $this->updateOwnerOperatorDetails($request, $driverApplication);
                    break;
                case 'third_party':
                    $this->updateThirdPartyDetails($request, $driverApplication);
                    break;
                case 'other':
                    $this->updateOtherDetails($request, $driverApplication);
                    break;
            }

            DB::commit();
            Log::info('DriverTypeController@update: Successfully updated driver application ID: ' . $driverApplication->id);

            return redirect()->route('admin.driver-types.index')
                ->with('success', 'Tipo de conductor actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in DriverTypeController@update: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el tipo de conductor: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified driver type from storage.
     */
    public function destroy(DriverApplication $driverApplication)
    {
        try {
            DB::beginTransaction();

            // Eliminar registros relacionados
            if ($driverApplication->userDriverDetail) {
                $driverApplication->userDriverDetail->delete();
            }
            if ($driverApplication->ownerOperatorDetail) {
                $driverApplication->ownerOperatorDetail->delete();
            }
            if ($driverApplication->thirdPartyDetail) {
                $driverApplication->thirdPartyDetail->delete();
            }

            // Eliminar la aplicación principal
            $driverApplication->delete();

            DB::commit();
            Log::info('DriverTypeController@destroy: Successfully deleted driver application ID: ' . $driverApplication->id);

            return redirect()->route('admin.driver-types.index')
                ->with('success', 'Tipo de conductor eliminado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in DriverTypeController@destroy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar el tipo de conductor: ' . $e->getMessage());
        }
    }

    /**
     * Update company driver details
     */
    private function updateCompanyDriverDetails(Request $request, DriverApplication $driverApplication)
    {
        $validatedData = $request->validate([
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'driver_email' => 'nullable|email|max:255',
            'license_number' => 'nullable|string|max:50',
            'license_expiration' => 'nullable|date',
        ]);

        UserDriverDetail::updateOrCreate(
            ['driver_application_id' => $driverApplication->id],
            $validatedData
        );
    }

    /**
     * Update owner operator details
     */
    private function updateOwnerOperatorDetails(Request $request, DriverApplication $driverApplication)
    {
        $validatedData = $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_phone' => 'nullable|string|max:20',
            'owner_email' => 'nullable|email|max:255',
            'license_number' => 'nullable|string|max:50',
            'license_expiration' => 'nullable|date',
            'mc_number' => 'nullable|string|max:50',
            'dot_number' => 'nullable|string|max:50',
        ]);

        OwnerOperatorDetail::updateOrCreate(
            ['driver_application_id' => $driverApplication->id],
            $validatedData
        );
    }

    /**
     * Update third party details
     */
    private function updateThirdPartyDetails(Request $request, DriverApplication $driverApplication)
    {
        $validatedData = $request->validate([
            'third_party_name' => 'required|string|max:255',
            'third_party_phone' => 'nullable|string|max:20',
            'third_party_email' => 'nullable|email|max:255',
            'third_party_address' => 'nullable|string|max:500',
            'license_number' => 'nullable|string|max:50',
            'license_expiration' => 'nullable|date',
        ]);

        ThirdPartyDetail::updateOrCreate(
            ['driver_application_id' => $driverApplication->id],
            $validatedData
        );
    }

    /**
     * Update other details
     */
    private function updateOtherDetails(Request $request, DriverApplication $driverApplication)
    {
        $validatedData = $request->validate([
            'other_details' => 'nullable|string|max:1000',
        ]);

        // Para 'other' podemos usar UserDriverDetail o crear un modelo específico
        UserDriverDetail::updateOrCreate(
            ['driver_application_id' => $driverApplication->id],
            $validatedData
        );
    }
}