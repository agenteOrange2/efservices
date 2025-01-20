<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Carrier;
use Illuminate\Http\Request;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Admin\Driver\NewUserDriverNotification;

class UserDriverController extends Controller
{
    public function index(Carrier $carrier)
    {
        $maxDrivers = $carrier->membership->max_drivers ?? 1;
        $currentDrivers = UserDriverDetail::where('carrier_id', $carrier->id)->count();
        $exceededLimit = $currentDrivers >= $maxDrivers;
    
        return view('admin.user_driver.index', [
            'carrier' => $carrier,
            'userDrivers' => UserDriverDetail::where('carrier_id', $carrier->id)
                ->with('user')
                ->paginate(10),
            'maxDrivers' => $maxDrivers,
            'currentDrivers' => $currentDrivers,
            'exceeded_limit' => $exceededLimit,
        ]);
    }

    public function create(Carrier $carrier)
    {
        // Verificar el límite de drivers para este carrier específico
        $maxDrivers = $carrier->membership->max_drivers ?? 1;
        
        // Solo contar los drivers del carrier actual
        $currentDriversCount = UserDriverDetail::where('carrier_id', $carrier->id)->count();
    
        Log::info('Verificando límite de drivers para carrier', [
            'carrier_id' => $carrier->id,
            'carrier_name' => $carrier->name,
            'max_drivers' => $maxDrivers,
            'current_drivers_count' => $currentDriversCount
        ]);
    
        if ($currentDriversCount >= $maxDrivers) {
            Log::warning('Límite de drivers excedido para carrier específico', [
                'carrier_id' => $carrier->id,
                'max_drivers' => $maxDrivers,
                'current_count' => $currentDriversCount
            ]);
            
            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('exceeded_limit', true)
                ->with('error', 'No puedes agregar más conductores a este carrier. Actualiza tu plan o contacta al administrador.');
        }
    
        return view('admin.user_driver.create', compact('carrier'));
    }

