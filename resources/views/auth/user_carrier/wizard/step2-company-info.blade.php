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
                        :current-step="2"
                        :completed-steps="[1]"
                        size="sm"
                        class="mb-4 sm:mb-6"
                    />
                </div>

                <!-- Header -->
                <div class="mt-4 sm:mt-6">
                    <div class="text-xl sm:text-2xl font-medium">Company Information</div>
                    <div class="mt-2 sm:mt-2.5 text-sm sm:text-base text-slate-600">
                        Tell us about your transportation business
                    </div>
                </div>

                <!-- Form -->
                <div class="mt-4 sm:mt-6">
                    <form method="POST" action="{{ route('carrier.wizard.step2.process') }}" id="company-info-form">
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

                        <!-- Company Name -->
                        <div class="mb-4 sm:mb-5">
                            <x-base.form-label for="carrier_name">Company Name*</x-base.form-label>
                            <x-base.form-input 
                                class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                type="text" 
                                placeholder="ABC Transportation LLC" 
                                name="carrier_name" 
                                id="carrier_name"
                                value="{{ old('carrier_name') }}" 
                                autocomplete="organization"
                                required
                            />
                            <div class="text-red-500 text-sm mt-1 hidden" id="carrier_name-error"></div>
                        </div>

                        <!-- Address -->
                        <div class="mb-4 sm:mb-5">
                            <x-base.form-label for="address">Business Address*</x-base.form-label>
                            <x-base.form-input 
                                class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                type="text" 
                                placeholder="123 Main Street, Suite 100" 
                                name="address" 
                                id="address"
                                value="{{ old('address') }}" 
                                autocomplete="street-address"
                                required
                            />
                            <div class="text-red-500 text-sm mt-1 hidden" id="address-error"></div>
                        </div>

                        <!-- State and Zip Code -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-5">
                            <div>
                                <x-base.form-label for="state">State*</x-base.form-label>
                                <x-base.form-select 
                                    class="block w-full rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                    name="state" 
                                    id="state" 
                                    autocomplete="address-level1"
                                    required
                                >
                                    <option value="">Select State</option>
                                    @foreach($states as $code => $name)
                                        <option value="{{ $code }}" {{ old('state') === $code ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </x-base.form-select>
                                <div class="text-red-500 text-sm mt-1 hidden" id="state-error"></div>
                            </div>
                            <div>
                                <x-base.form-label for="zip_code">Zip Code*</x-base.form-label>
                                <x-base.form-input 
                                    class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                    type="text" 
                                    placeholder="12345" 
                                    name="zip_code" 
                                    id="zip_code"
                                    value="{{ old('zip_code') }}" 
                                    autocomplete="postal-code"
                                    required
                                />
                                <div class="text-red-500 text-sm mt-1 hidden" id="zip_code-error"></div>
                            </div>
                        </div>

                        <!-- EIN Number -->
                        <div class="mb-4 sm:mb-5">
                            <x-base.form-label for="ein_number">EIN Number*</x-base.form-label>
                            <x-base.form-input 
                                class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                type="text" 
                                placeholder="12-3456789" 
                                name="ein_number" 
                                id="ein_number"
                                value="{{ old('ein_number') }}" 
                                required
                            />
                            <div class="text-xs text-slate-500 mt-1">Federal Employer Identification Number</div>
                            <div class="text-red-500 text-sm mt-1 hidden" id="ein_number-error"></div>
                        </div>

                        <!-- DOT and MC Numbers -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-5">
                            <div>
                                <x-base.form-label for="dot_number">DOT Number</x-base.form-label>
                                <x-base.form-input 
                                    class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                    type="text" 
                                    placeholder="1234567" 
                                    name="dot_number" 
                                    id="dot_number"
                                    value="{{ old('dot_number') }}"
                                />
                                <div class="text-red-500 text-sm mt-1 hidden" id="dot_number-error"></div>
                            </div>
                            <div>
                                <x-base.form-label for="mc_number">MC Number</x-base.form-label>
                                <x-base.form-input 
                                    class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                    type="text" 
                                    placeholder="MC-123456" 
                                    name="mc_number" 
                                    id="mc_number"
                                    value="{{ old('mc_number') }}"
                                />
                                <div class="text-red-500 text-sm mt-1 hidden" id="mc_number-error"></div>
                            </div>
                        </div>

                        <!-- State DOT and IFTA -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-5">
                            <div>
                                <x-base.form-label for="state_dot_number">State DOT Number</x-base.form-label>
                                <x-base.form-input 
                                    class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                    type="text" 
                                    placeholder="ST123456" 
                                    name="state_dot_number" 
                                    id="state_dot_number"
                                    value="{{ old('state_dot_number') }}"
                                />
                                <div class="text-red-500 text-sm mt-1 hidden" id="state_dot_number-error"></div>
                            </div>
                            <div>
                                <x-base.form-label for="ifta_account_number">IFTA Account Number</x-base.form-label>
                                <x-base.form-input 
                                    class="block rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                    type="text" 
                                    placeholder="IFTA123456" 
                                    name="ifta_account_number" 
                                    id="ifta_account_number"
                                    value="{{ old('ifta_account_number') }}"
                                />
                                <div class="text-red-500 text-sm mt-1 hidden" id="ifta_account_number-error"></div>
                            </div>
                        </div>

                        <!-- Business Details -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-5">
                            <div>
                                <x-base.form-label for="business_type">Business Type</x-base.form-label>
                                <x-base.form-select 
                                    class="block w-full rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                    name="business_type" 
                                    id="business_type"
                                >
                                    <option value="">Select Type</option>
                                    <option value="LLC" {{ old('business_type') === 'LLC' ? 'selected' : '' }}>LLC</option>
                                    <option value="Corporation" {{ old('business_type') === 'Corporation' ? 'selected' : '' }}>Corporation</option>
                                    <option value="Partnership" {{ old('business_type') === 'Partnership' ? 'selected' : '' }}>Partnership</option>
                                    <option value="Sole Proprietorship" {{ old('business_type') === 'Sole Proprietorship' ? 'selected' : '' }}>Sole Proprietorship</option>
                                </x-base.form-select>
                                <div class="text-red-500 text-sm mt-1 hidden" id="business_type-error"></div>
                            </div>
                            <div>
                                <x-base.form-label for="years_in_business">Years in Business</x-base.form-label>
                                <x-base.form-select 
                                    class="block w-full rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                    name="years_in_business" 
                                    id="years_in_business"
                                >
                                    <option value="">Select Range</option>
                                    <option value="0-1" {{ old('years_in_business') === '0-1' ? 'selected' : '' }}>Less than 1 year</option>
                                    <option value="1-3" {{ old('years_in_business') === '1-3' ? 'selected' : '' }}>1-3 years</option>
                                    <option value="3-5" {{ old('years_in_business') === '3-5' ? 'selected' : '' }}>3-5 years</option>
                                    <option value="5-10" {{ old('years_in_business') === '5-10' ? 'selected' : '' }}>5-10 years</option>
                                    <option value="10+" {{ old('years_in_business') === '10+' ? 'selected' : '' }}>10+ years</option>
                                </x-base.form-select>
                                <div class="text-red-500 text-sm mt-1 hidden" id="years_in_business-error"></div>
                            </div>
                        </div>

                        <!-- Fleet Size -->
                        <div class="mb-5 sm:mb-6">
                            <x-base.form-label for="fleet_size">Fleet Size</x-base.form-label>
                            <x-base.form-select 
                                class="block w-full rounded-[0.6rem] border-slate-300/80 px-3 sm:px-4 py-2.5 sm:py-3.5 transition-all duration-200 focus:ring-2 focus:ring-primary/20 focus:border-primary text-sm sm:text-base"
                                name="fleet_size" 
                                id="fleet_size"
                            >
                                <option value="">Select Fleet Size</option>
                                <option value="1-5" {{ old('fleet_size') === '1-5' ? 'selected' : '' }}>1-5 vehicles</option>
                                <option value="6-10" {{ old('fleet_size') === '6-10' ? 'selected' : '' }}>6-10 vehicles</option>
                                <option value="11-25" {{ old('fleet_size') === '11-25' ? 'selected' : '' }}>11-25 vehicles</option>
                                <option value="26-50" {{ old('fleet_size') === '26-50' ? 'selected' : '' }}>26-50 vehicles</option>
                                <option value="50+" {{ old('fleet_size') === '50+' ? 'selected' : '' }}>50+ vehicles</option>
                            </x-base.form-select>
                            <div class="text-red-500 text-sm mt-1 hidden" id="fleet_size-error"></div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <x-base.button
                                type="button"
                                class="flex-1 bg-slate-100 text-slate-700 py-2.5 sm:py-3.5 font-medium transition-all duration-200 hover:bg-slate-200 text-sm sm:text-base"
                                variant="secondary" 
                                rounded
                                onclick="window.history.back()"
                            >
                                <span class="flex items-center justify-center">
                                    <svg class="w-4 sm:w-5 h-4 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                                    </svg>
                                    Back
                                </span>
                            </x-base.button>
                            <x-base.button
                                type="submit"
                                class="flex-1 bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-2.5 sm:py-3.5 text-white font-medium transition-all duration-200 hover:from-theme-1 hover:to-theme-2 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
                                variant="primary" 
                                rounded
                                id="submit-btn"
                            >
                                <span class="flex items-center justify-center">
                                    <span id="submit-text">Continue to Membership</span>
                                    <svg class="w-4 sm:w-5 h-4 sm:h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                    <div class="hidden" id="loading-spinner">
                                        <svg class="animate-spin -ml-1 mr-3 h-4 sm:h-5 w-4 sm:w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
    <script src="https://unpkg.com/imask"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize input masks
            const zipMask = IMask(document.getElementById('zip_code'), {
                mask: '00000',
                lazy: false
            });

            const einMask = IMask(document.getElementById('ein_number'), {
                mask: '00-0000000',
                lazy: false
            });

            const dotMask = IMask(document.getElementById('dot_number'), {
                mask: '0000000',
                lazy: false
            });

            const mcMask = IMask(document.getElementById('mc_number'), {
                mask: 'MC-000000',
                lazy: false
            });

            const stateDotMask = IMask(document.getElementById('state_dot_number'), {
                mask: /^[A-Z]{2}\d{6}$/,
                lazy: false
            });

            const iftaMask = IMask(document.getElementById('ifta_account_number'), {
                mask: 'IFTA000000',
                lazy: false
            });

            // Real-time validation
            setupRealTimeValidation();
            
            // Configurar validación AJAX en tiempo real
            setupAjaxValidation();

            function setupRealTimeValidation() {
                const form = document.getElementById('company-info-form');
                const inputs = form.querySelectorAll('input[required], select[required]');

                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        validateField(this);
                    });

                    input.addEventListener('input', function() {
                        clearFieldError(this);
                    });

                    if (input.tagName === 'SELECT') {
                        input.addEventListener('change', function() {
                            validateField(this);
                        });
                    }
                });

                // Special validation for unique fields
                const uniqueFields = ['carrier_name', 'ein_number', 'dot_number', 'mc_number'];
                uniqueFields.forEach(fieldName => {
                    const field = document.getElementById(fieldName);
                    if (field) {
                        let timeout;
                        field.addEventListener('input', function() {
                            clearTimeout(timeout);
                            timeout = setTimeout(() => {
                                if (this.value.trim()) {
                                    checkUniqueness(this);
                                }
                            }, 1000);
                        });
                    }
                });

                form.addEventListener('submit', function(e) {
                    let isValid = true;
                    inputs.forEach(input => {
                        if (!validateField(input)) {
                            isValid = false;
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                    } else {
                        showLoadingState();
                    }
                });
            }

            function validateField(field) {
                const value = field.value.trim();
                const fieldName = field.name;
                let isValid = true;
                let errorMessage = '';

                // Required field validation
                if (field.hasAttribute('required') && !value) {
                    isValid = false;
                    errorMessage = `${getFieldLabel(fieldName)} is required.`;
                }

                // EIN format validation
                if (fieldName === 'ein_number' && value && !isValidEIN(value)) {
                    isValid = false;
                    errorMessage = 'EIN must be in format 12-3456789.';
                }

                // Zip code validation
                if (fieldName === 'zip_code' && value && !isValidZipCode(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid 5-digit zip code.';
                }

                // DOT number validation
                if (fieldName === 'dot_number' && value && !isValidDOTNumber(value)) {
                    isValid = false;
                    errorMessage = 'DOT number must be 7 digits.';
                }

                // MC number validation
                if (fieldName === 'mc_number' && value && !isValidMCNumber(value)) {
                    isValid = false;
                    errorMessage = 'MC number must be in format MC-123456.';
                }

                showFieldError(field, isValid, errorMessage);
                return isValid;
            }

            function checkUniqueness(field) {
                const value = field.value.trim();
                if (!value) return;

                // Show checking indicator
                const errorDiv = document.getElementById(`${field.name}-error`);
                if (errorDiv) {
                    errorDiv.textContent = 'Checking availability...';
                    errorDiv.classList.remove('hidden');
                    errorDiv.className = 'text-blue-500 text-sm mt-1';
                }

                // Simulate API call for uniqueness check
                setTimeout(() => {
                    // This would be replaced with actual AJAX call
                    const isUnique = Math.random() > 0.3; // 70% chance of being unique
                    
                    if (errorDiv) {
                        if (isUnique) {
                            errorDiv.textContent = 'Available ✓';
                            errorDiv.className = 'text-green-500 text-sm mt-1';
                            field.classList.remove('border-red-500');
                            field.classList.add('border-green-500');
                        } else {
                            errorDiv.textContent = `This ${getFieldLabel(field.name).toLowerCase()} is already registered.`;
                            errorDiv.className = 'text-red-500 text-sm mt-1';
                            field.classList.add('border-red-500');
                            field.classList.remove('border-green-500');
                        }
                    }
                }, 1500);
            }

            function showFieldError(field, isValid, errorMessage) {
                const errorDiv = document.getElementById(`${field.name}-error`);
                if (errorDiv) {
                    if (isValid) {
                        errorDiv.classList.add('hidden');
                        field.classList.remove('border-red-500');
                        field.classList.add('border-slate-300/80');
                    } else {
                        errorDiv.textContent = errorMessage;
                        errorDiv.className = 'text-red-500 text-sm mt-1';
                        errorDiv.classList.remove('hidden');
                        field.classList.add('border-red-500');
                        field.classList.remove('border-slate-300/80');
                    }
                }
            }

            function clearFieldError(field) {
                const errorDiv = document.getElementById(`${field.name}-error`);
                if (errorDiv && !errorDiv.classList.contains('hidden')) {
                    errorDiv.classList.add('hidden');
                    field.classList.remove('border-red-500', 'border-green-500');
                    field.classList.add('border-slate-300/80');
                }
            }

            function getFieldLabel(fieldName) {
                const labels = {
                    'carrier_name': 'Company Name',
                    'address': 'Business Address',
                    'state': 'State',
                    'zip_code': 'Zip Code',
                    'ein_number': 'EIN Number',
                    'dot_number': 'DOT Number',
                    'mc_number': 'MC Number',
                    'state_dot_number': 'State DOT Number',
                    'ifta_account_number': 'IFTA Account Number',
                    'business_type': 'Business Type',
                    'years_in_business': 'Years in Business',
                    'fleet_size': 'Fleet Size'
                };
                return labels[fieldName] || fieldName;
            }

            function isValidEIN(ein) {
                return /^\d{2}-\d{7}$/.test(ein);
            }

            function isValidZipCode(zip) {
                return /^\d{5}$/.test(zip);
            }

            function isValidDOTNumber(dot) {
                return /^\d{7}$/.test(dot);
            }

            function isValidMCNumber(mc) {
                return /^MC-\d{6}$/.test(mc);
            }

            function setupAjaxValidation() {
                const criticalFields = ['dot_number', 'mc_number', 'ein_number'];
                const timeouts = {};
                
                criticalFields.forEach(field => {
                    const input = document.getElementById(field);
                    const errorElement = document.getElementById(field + '-error');
                    
                    if (input && errorElement) {
                        input.addEventListener('input', function() {
                            const value = this.value.trim();
                            
                            // Clear previous timeout
                            clearTimeout(timeouts[field]);
                            
                            if (value && isValidFieldFormat(field, value)) {
                                // Debounce AJAX call
                                timeouts[field] = setTimeout(() => {
                                    checkFieldUniqueness(field, value, errorElement);
                                }, 800);
                            } else if (value) {
                                showAjaxFieldError(errorElement, getFieldValidationMessage(field));
                            } else {
                                clearAjaxFieldError(errorElement);
                            }
                        });
                    }
                });
            }
            
            function isValidFieldFormat(field, value) {
                switch (field) {
                    case 'dot_number':
                        return /^\d{1,8}$/.test(value);
                    case 'mc_number':
                        return /^MC-\d{1,8}$/.test(value);
                    case 'ein_number':
                        return /^\d{2}-\d{7}$/.test(value);
                    default:
                        return true;
                }
            }
            
            function getFieldValidationMessage(field) {
                switch (field) {
                    case 'dot_number':
                        return 'DOT number must be 1-8 digits';
                    case 'mc_number':
                        return 'MC number must be in format MC-123456';
                    case 'ein_number':
                        return 'EIN must be in format XX-XXXXXXX';
                    default:
                        return 'Invalid format';
                }
            }
            
            // Función para verificar unicidad de campos via AJAX
            async function checkFieldUniqueness(field, value, errorElement) {
                try {
                    showAjaxFieldLoading(errorElement);
                    
                    const response = await fetch('/carrier/wizard/check-uniqueness', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            field: field,
                            value: value
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.available) {
                        showAjaxFieldSuccess(errorElement, data.message);
                    } else {
                        showAjaxFieldError(errorElement, data.message);
                    }
                } catch (error) {
                    console.error('Error checking field uniqueness:', error);
                    showAjaxFieldError(errorElement, 'Error validating field. Please try again.');
                }
            }
            
            // Funciones para mostrar estados de validación AJAX
            function showAjaxFieldError(element, message) {
                element.textContent = message;
                element.className = 'text-red-600 text-sm mt-1';
                element.style.display = 'block';
            }
            
            function showAjaxFieldSuccess(element, message) {
                element.textContent = message;
                element.className = 'text-green-600 text-sm mt-1';
                element.style.display = 'block';
            }
            
            function showAjaxFieldLoading(element) {
                element.textContent = 'Checking availability...';
                element.className = 'text-blue-600 text-sm mt-1';
                element.style.display = 'block';
            }
            
            function clearAjaxFieldError(element) {
                element.style.display = 'none';
                element.textContent = '';
            }

            function showLoadingState() {
                const submitBtn = document.getElementById('submit-btn');
                const submitText = document.getElementById('submit-text');
                const loadingSpinner = document.getElementById('loading-spinner');
                
                submitBtn.disabled = true;
                submitText.classList.add('hidden');
                loadingSpinner.classList.remove('hidden');
            }
        });
    </script>
</x-guest-layout>