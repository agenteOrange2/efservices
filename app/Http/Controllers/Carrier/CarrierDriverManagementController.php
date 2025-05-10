<?php

namespace App\Http\Controllers\Carrier;

use App\Models\User;
use App\Models\Carrier;
use App\Models\UserDriverDetail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\DriverRegistrationCredentials;

class CarrierDriverManagementController extends Controller
{
    /**
     * Muestra la lista de conductores del carrier.
     */
    public function index()
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
            ->with('user')
            ->paginate(10);
            
        return view('carrier.drivers.index', compact('drivers', 'carrier'));
    }
    
    /**
     * Muestra el formulario para crear un nuevo conductor.
     */
    public function create()
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar si se ha alcanzado el límite de conductores
        $maxDrivers = $carrier->membership->max_drivers ?? 1;
        $currentDriversCount = UserDriverDetail::where('carrier_id', $carrier->id)->count();
        
        if ($currentDriversCount >= $maxDrivers) {
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'Has alcanzado el límite máximo de conductores para tu plan. Actualiza tu membresía para añadir más conductores.');
        }

        return view('carrier.drivers.create', [
            'carrier' => $carrier
        ]);
    }
    
    /**
     * Almacena un nuevo conductor en la base de datos.
     */
    public function store(Request $request)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Validar los datos del formulario
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:20',
            'license_number' => 'required|string|max:255',
            'license_state' => 'required|string|max:255',
            'license_expiration' => 'required|date',
            'date_of_birth' => 'required|date',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        try {
            // Generar una contraseña aleatoria
            $password = Str::random(10);
            
            // Crear el usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
            ]);
            
            // Asignar el rol de conductor
            $user->assignRole('user_driver');
            
            // Crear los detalles del conductor
            $driverDetail = UserDriverDetail::create([
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zipcode' => $validated['zipcode'],
                'license_number' => $validated['license_number'],
                'license_state' => $validated['license_state'],
                'license_expiration' => $validated['license_expiration'],
                'date_of_birth' => $validated['date_of_birth'],
                'status' => 1, // Activo por defecto
            ]);
            
            // Procesar la foto de perfil si se proporcionó
            if ($request->hasFile('profile_photo')) {
                $driverDetail->addMediaFromRequest('profile_photo')
                    ->toMediaCollection('profile_photo_driver');
            }
            
            // Enviar correo con credenciales
            $resumeLink = route('driver.dashboard');
            Mail::to($user->email)->send(new DriverRegistrationCredentials($user->name, $user->email, $password, $resumeLink));
            
            return redirect()->route('carrier.drivers.index')
                ->with('success', 'Conductor creado exitosamente. Se han enviado las credenciales por correo electrónico.');
                
        } catch (\Exception $e) {
            Log::error('Error al crear conductor', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al crear conductor: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Muestra los detalles de un conductor específico.
     */
    public function show(UserDriverDetail $driver)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
        return view('carrier.drivers.show', compact('driver', 'carrier'));
    }
    
    /**
     * Muestra el formulario para editar un conductor.
     */
    public function edit(UserDriverDetail $driver)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
        return view('carrier.drivers.edit', compact('driver', 'carrier'));
    }
    
    /**
     * Actualiza un conductor en la base de datos.
     */
    public function update(Request $request, UserDriverDetail $driver)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
        // Validar los datos del formulario
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $driver->user->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:20',
            'license_number' => 'required|string|max:255',
            'license_state' => 'required|string|max:255',
            'license_expiration' => 'required|date',
            'date_of_birth' => 'required|date',
            'status' => 'required|in:0,1,2',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        try {
            // Actualizar el usuario
            $driver->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);
            
            // Actualizar los detalles del conductor
            $driver->update([
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zipcode' => $validated['zipcode'],
                'license_number' => $validated['license_number'],
                'license_state' => $validated['license_state'],
                'license_expiration' => $validated['license_expiration'],
                'date_of_birth' => $validated['date_of_birth'],
                'status' => $validated['status'],
            ]);
            
            // Procesar la foto de perfil si se proporcionó
            if ($request->hasFile('profile_photo')) {
                $driver->clearMediaCollection('profile_photo_driver');
                $driver->addMediaFromRequest('profile_photo')
                    ->toMediaCollection('profile_photo_driver');
            }
            
            return redirect()->route('carrier.drivers.index')
                ->with('success', 'Conductor actualizado exitosamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar conductor', [
                'error' => $e->getMessage(),
                'driver_id' => $driver->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al actualizar conductor: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Elimina un conductor.
     */
    public function destroy(UserDriverDetail $driver)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
        try {
            $user = $driver->user;
            
            if ($user) {
                // Eliminar foto de perfil
                $driver->clearMediaCollection('profile_photo_driver');
                
                // Eliminar otras colecciones de medios relacionadas con el driver
                if ($driver->licenses()->exists()) {
                    $driver->licenses()->get()->each(function($license) {
                        $license->clearMediaCollection('license_front');
                        $license->clearMediaCollection('license_back');
                    });
                }
                
                if ($driver->medicalQualification) {
                    $driver->medicalQualification->clearMediaCollection('medical_card');
                }
                
                if ($driver->trainingSchools()->exists()) {
                    $driver->trainingSchools()->get()->each(function($school) {
                        $school->clearMediaCollection('school_certificates');
                    });
                }
                
                if ($driver->certification) {
                    $driver->certification->clearMediaCollection('signature');
                }
                
                // Eliminar el usuario (que también eliminará el UserDriverDetail por cascada)
                $user->delete();
            }
            
            return redirect()->route('carrier.drivers.index')
                ->with('success', 'Conductor eliminado exitosamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar conductor', [
                'error' => $e->getMessage(),
                'driver_id' => $driver->id
            ]);
            
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'Error al eliminar conductor: ' . $e->getMessage());
        }
    }
    
    /**
     * Elimina la foto de perfil de un conductor.
     */
    public function deletePhoto(UserDriverDetail $driver)
    {
        $carrier = Auth::user()->carrierDetails->carrier;
        
        // Verificar que el conductor pertenezca al carrier del usuario autenticado
        if ($driver->carrier_id !== $carrier->id) {
            return redirect()->route('carrier.drivers.index')
                ->with('error', 'No tienes acceso a este conductor.');
        }
        
        try {
            $driver->clearMediaCollection('profile_photo_driver');
            
            return redirect()->back()
                ->with('success', 'Foto de perfil eliminada exitosamente.');
                
        } catch (\Exception $e) {
            Log::error('Error al eliminar foto de perfil', [
                'error' => $e->getMessage(),
                'driver_id' => $driver->id
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al eliminar foto de perfil: ' . $e->getMessage());
        }
    }
}
