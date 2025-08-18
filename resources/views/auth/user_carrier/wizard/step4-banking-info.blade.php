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
                        :current-step="4"
                        :completed-steps="[1, 2, 3]"
                        size="sm"
                        class="mb-4 sm:mb-6"
                    />
                </div>

                <!-- Header -->
                <div class="mt-4 sm:mt-6">
                    <div class="text-xl sm:text-2xl font-medium">Banking Information</div>
                    <div class="mt-2 sm:mt-2.5 text-sm sm:text-base text-slate-600">
                        Provide your banking details for secure payment processing
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <div class="text-sm font-medium text-blue-800">Secure & Encrypted</div>
                            <div class="text-xs text-blue-700 mt-1">Your banking information is encrypted and securely stored. This data is only accessible to authorized administrators for verification purposes.</div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="mt-4 sm:mt-6">
                    <form method="POST" action="{{ route('carrier.wizard.step4.process') }}" id="banking-form">
                        @csrf

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

                        <!-- Country Code (Hidden for US) -->
                        <input type="hidden" name="country_code" value="US">

                        <!-- Account Number -->
                        <div class="mb-4 sm:mb-6">
                            <x-base.form-label for="account_number" class="text-sm sm:text-base font-medium mb-2">
                                Bank Account Number <span class="text-red-500">*</span>
                            </x-base.form-label>
                            <x-base.form-input
                                id="account_number"
                                name="account_number"
                                type="text"
                                placeholder="Enter your bank account number"
                                value="{{ old('account_number', $bankingDetails->account_number ?? '') }}"
                                class="w-full text-sm sm:text-base py-2.5 sm:py-3"
                                maxlength="17"
                                required
                            />
                            <div class="text-xs text-slate-500 mt-1">US bank account numbers are typically 8-17 digits</div>
                            @error('account_number')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Account Holder Name -->
                        <div class="mb-4 sm:mb-6">
                            <x-base.form-label for="account_holder_name" class="text-sm sm:text-base font-medium mb-2">
                                Account Holder Name <span class="text-red-500">*</span>
                            </x-base.form-label>
                            <x-base.form-input
                                id="account_holder_name"
                                name="account_holder_name"
                                type="text"
                                placeholder="Enter the full name on the bank account"
                                value="{{ old('account_holder_name', $bankingDetails->account_holder_name ?? '') }}"
                                class="w-full text-sm sm:text-base py-2.5 sm:py-3"
                                maxlength="100"
                                required
                            />
                            <div class="text-xs text-slate-500 mt-1">Enter the exact name as it appears on your bank account</div>
                            @error('account_holder_name')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Important Notice -->
                        <div class="mb-4 sm:mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-yellow-800">Account Validation Required</div>
                                    <div class="text-xs text-yellow-700 mt-1">
                                        After submitting your banking information, your account will be set to "Pending Validation" status. 
                                        Our administrators will review and verify your information before activating your account.
                                    </div>
                                </div>
                            </div>
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
                                    Complete Registration
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 ml-1.5 sm:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </span>
                            </x-base.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side Image -->
        <div @class([
            'relative h-full col-span-12 lg:col-span-7 2xl:col-span-8',
            "before:content-[''] before:absolute before:inset-0 before:rounded-2xl before:bg-black/20 before:z-10",
        ])>
            <div class="relative z-10 flex flex-col justify-center w-full h-full p-7 sm:p-14 lg:p-28">
                <div class="text-white">
                    <div class="text-2xl sm:text-3xl lg:text-5xl font-medium leading-tight">
                        Secure Banking
                    </div>
                    <div class="text-2xl sm:text-3xl lg:text-5xl font-medium leading-tight mt-2">
                        Integration
                    </div>
                    <div class="mt-4 sm:mt-6 text-sm sm:text-base lg:text-lg text-white/80 leading-relaxed">
                        Your financial information is protected with enterprise-grade encryption. 
                        We ensure the highest security standards for all banking data.
                    </div>
                </div>
            </div>
            <div class="absolute inset-0 bg-gradient-to-b from-theme-1 to-theme-2 rounded-2xl"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('banking-form');
            const submitBtn = document.getElementById('submit-btn');
            const accountNumberInput = document.getElementById('account_number');
            const accountHolderInput = document.getElementById('account_holder_name');

            // Format account number input (numbers only)
            accountNumberInput.addEventListener('input', function(e) {
                // Remove any non-numeric characters
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });

            // Format account holder name (letters, spaces, hyphens, periods only)
            accountHolderInput.addEventListener('input', function(e) {
                // Allow only letters, spaces, hyphens, and periods
                e.target.value = e.target.value.replace(/[^a-zA-Z\s\-\.]/g, '');
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <span class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                `;
            });
        });
    </script>
</x-guest-layout>