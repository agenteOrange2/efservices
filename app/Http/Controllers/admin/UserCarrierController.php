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
    public function index(Request $request)
    {
        $carrierId = $request->carrier_id; // Recibido como parámetro desde el tab "Users".
        $carrier = Carrier::with('userCarriers')->findOrFail($carrierId);

        return view('admin.user_carrier.index', compact('carrier'));
    }

    /**
     * Mostrar el formulario para crear un nuevo registro.
     */

    public function create()
    {
        // Asegúrate de obtener modelos Eloquent con los datos necesarios
        $users = User::select('id', 'name')->get(); // Usuarios disponibles
        $carriers = Carrier::where('status', 1)->select('id', 'name')->get();
        //$carriers = Carrier::with('membership:id,name')->select('id', 'name', 'id_plan')->get(); // Carriers disponibles

        return view('admin.user_carrier.create', compact('users', 'carriers'));
    }




    /**
     * Almacenar un nuevo registro en la base de datos.
     */

     public function store(Request $request)
     {
         $validated = $request->validate([
             'carrier_id' => 'required|exists:carriers,id',
             'name' => 'required|string|max:255',
             'email' => 'required|string|email|max:255|unique:user_carriers',
             'password' => 'required|min:8|confirmed',
             'phone' => 'required|string|max:15',
             'job_position' => 'required|string|max:255',
             'photo' => 'nullable|image|max:2048',
             'status' => 'required|integer|in:0,1,3',
         ]);
     
         // Crear usuario carrier relacionado al Carrier
         $userCarrier = UserCarrier::create($validated);
     
         if ($request->hasFile('profile_photo_carrier')) {
             $fileName = strtolower(str_replace(' ', '_', $userCarrier->name)) . '.webp';
     
             $userCarrier->addMediaFromRequest('profile_photo_carrier')
                 ->usingFileName($fileName)
                 ->toMediaCollection('profile_photo_carrier');
         }
     
         return redirect()
             ->route('carrier.users', $validated['carrier_id'])
             ->with('notification', [
                 'type' => 'success',
                 'message' => 'User Carrier created successfully!',
             ]);
     }
     


    /**
     * Mostrar el formulario para editar un registro.
     */
    public function edit(Carrier $carrier)
    {
        $memberships = Membership::where('status', 1)->select('id', 'name')->get(); // Solo carriers activos

        // Pasar información a las tabs
        return view('admin.carrier.edit', compact('carrier', 'memberships'));
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

    public function update(Request $request, UserCarrier $userCarrier)
    {
        $validated = $request->validate([
            'carrier_id' => 'required|exists:carriers,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:user_carriers,email,' . $userCarrier->id,
            'password' => 'nullable|min:8|confirmed',
            'phone' => 'required|string|max:15',
            'job_position' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|integer|in:0,1,3',
        ]);



        if ($validated['password']) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $userCarrier->update($validated);

        if ($request->hasFile('profile_photo_carrier')) {
            $fileName = strtolower(str_replace(' ', '_', $userCarrier->name)) . '.webp'; // Genera el nombre basado en el usuario

            // Limpiar la colección anterior
            $userCarrier->clearMediaCollection('profile_photos_carrier');

            // Guardar la nueva foto con el nombre personalizado
            $userCarrier->addMediaFromRequest('profile_photo_carrier')
                ->usingFileName($fileName) // Usa el nombre basado en el usuario
                ->toMediaCollection('profile_photos_carrier');
        }

        // Mensaje dinámico para la notificación
        return redirect()
            ->route('admin.user_carrier.edit', $userCarrier->id)
            ->with('notification', [
                'type' => 'success',
                'message' => 'User Carrier updated successfully!',
                'details' => 'The User Carrier data has been updated correctly.',
            ]);
    }


    public function deletePhoto(UserCarrier $userCarrier)
    {
        $media = $userCarrier->getFirstMedia('profile_photo_carrier');

        if ($media) {
            $media->delete(); // Elimina la foto
            return response()->json([
                'message' => 'Photo deleted successfully.',
                'defaultPhotoUrl' => asset('build/default_profile.png'), // Retorna la foto predeterminada
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
