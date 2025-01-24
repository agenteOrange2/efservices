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
        $usStates = \App\Helpers\Constants::usStates();
        $driverPositions = \App\Helpers\Constants::driverPositions();
        $referralSources = \App\Helpers\Constants::referralSources();

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
        Log::info('Iniciando store de driver (lógica Livewire)', [
            'carrier_id' => $carrier->id,
            'request_data' => $request->except(['password', 'password_confirmation']),
        ]);

        // Aquí definimos las reglas de validación, replicando las del componente Livewire
        $rules = [
            // Datos de User
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],            

            // Foto (photo en Livewire)
            'photo' => ['nullable', 'image', 'max:2048'],

            // Driver details
            'middle_name'     => ['nullable', 'string', 'max:255'],
            'last_name'       => ['required', 'string', 'max:255'],
            'license_number'  => ['required', 'string', 'max:255'],
            'state_of_issue'  => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:15'],
            'date_of_birth'   => ['required', 'date'],

            // Datos de aplicación
            'social_security_number' => ['nullable', 'string', 'max:255'], // ajusta según tu DB

            // Direcciones
            'address_line1'   => ['required', 'string', 'max:255'],
            'address_line2'   => ['nullable', 'string', 'max:255'],
            'city'            => ['required', 'string', 'max:255'],
            'state'           => ['required', 'string', 'max:255'],
            'zip_code'        => ['required', 'string', 'max:255'],
            'from_date'       => ['required', 'date'],
            'to_date'         => ['nullable', 'date'],
            'lived_three_years' => ['boolean'],

            // previous_addresses.*.address_line1 => etc. (para las direcciones anteriores)
            'previous_addresses'                                 => ['array'],
            'previous_addresses.*.address_line1'                => ['required', 'string', 'max:255'],
            'previous_addresses.*.address_line2'                => ['nullable', 'string', 'max:255'],
            'previous_addresses.*.city'                         => ['required', 'string', 'max:255'],
            'previous_addresses.*.state'                        => ['required', 'string', 'max:255'],
            'previous_addresses.*.zip_code'                     => ['required', 'string', 'max:255'],
            'previous_addresses.*.from_date'                    => ['required', 'date'],
            'previous_addresses.*.to_date'                      => ['required', 'date'],

            // Otros campos de la aplicación
            'applying_position'      => ['required', 'string'],
            'applying_position_other' => ['required_if:applying_position,other'],
            'applying_location'      => ['required', 'string', 'max:255'],
            'eligible_to_work'       => ['required', 'boolean'],
            'can_speak_english'      => ['sometimes', 'boolean'],
            'has_twic_card'          => ['sometimes', 'boolean'],
            'twic_expiration_date'   => ['required_if:has_twic_card,true', 'nullable', 'date'],
            'how_did_hear'           => ['required', 'string'],
            'how_did_hear_other'     => ['required_if:how_did_hear,other'],
            'referral_employee_name' => ['required_if:how_did_hear,employee_referral'],
            'expected_pay'           => ['nullable', 'string', 'max:255'],
        ];

        dd($request->all());
        // Mensajes personalizados (ej. para la edad mínima)
        $messages = [
            'date_of_birth.required'       => 'La fecha de nacimiento es requerida.',
            'twic_expiration_date.required_if' => 'Si tiene TWIC card, debe colocar la fecha de expiración.',
            'applying_position_other.required_if' =>
            'Si la posición aplicada es "other", debes especificar la posición.',
            'how_did_hear_other.required_if' =>
            'Si la fuente de referencia es "other", debes especificarla.',
            'referral_employee_name.required_if' =>
            'Si la fuente es "employee_referral", debes poner el nombre del empleado.',
        ];

        // Primero validamos
        $validated = $request->validate($rules, $messages);

        // Validar manualmente que sea mayor de 18 años
        $dob = Carbon::parse($validated['date_of_birth']);
        if ($dob->age < 18) {
            return back()->withErrors(['date_of_birth' => 'Debes tener al menos 18 años.'])->withInput();
        }

        // Validar dirección: si NO ha vivido 3 años en la misma dirección y no hay previous_addresses, error
        // (Replicando la lógica isAddressValid / lived_three_years)
        $livedThreeYears = $request->boolean('lived_three_years');
        $previousAddresses = $validated['previous_addresses'] ?? [];

        // "isAddressValid" en Livewire equivalía a "tengo address de 3 años" 
        // Aquí podrías checar total de años en las direcciones, etc.
        // Simplificando: si no ha vivido 3 años y no trae previous_addresses, error:
        if (!$livedThreeYears && empty($previousAddresses)) {
            return back()->withErrors(['address_history' => 'Debes proveer 3 años de historial de direcciones.'])
                ->withInput();
        }

        // Validar si es elegible para trabajar
        if (!$validated['eligible_to_work']) {
            // Podrías redirigir o tirar un error
            return back()->withErrors(['eligible_to_work' => 'Debes ser elegible para trabajar en U.S.'])->withInput();
        }

        try {
            DB::beginTransaction();
            Log::info('CreateDriver: Iniciando transacción DB');

            // 1. Crear usuario
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status'   => 1, // asumiendo 1 => activo
            ]);
            Log::info('CreateDriver: Usuario creado', ['user_id' => $user->id]);

            // Asignar rol driver
            $user->assignRole('driver');
            Log::info('CreateDriver: Rol asignado driver');

            // 2. Crear detalles del conductor
            $driverDetail = $user->driverDetails()->create([
                'carrier_id'     => $carrier->id,
                'middle_name'    => $validated['middle_name'] ?? null,
                'last_name'      => $validated['last_name'],
                'license_number' => $validated['license_number'],
                'state_of_issue' => $validated['state_of_issue'],
                'phone'          => $validated['phone'],
                'date_of_birth'  => $validated['date_of_birth'],
                'status'         => 1, // asumiendo 1 => activo
            ]);
            Log::info('CreateDriver: Detalles del conductor creados', [
                'driver_detail_id' => $driverDetail->id,
            ]);

            // 3. Manejar la foto si existe
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                Log::info('CreateDriver: Procesando foto de perfil');
                $user->addMedia($file)
                    ->usingFileName(Str::slug($user->name) . '.webp')
                    ->toMediaCollection('profile_photo_driver');
            }

            // 4. Crear aplicación
            $application = DriverApplication::create([
                'user_id' => $user->id,
                'social_security_number' => $validated['social_security_number'] ?? null,
                'status' => 'draft',
            ]);
            Log::info('CreateDriver: Aplicación creada', ['application_id' => $application->id]);

            // 5. Crear dirección principal
            $address = $application->addresses()->create([
                'address_line1'    => $validated['address_line1'],
                'address_line2'    => $validated['address_line2'] ?? null,
                'city'             => $validated['city'],
                'state'            => $validated['state'],
                'zip_code'         => $validated['zip_code'],
                'lived_three_years' => $livedThreeYears,
                'from_date'        => $validated['from_date'],
                'to_date'          => $validated['to_date'] ?? null,
            ]);
            Log::info('CreateDriver: Dirección principal creada', ['address_id' => $address->id]);

            // 6. Crear direcciones anteriores si aplica
            if (!$livedThreeYears && count($previousAddresses)) {
                foreach ($previousAddresses as $prevAddress) {
                    $application->addresses()->create([
                        'address_line1'    => $prevAddress['address_line1'],
                        'address_line2'    => $prevAddress['address_line2'] ?? null,
                        'city'             => $prevAddress['city'],
                        'state'            => $prevAddress['state'],
                        'zip_code'         => $prevAddress['zip_code'],
                        'lived_three_years' => false,
                        'from_date'        => $prevAddress['from_date'],
                        'to_date'          => $prevAddress['to_date'],
                    ]);
                }
            }

            // 7. Crear detalles de la aplicación
            //    Si applying_position = other, usamos applying_position_other.
            //    Si how_did_hear = other, usamos how_did_hear_other.
            //    Si how_did_hear = employee_referral => referral_employee_name.
            $applicationDetails = $application->details()->create([
                'applying_position' => $validated['applying_position'] === 'other'
                    ? $validated['applying_position_other']
                    : $validated['applying_position'],
                'applying_location' => $validated['applying_location'],
                'eligible_to_work'  => $validated['eligible_to_work'],
                'can_speak_english' => $request->boolean('can_speak_english', false),
                'has_twic_card'     => $request->boolean('has_twic_card', false),
                'twic_expiration_date' => $validated['twic_expiration_date'] ?? null,
                'expected_pay'         => $validated['expected_pay'] ?? null,
                'how_did_hear'         => $validated['how_did_hear'] === 'other'
                    ? $validated['how_did_hear_other']
                    : $validated['how_did_hear'],
                'referral_employee_name' => $validated['how_did_hear'] === 'employee_referral'
                    ? $validated['referral_employee_name']
                    : null,
            ]);
            Log::info('CreateDriver: Detalles de aplicación creados', [
                'details_id' => $applicationDetails->id,
            ]);

            // Todo OK, commit
            DB::commit();
            Log::info('CreateDriver: Transacción completada exitosamente.');

            // Redirigir a donde desees (por ejemplo, al edit del nuevo driver)
            return redirect()->route('admin.carrier.user_drivers.edit', [
                'carrier' => $carrier->slug,
                'userDriverDetail' => $driverDetail->driver_number,
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
        $user = $userDriverDetail->user;
        if (!$user) {
            return back()->withErrors('No se encontró el usuario relacionado al Driver.');
        }

        // Cargamos la aplicación (si existe) y sus direcciones
        $application = DriverApplication::where('user_id', $user->id)->first();

        // Para popular el formulario en la vista
        // (Ajusta si tienes relaciones o métodos directos, por ejemplo $user->driverApplication)
        $applicationDetails = $application ? $application->details()->first() : null;
        $addresses = $application ? $application->addresses()->get() : collect([]);
        // Cargar constantes:
        $usStates = \App\Helpers\Constants::usStates();
        $driverPositions = \App\Helpers\Constants::driverPositions();
        $referralSources = \App\Helpers\Constants::referralSources();

        return view('admin.user_driver.edit', [
            'carrier' => $carrier,
            'userDriverDetail' => $userDriverDetail,
            'user' => $user,
            'application' => $application,
            'addresses' => $addresses,
            'applicationDetails' => $applicationDetails,

            // Opcional: para selects
            'usStates' => \App\Helpers\Constants::usStates(),
            'driverPositions' => \App\Helpers\Constants::driverPositions(),
            'referralSources' => \App\Helpers\Constants::referralSources(),
        ]);
    }

    /**
     * Actualizar un driver existente (replicando la lógica Livewire de store).
     */
    public function update(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        $user = $userDriverDetail->user;
        if (!$user) {
            Log::error('No se encontró el usuario relacionado al UserDriverDetail.', [
                'user_driver_detail_id' => $userDriverDetail->id,
            ]);
            return back()->withErrors('No se encontró el usuario relacionado.')->withInput();
        }

        Log::info('Iniciando actualización de driver (lógica Livewire).', [
            'carrier_id' => $carrier->id,
            'user_id' => $user->id,
        ]);

        // 1. Definir reglas (muy parecidas a las de store).
        //    Ajustamos la regla de email para que no choque con el del propio user.
        $rules = [
            // Datos de User
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],

            // La contraseña es opcional en un update, solo si el admin desea cambiarla
            'password' => ['nullable', 'min:8', 'confirmed'],
            'password_confirmation' => ['nullable', 'min:8'],

            // Foto (photo)
            'photo' => ['nullable', 'image', 'max:2048'],

            // Driver details
            'middle_name'     => ['nullable', 'string', 'max:255'],
            'last_name'       => ['required', 'string', 'max:255'],
            'license_number'  => ['required', 'string', 'max:255'],
            'state_of_issue'  => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:15'],
            'date_of_birth'   => ['required', 'date'],

            // Datos de aplicación
            'social_security_number' => ['nullable', 'string', 'max:255'],

            // Direcciones
            'address_line1'   => ['required', 'string', 'max:255'],
            'address_line2'   => ['nullable', 'string', 'max:255'],
            'city'            => ['required', 'string', 'max:255'],
            'state'           => ['required', 'string', 'max:255'],
            'zip_code'        => ['required', 'string', 'max:255'],
            'from_date'       => ['required', 'date'],
            'to_date'         => ['nullable', 'date'],
            'lived_three_years' => ['boolean'],

            // previous_addresses (direcciones anteriores)
            'previous_addresses'                                 => ['array'],
            'previous_addresses.*.address_line1'                => ['required', 'string', 'max:255'],
            'previous_addresses.*.address_line2'                => ['nullable', 'string', 'max:255'],
            'previous_addresses.*.city'                         => ['required', 'string', 'max:255'],
            'previous_addresses.*.state'                        => ['required', 'string', 'max:255'],
            'previous_addresses.*.zip_code'                     => ['required', 'string', 'max:255'],
            'previous_addresses.*.from_date'                    => ['required', 'date'],
            'previous_addresses.*.to_date'                      => ['required', 'date'],

            // Otros campos de la aplicación
            'applying_position'      => ['required', 'string'],
            'applying_position_other' => ['required_if:applying_position,other'],
            'applying_location'      => ['required', 'string', 'max:255'],
            'eligible_to_work'       => ['required', 'boolean'],
            'can_speak_english'      => ['sometimes', 'boolean'],
            'has_twic_card'          => ['sometimes', 'boolean'],
            'twic_expiration_date'   => ['required_if:has_twic_card,true', 'nullable', 'date'],
            'how_did_hear'           => ['required', 'string'],
            'how_did_hear_other'     => ['required_if:how_did_hear,other'],
            'referral_employee_name' => ['required_if:how_did_hear,employee_referral'],
            'expected_pay'           => ['nullable', 'string', 'max:255'],
        ];

        // 2. Mensajes de validación opcionales
        $messages = [
            'twic_expiration_date.required_if' => 'Si tiene TWIC card, debe colocar la fecha de expiración.',
            'applying_position_other.required_if' =>
            'Si la posición aplicada es "other", debes especificar la posición.',
            'how_did_hear_other.required_if' =>
            'Si la fuente de referencia es "other", debes especificarla.',
            'referral_employee_name.required_if' =>
            'Si la fuente es "employee_referral", debes poner el nombre del empleado.',
        ];

        // 3. Validar
        $validated = $request->validate($rules, $messages);

        // 4. Checar edad mínima
        $dob = Carbon::parse($validated['date_of_birth']);
        if ($dob->age < 18) {
            return back()->withErrors(['date_of_birth' => 'Debes tener al menos 18 años.'])->withInput();
        }

        // 5. Validar dirección => livedThreeYears o previous_addresses
        $livedThreeYears = $request->boolean('lived_three_years');
        $previousAddresses = $validated['previous_addresses'] ?? [];
        if (!$livedThreeYears && empty($previousAddresses)) {
            return back()->withErrors(['address_history' => 'Debes proveer 3 años de historial de direcciones.'])
                ->withInput();
        }

        // 6. Verificar elegibilidad
        if (!$validated['eligible_to_work']) {
            return back()->withErrors(['eligible_to_work' => 'Debes ser elegible para trabajar en U.S.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // === A) Actualizar Usuario
            $user->update([
                'name'  => $validated['name'],
                'email' => $validated['email'],
                // si password no es null, lo actualizamos
                'password' => $validated['password']
                    ? Hash::make($validated['password'])
                    : $user->password,
            ]);

            // === B) Actualizar detalles del driver
            $userDriverDetail->update([
                'middle_name'    => $validated['middle_name'] ?? null,
                'last_name'      => $validated['last_name'],
                'license_number' => $validated['license_number'],
                'state_of_issue' => $validated['state_of_issue'],
                'phone'          => $validated['phone'],
                'date_of_birth'  => $validated['date_of_birth'],
                // 'status' => 1 si lo manejas, o según tu lógica
            ]);

            // === C) Manejo de foto
            if ($request->hasFile('photo')) {
                // Eliminamos la foto anterior
                $user->clearMediaCollection('profile_photo_driver');
                // Subimos la nueva
                $user->addMedia($request->file('photo'))
                    ->usingFileName(Str::slug($user->name) . '.webp')
                    ->toMediaCollection('profile_photo_driver');
            }

            // === D) Obtener o crear la DriverApplication
            $application = DriverApplication::where('user_id', $user->id)->first();
            if (!$application) {
                // Si no existe, la creamos. (o puedes forzar error, depende de tu lógica)
                $application = DriverApplication::create([
                    'user_id' => $user->id,
                    'social_security_number' => $validated['social_security_number'] ?? null,
                    'status' => 'draft',
                ]);
            } else {
                // Solo actualizamos SSN si procede
                $application->update([
                    'social_security_number' => $validated['social_security_number'] ?? null,
                    // status => 'draft', o lo que prefieras
                ]);
            }

            // === E) Actualizar direcciones
            //       Para simplificar, borramos todas y las re-creamos (similar a store).
            $application->addresses()->delete();

            // Dirección principal
            $application->addresses()->create([
                'address_line1'     => $validated['address_line1'],
                'address_line2'     => $validated['address_line2'] ?? null,
                'city'              => $validated['city'],
                'state'             => $validated['state'],
                'zip_code'          => $validated['zip_code'],
                'lived_three_years' => $livedThreeYears,
                'from_date'         => $validated['from_date'],
                'to_date'           => $validated['to_date'] ?? null,
            ]);

            // Direcciones anteriores
            if (!$livedThreeYears && count($previousAddresses)) {
                foreach ($previousAddresses as $prev) {
                    $application->addresses()->create([
                        'address_line1'     => $prev['address_line1'],
                        'address_line2'     => $prev['address_line2'] ?? null,
                        'city'              => $prev['city'],
                        'state'             => $prev['state'],
                        'zip_code'          => $prev['zip_code'],
                        'lived_three_years' => false,
                        'from_date'         => $prev['from_date'],
                        'to_date'           => $prev['to_date'],
                    ]);
                }
            }

            // === F) Actualizar detalles de la aplicación
            //       Si no existen, creamos. De lo contrario, actualizamos.
            $applicationDetails = $application->details()->first();
            if (!$applicationDetails) {
                $applicationDetails = $application->details()->create([
                    'applying_position' => $validated['applying_position'] === 'other'
                        ? $validated['applying_position_other']
                        : $validated['applying_position'],
                    'applying_location' => $validated['applying_location'],
                    'eligible_to_work'  => $validated['eligible_to_work'],
                    'can_speak_english' => $request->boolean('can_speak_english', false),
                    'has_twic_card'     => $request->boolean('has_twic_card', false),
                    'twic_expiration_date' => $validated['twic_expiration_date'] ?? null,
                    'expected_pay'         => $validated['expected_pay'] ?? null,
                    'how_did_hear'         => $validated['how_did_hear'] === 'other'
                        ? $validated['how_did_hear_other']
                        : $validated['how_did_hear'],
                    'referral_employee_name' => $validated['how_did_hear'] === 'employee_referral'
                        ? $validated['referral_employee_name']
                        : null,
                ]);
            } else {
                $applicationDetails->update([
                    'applying_position' => $validated['applying_position'] === 'other'
                        ? $validated['applying_position_other']
                        : $validated['applying_position'],
                    'applying_location' => $validated['applying_location'],
                    'eligible_to_work'  => $validated['eligible_to_work'],
                    'can_speak_english' => $request->boolean('can_speak_english', false),
                    'has_twic_card'     => $request->boolean('has_twic_card', false),
                    'twic_expiration_date' => $validated['twic_expiration_date'] ?? null,
                    'expected_pay'         => $validated['expected_pay'] ?? null,
                    'how_did_hear'         => $validated['how_did_hear'] === 'other'
                        ? $validated['how_did_hear_other']
                        : $validated['how_did_hear'],
                    'referral_employee_name' => $validated['how_did_hear'] === 'employee_referral'
                        ? $validated['referral_employee_name']
                        : null,
                ]);
            }

            DB::commit();
            Log::info('Driver actualizado exitosamente.', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
            ]);

            return redirect()
                ->route('admin.carrier.user_drivers.edit', [
                    'carrier' => $carrier->slug,
                    'userDriverDetail' => $userDriverDetail->driver_number,
                ])
                ->with('success', 'Driver actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar driver', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
            ]);
            return back()
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
}
