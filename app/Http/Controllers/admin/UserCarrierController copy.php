<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Carrier;

use App\Models\Membership;
use App\Models\UserCarrier;
use Illuminate\Http\Request;
use App\Models\UserCarrierDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

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
        $maxCarriers = $carrier->membership->max_carrier ?? 1;
        $currentCarriersCount = $carrier->userCarriers->count();

        if ($currentCarriersCount >= $maxCarriers) {
            return redirect()
                ->route('admin.carrier.user_carriers.index', $carrier)
                ->with('exceeded_limit', true)
                ->with('error', 'No puedes agregar más usuarios. Actualiza tu plan o contacta al administrador.');
        }

        return view('admin.user_carrier.create', compact('carrier'));
    }


    /**
     * Almacenar un nuevo registro en la base de datos.
     */

    public function store(Request $request, Carrier $carrier)
    {

        // Log inicial
        Log::info('Iniciando creación de UserCarrier desde el admin.', [
            'carrier_id' => $carrier->id,
            'request_data' => $request->all(),
        ]);

        // Límite de carriers según la membresía
        $maxCarriers = $carrier->membership->max_carrier ?? 1;
        $currentCarriersCount = $carrier->userCarriers()->count();

        // Validar si se excede el límite
        if ($currentCarriersCount >= $maxCarriers) {
            return redirect()
                ->route('admin.carrier.user_carriers.index', $carrier)
                ->with('error', 'Has alcanzado el límite máximo de usuarios permitidos por tu plan.');
        }

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
        $validated['password'] = Hash::make($validated['password']);

        try {
            // Crear el registro
            $userCarrier = UserCarrier::create($validated);

            $userCarrier->assignRole('user_carrier');
            Log::info('Rol asignado al UserCarrier.', ['user_carrier_id' => $userCarrier->id, 'role' => 'user_carrier']);

            // Asignar el rol
            $userCarrier->assignRole('user_carrier');
            Log::info('Rol asignado exitosamente al UserCarrier.', [
                'user_carrier_id' => $userCarrier->id,
                'role' => 'user_carrier',
            ]);

            // Manejo de la foto de perfil
            if ($request->hasFile('profile_photo_carrier')) {
                $fileName = strtolower(str_replace(' ', '_', $userCarrier->name)) . '.webp';
                $userCarrier->addMediaFromRequest('profile_photo_carrier')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_carrier');
                Log::info('Foto de perfil subida exitosamente.', [
                    'user_carrier_id' => $userCarrier->id,
                    'file_name' => $fileName,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error al crear el UserCarrier o asignar rol.', [
                'errors' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors('Error al crear el usuario o asignar el rol.');
        }

        // Redirige al índice de usuarios del Carrier utilizando el slug
        return redirect()
            ->route('admin.carrier.user_carriers.index', $carrier)
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

        //dd($request->all());
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
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
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

        Log::info($request->all());
        // Redirige al índice de usuarios del Carrier utilizando el slug
        return redirect()
            ->route('admin.carrier.user_carriers.index', $carrier)
            ->with('success', 'User Carrier actualizado correctamente.');
    }




    public function deletePhoto(UserCarrierDetail $userCarrierDetails)
    {
        // Cargar el usuario relacionado
        $user = $userCarrierDetails->user;
    
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    
        // Obtener la foto de perfil del UserCarrier
        $media = $userCarrierDetails->getFirstMedia('profile_photo_carrier');
    
        if ($media) {
            $media->delete(); // Elimina la foto
            return response()->json([
                'message' => 'Photo deleted successfully.',
                'defaultPhotoUrl' => asset('build/default_profile.png'), // Retorna la foto predeterminada
            ]);
        }
    
        return response()->json(['message' => 'No photo to delete.'], 404);
    }
    
    

    /*
    public function destroy(UserCarrier $userCarrier)
    {
        $userCarrier->delete();

        return redirect()
        ->route('admin.user_carrier.index')
        ->with('success', 'User-Carrier relation deleted successfully!');
    }
    */

    public function destroy(Carrier $carrier, UserCarrier $userCarrier)
    {
        Log::info('Eliminando UserCarrier', [
            'carrier_id' => $carrier->id,
            'user_carrier_id' => $userCarrier->id,
        ]);

        $userCarrier->delete();

        return redirect()
            ->route('admin.carrier.user_carriers.index', $carrier->slug)
            ->with('success', 'User-Carrier relation deleted successfully!');
    }
}
