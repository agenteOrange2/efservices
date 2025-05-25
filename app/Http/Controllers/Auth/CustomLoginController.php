<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Carrier;
use App\Helpers\Constants;
use App\Models\Membership;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\UserCarrierDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\CarrierConfirmationMail;
use App\Services\NotificationService;
use App\Traits\GeneratesBaseDocuments;
use App\Services\CarrierDocumentService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;


class CustomLoginController extends Controller
{
    use GeneratesBaseDocuments;

    protected $documentService;

    public function __construct(CarrierDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function authenticated(Request $request, $user)
    {
        // Verificar si el usuario tiene el rol de carrier
        if ($user instanceof \App\Models\User && $user->hasRole('user_carrier')) {
            // Verificar si el usuario necesita completar el registro
            if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
                return redirect()->route('carrier.complete_registration')
                    ->with('warning', 'Please complete your registration.');
            }

            $carrier = $user->carrierDetails->carrier;
            
            // Si está pendiente o inactivo
            if ($carrier->status !== Carrier::STATUS_ACTIVE) {
                return redirect()->route('carrier.confirmation')
                    ->with('warning', 'Your account is pending approval.');
            }

            // Si todo está bien, redirigir al dashboard del carrier
            return redirect()->route('carrier.dashboard');
        }

        // Si no es carrier, redirigir según el rol
        if ($user instanceof \App\Models\User && $user->hasRole('superadmin')) {
            return redirect()->route('admin.dashboard');
        }

        // Por defecto
        return redirect()->route('home');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
    
            // Verificar si el usuario tiene el rol de carrier
            if ($user instanceof \App\Models\User && $user->hasRole('user_carrier')) {
                // Verificar si el usuario tiene carrier details
                if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
                    return redirect()->route('carrier.complete_registration')
                        ->with('warning', 'Please complete your registration.');
                }
                
    
                $carrier = $user->carrierDetails->carrier;
    
                // Verificar estado del carrier
                if ($carrier->status === Carrier::STATUS_PENDING) {
                    return redirect()->route('carrier.confirmation')
                        ->with('warning', 'Your account is pending approval.');
                }
    
                if ($carrier->status === Carrier::STATUS_INACTIVE) {
                    Auth::logout();
                    return redirect()->route('login')
                        ->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
                }
    
                // Verificar estado de documentos
                if ($carrier->document_status === Carrier::DOCUMENT_STATUS_IN_PROGRESS) {
                    return redirect()->route('carrier.documents.index', $carrier->slug)
                        ->with('warning', 'Please complete your document submission.');
                }
    
                return redirect()->route('carrier.dashboard');
            }
    
