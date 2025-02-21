<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Carrier;
use App\Helpers\Constants;
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
use App\Models\Admin\Driver\LicenseEndorsement;
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


    protected function calculateYears($fromDate, $toDate = null)
    {
        $from = Carbon::parse($fromDate);
        $to = $toDate ? Carbon::parse($toDate) : Carbon::now();
        return $from->diffInYears($to);
    }


    public function store(Request $request, Carrier $carrier)
    {

        // Agregar un dd() al inicio para ver todo lo que llega


        Log::info('Iniciando store de driver', [
            'carrier_id' => $carrier->id,
            'request_data' => $request->except(['password', 'password_confirmation']),
        ]);
        /*
        Log::info('Datos recibidos de direcciones', [
            'dirección_principal' => $request->only(['address_line1', 'from_date', 'to_date']),
            'direcciones_previas' => $request->input('previous_addresses')
        ]);
        */

        //dd($request->all());
        try {
            // Realizamos la validación directa usando el formato de validate sin reglas explícitas
            $validatedBase = $request->validate([
                // Datos de User
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|confirmed',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                /*
                'license_number' => 'required|string|max:255',
                'state_of_issue' => 'required|string|max:255',
                */
                'phone' => 'required|string|max:15',
                'date_of_birth' => 'required|date',

                // Datos de la aplicación
                //'social_security_number' => 'nullable|string|max:255', // ajusta según tu DB

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
                'previous_addresses' => 'array|required_if:lived_three_years,0',
                'previous_addresses.*.address_line1' => 'required_with:previous_addresses',
                'previous_addresses.*.address_line2' => 'required_with:previous_addresses',
                'previous_addresses.*.city' => 'required_with:previous_addresses',
                'previous_addresses.*.state' => 'required_with:previous_addresses',
                'previous_addresses.*.zip_code' => 'required_with:previous_addresses',
                'previous_addresses.*.from_date' => 'required_with:previous_addresses|date',
                'previous_addresses.*.to_date' => 'required_with:previous_addresses|date|after:previous_addresses.*.from_date',

                // Resto de validaciones
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

            // Validación de los datos de licencia (opcionales en esta etapa)
            $validatedLicenses = null;
            if ($request->has('licenses')) {
                $validatedLicenses = $request->validate([
                    'licenses' => 'array',                    
                    'current_license_number' => 'required|string|max:255', 
                    'licenses.*.license_number' => 'required_with:licenses|string|max:255',
                    'licenses.*.state_of_issue' => 'required_with:licenses|string|max:255',
                    'licenses.*.license_class' => 'required_with:licenses|string|max:255',
                    'licenses.*.expiration_date' => 'required_with:licenses|date',
                    'licenses.*.is_cdl' => 'sometimes|boolean',
                    'licenses.*.endorsements' => 'nullable|array',
                    'licenses.*.license_front' => 'nullable|file|image|max:2048',
                    'licenses.*.license_back' => 'nullable|file|image|max:2048',
                ]);
            }

            // Validación de los datos de experiencia de conducción (opcionales en esta etapa)
            $validatedExperiences = null;
            if ($request->has('experiences')) {
                $validatedExperiences = $request->validate([
                    'experiences' => 'array',
                    'experiences.*.equipment_type' => 'required_with:experiences|string|max:255',
                    'experiences.*.years_experience' => 'required_with:experiences|integer|min:0',
                    'experiences.*.miles_driven' => 'required_with:experiences|integer|min:0',
                    'experiences.*.requires_cdl' => 'sometimes|boolean',
                ]);
            }

            // Validación de los datos médicos (opcionales en esta etapa)
            $validatedMedical = null;
            if ($request->has('social_security_number')) {
                $validatedMedical = $request->validate([
                    'social_security_number' => 'required|string|max:255',
                    'hire_date' => 'nullable|date',
                    'location' => 'nullable|string|max:255',
                    'is_suspended' => 'sometimes|boolean',
                    'suspension_date' => 'nullable|required_if:is_suspended,true|date',
                    'is_terminated' => 'sometimes|boolean',
                    'termination_date' => 'nullable|required_if:is_terminated,true|date',
                    'medical_examiner_name' => 'nullable|string|max:255',
                    'medical_examiner_registry_number' => 'nullable|string|max:255',
                    'medical_card_expiration_date' => 'nullable|date',
                    'medical_card_file' => 'nullable|file|max:2048',
                ]);
            }

            Log::info('Validación completada', [
                'base' => $validatedBase,
                'licenses' => $validatedLicenses,
                'experiences' => $validatedExperiences,
                'medical' => $validatedMedical
            ]);

            // Validación manual de edad mayor de 18
            $dob = Carbon::parse($validatedBase['date_of_birth']);
            if ($dob->age < 18) {
                return back()->withErrors(['date_of_birth' => 'Debes tener al menos 18 años.'])->withInput();
            }

            // Calcular años en dirección actual
            $fromDate = Carbon::parse($validatedBase['from_date']);
            $toDate = $validatedBase['to_date'] ? Carbon::parse($validatedBase['to_date']) : Carbon::now();
            $currentAddressYears = $fromDate->diffInYears($toDate);

            $totalYears = $currentAddressYears;
            $previousAddressesYears = 0;

            // Sumar años de direcciones previas si existen
            if ($request->has('previous_addresses')) {
                foreach ($request->input('previous_addresses') as $address) {
                    if (!empty($address['from_date']) && !empty($address['to_date'])) {
                        $prevFromDate = Carbon::parse($address['from_date']);
                        $prevToDate = Carbon::parse($address['to_date']);
                        $previousAddressesYears += $prevFromDate->diffInYears($prevToDate);
                    }
                }
                $totalYears += $previousAddressesYears;
            }

            Log::info('Cálculo de años de residencia', [
                'años_direccion_actual' => $currentAddressYears,
                'años_direcciones_previas' => $previousAddressesYears,
                'años_totales' => $totalYears,
                'direcciones_previas' => $request->input('previous_addresses')
            ]);

            // Validación del total de años
            if ($totalYears < 3) {
                return back()->withErrors([
                    'address_years' => 'El historial de direcciones debe sumar al menos 3 años. Total actual: ' .
                        number_format($totalYears, 1) . ' años.'
                ])->withInput();
            }

            $livedThreeYears = $totalYears >= 3;

            // Solo validar que se cubran los 3 años si hay direcciones adicionales
            if (!empty($validated['previous_addresses']) && $totalYears < 3) {
                return back()->withErrors([
                    'previous_addresses' => 'El historial de direcciones debe cubrir al menos 3 años. Total actual: ' .
                        number_format($totalYears, 1) . ' años.'
                ])->withInput();
            }

            // Validación si es elegible para trabajar
            if (!$validatedBase['eligible_to_work']) {
                return back()->withErrors(['eligible_to_work' => 'Debes ser elegible para trabajar en U.S.'])->withInput();
            }

            // Inicia la transacción para crear los registros
            DB::beginTransaction();
            Log::info('CreateDriver: Iniciando transacción DB');

            // Crear usuario
            $user = User::create([
                'name' => $validatedBase['name'],
                'email' => $validatedBase['email'],
                'password' => Hash::make($validatedBase['password']),
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
                'middle_name' => $validatedBase['middle_name'],
                'last_name' => $validatedBase['last_name'],
                'phone' => $validatedBase['phone'],
                'date_of_birth' => $validatedBase['date_of_birth'],
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
                'status' => 'draft', // estado 'draft' para la aplicación
            ]);

            Log::info('CreateDriver: Aplicación creada', ['application_id' => $application->id]);

            // Crear dirección principal
            $address = $application->addresses()->create([
                'primary' => 1,
                'address_line1' => $validatedBase['address_line1'],
                'address_line2' => $validatedBase['address_line2'] ?? null,
                'city' => $validatedBase['city'],
                'state' => $validatedBase['state'],
                'zip_code' => $validatedBase['zip_code'],
                'lived_three_years' => $livedThreeYears,
                'from_date' => $validatedBase['from_date'],
                'to_date' => $validatedBase['to_date'] ?? null,
            ]);
            Log::info('CreateDriver: Dirección principal creada', ['address_id' => $address->id]);

            if (!empty($request->input('previous_addresses'))) {
                foreach ($request->input('previous_addresses') as $prevAddress) {
                    // Validar que la dirección tenga los campos requeridos
                    if (
                        !empty($prevAddress['address_line1']) &&
                        !empty($prevAddress['address_line2']) &&
                        !empty($prevAddress['city']) &&
                        !empty($prevAddress['state']) &&
                        !empty($prevAddress['zip_code']) &&
                        !empty($prevAddress['from_date']) &&
                        !empty($prevAddress['to_date'])
                    ) {
                        // Crear cada dirección previa
                        $application->addresses()->create([
                            'primary' => 0, // No es la dirección principal
                            'address_line1' => $prevAddress['address_line1'],
                            'address_line2' => $prevAddress['address_line2'] ?? null,
                            'city' => $prevAddress['city'],
                            'state' => $prevAddress['state'],
                            'zip_code' => $prevAddress['zip_code'],
                            'from_date' => $prevAddress['from_date'],
                            'to_date' => $prevAddress['to_date'],
                            'lived_three_years' => false // Por defecto falso para direcciones previas
                        ]);

                        Log::info('Dirección previa creada', [
                            'address' => $prevAddress,
                            'application_id' => $application->id
                        ]);
                    }
                }
            }

            Log::info('Total años acumulados', ['total' => $totalYears]);

            // Crear detalles de la aplicación
            $applicationDetails = $application->details()->create([
                'applying_position' => $validatedBase['applying_position'],
                'applying_position_other' => $validatedBase['applying_position'] === 'other' ?
                    $validatedBase['applying_position_other'] : null,
                'applying_location' => $validatedBase['applying_location'],
                'eligible_to_work' => $validatedBase['eligible_to_work'],
                'can_speak_english' => $request->boolean('can_speak_english', false),
                'has_twic_card' => $request->boolean('has_twic_card', false),
                'twic_expiration_date' => $validated['twic_expiration_date'] ?? null,
                'expected_pay' => $validatedBase['expected_pay'] ?? null,
                'how_did_hear' => $validatedBase['how_did_hear'],
                'how_did_hear_other' => $validatedBase['how_did_hear'] === 'other' ?
                    $validatedBase['how_did_hear_other'] : null,
                'referral_employee_name' => $validatedBase['how_did_hear'] === 'employee_referral' ?
                    ($validated['referral_employee_name'] ?? null) : null,
            ]);
            Log::info('CreateDriver: Detalles de aplicación creados', ['details_id' => $applicationDetails->id]);


            // Procesar licencias si existen
            if ($validatedLicenses && !empty($validatedLicenses['licenses'])) {
                foreach ($validatedLicenses['licenses'] as $index => $licenseData) {
                    // Saltar entradas de licencia vacías o incompletas
                    if (
                        empty($licenseData['current_license_number']) ||
                        empty($licenseData['license_number']) ||
                        empty($licenseData['state_of_issue']) ||
                        empty($licenseData['license_class']) ||
                        empty($licenseData['expiration_date'])
                    ) {
                        continue;
                    }

                    Log::info('Procesando licencia', [
                        'index' => $index,
                        'data' => $licenseData
                    ]);

                    $license = $userDriverDetail->licenses()->create([
                        'current_license_number' => $licenseData['current_license_number'],
                        'license_number' => $licenseData['license_number'],
                        'state_of_issue' => $licenseData['state_of_issue'],
                        'license_class' => $licenseData['license_class'],
                        'expiration_date' => $licenseData['expiration_date'],
                        'is_cdl' => isset($licenseData['is_cdl']) ? true : false,
                        'is_primary' => $index === 0, // La primera licencia es la principal
                        'status' => 'active',
                    ]);

                    // Guardar imágenes de la licencia si se proporcionaron
                    if (isset($licenseData['license_front']) && $licenseData['license_front']) {
                        $license->addMedia($licenseData['license_front'])
                            ->toMediaCollection('license_front');
                    }
                    if (isset($licenseData['license_back']) && $licenseData['license_back']) {
                        $license->addMedia($licenseData['license_back'])
                            ->toMediaCollection('license_back');
                    }

                    // Guardar endosos si es una licencia CDL y hay endosos seleccionados
                    if (isset($licenseData['is_cdl']) && isset($licenseData['endorsements'])) {
                        foreach ($licenseData['endorsements'] as $endorsementCode) {
                            // Obtener o crear el endoso en la tabla
                            $endorsement = LicenseEndorsement::firstOrCreate(
                                ['code' => $endorsementCode],
                                [
                                    'name' => $this->getEndorsementName($endorsementCode),
                                    'description' => null,
                                    'is_active' => true
                                ]
                            );

                            // Asociar el endoso a la licencia
                            $license->endorsements()->attach($endorsement->id);
                        }
                    }
                }
            }


            // Procesar experiencias de conducción si existen
            if ($validatedExperiences && !empty($validatedExperiences['experiences'])) {
                // Asegúrate de iterar sobre el array independientemente de las claves
                foreach ($validatedExperiences['experiences'] as $experienceData) {
                    // Saltar entradas de experiencia vacías o incompletas
                    if (
                        empty($experienceData['equipment_type']) ||
                        is_null($experienceData['years_experience']) ||
                        is_null($experienceData['miles_driven'])
                    ) {
                        continue;
                    }

                    Log::info('Procesando experiencia', [
                        'data' => $experienceData
                    ]);

                    $userDriverDetail->experiences()->create([
                        'equipment_type' => $experienceData['equipment_type'],
                        'years_experience' => $experienceData['years_experience'],
                        'miles_driven' => $experienceData['miles_driven'],
                        'requires_cdl' => isset($experienceData['requires_cdl']) ? true : false,
                    ]);
                }
            }

            // Procesar información médica si existe
            if ($validatedMedical) {
                $medical = $userDriverDetail->medicalQualification()->create([
                    'hire_date' => $validatedMedical['hire_date'] ?? null,
                    'location' => $validatedMedical['location'] ?? null,
                    'is_suspended' => $request->boolean('is_suspended', false),
                    'suspension_date' => $validatedMedical['suspension_date'] ?? null,
                    'is_terminated' => $request->boolean('is_terminated', false),
                    'termination_date' => $validatedMedical['termination_date'] ?? null,
                    'medical_examiner_name' => $validatedMedical['medical_examiner_name'] ?? null,
                    'medical_examiner_registry_number' => $validatedMedical['medical_examiner_registry_number'] ?? null,
                    'medical_card_expiration_date' => $validatedMedical['medical_card_expiration_date'] ?? null,
                ]);

                // Procesar tarjeta médica si se proporcionó
                if ($request->hasFile('medical_card_file')) {
                    $medical->addMediaFromRequest('medical_card_file')
                        ->toMediaCollection('medical_card');
                }
            }

            // Determinar si la aplicación está completa
            $isCompleted = $this->checkApplicationCompleted($userDriverDetail, $application);
            $userDriverDetail->update([
                'application_completed' => $isCompleted
            ]);

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


    private function getEndorsementName($code)
    {
        $endorsements = [
            'H' => 'Hazardous Materials',
            'N' => 'Tank Vehicle',
            'P' => 'Passenger',
            'T' => 'Double/Triple Trailers',
            'X' => 'Combination of tank vehicle and hazardous materials',
            'S' => 'School Bus'
        ];

        return $endorsements[$code] ?? 'Unknown Endorsement';
    }

    private function checkApplicationCompleted($userDriverDetail, $application)
    {
        // Verificar si tiene al menos:
        // - Una licencia registrada
        // - Al menos una experiencia de conducción
        // - Información médica básica
        $hasLicense = $userDriverDetail->licenses()->exists();
        $hasExperience = $userDriverDetail->experiences()->exists();
        $hasMedical = $userDriverDetail->medicalQualification()->exists();

        return $hasLicense && $hasExperience && $hasMedical;
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

        // Obtener la dirección principal
        $mainAddress = $userDriverDetail->addresses()
            ->where('primary', true)
            ->first();

        // Obtener las direcciones previas
        $previousAddresses = $userDriverDetail->addresses()
            ->where('primary', false)
            ->orderBy('from_date', 'desc')
            ->get();

        // Log para debugging
        Log::info('Recuperando direcciones del driver', [
            'dirección_principal' => $mainAddress,
            'direcciones_previas' => $previousAddresses
        ]);

        // Verificar si tiene foto de perfil
        Log::info('Recuperando foto del Driver', [
            'user_driver_id' => $userDriverDetail->id,
            'media_exists' => $userDriverDetail->hasMedia('profile_photo_driver'),
            'media_url' => $userDriverDetail->getFirstMediaUrl('profile_photo_driver')
        ]);

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
                'previous_addresses.*.address_line2' => 'nullable|string|max:255',
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
                'applying_position' => $validated['applying_position'],
                'applying_position_other' => $validated['applying_position'] === 'other' ?
                    $validated['applying_position_other'] : null,
                'applying_location' => $validated['applying_location'],
                'eligible_to_work' => $validated['eligible_to_work'],
                'can_speak_english' => $validated['can_speak_english'],
                'has_twic_card' => $validated['has_twic_card'],
                'twic_expiration_date' => $validated['twic_expiration_date'],
                'expected_pay' => $validated['expected_pay'],
                'how_did_hear' => $validated['how_did_hear'],
                'how_did_hear_other' => $validated['how_did_hear'] === 'other' ?
                    $validated['how_did_hear_other'] : null,
                'referral_employee_name' => $validated['how_did_hear'] === 'employee_referral' ?
                    ($validated['referral_employee_name'] ?? null) : null,
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
    public function deletePhoto(Carrier $carrier, UserDriverDetail $userDriverDetail)
    {
        try {
            // Eliminar directamente del UserDriverDetail
            if ($userDriverDetail->hasMedia('profile_photo_driver')) {
                $userDriverDetail->clearMediaCollection('profile_photo_driver');

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

            return response()->json(['message' => 'Error deleting photo: ' . $e->getMessage()], 500);
        }
    }
}
