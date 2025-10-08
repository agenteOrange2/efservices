<div class="mt-6 p-6 border border-blue-100 rounded-lg bg-blue-50 shadow-inner">
    <h3 class="text-lg font-semibold mb-5 text-primary border-b border-blue-200 pb-3 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        Owner Operator Information
    </h3>

    <!-- Owner Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block mb-1">Owner Name *</label>
            <x-base.form-input type="text" wire:model="owner_name" class="w-full px-3 py-2 border rounded" />
            @error('owner_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">Phone Number *</label>
            <x-base.form-input type="number" wire:model="owner_phone" class="w-full px-3 py-2 border rounded" />
            @error('owner_phone')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="mb-4">
        <label class="block mb-1">Email *</label>
        <x-base.form-input type="email" wire:model="owner_email" class="w-full px-3 py-2 border rounded" />
        @error('owner_email')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Vehicle Information Section -->
    <h4 class="font-semibold text-lg text-primary mb-4 mt-8 border-b border-blue-200 pb-3 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
        </svg>
        Vehicle Information
    </h4>

    <!-- Existing Vehicles -->
    @if(isset($vehiclesByType['owner_operator']) && count($vehiclesByType['owner_operator']) > 0)
        <div class="mb-5 p-4 bg-blue-50 rounded-lg border border-blue-100 shadow-sm">
            <h5 class="font-medium mb-2">Existing Vehicles</h5>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Make</th>
                            <th class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                            <th class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                            <th class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VIN</th>
                            <th class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="py-2.5 px-4 border-b bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehiclesByType['owner_operator'] as $vehicle)
                            <tr class="{{ $selectedVehicleId == $vehicle->id ? 'bg-blue-100' : '' }}">
                                <td class="py-2 px-3 border-b">{{ $vehicle->make }}</td>
                                <td class="py-2 px-3 border-b">{{ $vehicle->model }}</td>
                                <td class="py-2 px-3 border-b">{{ $vehicle->year }}</td>
                                <td class="py-2 px-3 border-b">{{ $vehicle->vin }}</td>
                                <td class="py-2 px-3 border-b">{{ ucfirst($vehicle->type) }}</td>
                                <td class="py-2 px-3 border-b">
                                    <div class="flex space-x-2">
                                        <button type="button" wire:click="selectVehicle({{ $vehicle->id }})" class="px-2.5 py-1.5 bg-blue-500 text-white rounded-md text-sm hover:bg-primary transition duration-150 ease-in-out flex items-center">
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
                <button type="button" wire:click="clearVehicleForm" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition duration-150 ease-in-out flex items-center shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Register New Vehicle
                </button>
            </div>
        </div>
    @endif

    <!-- Vehicle Form -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block mb-1">Make *</label>
            <x-base.form-input type="text" wire:model="vehicle_make" class="w-full px-3 py-2 border rounded" />
            @error('vehicle_make')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">Model *</label>
            <x-base.form-input type="text" wire:model="vehicle_model" class="w-full px-3 py-2 border rounded" />
            @error('vehicle_model')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block mb-1">Year *</label>
            <x-base.form-input type="number" wire:model="vehicle_year" class="w-full px-3 py-2 border rounded" />
            @error('vehicle_year')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">VIN *</label>
            <x-base.form-input type="text" wire:model="vehicle_vin" class="w-full px-3 py-2 border rounded" />
            @error('vehicle_vin')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block mb-1">Company Unit Number</label>
            <x-base.form-input type="text" wire:model="vehicle_company_unit_number" class="w-full px-3 py-2 border rounded" />
            @error('vehicle_company_unit_number')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">Type *</label>
            <select wire:model="vehicle_type" class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
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
            <x-base.form-input type="text" wire:model="vehicle_gvwr" class="w-full px-3 py-2 border rounded" />
            @error('vehicle_gvwr')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">Tire Size</label>
            <x-base.form-input type="text" wire:model="vehicle_tire_size" class="w-full px-3 py-2 border rounded" />
            @error('vehicle_tire_size')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block mb-1">Fuel Type *</label>
            <select wire:model="vehicle_fuel_type" class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
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
                <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="vehicle_irp_apportioned_plate" wire:model="vehicle_irp_apportioned_plate" id="vehicle_irp_apportioned_plate_owner" />
                <label for="vehicle_irp_apportioned_plate_owner">IRP Apportioned Plate</label>
            </div>
            @error('vehicle_irp_apportioned_plate')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block mb-1">Registration State *</label>
            <select wire:model="vehicle_registration_state" class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
                <option value="">Select State</option>
                @foreach($usStates as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
            @error('vehicle_registration_state')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">Registration Number *</label>
            <x-base.form-input type="text" wire:model="vehicle_registration_number" class="w-full px-3 py-2 border rounded" />
            @error('vehicle_registration_number')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block mb-1">Registration Expiration Date *</label>
            <input type="text" name="vehicle_registration_expiration_date" wire:model="vehicle_registration_expiration_date" class="driver-datepicker w-full px-3 py-2 border rounded" placeholder="MM/DD/YYYY" value="{{ $vehicle_registration_expiration_date }}" />
            @error('vehicle_registration_expiration_date')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">Permanent Tag</label>
            <div class="flex items-center mt-2">
                <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="vehicle_permanent_tag_owner" wire:model="vehicle_permanent_tag" id="vehicle_permanent_tag_owner" />
                <label for="vehicle_permanent_tag_owner">Permanent Tag</label>
            </div>
            @error('vehicle_permanent_tag')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="mb-4">
        <label class="block mb-1">Location</label>
        <x-base.form-input type="text" wire:model="vehicle_location" class="w-full px-3 py-2 border rounded" />
        @error('vehicle_location')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <div class="mb-4">
        <label class="block mb-1">Notes</label>
        <x-base.form-textarea wire:model="vehicle_notes" class="w-full px-3 py-2 border rounded" rows="3" />
        @error('vehicle_notes')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Contract Agreement -->
    <div class="mt-5 p-4 bg-amber-50 rounded-lg border border-amber-100 shadow-sm">
        <p class="mb-3">By checking this box, I agree to the terms and conditions of the contract. I understand that I am responsible for maintaining my vehicle according to company standards and complying with all applicable regulations.</p>
        <p class="mb-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl.</p>
        <p class="mb-3">Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl. Nullam auctor, nisl eget ultricies tincidunt, nisl nisl aliquam nisl, eget ultricies nisl nisl eget nisl.</p>

        <div class="flex items-center">
            <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="contract_agreed" wire:model="contract_agreed" id="contract_agreed" />
            <label for="contract_agreed" class="cursor-pointer">I Agree to the Terms and Conditions *</label>
        </div>
        @error('contract_agreed')
            <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
        @enderror
    </div>
</div>