            // Si es superadmin
            if ($user instanceof \App\Models\User && $user->hasRole('superadmin')) {
                return redirect()->route('admin.dashboard');
            }
        }
    
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function showRegisterForm(Request $request)
    {
        if ($request->is('carrier/*')) {
            return view('auth.user_carrier.register'); // Vista para user_carrier
        }

        if ($request->is('driver/*')) {
            return view('auth.user_driver.register'); // Vista para user_driver
        }

        abort(404); // Mostrar error si no corresponde a ninguna ruta válida
    }

    public function register(Request $request)
    {
        if ($request->routeIs('carrier.*')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'required|string|max:15',
                'job_position' => 'required|string|max:255',
            ]);

            // Crear el usuario
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => UserCarrierDetail::STATUS_ACTIVE, // Cambiado a ACTIVE para que pueda iniciar sesión y completar el registro del carrier
            ]);

            // Asignar el rol automáticamente
            $user->assignRole('user_carrier');
            Log::info('Rol asignado al User.', ['user_id' => $user->id, 'role' => 'user_carrier']);

            // Crear el detalle del UserCarrier
            $userCarrierDetail = $user->carrierDetails()->create([
                'phone' => $validated['phone'],
                'job_position' => $validated['job_position'],
                'status' => UserCarrierDetail::STATUS_ACTIVE, // Cambiado a ACTIVE para ser consistente con el status del usuario
                'confirmation_token' => Str::random(32), // Generar un token de confirmación
            ]);

            Log::info('UserCarrierDetail creado.', ['user_carrier_detail_id' => $userCarrierDetail->id]);

            // Enviar correo de confirmación
            Mail::to($user->email)->send(new CarrierConfirmationMail($userCarrierDetail));

            return redirect()->route('login')->with('status', 'Registration successful. Please check your email to confirm.');
        }

        abort(404); // Si no corresponde a la ruta, devolver 404
    }


    public function confirmEmail($token)
    {
        // Busca el detalle del usuario carrier usando el token
        $userCarrierDetail = UserCarrierDetail::where('confirmation_token', $token)->first();

        if (!$userCarrierDetail) {
            return redirect()->route('login')->withErrors([
                'email' => 'Invalid or expired confirmation token.',
            ]);
        }

        // Actualiza el estado del correo electrónico y elimina el token
        $userCarrierDetail->update([
            'confirmation_token' => null,
            'status' => UserCarrierDetail::STATUS_ACTIVE,
        ]);

        // Autenticar al usuario
        Auth::login($userCarrierDetail->user);

        return redirect()->route('admin.dashboard')
            ->with('status', 'Your email has been confirmed. Welcome to the admin dashboard!');
    }


    public function showCompleteRegistrationForm(Request $request)
    {
        Log::info('Loading complete registration form', [
            'user' => Auth::user(),
            'path' => 'auth.user_carrier.complete_registration'
        ]);

        $usStates = Constants::usStates();
        // Cargar solo las membresías activas y marcadas para mostrar en el registro
        $memberships = Membership::where('status', 1)
                               ->where('show_in_register', true)
                               ->get();

        return view('auth.user_carrier.complete_registration', compact('usStates', 'memberships'));
    }

    public function completeRegistration(Request $request)
    {
        // Activar la visualización de errores para depuración
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        
        // Registrar los datos recibidos para depuración
        Log::info('Iniciando completeRegistration', [
            'user_id' => Auth::id(),
            'request_data' => $request->except(['password'])
        ]);
        
        // Validar los datos del formulario con mensajes de error detallados
        try {
            $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Verificar que el nombre no exista ya en la tabla carriers
                function ($attribute, $value, $fail) {
                    if (Carrier::where('name', $value)->exists()) {
                        $fail('The company name has already been taken. Please choose a different name.');
                    }
                },
            ],
            'address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:10',
            'ein_number' => 'required|string|max:255',
            'dot_number' => [
                'nullable',
                'string',
                'max:255',
                // Verificar que el DOT number no exista ya en la tabla carriers (si se proporciona)
                function ($attribute, $value, $fail) {
                    if ($value && Carrier::where('dot_number', $value)->exists()) {
                        $fail('This DOT number is already registered in our system.');
                    }
                },
            ],
            'mc_number' => [
                'nullable',
                'string',
                'max:255',
                // Verificar que el MC number no exista ya en la tabla carriers (si se proporciona)
                function ($attribute, $value, $fail) {
                    if ($value && Carrier::where('mc_number', $value)->exists()) {
                        $fail('This MC number is already registered in our system.');
                    }
                },
            ],
            'state_dot' => 'nullable|string|max:255',
            'ifta_account' => 'nullable|string|max:255',
            'id_plan' => 'required|exists:memberships,id',
            'has_documents' => 'required|in:yes,no'
        ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Capturar y registrar los errores de validación
            Log::error('Error de validación en completeRegistration', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['password'])
            ]);
            
            // Mostrar los errores en la respuesta
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error_debug', 'Validation failed: ' . json_encode($e->errors()));
        }

        $user = Auth::user();

        try {
            // Crear el Carrier
            Log::info('Creando nuevo carrier', [
                'user_id' => $user->id,
                'name' => $validated['name'],
                'dot_number' => $validated['dot_number']
            ]);
            
            $carrier = Carrier::create([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'state' => $validated['state'],
                'zipcode' => $validated['zipcode'],
                'ein_number' => $validated['ein_number'],
                'dot_number' => $validated['dot_number'],
                'mc_number' => $validated['mc_number'],
                'state_dot' => $validated['state_dot'],
                'ifta_account' => $validated['ifta_account'],
                'id_plan' => $validated['id_plan'], // Aseguramos que se guarde el id_plan
                'slug' => Str::slug($validated['name']),
                'referrer_token' => Str::random(16),
                'status' => Carrier::STATUS_PENDING,
                'document_status' => $validated['has_documents'] === 'yes' ? 'in_progress' : 'skipped'
            ]);
            
            Log::info('Carrier creado exitosamente', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'carrier_status' => $carrier->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear carrier', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->withErrors(['error' => 'Error al crear el carrier: ' . $e->getMessage()]);
        }

        // Actualizar o crear el detalle del usuario
        try {
            $userCarrierDetail = $user->carrierDetails;
            
            // LOGS DETALLADOS para depuración
            Log::error('ESTADO INICIAL de UserCarrierDetail', [
                'user_id' => $user->id,
                'tiene_carrier_details' => $userCarrierDetail ? 'SI' : 'NO',
                'phone' => $userCarrierDetail ? $userCarrierDetail->phone : 'N/A',
                'job_position' => $userCarrierDetail ? $userCarrierDetail->job_position : 'N/A',
                'carrier_id_actual' => $userCarrierDetail ? $userCarrierDetail->carrier_id : 'N/A',
                'nuevo_carrier_id' => $carrier->id
            ]);
            
            if ($userCarrierDetail) {
                // Actualizar el registro existente PRESERVANDO phone y job_position
                $userCarrierDetail->carrier_id = $carrier->id;
                $userCarrierDetail->save();
                
                // Log después de actualizar
                Log::error('ACTUALIZACIÓN de UserCarrierDetail - DATOS PRESERVADOS', [
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id,
                    'phone_preservado' => $userCarrierDetail->phone,
                    'job_position_preservado' => $userCarrierDetail->job_position
                ]);
            } else {
                // Crear un nuevo registro de detalles del carrier para el usuario
                $phone = $request->input('phone') ?? $user->phone ?? 'Not provided';
                $jobPosition = $request->input('job_position') ?? 'Not provided';
                
                $newDetail = UserCarrierDetail::create([
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id,
                    'status' => UserCarrierDetail::STATUS_ACTIVE,
                    'phone' => $phone,
                    'job_position' => $jobPosition,
                ]);
                
                // Log después de crear nuevo detalle
                Log::error('NUEVO UserCarrierDetail creado', [
                    'user_id' => $user->id,
                    'userCarrierDetail_id' => $newDetail->id,
                    'carrier_id' => $carrier->id,
                    'phone_asignado' => $phone,
                    'job_position_asignado' => $jobPosition
                ]);
                
                // Recargar la relación para que esté disponible en la misma solicitud
                $user = User::find($user->id); // Recargar el usuario con sus relaciones
            }
        } catch (\Exception $e) {
            
            Log::error('Error al actualizar UserCarrierDetail', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->withErrors(['error' => 'Error al actualizar detalles del usuario: ' . $e->getMessage()]);
        }

        // Generar documentos base usando el servicio
        try {
            
            Log::info('Generando documentos base', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id
            ]);
            
            $this->documentService->generateBaseDocuments($carrier);
            
            
            Log::info('Documentos base generados correctamente', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id
            ]);
            

        } catch (\Exception $e) {
            
            Log::error('Error al generar documentos base', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // No hacemos return aquí para permitir que continúe con la redirección
        }

        // INICIO PROCESO DE REDIRECCIÓN FINAL
        Log::error('INICIO REDIRECCIÓN FINAL', [
            'user_id' => $user->id,
            'carrier_id' => $carrier->id,
            'has_documents' => $validated['has_documents'],
            'carrier_slug' => $carrier->slug
        ]);
        
        try {
            if ($validated['has_documents'] === 'yes') {
                // Redirección a la página de documentos
                Log::error('INTENTANDO REDIRECCIÓN A DOCUMENTOS', [
                    'user_id' => $user->id,
                    'carrier_id' => $carrier->id,
                    'carrier_slug' => $carrier->slug,
                    'url_destino' => "/carrier/{$carrier->slug}/documents"
                ]);
                
                // Implementación de múltiples logs antes y después de la redirección
                $redirectResponse = redirect("/carrier/{$carrier->slug}/documents")
                    ->with('status', 'Please upload your documents to complete registration.');
                
                Log::error('REDIRECCIÓN A DOCUMENTOS GENERADA (aún no enviada)', [
                    'user_id' => $user->id, 
                    'url' => "/carrier/{$carrier->slug}/documents"
                ]);
                
                return $redirectResponse;
            }
            
            // Redirección a la página de confirmación
            Log::error('INTENTANDO REDIRECCIÓN A CONFIRMATION', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'url_destino' => '/carrier/confirmation'
            ]);
            
            // Usar mismo patrón que para documentos - crear primero la respuesta, luego log, luego return
            $redirectResponse = redirect('/carrier/confirmation')
                ->with('status', 'Your registration has been submitted for review. You can upload your documents later.');
            
            Log::error('REDIRECCIÓN A CONFIRMATION GENERADA (aún no enviada)', [
                'user_id' => $user->id,
                'url' => '/carrier/confirmation'
            ]);
            
            return $redirectResponse;
        } catch (\Exception $e) {
            
            Log::error('Error en la redirección final', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Si hay un error en la redirección, intentamos una alternativa simple
            return redirect('/carrier/confirmation')
                ->with('status', 'Registration completed, but there was an issue with the redirection. Your registration has been submitted for review.');
        }
    }
}
