<?php

namespace App\Http\Controllers\Admin;

use App\Models\Carrier;
use App\Models\Membership;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CarrierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Mostrar todos los transportistas
    public function index()
    {
        $carriers = Carrier::with('documents', 'managers', 'membership')->get();
        return view('admin.carrier.index', compact('carriers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    // Mostrar el formulario para crear un nuevo transportista
    public function create()
    {
        $memberships = Membership::all(); // Obtener todos los planes
        return view('admin.carriers.create', compact('memberships'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // Guardar un nuevo transportista
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:10',
            'ein_number' => 'required|string|max:255',
            'dot_number' => 'required|string|max:255',
            'mc_number' => 'nullable|string|max:255',
            'state_dot' => 'nullable|string|max:255',
            'ifta_account' => 'nullable|string|max:255',
            'logo_img' => 'nullable|image|max:2048',
            'id_plan' => 'required|exists:memberships,id',
            'status' => 'required|in:pending,active,inactive',
        ]);

        if ($request->hasFile('logo_img')) {
            $validated['logo_img'] = $request->file('logo_img')->store('logos', 'public');
        }

        Carrier::create($validated);

        return redirect()->route('admin.carriers.index')->with('success', 'Carrier created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    // Mostrar el formulario para editar un transportista
    public function edit(Carrier $carrier)
    {
        $memberships = Membership::all(); // Obtener todos los planes
        return view('admin.carriers.edit', compact('carrier', 'memberships'));
    }

    /**
     * Update the specified resource in storage.
     */
    // Actualizar un transportista existente
    public function update(Request $request, Carrier $carrier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:10',
            'ein_number' => 'required|string|max:255',
            'dot_number' => 'required|string|max:255',
            'mc_number' => 'nullable|string|max:255',
            'state_dot' => 'nullable|string|max:255',
            'ifta_account' => 'nullable|string|max:255',
            'logo_img' => 'nullable|image|max:2048',
            'id_plan' => 'required|exists:memberships,id',
            'status' => 'required|in:pending,active,inactive',
        ]);

        if ($request->hasFile('logo_img')) {
            $validated['logo_img'] = $request->file('logo_img')->store('logos', 'public');
        }

        $carrier->update($validated);

        return redirect()->route('admin.carriers.index')->with('success', 'Carrier updated successfully!');
    }

        /**
     * Remove the specified resource from storage.
     */

    // Eliminar un transportista
    public function destroy(Carrier $carrier)
    {
        $carrier->delete();

        return redirect()->route('admin.carriers.index')->with('success', 'Carrier deleted successfully!');
    }


}
