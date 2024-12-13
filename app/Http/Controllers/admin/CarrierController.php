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
        //$carriers = Carrier::with('documents', 'managers', 'membership')->get();
        //return view('admin.carrier.index', compact('carriers'));
        return view('admin.carrier.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    // Mostrar el formulario para crear un nuevo transportista
    public function create()
    {

        $memberships = Membership::where('status', 1)->select('id', 'name')->get(); // Obtener todos los planes        
        return view('admin.carrier.create', compact('memberships'));
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
            'status' => 'required|integer|in:0,1,3',
        ]);

        $carrier = Carrier::create($validated);

        if ($request->hasFile('logo_carrier')) {
            $fileName = strtolower(str_replace(' ', '_', $carrier->name)) . '.webp'; // Genera el nombre basado en el nombre del plan

            $carrier->addMediaFromRequest('logo_carrier')
                ->usingFileName($fileName) // Usa el nombre personalizado
                ->toMediaCollection('logo_carrier');
        }

        // Mensaje dinámico para la notificación
        return redirect()
            ->route('admin.carrier.edit', $carrier->id)
            ->with('notification', [
                'type' => 'success',
                'message' => 'Carrier created successfully!',
                'details' => 'The Carrier data has been saved correctly.',
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    // Mostrar el formulario para editar un transportista
    public function edit(Carrier $carrier)
    {
        $memberships = Membership::where('status', 1)->select('id', 'name')->get(); // Solo carriers activos
        return view('admin.carrier.edit', compact('carrier', 'memberships'));
    }

    public function users(Carrier $carrier)
    {
        // Obtenemos los registros de UserCarrier asociados al Carrier
        $userCarriers = $carrier->userCarriers()->paginate(10);
    
        return view('admin.carrier.tabs.users', compact('carrier', 'userCarriers'));
    }

    /*
public function documents(Carrier $carrier)
{
    $documents = $carrier->documents()->paginate(10);

    return view('admin.carrier.tabs.documents', compact('carrier', 'documents'));
}
    */

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
            'status' => 'required|integer|in:0,1,3',
        ]);


        if ($request->hasFile('logo_carrier')) {
            $fileName = strtolower(str_replace(' ', '_', $carrier->name)) . '.webp';

            // Limpiar la colección anterior
            $carrier->clearMediaCollection('logo_carrier');

            // Guardar la nueva foto con el nombre personalizado
            $carrier->addMediaFromRequest('logo_carrier')
                ->usingFileName($fileName)
                ->toMediaCollection('logo_carrier');
        }

        $carrier->update($validated);

        // Mensaje dinámico para la notificación
        return redirect()
            ->route('admin.carrier.edit', $carrier->id)
            ->with('notification', [
                'type' => 'success',
                'message' => 'Carrier updated successfully!',
                'details' => 'The Updated data has been saved correctly.',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */

    public function deletePhoto(Carrier $carrier)
    {
        $media = $carrier->getFirstMedia('logo_carrier');

        if ($media) {
            $media->delete(); // Elimina la foto
            return response()->json([
                'message' => 'Photo deleted successfully.',
                'defaultPhotoUrl' => asset('build/default_profile.png'), // Retorna la foto predeterminada
            ]);
        }

        return response()->json(['message' => 'No photo to delete.'], 404);
    }


    // Eliminar un transportista
    public function destroy(Carrier $carrier)
    {
        $carrier->delete();

        return redirect()->route('admin.carriers.index')->with('success', 'Carrier deleted successfully!');
    }
}
