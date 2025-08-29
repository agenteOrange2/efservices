<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CarrierBasicInfoRequest;
use App\Http\Requests\CarrierCompanyInfoRequest;
use App\Http\Requests\CarrierStep2Request;
use App\Http\Requests\CarrierBankingInfoRequest;
use App\Models\User;
use App\Models\UserCarrierDetail;
use App\Models\Carrier;
use App\Models\Membership;
use App\Models\CarrierDocument;
use App\Models\DocumentType;
use App\Models\CarrierBankingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Events\CarrierStepCompleted;
use App\Events\CarrierRegistrationCompleted;

class CarrierWizardController extends Controller
{
    /**
     * Show step 1: Basic Information
     */
    public function showStep1(): View|RedirectResponse
    {
        // Check if user is already authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // If user has carrier details and is active, redirect to dashboard
            if ($user->carrierDetails && $user->carrierDetails->carrier && $user->status) {
                return redirect()->route('carrier.dashboard');
            }
            
            // If user is verified but hasn't completed registration, continue with wizard
            if ($user->email_verified_at) {
                return redirect()->route('carrier.wizard.step2');
            }
        }
        
        return view('auth.user_carrier.wizard.step1-basic-info');
    }

    /**
     * Process step 1: Basic Information
     */
    public function processStep1(CarrierBasicInfoRequest $request): RedirectResponse
    {
        try {
            if (!app()->environment('testing')) {
                DB::beginTransaction();
            }

            // Additional validation to ensure email uniqueness
            if (User::where('email', $request->email)->exists()) {
                return back()->withErrors([
                    'email' => 'This email is already registered.'
                ])->withInput();
            }

            // Create user with only valid fields for users table
            $user = User::create([
                'name' => $request->full_name, // Map full_name to name
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => true // Active - permite al usuario hacer login inmediatamente
            ]);

            // Assign user_carrier role to the user
            $user->assignRole('user_carrier');

            // Get processed data from request
            $processedData = $request->getProcessedData();
            
            // Create user carrier details with additional fields
            UserCarrierDetail::create([
                'user_id' => $user->id,
                'phone' => $processedData['phone'],
                'job_position' => $processedData['job_position'],
                'status' => 1 // Active - permite al usuario continuar con el wizard
            ]);

            // Send email verification
            $user->sendEmailVerificationNotification();

            if (!app()->environment('testing')) {
                DB::commit();
            }

            // Log successful user creation with more details
            Log::info('Carrier user created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'phone' => $request->phone,
                'job_position' => $request->job_position,
                'step' => 'basic_info',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Disparar evento de paso completado
            event(new CarrierStepCompleted($user, 'step1', [
                'email' => $user->email,
                'name' => $user->name,
                'phone' => $processedData['phone'],
                'job_position' => $processedData['job_position']
            ]));

            // Redirect to login for email verification
            return redirect()->route('login')
                ->with('success', 'Account created successfully! Please check your email to verify your account, then log in to continue with your registration.');

        } catch (\Exception $e) {
            if (!app()->environment('testing')) {
                DB::rollBack();
            }
            
            Log::error('Error creating carrier user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $request->email ?? 'unknown',
                'full_name' => $request->full_name ?? 'unknown',
                'step' => 'basic_info',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return back()->withErrors([
                'general' => 'An error occurred while creating your account. Please try again.'
            ])->withInput();
        }
    }

    // Method removed: showStep1Success - now redirects directly to login after registration

    /**
     * Check email verification status (AJAX)
     */
    public function checkVerification(Request $request): JsonResponse
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['verified' => false, 'error' => 'User not authenticated']);
        }

        return response()->json([
            'verified' => !is_null($user->email_verified_at)
        ]);
    }

    /**
     * Show step 2: Company Information
     */
    public function showStep2(): View|RedirectResponse
    {
        // Temporary logging for debugging
        Log::info('CarrierWizardController showStep2: Access attempt', [
            'is_authenticated' => Auth::check(),
            'user_id' => Auth::check() ? Auth::user()->id : null,
            'email' => Auth::check() ? Auth::user()->email : null,
            'email_verified_at' => Auth::check() ? Auth::user()->email_verified_at : null,
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method(),
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        // Ensure user is authenticated and email is verified
        if (!Auth::check()) {
            Log::warning('CarrierWizardController showStep2: User not authenticated', [
                'redirect_to' => 'carrier.wizard.step1',
                'session_id' => session()->getId()
            ]);
            return redirect()->route('carrier.wizard.step1')
                ->withErrors(['general' => 'Please log in first.']);
        }

        // Temporarily disabled email verification to allow wizard completion
        // TODO: Re-enable after implementing proper email verification flow
        /*
        if (is_null(Auth::user()->email_verified_at)) {
            Log::warning('CarrierWizardController showStep2: Email not verified', [
                'user_id' => Auth::user()->id,
                'email' => Auth::user()->email,
                'redirect_to' => 'carrier.wizard.step1',
                'session_id' => session()->getId()
            ]);
            return redirect()->route('carrier.wizard.step1')
                ->withErrors(['general' => 'Please verify your email first.']);
        }
        */

        Log::info('CarrierWizardController showStep2: Validation passed, showing step 2', [
            'user_id' => Auth::user()->id,
            'email' => Auth::user()->email,
            'session_id' => session()->getId()
        ]);

        $memberships = Membership::where('status', true)
            ->orderBy('id')
            ->get();

        // US States array for dropdown
        $states = [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
            'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
            'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
            'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
            'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
            'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
            'WI' => 'Wisconsin', 'WY' => 'Wyoming', 'DC' => 'District of Columbia'
        ];

        return view('auth.user_carrier.wizard.step2-company-info', compact('memberships', 'states'));
    }

    /**
     * Process step 2: Company Information
     */
    public function processStep2(CarrierStep2Request $request): RedirectResponse
    {
        try {
            if (!app()->environment('testing')) {
                DB::beginTransaction();
            }

            $user = Auth::user();
            
            // Temporarily disabled email verification to allow wizard completion
            // TODO: Re-enable after implementing proper email verification flow
            /*
            if (!$user || is_null($user->email_verified_at)) {
                return redirect()->route('carrier.wizard.step1')
                    ->withErrors(['general' => 'Please verify your email first.']);
            }
            */

            // Get processed data from request
            $carrierData = $request->getProcessedData();
            $carrierData['status'] = 2; // pending_membership
            
            // Create or update carrier
            $carrier = Carrier::updateOrCreate(
                ['user_id' => $user->id],
                $carrierData
            );

            // Update UserCarrierDetail with carrier_id
            $userCarrierDetail = $user->carrierDetails;
            if ($userCarrierDetail) {
                $userCarrierDetail->update([
                    'carrier_id' => $carrier->id,
                    'status' => 1 // active
                ]);
            }

            // Update user status - mantener activo para permitir acceso al wizard
            $user->update(['status' => true]);

            if (!app()->environment('testing')) {
                DB::commit();
            }

            Log::info('Carrier company info updated successfully', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'step' => 'company_info'
            ]);

            // Disparar evento de paso completado
            event(new CarrierStepCompleted($user, 'step2', [
                'carrier_id' => $carrier->id,
                'company_name' => $carrier->name,
                'address' => $carrier->address,
                'state' => $carrier->state
            ]));

            return redirect()->route('carrier.wizard.step3')
                ->with('success', 'Company information saved successfully!');

        } catch (\Exception $e) {
            if (!app()->environment('testing')) {
                DB::rollBack();
            }
            
            Log::error('Error updating carrier company info', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'step' => 'company_info'
            ]);

            return back()->withErrors([
                'general' => 'An error occurred while saving your information. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Show step 3: Membership Selection
     */
    public function showStep3(): View|RedirectResponse
    {
        $user = Auth::user();
        
        // Temporarily disabled email verification to allow wizard completion
        // TODO: Re-enable after implementing proper email verification flow
        /*
        if (!$user || is_null($user->email_verified_at)) {
            return redirect()->route('carrier.wizard.step1')
                ->withErrors(['general' => 'Please verify your email first.']);
        }
        */

        $carrier = $user->carrierDetails?->carrier;
        if (!$carrier) {
            return redirect()->route('carrier.wizard.step2')
                ->withErrors(['general' => 'Please complete your company information first.']);
        }

        $memberships = Membership::where('status', true)
            ->orderBy('id')
            ->get();

        return view('auth.user_carrier.wizard.step3-membership', compact('memberships', 'carrier'));
    }

    /**
     * Process step 3: Membership Selection
     */
    public function processStep3(Request $request): RedirectResponse
    {
        $request->validate([
            'membership_id' => 'required|exists:memberships,id',
            'terms_accepted' => 'required|accepted'
        ]);

        try {
            if (!app()->environment('testing')) {
                DB::beginTransaction();
            }

            $user = Auth::user();
            $carrier = $user->carrierDetails->carrier;
            
            if (!$carrier) {
                return redirect()->route('carrier.wizard.step2')
                    ->withErrors(['general' => 'Please complete your company information first.']);
            }

            $membership = Membership::findOrFail($request->membership_id);

            // Update carrier with membership
            $carrier->update([
                'id_plan' => $membership->id,
                'documents_ready' => 'no',
                'terms_accepted_at' => now(),
                'status' => Carrier::STATUS_PENDING,
                'document_status' => Carrier::DOCUMENT_STATUS_PENDING
            ]);

            // Update user status
            $user->update([
                'status' => true
            ]);

            // Always generate base documents for carrier registration
            $this->generateBaseDocuments($carrier);

            if (!app()->environment('testing')) {
                DB::commit();
            }

            Log::info('Carrier registration completed successfully', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'membership_id' => $membership->id,
                'step' => 'membership_selection'
            ]);

            // Disparar evento de paso completado
            event(new CarrierStepCompleted($user, 'step3', [
                'carrier_id' => $carrier->id,
                'membership_id' => $membership->id,
                'membership_name' => $membership->name ?? 'Unknown'
            ]));

            // Always redirect to step 4 for banking information
            return redirect()->route('carrier.wizard.step4')
                ->with('success', 'Membership selected successfully! Please provide your banking information.');

        } catch (\Exception $e) {
            if (!app()->environment('testing')) {
                DB::rollBack();
            }
            
            Log::error('Error completing carrier registration', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'step' => 'membership_selection'
            ]);

            return back()->withErrors([
                'general' => 'An error occurred while completing your registration. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Generate base documents for carrier
     */
    private function generateBaseDocuments(Carrier $carrier): void
    {
        try {
            Log::info('Starting generateBaseDocuments', ['carrier_id' => $carrier->id]);
            
            $documentTypes = DocumentType::where('requirement', true)
                ->get();

            Log::info('Generating base documents', [
                'carrier_id' => $carrier->id,
                'document_types_found' => $documentTypes->count(),
                'document_types' => $documentTypes->pluck('name')->toArray(),
                'all_document_types' => DocumentType::all()->pluck('name', 'requirement')->toArray()
            ]);
            
            if ($documentTypes->isEmpty()) {
                Log::warning('No document types with requirement=true found');
                return;
            }

            foreach ($documentTypes as $documentType) {
                try {
                    Log::info('Attempting to create carrier document', [
                        'carrier_id' => $carrier->id,
                        'document_type_id' => $documentType->id,
                        'document_type_name' => $documentType->name
                    ]);
                    
                    $carrierDocument = CarrierDocument::firstOrCreate(
                         [
                             'carrier_id' => $carrier->id,
                             'document_type_id' => $documentType->id
                         ],
                         [
                             'status' => CarrierDocument::STATUS_PENDING,
                             'date' => now(),
                             'created_at' => now(),
                             'updated_at' => now()
                         ]
                     );
                    
                    Log::info('Successfully created carrier document', [
                        'carrier_id' => $carrier->id,
                        'document_type_id' => $documentType->id,
                        'document_name' => $documentType->name,
                        'carrier_document_id' => $carrierDocument->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create carrier document', [
                        'carrier_id' => $carrier->id,
                        'document_type_id' => $documentType->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            // Verificar cuántos documentos se crearon
            $createdCount = CarrierDocument::where('carrier_id', $carrier->id)->count();
            Log::info('Finished generateBaseDocuments', [
                'carrier_id' => $carrier->id,
                'documents_created' => $createdCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating base documents', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'carrier_id' => $carrier->id
            ]);
            
            // Don't throw exception as this is not critical for registration flow
        }
    }

    /**
     * Show step 4: Banking Information
     */
    public function showStep4(): View|RedirectResponse
    {
        $user = Auth::user();
        $carrier = $user->carrierDetails->carrier;
        
        if (!$carrier || !$carrier->id_plan) {
            return redirect()->route('carrier.wizard.step3')
                ->withErrors(['general' => 'Please complete your membership selection first.']);
        }

        // Check if user is from US (banking information is only required for US carriers)
        if ($carrier->country !== 'US') {
            return redirect()->route('carrier.dashboard')
                ->with('error', 'Banking information is only required for US carriers.');
        }

        // Check if banking details already exist
        $bankingDetails = $carrier->bankingDetails;
        
        return view('auth.user_carrier.wizard.step4-banking-info', compact('bankingDetails'));
    }

    /**
     * Process step 4: Banking Information
     */
    public function processStep4(CarrierBankingInfoRequest $request): RedirectResponse
    {
        try {
            if (!app()->environment('testing')) {
                DB::beginTransaction();
            }

            $user = Auth::user();
            $carrier = $user->carrierDetails->carrier;
            
            if (!$carrier || !$carrier->id_plan) {
                return redirect()->route('carrier.wizard.step3')
                    ->withErrors(['general' => 'Please complete your membership selection first.']);
            }

            // Check if user is from US (banking information is only required for US carriers)
            if ($carrier->country !== 'US') {
                return redirect()->route('carrier.dashboard')
                    ->with('error', 'Banking information is only required for US carriers.');
            }

            // Create or update banking details
            CarrierBankingDetail::updateOrCreate(
                ['carrier_id' => $carrier->id],
                [
                    'account_number' => $request->account_number,
                    'account_holder_name' => $request->account_holder_name,
                    'country_code' => $request->country_code,
                    'status' => CarrierBankingDetail::STATUS_PENDING
                ]
            );

            // Update carrier status to pending for admin review
            $carrier->update([
                'status' => Carrier::STATUS_PENDING
            ]);

            if (!app()->environment('testing')) {
                DB::commit();
            }

            Log::info('Banking information submitted successfully', [
                'user_id' => $user->id,
                'carrier_id' => $carrier->id,
                'country_code' => $request->country_code,
                'step' => 'banking_information'
            ]);

            // Disparar evento de registro completado
            event(new CarrierRegistrationCompleted($user, $carrier, [
                'banking_info' => 'completed',
                'registration_method' => 'wizard',
                'total_steps' => 4
            ]));

            // Redirect to pending validation page
            return redirect()->route('carrier.pending.validation')
                ->with('success', 'Banking information saved! Your account is now pending validation by our administrators. You will be notified once approved.');

        } catch (\Exception $e) {
            if (!app()->environment('testing')) {
                DB::rollBack();
            }
            
            Log::error('Error saving banking information', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'step' => 'banking_information'
            ]);

            return back()->withErrors([
                'general' => 'An error occurred while saving your banking information. Please try again.'
            ])->withInput();
        }
    }

    /**
     * AJAX endpoint to check field uniqueness
     */
    public function checkUniqueness(Request $request): JsonResponse
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $field = $request->input('field');
        $value = $request->input('value');
        $currentId = $request->input('current_id'); // For updates

        if (!$field || !$value) {
            return response()->json(['unique' => false, 'message' => 'Invalid parameters']);
        }

        $available = true;
        $message = '';

        try {
            switch ($field) {
                case 'email':
                    $query = User::where('email', $value);
                    if ($currentId) {
                        $query->where('id', '!=', $currentId);
                    }
                    $exists = $query->exists();
                    $available = !$exists;
                    $message = $exists ? 'This email is already registered' : 'Email is available';
                    break;

                case 'dot':
                    $query = Carrier::where('dot_number', $value);
                    if ($currentId) {
                        $query->where('id', '!=', $currentId);
                    }
                    $exists = $query->exists();
                    $available = !$exists;
                    $message = $exists ? 'This DOT number is already registered' : 'DOT number is available';
                    break;

                case 'mc':
                    $query = Carrier::where('mc_number', $value);
                    if ($currentId) {
                        $query->where('id', '!=', $currentId);
                    }
                    $exists = $query->exists();
                    $available = !$exists;
                    $message = $exists ? 'This MC number is already registered' : 'MC number is available';
                    break;

                case 'ein':
                    $query = Carrier::where('ein_number', $value);
                    if ($currentId) {
                        $query->where('id', '!=', $currentId);
                    }
                    $exists = $query->exists();
                    $available = !$exists;
                    $message = $exists ? 'This EIN is already registered' : 'EIN is available';
                    break;

                default:
                    return response()->json(['unique' => false, 'message' => 'Invalid field']);
            }

        } catch (\Exception $e) {
            Log::error('Error checking field uniqueness', [
                'error' => $e->getMessage(),
                'field' => $field,
                'value' => $value
            ]);

            return response()->json([
                'unique' => false, 
                'message' => 'Error checking availability'
            ]);
        }

        return response()->json([
            'unique' => $available,
            'message' => $message
        ]);
    }
}