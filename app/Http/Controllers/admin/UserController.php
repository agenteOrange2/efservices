<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // Obtener usuarios paginados
        $users = User::paginate(1); // Pagina de 10 en 10
        //
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        //dd($request->all());

        // Validación de los datos del formulario
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email', // Verifica que el correo sea único en la tabla `users`
            'password' => 'required|min:8|confirmed', // Asegura que la contraseña coincida con el campo `password_confirmation`
            'status' => 'required|boolean',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        // Crear el usuario en la base de datos
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
        ]);


        if ($request->hasFile('profile_photo')) {
            $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp'; // Genera el nombre basado en el usuario

            $user->addMediaFromRequest('profile_photo')
                ->usingFileName($fileName) // Usa el nombre basado en el usuario
                ->toMediaCollection('profile_photos');
        }


        // Mensaje dinámico para la notificación
        return redirect()
            ->route('admin.users.edit', $user->id)
            ->with('notification', [
                'type' => 'success',
                'message' => 'User created successfully!',
                'details' => 'The user data has been saved correctly.',
            ]);
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $profilePhotoUrl = $user->getFirstMediaUrl('profile_photos', 'webp');
        return view('admin.users.edit', compact('user', 'profilePhotoUrl'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);



        if ($request->hasFile('profile_photo')) {
            $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp'; // Genera el nombre basado en el usuario

            // Limpiar la colección anterior
            $user->clearMediaCollection('profile_photos');

            // Guardar la nueva foto con el nombre personalizado
            $user->addMediaFromRequest('profile_photo')
                ->usingFileName($fileName) // Usa el nombre basado en el usuario
                ->toMediaCollection('profile_photos');
        }

        return redirect()
            ->route('admin.users.edit', $user->id)
            ->with('notification', [
                'type' => 'success',
                'message' => 'User updated successfully!',
                'details' => 'The user details have been updated.',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('notification', [
            'type' => 'error',
            'message' => 'User deleted successfully!',
        ]);
    }

}
