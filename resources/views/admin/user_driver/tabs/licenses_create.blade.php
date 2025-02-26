<!-- TAB: LICENSES -->

{{-- <div class="bg-white p-4 ">
    <h3 class="text-lg font-semibold mb-4">Driver's License Information</h3>

    <div class="mb-6 border-b pb-4">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-medium text-gray-700">License Details</h4>
            <x-base.button type="button" @click="addLicense()"  variant="primary">
                <x-base.lucide class="mr-2 h-4 w-4" icon="Activity" />
                Add Another License
            </x-base.button>
        </div>

        <!-- Current License Number -->
        <div class="my-8 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Current License Number</div>
                        <div class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <x-base.form-input name="current_license_number" type="text"
                    placeholder="Enter Current license number" value="{{ old('current_license_number') }}" />
                @error('current_license_number')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Licenses Entries -->
        <template x-for="(license, index) in licenses" :key="index">
            <div class="license-entry border rounded-lg p-4 mb-4">
                <!-- Header with Remove Button (if not the first) -->
                <div class="flex justify-between items-center mb-4">
                    <h5 class="font-medium text-gray-600" x-text="'License #' + (index + 1)"></h5>
                    <button type="button" x-show="index > 0" @click="removeLicense(index)"
                        class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                        Remove
                    </button>
                </div>

                <!-- License Number & State -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Number <span
                                class="text-red-500">*</span></label>
                        <input type="text" :name="`licenses[${index}][license_number]`" x-model="license.license_number"
                            placeholder="Enter license number"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 focus:ring-primary focus:border-primary">
                        @error('licenses.*.license_number')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State of Issue <span
                                class="text-red-500">*</span></label>
                        <select :name="`licenses[${index}][state_of_issue]`" x-model="license.state_of_issue"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-primary">
                            <option value="">Select State</option>
                            @foreach ($usStates as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('licenses.*.state_of_issue')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- License Class & Expiration Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Class <span
                                class="text-red-500">*</span></label>
                        <select :name="`licenses[${index}][license_class]`" x-model="license.license_class"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">Select Class</option>
                            <option value="A">Class A</option>
                            <option value="B">Class B</option>
                            <option value="C">Class C</option>
                        </select>
                        @error('licenses.*.license_class')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date <span
                                class="text-red-500">*</span></label>
                        <input type="date" :name="`licenses[${index}][expiration_date]`" x-model="license.expiration_date"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error('licenses.*.expiration_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- CDL Checkbox -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" :name="`licenses[${index}][is_cdl]`" :id="`is_cdl_${index}`" value="1"
                        :checked="license.is_cdl"
                        @change="license.is_cdl = $event.target.checked"
                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                        <label :for="`is_cdl_${index}`" class="ml-2 text-sm">This is a Commercial Driver's License (CDL)</label>
                    </div>
                </div>

                <!-- Endorsements (visible when CDL is checked) -->
                <div class="mb-4" x-show="license.is_cdl">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Endorsements</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" :name="`licenses[${index}][endorsements][]`" value="N"
                                :id="`endorsement_n_${index}`" @click="toggleEndorsement(index, 'N')"
                                :checked="checkEndorsement(index, 'N')"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_n_${index}`" class="ml-2 text-sm">N (Tank)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" :name="`licenses[${index}][endorsements][]`" value="H"
                                :id="`endorsement_h_${index}`" @click="toggleEndorsement(index, 'H')"
                                :checked="checkEndorsement(index, 'H')"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_h_${index}`" class="ml-2 text-sm">H (HAZMAT)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" :name="`licenses[${index}][endorsements][]`" value="X"
                                :id="`endorsement_x_${index}`" @click="toggleEndorsement(index, 'X')"
                                :checked="checkEndorsement(index, 'X')"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_x_${index}`" class="ml-2 text-sm">X (Combo)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" :name="`licenses[${index}][endorsements][]`" value="T"
                                :id="`endorsement_t_${index}`" @click="toggleEndorsement(index, 'T')"
                                :checked="checkEndorsement(index, 'T')"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_t_${index}`" class="ml-2 text-sm">T (Double/Triple)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" :name="`licenses[${index}][endorsements][]`" value="P"
                                :id="`endorsement_p_${index}`" @click="toggleEndorsement(index, 'P')"
                                :checked="checkEndorsement(index, 'P')"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_p_${index}`" class="ml-2 text-sm">P (Passenger)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" :name="`licenses[${index}][endorsements][]`" value="S"
                                :id="`endorsement_s_${index}`" @click="toggleEndorsement(index, 'S')"
                                :checked="checkEndorsement(index, 'S')"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_s_${index}`" class="ml-2 text-sm">S (School Bus)</label>
                        </div>
                    </div>
                </div>

                <!-- License Images -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div x-data="{ 
                        preview: licenses[index].front_preview || '', 
                        filename: licenses[index].front_filename || '', 
                        loading: false, 
                        error: '' 
                    }">
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Front Image</label>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="$refs.license_front.click()" 
                                class="px-3 py-2 bg-gray-200 rounded cursor-pointer hover:bg-gray-300 text-sm">
                                <span x-show="!loading">Select Image</span>
                                <span x-show="loading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Uploading...
                                </span>
                            </button>
                            <input type="file" x-ref="license_front" class="hidden" accept="image/*"
                                @change="
                                    const file = $event.target.files[0];
                                    if(!file) return;
                                    
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
                                    .then(response => {
                                        if(!response.ok) throw new Error('Upload failed');
                                        return response.json();
                                    })
                                    .then(result => {
                                        licenses[index].temp_front_token = result.token;
                                        licenses[index].front_preview = preview;
                                        licenses[index].front_filename = filename;
                                        loading = false;
                                    })
                                    .catch(err => {
                                        console.error('Error uploading:', err);
                                        error = 'Failed to upload image';
                                        loading = false;
                                    });
                                ">
                            
                            <span x-text="filename" class="text-sm text-gray-600"></span>
                            
                            <button type="button" x-show="filename" @click="
                                preview = '';
                                filename = '';
                                licenses[index].temp_front_token = '';
                                licenses[index].front_preview = '';
                                licenses[index].front_filename = '';
                                $refs.license_front.value = '';
                            " class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        
                        <input type="hidden" :name="`licenses[${index}][temp_front_token]`" x-model="licenses[index].temp_front_token">
                        
                        <div x-show="preview" class="mt-2">
                            <img :src="preview" class="h-32 object-contain border rounded" />
                        </div>
                        
                        <p x-show="error" x-text="error" class="text-red-500 text-sm mt-1"></p>
                    </div>
                
                    <!-- License Back Image - Estructura similar a la imagen frontal -->
                    <div x-data="{ 
                        preview: licenses[index].back_preview || '', 
                        filename: licenses[index].back_filename || '', 
                        loading: false, 
                        error: '' 
                    }">
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Back Image</label>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="$refs.license_back.click()" 
                                class="px-3 py-2 bg-gray-200 rounded cursor-pointer hover:bg-gray-300 text-sm">
                                <span x-show="!loading">Select Image</span>
                                <span x-show="loading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Uploading...
                                </span>
                            </button>
                            <input type="file" x-ref="license_back" class="hidden" accept="image/*"
                                @change="
                                    const file = $event.target.files[0];
                                    if(!file) return;
                                    
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
                                    .then(response => {
                                        if(!response.ok) throw new Error('Upload failed');
                                        return response.json();
                                    })
                                    .then(result => {
                                        licenses[index].temp_back_token = result.token;
                                        licenses[index].back_preview = preview;
                                        licenses[index].back_filename = filename;
                                        loading = false;
                                    })
                                    .catch(err => {
                                        console.error('Error uploading:', err);
                                        error = 'Failed to upload image';
                                        loading = false;
                                    });
                                ">
                            
                            <span x-text="filename" class="text-sm text-gray-600"></span>
                            
                            <button type="button" x-show="filename" @click="
                                preview = '';
                                filename = '';
                                licenses[index].temp_back_token = '';
                                licenses[index].back_preview = '';
                                licenses[index].back_filename = '';
                                $refs.license_back.value = '';
                            " class="text-red-500 hover:text-red-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        
                        <input type="hidden" :name="`licenses[${index}][temp_back_token]`" x-model="licenses[index].temp_back_token">
                        
                        <div x-show="preview" class="mt-2">
                            <img :src="preview" class="h-32 object-contain border rounded" />
                        </div>
                        
                        <p x-show="error" x-text="error" class="text-red-500 text-sm mt-1"></p>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Driving Experience Section -->
    <div class="mb-6  pb-4">
        <h4 class="font-medium text-gray-700 mb-4">Driving Experience</h4>

        <template x-for="(experience, index) in experiences" :key="index">
            <div class="experience-entry border rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="font-medium text-gray-600" x-text="'Vehicle #' + (index + 1)"></h5>
                    <div class="flex">
                        <x-base.button type="button" @click="addExperience()" variant="primary">
                            <x-base.lucide class="mr-2 h-4 w-4" icon="Activity" />
                            Add Another Vehicle
                        </x-base.button>
                        <button type="button" x-show="index > 0" @click="removeExperience(index)"
                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm ml-2">
                            Remove
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Equipment Type</label>
                        <select :name="`experiences[${index}][equipment_type]`" x-model="experience.equipment_type"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">Select Equipment Type</option>
                            <option value="Straight Truck">Straight Truck</option>
                            <option value="Tractor & Semi-Trailer">Tractor & Semi-Trailer</option>
                            <option value="Tractor & Two Trailers">Tractor & Two Trailers</option>
                            <option value="Tractor & Triple Trailers">Tractor & Triple Trailers</option>
                            <option value="Other">Other</option>
                        </select>
                        @error('experiences.*.equipment_type')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Years of Experience</label>
                        <input type="number" :name="`experiences[${index}][years_experience]`" 
                            x-model="experience.years_experience" min="0" max="10" step="1"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error('experiences.*.years_experience')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                        <span class="text-xs text-gray-500">Maximum 10 years</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Miles Driven</label>
                        <input type="number" :name="`experiences[${index}][miles_driven]`" 
                            x-model="experience.miles_driven" min="0" step="1"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error('experiences.*.miles_driven')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <div class="h-8"></div> <!-- Spacer -->
                        <div class="flex items-center">
                            <input type="checkbox" :name="`experiences[${index}][requires_cdl]`" value="1"
                                :id="`requires_cdl_${index}`" x-model="experience.requires_cdl"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`requires_cdl_${index}`" class="ml-2 text-sm">This vehicle requires a CDL</label>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div> --}}

<!-- TAB: LICENSES -->

<div class="bg-white p-4 ">
    <h3 class="text-lg font-semibold mb-4">Driver's License Information</h3>

    <div class="mb-6 border-b pb-4">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-medium text-gray-700">License Details</h4>
            <x-base.button type="button" @click="addLicense()" variant="primary">
                <x-base.lucide class="mr-2 h-4 w-4" icon="Activity" />
                Add Another License
            </x-base.button>
        </div>

        <!-- Current License Number -->
        <div class="my-8 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
            <div class="mb-2 sm:mb-0 sm:mr-5 xl:mr-14 xl:w-60">
                <div class="text-left">
                    <div class="flex items-center">
                        <div class="font-medium">Current License Number</div>
                        <div class="ml-2.5 rounded-md border bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                            Required
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 w-full flex-1 xl:mt-0">
                <x-base.form-input name="current_license_number" type="text"
                    placeholder="Enter Current license number" value="{{ old('current_license_number') }}" 
                    aria-required="true" />
                @error('current_license_number')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Licenses Entries -->
        <template x-for="(license, index) in licenses" :key="index">
            <div class="license-entry border rounded-lg p-4 mb-4">
                <!-- Header with Remove Button (if not the first) -->
                <div class="flex justify-between items-center mb-4">
                    <h5 class="font-medium text-gray-600" x-text="'License #' + (index + 1)"></h5>
                    <button type="button" x-show="index > 0" @click="removeLicense(index)"
                        class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
                        aria-label="Remove license">
                        Remove
                    </button>
                </div>

                <!-- License Number & State -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" :for="`license_number_${index}`">
                            License Number <span class="text-red-500">*</span>
                        </label>
                        <input type="text" :id="`license_number_${index}`" :name="`licenses[${index}][license_number]`" 
                            x-model="license.license_number"
                            placeholder="Enter license number"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 focus:ring-primary focus:border-primary"
                            aria-required="true">
                        @error('licenses.*.license_number')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" :for="`state_of_issue_${index}`">
                            State of Issue <span class="text-red-500">*</span>
                        </label>
                        <select :id="`state_of_issue_${index}`" :name="`licenses[${index}][state_of_issue]`" 
                            x-model="license.state_of_issue"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-primary"
                            aria-required="true">
                            <option value="">Select State</option>
                            @foreach ($usStates as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('licenses.*.state_of_issue')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- License Class & Expiration Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" :for="`license_class_${index}`">
                            License Class <span class="text-red-500">*</span>
                        </label>
                        <select :id="`license_class_${index}`" :name="`licenses[${index}][license_class]`" 
                            x-model="license.license_class"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8"
                            aria-required="true">
                            <option value="">Select Class</option>
                            <option value="A">Class A</option>
                            <option value="B">Class B</option>
                            <option value="C">Class C</option>
                        </select>
                        @error('licenses.*.license_class')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" :for="`expiration_date_${index}`">
                            Expiration Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" :id="`expiration_date_${index}`" :name="`licenses[${index}][expiration_date]`" 
                            x-model="license.expiration_date"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                            aria-required="true">
                        @error('licenses.*.expiration_date')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- CDL Checkbox -->
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" :id="`is_cdl_${index}`" :name="`licenses[${index}][is_cdl]`" value="1"
                            x-model="license.is_cdl"
                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                        <label :for="`is_cdl_${index}`" class="ml-2 text-sm">This is a Commercial Driver's License (CDL)</label>
                    </div>
                </div>

                <!-- Endorsements (visible when CDL is checked) -->
                <div class="mb-4" x-show="license.is_cdl">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Endorsements</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <input type="checkbox" :id="`endorsement_n_${index}`" value="N"
                                x-model="license.endorsements" 
                                :name="`licenses[${index}][endorsements][]`"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_n_${index}`" class="ml-2 text-sm">N (Tank)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" :id="`endorsement_h_${index}`" value="H"
                                x-model="license.endorsements" 
                                :name="`licenses[${index}][endorsements][]`"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_h_${index}`" class="ml-2 text-sm">H (HAZMAT)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" :id="`endorsement_x_${index}`" value="X"
                                x-model="license.endorsements" 
                                :name="`licenses[${index}][endorsements][]`"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_x_${index}`" class="ml-2 text-sm">X (Combo)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" :id="`endorsement_t_${index}`" value="T"
                                x-model="license.endorsements" 
                                :name="`licenses[${index}][endorsements][]`"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_t_${index}`" class="ml-2 text-sm">T (Double/Triple)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" :id="`endorsement_p_${index}`" value="P"
                                x-model="license.endorsements" 
                                :name="`licenses[${index}][endorsements][]`"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_p_${index}`" class="ml-2 text-sm">P (Passenger)</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" :id="`endorsement_s_${index}`" value="S"
                                x-model="license.endorsements" 
                                :name="`licenses[${index}][endorsements][]`"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`endorsement_s_${index}`" class="ml-2 text-sm">S (School Bus)</label>
                        </div>
                    </div>
                </div>

                <!-- License Images -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div x-data="{ 
                        preview: license.front_preview || '', 
                        filename: license.front_filename || '', 
                        loading: false, 
                        error: '' 
                    }">
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Front Image</label>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="$refs.license_front.click()" 
                                class="px-3 py-2 bg-gray-200 rounded cursor-pointer hover:bg-gray-300 text-sm"
                                :disabled="loading"
                                aria-label="Upload license front image">
                                <span x-show="!loading">Select Image</span>
                                <span x-show="loading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Uploading...
                                </span>
                            </button>
                            <input type="file" x-ref="license_front" class="hidden" accept="image/*" 
                                @change="
                                    const file = $event.target.files[0];
                                    if(!file) return;
                                    
                                    // Validate file size (max 2MB)
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
                                    .then(response => {
                                        if(!response.ok) throw new Error('Upload failed');
                                        return response.json();
                                    })
                                    .then(result => {
                                        licenses[index].temp_front_token = result.token;
                                        licenses[index].front_preview = preview;
                                        licenses[index].front_filename = filename;
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
                            
                            <button type="button" x-show="filename" @click="
                                preview = '';
                                filename = '';
                                licenses[index].temp_front_token = '';
                                licenses[index].front_preview = '';
                                licenses[index].front_filename = '';
                                $refs.license_front.value = '';
                                error = '';
                            " class="text-red-500 hover:text-red-700" aria-label="Remove license front image">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        
                        <input type="hidden" :name="`licenses[${index}][temp_front_token]`" x-model="licenses[index].temp_front_token">
                        
                        <div x-show="preview" class="mt-2">
                            <img :src="preview" class="h-32 object-contain border rounded" alt="License Front Preview" />
                        </div>
                        
                        <p x-show="error" x-text="error" class="text-red-500 text-sm mt-1"></p>
                    </div>
                
                    <!-- License Back Image - Estructura similar a la imagen frontal -->
                    <div x-data="{ 
                        preview: license.back_preview || '', 
                        filename: license.back_filename || '', 
                        loading: false, 
                        error: '' 
                    }">
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Back Image</label>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="$refs.license_back.click()" 
                                class="px-3 py-2 bg-gray-200 rounded cursor-pointer hover:bg-gray-300 text-sm"
                                :disabled="loading"
                                aria-label="Upload license back image">
                                <span x-show="!loading">Select Image</span>
                                <span x-show="loading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Uploading...
                                </span>
                            </button>
                            <input type="file" x-ref="license_back" class="hidden" accept="image/*"
                                @change="
                                    const file = $event.target.files[0];
                                    if(!file) return;
                                    
                                    // Validate file size (max 2MB)
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
                                    .then(response => {
                                        if(!response.ok) throw new Error('Upload failed');
                                        return response.json();
                                    })
                                    .then(result => {
                                        licenses[index].temp_back_token = result.token;
                                        licenses[index].back_preview = preview;
                                        licenses[index].back_filename = filename;
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
                            
                            <button type="button" x-show="filename" @click="
                                preview = '';
                                filename = '';
                                licenses[index].temp_back_token = '';
                                licenses[index].back_preview = '';
                                licenses[index].back_filename = '';
                                $refs.license_back.value = '';
                                error = '';
                            " class="text-red-500 hover:text-red-700" aria-label="Remove license back image">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        
                        <input type="hidden" :name="`licenses[${index}][temp_back_token]`" x-model="licenses[index].temp_back_token">
                        
                        <div x-show="preview" class="mt-2">
                            <img :src="preview" class="h-32 object-contain border rounded" alt="License Back Preview" />
                        </div>
                        
                        <p x-show="error" x-text="error" class="text-red-500 text-sm mt-1"></p>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Driving Experience Section -->
    <div class="mb-6 pb-4">
        <h4 class="font-medium text-gray-700 mb-4">Driving Experience</h4>

        <template x-for="(experience, index) in experiences" :key="index">
            <div class="experience-entry border rounded-lg p-4 mb-4">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="font-medium text-gray-600" x-text="'Vehicle #' + (index + 1)"></h5>
                    <div class="flex">
                        <x-base.button type="button" @click="addExperience()" variant="primary" class="mr-2">
                            <x-base.lucide class="mr-2 h-4 w-4" icon="Activity" />
                            Add Another Vehicle
                        </x-base.button>
                        <button type="button" x-show="index > 0" @click="removeExperience(index)"
                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
                            aria-label="Remove vehicle experience">
                            Remove
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" :for="`equipment_type_${index}`">
                            Equipment Type
                        </label>
                        <select :id="`equipment_type_${index}`" :name="`experiences[${index}][equipment_type]`" 
                            x-model="experience.equipment_type"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">Select Equipment Type</option>
                            <option value="Straight Truck">Straight Truck</option>
                            <option value="Tractor & Semi-Trailer">Tractor & Semi-Trailer</option>
                            <option value="Tractor & Two Trailers">Tractor & Two Trailers</option>
                            <option value="Tractor & Triple Trailers">Tractor & Triple Trailers</option>
                            <option value="Other">Other</option>
                        </select>
                        @error('experiences.*.equipment_type')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" :for="`years_experience_${index}`">
                            Years of Experience
                        </label>
                        <input type="number" :id="`years_experience_${index}`" :name="`experiences[${index}][years_experience]`" 
                            x-model="experience.years_experience" min="0" max="10" step="1"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error('experiences.*.years_experience')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                        <span class="text-xs text-gray-500">Maximum 10 years</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" :for="`miles_driven_${index}`">
                            Total Miles Driven
                        </label>
                        <input type="number" :id="`miles_driven_${index}`" :name="`experiences[${index}][miles_driven]`" 
                            x-model="experience.miles_driven" min="0" step="1"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        @error('experiences.*.miles_driven')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <div class="h-8"></div> <!-- Spacer -->
                        <div class="flex items-center">
                            <input type="checkbox" :id="`requires_cdl_${index}`" :name="`experiences[${index}][requires_cdl]`" 
                                value="1" x-model="experience.requires_cdl"
                                class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                            <label :for="`requires_cdl_${index}`" class="ml-2 text-sm">This vehicle requires a CDL</label>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>