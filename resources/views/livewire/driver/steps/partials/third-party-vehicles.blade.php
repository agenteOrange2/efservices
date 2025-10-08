<div class="mt-6 p-6 border border-green-100 rounded-lg bg-green-50 shadow-inner">
    <h3 class="text-lg font-semibold mb-5 text-primary border-b border-green-200 pb-3 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        Third Party Company Driver Information
    </h3>

    <!-- Company Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block mb-1">Company Representative Name *</label>
            <x-base.form-input type="text" wire:model="third_party_representative_name" class="w-full px-3 py-2 border rounded" />
            @error('third_party_representative_name')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">Phone Number *</label>
            <x-base.form-input type="number" wire:model="third_party_phone_number" class="w-full px-3 py-2 border rounded" />
            @error('third_party_phone_number')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block mb-1">Email *</label>
            <x-base.form-input type="email" wire:model="third_party_email" class="w-full px-3 py-2 border rounded" />
            @error('third_party_email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">DBA</label>
            <x-base.form-input type="text" wire:model="third_party_dba" class="w-full px-3 py-2 border rounded" />
            @error('third_party_dba')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label class="block mb-1">Contact Person</label>
            <x-base.form-input type="text" wire:model="third_party_contact" class="w-full px-3 py-2 border rounded" />
            @error('third_party_contact')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <div>
            <label class="block mb-1">FEIN</label>
            <x-base.form-input type="text" wire:model="third_party_fein" class="w-full px-3 py-2 border rounded" />
            @error('third_party_fein')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="mb-4">
        <label class="block mb-1">Address</label>
        <x-base.form-textarea wire:model="third_party_address" class="w-full px-3 py-2 border rounded" rows="3" />
        @error('third_party_address')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Vehicle Information Section -->
    <h4 class="font-semibold text-lg text-primary mb-4 mt-8 border-b border-green-200 pb-3 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
        </svg>
        Vehicle Information
    </h4>

    <!-- Existing Vehicles -->
    @if(isset($vehiclesByType['third_party_driver']) && count($vehiclesByType['third_party_driver']) > 0)
        <div class="mb-5 p-4 bg-green-50 rounded-lg border border-green-100 shadow-sm">
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
                        @foreach($vehiclesByType['third_party_driver'] as $vehicle)
                            <tr class="{{ $selectedVehicleId == $vehicle->id ? 'bg-green-100' : '' }}">
                                <td class="py-2 px-3 border-b">{{ $vehicle->make }}</td>
                                <td class="py-2 px-3 border-b">{{ $vehicle->model }}</td>
                                <td class="py-2 px-3 border-b">{{ $vehicle->year }}</td>
                                <td class="py-2 px-3 border-b">{{ $vehicle->vin }}</td>
                                <td class="py-2 px-3 border-b">{{ ucfirst($vehicle->type) }}</td>
                                <td class="py-2 px-3 border-b">
                                    <div class="flex space-x-2">
                                        <button type="button" wire:click="selectVehicle({{ $vehicle->id }})" class="px-2.5 py-1.5 bg-green-500 text-white rounded-md text-sm hover:bg-green-600 transition duration-150 ease-in-out flex items-center">
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
                <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="vehicle_irp_apportioned_plate" wire:model="vehicle_irp_apportioned_plate" id="vehicle_irp_apportioned_plate_third_party" />
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
                <x-base.form-check.input class="mr-2.5 border" type="checkbox" name="vehicle_permanent_tag_third_party" wire:model="vehicle_permanent_tag" id="vehicle_permanent_tag_third_party" />
                <label for="vehicle_permanent_tag_third_party">Permanent Tag</label>
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

    <!-- Document Signing Buttons -->
    <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-100 shadow-sm">
        <h5 class="font-medium mb-3 text-yellow-800">Document Signing</h5>
        <div class="flex space-x-3">
            <button type="button" wire:click="sendDocumentSigningRequest" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-150 ease-in-out flex items-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                Send Document Signing Request
            </button>
            <button type="button" wire:click="resendEmail" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-150 ease-in-out flex items-center shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Resend Email
            </button>
        </div>
    </div>
</div>