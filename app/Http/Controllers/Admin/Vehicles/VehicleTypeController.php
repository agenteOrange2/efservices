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
    public function index(Request $request)
    {
        // Consulta base con conteo de vehículos
        $query = VehicleType::withCount('vehicles')->orderBy('name');
        
        // Filtro de búsqueda
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $vehicleTypes = $query->paginate(15)->withQueryString();
        
        return view('admin.vehicles.vehicle-types.index', compact('vehicleTypes'));
    }

    /**
     * Almacenar un nuevo tipo de vehículo.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vehicle_types,name',
        ], [
            'name.required' => 'El nombre del tipo es obligatorio',
            'name.unique' => 'Este tipo ya existe en el sistema',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $type = VehicleType::create($request->all());
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'type' => $type
            ]);
        }
        
        return redirect()->route('admin.vehicle-types.index')
            ->with('success', 'Tipo de vehículo creado exitosamente');
    }

    /**
     * Actualizar un tipo de vehículo específico.
     */
    public function update(Request $request, VehicleType $vehicleType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vehicle_types,name,' . $vehicleType->id,
        ], [
            'name.required' => 'El nombre del tipo es obligatorio',
            'name.unique' => 'Este tipo ya existe en el sistema',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $vehicleType->update($request->all());
        
        return redirect()->route('admin.vehicle-types.index')
            ->with('success', 'Tipo de vehículo actualizado exitosamente');
    }

    /**
     * Eliminar un tipo de vehículo específico.
     */
    public function destroy(VehicleType $vehicleType)
    {
        // Verificar si hay vehículos que usan este tipo
        if ($vehicleType->vehicles()->count() > 0) {
            return redirect()->route('admin.vehicle-types.index')
                ->with('error', 'No se puede eliminar este tipo porque está siendo utilizado por vehículos');
        }

        try {
            $vehicleType->delete();
            return redirect()->route('admin.vehicle-types.index')
                ->with('success', 'Tipo de vehículo eliminado exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('admin.vehicle-types.index')
                ->with('error', 'Error al eliminar el tipo: ' . $e->getMessage());
        }
    }

    /**
     * API para búsqueda de tipos vía AJAX
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