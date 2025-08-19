<x-guest-layout>
    <div class="container grid grid-cols-12 px-5 py-10 sm:px-10 sm:py-14 md:px-36 lg:h-screen lg:max-w-[1550px] lg:py-0 lg:pl-14 lg:pr-12 xl:px-24 2xl:max-w-[1750px]">
        <div @class([
            'relative z-50 h-full col-span-12 p-7 sm:p-14 bg-white rounded-2xl lg:bg-transparent lg:pr-10 lg:col-span-5 xl:pr-24 2xl:col-span-4 lg:p-0',
            "before:content-[''] before:absolute before:inset-0 before:-mb-3.5 before:bg-white/40 before:rounded-2xl before:mx-5",
        ])>
            <div class="relative z-10 flex flex-col justify-center w-full h-full py-2 lg:py-32">
                <!-- Logo -->
                <div class="flex h-[55px] w-[55px] items-center justify-center rounded-[0.8rem] border border-primary/30">
                    <div class="relative flex h-[50px] w-[50px] items-center justify-center rounded-[0.6rem] bg-white bg-gradient-to-b from-theme-1/90 to-theme-2/90">
                        <div class="relative h-[26px] w-[26px] -rotate-45 [&_div]:bg-white">
                            <div class="absolute inset-y-0 left-0 my-auto h-[75%] w-[20%] rounded-full opacity-50"></div>
                            <div class="absolute inset-0 m-auto h-[120%] w-[20%] rounded-full"></div>
                            <div class="absolute inset-y-0 right-0 my-auto h-[75%] w-[20%] rounded-full opacity-50"></div>
                        </div>
                    </div>
                </div>

                <!-- Progress Stepper -->
                <div class="mt-6 sm:mt-8">
                    <x-progress-stepper 
                        :steps="[
                            ['label' => 'Basic Info', 'description' => 'Personal details'],
                            ['label' => 'Company', 'description' => 'Business information'],
                            ['label' => 'Membership', 'description' => 'Select plan'],
                            ['label' => 'Banking Info', 'description' => 'Payment details']
                        ]"
                        :current-step="3"
                        :completed-steps="[1, 2]"
                        size="sm"
                        class="mb-4 sm:mb-6"
                    />
                </div>

                <!-- Header -->
                <div class="mt-4 sm:mt-6">
                    <div class="text-xl sm:text-2xl font-medium">Choose Your Plan</div>
                    <div class="mt-2 sm:mt-2.5 text-sm sm:text-base text-slate-600">
                        Select the membership that best fits your business needs
                    </div>
                </div>

                <!-- Form -->
                <div class="mt-4 sm:mt-6">
                    <!-- Alpine.js Data Container - Extended to include entire form and summary -->
                    <div x-data="{
                        selectedPlan: null,
                        termsAccepted: false,
                        
                        selectPlan(planId) {
                            this.selectedPlan = planId;
                            document.querySelector(`input[name='membership_id'][value='${planId}']`).checked = true;
                        }
                    }" x-init="
                        // Initialize with old values if they exist
                        const checkedMembership = document.querySelector('input[name=\"membership_id\"]:checked');
                        if (checkedMembership) {
                            this.selectedPlan = parseInt(checkedMembership.value);
                        }
                        
                        const checkedTerms = document.querySelector('input[name=\"terms_accepted\"]:checked');
                        if (checkedTerms) {
                            this.termsAccepted = true;
                        }
                    ">
                    <div class="flex flex-col  lg:gap-8">
                        <!-- Left Panel - Form -->
                        <div class="lg:w-full">
                            <form method="POST" action="{{ route('carrier.wizard.step3.process') }}" id="membership-form">
                                @csrf
                                <input type="hidden" name="step" value="membership">

                        <!-- Error Messages -->
                        @if ($errors->any())
                            <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    <strong>Please correct the following errors:</strong>
                                </div>
                                <ul class="list-disc pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <!-- Membership Plans -->
                        <div class="space-y-3 sm:space-y-4 mb-4 sm:mb-6">
                            @foreach($memberships as $membership)
                                <div class="membership-option border-2 rounded-lg p-3 sm:p-4 cursor-pointer transition-all duration-200" 
                                     :class="selectedPlan === {{ $membership->id }} ? 'border-primary bg-primary/5' : 'border-slate-200 hover:border-primary/50'"
                                     @click="selectPlan({{ $membership->id }})"
                                     data-membership-id="{{ $membership->id }}">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start">
                                            <input type="radio" 
                                                   name="membership_id" 
                                                   value="{{ $membership->id }}" 
                                                   id="membership_{{ $membership->id }}"
                                                   class="mt-0.5 sm:mt-1 mr-2 sm:mr-3 text-primary focus:ring-primary"
                                                   {{ old('membership_id') == $membership->id ? 'checked' : '' }}>
                                            <div class="flex-1">
                                                <label for="membership_{{ $membership->id }}" class="block text-sm sm:text-base font-medium text-slate-900 cursor-pointer">
                                                    {{ $membership->name }}
                                                </label>
                                                @if($membership->description)
                                                    <p class="text-xs sm:text-sm text-slate-600 mt-1">{{ $membership->description }}</p>
                                                @endif
                                                
                                                <!-- Pricing Details -->
                                                <div class="mt-2 sm:mt-3 text-xs sm:text-sm">
                                                    @if($membership->pricing_model === 'plan_based')
                                                        <div class="flex items-center text-primary font-semibold">
                                                            <span class="text-lg sm:text-2xl">${{ number_format($membership->weekly_price, 2) }}</span>
                                                            <span class="ml-1 text-xs sm:text-sm text-slate-600">/week</span>
                                                        </div>
                                                        @if($membership->setup_fee > 0)
                                                            <div class="text-xs sm:text-sm text-slate-600 mt-1">
                                                                Setup fee: ${{ number_format($membership->setup_fee, 2) }}
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="space-y-1">
                                                            @if($membership->price_per_user > 0)
                                                                <div class="text-xs sm:text-sm text-slate-700">
                                                                    Users: ${{ number_format($membership->price_per_user, 2) }}/week each
                                                                </div>
                                                            @endif
                                                            @if($membership->price_per_driver > 0)
                                                                <div class="text-xs sm:text-sm text-slate-700">
                                                                    Drivers: ${{ number_format($membership->price_per_driver, 2) }}/week each
                                                                </div>
                                                            @endif
                                                            @if($membership->price_per_vehicle > 0)
                                                                <div class="text-xs sm:text-sm text-slate-700">
                                                                    Vehicles: ${{ number_format($membership->price_per_vehicle, 2) }}/week each
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Features -->
                                                @if($membership->features)
                                                    <div class="mt-2 sm:mt-3">
                                                        <div class="text-xs font-medium text-slate-700 mb-1 sm:mb-2">INCLUDES:</div>
                                                        <div class="grid grid-cols-1 gap-1">
                                                            @foreach(explode(',', $membership->features) as $feature)
                                                                <div class="flex items-center text-xs text-slate-600">
                                                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 mr-1.5 sm:mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ trim($feature) }}
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Popular Badge -->
                                        @if($membership->is_popular)
                                            <div class="bg-primary text-white text-xs px-2 py-0.5 sm:py-1 rounded-full font-medium">
                                                POPULAR
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>



                        <!-- Terms and Conditions -->
                        <div class="mb-4 sm:mb-6">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" name="terms_accepted" value="1" class="mt-0.5 sm:mt-1 mr-2 sm:mr-3 text-primary focus:ring-primary" {{ old('terms_accepted') ? 'checked' : '' }} required @change="termsAccepted = $event.target.checked">
                                <span class="text-xs sm:text-sm text-slate-700">
                                    I agree to the 
                                    <button type="button" class="text-primary hover:underline" onclick="openTermsModal()">Terms of Service</button> 
                                    and 
                                    <button type="button" class="text-primary hover:underline" onclick="openPrivacyModal()">Privacy Policy</button>
                                </span>
                            </label>
                            <div class="text-red-500 text-sm mt-1 hidden" id="terms_accepted-error"></div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                            <x-base.button
                                type="button"
                                class="flex-1 bg-slate-100 text-slate-700 py-2.5 sm:py-3.5 text-sm sm:text-base font-medium transition-all duration-200 hover:bg-slate-200"
                                variant="secondary" 
                                rounded
                                onclick="window.history.back()"
                            >
                                <span class="flex items-center justify-center">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                                    </svg>
                                    Back
                                </span>
                            </x-base.button>
                            <x-base.button
                                type="submit"
                                class="flex-1 bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-2.5 sm:py-3.5 text-sm sm:text-base text-white font-medium transition-all duration-200 hover:from-theme-1 hover:to-theme-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                variant="primary" 
                                rounded
                                id="submit-btn"
                                disabled
                            >
                                <span class="flex items-center justify-center">
                                    <span id="submit-text">Continue to Banking Info</span>
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 ml-1.5 sm:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                    <div class="hidden" id="loading-spinner">
                                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 sm:h-5 sm:w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </span>
                            </x-base.button>
                                </div>
                            </form>
                        </div>
                        

                    </div>
                    </div> <!-- End Alpine.js Data Container -->
                </div>
            </div>
        </div>
    </div>
    <div
        class="container fixed inset-0 grid h-screen w-screen grid-cols-12 pl-14 pr-12 lg:max-w-[1550px] xl:px-24 2xl:max-w-[1750px]">
        <div @class([
            'relative h-screen col-span-12 lg:col-span-5 2xl:col-span-4 z-20',
            "after:bg-white after:hidden after:lg:block after:content-[''] after:absolute after:right-0 after:inset-y-0 after:bg-gradient-to-b after:from-white after:to-slate-100/80 after:w-[800%] after:rounded-[0_1.2rem_1.2rem_0/0_1.7rem_1.7rem_0]",
            "before:content-[''] before:hidden before:lg:block before:absolute before:right-0 before:inset-y-0 before:my-6 before:bg-gradient-to-b before:from-white/10 before:to-slate-50/10 before:bg-white/50 before:w-[800%] before:-mr-4 before:rounded-[0_1.2rem_1.2rem_0/0_1.7rem_1.7rem_0]",
        ])></div>
        <div @class([
            'h-full col-span-7 2xl:col-span-8 lg:relative',
            "before:content-[''] before:absolute before:lg:-ml-10 before:left-0 before:inset-y-0 before:bg-gradient-to-b before:from-theme-1 before:to-theme-2 before:w-screen before:lg:w-[800%]",
            "after:content-[''] after:absolute after:inset-y-0 after:left-0 after:w-screen after:lg:w-[800%] after:bg-texture-white after:bg-fixed after:bg-center after:lg:bg-[25rem_-25rem] after:bg-no-repeat",
        ])>
            <div class="sticky top-0 z-10 flex-col justify-center hidden h-screen ml-16 lg:flex xl:ml-28 2xl:ml-36">
                <div class="text-[2.6rem] font-medium leading-[1.4] text-white xl:text-5xl xl:leading-[1.2]">
                    Welcome to EF Services
                </div>
                <div class="mt-5 text-base leading-relaxed text-white/70 xl:text-lg">
                    Our dedicated team is committed to guiding you at every turn. We go above and beyond to ensure
                    complete customer satisfaction, delivering tailored transport solutions designed to keep you moving
                    forward.
                </div>
                <div class="flex flex-col gap-3 mt-10 xl:flex-row xl:items-center">
                    {{-- <div class="flex items-center">
                        <div class="image-fit zoom-in h-9 w-9 2xl:h-11 2xl:w-11">
                            <x-base.tippy class="rounded-full border-[3px] border-white/50"
                                src="{{ Vite::asset($users[0]['photo']) }}"
                                alt="Tailwise - Admin Dashboard Template" as="img"
                                content="{{ $users[0]['name'] }}" />
                        </div>
                        <div class="-ml-3 image-fit zoom-in h-9 w-9 2xl:h-11 2xl:w-11">
                            <x-base.tippy class="rounded-full border-[3px] border-white/50"
                                src="{{ Vite::asset($users[1]['photo']) }}"
                                alt="Tailwise - Admin Dashboard Template" as="img"
                                content="{{ $users[1]['name'] }}" />
                        </div>
                        <div class="-ml-3 image-fit zoom-in h-9 w-9 2xl:h-11 2xl:w-11">
                            <x-base.tippy class="rounded-full border-[3px] border-white/50"
                                src="{{ Vite::asset($users[2]['photo']) }}"
                                alt="Tailwise - Admin Dashboard Template" as="img"
                                content="{{ $users[2]['name'] }}" />
                        </div>
                        <div class="-ml-3 image-fit zoom-in h-9 w-9 2xl:h-11 2xl:w-11">
                            <x-base.tippy class="rounded-full border-[3px] border-white/50"
                                src="{{ Vite::asset($users[3]['photo']) }}"
                                alt="Tailwise - Admin Dashboard Template" as="img"
                                content="{{ $users[3]['name'] }}" />
                        </div>
                    </div> --}}
                    <div class="text-base text-white/70 xl:ml-2 2xl:ml-3">
                        Log in now and experience the difference that passion, reliability, and innovation can bring to
                        your operations.
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- Terms Modal -->
<div id="termsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[80vh] overflow-hidden">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-semibold text-slate-800">Terms of Service - EF Services</h3>
            <button type="button" onclick="closeTermsModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            <div class="prose prose-sm max-w-none">
                <h4 class="font-semibold mb-3">1. Acceptance of Terms</h4>
                <p class="mb-4">By accessing and using EF Services, you accept and agree to be bound by the terms and provision of this agreement.</p>
                
                <h4 class="font-semibold mb-3">2. Services Description</h4>
                <p class="mb-4">EF Services provides comprehensive transportation management solutions including carrier registration, driver management, vehicle tracking, and compliance monitoring.</p>
                
                <h4 class="font-semibold mb-3">3. User Responsibilities</h4>
                <p class="mb-4">Users are responsible for maintaining the confidentiality of their account information and for all activities that occur under their account.</p>
                
                <h4 class="font-semibold mb-3">4. Payment Terms</h4>
                <p class="mb-4">Membership fees are charged according to the selected plan. All payments are processed securely and are non-refundable unless otherwise specified.</p>
                
                <h4 class="font-semibold mb-3">5. Data Protection</h4>
                <p class="mb-4">We are committed to protecting your personal information and comply with all applicable data protection regulations.</p>
                
                <h4 class="font-semibold mb-3">6. Limitation of Liability</h4>
                <p class="mb-4">EF Services shall not be liable for any indirect, incidental, special, consequential, or punitive damages.</p>
                
                <h4 class="font-semibold mb-3">7. Modifications</h4>
                <p class="mb-4">We reserve the right to modify these terms at any time. Users will be notified of significant changes.</p>
            </div>
        </div>
        <div class="p-6 border-t bg-slate-50">
            <button type="button" onclick="closeTermsModal()" class="w-full bg-primary text-white py-2 px-4 rounded-lg hover:bg-primary/90 transition-colors">
                I Understand
            </button>
        </div>
    </div>
