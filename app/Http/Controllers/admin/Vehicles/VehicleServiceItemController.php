<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Models\Admin\Vehicle\Vehicle;
use App\Models\Admin\Vehicle\VehicleServiceItem;
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
        $serviceItems = $vehicle->serviceItems()->orderBy('service_date', 'desc')->paginate(10);

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

        // Crear un nuevo objeto con todos los datos del formulario
        $serviceItem = new VehicleServiceItem($request->all());

        // Asignar explícitamente el vehicle_id desde el parámetro de ruta
        $serviceItem->vehicle_id = $vehicle->id;

        // Guardar el servicio
        $serviceItem->save();

        return redirect()->route('admin.vehicles.show', $vehicle->id)
            ->with('success', 'Servicio de mantenimiento creado exitosamente');
    }

    /**
     * Mostrar un item de servicio específico.
     */
    public function show(Vehicle $vehicle, VehicleServiceItem $serviceItem)
    {
        // Verificar que el service item pertenece a este vehículo
        if ($serviceItem->vehicle_id !== $vehicle->id) {
            abort(404);
        }
        
        return view('admin.vehicles.service-items.show', compact('vehicle', 'serviceItem'));
    }

    /**
     * Mostrar el formulario para editar un item de servicio.
     */
    public function edit(Vehicle $vehicle, VehicleServiceItem $serviceItem)
    {
        // Verificar que el service item pertenece a este vehículo
        if ($serviceItem->vehicle_id !== $vehicle->id) {
            abort(404);
        }
        
        return view('admin.vehicles.service-items.edit', compact('vehicle', 'serviceItem'));
    }

    /**
     * Actualizar un item de servicio específico.
     */
    public function update(Request $request, Vehicle $vehicle, VehicleServiceItem $serviceItem)
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

        $serviceItem->update($request->all());

        return redirect()->route('admin.vehicles.service-items.index', $vehicle->id)
            ->with('success', 'Item de servicio actualizado exitosamente');
    }

    /**
     * Eliminar un item de servicio específico.
     */
    public function destroy(Vehicle $vehicle, VehicleServiceItem $serviceItem)
    {
        $serviceItem->delete();

        return redirect()->route('admin.vehicles.service-items.index', $vehicle->id)
            ->with('success', 'Item de servicio eliminado exitosamente');
    }
}
