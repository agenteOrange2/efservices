<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">FMCSA Driver Medical Qualification</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Social Security Number -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Social Security Number <span
                    class="text-red-500">*</span></label>
            <input type="text" wire:model="social_security_number" placeholder="XXX-XX-XXXX"
                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" pattern="\d{3}-\d{2}-\d{4}"
                x-mask="999-99-9999">
            <p class="mt-1 text-xs text-gray-500">Format: XXX-XX-XXXX</p>
            @error('social_security_number')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Hire Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
            <input type="text" name="hire_date" wire:model="hire_date" value="{{ $hire_date }}" placeholder="MM/DD/YYYY" class="driver-datepicker w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
            @error('hire_date')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Location -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <input type="text" wire:model="location" placeholder="Work location"
                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Suspension Status -->
        <div x-data="{ isSuspended: {{ json_encode($is_suspended ?? false) }} }">
            <div class="flex items-center mb-2">
                <input type="checkbox" wire:model="is_suspended" id="is_suspended" value="1" x-model="isSuspended"
                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                <label for="is_suspended" class="ml-2 text-sm">Driver is Suspended</label>
            </div>
            <div x-show="isSuspended" class="mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Suspension Date</label>
                <input type="text" name="suspension_date" wire:model="suspension_date" value="{{ $suspension_date }}" placeholder="MM/DD/YYYY" class="driver-datepicker w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                @error('suspension_date')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Termination Status -->
        <div x-data="{ isTerminated: {{ json_encode($is_terminated ?? false) }} }">
            <div class="flex items-center mb-2">
                <input type="checkbox" wire:model="is_terminated" id="is_terminated" value="1"
                    x-model="isTerminated" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                <label for="is_terminated" class="ml-2 text-sm">Driver is Terminated</label>
            </div>
            <div x-show="isTerminated" class="mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Termination Date</label>
                <input type="text" name="termination_date" wire:model="termination_date" value="{{ $termination_date }}" placeholder="MM/DD/YYYY" class="driver-datepicker w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                @error('termination_date')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="border-t border-gray-200 pt-6 mt-6">
        <h4 class="font-medium text-gray-700 mb-4">Medical Certification Information</h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Medical Examiner Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Medical Examiner Name <span
                        class="text-red-500">*</span></label>
                <input type="text" wire:model="medical_examiner_name"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                @error('medical_examiner_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Medical Examiner Registry Number -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Medical Examiner Registry Number <span
                        class="text-red-500">*</span></label>
                <input type="text" wire:model="medical_examiner_registry_number"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                @error('medical_examiner_registry_number')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Medical Card Expiration Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Medical Card Expiration Date <span
                        class="text-red-500">*</span></label>
                <input type="text" name="medical_card_expiration_date" wire:model="medical_card_expiration_date" value="{{ $medical_card_expiration_date }}" placeholder="MM/DD/YYYY" class="driver-datepicker w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                @error('medical_card_expiration_date')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Medical Card Upload -->
        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Medical Card Upload</h3>
            
            @if(!empty($medicalQualificationId))
                <!-- Show upload component only when medical qualification record exists -->
                <x-unified-image-upload 
                    :existing-image-url="$medical_card_preview_url ?? ''"
                    :existing-image-name="$medical_card_filename ?? ''"
                    accept="image/*,application/pdf" 
                    max-size="10240" 
                    class="w-full"
                    :model-type="'medical_card'"
                    :model-id="$medicalQualificationId"
                    :driver-id="$driverId"
                    collection="medical_card"
                    document-type="medical_card"
                    path="driver/{{ $driverId }}/medical/"
                    remove-method="removeMedicalCard"
                    wire:key="medical-card-{{ $medicalQualificationId }}"
                />
            @else
                <!-- Show message when no medical qualification record exists yet -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="mt-4">
                            <span class="mt-2 block text-sm font-medium text-gray-600">
                                Please save medical information first to enable image upload
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Medical card upload will be available after saving the form
                        </p>
                        <div class="mt-4">
                            <x-base.button type="button" wire:click="saveMedicalInfo" class="px-6 py-2" variant="primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4z" />
                                </svg>
                                Save Medical Information
                            </x-base.button>
                        </div>
                    </div>
                </div>
            @endif
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
