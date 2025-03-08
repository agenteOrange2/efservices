<?php

namespace App\Http\Controllers\Admin\Vehicles;

use App\Http\Controllers\Controller;
use App\Models\Admin\Vehicle\VehicleMake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleMakeController extends Controller
{
    public function index()
    {
        $vehicleMakes = VehicleMake::orderBy('name')->paginate(10);
        return view('admin.vehicles.makes.index', compact('vehicleMakes'));
    }

    public function create()
    {
        return view('admin.makes.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vehicle_makes,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        VehicleMake::create($request->all());

        return redirect()->route('admin.vehicle-makes.index')
            ->with('success', 'Vehicle make created successfully');
    }

    public function show(VehicleMake $vehicleMake)
    {
        return view('admin.makes.show', compact('vehicleMake'));
    }

    public function edit(VehicleMake $vehicleMake)
    {
        return view('admin.vehicles.makes.edit', compact('vehicleMake'));
    }

    public function update(Request $request, VehicleMake $vehicleMake)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vehicle_makes,name,' . $vehicleMake->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $vehicleMake->update($request->all());

        return redirect()->route('admin.vehicle-makes.index')
            ->with('success', 'Vehicle make updated successfully');
    }

    public function destroy(VehicleMake $vehicleMake)
    {
        // Verificar si hay vehículos que usan esta marca
        if ($vehicleMake->vehicles()->count() > 0) {
            return redirect()->route('admin.vehicle-makes.index')
                ->with('error', 'Cannot delete this make because it is used by vehicles');
        }

        $vehicleMake->delete();

        return redirect()->route('admin.vehicle-makes.index')
            ->with('success', 'Vehicle make deleted successfully');
    }

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