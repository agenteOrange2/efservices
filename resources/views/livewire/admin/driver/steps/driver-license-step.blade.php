<div class="bg-white p-4 rounded-lg shadow mb-6">
    <h3 class="text-lg font-semibold mb-4">Driver's License Information</h3>
    <div class="mb-6 border-b pb-4">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-medium text-gray-700">License Details</h4>
            <button type="button" wire:click="addLicense"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-plus mr-1"></i> Add Another License
            </button>
        </div>

        <!-- Current License Number -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Current License Number <span
                    class="text-red-500">*</span></label>
            <input type="number" wire:model="current_license_number" class="w-full px-3 py-2 border rounded">
            @error('current_license_number')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- License entries -->
        @foreach ($licenses as $index => $license)
            <div class="license-entry border rounded-lg p-4 mb-4">
                <!-- Header with Remove Button (if not the first) -->
                <div class="flex justify-between items-center mb-4">
                    <h5 class="font-medium text-gray-600">License #{{ $index + 1 }}</h5>
                    @if ($index > 0)
                        <button type="button" wire:click="removeLicense({{ $index }})"
                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                            Remove
                        </button>
                    @endif
                </div>

                <!-- License Number & State -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            License Number <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model="licenses.{{ $index }}.license_number"
                            placeholder="Enter license number"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error("licenses.{$index}.license_number")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            State of Issue <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="licenses.{{ $index }}.state_of_issue"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                            <option value="">Select State</option>
                            @foreach ($usStates as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error("licenses.{$index}.state_of_issue")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- License Class & Expiration Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            License Class <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="licenses.{{ $index }}.license_class"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                            <option value="">Select Class</option>
                            <option value="A">Class A</option>
                            <option value="B">Class B</option>
                            <option value="C">Class C</option>
                        </select>
                        @error("licenses.{$index}.license_class")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Expiration Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" wire:model="licenses.{{ $index }}.expiration_date"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error("licenses.{$index}.expiration_date")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- CDL Checkbox -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" wire:model.live="licenses.{{ $index }}.is_cdl"
                            id="is_cdl_{{ $index }}"
                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                        <label for="is_cdl_{{ $index }}" class="ml-2 text-sm">This is a Commercial Driver's
                            License (CDL)</label>
                    </div>
                </div>

                <!-- Endorsements (visible when CDL is checked) -->
                @if ($license['is_cdl'])
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Endorsements</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_n_{{ $index }}" value="N"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_n_{{ $index }}" class="ml-2 text-sm">N (Tank)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_h_{{ $index }}" value="H"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_h_{{ $index }}" class="ml-2 text-sm">H (HAZMAT)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_x_{{ $index }}" value="X"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_x_{{ $index }}" class="ml-2 text-sm">X (Combo)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_t_{{ $index }}" value="T"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_t_{{ $index }}" class="ml-2 text-sm">T
                                    (Double/Triple)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_p_{{ $index }}" value="P"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_p_{{ $index }}" class="ml-2 text-sm">P
                                    (Passenger)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="endorsement_s_{{ $index }}" value="S"
                                    wire:model="licenses.{{ $index }}.endorsements"
                                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                                <label for="endorsement_s_{{ $index }}" class="ml-2 text-sm">S (School
                                    Bus)</label>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- License Images -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div x-data="{
                        preview: '{{ $license['front_preview'] ?? '' }}',
                        filename: '{{ $license['front_filename'] ?? '' }}',
                        loading: false,
                        error: ''
                    }">
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Front Image</label>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="$refs.license_front.click()"
                                class="px-3 py-2 bg-gray-200 rounded cursor-pointer hover:bg-gray-300 text-sm"
                                :disabled="loading">
                                <span x-show="!loading">Select Image</span>
                                <span x-show="loading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-primary"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Uploading...
                                </span>
                            </button>
                            <input type="file" x-ref="license_front" class="hidden" accept="image/*"
                                @change="
                                const file = $event.target.files[0];
                                if(!file) return;
                                if(file.size > 2 * 1024 * 1024) {
                                    error = 'File size must be less than 2MB';
                                    $event.target.value = '';
                                    return;
                                }
                                loading = true;
                                filename = file.name;
                                if(file.type.startsWith('image/')) {
                                    preview = URL.createObjectURL(file);
                                }
                                const formData = new FormData();
                                formData.append('file', file);
                                formData.append('type', 'license_front');
                                fetch('{{ route('admin.temp.upload') }}', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(response => response.json())
                                .then(result => {
                                    @this.set('licenses.{{ $index }}.temp_front_token', result.token);
                                    @this.set('licenses.{{ $index }}.front_preview', preview);
                                    @this.set('licenses.{{ $index }}.front_filename', filename);
                                    loading = false;
                                    error = '';
                                })
                                .catch(err => {
                                    console.error('Error uploading:', err);
                                    error = 'Failed to upload image';
                                    loading = false;
                                });
                            ">
                            <span x-text="filename" class="text-sm text-gray-600 truncate max-w-[100px]"></span>
                            <button type="button" x-show="filename"
                                @click="
                                preview = '';
                                filename = '';
                                @this.set('licenses.{{ $index }}.temp_front_token', '');
                                @this.set('licenses.{{ $index }}.front_preview', '');
                                @this.set('licenses.{{ $index }}.front_filename', '');
                                $refs.license_front.value = '';
                                error = '';
                            "
                                class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div x-show="preview" class="mt-2">
                            <img :src="preview" class="h-32 object-contain border rounded"
                                alt="License Front Preview" />
                        </div>
                        <p x-show="error" x-text="error" class="text-red-500 text-sm mt-1"></p>
                    </div>

                    <!-- License Back Image -->
                    <div x-data="{
                        preview: '{{ $license['back_preview'] ?? '' }}',
                        filename: '{{ $license['back_filename'] ?? '' }}',
                        loading: false,
                        error: ''
                    }">
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Back Image</label>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="$refs.license_back.click()"
                                class="px-3 py-2 bg-gray-200 rounded cursor-pointer hover:bg-gray-300 text-sm"
                                :disabled="loading">
                                <span x-show="!loading">Select Image</span>
                                <span x-show="loading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-primary"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Uploading...
                                </span>
                            </button>
                            <input type="file" x-ref="license_back" class="hidden" accept="image/*"
                                @change="
                                const file = $event.target.files[0];
                                if(!file) return;
                                if(file.size > 2 * 1024 * 1024) {
                                    error = 'File size must be less than 2MB';
                                    $event.target.value = '';
                                    return;
                                }
                                loading = true;
                                filename = file.name;
                                if(file.type.startsWith('image/')) {
                                    preview = URL.createObjectURL(file);
                                }
                                const formData = new FormData();
                                formData.append('file', file);
                                formData.append('type', 'license_back');
                                fetch('{{ route('admin.temp.upload') }}', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(response => response.json())
                                .then(result => {
                                    @this.set('licenses.{{ $index }}.temp_back_token', result.token);
                                    @this.set('licenses.{{ $index }}.back_preview', preview);
                                    @this.set('licenses.{{ $index }}.back_filename', filename);
                                    loading = false;
                                    error = '';
                                })
                                .catch(err => {
                                    console.error('Error uploading:', err);
                                    error = 'Failed to upload image';
                                    loading = false;
                                });
                            ">
                            <span x-text="filename" class="text-sm text-gray-600 truncate max-w-[100px]"></span>
                            <button type="button" x-show="filename"
                                @click="
                                preview = '';
                                filename = '';
                                @this.set('licenses.{{ $index }}.temp_back_token', '');
                                @this.set('licenses.{{ $index }}.back_preview', '');
                                @this.set('licenses.{{ $index }}.back_filename', '');
                                $refs.license_back.value = '';
                                error = '';
                            "
                                class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <div x-show="preview" class="mt-2">
                            <img :src="preview" class="h-32 object-contain border rounded"
                                alt="License Back Preview" />
                        </div>
                        <p x-show="error" x-text="error" class="text-red-500 text-sm mt-1"></p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Driving Experience Section -->
    <div class="mb-6 pb-4">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-medium text-gray-700">Driving Experience</h4>
            <button type="button" wire:click="addExperience"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-plus mr-1"></i> Add Another Vehicle
            </button>
        </div>

        @foreach ($experiences as $index => $experience)
            <div class="experience-entry border rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="font-medium text-gray-600">Vehicle #{{ $index + 1 }}</h5>
                    @if ($index > 0)
                        <button type="button" wire:click="removeExperience({{ $index }})"
                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                            Remove
                        </button>
                    @endif
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Equipment Type <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="experiences.{{ $index }}.equipment_type"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                            <option value="">Select Equipment Type</option>
                            <option value="Straight Truck">Straight Truck</option>
                            <option value="Tractor & Semi-Trailer">Tractor & Semi-Trailer</option>
                            <option value="Tractor & Two Trailers">Tractor & Two Trailers</option>
                            <option value="Tractor & Triple Trailers">Tractor & Triple Trailers</option>
                            <option value="Other">Other</option>
                        </select>
                        @error("experiences.{$index}.equipment_type")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Years of Experience <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model="experiences.{{ $index }}.years_experience"
                            min="0" max="10" step="1"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error("experiences.{$index}.years_experience")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                        <span class="text-xs text-gray-500">Maximum 10 years</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Total Miles Driven <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model="experiences.{{ $index }}.miles_driven"
                            min="0" step="1"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error("experiences.{$index}.miles_driven")
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <div class="h-8"></div> <!-- Spacer -->
                        <div class="flex items-center">
                            <input type="checkbox" id="requires_cdl_{{ $index }}"
                                wire:model="experiences.{{ $index }}.requires_cdl"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label for="requires_cdl_{{ $index }}" class="ml-2 text-sm">
                                This vehicle requires a CDL
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Navigation Buttons -->
    <div class="flex justify-between mt-8">
        <div>
            <button type="button" wire:click="previous" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                Previous
            </button>
        </div>
        <div class="flex space-x-2">
            <button type="button" wire:click="saveAndExit"
                class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                Save & Exit
            </button>
            <button type="button" wire:click="next"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Next
            </button>
        </div>
    </div>
</div>
