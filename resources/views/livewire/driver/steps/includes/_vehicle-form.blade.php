{{-- Componente reutilizable para formulario de vehículos --}}
<div class="bg-white rounded-lg border border-gray-200 p-6 mb-4">
    <div class="flex items-center mb-4">
        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
        </svg>
        <h4 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Vehicle Information' }}</h4>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Make --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Make *</label>
            <input type="text" 
                   wire:model="vehicle_make" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Enter vehicle make">
            @error('vehicle_make')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Model --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Model *</label>
            <input type="text" 
                   wire:model="vehicle_model" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Enter vehicle model">
            @error('vehicle_model')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Year --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Year *</label>
            <input type="number" 
                   wire:model="vehicle_year" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Enter year" 
                   min="1900" 
                   max="{{ date('Y') + 1 }}">
            @error('vehicle_year')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- VIN --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">VIN *</label>
            <input type="text" 
                   wire:model="vehicle_vin" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Enter VIN number" 
                   maxlength="17">
            @error('vehicle_vin')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Company Unit Number --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Company Unit Number</label>
            <input type="text" 
                   wire:model="vehicle_company_unit_number" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Enter unit number">
            @error('vehicle_company_unit_number')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Type --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
            <select wire:model="vehicle_type" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Select vehicle type</option>
                <option value="truck">Truck</option>
                <option value="trailer">Trailer</option>
                <option value="other">Other</option>
            </select>
            @error('vehicle_type')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- GVWR --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">GVWR (lbs)</label>
            <input type="number" 
                   wire:model="vehicle_gvwr" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Enter GVWR">
            @error('vehicle_gvwr')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Tire Size --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tire Size</label>
            <input type="text" 
                   wire:model="vehicle_tire_size" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Enter tire size">
            @error('vehicle_tire_size')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Fuel Type --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fuel Type</label>
            <select wire:model="vehicle_fuel_type" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Select fuel type</option>
                <option value="diesel">Diesel</option>
                <option value="gasoline">Gasoline</option>
                <option value="electric">Electric</option>
                <option value="hybrid">Hybrid</option>
            </select>
            @error('vehicle_fuel_type')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- IRP Apportioned Plate --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">IRP Apportioned Plate</label>
            <div class="flex items-center">
                <input type="checkbox" 
                       wire:model="vehicle_irp_apportioned_plate" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                       id="vehicle_irp_checkbox">
                <label for="vehicle_irp_checkbox" class="ml-2 text-sm text-gray-700">Has IRP Apportioned Plate</label>
            </div>
            @error('vehicle_irp_apportioned_plate')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Registration State --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Registration State *</label>
            <div class="relative">
                <select wire:model="vehicle_registration_state" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                    <option value="">Select state</option>
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <option value="AZ">Arizona</option>
                    <option value="AR">Arkansas</option>
                    <option value="CA">California</option>
                    <option value="CO">Colorado</option>
                    <option value="CT">Connecticut</option>
                    <option value="DE">Delaware</option>
                    <option value="FL">Florida</option>
                    <option value="GA">Georgia</option>
                    <option value="HI">Hawaii</option>
                    <option value="ID">Idaho</option>
                    <option value="IL">Illinois</option>
                    <option value="IN">Indiana</option>
                    <option value="IA">Iowa</option>
                    <option value="KS">Kansas</option>
                    <option value="KY">Kentucky</option>
                    <option value="LA">Louisiana</option>
                    <option value="ME">Maine</option>
                    <option value="MD">Maryland</option>
                    <option value="MA">Massachusetts</option>
                    <option value="MI">Michigan</option>
                    <option value="MN">Minnesota</option>
                    <option value="MS">Mississippi</option>
                    <option value="MO">Missouri</option>
                    <option value="MT">Montana</option>
                    <option value="NE">Nebraska</option>
                    <option value="NV">Nevada</option>
                    <option value="NH">New Hampshire</option>
                    <option value="NJ">New Jersey</option>
                    <option value="NM">New Mexico</option>
                    <option value="NY">New York</option>
                    <option value="NC">North Carolina</option>
                    <option value="ND">North Dakota</option>
                    <option value="OH">Ohio</option>
                    <option value="OK">Oklahoma</option>
                    <option value="OR">Oregon</option>
                    <option value="PA">Pennsylvania</option>
                    <option value="RI">Rhode Island</option>
                    <option value="SC">South Carolina</option>
                    <option value="SD">South Dakota</option>
                    <option value="TN">Tennessee</option>
                    <option value="TX">Texas</option>
                    <option value="UT">Utah</option>
                    <option value="VT">Vermont</option>
                    <option value="VA">Virginia</option>
                    <option value="WA">Washington</option>
                    <option value="WV">West Virginia</option>
                    <option value="WI">Wisconsin</option>
                    <option value="WY">Wyoming</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
            @error('vehicle_registration_state')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Registration Number --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Registration Number *</label>
            <input type="text" 
                   wire:model="vehicle_registration_number" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Enter registration number">
            @error('vehicle_registration_number')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Registration Expiry Date --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Registration Expiry Date *</label>
            <x-base.litepicker 
                wire:model="vehicle_registration_expiration_date" 
                placeholder="MM/DD/YYYY"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            @error('vehicle_registration_expiration_date')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Permanent Tag --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Permanent Tag</label>
            <div class="flex items-center">
                <input type="checkbox" 
                       wire:model="vehicle_permanent_tag" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                       id="vehicle_permanent_tag_checkbox">
                <label for="vehicle_permanent_tag_checkbox" class="ml-2 text-sm text-gray-700">Has Permanent Tag</label>
            </div>
            @error('vehicle_permanent_tag')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>

        {{-- Location --}}
        <div class="w-full">
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <input type="text" 
                   wire:model="vehicle_location" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Enter location">
            @error('vehicle_location')
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Notes --}}
    <div class="mt-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea wire:model="vehicle_notes" 
                  rows="3" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter any additional notes about this vehicle"></textarea>
        @error('vehicle_notes')
            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
        @enderror
    </div>

    {{-- Action Buttons --}}
    @if(isset($showButtons) && $showButtons)
    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
        <button type="button" 
                wire:click="clearVehicleForm" 
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Clear Form
        </button>
        <button type="button" 
                wire:click="{{ $saveAction ?? 'saveVehicle' }}" 
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            {{ $saveButtonText ?? 'Save Vehicle' }}
        </button>
    </div>
    @endif
</div>