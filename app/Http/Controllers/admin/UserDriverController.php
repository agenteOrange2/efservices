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
use App\Services\Admin\DriverStepService;
use App\Services\Admin\TempUploadService;
use Illuminate\Support\Facades\Notification;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\LicenseEndorsement;
use App\Notifications\Admin\Driver\NewUserDriverNotification;
use App\Notifications\Admin\Driver\NewDriverNotificationAdmin;
use App\Notifications\Admin\Driver\NewDriverCreatedNotification;

class UserDriverController extends Controller
{

    protected $driverStepService;

    public function __construct(DriverStepService $driverStepService)
    {
        $this->driverStepService = $driverStepService;
    }

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

        //dd($request->all());
        // Obtener la pestaña activa desde el formulario
        $activeTab = $request->input('active_tab', 'general');
        $submissionType = $request->input('submission_type', 'partial');
        $isFullSubmit = $submissionType === 'complete';

        Log::info('Iniciando store de driver', [
            'carrier_id' => $carrier->id,
            'active_tab' => $activeTab,
            'submission_type' => $submissionType,
            'request_data' => $request->except(['password', 'password_confirmation']),
        ]);

        try {
            // Validación según la pestaña activa
            $validationRules = $this->getValidationRulesForTab($activeTab);
            $validatedData = $request->validate($validationRules);

            // Inicia la transacción para crear los registros
            DB::beginTransaction();
            Log::info('CreateDriver: Iniciando transacción DB');

            // Verificar si es una creación inicial o una actualización
            $user = null;
            $userDriverDetail = null;
            $application = null;

            if ($request->has('user_id')) {
                // Es una actualización, buscar el usuario y los detalles existentes
                $user = User::find($request->input('user_id'));
                if ($user) {
                    $userDriverDetail = UserDriverDetail::where('user_id', $user->id)->first();
                    if ($userDriverDetail) {
                        $application = DriverApplication::where('user_id', $user->id)->first();
                    }
                }
            }

            // Si no existe el usuario, crearlo
            if (!$user) {
                // Crear usuario
                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                    'status' => 1, // 1 = activo
                ]);

