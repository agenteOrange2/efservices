<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Carrier;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Helpers\Constants;
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

        // Cargar las constantes que necesitas en la vista:
        $usStates = Constants::usStates();
        $driverPositions = Constants::driverPositions();
        $referralSources = Constants::referralSources();

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

        return view('admin.user_driver.create', compact(
            'carrier',
            'usStates',
            'driverPositions',
            'referralSources'
        ));
    }


    public function store(Request $request, Carrier $carrier)
    {


        Log::info('Iniciando store de driver', [
            'carrier_id' => $carrier->id,
            'request_data' => $request->except(['password', 'password_confirmation']),
        ]);


        try {
            // Realizamos la validación directa usando el formato de validate sin reglas explícitas
            $validated = $request->validate([
                // Datos de User
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|confirmed',
                'last_name' => 'required|string|max:255',
                'license_number' => 'required|string|max:255',
                'state_of_issue' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
                'date_of_birth' => 'required|date',

                // Datos de la aplicación
                'social_security_number' => 'nullable|string|max:255', // ajusta según tu DB

                // Direcciones
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'zip_code' => 'required|string|max:255',
                'from_date' => 'required|date',
                'to_date' => 'nullable|date',
                'lived_three_years' => 'nullable|boolean',


                // Direcciones anteriores
                'previous_addresses' => 'array',
                'previous_addresses.*.address_line1' => 'required|string|max:255',
                'previous_addresses.*.address_line2' => 'nullable|string|max:255',
                'previous_addresses.*.city' => 'required|string|max:255',
                'previous_addresses.*.state' => 'required|string|max:255',
                'previous_addresses.*.zip_code' => 'required|string|max:255',
                'previous_addresses.*.from_date' => 'required|date',
                'previous_addresses.*.to_date' => 'required|date',

                // Otros campos de la aplicación
                'applying_position' => 'required|string',
                'applying_position_other' => 'required_if:applying_position,other',
                'applying_location' => 'required|string|max:255',
                'eligible_to_work' => 'required|boolean',
                'can_speak_english' => 'sometimes|boolean',
                'has_twic_card' => 'sometimes|boolean',
                'twic_expiration_date' => 'required_if:has_twic_card,true|nullable|date',
                'how_did_hear' => 'required|string',
                'how_did_hear_other' => 'required_if:how_did_hear,other',
                'referral_employee_name' => 'required_if:how_did_hear,employee_referral',
                'expected_pay' => 'nullable|string|max:255',
            ]);

            Log::info('Validación completada', $validated);

            // Validación manual de edad mayor de 18
            $dob = Carbon::parse($validated['date_of_birth']);
            if ($dob->age < 18) {
                return back()->withErrors(['date_of_birth' => 'Debes tener al menos 18 años.'])->withInput();
            }

            // Reemplazar esta sección en el controlador
            $fromDate = Carbon::parse($validated['from_date']);
            $toDate = $validated['to_date'] ? Carbon::parse($validated['to_date']) : now();
            $currentAddressYears = $fromDate->diffInYears($toDate);
            $totalYears = $currentAddressYears;

            // Si la dirección actual no cubre 3 años, permitir y validar direcciones adicionales
            if ($totalYears < 3) {
                $previousAddresses = $validated['previous_addresses'] ?? [];

                // Sumar años de direcciones adicionales si existen
                foreach ($previousAddresses as $address) {
                    $fromD = Carbon::parse($address['from_date']);
                    $toD = Carbon::parse($address['to_date']);
                    $totalYears += $fromD->diffInYears($toD);
                }
            }

            // Actualizar livedThreeYears basado en el total
            $livedThreeYears = $totalYears >= 3;

            // Solo validar que se cubran los 3 años si hay direcciones adicionales
            if (!empty($previousAddresses) && $totalYears < 3) {
                return back()->withErrors([
                    'previous_addresses' => 'El historial de direcciones debe cubrir al menos 3 años. Total actual: ' .
                        number_format($totalYears, 1) . ' años.'
                ])->withInput();
            }

            // Validación si es elegible para trabajar
            if (!$validated['eligible_to_work']) {
                return back()->withErrors(['eligible_to_work' => 'Debes ser elegible para trabajar en U.S.'])->withInput();
            }

            // Inicia la transacción para crear los registros
            DB::beginTransaction();
            Log::info('CreateDriver: Iniciando transacción DB');

            // Crear usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => 1, // 1 = activo
            ]);

            Log::info('Usuario creado', ['user_id' => $user->id]);

            // Asignar rol de 'driver' al usuario
            $user->assignRole('driver');
            Log::info('CreateDriver: Rol asignado driver');

            // Crear el detalle del conductor
            $userDriverDetail = UserDriverDetail::create([
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'last_name' => $validated['last_name'],
                'license_number' => $validated['license_number'],
                'state_of_issue' => $validated['state_of_issue'],
                'phone' => $validated['phone'],
                'date_of_birth' => $validated['date_of_birth'],
                'status' => 1, // 1 = activo
                'terms_accepted' => $request->has('terms_accepted') ? true : false,
                'confirmation_token' => Str::random(60), // token de confirmación
            ]);

            Log::info('Detalles del driver creados', ['user_driver_id' => $userDriverDetail->id]);


            if ($request->hasFile('photo')) { // Cambiar a 'photo' que es el nombre en el formulario
                $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp';
                Log::info('CreateDriver: Procesando foto de perfil', [
                    'file_exists' => true,
                    'original_name' => $request->file('photo')->getClientOriginalName()
                ]);

                // Guardar la imagen en el UserDriverDetail en lugar del User
                $userDriverDetail->addMediaFromRequest('photo')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }


            // Crear la aplicación del driver
            $application = DriverApplication::create([
                'user_id' => $user->id,
                'social_security_number' => $validated['social_security_number'] ?? null,
                'status' => 'draft', // estado 'draft' para la aplicación
            ]);
            Log::info('CreateDriver: Aplicación creada', ['application_id' => $application->id]);

            // Crear dirección principal
            $address = $application->addresses()->create([
                'primary' => 1,
                'address_line1' => $validated['address_line1'],
                'address_line2' => $validated['address_line2'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'],
                'zip_code' => $validated['zip_code'],
                'lived_three_years' => $livedThreeYears,
                'from_date' => $validated['from_date'],
                'to_date' => $validated['to_date'] ?? null,
            ]);
            Log::info('CreateDriver: Dirección principal creada', ['address_id' => $address->id]);

            // Si hay direcciones anteriores, las creamos
            // Si hay direcciones anteriores, las creamos
            if (!$livedThreeYears && !empty($previousAddresses)) {
                foreach ($previousAddresses as $prevAddress) {
                    $application->addresses()->create([
                        'primary' => 0, // Siempre 0 para direcciones adicionales
                        'address_line1' => $prevAddress['address_line1'],
                        'address_line2' => $prevAddress['address_line2'] ?? null,
                        'city' => $prevAddress['city'],
                        'state' => $prevAddress['state'],
                        'zip_code' => $prevAddress['zip_code'],
                        'lived_three_years' => false,
                        'from_date' => $prevAddress['from_date'],
                        'to_date' => $prevAddress['to_date']
                    ]);
                }
            }

            // Crear detalles de la aplicación
            $applicationDetails = $application->details()->create([
                'applying_position' => $validated['applying_position'] === 'other'
                    ? $validated['applying_position_other']
                    : $validated['applying_position'],
                'applying_location' => $validated['applying_location'],
                'eligible_to_work' => $validated['eligible_to_work'],
                'can_speak_english' => $request->boolean('can_speak_english', false),
                'has_twic_card' => $request->boolean('has_twic_card', false),
                'twic_expiration_date' => $validated['twic_expiration_date'] ?? null,
                'expected_pay' => $validated['expected_pay'] ?? null,
                'how_did_hear' => $validated['how_did_hear'] === 'other'
                    ? $validated['how_did_hear_other']
                    : $validated['how_did_hear'],
                'referral_employee_name' => $validated['how_did_hear'] === 'employee_referral'
                    ? $validated['referral_employee_name']
                    : null,
            ]);
            Log::info('CreateDriver: Detalles de aplicación creados', ['details_id' => $applicationDetails->id]);

            // Todo ok, confirmamos la transacción
            DB::commit();
            Log::info('CreateDriver: Transacción completada exitosamente.');

            return redirect()->route('admin.carrier.user_drivers.edit', [
                'carrier' => $carrier,
                'userDriverDetail' => $userDriverDetail->id
            ])->with('success', 'Driver creado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CreateDriver: Error en la transacción DB', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Error creando el driver: ' . $e->getMessage()])
                ->withInput();
        }
    }


    public function edit(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        Log::info('Iniciando edición de driver para carrier', [
            'carrier_id' => $carrier->id,
            'user_driver_id' => $userDriverDetail->id,
        ]);

        // Recupera los datos para los selects
        $usStates = Constants::usStates();
        $driverPositions = Constants::driverPositions();
        $referralSources = Constants::referralSources();

        // Cargar los datos del driver y sus direcciones
        $driver = $userDriverDetail->user;
        $userDriverDetail->load([
            'application.details',
            'addresses',
            'user'
        ]); // Cargar direcciones

        // Get the main/primary address
        $mainAddress = $userDriverDetail->addresses()->where('primary', true)->first();
        $previousAddresses = $mainAddress ? json_decode($mainAddress->previous_addresses, true) : [];

        // Obtener la URL de la foto del conductor
        $profilePhotoUrl = $userDriverDetail->getFirstMedia('profile_photo_driver')?->getUrl()
            ?? asset('build/default_profile.png');

        // Pasar los datos a la vista
        return view('admin.user_driver.edit', compact(
            'carrier',
            'userDriverDetail',
            'driver',
            'usStates',
            'driverPositions',
            'referralSources',
            'mainAddress',
            'previousAddresses',
            'profilePhotoUrl'
        ));
    }

    /**
     * Actualizar un driver existente (replicando la lógica Livewire de store).
     */
    protected function getValidatedData(Request $request)
    {
        $validated = $request->validate([
            // ... other validations ...
            'referral_employee_name' => 'nullable|string|max:255',
        ]);

        // Set default null for referral_employee_name if not present
        $validated['referral_employee_name'] = $validated['referral_employee_name'] ?? null;

        return $validated;
    }

    public function update(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        Log::info('Iniciando actualización de driver', [
            'carrier_id' => $carrier->id,
            'user_driver_id' => $userDriverDetail->id,
            'request_data' => $request->except(['password', 'password_confirmation']),
        ]);

        try {
            // Validación
            $validated = $request->validate([
                'photo' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $userDriverDetail->user->id,
                'password' => 'nullable|min:8|confirmed',
                'middle_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'license_number' => 'required|string|max:255',
                'state_of_issue' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
                'date_of_birth' => 'required|date',
                'status' => 'required|integer|in:0,1,2',
                'social_security_number' => 'nullable|string|max:255',
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'zip_code' => 'required|string|max:255',
                'from_date' => 'required|date',
                'to_date' => 'nullable|date',
                'lived_three_years' => 'boolean',
                'previous_addresses' => 'array',
                'previous_addresses.*.address_line1' => 'required|string|max:255',
                'previous_addresses.*.city' => 'required|string|max:255',
                'previous_addresses.*.state' => 'required|string|max:255',
                'previous_addresses.*.zip_code' => 'required|string|max:255',
                'previous_addresses.*.from_date' => 'required|date',
                'previous_addresses.*.to_date' => 'required|date',
                'applying_position' => 'required|string',
                'applying_position_other' => 'required_if:applying_position,other',
                'applying_location' => 'required|string|max:255',
                'eligible_to_work' => 'required|boolean',
                'can_speak_english' => 'sometimes|boolean',
                'has_twic_card' => 'sometimes|boolean',
                'twic_expiration_date' => 'nullable|date|required_if:has_twic_card,true',
                'how_did_hear' => 'required|string',
                'how_did_hear_other' => 'required_if:how_did_hear,other',
                'referral_employee_name' => 'required_if:how_did_hear,employee_referral',
                'expected_pay' => 'nullable|string|max:255',
            ]);

            // Actualizamos el usuario
            $user = $userDriverDetail->user;
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
            ]);

            // Actualizamos el detalle del conductor
            $userDriverDetail->update([
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'license_number' => $validated['license_number'],
                'state_of_issue' => $validated['state_of_issue'],
                'phone' => $validated['phone'],
                'date_of_birth' => $validated['date_of_birth'],
                'status' => $validated['status'],
            ]);

            // Manejar la foto si se sube una nueva
            if ($request->hasFile('photo')) {
                // Eliminar foto anterior si existe
                $userDriverDetail->clearMediaCollection('profile_photo_driver');

                // Subir nueva foto
                $fileName = strtolower(str_replace(' ', '_', $userDriverDetail->user->name)) . '.webp';
                $userDriverDetail->addMediaFromRequest('photo')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }

            // Actualizar dirección
            // $userDriverDetail->addresses()->update([
            //     'address_line1' => $validated['address_line1'],
            //     'address_line2' => $validated['address_line2'],
            //     'city' => $validated['city'],
            //     'state' => $validated['state'],
            //     'zip_code' => $validated['zip_code'],
            //     'from_date' => $validated['from_date'],
            //     'to_date' => $validated['to_date'],
            //     'lived_three_years' => $validated['lived_three_years'],
            // ]);

            $userDriverDetail->addresses()->updateOrCreate(
                ['primary' => true],
                [
                    'address_line1' => $validated['address_line1'],
                    'address_line2' => $validated['address_line2'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'zip_code' => $validated['zip_code'],
                    'from_date' => $validated['from_date'],
                    'to_date' => $validated['to_date'],
                    'lived_three_years' => $validated['lived_three_years'],
                ]
            );

            if (!$validated['lived_three_years']) {
                // Eliminar direcciones anteriores existentes
                $userDriverDetail->addresses()->where('primary', false)->delete();

                // Obtener el total de años
                $mainAddressYears = Carbon::parse($validated['from_date'])
                    ->diffInYears(Carbon::parse($validated['to_date'] ?? now()));
                $totalYears = $mainAddressYears;

                // Crear las direcciones previas en orden, sumando años hasta alcanzar 3
                foreach ($validated['previous_addresses'] as $prevAddress) {
                    $addressYears = Carbon::parse($prevAddress['from_date'])
                        ->diffInYears(Carbon::parse($prevAddress['to_date']));
                    $totalYears += $addressYears;

                    // Crear la dirección previa
                    $userDriverDetail->addresses()->create([
                        'primary' => false,
                        'address_line1' => $prevAddress['address_line1'],
                        'address_line2' => $prevAddress['address_line2'] ?? null,
                        'city' => $prevAddress['city'],
                        'state' => $prevAddress['state'],
                        'zip_code' => $prevAddress['zip_code'],
                        'from_date' => $prevAddress['from_date'],
                        'to_date' => $prevAddress['to_date']
                    ]);

                    // Si ya alcanzamos 3 años, no procesar más direcciones
                    if ($totalYears >= 3) break;
                }
            }
            // Actualizamos los detalles de la aplicación
            $applicationDetails = $userDriverDetail->application->details()->update([
                'applying_position' => $validated['applying_position'] === 'other'
                    ? $validated['applying_position_other']
                    : $validated['applying_position'],
                'applying_location' => $validated['applying_location'],
                'eligible_to_work' => $validated['eligible_to_work'],
                'can_speak_english' => $validated['can_speak_english'],
                'has_twic_card' => $validated['has_twic_card'],
                'twic_expiration_date' => $validated['twic_expiration_date'],
                'expected_pay' => $validated['expected_pay'],
                'how_did_hear' => $validated['how_did_hear'] === 'other'
                    ? $validated['how_did_hear_other']
                    : $validated['how_did_hear'],
                'referral_employee_name' => $validated['referral_employee_name'],
            ]);

            DB::commit();

            return redirect()->route('admin.carrier.user_drivers.index', [
                'carrier' => $carrier->slug,
                'userDriverDetail' => $userDriverDetail->driver_number,
            ])->with('success', 'Driver actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en la actualización de driver', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['error' => 'Error al actualizar el driver: ' . $e->getMessage()])
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
