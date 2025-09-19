<div class="w-full mx-auto px-0 md:px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header Section -->
    <div class="py-4 mb-8">
        <div class="flex items-center mb-6">
            <div class="bg-blue-100 rounded-full p-3 mr-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 0a9 9 0 1118 0a9 9 0 01-18 0z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Driver Application</h2>
                <p class="text-gray-600 mt-1">Complete your application details below</p>
            </div>
        </div>

        <!-- Position Applied For -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-gray-700 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
                </svg>
                <label class="text-xl font-semibold text-gray-800">Position Applied For *</label>
                <div class="ml-3 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Required</div>
            </div>
            <p class="text-gray-600 mb-4">Select the position you are applying for to see relevant sections.</p>

            <select wire:model.live="applying_position" class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                <option value="">Select Position</option>
                @foreach ($driverPositions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('applying_position')
            <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
            @enderror

            <div x-show="$wire.applying_position === 'other'" x-transition class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Specify Other Position *</label>
                <input type="text" wire:model="applying_position_other" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500" placeholder="Enter position details">
                @error('applying_position_other')
                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <!-- Owner Operator Section -->
    <div x-show="$wire.applying_position === 'owner_operator'"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 transform -translate-y-8"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-lg border-2 border-blue-200 p-8 mb-8">

        <div class="flex items-center mb-8">
            <div class="bg-blue-600 rounded-full p-3 mr-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-blue-900">Owner Operator Information</h3>
                <p class="text-blue-700 mt-1">Provide your business and vehicle details</p>
            </div>
        </div>

        <!-- Owner Information Card -->
        <div class="bg-white rounded-xl shadow-md border border-blue-200 p-6 mb-6">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <h4 class="text-xl font-semibold text-gray-800">Business Owner Details</h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Owner Name *</label>
                    <input type="text" wire:model="owner_name" class="w-full px-3 py-2 border rounded" placeholder="Enter full name" disabled>
                    @error('owner_name')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                    <input type="tel" wire:model="owner_phone" class="w-full px-3 py-2 border rounded" placeholder="Enter phone number" disabled>
                    @error('owner_phone')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input type="email" wire:model="owner_email" class="w-full px-3 py-2 border rounded"placeholder="Enter email address" disabled>
                    @error('owner_email')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Vehicle Information Section -->
        <div class="mb-6">
            <div class="flex items-center mb-6">
                <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <h4 class="text-xl font-semibold text-gray-800">Vehicle Information</h4>
            </div>

            @if ($this->existingVehicles && count($this->existingVehicles) > 0)
            @include('livewire.driver.steps.includes._vehicle-table', [
            'vehicles' => $this->existingVehicles,
            'title' => 'Your Registered Vehicles',
            'showAddButton' => true,
            'addButtonText' => 'Register New Vehicle',
            'addAction' => 'clearVehicleForm'
            ])
            @endif

            @include('livewire.driver.steps.includes._vehicle-form', [
            'wirePrefix' => 'vehicle_make',
            'title' => ($this->existingVehicles && count($this->existingVehicles) > 0) ? 'Add New Vehicle' : 'Vehicle Details',
            'showButtons' => false
            ])
        </div>

        <!-- Contract Agreement -->
        <div class="bg-white rounded-xl shadow-md border border-blue-200 p-6 mb-6">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h4 class="text-xl font-semibold text-gray-800">Contract Agreement</h4>
            </div>

            <div class="flex items-start">
                <input type="checkbox" wire:model="owner_terms_accepted" class="mt-1 h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label class="ml-3 text-gray-700">
                    I agree to the terms and conditions of the Owner Operator contract and understand my responsibilities as an independent contractor.
                </label>
            </div>
            @error('owner_terms_accepted')
            <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Create Record Section -->
        <div class="bg-slate-50 p-6 rounded-lg">
            <h3 class="text-lg font-medium text-slate-900 mb-4">Owner Operator Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-base.form-label for="owner_name">Owner Name *</x-base.form-label>
                    <x-base.form-input id="owner_name" type="text" wire:model="owner_name" placeholder="Enter owner name" class="@error('owner_name') border-red-500 @enderror" />
                    @error('owner_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <x-base.form-label for="owner_phone">Owner Phone *</x-base.form-label>
                    <x-base.form-input id="owner_phone" type="tel" wire:model="owner_phone" placeholder="Enter owner phone" class="@error('owner_phone') border-red-500 @enderror" />
                    @error('owner_phone')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <x-base.form-label for="owner_email">Owner Email *</x-base.form-label>
                    <x-base.form-input id="owner_email" type="email" wire:model="owner_email" placeholder="Enter owner email" class="@error('owner_email') border-red-500 @enderror" />
                    @error('owner_email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="text-center mt-6">
                <button type="submit" wire:click="createOwnerOperatorRecord"
                    class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">
                    Crear Registro de Vehículo
                </button>
            </div>
        </div>
    </div>

    <!-- Third Party Driver Section -->
    <div x-show="$wire.applying_position === 'third_party_driver'"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 transform -translate-y-8"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg border-2 border-green-200 p-0 md:p-5 mb-8">

        <div class="flex items-center mb-8">
            <div class="bg-green-600 rounded-full p-3 mr-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-green-900">Third Party Company Driver</h3>
                <p class="text-green-700 mt-1">Provide your company and vehicle information</p>
            </div>
        </div>

        <!-- Company Information Card -->
        <div class="bg-white rounded-xl shadow-md border border-green-200 p-6 mb-6">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h4 class="text-xl font-semibold text-gray-800">Company Information</h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                    <input type="text" wire:model="third_party_company_name" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Enter company name">
                    @error('third_party_company_name')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Phone *</label>
                    <input type="tel" wire:model="third_party_company_phone" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Enter phone number">
                    @error('third_party_company_phone')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Email *</label>
                    <input type="email" wire:model="third_party_company_email" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Enter email address">
                    @error('third_party_company_email')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">DBA (Doing Business As)</label>
                    <input type="text" wire:model="third_party_dba" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Enter DBA name">
                    @error('third_party_dba')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">FEIN (Federal Tax ID)</label>
                    <input type="text" wire:model="third_party_fein" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Enter FEIN">
                    @error('third_party_fein')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Address *</label>
                    <input type="text" wire:model="third_party_address" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" placeholder="Enter complete address">
                    @error('third_party_address')
                    <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Vehicle Information Section -->
        <div class="mb-6">
            <div class="flex items-center mb-6">
                <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                <h4 class="text-xl font-semibold text-gray-800">Company Vehicle Information</h4>
            </div>

            @if ($this->existingThirdPartyVehicles && count($this->existingThirdPartyVehicles) > 0)
            @include('livewire.driver.steps.includes._vehicle-table', [
            'vehicles' => $this->existingThirdPartyVehicles,
            'title' => 'Company Vehicles',
            'showAddButton' => true,
            'addButtonText' => 'Register New Vehicle',
            'addAction' => 'clearVehicleForm'
            ])
            @endif

            @include('livewire.driver.steps.includes._vehicle-form', [
            'wirePrefix' => 'third_party_vehicle_make',
            'title' => ($this->existingThirdPartyVehicles && count($this->existingThirdPartyVehicles) > 0) ? 'Add New Vehicle' : 'Vehicle Details',
            'showButtons' => false
            ])
        </div>

        <!-- Create Record and Email Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Create Record -->
            <div class="bg-white rounded-xl shadow-md border border-green-200 p-6">
                <div class="flex items-center mb-4">
                    <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <h4 class="text-lg font-semibold text-gray-800">Crear Registro de Vehículo</h4>
                </div>

                @if($this->third_party_created)
                <div class="flex items-center text-green-600 mb-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">Registro Creado</span>
                </div>
                @else
                <div class="flex items-center text-orange-600 mb-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">Pending Creation</span>
                </div>
                @endif

                <button type="submit" wire:click="createThirdPartyRecord"
                    class="w-full px-4 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-500/20 transition-all duration-200">
                    Crear Registro de Vehículo Third Party
                </button>
            </div>

            <!-- Email Documents - Only show after third party record is created -->
            @if($thirdPartyRecordCreated)
            <div class="bg-white rounded-xl shadow-md border border-green-200 p-6">
                <div class="flex items-center mb-4">
                    <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <h4 class="text-lg font-semibold text-gray-800">Email Documents</h4>
                </div>

                <p class="text-gray-600 mb-4 text-sm">Send document signing request to the company.</p>

                <button type="button" wire:click="sendThirdPartyEmail"
                    class="w-full px-4 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 transition-all duration-200 flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Send Email Request
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Company Driver Section -->
    <div x-show="$wire.applying_position === 'company_driver'"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 transform -translate-y-8"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl shadow-lg border-2 border-purple-200 p-8 mb-8">

        <div class="flex items-center mb-6">
            <div class="bg-purple-600 rounded-full p-3 mr-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-2xl font-bold text-purple-900">Company Driver</h3>
                <p class="text-purple-700 mt-1">You will be assigned a vehicle from our fleet</p>
            </div>
        </div>

        <div class="bg-slate-50 p-6 rounded-lg">
            <h3 class="text-lg font-medium text-slate-900 mb-4">Company Driver Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-base.form-label for="driver_license_number">Driver License Number *</x-base.form-label>
                    <x-base.form-input id="driver_license_number" type="text" wire:model="driver_license_number" placeholder="Enter license number" class="@error('driver_license_number') border-red-500 @enderror" />
                    @error('driver_license_number')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <x-base.form-label for="driver_experience_years">Years of Experience *</x-base.form-label>
                    <x-base.form-input id="driver_experience_years" type="number" wire:model="driver_experience_years" placeholder="Enter years" class="@error('driver_experience_years') border-red-500 @enderror" />
                    @error('driver_experience_years')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information Sections -->
    <div x-show="$wire.applying_position && $wire.applying_position !== ''" class="space-y-8">

        <!-- Location Preferences -->
        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
            <!-- Location Preference -->
            <div class="mb-6 bg-gray-50 py-2 rounded-lg">
                <label class="block mb-2 font-medium text-gray-700">Location Preference <span
                        class="text-red-500">*</span></label>
                <select wire:model="applying_location"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                    <option value="">Select State</option>
                    @foreach ($usStates as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>

                @error('applying_location')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Eligibility Information -->
            <div class="p-5 border rounded-lg bg-blue-50 border-blue-100 shadow-sm mb-6">
                <h3 class="text-lg font-medium mb-4 text-primary border-b border-blue-100 pb-2">Eligibility Information</h3>

                <div class="flex items-center mt-4 mb-4">
                    <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="eligible_to_work"
                        wire:model="eligible_to_work" />
                    <span class="cursor-pointer ">
                        Eligible to work in the United States *
                    </span>
                    @error('eligible_to_work')
                    <span class="text-red-500 text-sm block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center mt-4 mb-4">
                    <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="can_speak_english"
                        wire:model="can_speak_english" />
                    <span class="cursor-pointer ">
                        Can speak and understand English
                    </span>
                    @error('can_speak_english')
                    <span class="text-red-500 text-sm block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="has_twic_card" wire:model="has_twic_card" />
                        <span>I have a TWIC Card</span>
                    </label>

                    <div x-show="$wire.has_twic_card" x-transition class="mt-2">
                        <label class="block mb-1 font-medium text-gray-700">TWIC Card Expiration Date *</label>
                        <input type="text" 
                            name="twic_expiration_date"
                            wire:model="twic_expiration_date"
                            class="driver-datepicker w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm"
                            placeholder="MM/DD/YYYY"
                            value="{{ $twic_expiration_date }}" />
                        @error('twic_expiration_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Expected Pay Rate -->
            <div class="mb-6 bg-gray-50 py-2 rounded-lg">
                <label class="block mb-2 font-medium text-gray-700">Expected Pay Rate</label>
                <input type="input" wire:model="expected_pay" class="w-full px-3 py-2 border rounded"
                    placeholder="e.g. $25/hour">
            </div>

            <!-- Referral Source -->
            <div class="mb-6 bg-gray-50 py-4 rounded-lg">
                <label class="block mb-2 font-medium text-gray-700">Referral Source <span
                        class="text-red-500">*</span></label>
                <select wire:model="how_did_hear"
                    class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                    <option value="">Select Source</option>
                    @foreach ($referralSources as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('how_did_hear')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror

                <div x-show="$wire.how_did_hear === 'employee_referral'" x-transition class="mt-2">
                    <label class="block mb-1">Employee Name *</label>
                    <x-base.form-input type="text" wire:model="referral_employee_name"
                        class="w-full px-3 py-2 border rounded" />
                    @error('referral_employee_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div x-show="$wire.how_did_hear === 'other'" x-transition class="mt-2">
                    <label class="block mb-1">Specify Other Source *</label>
                    <x-base.form-input type="text" wire:model="how_did_hear_other"
                        class="w-full px-3 py-2 border rounded" />
                    @error('how_did_hear_other')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Work History -->
            <div class="mb-6 bg-gray-50 py-4 rounded-lg">
                <h3 class="text-lg font-medium mb-4 text-primary border-b border-gray-200 pb-2">Work History</h3>
                <div class="mb-4">
                    <label
                        class="flex items-center p-2 bg-white rounded-md shadow-sm border border-gray-200 hover:bg-gray-50 cursor-pointer">
                        <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="has_work_history"
                            wire:model="has_work_history" />
                        <span class="text-gray-700">Work History with this company</span>
                    </label>
                </div>

                <div x-show="$wire.has_work_history" x-transition>
                    @foreach ($work_histories as $index => $history)
                    <div class="p-5 border rounded-lg bg-white shadow-sm mb-5">
                        <div class="flex justify-between mb-2">
                            <h4 class="font-medium">Work History #{{ $index + 1 }}</h4>
                            @if (count($work_histories) > 1)
                            <button type="button" wire:click="removeWorkHistory({{ $index }})"
                                class="text-red-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block mb-1">Previous Company *</label>
                                <x-base.form-input type="text"
                                    wire:model.defer="work_histories.{{ $index }}.previous_company"
                                    class="w-full px-3 py-2 border rounded" />
                                @error('work_histories.' . $index . '.previous_company')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block mb-1">Position *</label>
                                <x-base.form-input type="text"
                                    wire:model.defer="work_histories.{{ $index }}.position"
                                    class="w-full px-3 py-2 border rounded" />
                                @error('work_histories.' . $index . '.position')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block mb-1 font-medium text-gray-700">Start Date *</label>
                                <input type="text"
                                    name="work_histories.{{ $index }}.start_date"
                                    wire:model="work_histories.{{ $index }}.start_date"
                                    class="driver-datepicker form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm"
                                    placeholder="MM/DD/YYYY"
                                    value="{{ $work_histories[$index]['start_date'] ?? '' }}" />
                                @error('work_histories.' . $index . '.start_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block mb-1 font-medium text-gray-700">End Date *</label>
                                <input type="text"
                                    name="work_histories.{{ $index }}.end_date"
                                    wire:model="work_histories.{{ $index }}.end_date"
                                    class="driver-datepicker form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm"
                                    placeholder="MM/DD/YYYY"
                                    value="{{ $work_histories[$index]['end_date'] ?? '' }}" />
                                @error('work_histories.' . $index . '.end_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Location *</label>
                            <x-base.form-input type="text"
                                wire:model.defer="work_histories.{{ $index }}.location"
                                class="w-full px-3 py-2 border rounded" />
                            @error('work_histories.' . $index . '.location')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Reason for Leaving</label>
                            <x-base.form-input type="text"
                                wire:model.defer="work_histories.{{ $index }}.reason_for_leaving"
                                class="w-full px-3 py-2 border rounded" />
                        </div>

                        <div class="mb-4">
                            <label class="block mb-1">Reference Contact</label>
                            <x-base.form-input type="text"
                                wire:model.defer="work_histories.{{ $index }}.reference_contact"
                                class="w-full px-3 py-2 border rounded" placeholder="Name and phone number" />
                        </div>
                    </div>
                    @endforeach

                    <button type="button" wire:click="addWorkHistory"
                        class="px-4 py-2.5 bg-blue-800 text-white rounded-lg hover:bg-blue-800 transition duration-150 ease-in-out flex items-center shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Add Work History
                    </button>
                </div>
            </div>

        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between items-center pt-8 border-t border-gray-200 mt-8">
            <button type="button" wire:click="previous" class="px-6 py-3 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-500/20 transition-all duration-200 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Previous
            </button>

            <div class="flex space-x-4">
                <button type="button" wire:click="saveAndExit" class="px-6 py-3 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-4 focus:ring-yellow-500/20 transition-all duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Save & Exit
                </button>

                <button type="button" wire:click="next" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 transition-all duration-200 flex items-center">
                    Next
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validation for Owner Operator
            function validateOwnerOperator() {
                const requiredFields = [
                    'owner_name', 'owner_phone', 'owner_email',
                    'vehicle_make', 'vehicle_model', 'vehicle_year', 'vehicle_vin', 'vehicle_type'
                ];

                let isValid = true;
                requiredFields.forEach(field => {
                    const element = document.querySelector(`[wire\\:model="${field}"]`);
                    if (element && !element.value.trim()) {
                        element.classList.add('border-red-500');
                        isValid = false;
                    } else if (element) {
                        element.classList.remove('border-red-500');
                    }
                });

                // Check terms acceptance
                const termsCheckbox = document.querySelector('[wire\\:model="owner_terms_accepted"]');
                if (termsCheckbox && !termsCheckbox.checked) {
                    termsCheckbox.closest('.bg-white').classList.add('border-red-500');
                    isValid = false;
                } else if (termsCheckbox) {
                    termsCheckbox.closest('.bg-white').classList.remove('border-red-500');
                }

                return isValid;
            }

            // Validation for Third Party
            function validateThirdParty() {
                const requiredFields = [
                    'third_party_company_name', 'third_party_company_phone', 'third_party_company_email',
                    'third_party_address', 'third_party_vehicle_make', 'third_party_vehicle_model',
                    'third_party_vehicle_year', 'third_party_vehicle_vin', 'third_party_vehicle_type'
                ];

                let isValid = true;
                requiredFields.forEach(field => {
                    const element = document.querySelector(`[wire\\:model="${field}"]`);
                    if (element && !element.value.trim()) {
                        element.classList.add('border-red-500');
                        isValid = false;
                    } else if (element) {
                        element.classList.remove('border-red-500');
                    }
                });

                return isValid;
            }

            // Add event listeners for real-time validation
            document.addEventListener('input', function(e) {
                if (e.target.hasAttribute('wire:model')) {
                    e.target.classList.remove('border-red-500');
                }
            });

            document.addEventListener('change', function(e) {
                if (e.target.hasAttribute('wire:model')) {
                    e.target.classList.remove('border-red-500');
                }
            });
        });
    </script>