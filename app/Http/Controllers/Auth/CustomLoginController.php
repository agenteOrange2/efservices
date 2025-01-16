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


class CustomLoginController
{
    use GeneratesBaseDocuments;

    protected $documentService;
    protected $notificationService;

    public function __construct(
        CarrierDocumentService $documentService,
        NotificationService $notificationService
    ) {
        $this->documentService = $documentService;
        $this->notificationService = $notificationService;
    }



    public function authenticated(Request $request, $user)
    {
        if ($user->hasRole('user_carrier')) {
            if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
                return redirect()->route('carrier.complete_registration')
                    ->with('status', 'Please complete your carrier registration.');
            }

            // Verificar si el usuario necesita subir documentos
            $carrier = $user->carrierDetails->carrier;
            if ($carrier->document_status === 'in_progress') {
                return redirect()->route('carrier.documents.index', $carrier->slug)
                    ->with('status', 'Please complete your document submission.');
            }

            if ($carrier->status !== Carrier::STATUS_ACTIVE) {
                return redirect()->route('carrier.confirmation')
                    ->with('warning', 'Your carrier account is pending approval.');
            }

            return redirect()->route('carrier.dashboard');
        }

        if ($user->hasRole('user_driver')) {
            return redirect()->route('driver.dashboard');
        }

        return redirect()->route('admin');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->hasRole('user_carrier')) {
                if (!$user->carrierDetails || !$user->carrierDetails->carrier_id) {
                    return redirect()->route('carrier.complete_registration')
                        ->with('warning', 'Please complete your registration.');
                }

                if ($user->carrierDetails->carrier->status !== Carrier::STATUS_ACTIVE) {
                    return redirect()->route('carrier.confirmation')
                        ->with('warning', 'Your carrier account is pending approval.');
                }

                return redirect()->route('carrier.dashboard');
            }

            if ($user->hasRole('superadmin')) {
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
                'status' => UserCarrierDetail::STATUS_PENDING, // Utilizando la constante de UserCarrierDetail
            ]);

            // Asignar el rol automáticamente
            $user->assignRole('user_carrier');
            Log::info('Rol asignado al User.', ['user_id' => $user->id, 'role' => 'user_carrier']);

            // Crear el detalle del UserCarrier
            $userCarrierDetail = $user->carrierDetails()->create([
                'phone' => $validated['phone'],
                'job_position' => $validated['job_position'],
                'status' => UserCarrierDetail::STATUS_PENDING, // Utilizando la constante de UserCarrierDetail
                'confirmation_token' => Str::random(32), // Generar un token de confirmación
            ]);

            // Agregar notificación para admins
            $this->notificationService->notifyAdminsOfNewCarrier(
                $user,
                "New carrier user registered: {$user->name} ({$user->email})"
            );

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
        $memberships = Membership::where('status', 1)->select('id', 'name')->get();

        return view('auth.user_carrier.complete_registration', compact('usStates', 'memberships'));
    }

    public function completeRegistration(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zipcode' => 'required|string|max:10',
            'ein_number' => 'required|string|max:255',
            'dot_number' => 'required|string|max:255',
            'mc_number' => 'nullable|string|max:255',
            'state_dot' => 'nullable|string|max:255',
            'ifta_account' => 'nullable|string|max:255',
            'id_plan' => 'required|exists:memberships,id',
            'has_documents' => 'required|in:yes,no'
        ]);

        $user = Auth::user();

        // Crear el Carrier
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
            'slug' => Str::slug($validated['name']),
            'referrer_token' => Str::random(16),
            'status' => Carrier::STATUS_PENDING,
            'document_status' => $validated['has_documents'] === 'yes' ? 'in_progress' : 'skipped'
        ]);

        // Actualizar el detalle del usuario
        $user->carrierDetails()->update([
            'carrier_id' => $carrier->id,
        ]);

        // Generar documentos base usando el servicio
        $this->documentService->generateBaseDocuments($carrier);


        // Agregar notificación para admins sobre el carrier creado
        $this->notificationService->createNotification(
            User::role('superadmin')->get(),
            'new_carrier_registration',
            "New carrier company registered: {$carrier->name}"
        );
        // Redireccionar basado en la elección de documentos
        if ($validated['has_documents'] === 'yes') {
            return redirect()->route('carrier.documents.index', $carrier->slug)
                ->with('status', 'Please upload your documents to complete registration.');
        }

        return redirect()->route('carrier.confirmation')
            ->with('status', 'Your registration has been submitted for review. You can upload your documents later.');
    }
}
