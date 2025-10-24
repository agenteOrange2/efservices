<?php
namespace App\Http\Controllers\Admin\Vehicles;
use Carbon\Carbon;
use App\Models\Carrier;
use App\Helpers\Constants;
use Illuminate\Http\Request;
use App\Models\ThirdPartyDetail;
use App\Models\UserDriverDetail;
use App\Models\OwnerOperatorDetail;
use App\Models\Admin\Vehicle\VehicleDriverAssignment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMake;
use App\Models\Admin\Vehicle\VehicleType;
use Illuminate\Support\Facades\Validator;
use App\Mail\ThirdPartyVehicleVerification;
use App\Models\Admin\Vehicle\VehicleServiceItem;

class VehicleController extends Controller
{
    /**
     * Mostrar una lista de todos los vehículos.
     */
    public function index(Request $request)
    {
        $query = Vehicle::with(['carrier', 'currentDriverAssignment.user']);
        
        // Filtro por Carrier
        if ($request->filled('carrier_id') && $request->carrier_id !== '') {
            $query->where('carrier_id', $request->carrier_id);
        }

        // Filtro por Status
        if ($request->filled('status') && $request->status !== '') {
            if ($request->status === 'active') {
                $query->where('out_of_service', false)->where('suspended', false);
            } elseif ($request->status === 'out_of_service') {
                $query->where('out_of_service', true);
            } elseif ($request->status === 'suspended') {
                $query->where('suspended', true);
            }
        }

        // Filtro por Vehicle Type
        if ($request->filled('vehicle_type') && $request->vehicle_type !== '') {
            $query->where('type', $request->vehicle_type);
        }

        // Filtro por Brand/Make
        if ($request->filled('vehicle_make') && $request->vehicle_make !== '') {
            $query->where('make', $request->vehicle_make);
        }

        // Búsqueda mejorada
        if ($request->filled('search') && $request->search !== '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('company_unit_number', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('make', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('model', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('vin', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('registration_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Paginación con cantidad personalizable
        $perPage = $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }
        
        $vehicles = $query->paginate($perPage)->appends($request->query());
        
        // Obtener datos para los filtros
        $carriers = Carrier::where('status', 1)->orderBy('name')->get();
        $vehicleTypes = VehicleType::orderBy('name')->get();
        $vehicleMakes = VehicleMake::orderBy('name')->get();
        
        return view('admin.vehicles.index', compact('vehicles', 'carriers', 'vehicleTypes', 'vehicleMakes'));
    }

    /**
     * Mostrar el formulario para crear un nuevo vehículo.
     */
    public function create()
    {
        $carriers = Carrier::where('status', 1)->get();
        $vehicleMakes = VehicleMake::all();
        $vehicleTypes = VehicleType::all();
        $usStates = Constants::usStates();

        return view('admin.vehicles.create', compact('carriers', 'vehicleMakes', 'vehicleTypes', 'usStates'));
    }