</div>

<!-- Privacy Policy Modal -->
<div id="privacyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[80vh] overflow-hidden">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-xl font-semibold text-slate-800">Privacy Policy - EF Services</h3>
            <button type="button" onclick="closePrivacyModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            <div class="prose prose-sm max-w-none">
                <h4 class="font-semibold mb-3">Information We Collect</h4>
                <p class="mb-4">We collect information you provide directly to us, such as when you create an account, use our services, or contact us for support.</p>
                
                <h4 class="font-semibold mb-3">How We Use Your Information</h4>
                <p class="mb-4">We use the information we collect to provide, maintain, and improve our services, process transactions, and communicate with you.</p>
                
                <h4 class="font-semibold mb-3">Information Sharing</h4>
                <p class="mb-4">We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except as described in this policy.</p>
                
                <h4 class="font-semibold mb-3">Data Security</h4>
                <p class="mb-4">We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
                
                <h4 class="font-semibold mb-3">Cookies and Tracking</h4>
                <p class="mb-4">We use cookies and similar technologies to enhance your experience and analyze usage patterns on our platform.</p>
                
                <h4 class="font-semibold mb-3">Your Rights</h4>
                <p class="mb-4">You have the right to access, update, or delete your personal information. Contact us if you wish to exercise these rights.</p>
                
                <h4 class="font-semibold mb-3">Contact Information</h4>
                <p class="mb-4">If you have questions about this Privacy Policy, please contact us at privacy@efservices.la</p>
            </div>
        </div>
        <div class="p-6 border-t bg-slate-50">
            <button type="button" onclick="closePrivacyModal()" class="w-full bg-primary text-white py-2 px-4 rounded-lg hover:bg-primary/90 transition-colors">
                I Understand
            </button>
        </div>
    </div>
</div>

</x-guest-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submit-btn');
    
    function updateButtonState() {
        const selectedPlan = document.querySelector('input[name="membership_id"]:checked');
        const termsAccepted = document.querySelector('input[name="terms_accepted"]:checked');
        
        // Enable/disable submit button - only require plan selection and terms acceptance
        if (selectedPlan && termsAccepted) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }
    
    // Add event listeners
    document.querySelectorAll('input[name="membership_id"]').forEach(input => {
        input.addEventListener('change', updateButtonState);
    });
    
    document.querySelector('input[name="terms_accepted"]').addEventListener('change', updateButtonState);
    
    // Initial state check
    updateButtonState();
});

// Modal functions
function openTermsModal() {
    document.getElementById('termsModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeTermsModal() {
    document.getElementById('termsModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openPrivacyModal() {
    document.getElementById('privacyModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closePrivacyModal() {
    document.getElementById('privacyModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modals when clicking outside
document.getElementById('termsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTermsModal();
    }
});

document.getElementById('privacyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePrivacyModal();
    }
});
</script>