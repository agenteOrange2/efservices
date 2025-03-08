<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Carrier;
use App\Models\Membership;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\UserCarrierDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Notifications\Admin\Carrier\NewUserCarrierNotification;

class UserCarrierController extends Controller
{
    /**
     * Mostrar todos los registros de user_carrier.
     */
    public function index(Carrier $carrier)
    {
        $maxCarriers = $carrier->membership->max_carrier ?? 1;
        $currentCarriers = $carrier->users()->count();
        $exceededLimit = $currentCarriers >= $maxCarriers;

        return view('admin.user_carrier.index', [
            'carrier' => $carrier,
            'userCarriers' => $carrier->users()->with('carrierDetails')->paginate(10),
            'exceeded_limit' => $exceededLimit,
        ]);
    }

    /**
     * Mostrar el formulario para crear un nuevo registro.
     */
    public function create(Carrier $carrier)
    {
        $maxCarriers = $carrier->membership->max_carrier ?? 1;
        $currentCarriersCount = $carrier->users()->count();

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
        // Validación de los datos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|string|max:15',
            'job_position' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|max:2048',
            'status' => 'nullable|integer|in:0,1,2',
        ]);

        // Validar límite de usuarios según la membresía
        $maxCarriers = $carrier->membership->max_carrier ?? 1;
        $currentCarriersCount = $carrier->users()->count();

        if ($currentCarriersCount >= $maxCarriers) {
            return redirect()
                ->route('admin.carrier.user_carriers.index', $carrier)
                ->with('error', 'Has alcanzado el límite máximo de usuarios permitidos por tu plan.');
        }

        try {
            // Crear el usuario en la tabla `users`
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'],
            ]);

            // Asignar el rol de carrier
            $user->assignRole('user_carrier');

            // Crear los detalles específicos en `user_carrier_details`
            $user->carrierDetails()->create([
                'carrier_id' => $carrier->id,
                'phone' => $validated['phone'],
                'job_position' => $validated['job_position'],
                'status' => $validated['status'],
            ]);

            // Subir la foto de perfil si existe
            if ($request->hasFile('profile_photo_carrier')) {
                $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp';
                $user->addMediaFromRequest('profile_photo_carrier')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_carrier');
            }

            // Obtener todos los destinatarios únicos
            $recipients = collect([$user])
                ->merge(User::role('superadmin')->where('id', '!=', $user->id)->get())
                ->unique('id');

            // Enviar una sola vez a cada destinatario
            Notification::send($recipients, new NewUserCarrierNotification($user, $carrier));


            Log::info('UserCarrier creado exitosamente.', ['user_id' => $user->id, 'carrier_id' => $carrier->id]);

            return redirect()
                ->route('admin.carrier.user_carriers.index', $carrier)
                ->with('success', 'User Carrier creado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear el UserCarrier.', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors('Error al crear el usuario o asignar el rol.');
        }
    }

    /**
     * Mostrar el formulario para editar un registro.
     */
    public function edit(Carrier $carrier, UserCarrierDetail $userCarrierDetails)
    {
        $userCarrierDetails->load('user');

        Log::info('Cargando UserCarrierDetail', [
            'carrier_id' => $carrier->id,
            'userCarrierDetail_id' => $userCarrierDetails->id,
            'profile_photo_url' => $userCarrierDetails->user->profile_photo_url,
        ]);

        return view('admin.user_carrier.edit', [
            'carrier' => $carrier,
            'userCarrier' => $userCarrierDetails,
        ]);
    }


    /**
     * Actualizar un registro existente.
     */
    public function update(Request $request, Carrier $carrier, UserCarrierDetail $userCarrierDetails)
    {
        $user = $userCarrierDetails->user; // Cargar el usuario relacionado
        if (!$user) {
            Log::error('No se encontró el usuario relacionado al UserCarrierDetail.', [
                'userCarrierDetail_id' => $userCarrierDetails->id,
            ]);
            return redirect()->back()->withErrors('No se encontró el usuario relacionado.');
        }

        Log::info('Iniciando actualización de usuario.', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'carrier_id' => $carrier->id,
            'request_data' => $request->all(),
        ]);

        // Validación de los datos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|min:8|confirmed',
            'phone' => 'required|string|max:15',
            'job_position' => 'required|string|max:255',
            'profile_photo' => 'nullable|image|max:2048',
            'status' => 'required|integer|in:0,1,2',
        ]);

        Log::info('Datos validados correctamente.', ['validated_data' => $validated]);

        try {
            // Actualizar datos generales en `users`
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
                'status' => $validated['status'],
            ]);

            Log::info('Datos del usuario actualizados correctamente.', ['user_id' => $user->id]);

            // Actualizar los detalles específicos en `user_carrier_details`
            $userCarrierDetails->update([
                'phone' => $validated['phone'],
                'job_position' => $validated['job_position'],
                'status' => $validated['status'],
            ]);

            Log::info('Detalles del UserCarrier actualizados correctamente.', [
                'user_carrier_details_id' => $userCarrierDetails->id,
            ]);

            // Manejar la actualización de la foto de perfil
            if ($request->hasFile('profile_photo_carrier')) {
                $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp';

                // Limpia la colección del modelo `User`
                $user->clearMediaCollection('profile_photo_carrier');

                // Añade la nueva imagen en la misma colección del modelo `User`
                $user->addMediaFromRequest('profile_photo_carrier')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_carrier');
            }

            return redirect()
                ->route('admin.carrier.user_carriers.index', $carrier)
                ->with('success', 'User Carrier actualizado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar el UserCarrier.', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->withErrors('Error al actualizar el usuario.');
        }
    }


    public function deletePhoto(UserCarrierDetail $userCarrierDetails)
    {
        try {
            // Verifica si existe el usuario relacionado
            $user = $userCarrierDetails->user;

            if (!$user) {
                Log::error('Usuario no encontrado para el UserCarrierDetail.', [
                    'userCarrierDetail_id' => $userCarrierDetails->id,
                ]);
                return response()->json(['message' => 'User not found.'], 404);
            }

            // Verifica si hay una foto en la colección 'profile_photo_carrier'
            $media = $userCarrierDetails->getFirstMedia('profile_photo_carrier');

            if ($media) {
                $media->delete(); // Elimina la foto
                Log::info('Foto eliminada correctamente.', [
                    'userCarrierDetail_id' => $userCarrierDetails->id,
                ]);

                return response()->json([
                    'message' => 'Photo deleted successfully.',
                    'defaultPhotoUrl' => asset('build/default_profile.png'), // URL de la foto predeterminada
                ]);
            }

            return response()->json(['message' => 'No photo to delete.'], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar la foto.', [
                'error_message' => $e->getMessage(),
                'userCarrierDetail_id' => $userCarrierDetails->id,
            ]);

            return response()->json(['message' => 'Error deleting photo.'], 500);
        }
    }



    /**
     * Eliminar un registro.
     */
    public function destroy(Carrier $carrier, User $userCarrier)
    {
        try {
            // Obtener los detalles específicos del UserCarrier
            $userCarrierDetail = $userCarrier->carrierDetails;

            if ($userCarrierDetail) {
                // Eliminar todas las fotos asociadas al detalle del usuario
                $userCarrierDetail->clearMediaCollection('profile_photo_carrier');
                $userCarrierDetail->delete(); // Eliminar los detalles
            }

            // Limpiar la colección de fotos del usuario y eliminar el usuario
            $userCarrier->clearMediaCollection('profile_photo_carrier');
            $userCarrier->delete();

            return redirect()
                ->route('admin.carrier.user_carriers.index', $carrier)
                ->with('success', 'User Carrier eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar el UserCarrier.', [
                'error_message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.carrier.user_carriers.index', $carrier)
                ->withErrors('Error al eliminar el usuario.');
        }
    }
}
