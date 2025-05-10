<?php
namespace App\Http\Controllers\Admin\Vehicles;
use App\Models\Carrier;
use App\Helpers\Constants;
use Illuminate\Http\Request;
use App\Models\UserDriverDetail;
use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMake;
use App\Models\Admin\Vehicle\VehicleType;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\Vehicle\VehicleServiceItem;
use App\Mail\ThirdPartyVehicleVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class VehicleController extends Controller
{
    /**
     * Mostrar una lista de todos los vehículos.
     */
    public function index(Request $request)
    {
        $query = Vehicle::with(['carrier', 'driver']);
        
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
        // No cargamos drivers inicialmente, se cargarán por AJAX según el carrier seleccionado
        $drivers = collect(); 
        $vehicleMakes = VehicleMake::all();
        $vehicleTypes = VehicleType::all();
        $usStates = Constants::usStates();

        return view('admin.vehicles.create', compact('carriers', 'drivers', 'vehicleMakes', 'vehicleTypes', 'usStates'));
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
            'vin' => 'required|string|max:255|unique:vehicles,vin',
            'gvwr' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:255',
            'registration_state' => 'required|string|max:255',
            'registration_expiration_date' => 'required|date',
            'ownership_type' => 'required|in:company,leased,owned,third-party,unassigned',
            'user_driver_detail_id' => 'nullable|exists:user_driver_details,id',
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

        // Prepare vehicle data
        $vehicleData = $request->all();

        // Create the vehicle
        $vehicle = Vehicle::create($request->all());

        // Create or update driver_application_details record based on ownership type
        if ($request->ownership_type === 'owned' || $originalOwnershipType === 'third-party') {
            // Get the user_id from the selected driver if available
            $userId = null;
            
            // First, try to get the user_id from the user_driver_detail_id if it exists
            if ($vehicle->user_driver_detail_id) {
                $userDriverDetail = \App\Models\userDriverDetail::find($vehicle->user_driver_detail_id);
                if ($userDriverDetail && $userDriverDetail->user_id) {
                    $userId = $userDriverDetail->user_id;
                    \Illuminate\Support\Facades\Log::info('Found user_id from user_driver_detail', [
                        'user_driver_detail_id' => $vehicle->user_driver_detail_id,
                        'user_id' => $userId
                    ]);
                }
            }
            
            // If no user_id is available from the driver, use the current authenticated user
            if (!$userId) {
                $userId = \Illuminate\Support\Facades\Auth::id();
                \Illuminate\Support\Facades\Log::info('Using authenticated user_id', ['user_id' => $userId]);
            }
            
            // If still no user_id, use the first admin user as fallback
            if (!$userId) {
                $adminUser = \App\Models\User::where('is_admin', true)->first();
                $userId = $adminUser ? $adminUser->id : 1;
                \Illuminate\Support\Facades\Log::info('Using fallback admin user_id', ['user_id' => $userId]);
            }
            
            // Create a new driver application with the user_id
            $driverApplication = new \App\Models\Admin\Driver\DriverApplication();
            $driverApplication->user_id = $userId;
            $driverApplication->status = 'pending';
            $driverApplication->save();
            
            \Illuminate\Support\Facades\Log::info('Created driver application', [
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
            
            // Add ownership-specific fields
            if ($request->ownership_type === 'owned') {
                $detailData['owner_name'] = $request->owner_name;
                $detailData['owner_phone'] = $request->owner_phone;
                $detailData['owner_email'] = $request->owner_email;
                $detailData['contract_agreed'] = true;
            } else if ($originalOwnershipType === 'third-party') {
                $detailData['third_party_name'] = $request->third_party_name;
                $detailData['third_party_phone'] = $request->third_party_phone;
                $detailData['third_party_email'] = $request->third_party_email;
                $detailData['third_party_dba'] = $request->third_party_dba ?? '';
                $detailData['third_party_address'] = $request->third_party_address ?? '';
                $detailData['third_party_contact'] = $request->third_party_contact ?? '';
                $detailData['third_party_fein'] = $request->third_party_fein ?? '';
                $detailData['email_sent'] = $request->has('email_sent') && $request->email_sent;
            }
            
            // Create the record using mass assignment
            $driverApplicationDetail = \App\Models\Admin\Driver\DriverApplicationDetail::create($detailData);
            
            // Send verification email for third-party company driver
            if ($originalOwnershipType === 'third-party') {
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
                    
                    \Illuminate\Support\Facades\Log::info('Email sent to third party', [
                        'third_party_email' => $request->third_party_email,
                        'vehicle_id' => $vehicle->id,
                        'driver_application_id' => $driverApplication->id
                    ]);
                }
            }
            
            // Log success
            \Illuminate\Support\Facades\Log::info('Successfully created driver application details for vehicle', [
                'vehicle_id' => $vehicle->id,
                'driver_application_id' => $driverApplication->id,
                'ownership_type' => $request->ownership_type
            ]);
        }
        
        // Process and save service items if they exist
        if ($request->has('service_items')) {
            foreach ($request->service_items as $serviceItem) {
                VehicleServiceItem::create([
                    'vehicle_id' => $vehicle->id,
                    'name' => $serviceItem['name'],
                    'description' => $serviceItem['description'] ?? null,
                    'service_date' => $serviceItem['service_date'] ?? null,
                    'next_service_date' => $serviceItem['next_service_date'] ?? null,
                    'status' => $serviceItem['status'] ?? 'pending',
                ]);
            }
        }
        
        return redirect()->route('admin.vehicles.show', $vehicle->id)
            ->with('success', 'Vehículo creado exitosamente');
    }

    /**
     * Mostrar un vehículo específico.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'carrier', 
            'driver', 
            'serviceItems',
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
        $carriers = Carrier::where('status', 1)->get();
        
        // Si ya hay un carrier seleccionado, cargar sus drivers
        if ($vehicle->carrier_id) {
            $drivers = UserDriverDetail::with('user')
                ->where('carrier_id', $vehicle->carrier_id)
                ->where('status', 1)
                ->get();
        } else {
            $drivers = collect();
        }
        
        $vehicleMakes = VehicleMake::all();
        $vehicleTypes = VehicleType::all();
        $usStates = Constants::usStates();

        return view('admin.vehicles.edit', compact('vehicle', 'carriers', 'drivers', 'vehicleMakes', 'vehicleTypes', 'usStates'));
    }

    /**
     * Actualizar un vehículo específico.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'carrier_id' => 'required|exists:carriers,id',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'company_unit_number' => 'nullable|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vin' => 'required|string|max:255|unique:vehicles,vin,' . $vehicle->id,
            'gvwr' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:255',
            'registration_state' => 'required|string|max:255',
            'registration_expiration_date' => 'required|date',
            'ownership_type' => 'required|in:company,leased,owned,third-party,unassigned',
            'user_driver_detail_id' => 'nullable|exists:user_driver_details,id',
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
        
        // Create or update driver_application_details record based on ownership type
        if ($request->ownership_type === 'owned' || $originalOwnershipType === 'third-party') {
            try {
                // Get the driver application detail
                $applicationDetail = \App\Models\Admin\Driver\DriverApplicationDetail::where('vehicle_id', $vehicle->id)->first();
                
                if (!$applicationDetail) {
                    // Get the user_id from the selected driver if available
                    $userId = null;
                    
                    // First, try to get the user_id from the user_driver_detail_id if it exists
                    if ($vehicle->user_driver_detail_id) {
                        try {
                            $userDriverDetail = \App\Models\userDriverDetail::find($vehicle->user_driver_detail_id);
                            if ($userDriverDetail && $userDriverDetail->user_id) {
                                $userId = $userDriverDetail->user_id;
                                \Illuminate\Support\Facades\Log::info('Found user_id from user_driver_detail (update)', [
                                    'user_driver_detail_id' => $vehicle->user_driver_detail_id,
                                    'user_id' => $userId
                                ]);
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error finding user_driver_detail (update)', [
                                'error' => $e->getMessage(),
                                'user_driver_detail_id' => $vehicle->user_driver_detail_id
                            ]);
                        }
                    }
                    
                    // If no user_id is available from the driver, use the current authenticated user
                    if (!$userId) {
                        $userId = \Illuminate\Support\Facades\Auth::id();
                        \Illuminate\Support\Facades\Log::info('Using authenticated user_id (update)', ['user_id' => $userId]);
                    }
                    
                    // If still no user_id, use the first admin user as fallback
                    if (!$userId) {
                        $adminUser = \App\Models\User::where('is_admin', true)->first();
                        $userId = $adminUser ? $adminUser->id : 1;
                        \Illuminate\Support\Facades\Log::info('Using fallback admin user_id (update)', ['user_id' => $userId]);
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
                    
                    // Add ownership-specific fields
                    if ($request->ownership_type === 'owned') {
                        $detailData['owner_name'] = $request->owner_name;
                        $detailData['owner_phone'] = $request->owner_phone;
                        $detailData['owner_email'] = $request->owner_email;
                        $detailData['contract_agreed'] = true;
                    } else if ($originalOwnershipType === 'third-party') {
                        $detailData['third_party_name'] = $request->third_party_name;
                        $detailData['third_party_phone'] = $request->third_party_phone;
                        $detailData['third_party_email'] = $request->third_party_email;
                        $detailData['third_party_dba'] = $request->third_party_dba ?? '';
                        $detailData['third_party_address'] = $request->third_party_address ?? '';
                        $detailData['third_party_contact'] = $request->third_party_contact ?? '';
                        $detailData['third_party_fein'] = $request->third_party_fein ?? '';
                        $detailData['email_sent'] = $request->has('email_sent') && $request->email_sent;
                    }
                    
                    // Create the record using mass assignment
                    $driverApplicationDetail = \App\Models\Admin\Driver\DriverApplicationDetail::create($detailData);
                    
                    // Send verification email for third-party company driver
                    if ($originalOwnershipType === 'third-party') {
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
                    // Update the driver application detail
                    if ($request->ownership_type === 'owned') {
                        // Update owner operator fields
                        $applicationDetail->owner_name = $request->owner_name;
                        $applicationDetail->owner_phone = $request->owner_phone;
                        $applicationDetail->owner_email = $request->owner_email;
                    } else if ($originalOwnershipType === 'third-party') {
                        // Update third party company fields
                        $applicationDetail->third_party_name = $request->third_party_name;
                        $applicationDetail->third_party_phone = $request->third_party_phone;
                        $applicationDetail->third_party_email = $request->third_party_email;
                        $applicationDetail->third_party_dba = $request->third_party_dba;
                        $applicationDetail->third_party_address = $request->third_party_address;
                        $applicationDetail->third_party_contact = $request->third_party_contact;
                        $applicationDetail->third_party_fein = $request->third_party_fein;
                        $applicationDetail->email_sent = $request->has('email_sent') && $request->email_sent;
                    }
                    
                    $applicationDetail->save();
                    
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
        if ($originalOwnershipType === 'third-party' && $request->has('email_sent') && $request->email_sent) {
            // Get the driver application detail we just created
            $applicationDetail = \App\Models\Admin\Driver\DriverApplicationDetail::where('vehicle_id', $vehicle->id)->first();
            
            if ($applicationDetail && $applicationDetail->third_party_email) {
                $this->sendThirdPartyVerificationEmail(
                    $vehicle,
                    $applicationDetail->third_party_name,
                    $applicationDetail->third_party_email,
                    $applicationDetail->third_party_phone,
                    $applicationDetail->driver_application_id
                );
                
                // Update the email_sent flag in the driver application detail
                $applicationDetail->email_sent = true;
                $applicationDetail->save();
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
}
