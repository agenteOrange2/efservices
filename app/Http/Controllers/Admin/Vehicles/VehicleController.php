<?php
namespace App\Http\Controllers\Admin\Vehicles;
use Carbon\Carbon;
use App\Models\Carrier;
use App\Helpers\Constants;
use Illuminate\Http\Request;
use App\Models\ThirdPartyDetail;
use App\Models\UserDriverDetail;
use App\Models\OwnerOperatorDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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
        
        // Filtros
        if ($request->has('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('out_of_service', false)->where('suspended', false);
            } elseif ($request->status === 'out_of_service') {
                $query->where('out_of_service', true);
            } elseif ($request->status === 'suspended') {
                $query->where('suspended', true);
            }
        }
        
        $vehicles = $query->paginate(10);
        
        // Obtener los tipos y marcas de vehículos para los filtros
        $vehicleTypes = VehicleType::orderBy('name')->get();
        $vehicleMakes = VehicleMake::orderBy('name')->get();
        
        return view('admin.vehicles.index', compact('vehicles', 'vehicleTypes', 'vehicleMakes'));
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
            'registration_expiration_date', 'permanent_tag', 'location', 'notes',
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
            'assignmentHistory.user',
            'driverApplicationDetail.application.ownerOperatorDetail',
            'driverApplicationDetail.application.thirdPartyDetail'
        ]);
        
        return view('admin.vehicles.show', compact('vehicle'));
    }
    
    /**
     * Mostrar el formulario para editar un vehículo.
     */
    public function edit(Vehicle $vehicle)
    {
        // Load current driver assignment relationship
        $vehicle->load('currentDriverAssignment.user');
        
        $carriers = Carrier::where('status', 1)->get();
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
        
        // Buscar el driver application detail asociado al vehículo
        $applicationDetail = \App\Models\Admin\Driver\DriverApplicationDetail::where('vehicle_id', $vehicle->id)->first();
        
        if ($applicationDetail) {
            // Cargar detalles según el tipo de propiedad
            if ($vehicle->ownership_type === 'owned') {
                $ownerDetails = OwnerOperatorDetail::where('driver_application_id', $applicationDetail->driver_application_id)->first();
                
                Log::info('Cargando detalles de owner operator para edición', [
                    'vehicle_id' => $vehicle->id,
                    'owner_details_found' => $ownerDetails ? true : false
                ]);
            } 
            else if ($vehicle->ownership_type === 'third-party') {
                $thirdPartyDetails = ThirdPartyDetail::where('driver_application_id', $applicationDetail->driver_application_id)->first();
                
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
        
        // Create or update driver_application_details record based on ownership type
        if ($request->ownership_type === 'owned' || $originalOwnershipType === 'third-party') {
            try {
                // Get the driver application detail
                $applicationDetail = \App\Models\Admin\Driver\DriverApplicationDetail::where('vehicle_id', $vehicle->id)->first();
                
                if (!$applicationDetail) {
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
                    
                    \Illuminate\Support\Facades\Log::info('Created driver application (update)', [
                        'driver_application_id' => $driverApplication->id,
                        'user_id' => $userId,
                        'vehicle_id' => $vehicle->id
                    ]);
                    
                    // Create a new driver_application_details record with all required fields
                    $detailData = [
                        'driver_application_id' => $driverApplication->id,
                        'vehicle_id' => $vehicle->id,
                        'applying_position' => $request->ownership_type === 'owned' ? 'owner_operator' : 'third_party_driver',
                        'applying_location' => $request->location ?? 'Unknown',
                        'eligible_to_work' => true,
                        'can_speak_english' => true,
                        'has_twic_card' => false,
                        'how_did_hear' => 'other',
                        'expected_pay' => 0.00,
                        'has_work_history' => false,
                        'has_unemployment_periods' => false,
                        'has_completed_employment_history' => false,
                    ];
                    
                    // Create the basic record using mass assignment
                    $driverApplicationDetail = \App\Models\Admin\Driver\DriverApplicationDetail::create($detailData);
                    
                    // Ahora guardar los campos específicos en sus respectivas tablas
                    if ($request->ownership_type === 'owned') {
                        // Guardar en la tabla owner_operator_details
                        $ownerDetails = new OwnerOperatorDetail([
                            'driver_application_id' => $driverApplication->id,
                            'owner_name' => $request->owner_name,
                            'owner_phone' => $request->owner_phone,
                            'owner_email' => $request->owner_email,
                            'contract_agreed' => true,
                            'vehicle_id' => $vehicle->id
                        ]);
                        $ownerDetails->save();
                        
                        Log::info('Owner operator details saved (update)', [
                            'driver_application_id' => $driverApplication->id,
                            'owner_name' => $request->owner_name
                        ]);
                    } 
                    else if ($request->ownership_type === 'third-party') {
                        // Guardar en la tabla third_party_details
                        $thirdPartyDetails = new ThirdPartyDetail([
                            'driver_application_id' => $driverApplication->id,
                            'third_party_name' => $request->third_party_name,
                            'third_party_phone' => $request->third_party_phone,
                            'third_party_email' => $request->third_party_email,
                            'third_party_dba' => $request->third_party_dba ?? '',
                            'third_party_address' => $request->third_party_address ?? '',
                            'third_party_contact' => $request->third_party_contact ?? '',
                            'third_party_fein' => $request->third_party_fein ?? '',
                            'email_sent' => false,
                            'vehicle_id' => $vehicle->id
                        ]);
                        $thirdPartyDetails->save();
                        
                        Log::info('Third party details saved (update)', [
                            'driver_application_id' => $driverApplication->id,
                            'third_party_name' => $request->third_party_name
                        ]);
                    }
                    
                    // Send verification email for third-party company driver
                    if ($request->ownership_type === 'third-party') {
                        $emailSent = $this->sendThirdPartyVerificationEmail(
                            $vehicle,
                            $request->third_party_name,
                            $request->third_party_email,
                            $request->third_party_phone,
                            $driverApplication->id
                        );
                        
                        // Update email_sent field in driver_application_details
                        if ($emailSent) {
                            $driverApplicationDetail->email_sent = true;
                            $driverApplicationDetail->save();
                            
                            \Illuminate\Support\Facades\Log::info('Email sent to third party (update)', [
                                'third_party_email' => $request->third_party_email,
                                'vehicle_id' => $vehicle->id,
                                'driver_application_id' => $driverApplication->id
                            ]);
                        }
                    }
                    
                    // Log success
                    \Illuminate\Support\Facades\Log::info('Successfully created driver application details for vehicle (update)', [
                        'vehicle_id' => $vehicle->id,
                        'driver_application_id' => $driverApplication->id,
                        'ownership_type' => $request->ownership_type
                    ]);
                } else {
                    // Solo actualizamos campos básicos en la tabla driver_application_details
                    $applicationDetail->applying_position = $request->ownership_type === 'owned' ? 'owner_operator' : 'third_party_driver';
                    $applicationDetail->save();
                    
                    // Ahora actualizar los campos específicos en sus respectivas tablas
                    if ($request->ownership_type === 'owned') {
                        // Actualizar o crear en la tabla owner_operator_details
                        $ownerDetails = OwnerOperatorDetail::updateOrCreate(
                            ['driver_application_id' => $applicationDetail->driver_application_id],
                            [
                                'owner_name' => $request->owner_name,
                                'owner_phone' => $request->owner_phone,
                                'owner_email' => $request->owner_email,
                                'contract_agreed' => true,
                                'vehicle_id' => $vehicle->id
                            ]
                        );
                        
                        Log::info('Owner operator details updated', [
                            'driver_application_id' => $applicationDetail->driver_application_id,
                            'owner_name' => $request->owner_name
                        ]);
                    } 
                    else if ($request->ownership_type === 'third-party') {
                        // Actualizar o crear en la tabla third_party_details
                        $thirdPartyDetails = ThirdPartyDetail::updateOrCreate(
                            ['driver_application_id' => $applicationDetail->driver_application_id],
                            [
                                'third_party_name' => $request->third_party_name,
                                'third_party_phone' => $request->third_party_phone,
                                'third_party_email' => $request->third_party_email,
                                'third_party_dba' => $request->third_party_dba ?? '',
                                'third_party_address' => $request->third_party_address ?? '',
                                'third_party_contact' => $request->third_party_contact ?? '',
                                'third_party_fein' => $request->third_party_fein ?? '',
                                'email_sent' => $request->has('email_sent') && $request->email_sent ? 1 : 0,
                                'vehicle_id' => $vehicle->id
                            ]
                        );
                        
                        Log::info('Third party details updated', [
                            'driver_application_id' => $applicationDetail->driver_application_id,
                            'third_party_name' => $request->third_party_name
                        ]);
                    }
                    
                    // Log success
                    \Illuminate\Support\Facades\Log::info('Successfully updated driver application details for vehicle', [
                        'vehicle_id' => $vehicle->id,
                        'driver_application_id' => $applicationDetail->driver_application_id,
                        'ownership_type' => $request->ownership_type
                    ]);
                }
            } catch (\Exception $e) {
                // Log the error
                \Illuminate\Support\Facades\Log::error('Error updating driver application details for vehicle', [
                    'vehicle_id' => $vehicle->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        // Si es third-party y se ha marcado para reenviar el correo, enviar correo de verificación
        if ($request->ownership_type === 'third-party' && $request->has('email_sent') && $request->email_sent) {
            // Get the driver application detail we just created
            $applicationDetail = \App\Models\Admin\Driver\DriverApplicationDetail::where('vehicle_id', $vehicle->id)->first();
            
            if ($applicationDetail) {
                // Buscar los detalles de third party en la tabla correcta
                $thirdPartyDetail = ThirdPartyDetail::where('driver_application_id', $applicationDetail->driver_application_id)->first();
                
                if ($thirdPartyDetail && $thirdPartyDetail->third_party_email) {
                    $this->sendThirdPartyVerificationEmail(
                        $vehicle,
                        $thirdPartyDetail->third_party_name,
                        $thirdPartyDetail->third_party_email,
                        $thirdPartyDetail->third_party_phone,
                        $applicationDetail->driver_application_id
                    );
                    
                    // Update the email_sent flag en la tabla third_party_details
                    $thirdPartyDetail->email_sent = 1;
                    $thirdPartyDetail->save();
                    
                    Log::info('Email sent to third party after update', [
                        'third_party_email' => $thirdPartyDetail->third_party_email,
                        'vehicle_id' => $vehicle->id
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
    private function sendThirdPartyVerificationEmail($vehicle, $thirdPartyName, $thirdPartyEmail, $thirdPartyPhone, $driverApplicationId)
    {
        try {
            // Obtener datos del driver desde la aplicación del conductor
            $driverName = '';
            $driverId = 0;
            
            // Obtener la aplicación del conductor
            $driverApplication = \App\Models\Admin\Driver\DriverApplication::find($driverApplicationId);
            if ($driverApplication && $driverApplication->user) {
                // Obtener el UserDriverDetail asociado al usuario de la aplicación
                $userDriverDetail = \App\Models\UserDriverDetail::where('user_id', $driverApplication->user_id)->first();
                
                if ($userDriverDetail) {
                    $driverName = $driverApplication->user->name;
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
                'driver_application_id' => $driverApplicationId,
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
                    $driverApplicationId
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
     * Synchronize ownership_type with applying_position in driver application
     */
    public function syncOwnershipWithApplyingPosition($vehicleId, $newOwnershipType)
    {
        try {
            $vehicle = Vehicle::find($vehicleId);
            if (!$vehicle) {
                return false;
            }

            // Get corresponding applying_position
            $applyingPosition = $this->mapOwnershipToApplyingPosition($newOwnershipType);

            // Find and update driver application details
            $applicationDetail = \App\Models\Admin\Driver\DriverApplicationDetail::where('vehicle_id', $vehicleId)->first();
            if ($applicationDetail) {
                $applicationDetail->applying_position = $applyingPosition;
                $applicationDetail->save();

                Log::info('Synchronized ownership_type with applying_position', [
                    'vehicle_id' => $vehicleId,
                    'ownership_type' => $newOwnershipType,
                    'applying_position' => $applyingPosition
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error synchronizing ownership with applying position', [
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
     * Mostrar formulario para asignar tipo de conductor
     */
    public function assignDriverType(Vehicle $vehicle)
    {
        // Cargar datos del conductor asignado si existe
        $driverData = null;
        
        Log::info('AssignDriverType - Iniciando carga de datos', [
            'vehicle_id' => $vehicle->id,
            'user_driver_detail_id' => $vehicle->user_driver_detail_id
        ]);
        
        // Get available users for driver selection
        $availableUsers = \App\Models\User::with('userDriverDetail')
            ->whereHas('userDriverDetail')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
        
        // Get current driver assignment if exists
        $currentAssignment = $vehicle->currentDriverAssignment();
        
        // Primero, verificar si ya existe un owner operator para este vehículo
        $existingOwnerOperator = \App\Models\OwnerOperatorDetail::where('vehicle_id', $vehicle->id)->first();
        
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
        }
        elseif ($vehicle->user_driver_detail_id) {
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
        
        return view('admin.vehicles.assign-driver-type', compact('vehicle', 'driverData', 'availableUsers', 'currentAssignment'));
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

        // Crear detalles de la aplicación
        // Mapear los valores del formulario a applying_position
        $ownershipToApplyingMapping = [
            'company_driver' => 'driver',
            'owner_operator' => 'owner_operator', 
            'third_party' => 'third_party_driver',
            'other' => 'other'
        ];
        
        $applyingPosition = $ownershipToApplyingMapping[$request->ownership_type] ?? 'other';
        
        Log::info('StoreDriverType - Mapeo de ownership_type a applying_position', [
            'ownership_type' => $request->ownership_type,
            'applying_position' => $applyingPosition
        ]);
        $detailData = [
            'driver_application_id' => $driverApplication->id,
            'vehicle_id' => $vehicle->id,
            'applying_position' => $applyingPosition,
            'applying_location' => $request->applying_location ?? 'TX', // Default location
            'eligible_to_work' => true,
            'can_speak_english' => true,
            'has_twic_card' => false,
            'how_did_hear' => 'admin_assignment',
            'expected_pay' => 0.00,
        ];
        
        // Agregar applying_position_other si el tipo es 'other'
        if ($request->ownership_type === 'other' && $request->has('applying_position_other')) {
            $detailData['applying_position_other'] = $request->applying_position_other;
        }
        
        Log::info('StoreDriverType - Creando o actualizando DriverApplicationDetail', $detailData);
        
        $driverApplicationDetail = \App\Models\Admin\Driver\DriverApplicationDetail::updateOrCreate(
            [
                'driver_application_id' => $driverApplication->id,
                'vehicle_id' => $vehicle->id
            ],
            $detailData
        );

        Log::info('StoreDriverType - Procesando tipo de ownership', [
            'ownership_type' => $request->ownership_type
        ]);
        
        // Crear registros específicos según el tipo
        if ($request->ownership_type === 'company_driver') {
            Log::info('StoreDriverType - Procesando company_driver - No se requieren registros adicionales');
        } elseif ($request->ownership_type === 'owner_operator') {
            $ownerName = trim(($request->owner_first_name ?? '') . ' ' . ($request->owner_last_name ?? ''));
            
            Log::info('StoreDriverType - Creando OwnerOperatorDetail', [
                'owner_name' => $ownerName,
                'owner_phone' => $request->owner_phone,
                'owner_email' => $request->owner_email
            ]);
            
            \App\Models\OwnerOperatorDetail::updateOrCreate(
                [
                    'driver_application_id' => $driverApplication->id,
                    'vehicle_id' => $vehicle->id
                ],
                [
                    'owner_name' => $ownerName,
                    'owner_phone' => $request->owner_phone,
                    'owner_email' => $request->owner_email,
                    'contract_agreed' => true,
                    'vehicle_id' => $vehicle->id
                ]
            );
        } elseif ($request->ownership_type === 'third_party') {
            Log::info('StoreDriverType - Creando ThirdPartyDetail', [
                'third_party_name' => $request->third_party_name,
                'third_party_phone' => $request->third_party_phone,
                'third_party_email' => $request->third_party_email
            ]);
            
            $thirdPartyDetail = ThirdPartyDetail::updateOrCreate(
                [
                    'driver_application_id' => $driverApplication->id,
                    'vehicle_id' => $vehicle->id
                ],
                [
                    'third_party_name' => $request->third_party_name,
                    'third_party_phone' => $request->third_party_phone,
                    'third_party_email' => $request->third_party_email,
                    'third_party_dba' => $request->third_party_dba ?? '',
                    'third_party_address' => $request->third_party_address ?? '',
                    'third_party_contact' => $request->third_party_contact_person ?? '',
                    'third_party_fein' => $request->third_party_fein ?? '',
                    'email_sent' => false,
                    'vehicle_id' => $vehicle->id
                ]
            );

            // Enviar correo de verificación
            $emailSent = $this->sendThirdPartyVerificationEmail(
                $vehicle,
                $request->third_party_name,
                $request->third_party_email,
                $request->third_party_phone,
                $driverApplication->id
            );

            if ($emailSent) {
                $thirdPartyDetail->email_sent = true;
                $thirdPartyDetail->save();
            }
        }
        
        Log::info('StoreDriverType - Proceso completado exitosamente', [
            'vehicle_id' => $vehicle->id,
            'ownership_type' => $request->ownership_type,
            'driver_application_id' => $driverApplication->id,
            'driver_application_detail_id' => $driverApplicationDetail->id
        ]);

        return redirect()->route('admin.vehicles.show', $vehicle->id)
            ->with('success', 'Tipo de conductor asignado exitosamente');
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
}
