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
use App\Services\Admin\TempUploadService;
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

                'phone' => 'required|string|max:15',
                'date_of_birth' => 'required|date',

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

                // Validación para historial laboral
                'has_work_history' => 'sometimes|boolean',

                'has_attended_training_school' => 'sometimes|boolean',
                'has_traffic_convictions' => 'sometimes|boolean',
                'has_accidents' => 'sometimes|boolean',
            ]);

            // Validación para el historial laboral si está habilitado
            if ($request->has('has_work_history') && $request->boolean('has_work_history')) {
                $request->validate([
                    'work_histories' => 'required|array',
                    'work_histories.*.previous_company' => 'required|string|max:255',
                    'work_histories.*.start_date' => 'required|date',
                    'work_histories.*.end_date' => 'required|date|after_or_equal:work_histories.*.start_date',
                    'work_histories.*.location' => 'required|string|max:255',
                    'work_histories.*.position' => 'required|string|max:255',
                    'work_histories.*.reason_for_leaving' => 'required|string',
                    'work_histories.*.reference_contact' => 'nullable|string|max:255',
                ]);
            }

            // Validación de los datos de licencia (opcionales en esta etapa)
            $validatedLicenses = null;
            if ($request->has('licenses')) {
                $validatedLicenses = $request->validate([
                    'licenses' => 'array',
                    'current_license_number' => 'required|string|max:255',
                    'licenses.*.license_number' => 'required_with:licenses|string|max:255',
                    'licenses.*.state_of_issue' => 'required_with:licenses|string|max:255',
                    'licenses.*.license_class' => 'nullable|string|max:255',
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

            $validatedTraining = null;
            if ($request->boolean('has_attended_training_school')) {
                $validatedTraining = $request->validate([
                    'training_schools' => 'required|array',
                    'training_schools.*.school_name' => 'required|string|max:255',
                    'training_schools.*.city' => 'required|string|max:255',
                    'training_schools.*.state' => 'required|string|max:255',
                    'training_schools.*.phone_number' => 'nullable|string|max:20',
                    'training_schools.*.date_start' => 'required|date',
                    'training_schools.*.date_end' => 'required|date|after_or_equal:training_schools.*.date_start',
                    'training_schools.*.graduated' => 'sometimes|boolean',
                    'training_schools.*.subject_to_safety_regulations' => 'sometimes|boolean',
                    'training_schools.*.performed_safety_functions' => 'sometimes|boolean',
                    'training_schools.*.training_skills' => 'nullable|array',
                ]);
            }

            $validatedTraffic = null;
            // Validaciones condicionales para infracciones de tráfico
            if ($request->boolean('has_traffic_convictions')) {
                $validatedTraffic = $request->validate([
                    'traffic_convictions' => 'required|array',
                    'traffic_convictions.*.conviction_date' => 'required|date',
                    'traffic_convictions.*.location' => 'required|string|max:255',
                    'traffic_convictions.*.charge' => 'required|string|max:255',
                    'traffic_convictions.*.penalty' => 'required|string|max:255',
                ]);
            }

            $validatedAccidents = null;
            if ($request->boolean('has_accidents')) {
                $validatedAccidents = $request->validate([
                    'accidents' => 'required|array',
                    'accidents.*.accident_date' => 'required|date',
                    'accidents.*.nature_of_accident' => 'required|string|max:255',
                    'accidents.*.had_injuries' => 'sometimes|boolean',
                    'accidents.*.number_of_injuries' => 'required_if:accidents.*.had_injuries,1|nullable|integer|min:0',
                    'accidents.*.had_fatalities' => 'sometimes|boolean',
                    'accidents.*.number_of_fatalities' => 'required_if:accidents.*.had_fatalities,1|nullable|integer|min:0',
                    'accidents.*.comments' => 'nullable|string',
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
                'twic_expiration_date' => $validatedBase['twic_expiration_date'] ?? null,
                'expected_pay' => $validatedBase['expected_pay'] ?? null,
                'how_did_hear' => $validatedBase['how_did_hear'],
                'how_did_hear_other' => $validatedBase['how_did_hear'] === 'other' ?
                    $validatedBase['how_did_hear_other'] : null,
                'referral_employee_name' => $validatedBase['how_did_hear'] === 'employee_referral' ?
                    $request->input('referral_employee_name') : null,
                'has_work_history' => $request->boolean('has_work_history', false),
            ]);
            Log::info('CreateDriver: Detalles de aplicación creados', ['details_id' => $applicationDetails->id]);


            // Procesar licencias si existen
            if ($validatedLicenses && !empty($validatedLicenses['licenses'])) {
                foreach ($request->input('licenses') as $index => $licenseDataRaw) {
                    // Obtener datos validados
                    $licenseData = $validatedLicenses['licenses'][$index];

                    // Saltar entradas de licencia vacías o incompletas
                    if (
                        empty($licenseData['license_number']) ||
                        empty($licenseData['state_of_issue']) ||
                        empty($licenseData['license_class']) ||
                        empty($licenseData['expiration_date'])
                    ) {
                        continue;
                    }

                    Log::info('Procesando licencia', [
                        'index' => $index,
                        'data' => $licenseData,
                        'raw_data' => $licenseDataRaw
                    ]);

                    $license = $userDriverDetail->licenses()->create([
                        'current_license_number' => $request->input('current_license_number'),
                        'license_number' => $licenseData['license_number'],
                        'state_of_issue' => $licenseData['state_of_issue'],
                        'license_class' => $licenseData['license_class'],
                        'expiration_date' => $licenseData['expiration_date'],
                        'is_cdl' => isset($licenseData['is_cdl']) ? true : false,
                        'is_primary' => $index === 0, // La primera licencia es la principal
                        'status' => 'active',
                    ]);

                    // IMPORTANTE: Verifica los tokens en los datos originales, NO en los validados
                    if (!empty($licenseDataRaw['temp_front_token'])) {
                        Log::info('Encontrado token frontal para licencia', [
                            'license_id' => $license->id,
                            'token' => $licenseDataRaw['temp_front_token']
                        ]);

                        try {
                            // Instancia del servicio
                            $tempUploadService = app(\App\Services\Admin\TempUploadService::class);

                            // Obtener ruta física
                            $tempPath = $tempUploadService->moveToPermanent($licenseDataRaw['temp_front_token']);

                            if ($tempPath && file_exists($tempPath)) {
                                // Usar la ruta física directamente
                                $license->addMedia($tempPath)
                                    ->toMediaCollection('license_front');

                                Log::info('Imagen frontal agregada a licencia', [
                                    'license_id' => $license->id,
                                    'path' => $tempPath
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error procesando imagen frontal', [
                                'error' => $e->getMessage(),
                                'license_id' => $license->id
                            ]);
                        }
                    }

                    // Repetir para imagen trasera
                    if (!empty($licenseDataRaw['temp_back_token'])) {
                        Log::info('Encontrado token trasero para licencia', [
                            'license_id' => $license->id,
                            'token' => $licenseDataRaw['temp_back_token']
                        ]);

                        try {
                            // Instancia del servicio
                            $tempUploadService = app(\App\Services\Admin\TempUploadService::class);

                            // Obtener ruta física
                            $tempPath = $tempUploadService->moveToPermanent($licenseDataRaw['temp_back_token']);

                            if ($tempPath && file_exists($tempPath)) {
                                // Usar la ruta física directamente
                                $license->addMedia($tempPath)
                                    ->toMediaCollection('license_back');

                                Log::info('Imagen trasera agregada a licencia', [
                                    'license_id' => $license->id,
                                    'path' => $tempPath
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error procesando imagen trasera', [
                                'error' => $e->getMessage(),
                                'license_id' => $license->id
                            ]);
                        }
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
                            $license->endorsements()->attach($endorsement->id, [
                                'issued_date' => now(),
                                'expiration_date' => $licenseData['expiration_date']
                            ]);
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

            // NUEVO BLOQUE: Procesar historial laboral si existe
            if ($request->has('has_work_history') && $request->boolean('has_work_history')) {
                Log::info('Procesando historiales laborales', [
                    'has_work_history' => true,
                    'work_histories_count' => is_array($request->work_histories) ? count($request->work_histories) : 0
                ]);

                // Si hay datos de historial laboral
                if ($request->has('work_histories') && is_array($request->work_histories)) {
                    foreach ($request->work_histories as $workHistoryData) {
                        // Verificar que tenga los datos mínimos necesarios
                        if (
                            !empty($workHistoryData['previous_company']) &&
                            !empty($workHistoryData['start_date']) &&
                            !empty($workHistoryData['end_date']) &&
                            !empty($workHistoryData['location']) &&
                            !empty($workHistoryData['position'])
                        ) {
                            // Crear el registro de historial laboral
                            $userDriverDetail->workHistories()->create([
                                'previous_company' => $workHistoryData['previous_company'],
                                'start_date' => $workHistoryData['start_date'],
                                'end_date' => $workHistoryData['end_date'],
                                'location' => $workHistoryData['location'],
                                'position' => $workHistoryData['position'],
                                'reason_for_leaving' => $workHistoryData['reason_for_leaving'] ?? null,
                                'reference_contact' => $workHistoryData['reference_contact'] ?? null,
                            ]);

                            Log::info('Historial laboral creado', [
                                'company' => $workHistoryData['previous_company'],
                                'user_driver_id' => $userDriverDetail->id
                            ]);
                        }
                    }
                }
            }

            // Procesar información médica si existe
            if ($validatedMedical) {
                $medical = $userDriverDetail->medicalQualification()->create([
                    'social_security_number' => $request->input('social_security_number'),
                    'hire_date' => $request->input('hire_date'),
                    'location' => $request->input('location'),
                    'is_suspended' => $request->boolean('is_suspended', false),
                    'suspension_date' => $request->input('suspension_date'),
                    'is_terminated' => $request->boolean('is_terminated', false),
                    'termination_date' => $request->input('termination_date'),
                    'medical_examiner_name' => $request->input('medical_examiner_name'),
                    'medical_examiner_registry_number' => $request->input('medical_examiner_registry_number'),
                    'medical_card_expiration_date' => $request->input('medical_card_expiration_date')
                ]);

                // Procesar tarjeta médica si se proporcionó
                if ($request->hasFile('medical_card_file')) {
                    $medical->addMediaFromRequest('medical_card_file')
                        ->toMediaCollection('medical_card');
                }
            }

            // PROCESAR FORMACIÓN DE CONDUCTORES
            if ($request->boolean('has_attended_training_school') && $request->has('training_schools')) {
                Log::info('Procesando escuelas de formación', [
                    'has_attended_training_school' => true,
                    'training_schools_count' => count($request->training_schools)
                ]);

                foreach ($request->training_schools as $schoolData) {
                    // Verificar datos mínimos necesarios
                    if (
                        empty($schoolData['school_name']) ||
                        empty($schoolData['date_start']) ||
                        empty($schoolData['date_end'])
                    ) {
                        continue;
                    }

                    // Crear registro de formación
                    $trainingSchool = $userDriverDetail->trainingSchools()->create([
                        'school_name' => $schoolData['school_name'],
                        'city' => $schoolData['city'],
                        'state' => $schoolData['state'],
                        'phone_number' => $schoolData['phone_number'] ?? null,
                        'date_start' => $schoolData['date_start'],
                        'date_end' => $schoolData['date_end'],
                        'graduated' => isset($schoolData['graduated']),
                        'subject_to_safety_regulations' => isset($schoolData['subject_to_safety_regulations']),
                        'performed_safety_functions' => isset($schoolData['performed_safety_functions']),
                        'training_skills' => isset($schoolData['training_skills']) ? $schoolData['training_skills'] : [],
                    ]);

                    Log::info('Escuela de formación creada', [
                        'school_id' => $trainingSchool->id,
                        'school_name' => $schoolData['school_name']
                    ]);
                }
            }

            // PROCESAR INFRACCIONES DE TRÁFICO
            if ($request->boolean('has_traffic_convictions') && $request->has('traffic_convictions')) {
                Log::info('Procesando infracciones de tráfico', [
                    'has_traffic_convictions' => true,
                    'traffic_convictions_count' => count($request->traffic_convictions)
                ]);

                foreach ($request->traffic_convictions as $convictionData) {
                    // Verificar datos mínimos necesarios
                    if (
                        empty($convictionData['conviction_date']) ||
                        empty($convictionData['location']) ||
                        empty($convictionData['charge']) ||
                        empty($convictionData['penalty'])
                    ) {
                        continue;
                    }

                    // Crear registro de infracción
                    $trafficConviction = $userDriverDetail->trafficConvictions()->create([
                        'conviction_date' => $convictionData['conviction_date'],
                        'location' => $convictionData['location'],
                        'charge' => $convictionData['charge'],
                        'penalty' => $convictionData['penalty'],
                    ]);

                    Log::info('Infracción de tráfico creada', [
                        'conviction_id' => $trafficConviction->id,
                        'charge' => $convictionData['charge']
                    ]);
                }
            }

            // PROCESAR REGISTRO DE ACCIDENTES
            if ($request->boolean('has_accidents') && $request->has('accidents')) {
                Log::info('Procesando registros de accidentes', [
                    'has_accidents' => true,
                    'accidents_count' => count($request->accidents)
                ]);

                foreach ($request->accidents as $accidentData) {
                    // Verificar datos mínimos necesarios
                    if (
                        empty($accidentData['accident_date']) ||
                        empty($accidentData['nature_of_accident'])
                    ) {
                        continue;
                    }

                    // Crear registro de accidente
                    $accident = $userDriverDetail->accidents()->create([
                        'accident_date' => $accidentData['accident_date'],
                        'nature_of_accident' => $accidentData['nature_of_accident'],
                        'had_injuries' => isset($accidentData['had_injuries']),
                        'number_of_injuries' => isset($accidentData['had_injuries']) ? ($accidentData['number_of_injuries'] ?? 0) : 0,
                        'had_fatalities' => isset($accidentData['had_fatalities']),
                        'number_of_fatalities' => isset($accidentData['had_fatalities']) ? ($accidentData['number_of_fatalities'] ?? 0) : 0,
                        'comments' => $accidentData['comments'] ?? null,
                    ]);

                    Log::info('Registro de accidente creado', [
                        'accident_id' => $accident->id,
                        'accident_date' => $accidentData['accident_date']
                    ]);
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
            'user',
            'workHistories' // Añadir esta relación para cargar el historial laboral
        ]);

        // Obtener la dirección principal
        $mainAddress = $userDriverDetail->addresses()
            ->where('primary', true)
            ->first();

        // Obtener las direcciones previas
        $previousAddresses = $userDriverDetail->addresses()
            ->where('primary', false)
            ->orderBy('from_date', 'desc')
            ->get();

        // Obtener el historial laboral
        $workHistories = $userDriverDetail->workHistories;

        // Log para debugging
        Log::info('Recuperando datos del driver', [
            'dirección_principal' => $mainAddress,
            'direcciones_previas' => $previousAddresses,
            'historial_laboral' => $workHistories
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
            'workHistories',
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
                // Datos de User
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $userDriverDetail->user->id,
                'password' => 'nullable|min:8|confirmed',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
                'date_of_birth' => 'required|date',
                'status' => 'required|integer|in:0,1,2',

                // Direcciones
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'zip_code' => 'required|string|max:255',
                'from_date' => 'required|date',
                'to_date' => 'nullable|date',
                'lived_three_years' => 'boolean',

                // Direcciones anteriores
                'previous_addresses' => 'array|required_if:lived_three_years,0',
                'previous_addresses.*.address_line1' => 'required_with:previous_addresses',
                'previous_addresses.*.address_line2' => 'nullable|string|max:255',
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
                'twic_expiration_date' => 'nullable|date|required_if:has_twic_card,1',
                'how_did_hear' => 'required|string',
                'how_did_hear_other' => 'required_if:how_did_hear,other',
                'referral_employee_name' => 'required_if:how_did_hear,employee_referral',
                'expected_pay' => 'nullable|string|max:255',

                // Validación para historial laboral
                'has_work_history' => 'sometimes|boolean',
                'work_histories' => 'array|required_if:has_work_history,1',
                'work_histories.*.id' => 'nullable|integer|exists:driver_work_history,id',
                'work_histories.*.previous_company' => 'required_with:work_histories|string|max:255',
                'work_histories.*.start_date' => 'required_with:work_histories|date',
                'work_histories.*.end_date' => 'required_with:work_histories|date|after_or_equal:work_histories.*.start_date',
                'work_histories.*.location' => 'required_with:work_histories|string|max:255',
                'work_histories.*.position' => 'required_with:work_histories|string|max:255',
                'work_histories.*.reason_for_leaving' => 'required_with:work_histories|string',
                'work_histories.*.reference_contact' => 'nullable|string|max:255',
            ]);

            // Validar edad mayor de 18
            $dob = Carbon::parse($validated['date_of_birth']);
            if ($dob->age < 18) {
                return back()->withErrors(['date_of_birth' => 'Debes tener al menos 18 años.'])->withInput();
            }

            // Calcular años en dirección actual
            $fromDate = Carbon::parse($validated['from_date']);
            $toDate = $validated['to_date'] ? Carbon::parse($validated['to_date']) : Carbon::now();
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

            // Validar cobertura de 3 años si lived_three_years es falso
            if (!$validated['lived_three_years'] && $totalYears < 3) {
                return back()->withErrors([
                    'address_years' => 'El historial de direcciones debe sumar al menos 3 años. Total actual: ' .
                        number_format($totalYears, 1) . ' años.'
                ])->withInput();
            }

            // Iniciar transacción
            DB::beginTransaction();

            // Actualizar datos de usuario
            $user = $userDriverDetail->user;
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status']
            ];

            // Solo actualizar password si se proporcionó uno nuevo
            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            // Actualizar detalles del conductor
            $userDriverDetail->update([
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'date_of_birth' => $validated['date_of_birth'],
                'status' => $validated['status'],
            ]);

            // Manejar foto del conductor
            if ($request->hasFile('photo')) {
                $userDriverDetail->clearMediaCollection('profile_photo_driver');
                $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp';
                $userDriverDetail->addMediaFromRequest('photo')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');
            }

            // Manejar foto temporal si se usó el servicio de carga temporal
            if ($request->has('temp_photo_token') && $request->input('temp_photo_token')) {
                $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                $tempPath = $tempUploadService->moveToPermanent($request->input('temp_photo_token'));

                if ($tempPath && file_exists($tempPath)) {
                    $userDriverDetail->clearMediaCollection('profile_photo_driver');
                    $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp';
                    $userDriverDetail->addMedia($tempPath)
                        ->usingFileName($fileName)
                        ->toMediaCollection('profile_photo_driver');
                }
            }

            // Obtener o crear la aplicación si no existe
            $application = $userDriverDetail->application;
            if (!$application) {
                $application = DriverApplication::create([
                    'user_id' => $user->id,
                    'status' => 'draft'
                ]);
            }

            // Actualizar dirección principal
            $address = $application->addresses()->updateOrCreate(
                ['primary' => true],
                [
                    'address_line1' => $validated['address_line1'],
                    'address_line2' => $validated['address_line2'] ?? null,
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'zip_code' => $validated['zip_code'],
                    'lived_three_years' => $validated['lived_three_years'],
                    'from_date' => $validated['from_date'],
                    'to_date' => $validated['to_date'] ?? null,
                ]
            );

            // Si no ha vivido 3 años en la dirección actual, actualizar direcciones previas
            if (!$validated['lived_three_years']) {
                // Eliminar direcciones previas existentes
                $application->addresses()->where('primary', false)->delete();

                // Crear nuevas direcciones previas
                if (!empty($request->input('previous_addresses'))) {
                    foreach ($request->input('previous_addresses') as $prevAddress) {
                        if (
                            !empty($prevAddress['address_line1']) &&
                            !empty($prevAddress['city']) &&
                            !empty($prevAddress['state']) &&
                            !empty($prevAddress['zip_code']) &&
                            !empty($prevAddress['from_date']) &&
                            !empty($prevAddress['to_date'])
                        ) {
                            $application->addresses()->create([
                                'primary' => false,
                                'address_line1' => $prevAddress['address_line1'],
                                'address_line2' => $prevAddress['address_line2'] ?? null,
                                'city' => $prevAddress['city'],
                                'state' => $prevAddress['state'],
                                'zip_code' => $prevAddress['zip_code'],
                                'from_date' => $prevAddress['from_date'],
                                'to_date' => $prevAddress['to_date'],
                                'lived_three_years' => false
                            ]);
                        }
                    }
                }
            }

            // ACTUALIZAR HISTORIAL LABORAL
            if ($request->has('has_work_history')) {
                $hasWorkHistory = $request->boolean('has_work_history');

                // Si no tiene historial laboral, eliminar registros existentes
                if (!$hasWorkHistory) {
                    $userDriverDetail->workHistories()->delete();
                } else if ($request->has('work_histories')) {
                    // Obtener IDs existentes para detectar eliminaciones
                    $existingWorkHistoryIds = $userDriverDetail->workHistories()->pluck('id')->toArray();
                    $updatedWorkHistoryIds = [];

                    foreach ($request->input('work_histories') as $workHistoryData) {
                        // Verificar datos mínimos necesarios
                        if (
                            empty($workHistoryData['previous_company']) ||
                            empty($workHistoryData['start_date']) ||
                            empty($workHistoryData['end_date']) ||
                            empty($workHistoryData['location']) ||
                            empty($workHistoryData['position'])
                        ) {
                            continue;
                        }

                        // Si tiene ID, es un historial existente
                        $workHistoryId = $workHistoryData['id'] ?? null;
                        $workHistory = null;

                        if ($workHistoryId) {
                            $workHistory = $userDriverDetail->workHistories()->find($workHistoryId);
                        }

                        if (!$workHistory) {
                            // Crear nuevo historial laboral
                            $workHistory = $userDriverDetail->workHistories()->create([
                                'previous_company' => $workHistoryData['previous_company'],
                                'start_date' => $workHistoryData['start_date'],
                                'end_date' => $workHistoryData['end_date'],
                                'location' => $workHistoryData['location'],
                                'position' => $workHistoryData['position'],
                                'reason_for_leaving' => $workHistoryData['reason_for_leaving'] ?? null,
                                'reference_contact' => $workHistoryData['reference_contact'] ?? null,
                            ]);
                        } else {
                            // Actualizar historial existente
                            $workHistory->update([
                                'previous_company' => $workHistoryData['previous_company'],
                                'start_date' => $workHistoryData['start_date'],
                                'end_date' => $workHistoryData['end_date'],
                                'location' => $workHistoryData['location'],
                                'position' => $workHistoryData['position'],
                                'reason_for_leaving' => $workHistoryData['reason_for_leaving'] ?? null,
                                'reference_contact' => $workHistoryData['reference_contact'] ?? null,
                            ]);
                        }

                        $updatedWorkHistoryIds[] = $workHistory->id;
                    }

                    // Eliminar historiales laborales que ya no existen en la actualización
                    $workHistoriesToDelete = array_diff($existingWorkHistoryIds, $updatedWorkHistoryIds);
                    if (!empty($workHistoriesToDelete)) {
                        $userDriverDetail->workHistories()->whereIn('id', $workHistoriesToDelete)->delete();
                    }
                }
            }

            // Actualizar o crear detalles de la aplicación
            $applicationDetails = $application->details()->updateOrCreate(
                [], // Encuentra el primero (o crea si no existe)
                [
                    'applying_position' => $validated['applying_position'],
                    'applying_position_other' => $validated['applying_position'] === 'other' ?
                        $validated['applying_position_other'] : null,
                    'applying_location' => $validated['applying_location'],
                    'eligible_to_work' => $validated['eligible_to_work'],
                    'can_speak_english' => $request->boolean('can_speak_english', false),
                    'has_twic_card' => $request->boolean('has_twic_card', false),
                    'twic_expiration_date' => $validated['twic_expiration_date'] ?? null,
                    'expected_pay' => $validated['expected_pay'] ?? null,
                    'how_did_hear' => $validated['how_did_hear'],
                    'how_did_hear_other' => $validated['how_did_hear'] === 'other' ?
                        $validated['how_did_hear_other'] : null,
                    'referral_employee_name' => $validated['how_did_hear'] === 'employee_referral' ?
                        $request->input('referral_employee_name') : null,
                ]
            );

            // ACTUALIZAR LICENCIAS
            if ($request->has('licenses')) {
                // Obtener IDs existentes para detectar eliminaciones
                $existingLicenseIds = $userDriverDetail->licenses()->pluck('id')->toArray();
                $updatedLicenseIds = [];

                foreach ($request->input('licenses') as $index => $licenseDataRaw) {
                    // Verificar si tiene los datos mínimos necesarios
                    if (
                        empty($licenseDataRaw['license_number']) ||
                        empty($licenseDataRaw['state_of_issue']) ||
                        empty($licenseDataRaw['license_class']) ||
                        empty($licenseDataRaw['expiration_date'])
                    ) {
                        continue;
                    }

                    // Si tiene ID, es una licencia existente
                    $licenseId = $licenseDataRaw['id'] ?? null;
                    $license = null;

                    if ($licenseId) {
                        $license = $userDriverDetail->licenses()->find($licenseId);
                    }

                    if (!$license) {
                        // Crear nueva licencia
                        $license = $userDriverDetail->licenses()->create([
                            'current_license_number' => $request->input('current_license_number', ''),
                            'license_number' => $licenseDataRaw['license_number'],
                            'state_of_issue' => $licenseDataRaw['state_of_issue'],
                            'license_class' => $licenseDataRaw['license_class'],
                            'expiration_date' => $licenseDataRaw['expiration_date'],
                            'is_cdl' => isset($licenseDataRaw['is_cdl']) ? true : false,
                            'is_primary' => $index === 0, // La primera es la principal
                            'status' => 'active',
                        ]);
                    } else {
                        // Actualizar licencia existente
                        $license->update([
                            'license_number' => $licenseDataRaw['license_number'],
                            'state_of_issue' => $licenseDataRaw['state_of_issue'],
                            'license_class' => $licenseDataRaw['license_class'],
                            'expiration_date' => $licenseDataRaw['expiration_date'],
                            'is_cdl' => isset($licenseDataRaw['is_cdl']) ? true : false,
                            'is_primary' => $index === 0,
                        ]);
                    }

                    $updatedLicenseIds[] = $license->id;

                    // Gestionar endosos
                    if (isset($licenseDataRaw['is_cdl']) && isset($licenseDataRaw['endorsements'])) {
                        // Eliminar endosos existentes
                        $license->endorsements()->detach();

                        // Crear nuevos endosos
                        foreach ($licenseDataRaw['endorsements'] as $endorsementCode) {
                            $endorsement = LicenseEndorsement::firstOrCreate(
                                ['code' => $endorsementCode],
                                [
                                    'name' => $this->getEndorsementName($endorsementCode),
                                    'description' => null,
                                    'is_active' => true
                                ]
                            );

                            $license->endorsements()->attach($endorsement->id, [
                                'issued_date' => now(),
                                'expiration_date' => $licenseDataRaw['expiration_date']
                            ]);
                        }
                    }

                    // Procesar imágenes usando el servicio de carga temporal
                    if (!empty($licenseDataRaw['temp_front_token'])) {
                        $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                        $tempPath = $tempUploadService->moveToPermanent($licenseDataRaw['temp_front_token']);

                        if ($tempPath && file_exists($tempPath)) {
                            $license->clearMediaCollection('license_front');
                            $license->addMedia($tempPath)
                                ->toMediaCollection('license_front');

                            Log::info('Imagen frontal actualizada en licencia', [
                                'license_id' => $license->id,
                                'path' => $tempPath
                            ]);
                        }
                    }

                    if (!empty($licenseDataRaw['temp_back_token'])) {
                        $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                        $tempPath = $tempUploadService->moveToPermanent($licenseDataRaw['temp_back_token']);

                        if ($tempPath && file_exists($tempPath)) {
                            $license->clearMediaCollection('license_back');
                            $license->addMedia($tempPath)
                                ->toMediaCollection('license_back');

                            Log::info('Imagen trasera actualizada en licencia', [
                                'license_id' => $license->id,
                                'path' => $tempPath
                            ]);
                        }
                    }
                }

                // Eliminar licencias que ya no existen en la actualización
                $licensesToDelete = array_diff($existingLicenseIds, $updatedLicenseIds);
                if (!empty($licensesToDelete)) {
                    $userDriverDetail->licenses()->whereIn('id', $licensesToDelete)->delete();
                }
            }

            // ACTUALIZAR EXPERIENCIAS
            if ($request->has('experiences')) {
                // Obtener IDs existentes para detectar eliminaciones
                $existingExpIds = $userDriverDetail->experiences()->pluck('id')->toArray();
                $updatedExpIds = [];

                foreach ($request->input('experiences') as $expData) {
                    // Verificar datos mínimos necesarios
                    if (
                        empty($expData['equipment_type']) ||
                        !isset($expData['years_experience']) ||
                        !isset($expData['miles_driven'])
                    ) {
                        continue;
                    }

                    // Si tiene ID, es una experiencia existente
                    $expId = $expData['id'] ?? null;
                    $experience = null;

                    if ($expId) {
                        $experience = $userDriverDetail->experiences()->find($expId);
                    }

                    if (!$experience) {
                        // Crear nueva experiencia
                        $experience = $userDriverDetail->experiences()->create([
                            'equipment_type' => $expData['equipment_type'],
                            'years_experience' => $expData['years_experience'],
                            'miles_driven' => $expData['miles_driven'],
                            'requires_cdl' => isset($expData['requires_cdl']) ? true : false,
                        ]);
                    } else {
                        // Actualizar experiencia existente
                        $experience->update([
                            'equipment_type' => $expData['equipment_type'],
                            'years_experience' => $expData['years_experience'],
                            'miles_driven' => $expData['miles_driven'],
                            'requires_cdl' => isset($expData['requires_cdl']) ? true : false,
                        ]);
                    }

                    $updatedExpIds[] = $experience->id;
                }

                // Eliminar experiencias que ya no existen en la actualización
                $expsToDelete = array_diff($existingExpIds, $updatedExpIds);
                if (!empty($expsToDelete)) {
                    $userDriverDetail->experiences()->whereIn('id', $expsToDelete)->delete();
                }
            }

            // ACTUALIZAR INFORMACIÓN MÉDICA
            if ($request->has('social_security_number')) {
                // Crear o actualizar la información médica
                $medical = $userDriverDetail->medicalQualification()->updateOrCreate(
                    [], // Solo una entrada por conductor
                    [
                        'social_security_number' => $request->input('social_security_number'),
                        'hire_date' => $request->input('hire_date'),
                        'location' => $request->input('location'),
                        'is_suspended' => $request->boolean('is_suspended', false),
                        'suspension_date' => $request->input('suspension_date'),
                        'is_terminated' => $request->boolean('is_terminated', false),
                        'termination_date' => $request->input('termination_date'),
                        'medical_examiner_name' => $request->input('medical_examiner_name'),
                        'medical_examiner_registry_number' => $request->input('medical_examiner_registry_number'),
                        'medical_card_expiration_date' => $request->input('medical_card_expiration_date')
                    ]
                );

                // Procesar archivo médico utilizando el servicio de carga temporal
                if ($request->has('temp_medical_card_token') && $request->input('temp_medical_card_token')) {
                    $tempUploadService = app(\App\Services\Admin\TempUploadService::class);
                    $tempPath = $tempUploadService->moveToPermanent($request->input('temp_medical_card_token'));

                    if ($tempPath && file_exists($tempPath)) {
                        $medical->clearMediaCollection('medical_card');
                        $medical->addMedia($tempPath)
                            ->toMediaCollection('medical_card');

                        Log::info('Tarjeta médica actualizada', [
                            'medical_id' => $medical->id,
                            'path' => $tempPath
                        ]);
                    }
                } else if ($request->hasFile('medical_card_file')) {
                    $medical->clearMediaCollection('medical_card');
                    $medical->addMediaFromRequest('medical_card_file')
                        ->toMediaCollection('medical_card');
                }
            }

            // Verificar si la aplicación está completa
            $isCompleted = $this->checkApplicationCompleted($userDriverDetail, $application);
            $userDriverDetail->update([
                'application_completed' => $isCompleted
            ]);

            // Confirmar transacción
            DB::commit();
            Log::info('UpdateDriver: Transacción completada exitosamente', [
                'user_driver_id' => $userDriverDetail->id
            ]);

            return redirect()->route('admin.carrier.user_drivers.index', [
                'carrier' => $carrier->slug,
            ])->with('success', 'Driver actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('UpdateDriver: Error en la transacción DB', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Error actualizando el driver: ' . $e->getMessage()])
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