    /**
     * Crear un nuevo Vehicle Make via AJAX
     */
    public function createMake(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_makes,name'
        ]);

        $make = VehicleMake::create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'make' => $make
        ]);
    }

    /**
     * Crear un nuevo Vehicle Type via AJAX
     */
    public function createType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_types,name'
        ]);

        $type = VehicleType::create([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'type' => $type
        ]);
    }

    /**
     * Almacenar un vehículo recién creado.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'carrier_id' => 'required|exists:carriers,id',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'company_unit_number' => 'nullable|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vin' => 'required|string|size:17|unique:vehicles,vin',
            'gvwr' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:255',
            'registration_state' => 'required|string|max:255',
            'registration_expiration_date' => 'required|date|after:today',
            'annual_inspection_expiration_date' => 'required|date|after:today',
            'fuel_type' => 'required|string|in:Diesel,Gasoline,CNG,LNG,Electric,Hybrid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create the vehicle (sólo campos básicos del vehículo)
        $vehicleData = $request->only([
            'carrier_id', 'make', 'model', 'type', 'year', 'vin', 'color',
            'company_unit_number', 'gvwr', 'tire_size', 'fuel_type',
            'irp_apportioned_plate', 'registration_state', 'registration_number',
            'registration_expiration_date', 'annual_inspection_expiration_date', 'permanent_tag', 'location', 'notes',
            'out_of_service', 'out_of_service_date', 'suspended', 'suspended_date'
        ]);
        
        $vehicle = Vehicle::create($vehicleData);
        
        // Procesar y guardar los service_items si existen
        if ($request->has('service_items') && is_array($request->service_items)) {
            foreach ($request->service_items as $serviceItem) {
                // Solo guardar si hay datos significativos
                if (!empty($serviceItem['service_date']) || !empty($serviceItem['service_tasks']) || 
                    !empty($serviceItem['vendor_mechanic']) || !empty($serviceItem['description'])) {
                    
                    // Crear un array con solo los campos permitidos en el modelo
                    $serviceItemData = [
                        'vehicle_id' => $vehicle->id,
                        'unit' => $serviceItem['unit'] ?? null,
                        'service_date' => $serviceItem['service_date'] ?? null,
                        'next_service_date' => $serviceItem['next_service_date'] ?? null,
                        'service_tasks' => $serviceItem['service_tasks'] ?? null,
                        'vendor_mechanic' => $serviceItem['vendor_mechanic'] ?? null,
                        'description' => $serviceItem['description'] ?? null,
                        'cost' => $serviceItem['cost'] ?? null,
                        'odometer' => $serviceItem['odometer'] ?? null
                    ];
                    
                    // Crear el service item vinculado al vehículo
                    \App\Models\Admin\Vehicle\VehicleServiceItem::create($serviceItemData);
                    
                    Log::info('Service item creado para vehículo', [
                        'vehicle_id' => $vehicle->id,
                        'service_date' => $serviceItem['service_date'] ?? null,
                        'service_tasks' => $serviceItem['service_tasks'] ?? null
                    ]);
                }
            }
        }

        // Redirect to driver type assignment page
        return redirect()->route('admin.vehicles.assign-driver-type', $vehicle->id)
            ->with('success', 'Vehicle created successfully. Please assign a driver type.');
    }

    /**
     * Mostrar un vehículo específico.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'carrier', 
            'maintenances',
            'currentDriverAssignment.user',
            'assignmentHistory.user'
        ]);
        
        return view('admin.vehicles.show', compact('vehicle'));
    }

    /**
     * Mostrar página dedicada del historial de asignaciones de conductores
     */
    public function driverAssignmentHistory(Vehicle $vehicle)
    {
        // Cargar el vehículo con todas las relaciones necesarias para el historial
        $vehicle->load([
            'carrier',
            'assignmentHistory' => function($query) {
                $query->with([
                    'user.driverDetail',
                    'ownerOperatorDetail',
                    'thirdPartyDetail',
                    'companyDriverDetail'
                ])->orderBy('start_date', 'desc');
            }
        ]);

        // Paginar el historial de asignaciones
        $assignmentHistory = VehicleDriverAssignment::where('vehicle_id', $vehicle->id)
            ->with([
                'user.driverDetail',
                'ownerOperatorDetail',
                'thirdPartyDetail',
                'companyDriverDetail'
            ])
            ->orderBy('start_date', 'desc')
            ->paginate(15);

        return view('admin.vehicles.driver-assignment-history', compact('vehicle', 'assignmentHistory'));
    }
    
    /**
     * Mostrar el formulario para editar un vehículo.
     */
    public function edit(Vehicle $vehicle)
    {
        // Load current driver assignment relationship (both active and pending)
        $vehicle->load(['currentDriverAssignment.user', 'currentDriverAssignment.driver.user', 'carrier']);
        
        // Debug logging para carrier_id
        Log::info('Vehicle edit - Debug carrier_id', [
            'vehicle_id' => $vehicle->id,
            'carrier_id' => $vehicle->carrier_id,
            'carrier_relationship' => $vehicle->carrier ? $vehicle->carrier->toArray() : null,
            'vehicle_attributes' => $vehicle->getAttributes()
        ]);
        
        $carriers = Carrier::where('status', 1)->get();
        
        // Debug logging para carriers disponibles
        Log::info('Vehicle edit - Available carriers', [
            'carriers_count' => $carriers->count(),
            'carrier_ids' => $carriers->pluck('id')->toArray(),
            'carrier_names' => $carriers->pluck('name', 'id')->toArray(),
            'looking_for_carrier_id' => $vehicle->carrier_id,
            'carrier_10_exists' => $carriers->where('id', 10)->count() > 0
        ]);
        $vehicleMakes = VehicleMake::all();
        $vehicleTypes = VehicleType::all();
        $usStates = Constants::usStates();
        
        // Cargar historial de mantenimiento del vehículo
        $maintenanceHistory = \App\Models\Admin\Vehicle\VehicleMaintenance::where('vehicle_id', $vehicle->id)
            ->orderBy('service_date', 'desc')
            ->get();
        
        // Cargar detalles adicionales del tipo de propiedad
        $ownerDetails = null;
        $thirdPartyDetails = null;
        
        // Buscar el vehicle driver assignment asociado al vehículo (incluyendo pending y active)
        $vehicleAssignment = VehicleDriverAssignment::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['active', 'pending'])
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($vehicleAssignment) {
            // Cargar detalles según el tipo de propiedad
            if ($vehicle->ownership_type === 'owned') {
                $ownerDetails = OwnerOperatorDetail::where('vehicle_driver_assignment_id', $vehicleAssignment->id)->first();
                
                Log::info('Cargando detalles de owner operator para edición', [
                    'vehicle_id' => $vehicle->id,
                    'owner_details_found' => $ownerDetails ? true : false
                ]);
            } 
            else if ($vehicle->ownership_type === 'third-party') {
                $thirdPartyDetails = ThirdPartyDetail::where('vehicle_driver_assignment_id', $vehicleAssignment->id)->first();
                
                Log::info('Cargando detalles de third party para edición', [
                    'vehicle_id' => $vehicle->id,
                    'third_party_details_found' => $thirdPartyDetails ? true : false
                ]);
            }
        }

        return view('admin.vehicles.edit', compact(
            'vehicle', 
            'carriers', 
            'vehicleMakes', 
            'vehicleTypes', 
            'usStates',
            'ownerDetails',
            'thirdPartyDetails',
            'maintenanceHistory'
        ));
    }
    public function update(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'carrier_id' => 'required|exists:carriers,id',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'company_unit_number' => 'nullable|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vin' => 'required|string|size:17|unique:vehicles,vin,' . $vehicle->id,
            'gvwr' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:255',
            'registration_state' => 'required|string|max:255',
            'registration_expiration_date' => 'required|date|after:today',
            'fuel_type' => 'required|string|in:Diesel,Gasoline,CNG,LNG,Electric,Hybrid',
            'location' => 'nullable|string|max:255',
            'ownership_type' => 'required|in:owned,leased,third-party,unassigned',

            'owner_name' => 'nullable|required_if:ownership_type,owned|string|max:255',
            'owner_phone' => 'nullable|required_if:ownership_type,owned|string|max:255',
            'owner_email' => 'nullable|required_if:ownership_type,owned|email|max:255',
            'third_party_name' => 'nullable|required_if:ownership_type,third-party|string|max:255',
            'third_party_phone' => 'nullable|required_if:ownership_type,third-party|string|max:255',
            'third_party_email' => 'nullable|required_if:ownership_type,third-party|email|max:255',
            'third_party_dba' => 'nullable|string|max:255',
            'third_party_address' => 'nullable|string|max:255',
            'third_party_contact' => 'nullable|string|max:255',
            'third_party_fein' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Guardar o crear marca de vehículo si no existe
        if ($request->has('make') && !VehicleMake::where('name', $request->make)->exists()) {
            VehicleMake::create(['name' => $request->make]);
        }
        
        // Guardar o crear tipo de vehículo si no existe
        if ($request->has('type') && !VehicleType::where('name', $request->type)->exists()) {
            VehicleType::create(['name' => $request->type]);
        }
        // Guardar el tipo de propiedad original
        $originalOwnershipType = $request->input('ownership_type');
        
        // Update the vehicle
        $vehicle->update($request->all());
        
        // Procesar y guardar/actualizar los service_items si existen
        if ($request->has('service_items') && is_array($request->service_items)) {
            // Opción: eliminar los service items existentes y crear nuevos
            // Esto es más simple pero menos eficiente que actualizar los existentes
            \App\Models\Admin\Vehicle\VehicleServiceItem::where('vehicle_id', $vehicle->id)->delete();
            
            foreach ($request->service_items as $serviceItem) {
                // Solo guardar si hay datos significativos
                if (!empty($serviceItem['service_date']) || !empty($serviceItem['service_tasks']) || 
                    !empty($serviceItem['vendor_mechanic']) || !empty($serviceItem['description'])) {
                    
                    // Crear un array con solo los campos permitidos en el modelo
                    $serviceItemData = [
                        'vehicle_id' => $vehicle->id,
                        'unit' => $serviceItem['unit'] ?? null,
                        'service_date' => $serviceItem['service_date'] ?? null,
                        'next_service_date' => $serviceItem['next_service_date'] ?? null,
                        'service_tasks' => $serviceItem['service_tasks'] ?? null,
                        'vendor_mechanic' => $serviceItem['vendor_mechanic'] ?? null,
                        'description' => $serviceItem['description'] ?? null,
                        'cost' => $serviceItem['cost'] ?? null,
                        'odometer' => $serviceItem['odometer'] ?? null
                    ];
                    
                    // Crear el service item vinculado al vehículo
                    \App\Models\Admin\Vehicle\VehicleServiceItem::create($serviceItemData);
                    
                    \Illuminate\Support\Facades\Log::info('Service item actualizado para vehículo', [
                        'vehicle_id' => $vehicle->id,
                        'service_date' => $serviceItem['service_date'] ?? null,
                        'service_tasks' => $serviceItem['service_tasks'] ?? null
                    ]);
                }
            }
        }
        
        // Create or update vehicle driver assignment based on ownership type
        if ($request->ownership_type === 'owned' || $request->ownership_type === 'third-party') {
            try {
                // Get or create vehicle driver assignment
                $vehicleAssignment = VehicleDriverAssignment::where('vehicle_id', $vehicle->id)->first();
                
                if (!$vehicleAssignment) {
                    // Use the current authenticated user for driver applications
                    $userId = \Illuminate\Support\Facades\Auth::id();
                    
                    // If no authenticated user, use the first admin user as fallback
                    if (!$userId) {
                        $adminUser = \App\Models\User::where('is_admin', true)->first();
                        $userId = $adminUser ? $adminUser->id : 1;
                        \Illuminate\Support\Facades\Log::info('Using fallback admin user_id (update)', ['user_id' => $userId]);
                    } else {
                        \Illuminate\Support\Facades\Log::info('Using authenticated user_id (update)', ['user_id' => $userId]);
                    }
                    
                    // Create a new driver application with the user_id
                    $driverApplication = new \App\Models\Admin\Driver\DriverApplication();
                    $driverApplication->user_id = $userId;
                    $driverApplication->status = 'pending';
                    $driverApplication->save();
                    
                    // Create vehicle driver assignment
                    $vehicleAssignment = VehicleDriverAssignment::create([
                        'driver_application_id' => $driverApplication->id,
                        'vehicle_id' => $vehicle->id,
                        'driver_type' => $request->ownership_type === 'owned' ? 'owner_operator' : 'third_party',
                        'status' => 'pending',
                        'assigned_at' => now()
                    ]);
                    
                    \Illuminate\Support\Facades\Log::info('Created vehicle driver assignment (update)', [
                        'assignment_id' => $vehicleAssignment->id,
                        'vehicle_id' => $vehicle->id,
                        'driver_type' => $vehicleAssignment->driver_type
                    ]);
                }
                
                // Update assignment details based on ownership type
                if ($request->ownership_type === 'owned') {
                    // Update or create owner operator details
                    $ownerDetails = OwnerOperatorDetail::updateOrCreate(
                        ['vehicle_driver_assignment_id' => $vehicleAssignment->id],
                        [
                            'owner_name' => $request->owner_name,
                            'owner_phone' => $request->owner_phone,
                            'owner_email' => $request->owner_email,
                            'contract_agreed' => true
                        ]
                    );
                    
                    Log::info('Owner operator details updated', [
                        'assignment_id' => $vehicleAssignment->id,
                        'owner_name' => $request->owner_name
                    ]);
                } 
                else if ($request->ownership_type === 'third-party') {
                    // Update or create third party details
                    $thirdPartyDetails = ThirdPartyDetail::updateOrCreate(
                        ['vehicle_driver_assignment_id' => $vehicleAssignment->id],
                        [
                            'third_party_name' => $request->third_party_name,
                            'third_party_phone' => $request->third_party_phone,
                            'third_party_email' => $request->third_party_email,
                            'third_party_dba' => $request->third_party_dba ?? '',
                            'third_party_address' => $request->third_party_address ?? '',
                            'third_party_contact' => $request->third_party_contact ?? '',
                            'third_party_fein' => $request->third_party_fein ?? '',
                            'email_sent' => $request->has('email_sent') && $request->email_sent ? 1 : 0
                        ]
                    );
                    
                    Log::info('Third party details updated', [
                        'assignment_id' => $vehicleAssignment->id,
                        'third_party_name' => $request->third_party_name
                    ]);
                }
                
                // Log success
                \Illuminate\Support\Facades\Log::info('Successfully updated vehicle assignment details', [
                    'vehicle_id' => $vehicle->id,
                    'assignment_id' => $vehicleAssignment->id,
                    'ownership_type' => $request->ownership_type
                ]);
            } catch (\Exception $e) {
                // Log the error
                \Illuminate\Support\Facades\Log::error('Error updating vehicle assignment details', [
                    'vehicle_id' => $vehicle->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        // Si es third-party y se ha marcado para reenviar el correo, enviar correo de verificación
        if ($request->ownership_type === 'third-party' && $request->has('email_sent') && $request->email_sent) {
            // Buscar el VehicleDriverAssignment para este vehículo
            $vehicleAssignment = VehicleDriverAssignment::where('vehicle_id', $vehicle->id)
                ->where('status', 'active')
                ->first();
            
            if ($vehicleAssignment) {
                // Buscar los detalles de third party usando el vehicle_driver_assignment_id
                $thirdPartyDetail = ThirdPartyDetail::where('vehicle_driver_assignment_id', $vehicleAssignment->id)->first();
                
                if ($thirdPartyDetail && $thirdPartyDetail->third_party_email) {
                    $this->sendThirdPartyVerificationEmail(
                        $vehicle,
                        $thirdPartyDetail->third_party_name,
                        $thirdPartyDetail->third_party_email,
                        $thirdPartyDetail->third_party_phone,
                        $vehicleAssignment->id
                    );
                    
                    // Update the email_sent flag en la tabla third_party_details
                    $thirdPartyDetail->email_sent = 1;
                    $thirdPartyDetail->save();
                    
                    Log::info('Email sent to third party after update', [
                        'third_party_email' => $thirdPartyDetail->third_party_email,
                        'vehicle_id' => $vehicle->id,
                        'vehicle_assignment_id' => $vehicleAssignment->id
                    ]);
                }
            }
        }
        
        return redirect()->route('admin.vehicles.show', $vehicle->id)
            ->with('success', 'Vehículo actualizado exitosamente');
    }

    /**
     * Eliminar un vehículo específico.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        
        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehículo eliminado exitosamente');
    }

    /**
     * Obtener drivers filtrados por carrier vía AJAX
     */
    public function getDriversByCarrier($carrierId)
    {
        // Obtener solo drivers activos para el carrier seleccionado
        $drivers = UserDriverDetail::with('user')
            ->where('carrier_id', $carrierId)
            ->where('status', 1) // Solo drivers activos (status=1 que significa activo)
            ->get();
        
        return response()->json($drivers);
    }
    
    /**
     * Enviar correo de verificación a third party company driver
     */
    private function sendThirdPartyVerificationEmail($vehicle, $thirdPartyName, $thirdPartyEmail, $thirdPartyPhone, $vehicleAssignmentId)
    {
        try {
            // Obtener datos del driver desde el vehicle assignment
            $driverName = '';
            $driverId = 0;
            
            // Obtener el vehicle assignment
            $vehicleAssignment = VehicleDriverAssignment::find($vehicleAssignmentId);
            if ($vehicleAssignment && $vehicleAssignment->driverApplication && $vehicleAssignment->driverApplication->user) {
                // Obtener el UserDriverDetail asociado al usuario de la aplicación
                $userDriverDetail = \App\Models\UserDriverDetail::where('user_id', $vehicleAssignment->driverApplication->user_id)->first();
                
                if ($userDriverDetail) {
                    $driverName = $vehicleAssignment->driverApplication->user->name;
                    $driverId = $userDriverDetail->id;
                    
                    // Actualizar el user_driver_detail_id del vehículo para que el CustomPathGenerator funcione correctamente
                    $vehicle->user_driver_detail_id = $driverId;
                    $vehicle->save();
                    
                    // Registrar la actualización del vehículo
                    \Illuminate\Support\Facades\Log::info('Vehículo actualizado con user_driver_detail_id correcto', [
                        'vehicle_id' => $vehicle->id,
                        'user_driver_detail_id' => $driverId
                    ]);
                }
            }
            
            // Generar token único para la verificación usando el modelo VehicleVerificationToken
            $token = \App\Models\VehicleVerificationToken::generateToken();
            $expiresAt = now()->addDays(7);
            
            // Guardar el token de verificación en la base de datos
            $verification = \App\Models\VehicleVerificationToken::create([
                'token' => $token,
                'vehicle_driver_assignment_id' => $vehicleAssignmentId,
                'vehicle_id' => $vehicle->id,
                'third_party_name' => $thirdPartyName,
                'third_party_email' => $thirdPartyEmail,
                'third_party_phone' => $thirdPartyPhone,
                'expires_at' => $expiresAt,
            ]);
            
            // Registrar la creación del token para depuración
            \Illuminate\Support\Facades\Log::info('Token de verificación creado', [
                'vehicle_id' => $vehicle->id,
                'token' => $token
            ]);
            
            // Convertir el objeto vehículo a un array asociativo para la plantilla de correo
            $vehicleData = [
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'vin' => $vehicle->vin,
                'type' => $vehicle->type,
                'registration_state' => $vehicle->registration_state,
                'registration_number' => $vehicle->registration_number
            ];
            
            // Registrar los datos del vehículo para depuración
            \Illuminate\Support\Facades\Log::info('Datos del vehículo para correo', $vehicleData);
            
            // Enviar correo
            \Illuminate\Support\Facades\Mail::to($thirdPartyEmail)
                ->queue(new \App\Mail\ThirdPartyVehicleVerification(
                    $thirdPartyName,
                    $driverName,
                    $vehicleData,
                    $token,
                    $driverId, // Este es el ID del conductor (user_driver_detail_id)
                    $vehicleAssignmentId
                ));
            
            // Registrar en el log
            \Illuminate\Support\Facades\Log::info('Correo enviado a third party', [
                'vehicle_id' => $vehicle->id,
                'third_party_email' => $thirdPartyEmail,
                'token' => $token
            ]);
            
            return true;
        } catch (\Exception $e) {
            // Registrar error en el log
            \Illuminate\Support\Facades\Log::error('Error al enviar correo a third party', [
                'vehicle_id' => $vehicle->id,
                'third_party_email' => $thirdPartyEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString() // Añadir stack trace para mejor depuración
            ]);
            
            return false;
        }
    }

    /**
     * Map applying_position to ownership_type using Constants helper
     */
    public function mapApplyingPositionToOwnership($applyingPosition)
    {
        return Constants::mapApplyingPositionToOwnership($applyingPosition);
    }

    /**
     * Map ownership_type to applying_position using Constants helper
     */
    public function mapOwnershipToApplyingPosition($ownershipType)
    {
        return Constants::mapOwnershipToApplyingPosition($ownershipType);
    }

    /**
     * Synchronize ownership_type with assignment_type in vehicle driver assignment
     */
    public function syncOwnershipWithApplyingPosition($vehicleId, $newOwnershipType)
    {
        try {
            $vehicle = Vehicle::find($vehicleId);
            if (!$vehicle) {
                return false;
            }

            // Get corresponding assignment_type
            $assignmentType = $newOwnershipType === 'owned' ? 'owner_operator' : 'third_party';

            // Find and update vehicle driver assignment
            $vehicleAssignment = VehicleDriverAssignment::where('vehicle_id', $vehicleId)->first();
            if ($vehicleAssignment) {
                $vehicleAssignment->assignment_type = $assignmentType;
                $vehicleAssignment->save();

                Log::info('Synchronized ownership_type with assignment_type', [
                    'vehicle_id' => $vehicleId,
                    'ownership_type' => $newOwnershipType,
                    'assignment_type' => $assignmentType
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error synchronizing ownership with assignment type', [
                'vehicle_id' => $vehicleId,
                'ownership_type' => $newOwnershipType,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Validate consistency between ownership_type and applying_position
     */
    public function validateOwnershipConsistency($ownershipType, $applyingPosition)
    {
        $expectedApplyingPosition = $this->mapOwnershipToApplyingPosition($ownershipType);
        $expectedOwnershipType = $this->mapApplyingPositionToOwnership($applyingPosition);

        return [
            'is_consistent' => ($expectedApplyingPosition === $applyingPosition && $expectedOwnershipType === $ownershipType),
            'expected_applying_position' => $expectedApplyingPosition,
            'expected_ownership_type' => $expectedOwnershipType,
            'current_applying_position' => $applyingPosition,
            'current_ownership_type' => $ownershipType
        ];
    }

    /**
     * Show unassigned vehicles.
     */
    public function unassignedVehicles()
    {
        Log::info('UnassignedVehicles - Iniciando consulta de vehículos sin asignar');
        
        // Obtener vehículos que no tienen asignaciones activas
        $unassignedVehicles = Vehicle::with(['make', 'vehicle_type', 'carrier'])
            ->whereDoesntHave('driverAssignments', function ($query) {
                $query->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        Log::info('UnassignedVehicles - Encontrados ' . $unassignedVehicles->total() . ' vehículos sin asignar');
        
        return view('admin.vehicles.unassigned-vehicles', compact('unassignedVehicles'));
    }
    
    /**
     * Endpoint AJAX para cargar datos del conductor sin recargar página
     */
    public function getDriverData(Request $request, Vehicle $vehicle)
    {
        try {
            $selectedDriverId = $request->get('driver_id');
            
            if (!$selectedDriverId) {
                return response()->json(['error' => 'Driver ID is required'], 400);
            }
            
            $selectedDriver = \App\Models\UserDriverDetail::with(['user', 'licenses'])
                ->whereHas('user', function($query) use ($selectedDriverId) {
                    $query->where('id', $selectedDriverId);
                })
                ->where('carrier_id', $vehicle->carrier_id)
                ->first();
                
            if (!$selectedDriver || !$selectedDriver->user) {
                return response()->json(['error' => 'Driver not found'], 404);
            }
            
            $primaryLicense = $selectedDriver->licenses()->first();
            
            // Construir nombre completo
            $fullName = trim($selectedDriver->user->name ?? '');
            if ($selectedDriver->middle_name) {
                $fullName .= ' ' . trim($selectedDriver->middle_name);
            }
            if ($selectedDriver->last_name) {
                $fullName .= ' ' . trim($selectedDriver->last_name);
            }
            
            // Separar nombre y apellido
            $nameParts = explode(' ', $fullName, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            // Formatear fecha de expiración
            $licenseExpiration = '';
            if ($primaryLicense && $primaryLicense->expiration_date) {
                try {
                    $licenseExpiration = \Carbon\Carbon::parse($primaryLicense->expiration_date)->format('m/d/Y');
                } catch (\Exception $e) {
                    $licenseExpiration = $primaryLicense->expiration_date;
                }
            }
            
            $driverData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $selectedDriver->phone ?? '',
                'email' => $selectedDriver->user->email ?? '',
                'license_number' => $primaryLicense ? ($primaryLicense->license_number ?? '') : '',
                'license_class' => $primaryLicense ? ($primaryLicense->license_class ?? '') : '',
                'license_state' => $primaryLicense ? ($primaryLicense->state_of_issue ?? '') : '',
                'license_expiration' => $licenseExpiration,
                'ownership_type' => 'owner_operator'
            ];
            
            return response()->json(['success' => true, 'data' => $driverData]);
            
        } catch (\Exception $e) {
            Log::error('Error loading driver data via AJAX', [
                'error' => $e->getMessage(),
                'vehicle_id' => $vehicle->id,
                'driver_id' => $selectedDriverId ?? null
            ]);
            
            return response()->json(['error' => 'Error loading driver data'], 500);
        }
    }

    /**
     * Mostrar formulario para asignar tipo de conductor
     */
    public function assignDriverType(Request $request, Vehicle $vehicle)
    {
        // Cargar el assignment actual del vehículo con sus relaciones
        $vehicle->load(['currentDriverAssignment.user', 'currentDriverAssignment.thirdPartyDetail', 'currentDriverAssignment.ownerOperatorDetail']);
        
        // Cargar datos del conductor asignado si existe
        $driverData = null;
        $thirdPartyData = [];
        $currentAssignment = $vehicle->currentDriverAssignment;
        
        // Determinar el assignment_type basándose en las relaciones existentes si no está definido
        if ($currentAssignment && !$currentAssignment->driver_type) {
            $assignmentType = null;
            
            if ($currentAssignment->ownerOperatorDetail) {
                $assignmentType = 'owner_operator';
            } elseif ($currentAssignment->thirdPartyDetail) {
                $assignmentType = 'third_party';
            } elseif ($currentAssignment->companyDriverDetail) {
                $assignmentType = 'company_driver';
            }
            
            // Actualizar el assignment con el tipo determinado
            if ($assignmentType) {
                $currentAssignment->update(['driver_type' => $assignmentType]);
                $currentAssignment->refresh();
            }
        }
        
        // Información de aplicación previa - removida por aislamiento de tablas
        $applicationInfo = null;
        $consistencyCheck = null;
        
        Log::info('AssignDriverType - Iniciando carga de datos', [
            'vehicle_id' => $vehicle->id,
            'user_driver_detail_id' => $vehicle->user_driver_detail_id,
            'selected_driver' => $request->get('selected_driver'),
            'current_assignment_id' => $currentAssignment ? $currentAssignment->id : null,
            'current_assignment_type' => $currentAssignment ? $currentAssignment->driver_type : null,
            'has_application_info' => $applicationInfo !== null,
            'consistency_check' => $consistencyCheck
        ]);
        
        // Si hay un conductor seleccionado, cargar sus datos
        $selectedDriverId = $request->get('selected_driver');
        if ($selectedDriverId) {
            $selectedDriver = \App\Models\UserDriverDetail::with(['user', 'licenses'])
                ->whereHas('user', function($query) use ($selectedDriverId) {
                    $query->where('id', $selectedDriverId);
                })
                ->where('carrier_id', $vehicle->carrier_id)
                ->first();
                
            if ($selectedDriver && $selectedDriver->user) {
                $primaryLicense = $selectedDriver->licenses()->first();
                
                // Construir nombre completo
                $fullName = trim($selectedDriver->user->name ?? '');
                if ($selectedDriver->middle_name) {
                    $fullName .= ' ' . trim($selectedDriver->middle_name);
                }
                if ($selectedDriver->last_name) {
                    $fullName .= ' ' . trim($selectedDriver->last_name);
                }
                
                // Separar nombre y apellido
                $nameParts = explode(' ', $fullName, 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';
                
                // Formatear fecha de expiración
                $licenseExpiration = '';
                if ($primaryLicense && $primaryLicense->expiration_date) {
                    try {
                        $licenseExpiration = \Carbon\Carbon::parse($primaryLicense->expiration_date)->format('m/d/Y');
                    } catch (\Exception $e) {
                        $licenseExpiration = $primaryLicense->expiration_date;
                    }
                }
                
                $driverData = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $selectedDriver->phone ?? '',
                    'email' => $selectedDriver->user->email ?? '',
                    'license_number' => $primaryLicense ? ($primaryLicense->license_number ?? '') : '',
                    'license_class' => $primaryLicense ? ($primaryLicense->license_class ?? '') : '',
                    'license_state' => $primaryLicense ? ($primaryLicense->state_of_issue ?? '') : '',
                    'license_expiration' => $licenseExpiration,
                    'selected_driver_id' => $selectedDriverId
                ];
                
                Log::info('AssignDriverType - Datos del conductor seleccionado cargados', $driverData);
            }
        }
        
        // Get ALL available drivers for this vehicle's carrier (company, owner operator, third party)
        $availableDrivers = \App\Models\UserDriverDetail::with(['user'])
            ->where('carrier_id', $vehicle->carrier_id)
            ->where('application_completed', 1)
            ->whereHas('user', function($query) {
                $query->where('status', 1);
            })
            ->get()
            ->map(function($driverDetail) {
                return (object) [
                    'id' => $driverDetail->user->id,
                    'name' => $driverDetail->user->name,
                    'email' => $driverDetail->user->email
                ];
            });
        
        // Si no hay conductor seleccionado, cargar datos existentes del assignment actual
        if (!$selectedDriverId && $currentAssignment) {
            // Cargar datos de third party si existe
            if ($currentAssignment->driver_type === 'third_party' && $currentAssignment->thirdPartyDetail) {
                $thirdPartyDetail = $currentAssignment->thirdPartyDetail;
                $thirdPartyData = [
                    'third_party_name' => $thirdPartyDetail->third_party_name ?? '',
                    'third_party_phone' => $thirdPartyDetail->third_party_phone ?? '',
                    'third_party_email' => $thirdPartyDetail->third_party_email ?? '',
                    'third_party_dba' => $thirdPartyDetail->third_party_dba ?? '',
                    'third_party_address' => $thirdPartyDetail->third_party_address ?? '',
                    'third_party_contact' => $thirdPartyDetail->third_party_contact ?? '',
                    'third_party_fein' => $thirdPartyDetail->third_party_fein ?? '',
                ];
                
                Log::info('AssignDriverType - Datos de Third Party cargados', $thirdPartyData);
            }
        }
        
        // Si no hay conductor seleccionado, cargar datos existentes
        if (!$selectedDriverId) {
            // Primero, verificar si ya existe un owner operator para este vehículo
            // Buscar a través de vehicle_driver_assignments ya que owner_operator_details no tiene vehicle_id directo
            $existingOwnerOperator = \App\Models\OwnerOperatorDetail::whereHas('assignment', function($query) use ($vehicle) {
                $query->where('vehicle_id', $vehicle->id);
            })->first();
            
            if ($existingOwnerOperator) {
                Log::info('AssignDriverType - Owner Operator existente encontrado', [
                    'owner_operator_id' => $existingOwnerOperator->id,
                    'owner_name' => $existingOwnerOperator->owner_name,
                    'owner_phone' => $existingOwnerOperator->owner_phone,
                    'owner_email' => $existingOwnerOperator->owner_email
                ]);
                
                // Formatear fecha de expiración de licencia si existe
                $licenseExpiration = '';
                if ($existingOwnerOperator->owner_license_expiry) {
                    try {
                        $licenseExpiration = \Carbon\Carbon::parse($existingOwnerOperator->owner_license_expiry)->format('m/d/Y');
                    } catch (\Exception $e) {
                        $licenseExpiration = $existingOwnerOperator->owner_license_expiry;
                    }
                }
                
                $driverData = [
                    'first_name' => $existingOwnerOperator->owner_name ? explode(' ', $existingOwnerOperator->owner_name, 2)[0] : '',
                    'last_name' => $existingOwnerOperator->owner_name && strpos($existingOwnerOperator->owner_name, ' ') !== false ? 
                        explode(' ', $existingOwnerOperator->owner_name, 2)[1] : '',
                    'phone' => $existingOwnerOperator->owner_phone ?? '',
                    'email' => $existingOwnerOperator->owner_email ?? '',
                    'license_number' => $existingOwnerOperator->owner_license_number ?? '',
                    'license_class' => '', // No se almacena en owner_operator_details
                    'license_state' => $existingOwnerOperator->owner_license_state ?? '',
                    'license_expiration' => $licenseExpiration,
                    'ownership_type' => 'owner_operator' // Indicar que es owner operator
                ];
                
                Log::info('AssignDriverType - Datos de Owner Operator cargados', $driverData);
            } elseif ($vehicle->user_driver_detail_id) {
            $driver = \App\Models\UserDriverDetail::with(['user', 'licenses'])
                ->find($vehicle->user_driver_detail_id);
            
            Log::info('AssignDriverType - Driver encontrado', [
                'driver_id' => $driver ? $driver->id : null,
                'driver_exists' => $driver ? true : false,
                'user_exists' => ($driver && $driver->user) ? true : false,
                'licenses_count' => $driver ? $driver->licenses->count() : 0
            ]);
            
            if ($driver && $driver->user) {
                $primaryLicense = $driver->licenses()->first();
                
                Log::info('AssignDriverType - Datos de licencia', [
                    'primary_license_exists' => $primaryLicense ? true : false,
                    'license_number' => $primaryLicense ? ($primaryLicense->license_number ?? 'N/A') : 'N/A',
                    'license_class' => $primaryLicense ? ($primaryLicense->license_class ?? 'N/A') : 'N/A',
                    'state_of_issue' => $primaryLicense ? ($primaryLicense->state_of_issue ?? 'N/A') : 'N/A',
                    'expiration_date' => $primaryLicense ? ($primaryLicense->expiration_date ?? 'N/A') : 'N/A',
                    'expiration_date_formatted' => $primaryLicense && $primaryLicense->expiration_date ? 
                        \Carbon\Carbon::parse($primaryLicense->expiration_date)->format('Y-m-d') : 'N/A'
                ]);
                
                // Construir nombre completo como en ApplicationStep.php
                $fullName = trim($driver->user->name ?? '');
                if ($driver->middle_name) {
                    $fullName .= ' ' . trim($driver->middle_name);
                }
                if ($driver->last_name) {
                    $fullName .= ' ' . trim($driver->last_name);
                }
                
                // Separar nombre y apellido para los campos individuales
                $nameParts = explode(' ', $fullName, 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';
                
                // Formatear fecha de expiración
                $licenseExpiration = '';
                if ($primaryLicense && $primaryLicense->expiration_date) {
                    try {
                        $licenseExpiration = \Carbon\Carbon::parse($primaryLicense->expiration_date)->format('m/d/Y');
                    } catch (\Exception $e) {
                        Log::error('Error formateando fecha de expiración', [
                            'expiration_date' => $primaryLicense->expiration_date,
                            'error' => $e->getMessage()
                        ]);
                        $licenseExpiration = $primaryLicense->expiration_date;
                    }
                }
                
                $driverData = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $driver->phone ?? '',
                    'email' => $driver->user->email ?? '',
                    'license_number' => $primaryLicense ? ($primaryLicense->license_number ?? '') : '',
                    'license_class' => $primaryLicense ? ($primaryLicense->license_class ?? '') : '',
                    'license_state' => $primaryLicense ? ($primaryLicense->state_of_issue ?? '') : '',
                    'license_expiration' => $licenseExpiration
                ];
                
                Log::info('AssignDriverType - Datos finales del conductor', $driverData);
            }
        } else {
            Log::info('AssignDriverType - No hay user_driver_detail_id asignado al vehículo ni owner operator existente');
        }
        }
        
        // Obtener el driver_type actual para preseleccionar el dropdown
        $currentDriverType = $currentAssignment ? $currentAssignment->driver_type : null;
        
        return view('admin.vehicles.assign-driver-type', compact('vehicle', 'driverData', 'availableDrivers', 'currentAssignment', 'thirdPartyData', 'currentDriverType'));
    }

    /**
     * Procesar la asignación de tipo de conductor
     */
    public function storeDriverType(Request $request, Vehicle $vehicle)
    {
        // Log de todos los datos recibidos
        Log::info('StoreDriverType - Datos recibidos del formulario', [
            'vehicle_id' => $vehicle->id,
            'all_request_data' => $request->all(),
            'ownership_type' => $request->ownership_type,
            'method' => $request->method(),
            'url' => $request->url()
        ]);
        
        try {
            $request->validate([
                'ownership_type' => 'required|in:company_driver,owner_operator,third_party,other',
                'owner_first_name' => 'nullable|string|max:255',
                'owner_last_name' => 'nullable|string|max:255', 
                'owner_phone' => 'nullable|string|max:20',
                'owner_email' => 'nullable|email|max:255',
                'owner_license_number' => 'nullable|string|max:255',
                'owner_license_state' => 'nullable|string|max:10',
                'owner_license_expiry' => 'nullable|string|max:20',
                'third_party_name' => 'nullable|string|max:255',
                'third_party_phone' => 'nullable|string|max:20',
                'third_party_email' => 'nullable|email|max:255',
                'third_party_dba' => 'nullable|string|max:255',
                'third_party_fein' => 'nullable|string|max:50',
                'third_party_address' => 'nullable|string|max:500',
                'third_party_contact_person' => 'nullable|string|max:255',
                'third_party_contact_phone' => 'nullable|string|max:20',
                'applying_position_other' => 'nullable|string|max:255',
            ]);
            
            Log::info('StoreDriverType - Validación pasada exitosamente');
         } catch (\Illuminate\Validation\ValidationException $e) {
             Log::error('StoreDriverType - Error de validación', [
                 'errors' => $e->errors(),
                 'input' => $request->all()
             ]);
             throw $e;
         }

        // Mapear los valores del formulario a los valores de la base de datos
        $ownershipMapping = [
            'company_driver' => 'leased',
            'owner_operator' => 'owned', 
            'third_party' => 'third_party',
            'other' => 'other'
        ];
        
        $dbOwnershipType = $ownershipMapping[$request->ownership_type] ?? 'unassigned';
        
        Log::info('StoreDriverType - Mapeo de ownership_type', [
            'form_value' => $request->ownership_type,
            'db_value' => $dbOwnershipType
        ]);
        
        // Actualizar el ownership_type del vehículo
        $vehicle->ownership_type = $dbOwnershipType;
        $vehicle->save();

        // Buscar o crear aplicación de conductor
        // Primero intentar encontrar por el conductor asignado al vehículo
        $userId = auth()->id();
        if ($vehicle->user_driver_detail_id) {
            $driverDetail = \App\Models\UserDriverDetail::find($vehicle->user_driver_detail_id);
            if ($driverDetail && $driverDetail->user_id) {
                $userId = $driverDetail->user_id;
            }
        }
        
        $driverApplication = \App\Models\Admin\Driver\DriverApplication::where('user_id', $userId)
            ->first();
            
        if (!$driverApplication) {
            $driverApplication = \App\Models\Admin\Driver\DriverApplication::create([
                'user_id' => $userId,
                'status' => 'pending',
            ]);
            
            Log::info('StoreDriverType - Nueva DriverApplication creada', [
                'driver_application_id' => $driverApplication->id,
                'user_id' => $userId,
                'vehicle_id' => $vehicle->id
            ]);
        } else {
            Log::info('StoreDriverType - DriverApplication existente encontrada', [
                'driver_application_id' => $driverApplication->id,
                'user_id' => $userId,
                'vehicle_id' => $vehicle->id
            ]);
        }

        // Buscar user_driver_detail_id si se proporcionó un user_id
        $userDriverDetailId = null;
        if ($request->filled('user_id')) {
            $userDriverDetail = \App\Models\UserDriverDetail::where('user_id', $request->user_id)->first();
            if ($userDriverDetail) {
                $userDriverDetailId = $userDriverDetail->id;
                Log::info('StoreDriverType - UserDriverDetail encontrado', [
                    'user_id' => $request->user_id,
                    'user_driver_detail_id' => $userDriverDetailId
                ]);
            } else {
                Log::warning('StoreDriverType - UserDriverDetail no encontrado para user_id', [
                    'user_id' => $request->user_id
                ]);
            }
        }

        // Buscar asignación activa existente
        $existingAssignment = VehicleDriverAssignment::where('vehicle_id', $vehicle->id)
            ->where('status', 'active')
            ->first();

        // Si existe una asignación activa, finalizarla para crear historial
        if ($existingAssignment) {
            $existingAssignment->update([
                'end_date' => now(),
                'status' => 'inactive'
            ]);
            
            Log::info('StoreDriverType - Asignación anterior finalizada para historial', [
                'previous_assignment_id' => $existingAssignment->id,
                'vehicle_id' => $vehicle->id,
                'previous_driver_type' => $existingAssignment->driver_type,
                'end_date' => $existingAssignment->end_date
            ]);
        }

        // Crear nueva asignación activa
        $assignment = VehicleDriverAssignment::create([
            'vehicle_id' => $vehicle->id,
            'user_driver_detail_id' => $userDriverDetailId,
            'driver_type' => $request->ownership_type,
            'start_date' => now(),
            'status' => 'active'
        ]);
        
        Log::info('StoreDriverType - Nueva asignación de vehículo creada', [
            'assignment_id' => $assignment->id,
            'vehicle_id' => $vehicle->id,
            'driver_type' => $request->ownership_type,
            'had_previous_assignment' => $existingAssignment ? true : false
        ]);

        // Guardar detalles específicos según el tipo de conductor
        try {
            switch ($request->ownership_type) {
                case 'owner_operator':
                    $ownerOperatorDetail = \App\Models\OwnerOperatorDetail::updateOrCreate(
                        ['vehicle_driver_assignment_id' => $assignment->id],
                        [
                            'owner_name' => trim(($request->owner_first_name ?? '') . ' ' . ($request->owner_last_name ?? '')),
                            'owner_phone' => $request->owner_phone,
                            'owner_email' => $request->owner_email,
                            'contract_agreed' => true,
                            'notes' => 'Owner operator assignment created via storeDriverType'
                        ]
                    );
                    
                    Log::info('StoreDriverType - OwnerOperatorDetail guardado', [
                        'assignment_id' => $assignment->id,
                        'owner_operator_detail_id' => $ownerOperatorDetail->id,
                        'owner_name' => $ownerOperatorDetail->owner_name
                    ]);
                    break;
                    
                case 'third_party':
                    $thirdPartyDetail = \App\Models\ThirdPartyDetail::updateOrCreate(
                        ['vehicle_driver_assignment_id' => $assignment->id],
                        [
                            'third_party_name' => $request->third_party_name,
                            'third_party_phone' => $request->third_party_phone,
                            'third_party_email' => $request->third_party_email,
                            'third_party_dba' => $request->third_party_dba,
                            'third_party_fein' => $request->third_party_fein,
                            'third_party_address' => $request->third_party_address,
                            'third_party_contact' => $request->third_party_contact_person,
                            'email_sent' => false,
                            'notes' => 'Third party assignment created via storeDriverType'
                        ]
                    );
                    
                    Log::info('StoreDriverType - ThirdPartyDetail guardado', [
                        'assignment_id' => $assignment->id,
                        'third_party_detail_id' => $thirdPartyDetail->id,
                        'third_party_name' => $thirdPartyDetail->third_party_name
                    ]);
                    break;
                    
                case 'company_driver':
                    $companyDriverDetail = \App\Models\CompanyDriverDetail::updateOrCreate(
                        ['vehicle_driver_assignment_id' => $assignment->id],
                        [
                            'carrier_id' => $vehicle->carrier_id,
                            'notes' => 'Company driver assignment created via storeDriverType'
                        ]
                    );
                    
                    Log::info('StoreDriverType - CompanyDriverDetail guardado', [
                        'assignment_id' => $assignment->id,
                        'company_driver_detail_id' => $companyDriverDetail->id,
                        'carrier_id' => $companyDriverDetail->carrier_id
                    ]);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('StoreDriverType - Error al guardar detalles específicos', [
                'assignment_id' => $assignment->id,
                'driver_type' => $request->ownership_type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Asignación de tipo de conductor completada
        Log::info('StoreDriverType - Asignación de tipo de conductor completada', [
            'vehicle_id' => $vehicle->id,
            'driver_type' => $request->ownership_type,
            'user_driver_detail_id' => $userDriverDetailId,
            'assignment_id' => $assignment->id
        ]);

        // Redirigir a la página del vehículo con mensaje de éxito
        return redirect()->route('admin.vehicles.show', $vehicle->id)
            ->with('success', 'Driver type successfully assigned.');
    }

    /**
     * Assign a driver to a vehicle using the new decoupled system
     */
    public function assignDriver(Request $request, Vehicle $vehicle)
    {
        $assignmentController = new \App\Http\Controllers\Admin\VehicleDriverAssignmentController();
        return $assignmentController->store($request, $vehicle);
    }

    /**
     * Remove a driver assignment from a vehicle
     */
    public function removeDriver(Vehicle $vehicle, $assignmentId)
    {
        $assignmentController = new \App\Http\Controllers\Admin\VehicleDriverAssignmentController();
        return $assignmentController->destroy($vehicle, $assignmentId);
    }

    // API Methods for Vehicle Management
    
    /**
     * API: Create a new Vehicle Make
     */
    public function apiCreateMake(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:vehicle_makes,name'
            ]);

            $make = VehicleMake::create([
                'name' => trim($request->name)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle make created successfully',
                'data' => [
                    'id' => $make->id,
                    'name' => $make->name
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating vehicle make: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the vehicle make'
            ], 500);
        }
    }

    /**
     * API: Create a new Vehicle Type
     */
    public function apiCreateType(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:vehicle_types,name'
            ]);

            $type = VehicleType::create([
                'name' => trim($request->name)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle type created successfully',
                'data' => [
                    'id' => $type->id,
                    'name' => $type->name
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating vehicle type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the vehicle type'
            ], 500);
        }
    }

    /**
     * API: Get all Vehicle Makes
     */
    public function apiGetMakes()
    {
        try {
            $makes = VehicleMake::orderBy('name')->get(['id', 'name']);
            
            return response()->json([
                'success' => true,
                'data' => $makes
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching vehicle makes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching vehicle makes'
            ], 500);
        }
    }

    /**
     * API: Get all Vehicle Types
     */
    public function apiGetTypes()
    {
        try {
            $types = VehicleType::orderBy('name')->get(['id', 'name']);
            
            return response()->json([
                'success' => true,
                'data' => $types
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching vehicle types: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching vehicle types'
            ], 500);
        }
    }

    /**
     * Show available owner operators for vehicle assignment
     */
    public function selectOwnerOperator(Vehicle $vehicle)
    {
        // Get all users with owner operator details filtered by carrier and completed application
        $ownerOperators = UserDriverDetail::with(['user', 'ownerOperatorDetail'])
            ->where('carrier_id', $vehicle->carrier_id)
            ->where('application_completed', 1)
            ->whereHas('ownerOperatorDetail')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->get();

        return view('admin.vehicles.select-owner-operator', compact('vehicle', 'ownerOperators'));
    }

    /**
     * Show available third party drivers for vehicle assignment
     */
    public function selectThirdParty(Vehicle $vehicle)
    {
        // Get all users with third party details filtered by carrier and completed application
        $thirdPartyDrivers = UserDriverDetail::with(['user', 'thirdPartyDetail'])
            ->where('carrier_id', $vehicle->carrier_id)
            ->where('application_completed', 1)
            ->whereHas('thirdPartyDetail')
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            ->get();

        return view('admin.vehicles.select-third-party', compact('vehicle', 'thirdPartyDrivers'));
    }

    /**
     * Show available company drivers for vehicle assignment
     */
    public function selectCompanyDriver(Vehicle $vehicle)
    {
        // Get all users with company driver details filtered by carrier and completed application
        $companyDrivers = UserDriverDetail::with(['user'])
            ->where('carrier_id', $vehicle->carrier_id)
            ->where('application_completed', 1)
            ->whereHas('user', function($query) {
                $query->where('status', 'active');
            })
            // Exclude drivers who already have owner operator or third party details
            ->whereDoesntHave('ownerOperatorDetail')
            ->whereDoesntHave('thirdPartyDetail')
            ->get();

        return view('admin.vehicles.select-company-driver', compact('vehicle', 'companyDrivers'));
    }

    /**
     * Get driver information for AJAX request
     */
    public function getDriverInfo($userDriverDetailId)
    {
        try {
            if (!$userDriverDetailId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver ID is required'
                ], 400);
            }

            $driver = \App\Models\UserDriverDetail::with(['user', 'primaryLicense'])
                ->find($userDriverDetailId);

            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found'
                ], 404);
            }

            $driverInfo = [
                'first_name' => $driver->user->name ?? '',
                'last_name' => $driver->last_name ?? '',
                'phone' => $driver->phone ?? '',
                'email' => $driver->user->email ?? '',
                'license_number' => $driver->primaryLicense->license_number ?? '',
                'state_of_issue' => $driver->primaryLicense->state_of_issue ?? '',
                'expiration_date' => $driver->primaryLicense->expiration_date ? $driver->primaryLicense->expiration_date->format('m/d/Y') : ''
            ];

            return response()->json([
                'success' => true,
                'data' => $driverInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting driver info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching driver information'
            ], 500);
        }
    }

    /**
     * Complete the driver assignment to vehicle
     */
    public function assignToDriver(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'user_driver_detail_id' => 'required|exists:user_driver_details,id',
            'driver_type' => 'required|in:owner_operator,third_party'
        ]);

        try {
            DB::beginTransaction();

            // Terminate any existing active assignments
            VehicleDriverAssignment::where('vehicle_id', $vehicle->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'inactive',
                    'end_date' => now(),
                    'unassigned_by' => auth()->id()
                ]);

            // Get the user driver detail
            $userDriverDetail = UserDriverDetail::with(['user', 'ownerOperatorDetail', 'thirdPartyDetail'])
                ->findOrFail($request->user_driver_detail_id);

            // Create new assignment
            $assignment = VehicleDriverAssignment::create([
                'vehicle_id' => $vehicle->id,
                'user_driver_detail_id' => $request->user_driver_detail_id,
                'driver_type' => $request->driver_type,
                'start_date' => now(),
                'status' => 'active',
                'assigned_by' => auth()->id()
            ]);

            // Update vehicle ownership type based on driver type
            $ownershipTypeMap = [
                'owner_operator' => 'owned',
                'third_party' => 'third_party'
            ];

            $vehicle->update([
                'ownership_type' => $ownershipTypeMap[$request->driver_type],
                'user_driver_detail_id' => $request->user_driver_detail_id
            ]);

            DB::commit();

            Log::info('Vehicle assignment completed', [
                'vehicle_id' => $vehicle->id,
                'user_driver_detail_id' => $request->user_driver_detail_id,
                'driver_type' => $request->driver_type,
                'assignment_id' => $assignment->id
            ]);

            return redirect()->route('admin.vehicles.show', $vehicle->id)
                ->with('success', 'Vehicle successfully assigned to ' . $userDriverDetail->user->name);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error assigning vehicle to driver: ' . $e->getMessage());
            
            return back()->withErrors([
                'assignment' => 'An error occurred while assigning the vehicle. Please try again.'
            ]);
        }
    }
}
