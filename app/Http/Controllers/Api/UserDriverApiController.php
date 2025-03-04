<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Carrier;
use App\Helpers\Constants;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\UserDriverDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\Admin\DriverStepService;
use App\Services\Admin\TempUploadService;
use App\Models\Admin\Driver\DriverApplication;
use App\Models\Admin\Driver\LicenseEndorsement;

class UserDriverApiController extends Controller
{
    protected $driverStepService;

    public function __construct(DriverStepService $driverStepService)
    {
        $this->driverStepService = $driverStepService;
    }

    /**
     * Mostrar listado de conductores
     */
    public function index(Request $request, Carrier $carrier): JsonResponse
    {
        try {
            $drivers = UserDriverDetail::where('carrier_id', $carrier->id)
                ->with(['user', 'application'])
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $drivers,
                'meta' => [
                    'max_drivers' => $carrier->membership->max_drivers ?? 1,
                    'current_drivers' => $drivers->total()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener listado de conductores', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conductores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos de un conductor específico
     */
    public function show(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail): JsonResponse
    {
        try {
            // Verificar pertenencia al carrier
            if ($userDriverDetail->carrier_id !== $carrier->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este conductor no pertenece al carrier especificado'
                ], 403);
            }

            // Cargar relaciones necesarias según el tab activo o cargar todas
            $tab = $request->query('tab', 'all');

            $userDriverDetail->load(['user']);

            if ($tab === 'all' || $tab === 'general') {
                $userDriverDetail->load([
                    'application.details',
                    'application.addresses',
                    'workHistories'
                ]);
            }

            if ($tab === 'all' || $tab === 'licenses') {
                $userDriverDetail->load([
                    'licenses.endorsements',
                    'experiences'
                ]);
            }

            if ($tab === 'all' || $tab === 'medical') {
                $userDriverDetail->load(['medicalQualification']);
            }

            if ($tab === 'all' || $tab === 'training') {
                $userDriverDetail->load(['trainingSchools']);
            }

            if ($tab === 'all' || $tab === 'traffic') {
                $userDriverDetail->load(['trafficConvictions']);
            }

            if ($tab === 'all' || $tab === 'accident') {
                $userDriverDetail->load(['accidents']);
            }

            // Obtener dirección principal y direcciones previas
            $mainAddress = null;
            $previousAddresses = collect();

            if ($userDriverDetail->application) {
                $mainAddress = $userDriverDetail->application->addresses()
                    ->where('primary', true)
                    ->first();

                $previousAddresses = $userDriverDetail->application->addresses()
                    ->where('primary', false)
                    ->orderBy('from_date', 'desc')
                    ->get();
            }

            // Calcular estado de pasos
            $stepsStatus = $this->driverStepService->getStepsStatus($userDriverDetail);
            $completionPercentage = $this->driverStepService->calculateCompletionPercentage($userDriverDetail);

            return response()->json([
                'success' => true,
                'data' => $userDriverDetail,
                'meta' => [
                    'main_address' => $mainAddress,
                    'previous_addresses' => $previousAddresses,
                    'profile_photo_url' => $userDriverDetail->getFirstMediaUrl('profile_photo_driver') ?: asset('build/default_profile.png'),
                    'steps_status' => $stepsStatus,
                    'completion_percentage' => $completionPercentage,
                    'current_step' => $userDriverDetail->current_step
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener conductor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del conductor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo conductor (paso General)
     */
    public function store(Request $request, Carrier $carrier): JsonResponse
    {

        
        try {
            // Add debug logging
            Log::info('API store method called', [
                'carrier_id' => $carrier->id,
                'request_data' => $request->except(['password', 'password_confirmation']),
                'active_tab' => $request->input('active_tab', 'general'),
                'submission_type' => $request->input('submission_type', 'complete')
            ]);
            
            // Verificar límite de drivers
            $maxDrivers = $carrier->membership->max_drivers ?? 1;
            $currentDrivers = UserDriverDetail::where('carrier_id', $carrier->id)->count();
            if ($currentDrivers >= $maxDrivers) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes agregar más conductores a este carrier. Actualiza tu plan.',
                    'meta' => [
                        'exceeded_limit' => true,
                        'max_drivers' => $maxDrivers,
                        'current_drivers' => $currentDrivers
                    ]
                ], 400);
            }
    
            // Validar datos del formulario según la pestaña activa
            $activeTab = $request->input('active_tab', 'general');
            $submissionType = $request->input('submission_type', 'complete');
            
            // Validación según pestaña activa (solo validamos los campos de la pestaña actual)
            $validationRules = [];
            
            if ($activeTab === 'general') {
                $validationRules = [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:8',
                    'middle_name' => 'nullable|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'phone' => 'required|string|max:15',
                    'date_of_birth' => 'required|date',
                    'terms_accepted' => 'sometimes|boolean',
                    // Dirección básica
                    'address_line1' => 'required|string|max:255',
                    'city' => 'required|string|max:255',
                    'state' => 'required|string|max:255',
                    'zip_code' => 'required|string|max:255',
                    'from_date' => 'required|date',
                    'to_date' => 'nullable|date',
                    'lived_three_years' => 'boolean',
                    // Direcciones previas solo si no ha vivido 3 años en la actual
                    'previous_addresses' => 'array|required_if:lived_three_years,0',
                    'previous_addresses.*.address_line1' => 'required_with:previous_addresses',
                    'previous_addresses.*.city' => 'required_with:previous_addresses',
                    'previous_addresses.*.state' => 'required_with:previous_addresses',
                    'previous_addresses.*.zip_code' => 'required_with:previous_addresses',
                    'previous_addresses.*.from_date' => 'required_with:previous_addresses|date',
                    'previous_addresses.*.to_date' => 'required_with:previous_addresses|date',
                    // Aplicación - solo validamos lo básico si es parcial
                    'applying_position' => $submissionType === 'complete' ? 'required|string' : 'nullable|string',
                    'applying_location' => $submissionType === 'complete' ? 'required|string' : 'nullable|string'
                ];
            }
            
            $validator = Validator::make($request->all(), $validationRules);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
    
            // Transacción de base de datos
            DB::beginTransaction();
            try {
                // Crear usuario
                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                    'status' => 1 // Activo por defecto
                ]);
                
                // Asignar rol de conductor
                $user->assignRole('driver');
                
                // Crear detalle de conductor
                $userDriverDetail = UserDriverDetail::create([
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id,
                    'middle_name' => $request->input('middle_name'),
                    'last_name' => $request->input('last_name'),
                    'phone' => $request->input('phone'),
                    'date_of_birth' => $request->input('date_of_birth'),
                    'status' => 1,
                    'terms_accepted' => $request->boolean('terms_accepted', false),
                    'confirmation_token' => Str::random(60),
                    'current_step' => $this->driverStepService::STEP_GENERAL
                ]);
                
                // Crear aplicación
                $application = DriverApplication::create([
                    'user_id' => $user->id,
                    'status' => 'draft'
                ]);
                
                // Crear dirección principal
                $application->addresses()->create([
                    'primary' => true,
                    'address_line1' => $request->input('address_line1'),
                    'address_line2' => $request->input('address_line2'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'zip_code' => $request->input('zip_code'),
                    'lived_three_years' => $request->boolean('lived_three_years', false),
                    'from_date' => $request->input('from_date'),
                    'to_date' => $request->input('to_date')
                ]);
                
                // Procesar direcciones adicionales si es necesario
                if (!$request->boolean('lived_three_years', false) && $request->has('previous_addresses')) {
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
                
                // Solo creamos los detalles de aplicación si es necesario (incluso parcialmente)
                if ($submissionType === 'complete' || $request->filled('applying_position')) {
                    $application->details()->create([
                        'applying_position' => $request->input('applying_position'),
                        'applying_position_other' => $request->input('applying_position') === 'other' ? 
                            $request->input('applying_position_other') : null,
                        'applying_location' => $request->input('applying_location'),
                        'eligible_to_work' => $request->boolean('eligible_to_work', true),
                        'can_speak_english' => $request->boolean('can_speak_english', true),
                        'has_twic_card' => $request->boolean('has_twic_card', false),
                        'twic_expiration_date' => $request->input('twic_expiration_date'),
                        'how_did_hear' => $request->input('how_did_hear', 'internet'),
                        'expected_pay' => $request->input('expected_pay')
                    ]);
                }
                
                // Procesar foto de perfil
                if ($request->hasFile('photo')) {
                    $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp';
                    $userDriverDetail->addMediaFromRequest('photo')
                        ->usingFileName($fileName)
                        ->toMediaCollection('profile_photo_driver');
                }
                
                // Si se proporcionó un token de foto, procesarlo
                if ($request->filled('temp_photo_token')) {
                    $tempUploadService = app(TempUploadService::class);
                    $tempPath = $tempUploadService->moveToPermanent($request->input('temp_photo_token'));
                    if ($tempPath && file_exists($tempPath)) {
                        $fileName = strtolower(str_replace(' ', '_', $user->name)) . '.webp';
                        $userDriverDetail->addMedia($tempPath)
                            ->usingFileName($fileName)
                            ->toMediaCollection('profile_photo_driver');
                    }
                }
                
                // Confirmar transacción
                DB::commit();
                
                // Calcular estado de pasos
                $stepsStatus = $this->driverStepService->getStepsStatus($userDriverDetail);
                $completionPercentage = $this->driverStepService->calculateCompletionPercentage($userDriverDetail);
                
                // Determinar la pestaña de redirección según el tipo de envío
                $nextTab = $submissionType === 'complete' ? 'licenses' : $activeTab;
                
                // URL de redirección
                $redirectUrl = route('admin.carrier.user_drivers.edit', [
                    'carrier' => $carrier,
                    'userDriverDetail' => $userDriverDetail->id,
                    'active_tab' => $nextTab
                ]);
                
                Log::info('Driver created successfully', [
                    'driver_id' => $userDriverDetail->id,
                    'redirect_url' => $redirectUrl,
                    'submission_type' => $submissionType
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Conductor creado correctamente',
                    'data' => [
                        'id' => $userDriverDetail->id,
                        'redirect_url' => route('admin.carrier.user_drivers.edit', [
                            'carrier' => $carrier,
                            'userDriverDetail' => $userDriverDetail->id,
                            // Asegúrate de que estos nombres de parámetros coincidan con tus definiciones de rutas
                        ])
                    ]
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error en creación de conductor', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear conductor: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error en validación de conductor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar información general del conductor
     */
    public function updateGeneral(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail): JsonResponse
    {
        try {
            // Verificar pertenencia al carrier
            if ($userDriverDetail->carrier_id !== $carrier->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este conductor no pertenece al carrier especificado'
                ], 403);
            }

            // Validar los datos
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $userDriverDetail->user_id,
                'password' => 'nullable|min:8|confirmed',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|string|max:15',
                'date_of_birth' => 'required|date',
                'status' => 'sometimes|integer|in:0,1,2',
                'terms_accepted' => 'sometimes|boolean',

                // Dirección
                'address_line1' => 'required|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'zip_code' => 'required|string|max:255',
                'from_date' => 'required|date',
                'to_date' => 'nullable|date',
                'lived_three_years' => 'boolean',

                // Direcciones previas
                'previous_addresses' => 'array|required_if:lived_three_years,0',
                'previous_addresses.*.address_line1' => 'required_with:previous_addresses',
                'previous_addresses.*.city' => 'required_with:previous_addresses',
                'previous_addresses.*.state' => 'required_with:previous_addresses',
                'previous_addresses.*.zip_code' => 'required_with:previous_addresses',
                'previous_addresses.*.from_date' => 'required_with:previous_addresses|date',
                'previous_addresses.*.to_date' => 'required_with:previous_addresses|date',

                // Aplicación
                'applying_position' => 'required|string',
                'applying_position_other' => 'required_if:applying_position,other',
                'applying_location' => 'required|string|max:255',
                'eligible_to_work' => 'required|boolean',
                'can_speak_english' => 'sometimes|boolean',
                'has_twic_card' => 'sometimes|boolean',
                'twic_expiration_date' => 'nullable|required_if:has_twic_card,1|date',
                'how_did_hear' => 'required|string',
                'how_did_hear_other' => 'required_if:how_did_hear,other',
                'referral_employee_name' => 'required_if:how_did_hear,employee_referral',
                'expected_pay' => 'nullable|string|max:255',

                // Historial laboral
                'has_work_history' => 'sometimes|boolean',
                'work_histories' => 'nullable|array|required_if:has_work_history,1',
                'work_histories.*.previous_company' => 'required_with:work_histories',
                'work_histories.*.start_date' => 'required_with:work_histories|date',
                'work_histories.*.end_date' => 'required_with:work_histories|date',
                'work_histories.*.location' => 'required_with:work_histories|string',
                'work_histories.*.position' => 'required_with:work_histories|string',
                'work_histories.*.reason_for_leaving' => 'required_with:work_histories|string',

                // Foto
                'temp_photo_token' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Transacción de base de datos
            DB::beginTransaction();

            try {
                // Actualizar usuario
                $userDriverDetail->user->update([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'status' => $request->input('status', $userDriverDetail->user->status)
                ]);

                // Actualizar password si se proporcionó
                if ($request->filled('password')) {
                    $userDriverDetail->user->update([
                        'password' => Hash::make($request->input('password'))
                    ]);
                }

                // Actualizar detalles del conductor
                $userDriverDetail->update([
                    'middle_name' => $request->input('middle_name'),
                    'last_name' => $request->input('last_name'),
                    'phone' => $request->input('phone'),
                    'date_of_birth' => $request->input('date_of_birth'),
                    'status' => $request->input('status', $userDriverDetail->status),
                    'terms_accepted' => $request->boolean('terms_accepted', $userDriverDetail->terms_accepted),
                    'current_step' => $this->driverStepService::STEP_LICENSES // Avanzar al siguiente paso
                ]);

                // Procesar foto de perfil
                if ($request->hasFile('photo')) {
                    $userDriverDetail->clearMediaCollection('profile_photo_driver');
                    $fileName = strtolower(str_replace(' ', '_', $userDriverDetail->user->name)) . '.webp';
                    $userDriverDetail->addMediaFromRequest('photo')
                        ->usingFileName($fileName)
                        ->toMediaCollection('profile_photo_driver');
                } elseif ($request->filled('temp_photo_token')) {
                    $tempUploadService = app(TempUploadService::class);
                    $tempPath = $tempUploadService->moveToPermanent($request->input('temp_photo_token'));

                    if ($tempPath && file_exists($tempPath)) {
                        $userDriverDetail->clearMediaCollection('profile_photo_driver');
                        $fileName = strtolower(str_replace(' ', '_', $userDriverDetail->user->name)) . '.webp';
                        $userDriverDetail->addMedia($tempPath)
                            ->usingFileName($fileName)
                            ->toMediaCollection('profile_photo_driver');
                    }
                }

                // Obtener o crear aplicación
                $application = $userDriverDetail->application;
                if (!$application) {
                    $application = DriverApplication::create([
                        'user_id' => $userDriverDetail->user_id,
                        'status' => 'draft'
                    ]);
                }

                // Actualizar dirección principal
                $application->addresses()->updateOrCreate(
                    ['primary' => true],
                    [
                        'address_line1' => $request->input('address_line1'),
                        'address_line2' => $request->input('address_line2'),
                        'city' => $request->input('city'),
                        'state' => $request->input('state'),
                        'zip_code' => $request->input('zip_code'),
                        'lived_three_years' => $request->boolean('lived_three_years'),
                        'from_date' => $request->input('from_date'),
                        'to_date' => $request->input('to_date')
                    ]
                );

                // Gestionar direcciones previas
                if (!$request->boolean('lived_three_years')) {
                    // Eliminar direcciones previas existentes
                    $application->addresses()->where('primary', false)->delete();

                    // Crear nuevas direcciones
                    if ($request->has('previous_addresses')) {
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

                // Actualizar detalles de aplicación
                $application->details()->updateOrCreate(
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
                        'has_work_history' => $request->boolean('has_work_history', false)
                    ]
                );

                // Procesar historial laboral
                if ($request->boolean('has_work_history')) {
                    // Obtener IDs existentes para detectar eliminaciones
                    $existingWorkHistoryIds = $userDriverDetail->workHistories()->pluck('id')->toArray();
                    $updatedWorkHistoryIds = [];

                    if ($request->has('work_histories')) {
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

                // Confirmar transacción
                DB::commit();

                // Calcular estado de pasos
                $stepsStatus = $this->driverStepService->getStepsStatus($userDriverDetail);
                $completionPercentage = $this->driverStepService->calculateCompletionPercentage($userDriverDetail);

                return response()->json([
                    'success' => true,
                    'message' => 'Información general actualizada correctamente',
                    'data' => [
                        'id' => $userDriverDetail->id,
                        'steps_status' => $stepsStatus,
                        'completion_percentage' => $completionPercentage,
                        'current_step' => $userDriverDetail->current_step,
                        'redirect_url' => route('admin.carrier.user_drivers.edit', [
                            'carrier' => $carrier,
                            'userDriverDetail' => $userDriverDetail->id,
                            'active_tab' => 'licenses'
                        ])
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error en actualización general de conductor', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar información general: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error en validación de actualización general', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar licencias y experiencia del conductor
     */
    public function updateLicenses(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail): JsonResponse
    {
        try {
            // Verificar pertenencia al carrier
            if ($userDriverDetail->carrier_id !== $carrier->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este conductor no pertenece al carrier especificado'
                ], 403);
            }

            // Validar los datos
            $validator = Validator::make($request->all(), [
                'current_license_number' => 'required|string|max:255',

                // Licencias
                'licenses' => 'required|array|min:1',
                'licenses.*.license_number' => 'required|string|max:255',
                'licenses.*.state_of_issue' => 'required|string|max:255',
                'licenses.*.license_class' => 'required|string|max:255',
                'licenses.*.expiration_date' => 'required|date',
                'licenses.*.is_cdl' => 'nullable|boolean',
                'licenses.*.endorsements' => 'nullable|array',
                'licenses.*.temp_front_token' => 'nullable|string',
                'licenses.*.temp_back_token' => 'nullable|string',

                // Experiencias
                'experiences' => 'required|array|min:1',
                'experiences.*.equipment_type' => 'required|string|max:255',
                'experiences.*.years_experience' => 'required|integer|min:0',
                'experiences.*.miles_driven' => 'required|integer|min:0',
                'experiences.*.requires_cdl' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Transacción de base de datos
            DB::beginTransaction();

            try {
                // Procesar licencias
                $existingLicenseIds = $userDriverDetail->licenses()->pluck('id')->toArray();
                $updatedLicenseIds = [];

                foreach ($request->input('licenses') as $index => $licenseData) {
                    // Si tiene ID, es una licencia existente
                    $licenseId = $licenseData['id'] ?? null;
                    $license = null;

                    if ($licenseId) {
                        $license = $userDriverDetail->licenses()->find($licenseId);
                    }

                    if (!$license) {
                        // Crear nueva licencia
                        $license = $userDriverDetail->licenses()->create([
                            'current_license_number' => $request->input('current_license_number'),
                            'license_number' => $licenseData['license_number'],
                            'state_of_issue' => $licenseData['state_of_issue'],
                            'license_class' => $licenseData['license_class'],
                            'expiration_date' => $licenseData['expiration_date'],
                            'is_cdl' => isset($licenseData['is_cdl']) ? true : false,
                            'is_primary' => $index === 0, // La primera es la principal
                            'status' => 'active',
                        ]);
                    } else {
                        // Actualizar licencia existente
                        $license->update([
                            'license_number' => $licenseData['license_number'],
                            'state_of_issue' => $licenseData['state_of_issue'],
                            'license_class' => $licenseData['license_class'],
                            'expiration_date' => $licenseData['expiration_date'],
                            'is_cdl' => isset($licenseData['is_cdl']) ? true : false,
                            'is_primary' => $index === 0,
                        ]);
                    }

                    $updatedLicenseIds[] = $license->id;

                    // Gestionar endosos
                    if (isset($licenseData['is_cdl']) && isset($licenseData['endorsements'])) {
                        // Eliminar endosos existentes
                        $license->endorsements()->detach();

                        // Crear nuevos endosos
                        foreach ($licenseData['endorsements'] as $endorsementCode) {
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
                                'expiration_date' => $licenseData['expiration_date']
                            ]);
                        }
                    }

                    // Procesar imágenes de la licencia usando tokens temporales
                    if (!empty($licenseData['temp_front_token'])) {
                        $tempUploadService = app(TempUploadService::class);
                        $tempPath = $tempUploadService->moveToPermanent($licenseData['temp_front_token']);

                        if ($tempPath && file_exists($tempPath)) {
                            $license->clearMediaCollection('license_front');
                            $license->addMedia($tempPath)->toMediaCollection('license_front');
                        }
                    }

                    if (!empty($licenseData['temp_back_token'])) {
                        $tempUploadService = app(TempUploadService::class);
                        $tempPath = $tempUploadService->moveToPermanent($licenseData['temp_back_token']);

                        if ($tempPath && file_exists($tempPath)) {
                            $license->clearMediaCollection('license_back');
                            $license->addMedia($tempPath)->toMediaCollection('license_back');
                        }
                    }
                }

                // Eliminar licencias que ya no existen
                $licensesToDelete = array_diff($existingLicenseIds, $updatedLicenseIds);

                if (!empty($licensesToDelete)) {
                    $userDriverDetail->licenses()->whereIn('id', $licensesToDelete)->delete();
                }

                // Procesar experiencias
                $existingExpIds = $userDriverDetail->experiences()->pluck('id')->toArray();
                $updatedExpIds = [];

                foreach ($request->input('experiences') as $expData) {
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

                // Eliminar experiencias que ya no existen
                $expsToDelete = array_diff($existingExpIds, $updatedExpIds);

                if (!empty($expsToDelete)) {
                    $userDriverDetail->experiences()->whereIn('id', $expsToDelete)->delete();
                }

                // Actualizar el paso actual
                $userDriverDetail->update([
                    'current_step' => $this->driverStepService::STEP_MEDICAL
                ]);

                // Confirmar transacción
                DB::commit();

                // Calcular estado de pasos
                $stepsStatus = $this->driverStepService->getStepsStatus($userDriverDetail);
                $completionPercentage = $this->driverStepService->calculateCompletionPercentage($userDriverDetail);

                return response()->json([
                    'success' => true,
                    'message' => 'Licencias y experiencia actualizadas correctamente',
                    'data' => [
                        'id' => $userDriverDetail->id,
                        'steps_status' => $stepsStatus,
                        'completion_percentage' => $completionPercentage,
                        'current_step' => $userDriverDetail->current_step,
                        'redirect_url' => route('admin.carrier.user_drivers.edit', [
                            'carrier' => $carrier,
                            'userDriverDetail' => $userDriverDetail->id,
                            'active_tab' => 'medical'
                        ])
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error en actualización de licencias', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar licencias y experiencia: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error en validación de licencias', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar información médica del conductor
     */
    public function updateMedical(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail): JsonResponse
    {
        try {
            // Verificar pertenencia al carrier
            if ($userDriverDetail->carrier_id !== $carrier->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este conductor no pertenece al carrier especificado'
                ], 403);
            }

            // Validar los datos
            $validator = Validator::make($request->all(), [
                'social_security_number' => 'required|string|max:255',
                'hire_date' => 'nullable|date',
                'location' => 'nullable|string|max:255',
                'is_suspended' => 'nullable|boolean',
                'suspension_date' => 'nullable|required_if:is_suspended,1|date',
                'is_terminated' => 'nullable|boolean',
                'termination_date' => 'nullable|required_if:is_terminated,1|date',
                'medical_examiner_name' => 'required|string|max:255',
                'medical_examiner_registry_number' => 'required|string|max:255',
                'medical_card_expiration_date' => 'required|date',
                'temp_medical_card_token' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Transacción de base de datos
            DB::beginTransaction();

            try {
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
                if ($request->filled('temp_medical_card_token')) {
                    $tempUploadService = app(TempUploadService::class);
                    $tempPath = $tempUploadService->moveToPermanent($request->input('temp_medical_card_token'));

                    if ($tempPath && file_exists($tempPath)) {
                        $medical->clearMediaCollection('medical_card');
                        $medical->addMedia($tempPath)->toMediaCollection('medical_card');
                    }
                } elseif ($request->hasFile('medical_card_file')) {
                    $medical->clearMediaCollection('medical_card');
                    $medical->addMediaFromRequest('medical_card_file')->toMediaCollection('medical_card');
                }

                // Actualizar el paso actual
                $userDriverDetail->update([
                    'current_step' => $this->driverStepService::STEP_TRAINING
                ]);

                // Confirmar transacción
                DB::commit();

                // Calcular estado de pasos
                $stepsStatus = $this->driverStepService->getStepsStatus($userDriverDetail);
                $completionPercentage = $this->driverStepService->calculateCompletionPercentage($userDriverDetail);

                return response()->json([
                    'success' => true,
                    'message' => 'Información médica actualizada correctamente',
                    'data' => [
                        'id' => $userDriverDetail->id,
                        'steps_status' => $stepsStatus,
                        'completion_percentage' => $completionPercentage,
                        'current_step' => $userDriverDetail->current_step,
                        'redirect_url' => route('admin.carrier.user_drivers.edit', [
                            'carrier' => $carrier,
                            'userDriverDetail' => $userDriverDetail->id,
                            'active_tab' => 'training'
                        ])
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error en actualización médica', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar información médica: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error en validación médica', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar información de entrenamiento del conductor
     */
    public function updateTraining(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail): JsonResponse
    {
        try {
            // Verificar pertenencia al carrier
            if ($userDriverDetail->carrier_id !== $carrier->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este conductor no pertenece al carrier especificado'
                ], 403);
            }

            // Validar los datos
            $validator = Validator::make($request->all(), [
                'has_attended_training_school' => 'required|boolean',
                'training_schools' => 'required_if:has_attended_training_school,1|array',
                'training_schools.*.school_name' => 'required_with:training_schools|string|max:255',
                'training_schools.*.city' => 'required_with:training_schools|string|max:255',
                'training_schools.*.state' => 'required_with:training_schools|string|max:255',
                'training_schools.*.phone_number' => 'nullable|string|max:20',
                'training_schools.*.date_start' => 'required_with:training_schools|date',
                'training_schools.*.date_end' => 'required_with:training_schools|date|after_or_equal:training_schools.*.date_start',
                'training_schools.*.graduated' => 'nullable|boolean',
                'training_schools.*.subject_to_safety_regulations' => 'nullable|boolean',
                'training_schools.*.performed_safety_functions' => 'nullable|boolean',
                'training_schools.*.training_skills' => 'nullable|array',
                'training_schools.*.temp_certificates' => 'nullable|array',
                'training_schools.*.temp_certificates.*' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Transacción de base de datos
            DB::beginTransaction();

            try {
                // Actualizar estado de asistencia a escuela de entrenamiento
                if ($userDriverDetail->application && $userDriverDetail->application->details) {
                    $userDriverDetail->application->details->update([
                        'has_attended_training_school' => $request->boolean('has_attended_training_school')
                    ]);
                } else if ($userDriverDetail->application) {
                    $userDriverDetail->application->details()->create([
                        'has_attended_training_school' => $request->boolean('has_attended_training_school')
                    ]);
                } else {
                    // Crear aplicación si no existe
                    $application = DriverApplication::create([
                        'user_id' => $userDriverDetail->user_id,
                        'status' => 'draft'
                    ]);

                    $application->details()->create([
                        'has_attended_training_school' => $request->boolean('has_attended_training_school')
                    ]);
                }

                // Si ha asistido a escuelas de entrenamiento, procesar datos
                if ($request->boolean('has_attended_training_school') && $request->has('training_schools')) {
                    // Obtener IDs existentes para detectar eliminaciones
                    $existingTrainingIds = $userDriverDetail->trainingSchools()->pluck('id')->toArray();
                    $updatedTrainingIds = [];

                    foreach ($request->input('training_schools') as $schoolData) {
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
                                'training_skills' => $schoolData['training_skills'] ?? [],
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
                                'training_skills' => $schoolData['training_skills'] ?? [],
                            ]);
                        }

                        $updatedTrainingIds[] = $trainingSchool->id;

                        // Procesar certificados si existen
                        if (isset($schoolData['temp_certificates']) && is_array($schoolData['temp_certificates'])) {
                            foreach ($schoolData['temp_certificates'] as $token) {
                                if (!empty($token)) {
                                    $tempUploadService = app(TempUploadService::class);
                                    $tempPath = $tempUploadService->moveToPermanent($token);

                                    if ($tempPath && file_exists($tempPath)) {
                                        $trainingSchool->addMedia($tempPath)->toMediaCollection('school_certificates');
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

                // Actualizar el paso actual
                $userDriverDetail->update([
                    'current_step' => $this->driverStepService::STEP_TRAFFIC
                ]);

                // Confirmar transacción
                DB::commit();

                // Calcular estado de pasos
                $stepsStatus = $this->driverStepService->getStepsStatus($userDriverDetail);
                $completionPercentage = $this->driverStepService->calculateCompletionPercentage($userDriverDetail);

                return response()->json([
                    'success' => true,
                    'message' => 'Información de entrenamiento actualizada correctamente',
                    'data' => [
                        'id' => $userDriverDetail->id,
                        'steps_status' => $stepsStatus,
                        'completion_percentage' => $completionPercentage,
                        'current_step' => $userDriverDetail->current_step,
                        'redirect_url' => route('admin.carrier.user_drivers.edit', [
                            'carrier' => $carrier,
                            'userDriverDetail' => $userDriverDetail->id,
                            'active_tab' => 'traffic'
                        ])
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error en actualización de entrenamiento', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar información de entrenamiento: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error en validación de entrenamiento', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar información de tráfico del conductor
     */
    public function updateTraffic(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail): JsonResponse
    {
        try {
            // Verificar pertenencia al carrier
            if ($userDriverDetail->carrier_id !== $carrier->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este conductor no pertenece al carrier especificado'
                ], 403);
            }

            // Validar los datos
            $validator = Validator::make($request->all(), [
                'has_traffic_convictions' => 'required|boolean',
                'traffic_convictions' => 'nullable|required_if:has_traffic_convictions,1|array',
                'traffic_convictions.*.conviction_date' => 'required_with:traffic_convictions|date',
                'traffic_convictions.*.location' => 'required_with:traffic_convictions|string|max:255',
                'traffic_convictions.*.charge' => 'required_with:traffic_convictions|string|max:255',
                'traffic_convictions.*.penalty' => 'required_with:traffic_convictions|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Transacción de base de datos
            DB::beginTransaction();

            try {
                // Actualizar estado de infracciones de tráfico
                if ($userDriverDetail->application && $userDriverDetail->application->details) {
                    $userDriverDetail->application->details->update([
                        'has_traffic_convictions' => $request->boolean('has_traffic_convictions')
                    ]);
                } else if ($userDriverDetail->application) {
                    $userDriverDetail->application->details()->create([
                        'has_traffic_convictions' => $request->boolean('has_traffic_convictions')
                    ]);
                } else {
                    // Crear aplicación si no existe
                    $application = DriverApplication::create([
                        'user_id' => $userDriverDetail->user_id,
                        'status' => 'draft'
                    ]);

                    $application->details()->create([
                        'has_traffic_convictions' => $request->boolean('has_traffic_convictions')
                    ]);
                }

                // Si tiene infracciones de tráfico, procesar datos
                if ($request->boolean('has_traffic_convictions') && $request->has('traffic_convictions')) {
                    // Obtener IDs existentes para detectar eliminaciones
                    $existingConvictionIds = $userDriverDetail->trafficConvictions()->pluck('id')->toArray();
                    $updatedConvictionIds = [];

                    foreach ($request->input('traffic_convictions') as $convictionData) {
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

                // Actualizar el paso actual
                $userDriverDetail->update([
                    'current_step' => $this->driverStepService::STEP_ACCIDENT
                ]);

                // Confirmar transacción
                DB::commit();

                // Calcular estado de pasos
                $stepsStatus = $this->driverStepService->getStepsStatus($userDriverDetail);
                $completionPercentage = $this->driverStepService->calculateCompletionPercentage($userDriverDetail);

                return response()->json([
                    'success' => true,
                    'message' => 'Información de tráfico actualizada correctamente',
                    'data' => [
                        'id' => $userDriverDetail->id,
                        'steps_status' => $stepsStatus,
                        'completion_percentage' => $completionPercentage,
                        'current_step' => $userDriverDetail->current_step,
                        'redirect_url' => route('admin.carrier.user_drivers.edit', [
                            'carrier' => $carrier,
                            'userDriverDetail' => $userDriverDetail->id,
                            'active_tab' => 'accident'
                        ])
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error en actualización de tráfico', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar información de tráfico: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error en validación de tráfico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar información de accidentes del conductor
     */
    public function updateAccident(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail): JsonResponse
    {
        try {
            // Verificar pertenencia al carrier
            if ($userDriverDetail->carrier_id !== $carrier->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este conductor no pertenece al carrier especificado'
                ], 403);
            }

            // Validar los datos
            $validator = Validator::make($request->all(), [
                'has_accidents' => 'required|boolean',
                'accidents' => 'nullable|required_if:has_accidents,1|array',
                'accidents.*.accident_date' => 'required_with:accidents|date',
                'accidents.*.nature_of_accident' => 'required_with:accidents|string|max:255',
                'accidents.*.had_injuries' => 'nullable|boolean',
                'accidents.*.number_of_injuries' => 'required_if:accidents.*.had_injuries,1|nullable|integer|min:0',
                'accidents.*.had_fatalities' => 'nullable|boolean',
                'accidents.*.number_of_fatalities' => 'required_if:accidents.*.had_fatalities,1|nullable|integer|min:0',
                'accidents.*.comments' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Transacción de base de datos
            DB::beginTransaction();

            try {
                // Actualizar estado de accidentes
                if ($userDriverDetail->application && $userDriverDetail->application->details) {
                    $userDriverDetail->application->details->update([
                        'has_accidents' => $request->boolean('has_accidents')
                    ]);
                } else if ($userDriverDetail->application) {
                    $userDriverDetail->application->details()->create([
                        'has_accidents' => $request->boolean('has_accidents')
                    ]);
                } else {
                    // Crear aplicación si no existe
                    $application = DriverApplication::create([
                        'user_id' => $userDriverDetail->user_id,
                        'status' => 'draft'
                    ]);

                    $application->details()->create([
                        'has_accidents' => $request->boolean('has_accidents')
                    ]);
                }

                // Si tiene accidentes, procesar datos
                if ($request->boolean('has_accidents') && $request->has('accidents')) {
                    // Obtener IDs existentes para detectar eliminaciones
                    $existingAccidentIds = $userDriverDetail->accidents()->pluck('id')->toArray();
                    $updatedAccidentIds = [];

                    foreach ($request->input('accidents') as $accidentData) {
                        // Si tiene ID, es un accidente existente
                        $accidentId = $accidentData['id'] ?? null;
                        $accident = null;

                        if ($accidentId) {
                            $accident = $userDriverDetail->accidents()->find($accidentId);
                        }

                        if (!$accident) {
                            // Crear nuevo accidente
                            $accident = $userDriverDetail->accidents()->create([
                                'accident_date' => $accidentData['accident_date'],
                                'nature_of_accident' => $accidentData['nature_of_accident'],
                                'had_injuries' => isset($accidentData['had_injuries']) ? (bool)$accidentData['had_injuries'] : false,
                                'number_of_injuries' => isset($accidentData['had_injuries']) && $accidentData['had_injuries'] ?
                                    ($accidentData['number_of_injuries'] ?? 0) : 0,
                                'had_fatalities' => isset($accidentData['had_fatalities']) ? (bool)$accidentData['had_fatalities'] : false,
                                'number_of_fatalities' => isset($accidentData['had_fatalities']) && $accidentData['had_fatalities'] ?
                                    ($accidentData['number_of_fatalities'] ?? 0) : 0,
                                'comments' => $accidentData['comments'] ?? null,
                            ]);
                        } else {
                            // Actualizar accidente existente
                            $accident->update([
                                'accident_date' => $accidentData['accident_date'],
                                'nature_of_accident' => $accidentData['nature_of_accident'],
                                'had_injuries' => isset($accidentData['had_injuries']) ? (bool)$accidentData['had_injuries'] : false,
                                'number_of_injuries' => isset($accidentData['had_injuries']) && $accidentData['had_injuries'] ?
                                    ($accidentData['number_of_injuries'] ?? 0) : 0,
                                'had_fatalities' => isset($accidentData['had_fatalities']) ? (bool)$accidentData['had_fatalities'] : false,
                                'number_of_fatalities' => isset($accidentData['had_fatalities']) && $accidentData['had_fatalities'] ?
                                    ($accidentData['number_of_fatalities'] ?? 0) : 0,
                                'comments' => $accidentData['comments'] ?? null,
                            ]);
                        }

                        $updatedAccidentIds[] = $accident->id;
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

                // Verificar si la aplicación está completa
                $isCompleted = $this->checkApplicationCompleted($userDriverDetail);

                // Actualizar el paso actual y la marca de completitud
                $userDriverDetail->update([
                    'current_step' => $this->driverStepService::STEP_GENERAL, // Volver al inicio por ser el último paso
                    'application_completed' => $isCompleted
                ]);

                // Confirmar transacción
                DB::commit();

                // Calcular estado de pasos
                $stepsStatus = $this->driverStepService->getStepsStatus($userDriverDetail);
                $completionPercentage = $this->driverStepService->calculateCompletionPercentage($userDriverDetail);

                return response()->json([
                    'success' => true,
                    'message' => 'Información de accidentes actualizada correctamente',
                    'data' => [
                        'id' => $userDriverDetail->id,
                        'steps_status' => $stepsStatus,
                        'completion_percentage' => $completionPercentage,
                        'current_step' => $userDriverDetail->current_step,
                        'is_completed' => $isCompleted,
                        'redirect_url' => route('admin.carrier.user_drivers.edit', [
                            'carrier' => $carrier,
                            'userDriverDetail' => $userDriverDetail->id,
                            'active_tab' => 'general' // Volver al principio
                        ])
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Error en actualización de accidentes', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar información de accidentes: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error en validación de accidentes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un conductor
     */
    public function destroy(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail): JsonResponse
    {
        try {
            // Verificar pertenencia al carrier
            if ($userDriverDetail->carrier_id !== $carrier->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este conductor no pertenece al carrier especificado'
                ], 403);
            }

            // Eliminar usando transacción
            DB::transaction(function () use ($userDriverDetail) {
                $user = $userDriverDetail->user;

                if ($user) {
                    // Limpiar medios y relaciones
                    $userDriverDetail->clearMediaCollection('profile_photo_driver');

                    // Limpiar relaciones si es necesario
                    $userDriverDetail->licenses()->get()->each(function ($license) {
                        $license->clearMediaCollection('license_front');
                        $license->clearMediaCollection('license_back');
                        $license->endorsements()->detach();
                        $license->delete();
                    });

                    $userDriverDetail->medicalQualification()->get()->each(function ($medical) {
                        $medical->clearMediaCollection('medical_card');
                        $medical->delete();
                    });

                    $userDriverDetail->trainingSchools()->get()->each(function ($school) {
                        $school->clearMediaCollection('school_certificates');
                        $school->delete();
                    });

                    $userDriverDetail->experiences()->delete();
                    $userDriverDetail->workHistories()->delete();
                    $userDriverDetail->trafficConvictions()->delete();
                    $userDriverDetail->accidents()->delete();

                    // Eliminar aplicación
                    if ($userDriverDetail->application) {
                        $userDriverDetail->application->addresses()->delete();
                        $userDriverDetail->application->details()->delete();
                        $userDriverDetail->application->delete();
                    }

                    // Eliminar usuario y conductor (el detail se elimina por cascada)
                    $user->delete();
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Conductor eliminado correctamente',
                'data' => [
                    'redirect_url' => route('admin.carrier.user_drivers.index', $carrier)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar conductor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar conductor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar foto de perfil
     */
    public function deletePhoto(Request $request, Carrier $carrier, UserDriverDetail $userDriverDetail): JsonResponse
    {
        try {
            // Verificar pertenencia al carrier
            if ($userDriverDetail->carrier_id !== $carrier->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este conductor no pertenece al carrier especificado'
                ], 403);
            }

            // Eliminar foto
            if ($userDriverDetail->hasMedia('profile_photo_driver')) {
                $userDriverDetail->clearMediaCollection('profile_photo_driver');

                return response()->json([
                    'success' => true,
                    'message' => 'Foto eliminada correctamente',
                    'data' => [
                        'default_photo_url' => asset('build/default_profile.png')
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No hay foto para eliminar'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al eliminar foto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar foto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Autosave para cualquier paso del formulario
     */
    public function autosave(Request $request, Carrier $carrier): JsonResponse
    {
        try {
            $tab = $request->input('active_tab', 'general');
            $driverId = $request->input('user_driver_id');
            
            // Validación básica según pestaña
            $validator = Validator::make($request->all(), [
                'active_tab' => 'required|string|in:general,licenses,medical,training,traffic,accident',
                'user_driver_id' => 'nullable|integer|exists:user_driver_details,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Si no hay ID de conductor, necesitamos datos básicos para crearlo
            if (!$driverId) {
                if ($tab === 'general') {
                    $validator = Validator::make($request->all(), [
                        'name' => 'required|string|max:255',
                        'email' => 'required|email|unique:users,email',
                        'phone' => 'required|string|max:15',
                    ]);
                    
                    if ($validator->fails()) {
                        return response()->json([
                            'success' => false,
                            'errors' => $validator->errors()
                        ], 422);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Se requiere crear primero la información general'
                    ], 400);
                }
            }
            
            // Transacción de base de datos
            DB::beginTransaction();
            try {
                $userDriverDetail = null;
                $nextStep = 1;
                
                // Si existe ID, obtener el conductor
                if ($driverId) {
                    $userDriverDetail = UserDriverDetail::where('carrier_id', $carrier->id)
                        ->where('id', $driverId)
                        ->first();
                    
                    if (!$userDriverDetail) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Conductor no encontrado'
                        ], 404);
                    }
                } else {
                    // Crear usuario y conductor básico
                    $user = User::create([
                        'name' => $request->input('name'),
                        'email' => $request->input('email'),
                        'password' => Hash::make(Str::random(10)), // Contraseña temporal
                        'status' => 1
                    ]);
                    
                    $user->assignRole('driver');
                    
                    $userDriverDetail = UserDriverDetail::create([
                        'user_id' => $user->id,
                        'carrier_id' => $carrier->id,
                        'phone' => $request->input('phone'),
                        'status' => 1,
                        'current_step' => $this->driverStepService::STEP_GENERAL
                    ]);
                    
                    // Crear aplicación básica
                    $application = DriverApplication::create([
                        'user_id' => $user->id,
                        'status' => 'draft'
                    ]);
                    
                    // Si estamos en la pestaña general y tenemos dirección, la guardamos
                    if ($tab === 'general' && $request->filled('address_line1')) {
                        $application->addresses()->create([
                            'primary' => true,
                            'address_line1' => $request->input('address_line1'),
                            'address_line2' => $request->input('address_line2'),
                            'city' => $request->input('city', ''),
                            'state' => $request->input('state', ''),
                            'zip_code' => $request->input('zip_code', ''),
                            'lived_three_years' => $request->boolean('lived_three_years', false),
                            'from_date' => $request->input('from_date'),
                            'to_date' => $request->input('to_date')
                        ]);
                        
                        // Procesar direcciones adicionales si es necesario
                        if (!$request->boolean('lived_three_years', false) && $request->has('previous_addresses')) {
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
                }
                
                // Determinar siguiente paso según la pestaña actual
                switch ($tab) {
                    case 'general':
                        $nextStep = $this->driverStepService::STEP_LICENSES;
                        break;
                    case 'licenses':
                        $nextStep = $this->driverStepService::STEP_MEDICAL;
                        break;
                    case 'medical':
                        $nextStep = $this->driverStepService::STEP_TRAINING;
                        break;
                    case 'training':
                        $nextStep = $this->driverStepService::STEP_TRAFFIC;
                        break;
                    case 'traffic':
                        $nextStep = $this->driverStepService::STEP_ACCIDENT;
                        break;
                    case 'accident':
                        $nextStep = $this->driverStepService::STEP_GENERAL;
                        break;
                    default:
                        $nextStep = $this->driverStepService::STEP_GENERAL;
                }
                
                // Actualizar el paso actual
                $userDriverDetail->update(['current_step' => $nextStep]);
                
                // Obtener el nombre de la siguiente pestaña
                $nextTabMap = [
                    $this->driverStepService::STEP_GENERAL => 'general',
                    $this->driverStepService::STEP_LICENSES => 'licenses',
                    $this->driverStepService::STEP_MEDICAL => 'medical',
                    $this->driverStepService::STEP_TRAINING => 'training',
                    $this->driverStepService::STEP_TRAFFIC => 'traffic',
                    $this->driverStepService::STEP_ACCIDENT => 'accident',
                ];
                
                $nextTabName = $nextTabMap[$nextStep] ?? 'general';
                
                // Confirmar transacción
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Datos guardados temporalmente',
                    'data' => [
                        'user_driver_id' => $userDriverDetail->id,
                        'next_tab' => $nextTabName,
                        'redirect_url' => route('admin.carrier.user_drivers.edit', [
                            'carrier' => $carrier,
                            'userDriverDetail' => $userDriverDetail->id,
                            'active_tab' => $nextTabName
                        ])
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error en autosave', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar temporalmente: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error en validación de autosave', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Verificar si la aplicación está completa
     */
    private function checkApplicationCompleted(UserDriverDetail $userDriverDetail): bool
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

    /**
     * Obtener el nombre de un endoso según su código
     */
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
}
