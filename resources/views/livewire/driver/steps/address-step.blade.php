<div class="box box--stacked flex flex-col">
    <div class="flex items-center px-5 py-5 border-b border-slate-200/60 dark:border-darkmode-400">
        <h2 class="font-medium text-base mr-auto">Address Information</h2>
    </div>
    <div class="p-5">

        <!-- Current Address Section -->
        <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Current Address</div>
                    </div>
                    <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                        Enter your current residential address information.
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <div class="p-5 border rounded-md bg-slate-50/50 shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Address Line 1 <span
                                    class="text-red-500">*</span></label>
                            <x-base.form-input type="text" wire:model="address_line1"
                                class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                            @error('address_line1')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">Address Line 2</label>
                            <x-base.form-input type="text" wire:model="address_line2"
                                class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                            @error('address_line2')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">City <span
                                    class="text-red-500">*</span></label>
                            <x-base.form-input type="text" wire:model="city"
                                class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                            @error('city')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">State <span
                                    class="text-red-500">*</span></label>
                            <select wire:model="state"
                                class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
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
                            <label class="block mb-2 text-sm font-medium text-gray-700">ZIP Code <span
                                    class="text-red-500">*</span></label>
                            <x-base.form-input type="text" wire:model="zip_code"
                                class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                            @error('zip_code')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">From Date <span
                                    class="text-red-500">*</span></label>
                            <x-date-picker
                                wire:model="from_date"
                                id="from_date"
                                name="from_date"
                                value="{{ $from_date }}"
                                required
                            />
                            @error('from_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">To Date</label>
                            <x-date-picker
                                wire:model="to_date"
                                id="to_date"
                                name="to_date"
                                value="{{ $to_date }}"
                            />
                            @error('to_date')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>


                    <div class="flex items-center mt-4">
                        <x-base.form-check.input class="mr-2.5 border" type="checkbox" wire:model="lived_three_years" />
                        <span class="cursor-pointer select-none text-sm text-gray-700">
                            I have lived at this address for 3 years or more
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Previous Addresses Section -->
        <div x-show="!$wire.lived_three_years" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-start">
                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                    <div class="text-left">
                        <div class="flex items-center">
                            <div class="font-medium">Previous Addresses</div>
                        </div>
                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                            Please provide address history covering at least 3 years.
                        </div>
                    </div>
                </div>
                <div class="mt-3 w-full flex-1 xl:mt-0">

                    @foreach ($previous_addresses as $index => $address)
                        <div class="p-5 border rounded-md bg-slate-50/50 shadow-sm mb-5">
                            <div class="flex justify-between items-center mb-3 border-b border-slate-200/60 pb-3">
                                <h4 class="font-medium text-slate-600">Previous Address #{{ $index + 1 }}</h4>
                                @if (count($previous_addresses) > 1)
                                    <button type="button" wire:click="removePreviousAddress({{ $index }})"
                                        class="text-red-500 hover:text-red-600 transition duration-150 ease-in-out">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            <!-- Previous Address Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">Address Line 1 <span
                                            class="text-red-500">*</span></label>
                                    <x-base.form-input type="text"
                                        wire:model="previous_addresses.{{ $index }}.address_line1"
                                        class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                                    @error('previous_addresses.' . $index . '.address_line1')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">Address Line 2</label>
                                    <x-base.form-input type="text"
                                        wire:model="previous_addresses.{{ $index }}.address_line2"
                                        class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">City <span
                                            class="text-red-500">*</span></label>
                                    <x-base.form-input type="text"
                                        wire:model="previous_addresses.{{ $index }}.city"
                                        class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                                    @error('previous_addresses.' . $index . '.city')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">State <span
                                            class="text-red-500">*</span></label>
                                    <select wire:model="previous_addresses.{{ $index }}.state"
                                        class="form-select w-full rounded-md border border-slate-300/60 bg-white px-3 py-2 shadow-sm">
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
                                    <label class="block mb-2 text-sm font-medium text-gray-700">ZIP Code <span
                                            class="text-red-500">*</span></label>
                                    <x-base.form-input type="text"
                                        wire:model="previous_addresses.{{ $index }}.zip_code"
                                        class="form-control w-full rounded-md border border-slate-300/60 px-3 py-2 shadow-sm" />
                                    @error('previous_addresses.' . $index . '.zip_code')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">From Date <span
                                            class="text-red-500">*</span></label>
                                    <x-date-picker
                                        wire:model="previous_addresses.{{ $index }}.from_date"
                                        id="prev_from_date_{{ $index }}"
                                        name="previous_addresses[{{ $index }}][from_date]"
                                        value="{{ $address['from_date'] ?? '' }}"
                                        required
                                    />
                                    @error('previous_addresses.' . $index . '.from_date')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700">To Date <span
                                            class="text-red-500">*</span></label>
                                    <x-date-picker
                                        wire:model="previous_addresses.{{ $index }}.to_date"
                                        id="prev_to_date_{{ $index }}"
                                        name="previous_addresses[{{ $index }}][to_date]"
                                        value="{{ $address['to_date'] ?? '' }}"
                                        required
                                    />
                                    @error('previous_addresses.' . $index . '.to_date')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-4">
                        <x-base.button type="button" wire:click="addPreviousAddress" class="inline-block w-64"
                            variant="primary">
                            <i class="fas fa-plus mr-2"></i> Add Previous Address
                        </x-base.button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Navigation Buttons -->
        <div class="mt-8 px-5 py-5 border-t border-slate-200/60 dark:border-darkmode-400">
            <div class="flex flex-col sm:flex-row justify-between gap-4">
                <div class="w-full sm:w-auto">
                    <x-base.button type="button" wire:click="previous" class="w-full sm:w-44" variant="secondary">
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
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('process-temporary-photo', () => {
            // Get the temporary photo from sessionStorage
            const storageKey = 'driver_registration_photo';
            const photoData = sessionStorage.getItem(storageKey);
            
            if (photoData) {
                // Call the Livewire method to upload the photo
                @this.uploadTemporaryPhoto(photoData).then(() => {
                    // Clear the sessionStorage after successful upload
                    sessionStorage.removeItem(storageKey);
                    console.log('Temporary photo processed successfully');
                }).catch((error) => {
                    console.error('Error processing temporary photo:', error);
                });
            }
        });
    });
</script>


