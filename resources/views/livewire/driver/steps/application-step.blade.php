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
                <!-- Position Select -->
                <div class="mb-6">
                    <select wire:model="applying_position" class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                        <option value="">Select Position</option>
                        @foreach($positionOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
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

                <!-- Vehicle Type Checkboxes (Independent) -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium mb-4 text-primary border-b border-gray-200 pb-2">Vehicle Assignment Types</h3>
                    <div class="text-sm text-gray-600 mb-4">Select the vehicle types you want to be assigned to (independent of position):</div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="flex items-center p-4 bg-white rounded-lg border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-colors duration-200">
                            <input type="checkbox" wire:model="vehicleTypeCheckboxes.owner_operator" class="mr-3 h-4 w-4 text-blue-600">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">Owner Operator Vehicles</div>
                                <div class="text-sm text-gray-500">Manage owner operator assignments</div>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 bg-white rounded-lg border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-colors duration-200">
                            <input type="checkbox" wire:model="vehicleTypeCheckboxes.third_party_driver" class="mr-3 h-4 w-4 text-blue-600">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">Third Party Vehicles</div>
                                <div class="text-sm text-gray-500">Manage third party assignments</div>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 bg-white rounded-lg border-2 border-gray-200 hover:border-blue-300 cursor-pointer transition-colors duration-200">
                            <input type="checkbox" wire:model="vehicleTypeCheckboxes.company_driver" class="mr-3 h-4 w-4 text-blue-600">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">Company Vehicles</div>
                                <div class="text-sm text-gray-500">Manage company vehicle assignments</div>
                            </div>
                        </label>
                    </div>
                    
                    <!-- Debug Info -->
                    <div class="mt-4 p-2 bg-yellow-100 border border-yellow-300 rounded text-sm">
                        <strong>Debug:</strong> vehicleTypeCheckboxes = {{ json_encode($vehicleTypeCheckboxes) }}
                    </div>
                </div>

                <!-- Owner Operator Information -->
                <div x-show="$wire.vehicleTypeCheckboxes.owner_operator" x-transition
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
                <div x-show="$wire.vehicleTypeCheckboxes.third_party_driver" x-transition
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
                <div x-show="$wire.vehicleTypeCheckboxes.company_driver" x-transition
                    class="mt-4 p-4 border rounded bg-gray-50">
                    <h3 class="text-lg font-medium mb-4 text-primary border-b border-gray-200 pb-2">Company Driver Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">Years of Experience *</label>
                            <select wire:model="company_driver_experience_years" class="w-full px-3 py-2 border rounded">
                                <option value="">Select Experience</option>
                                <option value="0-1">0-1 years</option>
                                <option value="2-5">2-5 years</option>
                                <option value="6-10">6-10 years</option>
                                <option value="11-15">11-15 years</option>
                                <option value="16+">16+ years</option>
                            </select>
                            @error('company_driver_experience_years')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">Schedule Preference *</label>
                            <select wire:model="company_driver_schedule_preference" class="w-full px-3 py-2 border rounded">
                                <option value="">Select Schedule</option>
                                <option value="local">Local (Home Daily)</option>
                                <option value="regional">Regional (Home Weekly)</option>
                                <option value="otr">OTR (Over The Road)</option>
                                <option value="dedicated">Dedicated Routes</option>
                            </select>
                            @error('company_driver_schedule_preference')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block mb-1 font-medium text-gray-700">Preferred Routes/Areas</label>
                        <textarea wire:model="company_driver_preferred_routes" 
                            class="w-full px-3 py-2 border rounded" 
                            rows="3" 
                            placeholder="Describe your preferred routes or areas you'd like to work in..."></textarea>
                        @error('company_driver_preferred_routes')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block mb-1 font-medium text-gray-700">Additional Certifications</label>
                        <textarea wire:model="company_driver_additional_certifications" 
                            class="w-full px-3 py-2 border rounded" 
                            rows="3" 
                            placeholder="List any additional certifications (HAZMAT, TWIC, etc.)"></textarea>
                        @error('company_driver_additional_certifications')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Company Vehicle Information -->
                    <div class="bg-green-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold text-green-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-.293-.707L15 4.586A1 1 0 0014.414 4H14v3z"></path>
                            </svg>
                            Company Vehicle Fleet
                        </h4>
                        <div class="text-sm text-green-700 mb-4">
                            <p class="mb-3">As a company driver, you'll have access to our modern, well-maintained fleet of vehicles. All vehicles are regularly serviced and equipped with the latest safety features.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-green-700">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Late Model Trucks (2018+)
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                GPS & ELD Systems
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Regular Maintenance
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                24/7 Roadside Assistance
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Fuel Cards Provided
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Safety Equipment Included
                            </div>
                        </div>
                    </div>

                    <!-- Company Driver Benefits -->
                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold text-blue-800 mb-3">Company Driver Benefits</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-700">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Competitive Pay
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Health Insurance
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Paid Time Off
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Retirement Plan
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Modern Equipment
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Performance Bonuses
                            </div>
                        </div>
                    </div>

                    <!-- Experience Level Guide -->
                    <div class="bg-gray-100 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold text-gray-800 mb-3">Experience Level Guide</h4>
                        <div class="text-sm text-gray-600 space-y-2">
                            <div><strong>0-1 years:</strong> New to commercial driving or recent CDL graduate</div>
                            <div><strong>2-5 years:</strong> Some commercial driving experience, familiar with basic operations</div>
                            <div><strong>6-10 years:</strong> Experienced driver with good safety record</div>
                            <div><strong>11-15 years:</strong> Veteran driver with extensive experience</div>
                            <div><strong>16+ years:</strong> Highly experienced professional driver</div>
                        </div>
                    </div>

                    <!-- Important Requirements -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Important Requirements</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Valid CDL Class A license required</li>
                                        <li>Clean driving record (last 3 years)</li>
                                        <li>Pass DOT physical and drug screening</li>
                                        <li>Minimum age requirement may apply</li>
                                        <li>Background check required</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
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