<?php
namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\VehicleMake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleMakeController extends Controller
{
    public function index(Request $request)
    {
        // Consulta base con conteo de vehículos
        $query = VehicleMake::withCount('vehicles')->orderBy('name');
        
        // Filtro de búsqueda
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $vehicleMakes = $query->paginate(10)->withQueryString();
        
        return view('admin.vehicles.makes.index', compact('vehicleMakes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vehicle_makes,name',
        ], [
            'name.required' => 'El nombre de la marca es obligatorio',
            'name.unique' => 'Esta marca ya existe en el sistema',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $make = VehicleMake::create($request->all());
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'make' => $make
            ]);
        }
        
        return redirect()->route('admin.vehicle-makes.index')
            ->with('success', 'Marca de vehículo creada exitosamente');
    }

    public function update(Request $request, VehicleMake $vehicleMake)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vehicle_makes,name,' . $vehicleMake->id,
        ], [
            'name.required' => 'El nombre de la marca es obligatorio',
            'name.unique' => 'Esta marca ya existe en el sistema',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $vehicleMake->update($request->all());
        
        return redirect()->route('admin.vehicle-makes.index')
            ->with('success', 'Marca de vehículo actualizada exitosamente');
    }

    public function destroy(VehicleMake $vehicleMake)
    {
        // Verificar si hay vehículos que usan esta marca
        if ($vehicleMake->vehicles()->count() > 0) {
            return redirect()->route('admin.vehicle-makes.index')
                ->with('error', 'No se puede eliminar esta marca porque está siendo utilizada por vehículos');
        }

        try {
            $vehicleMake->delete();
            return redirect()->route('admin.vehicle-makes.index')
                ->with('success', 'Marca de vehículo eliminada exitosamente');
        } catch (\Exception $e) {
            return redirect()->route('admin.vehicle-makes.index')
                ->with('error', 'Error al eliminar la marca: ' . $e->getMessage());
        }
    }

    /**
     * API para búsqueda de marcas vía AJAX
     */
    public function search(Request $request)
    {
        $term = $request->input('q', '');
        $makes = VehicleMake::where('name', 'LIKE', "%{$term}%")
            ->orderBy('name')
            ->limit(10)
            ->get();
            
        return response()->json($makes);
    }
}