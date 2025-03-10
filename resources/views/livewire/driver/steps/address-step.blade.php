<div>
    <h2 class="text-xl font-bold mb-4">Address Information</h2>
    
    <!-- Current Address -->
    <div class="p-4 border rounded bg-gray-50 mb-6">
        <h3 class="text-lg font-medium mb-3">Current Address</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1">Address Line 1 *</label>
                <input type="text" wire:model="address_line1" class="w-full px-3 py-2 border rounded">
                @error('address_line1')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block mb-1">Address Line 2</label>
                <input type="text" wire:model="address_line2" class="w-full px-3 py-2 border rounded">
                @error('address_line2')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block mb-1">City *</label>
                <input type="text" wire:model="city" class="w-full px-3 py-2 border rounded">
                @error('city')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block mb-1">State *</label>
                <select wire:model="state" class="w-full px-3 py-2 border rounded">
                    <option value="">Select State</option>
                    @foreach ($usStates as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
                @error('state')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block mb-1">ZIP Code *</label>
                <input type="text" wire:model="zip_code" class="w-full px-3 py-2 border rounded">
                @error('zip_code')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block mb-1">From Date *</label>
                <input type="date" wire:model="from_date" class="w-full px-3 py-2 border rounded">
                @error('from_date')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label class="block mb-1">To Date</label>
                <input type="date" wire:model="to_date" class="w-full px-3 py-2 border rounded">
                @error('to_date')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
        
        <div class="mb-2">
            <label class="flex items-center">
                <input type="checkbox" wire:model="lived_three_years" class="mr-2">
                <span>I have lived at this address for 3 years or more</span>
            </label>
        </div>
    </div>
    
    <!-- Previous Addresses -->
    <div x-show="!$wire.lived_three_years" x-transition class="mb-6">
        <h3 class="text-lg font-medium mb-3">Previous Addresses</h3>
        <p class="text-sm text-gray-600 mb-4">Please provide address history covering at least 3 years</p>
        
        @foreach ($previous_addresses as $index => $address)
            <div class="p-4 border rounded bg-gray-50 mb-4">
                <div class="flex justify-between mb-2">
                    <h4 class="font-medium">Previous Address #{{ $index + 1 }}</h4>
                    @if (count($previous_addresses) > 1)
                        <button type="button" wire:click="removePreviousAddress({{ $index }})" class="text-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endif
                </div>
                
                <!-- Previous Address Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block mb-1">Address Line 1 *</label>
                        <input type="text" wire:model="previous_addresses.{{ $index }}.address_line1" class="w-full px-3 py-2 border rounded">
                        @error('previous_addresses.' . $index . '.address_line1')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1">Address Line 2</label>
                        <input type="text" wire:model="previous_addresses.{{ $index }}.address_line2" class="w-full px-3 py-2 border rounded">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block mb-1">City *</label>
                        <input type="text" wire:model="previous_addresses.{{ $index }}.city" class="w-full px-3 py-2 border rounded">
                        @error('previous_addresses.' . $index . '.city')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1">State *</label>
                        <select wire:model="previous_addresses.{{ $index }}.state" class="w-full px-3 py-2 border rounded">
                            <option value="">Select State</option>
                            @foreach ($usStates as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('previous_addresses.' . $index . '.state')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1">ZIP Code *</label>
                        <input type="text" wire:model="previous_addresses.{{ $index }}.zip_code" class="w-full px-3 py-2 border rounded">
                        @error('previous_addresses.' . $index . '.zip_code')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1">From Date *</label>
                        <input type="date" wire:model="previous_addresses.{{ $index }}.from_date" class="w-full px-3 py-2 border rounded">
                        @error('previous_addresses.' . $index . '.from_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1">To Date *</label>
                        <input type="date" wire:model="previous_addresses.{{ $index }}.to_date" class="w-full px-3 py-2 border rounded">
                        @error('previous_addresses.' . $index . '.to_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        @endforeach
        
        <button type="button" wire:click="addPreviousAddress" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            Add Previous Address
        </button>
    </div>
    
    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-8">
        <div>
            <button type="button" wire:click="previous" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                Previous
            </button>
        </div>
        <div class="flex space-x-2">
            <button type="button" wire:click="saveAndExit" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                Save & Exit
            </button>
            <button type="button" wire:click="next" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Next
            </button>
        </div>
    </div>
</div>