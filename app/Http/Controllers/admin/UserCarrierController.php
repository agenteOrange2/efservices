<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Carrier;

use App\Models\Membership;
use App\Models\UserCarrier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserCarrierController extends Controller
{

    /**
     * Mostrar todos los registros de user_carrier.
     */
    public function index(Carrier $carrier)
    {
        // Obtenemos los registros de UserCarrier asociados al Carrier
        $userCarriers = $carrier->userCarriers()->paginate(10);
    
        return view('admin.user_carrier.index', compact('carrier', 'userCarriers'));
    }
    

    /**
     * Mostrar el formulario para crear un nuevo registro.
     */

     public function create(Carrier $carrier)
     {
         // Validar el límite de usuarios permitidos por la membresía
         if ($carrier->userCarriers->count() >= $carrier->membership->max_drivers) {
             return redirect()->route('admin.carrier.user_carriers', $carrier->id)
                 ->with('error', 'El carrier ha alcanzado el número máximo de usuarios permitidos según su membresía.');
         }
     
         return view('admin.user_carrier.create', compact('carrier'));
     }
     

    /**
     * Almacenar un nuevo registro en la base de datos.
     */

     public function store(Request $request, Carrier $carrier)
     {
         $validated = $request->validate([
             'carrier_id' => 'required|exists:carriers,id',
             'name' => 'required|string|max:255',
             'email' => 'required|string|email|max:255|unique:user_carriers,email',
             'password' => 'required|min:8|confirmed',
             'phone' => 'required|string|max:15',
             'job_position' => 'required|string|max:255',
             'photo' => 'nullable|image|max:2048',
             'status' => 'required|integer|in:0,1,2',
         ]);
     
         // Encripta la contraseña antes de guardar
         $validated['password'] = bcrypt($validated['password']);
     
         // Crea el registro
         $userCarrier = UserCarrier::create($validated);
     
         // Maneja la subida de la foto de perfil si se envía
         if ($request->hasFile('profile_photo_carrier')) {
             $fileName = strtolower(str_replace(' ', '_', $userCarrier->name)) . '.webp';
     
             $userCarrier->addMediaFromRequest('profile_photo_carrier')
                 ->usingFileName($fileName)
                 ->toMediaCollection('profile_photo_carrier');
         }
     
         // Redirige al índice de usuarios del Carrier utilizando el slug
         return redirect()
             ->route('admin.carrier.user_carriers.index', $carrier->slug)
             ->with('success', 'User Carrier creado exitosamente.');
     }
     
     
     
    /**
     * Mostrar el formulario para editar un registro.
     */
    public function edit(Carrier $carrier, UserCarrier $userCarrier)
    {
        $memberships = Membership::where('status', 1)->select('id', 'name')->get(); // Solo membresías activas
    
        return view('admin.user_carrier.edit', compact('carrier', 'userCarrier', 'memberships'));
    }

    public function users(Request $request, Carrier $carrier)
    {
        // Obtener los usuarios asociados al carrier
        $userCarriers = $carrier->userCarriers()->with('carrier')->paginate(10);

        return view('admin.carrier.users', compact('carrier', 'userCarriers'));
    }

    /**
     * Actualizar un registro existente.
     */

     public function update(Request $request, Carrier $carrier, UserCarrier $userCarrier)
     {
         $validated = $request->validate([
             'carrier_id' => 'required|exists:carriers,id',
             'name' => 'required|string|max:255',
             'email' => 'required|string|email|max:255|unique:user_carriers,email,' . $userCarrier->id,
             'password' => 'nullable|min:8|confirmed',
             'phone' => 'required|string|max:15',
             'job_position' => 'required|string|max:255',
             'photo' => 'nullable|image|max:2048',
             'status' => 'required|integer|in:0,1,2',
         ]);
     
         // Encripta la contraseña si fue proporcionada
         if ($validated['password']) {
             $validated['password'] = bcrypt($validated['password']);
         } else {
             unset($validated['password']);
         }
     
         // Actualiza el registro
         $userCarrier->update($validated);
     
         if ($request->hasFile('profile_photo_carrier')) {
             $fileName = strtolower(str_replace(' ', '_', $userCarrier->name)) . '.webp';
     
             // Limpiar la colección anterior
             $userCarrier->clearMediaCollection('profile_photos_carrier');
     
             // Guardar la nueva foto
             $userCarrier->addMediaFromRequest('profile_photo_carrier')
                 ->usingFileName($fileName)
                 ->toMediaCollection('profile_photos_carrier');
         }
     
         // Redirige al índice de usuarios del Carrier utilizando el slug
         return redirect()
             ->route('admin.carrier.user_carriers.index', $carrier->slug)
             ->with('success', 'User Carrier actualizado correctamente.');
     }
     
     


     public function deletePhoto(UserCarrier $userCarrier)
     {
         $media = $userCarrier->getFirstMedia('profile_photo_carrier');
     
         if ($media) {
             $media->delete(); // Eliminar la foto
             return response()->json([
                 'message' => 'Photo deleted successfully.',
                 'defaultPhotoUrl' => asset('build/default_profile.png'), // Foto predeterminada
             ]);
         }
     
         return response()->json(['message' => 'No photo to delete.'], 404);     
    }

    public function destroy(UserCarrier $userCarrier)
    {
        $userCarrier->delete();

        return redirect()->route('admin.user_carrier.index')->with('success', 'User-Carrier relation deleted successfully!');
    }
}
