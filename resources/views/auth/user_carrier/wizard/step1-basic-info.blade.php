<x-guest-layout>
    <div
        class="container grid grid-cols-12 px-5 py-10 sm:px-10 sm:py-14 md:px-36 lg:h-screen lg:max-w-[1550px] lg:py-0 lg:pl-14 lg:pr-12 xl:px-24 2xl:max-w-[1750px]">
        <div @class([
            'relative z-50 h-full col-span-12 p-7 sm:p-14 bg-white rounded-2xl lg:bg-transparent lg:pr-10 lg:col-span-5 xl:pr-24 2xl:col-span-4 lg:p-0',
            "before:content-[''] before:absolute before:inset-0 before:-mb-3.5 before:bg-white/40 before:rounded-2xl before:mx-5",
        ])>
            <div class="relative z-10 flex flex-col justify-center w-full h-full py-2 lg:py-32">
                <!-- Logo -->
                <div
                    class="flex h-[55px] w-[55px] items-center justify-center rounded-[0.8rem] border border-primary/30">
                    <div
                        class="relative flex h-[50px] w-[50px] items-center justify-center rounded-[0.6rem] bg-white bg-gradient-to-b from-theme-1/90 to-theme-2/90">
                        <div class="relative h-[26px] w-[26px] -rotate-45 [&_div]:bg-white">
                            <div class="absolute inset-y-0 left-0 my-auto h-[75%] w-[20%] rounded-full opacity-50"></div>
                            <div class="absolute inset-0 m-auto h-[120%] w-[20%] rounded-full"></div>
                            <div class="absolute inset-y-0 right-0 my-auto h-[75%] w-[20%] rounded-full opacity-50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Stepper -->
                <div class="mt-6 sm:mt-8">
                    <x-progress-stepper :steps="[
                        ['label' => 'Basic Info', 'description' => 'Personal details'],
                        ['label' => 'Company', 'description' => 'Business information'],
                        ['label' => 'Membership', 'description' => 'Select plan'],
                        ['label' => 'Banking Info', 'description' => 'Payment details']
                    ]" :current-step="1" :completed-steps="[]" size="sm"
                        class="mb-4 sm:mb-6" />
                </div>

                <!-- Header -->
                <div class="mt-4 sm:mt-6">
                    <div class="text-xl sm:text-2xl font-medium">Create Your Account</div>
                    <div class="mt-2.5 text-sm sm:text-base text-slate-600">
                        Already have an account?
                        <a class="font-medium text-primary" href="{{ route('login') }}">
                            Sign In
                        </a>
                    </div>
                </div>

                <!-- Form -->
                <div class="mt-4 sm:mt-6">
                    <form method="POST" action="{{ route('carrier.wizard.step1.process') }}" id="basic-info-form">
                        @csrf

                        <!-- Error Messages -->
                        @if ($errors->any())
                            <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"></path>
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

                        <!-- Full Name -->
                        <div class="mb-4 sm:mb-5">
                            <x-base.form-label for="full_name">Full Name*</x-base.form-label>
                            <x-base.form-input
                                class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                type="text" placeholder="John Doe" name="full_name" id="full_name"
                                value="{{ old('full_name') }}" required />
                            <div class="text-red-500 text-sm mt-1 hidden" id="full_name-error"></div>
                        </div>

                        <!-- Email -->
                        <div class="mb-4 sm:mb-5">
                            <x-base.form-label for="email">Email Address*</x-base.form-label>
                            <x-base.form-input
                                class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                type="email" placeholder="john@company.com" name="email" id="email"
                                value="{{ old('email') }}" required />
                            <div class="text-red-500 text-sm mt-1 hidden" id="email-error"></div>
                        </div>

                        <!-- Phone -->
                        <div class="mb-4 sm:mb-5">
                            <x-base.form-label for="phone">Phone Number</x-base.form-label>
                            <div class="flex gap-2">
                                <div class="w-[80px] sm:w-[90px]">
                                    <select id="country-code" name="country_code"
                                        class="block w-full rounded-[0.6rem] border-slate-300/80 px-2 sm:px-3 py-2.5 sm:py-3.5 text-gray-700 focus:ring-2 focus:ring-primary/20 focus:border-primary text-xs sm:text-sm">
                                        <option value="US"
                                            {{ old('country_code', 'US') === 'US' ? 'selected' : '' }}>+1 (US)</option>
                                        <option value="MX" {{ old('country_code') === 'MX' ? 'selected' : '' }}>+52
                                            (MX)</option>
                                        <option value="CA" {{ old('country_code') === 'CA' ? 'selected' : '' }}>+1
                                            (CA)</option>
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <x-base.form-input
                                        class="block w-full rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                        type="tel" placeholder="(123) 456-7890" name="phone" id="phone"
                                        value="{{ old('phone') }}" autocomplete="tel" />
                                </div>
                            </div>
                            <div class="text-red-500 text-sm mt-1 hidden" id="phone-error"></div>
                        </div>

                        <!-- Job Position -->
                        <div class="mb-4 sm:mb-5">
                            <x-base.form-label for="job_position">Job Position*</x-base.form-label>
                            <select name="job_position" id="job_position" required
                                class="block w-full rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base">
                                <option value="">Select your position</option>
                                <option value="Owner" {{ old('job_position') === 'Owner' ? 'selected' : '' }}>Owner</option>
                                <option value="Manager" {{ old('job_position') === 'Manager' ? 'selected' : '' }}>Manager</option>
                                <option value="Dispatcher" {{ old('job_position') === 'Dispatcher' ? 'selected' : '' }}>Dispatcher</option>
                                <option value="Safety Manager" {{ old('job_position') === 'Safety Manager' ? 'selected' : '' }}>Safety Manager</option>
                                <option value="Operations Manager" {{ old('job_position') === 'Operations Manager' ? 'selected' : '' }}>Operations Manager</option>
                                <option value="Other" {{ old('job_position') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <div class="text-red-500 text-sm mt-1 hidden" id="job_position-error"></div>
                        </div>

                        <!-- Password -->
                        <div class="mb-4 sm:mb-5">
                            <x-base.form-label for="password">Password*</x-base.form-label>
                            <div class="relative">
                                <x-base.form-input
                                    class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 pr-10 sm:pr-12 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                    type="password" name="password" id="password" placeholder="••••••••••••"
                                    required />
                                <button type="button" id="toggle-password"
                                    class="absolute inset-y-0 right-0 flex items-center pr-2 sm:pr-3 text-gray-500 hover:text-gray-700 transition-colors p-1"
                                    aria-label="Toggle password visibility">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-icon" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-off-icon hidden"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <!-- Password Strength Indicator -->
                            <div class="mt-2">
                                <div class="flex gap-1 mb-2">
                                    <div class="h-1 flex-1 rounded-full bg-slate-200 password-strength-bar"
                                        data-strength="1"></div>
                                    <div class="h-1 flex-1 rounded-full bg-slate-200 password-strength-bar"
                                        data-strength="2"></div>
                                    <div class="h-1 flex-1 rounded-full bg-slate-200 password-strength-bar"
                                        data-strength="3"></div>
                                    <div class="h-1 flex-1 rounded-full bg-slate-200 password-strength-bar"
                                        data-strength="4"></div>
                                </div>
                                <div class="text-xs text-slate-500" id="password-strength-text">Password must contain at least 8 characters</div>
                            </div>
                            <div class="text-red-500 text-sm mt-1 hidden" id="password-error"></div>
                        </div>

                        <!-- Password Confirmation -->
                        <div class="mb-4 sm:mb-5">
                            <x-base.form-label for="password_confirmation">Confirm Password*</x-base.form-label>
                            <div class="relative">
                                <x-base.form-input
                                    class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 pr-10 sm:pr-12 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                    type="password" name="password_confirmation" id="password_confirmation"
                                    placeholder="••••••••••••" required />
                                <button type="button" id="toggle-password-confirmation"
                                    class="absolute inset-y-0 right-0 flex items-center pr-2 sm:pr-3 text-gray-500 hover:text-gray-700 transition-colors p-1"
                                    aria-label="Toggle password confirmation visibility">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-icon" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-off-icon hidden"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <div class="text-red-500 text-sm mt-1 hidden" id="password_confirmation-error"></div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-4 sm:mb-5">
                            <div class="flex items-start">
                                <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1" 
                                    {{ old('terms_accepted') ? 'checked' : '' }} required
                                    class="mt-1 h-4 w-4 text-primary focus:ring-primary border-slate-300 rounded">
                                <label for="terms_accepted" class="ml-2 text-sm text-slate-600">
                                    I accept the <a href="#" class="text-primary hover:underline">Terms and Conditions</a> 
                                    and <a href="#" class="text-primary hover:underline">Privacy Policy</a>*
                                </label>
                            </div>
                            <div class="text-red-500 text-sm mt-1 hidden" id="terms_accepted-error"></div>
                        </div>

                        <!-- Marketing Consent -->
                        <div class="mb-5 sm:mb-6">
                            <div class="flex items-start">
                                <input type="checkbox" name="marketing_consent" id="marketing_consent" value="1" 
                                    {{ old('marketing_consent') ? 'checked' : '' }}
                                    class="mt-1 h-4 w-4 text-primary focus:ring-primary border-slate-300 rounded">
                                <label for="marketing_consent" class="ml-2 text-sm text-slate-600">
                                    I would like to receive marketing communications and updates about EF Services
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center xl:text-left pt-2 sm:pt-4">
                            <x-base.button type="submit"
                                class="w-full bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-2.5 sm:py-3.5 text-white font-medium transition-all duration-200 hover:from-theme-1 hover:to-theme-2 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
                                variant="primary" rounded id="submit-btn">
                                <span class="flex items-center justify-center">
                                    <span id="submit-text">Continue to Company Info</span>
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                    <div class="hidden" id="loading-spinner">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
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


    <!-- Scripts -->
    <script src="https://unpkg.com/imask"></script>
    <script>
        class FormValidator {
            constructor() {
                this.phoneMask = null;
                this.emailTimeout = null;
                this.init();
            }

            init() {
                this.initializePhoneMask();
                this.setupPasswordToggles();
                this.setupPasswordStrengthChecker();
                this.setupRealTimeValidation();
                this.setupEmailValidation();
            }

            initializePhoneMask() {
                const phoneInput = document.getElementById('phone');
                if (!phoneInput || typeof IMask === 'undefined') {
                    console.warn('Phone input not found or IMask not loaded');
                    return;
                }

                this.phoneMask = IMask(phoneInput, {
                    mask: '(000) 000-0000',
                    lazy: false,
                    placeholderChar: '_'
                });

                this.setupCountryCodeHandler();
            }

            setupCountryCodeHandler() {
                const countryCodeElement = document.getElementById('country-code');
                if (!countryCodeElement) return;

                const phoneMasks = {
                    '+52': '(00) 0000-0000', // Mexico
                    '+44': '00 0000 0000', // UK
                    '+34': '000 000 000', // Spain
                    '+33': '0 00 00 00 00', // France
                    '+49': '000 0000000', // Germany
                    '+1': '(000) 000-0000' // US/Canada (default)
                };

                countryCodeElement.addEventListener('change', (e) => {
                    const countryCode = e.target.value;
                    const mask = phoneMasks[countryCode] || phoneMasks['+1'];
                    this.phoneMask?.updateOptions({
                        mask
                    });
                });
            }

            setupPasswordToggles() {
                this.setupPasswordToggle('toggle-password', 'password');
                this.setupPasswordToggle('toggle-password-confirmation', 'password_confirmation');
            }

            setupPasswordToggle(toggleId, passwordId) {
                const toggleButton = document.getElementById(toggleId);
                const passwordInput = document.getElementById(passwordId);

                if (!toggleButton || !passwordInput) return;

                const eyeIcon = toggleButton.querySelector('.eye-icon');
                const eyeOffIcon = toggleButton.querySelector('.eye-off-icon');

                toggleButton.addEventListener('click', () => {
                    const isPassword = passwordInput.type === 'password';

                    passwordInput.type = isPassword ? 'text' : 'password';
                    eyeIcon?.classList.toggle('hidden', isPassword);
                    eyeOffIcon?.classList.toggle('hidden', !isPassword);
                });
            }

            setupPasswordStrengthChecker() {
                const passwordInput = document.getElementById('password');
                if (!passwordInput) return;

                passwordInput.addEventListener('input', (e) => {
                    const strengthData = this.calculatePasswordStrength(e.target.value);
                    this.updatePasswordStrength(strengthData);
                });
            }

            calculatePasswordStrength(password) {
                // Simplified validation - only check length
                const isValid = password.length >= 8;
                
                return {
                    strength: isValid ? 4 : (password.length > 0 ? 1 : 0),
                    hasNumber: true, // Always true for simplified validation
                    hasSymbol: true  // Always true for simplified validation
                };
            }

            updatePasswordStrength(strengthData) {
                const strengthBars = document.querySelectorAll('.password-strength-bar');
                const strengthText = document.getElementById('password-strength-text');

                if (!strengthBars.length || !strengthText) return;

                const strength = strengthData.strength || strengthData;
                const config = {
                    colors: ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'],
                    texts: ['Very Weak', 'Weak', 'Fair', 'Strong'],
                    textColors: ['text-red-600', 'text-orange-600', 'text-yellow-600', 'text-green-600']
                };

                strengthBars.forEach((bar, index) => {
                    bar.className = 'h-1 flex-1 rounded-full password-strength-bar';
                    bar.classList.add(
                        index < strength ? config.colors[strength - 1] : 'bg-slate-200'
                    );
                });

                if (strength > 0) {
                    strengthText.textContent = config.texts[strength - 1];
                    strengthText.className = `text-xs ${config.textColors[strength - 1]}`;
                } else {
                    strengthText.textContent = 'Enter a password to see strength';
                    strengthText.className = 'text-xs text-slate-500';
                }
            }

            setupEmailValidation() {
                const emailInput = document.getElementById('email');
                const emailError = document.getElementById('email-error');
                
                if (!emailInput || !emailError) {
                    console.warn('Email input or error element not found');
                    return;
                }

                emailInput.addEventListener('input', (e) => {
                    const email = e.target.value.trim();

                    clearTimeout(this.emailTimeout);

                    if (!email) {
                        this.clearFieldAjaxError(emailError);
                        return;
                    }

                    if (!this.isValidEmail(email)) {
                        this.showFieldAjaxError(emailError, 'Please enter a valid email address');
                        return;
                    }

                    this.emailTimeout = setTimeout(() => {
                        this.checkFieldUniqueness('email', email, emailError);
                    }, 500);
                });
            }

            setupRealTimeValidation() {
                const form = document.getElementById('basic-info-form');
                if (!form) return;

                const inputs = form.querySelectorAll('input[required], input[type="email"], input[type="checkbox"][required]');

                inputs.forEach(input => {
                    if (input.type === 'checkbox') {
                        input.addEventListener('change', () => this.validateField(input));
                    } else {
                        input.addEventListener('blur', () => this.validateField(input));
                        input.addEventListener('input', () => this.clearFieldError(input));
                    }
                });

                form.addEventListener('submit', (e) => {
                    let isValid = true;

                    inputs.forEach(input => {
                        if (!this.validateField(input)) {
                            isValid = false;
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        return;
                    }

                    this.showLoadingState();
                });
            }

            validateField(field) {
                const value = field.type === 'checkbox' ? field.checked : field.value.trim();
                const fieldName = field.name;
                const validationResult = this.getFieldValidation(field, value, fieldName);

                this.showFieldError(field, validationResult.isValid, validationResult.errorMessage);
                return validationResult.isValid;
            }

            getFieldValidation(field, value, fieldName) {
                // Required field validation
                if (field.hasAttribute('required') && !value) {
                    const errorMessage = fieldName === 'terms_accepted' 
                        ? 'You must accept the terms and conditions to continue.'
                        : `${this.getFieldLabel(fieldName)} is required.`;
                    return {
                        isValid: false,
                        errorMessage: errorMessage
                    };
                }

                // Email validation
                if (field.type === 'email' && value && !this.isValidEmail(value)) {
                    return {
                        isValid: false,
                        errorMessage: 'Please enter a valid email address.'
                    };
                }

                // Password validation - simplified to only check length
                if (fieldName === 'password' && value) {
                    if (value.length < 8) {
                        return {
                            isValid: false,
                            errorMessage: 'Password must be at least 8 characters long.'
                        };
                    }
                }

                // Password confirmation validation
                if (fieldName === 'password_confirmation' && value) {
                    const password = document.getElementById('password')?.value;
                    if (value !== password) {
                        return {
                            isValid: false,
                            errorMessage: 'Password confirmation does not match.'
                        };
                    }
                }

                return {
                    isValid: true,
                    errorMessage: ''
                };
            }

            showFieldError(field, isValid, errorMessage) {
                const errorDiv = document.getElementById(`${field.name}-error`);
                if (!errorDiv) return;

                if (isValid) {
                    errorDiv.classList.add('hidden');
                    field.classList.remove('border-red-500');
                    field.classList.add('border-slate-300/80');
                } else {
                    errorDiv.textContent = errorMessage;
                    errorDiv.classList.remove('hidden');
                    field.classList.add('border-red-500');
                    field.classList.remove('border-slate-300/80');
                }
            }

            clearFieldError(field) {
                const errorDiv = document.getElementById(`${field.name}-error`);
                if (!errorDiv || errorDiv.classList.contains('hidden')) return;

                errorDiv.classList.add('hidden');
                field.classList.remove('border-red-500');
                field.classList.add('border-slate-300/80');
            }

            getFieldLabel(fieldName) {
                const labels = {
                    full_name: 'Full Name',
                    email: 'Email Address',
                    phone: 'Phone Number',
                    job_position: 'Job Position',
                    password: 'Password',
                    password_confirmation: 'Password Confirmation',
                    terms_accepted: 'Terms and Conditions'
                };
                return labels[fieldName] || fieldName.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            }

            isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            async checkFieldUniqueness(field, value, errorElement) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (!csrfToken) {
                    console.error('CSRF token not found');
                    return;
                }

                try {
                    this.showFieldLoading(errorElement);

                    const response = await fetch(window.routes?.checkUniqueness || '/carrier/wizard/check-uniqueness', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            field,
                            value
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();

                    if (data.available) {
                        this.showFieldSuccess(errorElement, data.message);
                    } else {
                        this.showFieldAjaxError(errorElement, data.message);
                    }
                } catch (error) {
                    console.error('Error checking field uniqueness:', error);
                    this.showFieldAjaxError(errorElement, 'Error validating field. Please try again.');
                }
            }

            showFieldAjaxError(element, message) {
                if (!element) return;
                element.textContent = message;
                element.className = 'text-red-600 text-sm mt-1';
                element.style.display = 'block';
            }

            showFieldSuccess(element, message) {
                if (!element) return;
                element.textContent = message;
                element.className = 'text-green-600 text-sm mt-1';
                element.style.display = 'block';
            }

            showFieldLoading(element) {
                if (!element) return;
                element.textContent = 'Checking availability...';
                element.className = 'text-blue-600 text-sm mt-1';
                element.style.display = 'block';
            }

            clearFieldAjaxError(element) {
                if (!element) return;
                element.style.display = 'none';
                element.textContent = '';
            }

            showLoadingState() {
                const submitBtn = document.getElementById('submit-btn');
                const submitText = document.getElementById('submit-text');
                const loadingSpinner = document.getElementById('loading-spinner');

                if (submitBtn && submitText && loadingSpinner) {
                    submitBtn.disabled = true;
                    submitText.classList.add('hidden');
                    loadingSpinner.classList.remove('hidden');
                }
            }
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                new FormValidator();
            });
        } else {
            // DOM is already loaded
            new FormValidator();
        }
    </script>
</x-guest-layout>
