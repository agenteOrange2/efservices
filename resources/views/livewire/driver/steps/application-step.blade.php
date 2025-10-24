<div class="box box--stacked flex flex-col">
    <div class="flex items-center px-5 py-5 border-b border-slate-200/60 dark:border-darkmode-400">
        <h2 class="font-medium text-base mr-auto">Application Details</h2>
    </div>
    <div class="p-5">

        <!-- Position Applied For -->
        <div class="mt-5">
            <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Position Applied For</div>
                        <div class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required</div>
                    </div>
                    <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                        Select the position you are applying for.
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <!-- Position Select - SIMPLIFIED TO DRIVER AND OTHER -->
                <div class="mb-6">
                    <select wire:model="applying_position" class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                        <option value="">Select Position</option>
                        <option value="driver">Driver</option>
                        <option value="other">Other</option>
                    </select>
                    @error('applying_position')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                    
                    <!-- Other Position Input -->
                    <div x-show="$wire.applying_position === 'other'" x-transition class="mt-2">
                        <label class="block mb-1">Specify Position *</label>
                        <x-base.form-input type="text" wire:model="applying_position_other" class="w-full px-3 py-2 border rounded" placeholder="Enter position" />
                        @error('applying_position_other')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Vehicle Type Selection (Single Choice) -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-4 text-primary border-b border-gray-200 pb-2">Vehicle Assignment Type</h3>
                    <div class="text-sm text-gray-600 mb-4">Select ONE vehicle type you want to be assigned to:</div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="flex items-center p-4 bg-white rounded-lg border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-colors duration-200">
                            <input type="radio" wire:model="selectedDriverType" value="owner_operator" class="mr-3 h-4 w-4 text-blue-600">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">Owner Operator Vehicles</div>
                                <div class="text-sm text-gray-500">Manage owner operator assignments</div>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 bg-white rounded-lg border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-colors duration-200">
                            <input type="radio" wire:model="selectedDriverType" value="third_party" class="mr-3 h-4 w-4 text-blue-600">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">Third Party Vehicles</div>
                                <div class="text-sm text-gray-500">Manage third party assignments</div>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 bg-white rounded-lg border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-colors duration-200">
                            <input type="radio" wire:model="selectedDriverType" value="company_driver" class="mr-3 h-4 w-4 text-blue-600">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">Company Vehicles</div>
                                <div class="text-sm text-gray-500">Manage company vehicle assignments</div>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Debug Info -->
                    <div class="mt-4 p-2 bg-yellow-100 border border-yellow-300 rounded text-sm">
                        <strong>Debug:</strong> selectedDriverType = {{ $selectedDriverType }} | applying_position = {{ $applying_position }}
                    </div>
                </div>

                <!-- Owner Operator Information -->
                <div x-show="$wire.selectedDriverType === 'owner_operator'" x-transition
                    class="mt-4 p-4 border rounded bg-gray-50">
                    <h3 class="text-lg font-medium mb-4 text-primary border-b border-gray-200 pb-2">Owner Operator
                        Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Owner Name *</label>
                            <x-base.form-input type="text" wire:model="owner_name"
                                class="w-full px-3 py-2 border rounded" />
                            @error('owner_name')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Phone Number *</label>
                            <x-base.form-input type="tel" wire:model="owner_phone"
                                class="w-full px-3 py-2 border rounded" />
                            @error('owner_phone')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Email *</label>
                        <x-base.form-input type="email" wire:model="owner_email"
                            class="w-full px-3 py-2 border rounded" />
                        @error('owner_email')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <h4 class="font-medium text-lg text-primary mb-3 mt-5 border-b border-gray-200 pb-2">Vehicle
                        Information (will be assigned to carrier)</h4>

                    @if (count($existingVehicles) > 0)
                        <div class="mb-5 p-4 bg-blue-50 rounded-lg border border-blue-100 shadow-sm">
                            <h5 class="font-medium mb-2">Existing Vehicles</h5>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                                    <thead>
                                        <tr>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Make</th>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Model</th>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Year</th>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                VIN</th>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Type</th>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($existingVehicles as $vehicle)
                                            <tr class="{{ $selectedVehicleId == $vehicle->id ? 'bg-blue-100' : '' }}">
                                                <td class="py-2 px-3 border-b">{{ $vehicle->make }}</td>
                                                <td class="py-2 px-3 border-b">{{ $vehicle->model }}</td>
                                                <td class="py-2 px-3 border-b">{{ $vehicle->year }}</td>
                                                <td class="py-2 px-3 border-b">{{ $vehicle->vin }}</td>
                                                <td class="py-2 px-3 border-b">{{ ucfirst($vehicle->type) }}</td>
                                                <td class="py-2 px-3 border-b">
                                                    <div class="flex space-x-2">
                                                        <button type="button"
                                                            wire:click="selectVehicle({{ $vehicle->id }})"
                                                            class="px-2.5 py-1.5 bg-blue-800 text-white rounded-md text-sm hover:bg-blue-800 transition duration-150 ease-in-out flex items-center">
                                                            Select
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <button type="button" wire:click="clearVehicleForm"
                                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition duration-150 ease-in-out flex items-center shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Register New Vehicle
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Make *</label>
                            <x-base.form-input type="text" wire:model="vehicle_make"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_make')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Model *</label>
                            <x-base.form-input type="text" wire:model="vehicle_model"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_model')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Year *</label>
                            <x-base.form-input type="number" wire:model="vehicle_year"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_year')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">VIN *</label>
                            <x-base.form-input type="text" wire:model="vehicle_vin"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_vin')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Company Unit Number</label>
                            <x-base.form-input type="text" wire:model="vehicle_company_unit_number"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_company_unit_number')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Type *</label>
                            <select wire:model="vehicle_type"
                                class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                                <option value="">Select Type</option>
                                <option value="truck">Truck</option>
                                <option value="trailer">Trailer</option>
                                <option value="van">Van</option>
                                <option value="pickup">Pickup</option>
                                <option value="other">Other</option>
                            </select>
                            @error('vehicle_type')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">GVWR</label>
                            <x-base.form-input type="text" wire:model="vehicle_gvwr"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_gvwr')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Tire Size</label>
                            <x-base.form-input type="text" wire:model="vehicle_tire_size"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_tire_size')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Fuel Type *</label>
                            <select wire:model="vehicle_fuel_type"
                                class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                                <option value="">Select Fuel Type</option>
                                <option value="diesel">Diesel</option>
                                <option value="gasoline">Gasoline</option>
                                <option value="electric">Electric</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="other">Other</option>
                            </select>
                            @error('vehicle_fuel_type')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">IRP Apportioned Plate</label>
                            <div class="flex items-center mt-2">
                                <x-base.form-check.input class="mr-2.5 border" type="checkbox"
                                    name="vehicle_irp_apportioned_plate" wire:model="vehicle_irp_apportioned_plate"
                                    id="vehicle_irp_apportioned_plate_third_party" />
                                <label for="vehicle_irp_apportioned_plate_third_party">IRP Apportioned Plate</label>
                            </div>
                            @error('vehicle_irp_apportioned_plate')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Registration State *</label>
                            <select wire:model="vehicle_registration_state"
                                class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                                <option value="">Select State</option>
                                @foreach ($usStates as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('vehicle_registration_state')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Registration Number *</label>
                            <x-base.form-input type="text" wire:model="vehicle_registration_number"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_registration_number')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Registration Expiration Date *</label>
                            <input type="text" 
                                name="vehicle_registration_expiration_date"
                                wire:model="vehicle_registration_expiration_date"
                                class="driver-datepicker w-full px-3 py-2 border rounded"
                                placeholder="MM/DD/YYYY"
                                value="{{ $vehicle_registration_expiration_date }}" />
                            @error('vehicle_registration_expiration_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Permanent Tag</label>
                            <div class="flex items-center mt-2">
                                <x-base.form-check.input class="mr-2.5 border" type="checkbox"
                                    name="vehicle_permanent_tag_third_party" wire:model="vehicle_permanent_tag"
                                    id="vehicle_permanent_tag_third_party" />
                                <label for="vehicle_permanent_tag_third_party">Permanent Tag</label>
                            </div>
                            @error('vehicle_permanent_tag')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Location</label>
                        <x-base.form-input type="text" wire:model="vehicle_location"
                            class="w-full px-3 py-2 border rounded" />
                        @error('vehicle_location')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Notes</label>
                        <x-base.form-textarea wire:model="vehicle_notes" class="w-full px-3 py-2 border rounded"
                            rows="3" />
                        @error('vehicle_notes')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mt-5 p-4 bg-amber-50 rounded-lg border border-amber-100 shadow-sm">
                        <p class="mb-3">By checking this box, I agree to the terms and conditions of the contract. I
                            understand that I am responsible for maintaining my vehicle according to company standards
                            and complying with all applicable regulations.</p>
                        <p class="mb-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl
                            eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl. Nullam
                            auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget
                            nisl.</p>
                        <p class="mb-3">Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget
                            ultricies nisl nisl eget nisl. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl
                            aliquam nisl, eget ultricies nisl nisl eget nisl.</p>

                        <div class="flex items-center">
                            <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="contract_agreed"
                                wire:model="contract_agreed" id="contract_agreed" />
                            <label for="contract_agreed" class="cursor-pointer">I Agree to the Terms and Conditions
                                *</label>
                        </div>
                        @error('contract_agreed')
                            <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Third Party Driver Information -->
                <div x-show="$wire.selectedDriverType === 'third_party'" x-transition
                    class="mt-4 p-4 border rounded bg-gray-50">
                    <h3 class="text-lg font-medium mb-4 text-primary border-b border-gray-200 pb-2">Third Party Company Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Company Representative Name *</label>
                            <x-base.form-input type="text" wire:model="third_party_name"
                                class="w-full px-3 py-2 border rounded" />
                            @error('third_party_name')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Phone Number *</label>
                            <x-base.form-input type="tel" wire:model="third_party_phone"
                                class="w-full px-3 py-2 border rounded" />
                            @error('third_party_phone')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Email *</label>
                        <x-base.form-input type="email" wire:model="third_party_email"
                            class="w-full px-3 py-2 border rounded" />
                        @error('third_party_email')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">DBA (Doing Business As)</label>
                            <x-base.form-input type="text" wire:model="third_party_dba"
                                class="w-full px-3 py-2 border rounded" />
                            @error('third_party_dba')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Contact Person</label>
                            <x-base.form-input type="text" wire:model="third_party_contact"
                                class="w-full px-3 py-2 border rounded" />
                            @error('third_party_contact')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Address</label>
                            <x-base.form-input type="text" wire:model="third_party_address"
                                class="w-full px-3 py-2 border rounded" />
                            @error('third_party_address')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">FEIN (Federal Employer Identification Number)</label>
                            <x-base.form-input type="text" wire:model="third_party_fein"
                                class="w-full px-3 py-2 border rounded" />
                            @error('third_party_fein')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <h4 class="font-medium text-lg text-primary mb-3 mt-5 border-b border-gray-200 pb-2">Vehicle
                        Information (will be assigned to carrier)</h4>

                    @if (count($existingVehicles) > 0)
                        <div class="mb-5 p-4 bg-blue-50 rounded-lg border border-blue-100 shadow-sm">
                            <h5 class="font-medium mb-2">Existing Vehicles</h5>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                                    <thead>
                                        <tr>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Make</th>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Model</th>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Year</th>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                VIN</th>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Type</th>
                                            <th
                                                class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($existingVehicles as $vehicle)
                                            <tr class="{{ $selectedVehicleId == $vehicle->id ? 'bg-blue-100' : '' }}">
                                                <td class="py-2 px-3 border-b">{{ $vehicle->make }}</td>
                                                <td class="py-2 px-3 border-b">{{ $vehicle->model }}</td>
                                                <td class="py-2 px-3 border-b">{{ $vehicle->year }}</td>
                                                <td class="py-2 px-3 border-b">{{ $vehicle->vin }}</td>
                                                <td class="py-2 px-3 border-b">{{ ucfirst($vehicle->type) }}</td>
                                                <td class="py-2 px-3 border-b">
                                                    <div class="flex space-x-2">
                                                        <button type="button"
                                                            wire:click="selectVehicle({{ $vehicle->id }})"
                                                            class="px-2.5 py-1.5 bg-blue-800 text-white rounded-md text-sm hover:bg-blue-800 transition duration-150 ease-in-out flex items-center">
                                                            Select
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <button type="button" wire:click="clearVehicleForm"
                                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition duration-150 ease-in-out flex items-center shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Register New Vehicle
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Make *</label>
                            <x-base.form-input type="text" wire:model="vehicle_make"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_make')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Model *</label>
                            <x-base.form-input type="text" wire:model="vehicle_model"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_model')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Year *</label>
                            <x-base.form-input type="number" wire:model="vehicle_year"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_year')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">VIN *</label>
                            <x-base.form-input type="text" wire:model="vehicle_vin"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_vin')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Company Unit Number</label>
                            <x-base.form-input type="text" wire:model="vehicle_company_unit_number"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_company_unit_number')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Type *</label>
                            <select wire:model="vehicle_type"
                                class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                                <option value="">Select Type</option>
                                <option value="truck">Truck</option>
                                <option value="trailer">Trailer</option>
                                <option value="van">Van</option>
                                <option value="pickup">Pickup</option>
                                <option value="other">Other</option>
                            </select>
                            @error('vehicle_type')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">GVWR</label>
                            <x-base.form-input type="text" wire:model="vehicle_gvwr"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_gvwr')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Tire Size</label>
                            <x-base.form-input type="text" wire:model="vehicle_tire_size"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_tire_size')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Fuel Type *</label>
                            <select wire:model="vehicle_fuel_type"
                                class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                                <option value="">Select Fuel Type</option>
                                <option value="diesel">Diesel</option>
                                <option value="gasoline">Gasoline</option>
                                <option value="electric">Electric</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="other">Other</option>
                            </select>
                            @error('vehicle_fuel_type')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">IRP Apportioned Plate</label>
                            <div class="flex items-center mt-2">
                                <x-base.form-check.input class="mr-2.5 border" type="checkbox"
                                    name="vehicle_irp_apportioned_plate" wire:model="vehicle_irp_apportioned_plate"
                                    id="vehicle_irp_apportioned_plate_third_party" />
                                <label for="vehicle_irp_apportioned_plate_third_party">IRP Apportioned Plate</label>
                            </div>
                            @error('vehicle_irp_apportioned_plate')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Registration State *</label>
                            <select wire:model="vehicle_registration_state"
                                class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                                <option value="">Select State</option>
                                @foreach ($usStates as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('vehicle_registration_state')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Registration Number *</label>
                            <x-base.form-input type="text" wire:model="vehicle_registration_number"
                                class="w-full px-3 py-2 border rounded" />
                            @error('vehicle_registration_number')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1">Registration Expiration Date *</label>
                            <input type="text" 
                                name="vehicle_registration_expiration_date"
                                wire:model="vehicle_registration_expiration_date"
                                class="driver-datepicker w-full px-3 py-2 border rounded"
                                placeholder="MM/DD/YYYY"
                                value="{{ $vehicle_registration_expiration_date }}" />
                            @error('vehicle_registration_expiration_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1">Permanent Tag</label>
                            <div class="flex items-center mt-2">
                                <x-base.form-check.input class="mr-2.5 border" type="checkbox"
                                    name="vehicle_permanent_tag_third_party" wire:model="vehicle_permanent_tag"
                                    id="vehicle_permanent_tag_third_party" />
                                <label for="vehicle_permanent_tag_third_party">Permanent Tag</label>
                            </div>
                            @error('vehicle_permanent_tag')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Location</label>
                        <x-base.form-input type="text" wire:model="vehicle_location"
                            class="w-full px-3 py-2 border rounded" />
                        @error('vehicle_location')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Notes</label>
                        <x-base.form-textarea wire:model="vehicle_notes" class="w-full px-3 py-2 border rounded"
                            rows="3" />
                        @error('vehicle_notes')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mt-4 flex space-x-4">
                        <button type="button" wire:click="sendThirdPartyEmail"
                            class="px-4 py-2 bg-blue-800 text-white rounded hover:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed"
                            @if ($email_sent) disabled @endif>
                            {{ $email_sent ? 'Email Sent' : 'Send Document Signing Request' }}
                        </button>
                        @if ($email_sent)
                            <span class="text-green-500 ml-2 self-center">Email has been sent successfully</span>
                        @endif

                        <button type="button" wire:click="sendThirdPartyEmail"
                            class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
                            @if (!$email_sent) disabled @endif>
                            Resend Email
                        </button>
                    </div>
                </div>

                <!-- Company Driver Fields -->
                <div x-show="$wire.selectedDriverType === 'company_driver'" x-transition
                    class="mt-4 p-4 border rounded bg-gray-50">
                    <h3 class="text-lg font-medium mb-4 text-primary border-b border-gray-200 pb-2">Company Driver Information</h3>

                    <!-- Company Driver Notes -->
                    <div class="mb-6">
                        <label class="block mb-1 font-medium text-gray-700">Company Driver Information</label>
                        <textarea wire:model="company_driver_notes" 
                            class="w-full px-3 py-2 border rounded" 
                            rows="6" 
                            placeholder="Please provide any relevant information about your company driver application, including experience level, schedule preferences, preferred routes, additional certifications, or any other details that would be helpful for your application..."></textarea>
                        @error('company_driver_notes')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Location Preference -->
            <div class="mb-6 bg-gray-50 py-4 rounded-lg">
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
                <h3 class="text-lg font-medium mb-4 text-primary border-b border-blue-100 pb-2">Eligibility
                    Information</h3>

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

                {{-- <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" wire:model="can_speak_english" class="mr-2">
                <span>Can speak and understand English</span>
            </label>
        </div> --}}

                <div class="mb-4">
                    <label class="flex items-center">
                        <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="has_twic_card"
                            wire:model="has_twic_card" />
                        <span>I have a TWIC Card</span>
                    </label>

                    <div x-show="$wire.has_twic_card" x-transition class="mt-2">
                        <label class="block mb-1 font-medium text-gray-700">TWIC Card Expiration Date *</label>
                        <input type="text" 
                            name="twic_expiration_date"
                            wire:model="twic_expiration_date"
                            class="driver-datepicker w-full px-3 py-2 border rounded"
                            placeholder="MM/DD/YYYY"
                            value="{{ $twic_expiration_date }}" />
                        @error('twic_expiration_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Expected Pay Rate -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                <label class="block mb-2 font-medium text-gray-700">Expected Pay Rate</label>
                <input type="input" wire:model="expected_pay" class="w-full px-3 py-2 border rounded"
                    placeholder="e.g. $25/hour">
            </div>

            <!-- Referral Source -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
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
            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
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
                                        class="driver-datepicker w-full px-3 py-2 border rounded"
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
                                        class="driver-datepicker w-full px-3 py-2 border rounded"
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


            <!-- Navigation Buttons -->
            <div class="mt-8 px-5 py-5 border-t border-slate-200/60 dark:border-darkmode-400">
                <div class="flex flex-col sm:flex-row justify-between gap-4">
                    <div class="w-full sm:w-auto">
                        <x-base.button type="button" wire:click="previous" class="w-full sm:w-44"
                            variant="secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z"
                                    clip-rule="evenodd" />
                            </svg> Previous
                        </x-base.button>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                        <x-base.button type="button" wire:click="saveAndExit" class="w-full sm:w-44 text-white"
                            variant="warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4z" />
                            </svg>
                            Save & Exit
                        </x-base.button>
                        <x-base.button type="button" wire:click="next" class="w-full sm:w-44" variant="primary">
                            Next
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </x-base.button>
                    </div>
                </div>
            </div>
        </div>