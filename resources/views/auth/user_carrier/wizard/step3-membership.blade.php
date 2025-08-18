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
                                <div class="membership-option border-2 border-slate-200 rounded-lg p-3 sm:p-4 cursor-pointer transition-all duration-200 hover:border-primary/50" 
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

                        <!-- Document Upload Question -->
                        <div class="mb-4 sm:mb-6">
                            <x-base.form-label class="text-sm sm:text-base font-medium mb-2 sm:mb-3">Do you have your required documents ready to upload?</x-base.form-label>
                            <div class="space-y-2">
                                <label class="flex items-start sm:items-center cursor-pointer">
                                    <input type="radio" name="has_documents" value="yes" class="mr-2 sm:mr-3 mt-0.5 sm:mt-0 text-primary focus:ring-primary" {{ old('has_documents') === 'yes' ? 'checked' : '' }}>
                                    <span class="text-sm sm:text-base text-slate-700">Yes, I have all required documents ready</span>
                                </label>
                                <label class="flex items-start sm:items-center cursor-pointer">
                                    <input type="radio" name="has_documents" value="no" class="mr-2 sm:mr-3 mt-0.5 sm:mt-0 text-primary focus:ring-primary" {{ old('has_documents') === 'no' ? 'checked' : '' }}>
                                    <span class="text-sm sm:text-base text-slate-700">No, I'll upload them later</span>
                                </label>
                            </div>
                            <div class="text-red-500 text-sm mt-1 hidden" id="has_documents-error"></div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-4 sm:mb-6">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" name="terms_accepted" value="1" class="mt-0.5 sm:mt-1 mr-2 sm:mr-3 text-primary focus:ring-primary" {{ old('terms_accepted') ? 'checked' : '' }} required>
                                <span class="text-xs sm:text-sm text-slate-700">
                                    I agree to the 
                                    <a href="#" class="text-primary hover:underline" target="_blank">Terms of Service</a> 
                                    and 
                                    <a href="#" class="text-primary hover:underline" target="_blank">Privacy Policy</a>
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
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('membership-form');
            const membershipOptions = document.querySelectorAll('.membership-option');
            const submitBtn = document.getElementById('submit-btn');

            // Handle membership option clicks
            membershipOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                    updateSelectedOption();
                });
            });

            // Handle radio button changes
            document.querySelectorAll('input[name="membership_id"]').forEach(radio => {
                radio.addEventListener('change', updateSelectedOption);
            });

            function updateSelectedOption() {
                membershipOptions.forEach(option => {
                    const radio = option.querySelector('input[type="radio"]');
                    if (radio.checked) {
                        option.classList.add('border-primary', 'bg-primary/5');
                        option.classList.remove('border-slate-200');
                    } else {
                        option.classList.remove('border-primary', 'bg-primary/5');
                        option.classList.add('border-slate-200');
                    }
                });
            }

            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;

                // Check if membership is selected
                const membershipSelected = document.querySelector('input[name="membership_id"]:checked');
                if (!membershipSelected) {
                    isValid = false;
                    alert('Please select a membership plan.');
                }

                // Check if document question is answered
                const documentsAnswered = document.querySelector('input[name="has_documents"]:checked');
                if (!documentsAnswered) {
                    isValid = false;
                    showFieldError('has_documents', 'Please indicate if you have documents ready.');
                }

                // Check if terms are accepted
                const termsAccepted = document.querySelector('input[name="terms_accepted"]:checked');
                if (!termsAccepted) {
                    isValid = false;
                    showFieldError('terms_accepted', 'Please accept the terms and conditions.');
                }

                if (!isValid) {
                    e.preventDefault();
                } else {
                    showLoadingState();
                }
            });

            function showFieldError(fieldName, message) {
                const errorDiv = document.getElementById(`${fieldName}-error`);
                if (errorDiv) {
                    errorDiv.textContent = message;
                    errorDiv.classList.remove('hidden');
                }
            }

            function showLoadingState() {
                const submitText = document.getElementById('submit-text');
                const loadingSpinner = document.getElementById('loading-spinner');
                
                submitBtn.disabled = true;
                submitText.classList.add('hidden');
                loadingSpinner.classList.remove('hidden');
            }

            // Initialize selected option on page load
            updateSelectedOption();
        });
    </script>
</x-guest-layout>