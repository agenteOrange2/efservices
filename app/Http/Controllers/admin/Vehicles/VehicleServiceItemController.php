<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleMaintenance; // Cambiado de VehicleServiceItem
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleServiceItemController extends Controller
{
    /**
     * Mostrar todos los items de servicio para un vehículo.
     */
    public function index(Vehicle $vehicle)
    {
        // Ahora usamos el modelo VehicleMaintenance pero mantenemos la misma lógica
        $serviceItems = VehicleMaintenance::where('vehicle_id', $vehicle->id)
                                         ->orderBy('service_date', 'desc')
                                         ->paginate(10);
        
        return view('admin.vehicles.service-items.index', compact('vehicle', 'serviceItems'));
    }

    /**
     * Mostrar el formulario para crear un nuevo item de servicio.
     */
    public function create(Vehicle $vehicle)
    {
        return view('admin.vehicles.service-items.create', compact('vehicle'));
    }

    /**
     * Almacenar un nuevo item de servicio.
     */
    public function store(Request $request, Vehicle $vehicle)
    {
        $validator = Validator::make($request->all(), [
            'unit' => 'required|string|max:255',
            'service_date' => 'required|date',
            'next_service_date' => 'required|date|after:service_date',
            'service_tasks' => 'required|string|max:255',
            'vendor_mechanic' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'odometer' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Crear un nuevo mantenimiento - ahora usando VehicleMaintenance
        $serviceItem = new VehicleMaintenance([
            'vehicle_id' => $vehicle->id,
            'unit' => $request->unit,
            'service_date' => $request->service_date,
            'next_service_date' => $request->next_service_date,
            'service_tasks' => $request->service_tasks,
            'vendor_mechanic' => $request->vendor_mechanic,
            'description' => $request->description,
            'cost' => $request->cost,
            'odometer' => $request->odometer,
            'status' => false, // Por defecto, no completado
        ]);
        
        $serviceItem->save();

        return redirect()->route('admin.vehicles.show', $vehicle->id)
            ->with('success', 'Servicio de mantenimiento creado exitosamente');
    }

    /**
     * Mostrar un item de servicio específico.
     */
    public function show(Vehicle $vehicle, $serviceItemId)
    {
        // Buscar usando el nuevo modelo
        $serviceItem = VehicleMaintenance::findOrFail($serviceItemId);
        
        // Verificar que el service item pertenece a este vehículo
        if ($serviceItem->vehicle_id !== $vehicle->id) {
            abort(404);
        }
        
        return view('admin.vehicles.service-items.show', compact('vehicle', 'serviceItem'));
    }

    /**
     * Mostrar el formulario para editar un item de servicio.
     */
    public function edit(Vehicle $vehicle, $serviceItemId)
    {
        // Buscar usando el nuevo modelo
        $serviceItem = VehicleMaintenance::findOrFail($serviceItemId);
        
        // Verificar que el service item pertenece a este vehículo
        if ($serviceItem->vehicle_id !== $vehicle->id) {
            abort(404);
        }
        
        return view('admin.vehicles.service-items.edit', compact('vehicle', 'serviceItem'));
    }

    /**
     * Actualizar un item de servicio específico.
     */
    public function update(Request $request, Vehicle $vehicle, $serviceItemId)
    {
        // Buscar usando el nuevo modelo
        $serviceItem = VehicleMaintenance::findOrFail($serviceItemId);
        
        if ($serviceItem->vehicle_id !== $vehicle->id) {
            abort(404);
        }
        
        $validator = Validator::make($request->all(), [
            'unit' => 'required|string|max:255',
            'service_date' => 'required|date',
            'next_service_date' => 'required|date|after:service_date',
            'service_tasks' => 'required|string|max:255',
            'vendor_mechanic' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            'odometer' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar los campos - incluido status
        $serviceItem->update([
            'unit' => $request->unit,
            'service_date' => $request->service_date,
            'next_service_date' => $request->next_service_date,
            'service_tasks' => $request->service_tasks,
            'vendor_mechanic' => $request->vendor_mechanic,
            'description' => $request->description,
            'cost' => $request->cost,
            'odometer' => $request->odometer,
            // Conservamos el valor actual de status
        ]);

        return redirect()->route('admin.vehicles.service-items.index', $vehicle->id)
            ->with('success', 'Item de servicio actualizado exitosamente');
    }

    /**
     * Eliminar un item de servicio específico.
     */
    public function destroy(Vehicle $vehicle, $serviceItemId)
    {
        // Buscar usando el nuevo modelo
        $serviceItem = VehicleMaintenance::findOrFail($serviceItemId);
        
        if ($serviceItem->vehicle_id !== $vehicle->id) {
            abort(404);
        }
        
        $serviceItem->delete();
        
        return redirect()->route('admin.vehicles.service-items.index', $vehicle->id)
            ->with('success', 'Item de servicio eliminado exitosamente');
    }
    
    /**
     * Cambiar el estado del servicio (completado/pendiente)
     */
    public function toggleStatus(Vehicle $vehicle, $serviceItemId)
    {
        $serviceItem = VehicleMaintenance::findOrFail($serviceItemId);
        
        if ($serviceItem->vehicle_id !== $vehicle->id) {
            abort(404);
        }
        
        $serviceItem->status = !$serviceItem->status;
        $serviceItem->save();
        
        return back()->with('success', 'Estado del servicio actualizado exitosamente');
    }
}