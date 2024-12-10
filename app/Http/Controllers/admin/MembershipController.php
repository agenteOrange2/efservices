<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    //Mostrar todos los planes de membresia
    public function index()
    {
        $membership = Membership::all();
        return view('admin.membership.index', compact('membership'));
    }

    public function create()
    {
        return view('admin.membership.create');
    }
    //Crear y guardar un nuevo plan de membresía
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'max_drivers' => 'required|integer',
            'max_vehicles' => 'required|integer'
        ]);

        $membership = Membership::create($validated);

        // Mensaje dinámico para la notificación
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


}
