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
            'registration_state' => 'required|string|max:255',
            'registration_number' => 'required|string|max:255',
            'registration_expiration_date' => 'required|date',
            'permanent_tag' => 'boolean',
            'tire_size' => 'nullable|string|max:255',
            'fuel_type' => 'required|string|max:255',
            'irp_apportioned_plate' => 'boolean',
            'ownership_type' => 'required|in:owned,leased',
            'location' => 'nullable|string|max:255',
            'user_driver_detail_id' => 'nullable|exists:user_driver_details,id', // Ahora es nullable
            'annual_inspection_expiration_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Guardar o crear marca del vehículo si no existe
        if ($request->has('make') && !VehicleMake::where('name', $request->make)->exists()) {
            VehicleMake::create(['name' => $request->make]);
        }
        
        // Guardar o crear tipo de vehículo si no existe
        if ($request->has('type') && !VehicleType::where('name', $request->type)->exists()) {
            VehicleType::create(['name' => $request->type]);
        }

        // Crear el vehículo
        $vehicle = Vehicle::create($request->all());
        
        // Procesar y guardar los items de servicio si existen
        if ($request->has('service_items') && is_array($request->service_items)) {
            foreach ($request->service_items as $serviceItemData) {
                // Verificar que los datos principales están presentes
                if (
                    isset($serviceItemData['service_date']) &&
                    isset($serviceItemData['next_service_date']) &&
                    isset($serviceItemData['service_tasks']) &&
                    isset($serviceItemData['vendor_mechanic']) &&
                    isset($serviceItemData['cost'])
                ) {
                    // Crear el item de servicio para este vehículo
                    $serviceItem = new VehicleServiceItem($serviceItemData);
                    $serviceItem->vehicle_id = $vehicle->id;
                    $serviceItem->save();
                }
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
        $vehicle->load(['carrier', 'driver', 'serviceItems']);
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
            'registration_state' => 'required|string|max:255',
            'registration_number' => 'required|string|max:255',
            'registration_expiration_date' => 'required|date',
            'permanent_tag' => 'boolean',
            'tire_size' => 'nullable|string|max:255',
            'fuel_type' => 'required|string|max:255',
            'irp_apportioned_plate' => 'boolean',
            'ownership_type' => 'required|in:owned,leased',
            'location' => 'nullable|string|max:255',
            'user_driver_detail_id' => 'nullable|exists:user_driver_details,id', // Ahora es nullable
            'annual_inspection_expiration_date' => 'nullable|date',
            'out_of_service' => 'boolean',
            'out_of_service_date' => 'nullable|date|required_if:out_of_service,1',
            'suspended' => 'boolean',
            'suspended_date' => 'nullable|date|required_if:suspended,1',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Guardar o crear marca del vehículo si no existe
        if ($request->has('make') && !VehicleMake::where('name', $request->make)->exists()) {
            VehicleMake::create(['name' => $request->make]);
        }
        
        // Guardar o crear tipo de vehículo si no existe
        if ($request->has('type') && !VehicleType::where('name', $request->type)->exists()) {
            VehicleType::create(['name' => $request->type]);
        }
        
        // Actualizar el vehículo
        $vehicle->update($request->all());
        
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
}