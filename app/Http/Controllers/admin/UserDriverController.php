<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Models\Admin\Driver\DriverApplication;
use App\Notifications\Admin\Driver\NewUserDriverNotification;
use App\Notifications\Admin\Driver\NewDriverNotificationAdmin;
use App\Notifications\Admin\Driver\NewDriverCreatedNotification;

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
            // Datos de User
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|integer|in:0,1,2',
            'profile_photo_driver' => 'nullable|image|max:2048',

            // Datos de UserDriverDetail
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'license_number' => 'required|string|max:255',
            'state_of_issue' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'terms_accepted' => 'required|boolean',
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
            DB::beginTransaction();

            // Crear el usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'] ?? 1,
            ]);

            $user->assignRole('driver');

            // Crear detalles del driver
            $driverDetail = $user->driverDetails()->create([
                'carrier_id' => $carrier->id,
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'license_number' => $validated['license_number'],
                'state_of_issue' => $validated['state_of_issue'],
                'phone' => $validated['phone'],
                'status' => $validated['status'],
                'terms_accepted' => $validated['terms_accepted'],
            ]);

            // Manejar la foto
            if ($request->hasFile('profile_photo_driver')) {
                $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp';
                $user->addMediaFromRequest('profile_photo_driver')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }

            // Notificar al usuario
            $user->notify(new NewDriverCreatedNotification($user, $carrier, $validated['password']));

            // Notificar a los admins
            $admins = User::role('superadmin')->get();
            Notification::send($admins, new NewDriverNotificationAdmin($user, $carrier));

            DB::commit();

            return redirect()->route('admin.carrier.user_drivers.application.step1', [
                'carrier' => $carrier,
                'driver' => $driverDetail->id
            ])->with('success', 'Driver created successfully. Please complete the application process.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating driver', ['error' => $e->getMessage()]);
            return back()->withErrors('Error creating driver: ' . $e->getMessage());
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

        // Validación con todos los campos requeridos
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
            'license_number' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'years_experience' => 'required|integer|min:0',
            'status' => 'required|integer|in:0,1,2',
            'profile_photo_driver' => 'nullable|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Actualizar datos del usuario
            $userUpdate = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status']
            ];

            // Solo actualizar la contraseña si se proporcionó una nueva
            if (!empty($validated['password'])) {
                $userUpdate['password'] = Hash::make($validated['password']);
            }

            $user->update($userUpdate);

            // Actualizar detalles del driver
            $driverUpdate = [
                'license_number' => $validated['license_number'],
                'birth_date' => $validated['birth_date'],
                'years_experience' => $validated['years_experience'],
                'phone' => $validated['phone'],
                'status' => $validated['status']
            ];

            $userDriverDetail->update($driverUpdate);

            // Manejar la foto de perfil si se proporcionó una nueva
            if ($request->hasFile('profile_photo_driver')) {
                $user->clearMediaCollection('profile_photo_driver');
                $user->addMediaFromRequest('profile_photo_driver')
                    ->usingFileName(Str::slug($user->name) . '.webp')
                    ->toMediaCollection('profile_photo_driver');
            }

            DB::commit();

            Log::info('Driver actualizado exitosamente', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'updated_fields' => array_keys($driverUpdate)
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('success', 'Driver actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error actualizando driver', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'carrier_id' => $carrier->id
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'Error al actualizar el driver: ' . $e->getMessage()])
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

    public function createStep1(Carrier $carrier)
    {
        // Obtener el driver más reciente si viene de la creación
        $driver = UserDriverDetail::where('carrier_id', $carrier->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$driver) {
            return redirect()->route('admin.carrier.user_drivers.index', $carrier)
                ->withErrors('No driver found.');
        }

        return view('admin.user_driver.applications.step1', compact('carrier', 'driver'));
    }

    public function storeStep1(Request $request, Carrier $carrier)
    {
        Log::info('Iniciando storeStep1', [
            'request_data' => $request->all(),
            'carrier_id' => $carrier->id
        ]);

        try {
            $validated = $request->validate([
                'suffix' => 'nullable|string|max:50',
                'social_security_number' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
            ]);

            Log::info('Datos validados correctamente', [
                'validated_data' => $validated
            ]);

            DB::beginTransaction();
            Log::info('Iniciando transacción DB');

            $driver = UserDriverDetail::where('carrier_id', $carrier->id)
                ->orderBy('created_at', 'desc')
                ->first();

            Log::info('Driver encontrado', [
                'driver_id' => $driver ? $driver->id : null,
                'user_id' => $driver ? $driver->user_id : null
            ]);

            $application = DriverApplication::create([
                'user_id' => $driver->user_id,
                'carrier_id' => $carrier->id,
                'suffix' => $validated['suffix'],
                'social_security_number' => $validated['social_security_number'],
                'date_of_birth' => $validated['date_of_birth'],
                'status' => DriverApplication::STATUS_DRAFT
            ]);

            Log::info('Aplicación creada', [
                'application_id' => $application->id,
                'status' => $application->status
            ]);

            DB::commit();
            Log::info('Transacción completada exitosamente');

            // Modificamos esta parte para pasar la application en lugar del driver
            return redirect()->route('admin.carrier.user_drivers.application.step2', [
                'carrier' => $carrier,
                'application' => $application->id  // Cambiamos driver por application
            ])->with('success', 'Step 1 completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en storeStep1', [
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors('Error al guardar la aplicación: ' . $e->getMessage())->withInput();
        }
    }

    public function createStep2(Carrier $carrier, DriverApplication $application)
    {
        Log::info('Iniciando createStep2', [
            'carrier_id' => $carrier->id,
            'application_id' => $application->id
        ]);

        try {
            // Obtener el driver asociado con la aplicación
            $driver = UserDriverDetail::where('user_id', $application->user_id)
                ->where('carrier_id', $carrier->id)
                ->first();

            if (!$driver) {
                Log::error('Driver no encontrado para la aplicación', [
                    'application_id' => $application->id,
                    'user_id' => $application->user_id
                ]);
                throw new \Exception('Driver no encontrado');
            }

            // Verificar si existe una dirección actual
            $currentAddress = $application->addresses()->where('to_date', null)->first();

            Log::info('Datos recuperados para Step 2', [
                'driver_id' => $driver->id,
                'has_current_address' => $currentAddress ? true : false
            ]);

            return view('admin.user_driver.applications.step2', compact(
                'carrier',
                'application',
                'driver',
                'currentAddress'
            ));
        } catch (\Exception $e) {
            Log::error('Error en createStep2', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->withErrors('Error loading step 2: ' . $e->getMessage());
        }
    }

    public function storeStep2(Request $request, Carrier $carrier, DriverApplication $application)
    {
        Log::info('Iniciando storeStep2', [
            'request_data' => $request->except(['_token']),
            'carrier_id' => $carrier->id,
            'application_id' => $application->id
        ]);
    
        try {
            $validated = $request->validate([
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'zip_code' => 'required|string|max:20',
                'lived_three_years' => 'required|boolean',
                'from_date' => 'required|date',
                'to_date' => 'nullable|date|after:from_date',
                'previous_addresses' => 'required_if:lived_three_years,false|array',
                'previous_addresses.*.address_line1' => 'required_if:lived_three_years,false|string|max:255',
                'previous_addresses.*.city' => 'required_if:lived_three_years,false|string|max:255',
                'previous_addresses.*.state' => 'required_if:lived_three_years,false|string|max:255',
                'previous_addresses.*.zip_code' => 'required_if:lived_three_years,false|string|max:20',
                'previous_addresses.*.from_date' => 'required_if:lived_three_years,false|date',
                'previous_addresses.*.to_date' => 'required_if:lived_three_years,false|date|after:previous_addresses.*.from_date',
            ]);
    
            // Verificar que se cubran 3 años
            $mainFromDate = Carbon::parse($validated['from_date']);
            $mainToDate = $validated['to_date'] ? Carbon::parse($validated['to_date']) : Carbon::now();
            $totalDuration = $mainFromDate->diffInYears($mainToDate);
    
            if (!$validated['lived_three_years'] && isset($validated['previous_addresses'])) {
                foreach ($validated['previous_addresses'] as $address) {
                    $fromDate = Carbon::parse($address['from_date']);
                    $toDate = Carbon::parse($address['to_date']);
                    $totalDuration += $fromDate->diffInYears($toDate);
                }
            }
    
            if ($totalDuration < 3) {
                Log::warning('No se cubren los 3 años requeridos', [
                    'total_duration' => $totalDuration,
                    'main_address_duration' => $mainFromDate->diffInYears($mainToDate)
                ]);
                return back()
                    ->withErrors(['address_history' => 'Your address history must cover at least 3 years in total.'])
                    ->withInput();
            }
    
            DB::beginTransaction();
    
            // Crear dirección actual
            $application->addresses()->create([
                'address_line1' => $validated['address_line1'],
                'address_line2' => $validated['address_line2'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip_code' => $validated['zip_code'],
                'lived_three_years' => $validated['lived_three_years'],
                'from_date' => $validated['from_date'],
                'to_date' => $validated['to_date']
            ]);
    
            // Si hay direcciones previas, guardarlas
            if (!$validated['lived_three_years'] && isset($validated['previous_addresses'])) {
                foreach ($validated['previous_addresses'] as $address) {
                    $application->addresses()->create($address);
                }
            }
    
            DB::commit();
            
            Log::info('Direcciones guardadas exitosamente', [
                'application_id' => $application->id,
                'total_duration' => $totalDuration
            ]);
    
            return redirect()
                ->route('admin.carrier.user_drivers.application.step3', [
                    'carrier' => $carrier,
                    'application' => $application
                ])
                ->with('success', 'Address history saved successfully.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en storeStep2', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors('Error saving address information: ' . $e->getMessage())->withInput();
        }
    }

    public function createStep3(Carrier $carrier, DriverApplication $application)
    {
        Log::info('Iniciando createStep3', [
            'carrier_id' => $carrier->id,
            'application_id' => $application->id
        ]);

        try {
            // Obtener los detalles de la aplicación si existen
            $details = $application->details;

            Log::info('Datos recuperados', [
                'has_details' => !is_null($details),
                'application_status' => $application->status
            ]);

            return view('admin.user_driver.applications.step3', compact(
                'carrier',
                'application',
                'details'
            ));
        } catch (\Exception $e) {
            Log::error('Error en createStep3', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->withErrors('Error loading application details: ' . $e->getMessage());
        }
    }

    public function storeStep3(Request $request, Carrier $carrier, DriverApplication $application)
    {
        Log::info('Iniciando storeStep3', [
            'request_data' => $request->except(['_token']),
            'carrier_id' => $carrier->id,
            'application_id' => $application->id
        ]);

        try {
            $validated = $request->validate([
                'applying_position' => 'required|string|max:255',
                'applying_location' => 'required|string|max:255',
                'eligible_to_work' => 'required|boolean',
                'can_speak_english' => 'required|boolean',
                'has_twic_card' => 'required|boolean',
                'twic_expiration_date' => 'required_if:has_twic_card,true|nullable|date',
                'known_by_other_name' => 'required|boolean',
                'other_names' => 'required_if:known_by_other_name,true|nullable|string|max:255',
                'how_did_hear' => 'required|string|max:255',
                'referral_employee_name' => 'nullable|string|max:255',
                'expected_pay' => 'required|numeric|min:0|max:999999.99'
            ]);

            DB::beginTransaction();

            // Crear o actualizar los detalles de la aplicación
            $application->details()->updateOrCreate(
                ['driver_application_id' => $application->id],
                $validated
            );

            // Actualizar el estado de la aplicación
            $application->update(['status' => 'pending_review']);

            DB::commit();

            Log::info('Aplicación completada exitosamente', [
                'application_id' => $application->id,
                'new_status' => 'pending_review'
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.index', $carrier)
                ->with('success', 'Application completed successfully and is now pending review.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en storeStep3', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withErrors('Error saving application details: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function review(Carrier $carrier, DriverApplication $application)
    {
        $application->load(['addresses', 'details']);
        return view('admin.driver.applications.review', compact('carrier', 'application'));
    }

    public function show(Carrier $carrier, DriverApplication $application)
    {
        $application->load(['addresses', 'details']);
        return view('admin.driver.applications.show', compact('carrier', 'application'));
    }
}
