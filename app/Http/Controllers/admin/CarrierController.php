<?php

namespace App\Http\Controllers\Admin;

use App\Models\Carrier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CarrierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    // Obtener solo usuarios con el rol "superadmin"
    $carrier = Carrier::all();
    return view('admin.carrier.index', compact('carrier'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.carrier.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);
    
        $carrier = Carrier::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);
    
                
    
        return redirect()->route('admin.users.index')->with('success', 'Superadmin creado con éxito');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Carrier $carrier)
    {
        return view('admin.carrier.edit', compact('carrier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $user)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Carrier $carrier)
    {

    }
}