                // Asignar rol de 'driver' al usuario
                $user->assignRole('driver');
                Log::info('CreateDriver: Usuario y rol asignados', ['user_id' => $user->id]);
            }

            // Si no existe el detalle del conductor, crearlo
            if (!$userDriverDetail) {
                $userDriverDetail = UserDriverDetail::create([
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id,
                    'middle_name' => $request->input('middle_name'),
                    'last_name' => $request->input('last_name'),
                    'phone' => $request->input('phone'),
                    'date_of_birth' => $request->input('date_of_birth'),
                    'status' => 1, // 1 = activo
                    'terms_accepted' => $request->has('terms_accepted') ? true : false,
                    'confirmation_token' => Str::random(60), // token de confirmación
                    'current_step' => $this->driverStepService::STEP_GENERAL,
                ]);
                Log::info('Detalles del driver creados', ['user_driver_id' => $userDriverDetail->id]);
            } else {
                // Actualizar datos existentes
                $userDriverDetail->update([
                    'middle_name' => $request->input('middle_name', $userDriverDetail->middle_name),
                    'last_name' => $request->input('last_name', $userDriverDetail->last_name),
                    'phone' => $request->input('phone', $userDriverDetail->phone),
                    'date_of_birth' => $request->input('date_of_birth', $userDriverDetail->date_of_birth),
                    'terms_accepted' => $request->has('terms_accepted') ? true : $userDriverDetail->terms_accepted,
                ]);
            }

            // Procesar imagen de perfil si se ha subido
            if ($request->hasFile('photo')) {
                $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp';
                // Eliminar foto anterior si existe
                $userDriverDetail->clearMediaCollection('profile_photo_driver');
                // Subir nueva foto
                $userDriverDetail->addMediaFromRequest('photo')
                    ->usingFileName($fileName)
                    ->toMediaCollection('profile_photo_driver');

                Log::info('CreateDriver: Procesando foto de perfil', [
                    'file_exists' => true,
                    'original_name' => $request->file('photo')->getClientOriginalName()
                ]);
            }

            // Crear o actualizar la aplicación del driver
            if (!$application) {
                $application = DriverApplication::create([
                    'user_id' => $user->id,
                    'status' => 'draft', // estado 'draft' para la aplicación
                ]);
                Log::info('CreateDriver: Aplicación creada', ['application_id' => $application->id]);
            }

            // Procesar datos según la pestaña activa
            switch ($activeTab) {
                case 'general':
                    $this->processGeneralTab($request, $userDriverDetail, $application);
                    // Actualizar el paso actual
                    $userDriverDetail->update(['current_step' => $this->driverStepService::STEP_LICENSES]);
                    $userDriverDetail->save();
                    break;

                case 'licenses':
                    $this->processLicensesTab($request, $userDriverDetail);
                    // Actualizar el paso actual
                    $userDriverDetail->update(['current_step' => $this->driverStepService::STEP_MEDICAL]);
                    break;

                case 'medical':
                    $this->processMedicalTab($request, $userDriverDetail);
                    // Actualizar el paso actual
                    $userDriverDetail->update(['current_step' => $this->driverStepService::STEP_TRAINING]);
                    break;

                case 'training':
                    $this->processTrainingTab($request, $userDriverDetail, $application);
                    // Actualizar el paso actual
                    $userDriverDetail->update(['current_step' => $this->driverStepService::STEP_TRAFFIC]);
                    break;

                case 'traffic':
                    $this->processTrafficTab($request, $userDriverDetail, $application);
                    // Actualizar el paso actual
                    $userDriverDetail->update(['current_step' => $this->driverStepService::STEP_ACCIDENT]);
                    break;

                case 'accident':
                    $this->processAccidentTab($request, $userDriverDetail, $application);
                    // Marcar como completado si se completaron todos los pasos
                    $isCompleted = $this->checkApplicationCompleted($userDriverDetail, $application);
                    $userDriverDetail->update(['application_completed' => $isCompleted]);
                    break;
            }

            // Si es envío completo, verificar el estado general
            if ($isFullSubmit) {
                $isCompleted = $this->checkApplicationCompleted($userDriverDetail, $application);
                $userDriverDetail->update(['application_completed' => $isCompleted]);
            }

            // Todo ok, confirmamos la transacción
            DB::commit();
            Log::info('CreateDriver: Transacción completada exitosamente.');

            // Después de cargar las relaciones, obtén un objeto fresco de la base de datos para asegurar que tienes los valores actualizados
            $userDriverDetail = UserDriverDetail::with([
                'user',
                'application.details',
                'application.addresses',
                'licenses',
                'experiences',
                'medicalQualification',
                'workHistories',
                'trainingSchools',
                'trafficConvictions',
                'accidents'
            ])->find($userDriverDetail->id);

            // Redirección según el tipo de envío
            if ($isFullSubmit) {
                return redirect()->route('admin.carrier.user_drivers.index', $carrier)
                    ->with('success', 'Driver guardado correctamente.');
            } else {
                // En lugar de depender de current_step, usa el activeTab para determinar el siguiente
                $nextTabMap = [
                    'general' => 'licenses',
                    'licenses' => 'medical',
                    'medical' => 'training',
                    'training' => 'traffic',
                    'traffic' => 'accident',
                    'accident' => 'general'
                ];

                $nextTab = $nextTabMap[$activeTab] ?? 'licenses';

                Log::info('Redirigiendo a siguiente paso', [
                    'active_tab' => $activeTab,
                    'next_tab' => $nextTab
                ]);

                return redirect()->route('admin.carrier.user_drivers.edit', [
                    'carrier' => $carrier,
                    'userDriverDetail' => $userDriverDetail->id,
                    'active_tab' => $nextTab
                ])->with('success', 'Información guardada correctamente. Continúa con el siguiente paso.');
            }
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

    /**
     * Obtener reglas de validación según la pestaña activa
     */
    private function getValidationRulesForTab($tab)
    {
        $rules = [];

        // Reglas comunes para pestañas que requieren usuario
        $userRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|min:8|confirmed', // Requerido solo si es nuevo
        ];

        switch ($tab) {
            case 'general':
                // Datos básicos del usuario y dirección
                $rules = array_merge($userRules, [
                    'email' => 'required|email|unique:users,email,' . $this->getUserIdFromRequest(),
                    'password' => $this->getUserIdFromRequest() ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed',
                    'middle_name' => 'nullable|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'phone' => 'required|string|max:15',
                    'date_of_birth' => 'required|date',
                    'status' => 'sometimes|integer|in:0,1,2',

                    // Dirección
                    'address_line1' => 'required|string|max:255',
                    'address_line2' => 'nullable|string|max:255',
                    'city' => 'required|string|max:255',
                    'state' => 'required|string|max:255',
                    'zip_code' => 'required|string|max:255',
                    'from_date' => 'required|date',
                    'to_date' => 'nullable|date',
                    'lived_three_years' => 'nullable|boolean',

                    // Aplicación básica
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

                    // Direcciones previas son opcionales en creación
                    'previous_addresses' => 'array|required_if:lived_three_years,0',
                    'previous_addresses.*.address_line1' => 'required_with:previous_addresses',
                    'previous_addresses.*.address_line2' => 'nullable|string|max:255',
                    'previous_addresses.*.city' => 'required_with:previous_addresses',
                    'previous_addresses.*.state' => 'required_with:previous_addresses',
                    'previous_addresses.*.zip_code' => 'required_with:previous_addresses',
                    'previous_addresses.*.from_date' => 'required_with:previous_addresses|date',
                    'previous_addresses.*.to_date' => 'required_with:previous_addresses|date|after:previous_addresses.*.from_date',
                ]);
                break;

            case 'licenses':
                // Reglas para licencias
                $rules = [
                    'current_license_number' => 'required|string|max:255',
                    'licenses' => 'array',
                    'licenses.*.license_number' => 'required|string|max:255',
                    'licenses.*.state_of_issue' => 'required|string|max:255',
                    'licenses.*.license_class' => 'required|string|max:255',
                    'licenses.*.expiration_date' => 'required|date',
                    'licenses.*.is_cdl' => 'sometimes|boolean',
                    'licenses.*.endorsements' => 'nullable|array',
                    'licenses.*.temp_front_token' => 'nullable|string',
                    'licenses.*.temp_back_token' => 'nullable|string',

                    // Experiencias
                    'experiences' => 'array',
                    'experiences.*.equipment_type' => 'required|string|max:255',
                    'experiences.*.years_experience' => 'required|integer|min:0',
                    'experiences.*.miles_driven' => 'required|integer|min:0',
                    'experiences.*.requires_cdl' => 'sometimes|boolean',
                ];
                break;

            case 'medical':
                // Información médica
                $rules = [
                    'social_security_number' => 'required|string|max:255',
                    'hire_date' => 'nullable|date',
                    'location' => 'nullable|string|max:255',
                    'is_suspended' => 'sometimes|boolean',
                    'suspension_date' => 'nullable|required_if:is_suspended,true|date',
                    'is_terminated' => 'sometimes|boolean',
                    'termination_date' => 'nullable|required_if:is_terminated,true|date',
                    'medical_examiner_name' => 'required|string|max:255',
                    'medical_examiner_registry_number' => 'required|string|max:255',
                    'medical_card_expiration_date' => 'required|date',
                    'temp_medical_card_token' => 'nullable|string',
                ];
                break;

            case 'training':
                // Escuelas y formación
                $rules = [
                    'has_attended_training_school' => 'sometimes|boolean',
                    'has_work_history' => 'sometimes|boolean',
                ];

                // Historiales de trabajo opcionales
                if (request()->boolean('has_work_history')) {
                    $rules = array_merge($rules, [
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

                // Escuelas opcionales
                if (request()->boolean('has_attended_training_school')) {
                    $rules = array_merge($rules, [
                        'training_schools' => 'required|array',
                        'training_schools.*.school_name' => 'required|string|max:255',
                        'training_schools.*.city' => 'required|string|max:255',
                        'training_schools.*.state' => 'required|string|max:255',
                        'training_schools.*.phone_number' => 'nullable|string|max:20',
                        'training_schools.*.date_start' => 'required|date',
                        'training_schools.*.date_end' => 'required|date|after_or_equal:training_schools.*.date_start',
                        'training_schools.*.graduated' => 'sometimes|boolean',
                    ]);
                }
                break;

            case 'traffic':
                // Infracciones de tráfico
                $rules = [
                    'has_traffic_convictions' => 'sometimes|boolean',
                ];

                if (request()->boolean('has_traffic_convictions')) {
                    $rules = array_merge($rules, [
                        'traffic_convictions' => 'required|array',
                        'traffic_convictions.*.conviction_date' => 'required|date',
                        'traffic_convictions.*.location' => 'required|string|max:255',
                        'traffic_convictions.*.charge' => 'required|string|max:255',
                        'traffic_convictions.*.penalty' => 'required|string|max:255',
                    ]);
                }
                break;

            case 'accident':
                // Accidentes
                $rules = [
                    'has_accidents' => 'sometimes|boolean',
                ];

                if (request()->boolean('has_accidents')) {
                    $rules = array_merge($rules, [
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
                break;
        }

        return $rules;
    }


    /**
     * Obtener el ID del usuario de la request, si existe
     */
    private function getUserIdFromRequest()
    {
        return request()->input('user_id', null);
    }

    /**
     * Procesar datos de la pestaña General
     */
    private function processGeneralTab(Request $request, UserDriverDetail $userDriverDetail, DriverApplication $application)
    {
        // Crear dirección principal
        $livedThreeYears = $request->boolean('lived_three_years', false);

        $address = $application->addresses()->updateOrCreate(
            ['primary' => true],
            [
                'address_line1' => $request->input('address_line1'),
                'address_line2' => $request->input('address_line2'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'zip_code' => $request->input('zip_code'),
                'lived_three_years' => $livedThreeYears,
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
            ]
        );

        Log::info('Dirección principal creada/actualizada', ['address_id' => $address->id]);

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

        // Crear o actualizar detalles de la aplicación
        $applicationDetails = $application->details()->updateOrCreate(
            [], // Primera o única entrada
            [
                'applying_position' => $request->input('applying_position'),
                'applying_position_other' => $request->input('applying_position') === 'other' ?
                    $request->input('applying_position_other') : null,
                'applying_location' => $request->input('applying_location'),
                'eligible_to_work' => $request->boolean('eligible_to_work'),
                'can_speak_english' => $request->boolean('can_speak_english', false),
                'has_twic_card' => $request->boolean('has_twic_card', false),
                'twic_expiration_date' => $request->input('twic_expiration_date'),
                'expected_pay' => $request->input('expected_pay'),
                'how_did_hear' => $request->input('how_did_hear'),
                'how_did_hear_other' => $request->input('how_did_hear') === 'other' ?
                    $request->input('how_did_hear_other') : null,
                'referral_employee_name' => $request->input('how_did_hear') === 'employee_referral' ?
                    $request->input('referral_employee_name') : null,
            ]
        );

        Log::info('Detalles de aplicación creados/actualizados', [
            'details_id' => $applicationDetails->id
        ]);
    }

    /**
     * Procesar datos de la pestaña Licenses
     */
    private function processLicensesTab(Request $request, UserDriverDetail $userDriverDetail)
    {
        // Procesar licencias
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
                    $tempUploadService = app(TempUploadService::class);
                    $tempPath = $tempUploadService->moveToPermanent($licenseDataRaw['temp_front_token']);

                    if ($tempPath && file_exists($tempPath)) {
                        $license->clearMediaCollection('license_front');
                        $license->addMedia($tempPath)
                            ->toMediaCollection('license_front');

                        Log::info('Imagen frontal agregada/actualizada en licencia', [
                            'license_id' => $license->id,
                            'path' => $tempPath
                        ]);
                    }
                }

                if (!empty($licenseDataRaw['temp_back_token'])) {
                    $tempUploadService = app(TempUploadService::class);
                    $tempPath = $tempUploadService->moveToPermanent($licenseDataRaw['temp_back_token']);

                    if ($tempPath && file_exists($tempPath)) {
                        $license->clearMediaCollection('license_back');
                        $license->addMedia($tempPath)
                            ->toMediaCollection('license_back');

                        Log::info('Imagen trasera agregada/actualizada en licencia', [
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

        // Procesar experiencias
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
    }

    /**
     * Procesar datos de la pestaña Medical
     */
    private function processMedicalTab(Request $request, UserDriverDetail $userDriverDetail)
    {
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
            $tempUploadService = app(TempUploadService::class);
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

    /**
     * Procesar datos de la pestaña Training
     */
    private function processTrainingTab(Request $request, UserDriverDetail $userDriverDetail, DriverApplication $application)
    {
        // Actualizar campo has_work_history en los detalles de la aplicación
        if ($application->details) {
            $application->details->update([
                'has_work_history' => $request->boolean('has_work_history', false),
                'has_attended_training_school' => $request->boolean('has_attended_training_school', false),
            ]);
        }

        // PROCESAR HISTORIAL LABORAL
        if ($request->boolean('has_work_history')) {
            // Obtener IDs existentes para detectar eliminaciones
            $existingWorkHistoryIds = $userDriverDetail->workHistories()->pluck('id')->toArray();
            $updatedWorkHistoryIds = [];

            if ($request->has('work_histories') && is_array($request->work_histories)) {
                foreach ($request->work_histories as $workHistoryData) {
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

                    Log::info('Historial laboral procesado', [
                        'id' => $workHistory->id,
                        'company' => $workHistoryData['previous_company']
                    ]);
                }
            }

            // Eliminar historiales que ya no existen
            $workHistoriesToDelete = array_diff($existingWorkHistoryIds, $updatedWorkHistoryIds);
            if (!empty($workHistoriesToDelete)) {
                $userDriverDetail->workHistories()->whereIn('id', $workHistoriesToDelete)->delete();
            }
        } else {
            // Si no tiene historial laboral, eliminar todos los registros
            $userDriverDetail->workHistories()->delete();
        }

        // PROCESAR FORMACIÓN DE CONDUCTORES
        if ($request->boolean('has_attended_training_school')) {
            // Obtener IDs existentes para detectar eliminaciones
            $existingTrainingIds = $userDriverDetail->trainingSchools()->pluck('id')->toArray();
            $updatedTrainingIds = [];

            if ($request->has('training_schools') && is_array($request->training_schools)) {
                foreach ($request->training_schools as $schoolData) {
                    // Verificar datos mínimos necesarios
                    if (
                        empty($schoolData['school_name']) ||
                        empty($schoolData['date_start']) ||
                        empty($schoolData['date_end']) ||
                        empty($schoolData['city']) ||
                        empty($schoolData['state'])
                    ) {
                        continue;
                    }

                    // Si tiene ID, es una escuela existente
                    $schoolId = $schoolData['id'] ?? null;
                    $trainingSchool = null;

                    if ($schoolId) {
                        $trainingSchool = $userDriverDetail->trainingSchools()->find($schoolId);
                    }

                    if (!$trainingSchool) {
                        // Crear nuevo registro de escuela
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
                    } else {
                        // Actualizar escuela existente
                        $trainingSchool->update([
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
                    }

                    $updatedTrainingIds[] = $trainingSchool->id;

                    Log::info('Escuela de formación procesada', [
                        'id' => $trainingSchool->id,
                        'name' => $schoolData['school_name']
                    ]);

                    // Procesar certificados si existen
                    if (isset($schoolData['certificates']) && is_array($schoolData['certificates'])) {
                        foreach ($schoolData['certificates'] as $token) {
                            try {
                                // Obtener servicio de upload temporal
                                $tempUploadService = app(TempUploadService::class);

                                // Obtener la ruta física del archivo temporal
                                $tempPath = $tempUploadService->moveToPermanent($token);

                                if ($tempPath && file_exists($tempPath)) {
                                    // Añadir el archivo a la colección de certificados
                                    $trainingSchool->addMedia($tempPath)
                                        ->toMediaCollection('school_certificates');

                                    Log::info('Certificado de escuela añadido', [
                                        'school_id' => $trainingSchool->id,
                                        'path' => $tempPath
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Error procesando certificado de escuela', [
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString(),
                                    'school_id' => $trainingSchool->id,
                                    'token' => $token
                                ]);
                            }
                        }
                    }
                }
            }

            // Eliminar escuelas que ya no existen
            $schoolsToDelete = array_diff($existingTrainingIds, $updatedTrainingIds);
            if (!empty($schoolsToDelete)) {
                $userDriverDetail->trainingSchools()->whereIn('id', $schoolsToDelete)->delete();
            }
        } else {
            // Si no asistió a ninguna escuela, eliminar todos los registros
            $userDriverDetail->trainingSchools()->delete();
        }
    }

    /**
     * Procesar datos de la pestaña Traffic
     */
    private function processTrafficTab(Request $request, UserDriverDetail $userDriverDetail, DriverApplication $application)
    {
        // Actualizar campo has_traffic_convictions en los detalles de la aplicación
        if ($application->details) {
            $application->details->update([
                'has_traffic_convictions' => $request->boolean('has_traffic_convictions', false),
            ]);
        }

        // PROCESAR INFRACCIONES DE TRÁFICO
        if ($request->boolean('has_traffic_convictions')) {
            // Obtener IDs existentes para detectar eliminaciones
            $existingConvictionIds = $userDriverDetail->trafficConvictions()->pluck('id')->toArray();
            $updatedConvictionIds = [];

            if ($request->has('traffic_convictions') && is_array($request->traffic_convictions)) {
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

                    // Si tiene ID, es una infracción existente
                    $convictionId = $convictionData['id'] ?? null;
                    $trafficConviction = null;

                    if ($convictionId) {
                        $trafficConviction = $userDriverDetail->trafficConvictions()->find($convictionId);
                    }

                    if (!$trafficConviction) {
                        // Crear nueva infracción
                        $trafficConviction = $userDriverDetail->trafficConvictions()->create([
                            'conviction_date' => $convictionData['conviction_date'],
                            'location' => $convictionData['location'],
                            'charge' => $convictionData['charge'],
                            'penalty' => $convictionData['penalty'],
                        ]);
                    } else {
                        // Actualizar infracción existente
                        $trafficConviction->update([
                            'conviction_date' => $convictionData['conviction_date'],
                            'location' => $convictionData['location'],
                            'charge' => $convictionData['charge'],
                            'penalty' => $convictionData['penalty'],
                        ]);
                    }

                    $updatedConvictionIds[] = $trafficConviction->id;

                    Log::info('Infracción de tráfico procesada', [
                        'id' => $trafficConviction->id,
                        'charge' => $convictionData['charge']
                    ]);
                }
            }

            // Eliminar infracciones que ya no existen
            $convictionsToDelete = array_diff($existingConvictionIds, $updatedConvictionIds);
            if (!empty($convictionsToDelete)) {
                $userDriverDetail->trafficConvictions()->whereIn('id', $convictionsToDelete)->delete();
            }
        } else {
            // Si no tiene infracciones, eliminar todos los registros
            $userDriverDetail->trafficConvictions()->delete();
        }
    }

    /**
     * Procesar datos de la pestaña Accident
     */
    private function processAccidentTab(Request $request, UserDriverDetail $userDriverDetail, DriverApplication $application)
    {
        // Actualizar campo has_accidents en los detalles de la aplicación
        if ($application->details) {
            $application->details->update([
                'has_accidents' => $request->boolean('has_accidents', false),
            ]);
        }

        // PROCESAR REGISTRO DE ACCIDENTES
        if ($request->boolean('has_accidents')) {
            // Obtener IDs existentes para detectar eliminaciones
            $existingAccidentIds = $userDriverDetail->accidents()->pluck('id')->toArray();
            $updatedAccidentIds = [];

            if ($request->has('accidents') && is_array($request->accidents)) {
                foreach ($request->accidents as $accidentData) {
                    // Verificar datos mínimos necesarios
                    if (
                        empty($accidentData['accident_date']) ||
                        empty($accidentData['nature_of_accident'])
                    ) {
                        continue;
                    }

                    // Si tiene ID, es un accidente existente
                    $accidentId = $accidentData['id'] ?? null;
                    $accident = null;

                    if ($accidentId) {
                        $accident = $userDriverDetail->accidents()->find($accidentId);
                    }

                    if (!$accident) {
                        // Crear nuevo registro de accidente
                        $accident = $userDriverDetail->accidents()->create([
                            'accident_date' => $accidentData['accident_date'],
                            'nature_of_accident' => $accidentData['nature_of_accident'],
                            'had_injuries' => isset($accidentData['had_injuries']),
                            'number_of_injuries' => isset($accidentData['had_injuries']) ? ($accidentData['number_of_injuries'] ?? 0) : 0,
                            'had_fatalities' => isset($accidentData['had_fatalities']),
                            'number_of_fatalities' => isset($accidentData['had_fatalities']) ? ($accidentData['number_of_fatalities'] ?? 0) : 0,
                            'comments' => $accidentData['comments'] ?? null,
                        ]);
                    } else {
                        // Actualizar registro de accidente existente
                        $accident->update([
                            'accident_date' => $accidentData['accident_date'],
                            'nature_of_accident' => $accidentData['nature_of_accident'],
                            'had_injuries' => isset($accidentData['had_injuries']),
                            'number_of_injuries' => isset($accidentData['had_injuries']) ? ($accidentData['number_of_injuries'] ?? 0) : 0,
                            'had_fatalities' => isset($accidentData['had_fatalities']),
                            'number_of_fatalities' => isset($accidentData['had_fatalities']) ? ($accidentData['number_of_fatalities'] ?? 0) : 0,
                            'comments' => $accidentData['comments'] ?? null,
                        ]);
                    }

                    $updatedAccidentIds[] = $accident->id;

                    Log::info('Registro de accidente procesado', [
                        'id' => $accident->id,
                        'accident_date' => $accidentData['accident_date']
                    ]);
                }
            }

            // Eliminar accidentes que ya no existen
            $accidentsToDelete = array_diff($existingAccidentIds, $updatedAccidentIds);
            if (!empty($accidentsToDelete)) {
                $userDriverDetail->accidents()->whereIn('id', $accidentsToDelete)->delete();
            }
        } else {
            // Si no tiene accidentes, eliminar todos los registros
            $userDriverDetail->accidents()->delete();
        }
    }

    /**
     * Método auxiliar para convertir número de paso a nombre de pestaña
     */
    private function getTabNameFromStep(int $step): string
    {
        $tabs = [
            DriverStepService::STEP_GENERAL => 'general',
            DriverStepService::STEP_LICENSES => 'licenses',
            DriverStepService::STEP_MEDICAL => 'medical',
            DriverStepService::STEP_TRAINING => 'training',
            DriverStepService::STEP_TRAFFIC => 'traffic',
            DriverStepService::STEP_ACCIDENT => 'accident',
        ];

        return $tabs[$step] ?? 'general';
    }

    public function autosave(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail = null)
    {
        try {
            // Validar los datos básicos del formulario
            $validated = $request->validate([
                'active_tab' => 'required|string',
                // Otras validaciones básicas
            ]);

            // Si no existe userDriverDetail, crear un registro temporal
            if (!$userDriverDetail) {
                $user = User::create([
                    'name' => $request->input('name') ?? 'Temporary User',
                    'email' => $request->input('email') ?? 'temp_' . time() . '@example.com',
                    'password' => Hash::make(Str::random(10)),
                    'status' => 1,
                ]);
                $user->assignRole('driver');

                $userDriverDetail = UserDriverDetail::create([
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id,
                    'current_step' => $this->driverStepService::STEP_GENERAL,
                ]);
            }

            // Procesar datos según la pestaña activa
            $activeTab = $request->input('active_tab');
            switch ($activeTab) {
                case 'general':
                    // Guardar datos generales
                    break;
                case 'licenses':
                    // Guardar datos de licencias
                    break;
                    // Otros casos para las demás pestañas
            }

            return response()->json([
                'success' => true,
                'message' => 'Datos guardados temporalmente',
                'user_driver_id' => $userDriverDetail->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
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
        Log::info('Iniciando edición de driver', [
            'carrier_id' => $carrier->id,
            'user_driver_id' => $userDriverDetail->id,
        ]);
    
        try {
            // Recupera los datos para los selects
            Log::info('Cargando constantes');
            $usStates = Constants::usStates();
            $driverPositions = Constants::driverPositions();
            $referralSources = Constants::referralSources();
            
            // Cargar el usuario driver
            $driver = $userDriverDetail->user;
            Log::info('Driver user cargado', ['user_id' => $driver->id]);
    
            // Cargar otras relaciones necesarias
            Log::info('Iniciando carga de relaciones');
            $userDriverDetail->load([
                'application.details',
                'application.addresses',
                'licenses.endorsements',
                'experiences',
                'medicalQualification',
                'workHistories',
                'trainingSchools',
                'trafficConvictions',
                'accidents'
            ]);
            Log::info('Relaciones cargadas correctamente');
    
            // Obtener la dirección principal
            Log::info('Obteniendo dirección principal');
            $mainAddress = null;
            if ($userDriverDetail->application) {
                $mainAddress = $userDriverDetail->application->addresses()
                    ->where('primary', true)
                    ->first();
                Log::info('Dirección principal obtenida', ['main_address' => $mainAddress ? true : false]);
            } else {
                Log::warning('No hay aplicación asociada a este driver');
            }
    
            // Obtener las direcciones previas
            Log::info('Obteniendo direcciones previas');
            $previousAddresses = collect(); // Inicializar como colección vacía
            if ($userDriverDetail->application) {
                $previousAddresses = $userDriverDetail->application->addresses()
                    ->where('primary', false)
                    ->orderBy('from_date', 'desc')
                    ->get();
                Log::info('Direcciones previas obtenidas', ['count' => $previousAddresses->count()]);
            }
    
            // Verificar si tiene foto de perfil
            Log::info('Verificando foto de perfil');
            $profilePhotoUrl = $userDriverDetail->getFirstMediaUrl('profile_photo_driver');
            if (empty($profilePhotoUrl)) {
                $profilePhotoUrl = asset('build/default_profile.png');
                Log::info('Usando foto por defecto');
            } else {
                Log::info('Foto de perfil encontrada');
            }
    
            Log::info('Preparando variables para la vista');
            
            // Verificar que todas las variables estén definidas
            Log::info('Variables preparadas', [
                'carrier' => $carrier ? true : false,
                'userDriverDetail' => $userDriverDetail ? true : false,
                'driver' => $driver ? true : false,
                'usStates' => is_array($usStates) ? count($usStates) : false,
                'driverPositions' => is_array($driverPositions) ? count($driverPositions) : false,
                'referralSources' => is_array($referralSources) ? count($referralSources) : false,
                'mainAddress' => $mainAddress ? true : false,
                'previousAddresses' => $previousAddresses ? $previousAddresses->count() : 0,
                'profilePhotoUrl' => !empty($profilePhotoUrl)
            ]);
    
            Log::info('Renderizando vista');
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
        } catch (\Exception $e) {
            Log::error('Error en edit de driver', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return redirect()->route('admin.carrier.user_drivers.index', $carrier)
                ->with('error', 'Error al cargar el driver: ' . $e->getMessage());
        }
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