    public function store(Request $request, Carrier $carrier)
    {
        Log::info('Iniciando store de driver', [
            'carrier_id' => $carrier->id,
            'request_data' => $request->except(['password', 'password_confirmation'])
        ]);

        // Validación de los datos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'license_number' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'years_experience' => 'required|integer|min:0',
            'profile_photo_driver' => 'nullable|image|max:2048',
            'status' => 'nullable|integer|in:0,1,2',
        ]);

        // Validar límite de conductores según la membresía
        $maxDrivers = $carrier->membership->max_drivers ?? 1;
        $currentDriversCount = $carrier->userDrivers()->count();

        if ($currentDriversCount >= $maxDrivers) {
            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('error', 'Has alcanzado el límite máximo de conductores permitidos por tu plan.');
        }

        try {
            // Crear el usuario en la tabla `users`
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'] ?? 1,
            ]);

            // Asignar el rol de driver
            $user->assignRole('driver');

            // Crear los detalles específicos en `user_driver_details`
            $user->driverDetails()->create([
                'carrier_id' => $carrier->id,
                'license_number' => $validated['license_number'],
                'birth_date' => $validated['birth_date'],
                'years_experience' => $validated['years_experience'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['status'] ?? 1,
            ]);

            // Subir la foto de perfil si existe
            if ($request->hasFile('profile_photo_driver')) {
                $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp';
                $user->addMediaFromRequest('profile_photo_driver')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }

            // Notificaciones
            $recipients = collect([$user])
                ->merge(User::role('superadmin')->where('id', '!=', $user->id)->get())
                ->unique('id');

            Notification::send($recipients, new NewUserDriverNotification($user, $carrier));

            Log::info('UserDriver creado exitosamente.', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('success', 'Conductor creado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al crear el UserDriver.', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors('Error al crear el conductor.');
        }
    }

    /**
     * Mostrar el formulario para editar un driver.
     */
    public function edit(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        Log::info('Cargando formulario de edición de driver', [
            'carrier_id' => $carrier->id,
            'user_driver_detail_id' => $userDriverDetail->id
        ]);

        $userDriverDetail->load('user');

        return view('admin.user_driver.edit', [
            'carrier' => $carrier,
            'userDriver' => $userDriverDetail,
        ]);
    }

    /**
     * Actualizar un driver existente.
     */
    public function update(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        $user = $userDriverDetail->user;

        if (!$user) {
            Log::error('No se encontró el usuario relacionado al UserDriverDetail.', [
                'user_driver_detail_id' => $userDriverDetail->id,
            ]);
            return redirect()->back()->withErrors('No se encontró el usuario relacionado.');
        }

        Log::info('Iniciando actualización de driver.', [
            'user_id' => $user->id,
            'carrier_id' => $carrier->id,
            'request_data' => $request->except(['password', 'password_confirmation'])
        ]);

        // Validación
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
            'address' => 'required|string|max:255',
            'license_number' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'years_experience' => 'required|integer|min:0',
            'profile_photo_driver' => 'nullable|image|max:2048',
            'status' => 'required|integer|in:0,1,2',
        ]);

        try {
            // Actualizar datos del usuario
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
                'status' => $validated['status'],
            ]);

            // Actualizar detalles del driver
            $userDriverDetail->update([
                'license_number' => $validated['license_number'],
                'birth_date' => $validated['birth_date'],
                'years_experience' => $validated['years_experience'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['status'],
            ]);

            // Manejar la actualización de la foto
            if ($request->hasFile('profile_photo_driver')) {
                $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp';

                // Limpiar colección anterior
                $user->clearMediaCollection('profile_photo_driver');

                // Subir nueva foto
                $user->addMediaFromRequest('profile_photo_driver')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }

            Log::info('Driver actualizado exitosamente', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('success', 'Driver actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error actualizando driver', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'carrier_id' => $carrier->id
            ]);

            return redirect()
                ->back()
                ->withErrors('Error al actualizar el driver: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminar un driver.
     */
    public function destroy(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        try {
            $user = $userDriverDetail->user;

            if ($user) {
                // Eliminar foto de perfil
                $user->clearMediaCollection('profile_photo_driver');
                $user->delete(); // Esto eliminará también el UserDriverDetail por la relación cascade
            }

            Log::info('Driver eliminado exitosamente', [
                'carrier_id' => $carrier->id,
                'user_driver_detail_id' => $userDriverDetail->id
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('success', 'Driver eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error eliminando driver', [
                'error' => $e->getMessage(),
                'carrier_id' => $carrier->id,
                'user_driver_detail_id' => $userDriverDetail->id
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->withErrors('Error al eliminar el driver.');
        }
    }

    /**
 * Eliminar la foto de perfil de un driver.
 */
public function deletePhoto(UserDriverDetail $userDriverDetail)
{
    try {
        $user = $userDriverDetail->user;

        if (!$user) {
            Log::error('Usuario no encontrado para el UserDriverDetail.', [
                'user_driver_detail_id' => $userDriverDetail->id,
            ]);
            return response()->json(['message' => 'User not found.'], 404);
        }

        $media = $user->getFirstMedia('profile_photo_driver');

        if ($media) {
            $media->delete();
            
            Log::info('Foto de driver eliminada correctamente.', [
                'user_driver_detail_id' => $userDriverDetail->id,
            ]);

            return response()->json([
                'message' => 'Photo deleted successfully.',
                'defaultPhotoUrl' => asset('build/default_profile.png'),
            ]);
        }

        return response()->json(['message' => 'No photo to delete.'], 404);

    } catch (\Exception $e) {
        Log::error('Error al eliminar la foto del driver.', [
            'error' => $e->getMessage(),
            'user_driver_detail_id' => $userDriverDetail->id,
        ]);

        return response()->json(['message' => 'Error deleting photo.'], 500);
    }
    }
    

}
