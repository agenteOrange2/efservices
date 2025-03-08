<?php

namespace App\Http\Controllers\Admin\Vehicles;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\VehicleType;
use Illuminate\Support\Facades\Validator;

class VehicleTypeController extends Controller
{
    /**
     * Mostrar una lista de todos los tipos de vehículos.
     */
    public function index()
    {
        $vehicleTypes = VehicleType::withCount('vehicles')->orderBy('name')->paginate(20);
        
        return view('admin.vehicles.vehicle-types.index', compact('vehicleTypes'));
    }

    /**
     * Mostrar el formulario para crear un nuevo tipo.
     */
    public function create()
    {
        return view('admin.vehicles.vehicle-types.create');
    }

    /**
     * Almacenar un nuevo tipo de vehículo.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vehicle_types,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        VehicleType::create($request->all());

        return redirect()->route('admin.vehicle-types.index')
            ->with('success', 'Tipo de vehículo creado exitosamente');
    }

    /**
     * Mostrar el formulario para editar un tipo de vehículo.
     */
    public function edit(VehicleType $vehicleType)
    {
        return view('admin.vehicles.vehicle-types.edit', compact('vehicleType'));
    }

    /**
     * Actualizar un tipo de vehículo específico.
     */
    public function update(Request $request, VehicleType $vehicleType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vehicle_types,name,' . $vehicleType->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $vehicleType->update($request->all());

        return redirect()->route('admin.vehicles.vehicle-types.index')
            ->with('success', 'Tipo de vehículo actualizado exitosamente');
    }

    /**
     * Eliminar un tipo de vehículo específico.
     */
    public function destroy(VehicleType $vehicleType)
    {
        // Verificar si hay vehículos que usan este tipo
        if ($vehicleType->vehicles->count() > 0) {
            return redirect()->route('admin.vehicles.vehicle-types.index')
                ->with('error', 'No se puede eliminar este tipo porque hay vehículos que lo utilizan');
        }

        $vehicleType->delete();

        return redirect()->route('admin.vehicles.vehicle-types.index')
            ->with('success', 'Tipo de vehículo eliminado exitosamente');
    }
    
    /**
     * API para buscar tipos (para usar con select2 o similar)
     */
    public function search(Request $request)
    {
        $term = $request->input('q', '');
        $types = VehicleType::where('name', 'LIKE', "%{$term}%")
            ->orderBy('name')
            ->limit(10)
            ->get();
            
        return response()->json($types);
    }
}