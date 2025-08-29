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
use App\Services\CarrierDocumentService;
use App\Traits\GeneratesBaseDocuments;
use App\Events\CarrierRegistrationCompleted;

class CarrierOnboardingController extends Controller
{
    use GeneratesBaseDocuments;

    protected $documentService;

    public function __construct(CarrierDocumentService $documentService)
    {
        $this->documentService = $documentService;
        $this->middleware('auth');
    }

    /**
     * Mostrar el formulario para completar el registro del carrier.
     */
    public function showCompleteRegistrationForm()
    {
        $user = Auth::user();
        
        // Verificar que el usuario tenga el rol correcto
        if (!$user->hasRole('user_carrier')) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Unauthorized access.']);
        }

        // Verificar que no tenga ya un carrier asociado
        if ($user->carrierDetails && $user->carrierDetails->carrier_id) {
            return redirect()->route('carrier.dashboard')
                ->with('info', 'Registration already completed.');
        }

        Log::info('Loading complete registration form', [
            'user_id' => $user->id,
            'path' => 'auth.user_carrier.complete_registration'
        ]);

        $usStates = Constants::usStates();
        $memberships = Membership::where('status', 1)
                                ->where('show_in_register', true)
                                ->get();

        return view('auth.user_carrier.complete_registration', compact('usStates', 'memberships'));
    }

    /**
     * Procesar el formulario de completar registro.
     */
    public function completeRegistration(Request $request)
    {
        $user = Auth::user();
        
        Log::info('Iniciando completeRegistration', [
            'user_id' => $user->id,
            'request_data' => $request->except(['password'])
        ]);

        // Validar los datos del formulario
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
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
            Log::error('Error de validación en completeRegistration', [
                'user_id' => $user->id,
                'errors' => $e->errors()
            ]);
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        try {
            // Crear el Carrier
            $carrier = $this->createCarrier($validated, $user);
            
            // Actualizar UserCarrierDetail
            $this->updateUserCarrierDetail($user, $carrier);
            
            // Generar documentos base
            $this->generateCarrierDocuments($carrier, $user);
            
            // Disparar evento de registro completado
            event(new CarrierRegistrationCompleted($user, $carrier, [
                'registration_method' => 'onboarding',
                'has_documents' => $validated['has_documents'],
                'membership_id' => $validated['id_plan']
            ]));
            
            // Redireccionar según la elección de documentos
            return $this->redirectAfterRegistration($validated, $carrier);
            
        } catch (\Exception $e) {
            Log::error('Error en completeRegistration', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Registration failed. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Crear el carrier con los datos validados.
     */
    private function createCarrier(array $validated, User $user): Carrier
    {
        Log::info('Creando nuevo carrier', [
            'user_id' => $user->id,
            'name' => $validated['name']
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
            'id_plan' => $validated['id_plan'],
            'slug' => Str::slug($validated['name']),
            'referrer_token' => Str::random(16),
            'status' => Carrier::STATUS_PENDING,
            'document_status' => $validated['has_documents'] === 'yes' ? 'in_progress' : 'skipped'
        ]);
        
        Log::info('Carrier creado exitosamente', [
            'user_id' => $user->id,
            'carrier_id' => $carrier->id
        ]);
        
        return $carrier;
    }

    /**
     * Actualizar o crear UserCarrierDetail.
     */
    private function updateUserCarrierDetail(User $user, Carrier $carrier): void
    {
        $userCarrierDetail = $user->carrierDetails;
        
        if ($userCarrierDetail) {
            $userCarrierDetail->update(['carrier_id' => $carrier->id]);
            
            Log::info('UserCarrierDetail actualizado', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id
            ]);
        } else {
            UserCarrierDetail::create([
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'status' => UserCarrierDetail::STATUS_ACTIVE,
                'phone' => 'Not provided',
                'job_position' => 'Not provided',
            ]);
            
            Log::info('Nuevo UserCarrierDetail creado', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id
            ]);
        }
    }

    /**
     * Generar documentos base para el carrier.
     */
    private function generateCarrierDocuments(Carrier $carrier, User $user): void
    {
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
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Redireccionar después del registro según la elección de documentos.
     */
    private function redirectAfterRegistration(array $validated, Carrier $carrier)
    {
        if ($validated['has_documents'] === 'yes') {
            Log::info('Redirigiendo a documentos', [
                'carrier_id' => $carrier->id,
                'carrier_slug' => $carrier->slug
            ]);
            
            return redirect()->route('carrier.documents.index', $carrier->slug)
                ->with('status', 'Please upload your documents to complete registration.');
        }
        
        Log::info('Redirigiendo a confirmación', [
            'carrier_id' => $carrier->id
        ]);
        
        return redirect()->route('carrier.confirmation')
            ->with('status', 'Your registration has been submitted for review. You can upload your documents later.');
    }
}