<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class MembershipController extends Controller
{
    //Mostrar todos los planes de membresia
    public function index()
    {
        $membership = Membership::all(); // Obtener todos los planes
        return view('admin.membership.index', compact('membership'));
    }

    //Mostrar el formulario para crear un nuevo plan
    public function create()
    {
        return view('admin.membership.create');
    }

    //Crear y guardar un nuevo plan de membresía
    public function store(Request $request)
    {
        // Validación base
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:300',
            'pricing_type' => 'required|string|in:plan,individual',
            'max_carrier' => 'required|integer|min:1',
            'max_drivers' => 'required|integer|min:1',
            'max_vehicles' => 'required|integer|min:1',
            'image_membership' => 'nullable|image|max:2048',
            'status' => 'nullable|boolean',
            'show_in_register' => 'nullable|boolean',
        ];

        // Validación condicional basada en pricing_type
        if ($request->input('pricing_type') === 'plan') {
            $rules['price'] = 'required|numeric|min:0';
            $rules['carrier_price'] = 'nullable|numeric|min:0';
            $rules['driver_price'] = 'nullable|numeric|min:0';
            $rules['vehicle_price'] = 'nullable|numeric|min:0';
        } else {
            $rules['price'] = 'nullable|numeric|min:0';
            $rules['carrier_price'] = 'required|numeric|min:0';
            $rules['driver_price'] = 'required|numeric|min:0';
            $rules['vehicle_price'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);
        
        // Establecer show_in_register como false si no está presente
        $validated['show_in_register'] = $request->has('show_in_register') ? 1 : 0;

        $membership = Membership::create($validated);

        if ($request->hasFile('image_membership')) {
            $fileName = strtolower(str_replace(' ', '_', $membership->name)) . '.webp'; // Genera el nombre basado en el nombre del plan

            $membership->addMediaFromRequest('image_membership')
                ->usingFileName($fileName) // Usa el nombre personalizado
                ->toMediaCollection('image_membership');
        }

        return redirect()
            ->route('admin.membership.edit', $membership->id)
            ->with('notification', [
                'type' => 'success',
                'message' => 'Membership created successfully!',
                'details' => 'The Membership data has been saved correctly.',
            ]);
    }

    //Mostrar el formulario para editar un plan
    public function edit(Membership $membership)
    {
        return view('admin.membership.edit', compact('membership'));
    }

    // Actualizar un plan de membresía existente
    public function update(Request $request, Membership $membership)
    {
        // Validación base
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:300',
            'pricing_type' => 'required|string|in:plan,individual',
            'max_carrier' => 'required|integer|min:1',
            'max_drivers' => 'required|integer|min:1',
            'max_vehicles' => 'required|integer|min:1',
            'image_membership' => 'nullable|image|max:2048',
            'status' => 'nullable|boolean',
        ];

        // Validación condicional basada en pricing_type
        if ($request->input('pricing_type') === 'plan') {
            $rules['price'] = 'required|numeric|min:0';
            $rules['carrier_price'] = 'nullable|numeric|min:0';
            $rules['driver_price'] = 'nullable|numeric|min:0';
            $rules['vehicle_price'] = 'nullable|numeric|min:0';
        } else {
            $rules['price'] = 'nullable|numeric|min:0';
            $rules['carrier_price'] = 'required|numeric|min:0';
            $rules['driver_price'] = 'required|numeric|min:0';
            $rules['vehicle_price'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        // Establecer el estado como 0 si no está presente
        $validated['status'] = $request->has('status') ? 1 : 0;
        
        // Establecer show_in_register como false si no está presente
        $validated['show_in_register'] = $request->has('show_in_register') ? 1 : 0;

        if ($request->hasFile('image_membership')) {
            $fileName = strtolower(str_replace(' ', '_', $membership->name)) . '.webp';

            // Limpiar la colección anterior
            $membership->clearMediaCollection('image_membership');

            // Guardar la nueva foto con el nombre personalizado
            $membership->addMediaFromRequest('image_membership')
                ->usingFileName($fileName)
                ->toMediaCollection('image_membership');
        }

        $membership->update($validated);

        return redirect()
            ->route('admin.membership.edit', $membership->id)
            ->with('notification', [
                'type' => 'success',
                'message' => 'Membership updated',
                'details' => 'The updated data has been saved correctly.',
            ]);
    }

    public function deletePhoto(Membership $membership)
    {
        $media = $membership->getFirstMedia('image_membership');

        if ($media) {
            $media->delete(); // Elimina la foto
            return response()->json([
                'message' => 'Photo deleted successfully.',
                'defaultPhotoUrl' => asset('build/default_profile.png'), // Retorna la foto predeterminada
            ]);
        }

        return response()->json(['message' => 'No photo to delete.'], 404);
    }

    // Eliminar un plan de membresía
    public function destroy(Membership $membership)
    {
        // Eliminar el plan
        $membership->delete();

        return redirect()->route('admin.membership.index')->with('success', 'Membership deleted successfully!');
    }
}
