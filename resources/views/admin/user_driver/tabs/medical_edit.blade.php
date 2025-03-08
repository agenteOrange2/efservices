<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">FMCSA Driver Medical Qualification</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Social Security Number -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Social Security Number <span
                    class="text-red-500">*</span></label>
            <input type="text" name="social_security_number" 
                value="{{ old('social_security_number', $userDriverDetail->medicalQualification?->social_security_number) }}"
                placeholder="XXX-XX-XXXX" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                pattern="\d{3}-\d{2}-\d{4}" x-mask="999-99-9999">
            <p class="mt-1 text-xs text-gray-500">Format: XXX-XX-XXXX</p>
            @error('social_security_number')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <!-- Hire Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
            <input type="date" name="hire_date" 
                value="{{ old('hire_date', $userDriverDetail->medicalQualification?->hire_date?->format('Y-m-d')) }}"
                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Location -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <input type="text" name="location" 
                value="{{ old('location', $userDriverDetail->medicalQualification?->location) }}" 
                placeholder="Work location"
                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Suspension Status -->
        <div>
            <div class="flex items-center mb-2">
                <input type="checkbox" name="is_suspended" id="is_suspended" value="1" x-model="isSuspended"
                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                <label for="is_suspended" class="ml-2 text-sm">Driver is Suspended</label>
            </div>

            <div x-show="isSuspended" class="mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Suspension Date</label>
                <input type="date" name="suspension_date" 
                    value="{{ old('suspension_date', $userDriverDetail->medicalQualification?->suspension_date?->format('Y-m-d')) }}"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            </div>
        </div>

        <!-- Termination Status -->
        <div>
            <div class="flex items-center mb-2">
                <input type="checkbox" name="is_terminated" id="is_terminated" value="1" x-model="isTerminated"
                    class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded">
                <label for="is_terminated" class="ml-2 text-sm">Driver is Terminated</label>
            </div>

            <div x-show="isTerminated" class="mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Termination Date</label>
                <input type="date" name="termination_date" 
                    value="{{ old('termination_date', $userDriverDetail->medicalQualification?->termination_date?->format('Y-m-d')) }}"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
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
                <input type="text" name="medical_examiner_name" 
                    value="{{ old('medical_examiner_name', $userDriverDetail->medicalQualification?->medical_examiner_name) }}"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            </div>

            <!-- Medical Examiner Registry Number -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Medical Examiner Registry Number <span
                        class="text-red-500">*</span></label>
                <input type="text" name="medical_examiner_registry_number"
                    value="{{ old('medical_examiner_registry_number', $userDriverDetail->medicalQualification?->medical_examiner_registry_number) }}"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Medical Card Expiration Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Medical Card Expiration Date <span
                        class="text-red-500">*</span></label>
                <input type="date" name="medical_card_expiration_date"
                    value="{{ old('medical_card_expiration_date', $userDriverDetail->medicalQualification?->medical_card_expiration_date?->format('Y-m-d')) }}"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
            </div>
        </div>

        <!-- Medical Card Upload -->
        <div class="mb-6" x-data="{
            preview: '{{ $userDriverDetail->medicalQualification?->getFirstMediaUrl('medical_card') }}',
            filename: '{{ $userDriverDetail->medicalQualification && $userDriverDetail->medicalQualification->getFirstMedia('medical_card') ? $userDriverDetail->medicalQualification->getFirstMedia('medical_card')->file_name : '' }}',
            token: '',
            loading: false,
            error: '',
            
            // Determine if we already have a PDF file
            isPdf: '{{ $userDriverDetail->medicalQualification && $userDriverDetail->medicalQualification->getFirstMedia('medical_card') && $userDriverDetail->medicalQualification->getFirstMedia('medical_card')->mime_type === 'application/pdf' ? 'true' : 'false' }}',
        
            uploadMedicalCard(event) {
                const file = event.target.files[0];
                if (!file) return;
        
                this.loading = true;
                this.filename = file.name;
        
                // Set local preview
                if (file.type === 'application/pdf') {
                    this.preview = '#';
                    this.isPdf = true;
                } else if (file.type.startsWith('image/')) {
                    this.preview = URL.createObjectURL(file);
                    this.isPdf = false;
                }
        
                const formData = new FormData();
                formData.append('file', file);
                formData.append('type', 'medical_card');
        
                fetch('{{ route('admin.temp.upload') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Upload failed');
                        return response.json();
                    })
                    .then(result => {
                        this.token = result.token;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error uploading file:', error);
                        this.loading = false;
                        this.error = 'Failed to upload file. Please try again.';
                    });
            },
        
            removeFile() {
                this.filename = '';
                this.preview = '';
                this.token = '';
                this.isPdf = false;
                document.getElementById('medical_card_file').value = '';
            }
        }">
            <label class="block text-sm font-medium text-gray-700 mb-2">Medical Card <span
                    class="text-red-500">*</span></label>

            <!-- Display current medical card if it exists -->
            <div x-show="preview && !loading" class="mb-4">
                <template x-if="isPdf === 'true' || isPdf === true">
                    <div class="flex items-center p-3 bg-gray-50 rounded border">
                        <svg class="w-8 h-8 text-red-500" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-700" x-text="filename"></p>
                            <p class="text-xs text-gray-500">Current medical card PDF</p>
                        </div>
                        <a x-bind:href="preview" target="_blank" class="ml-auto text-sm text-primary hover:underline" 
                           x-show="preview.startsWith('http')">
                            View
                        </a>
                    </div>
                </template>
                <template x-if="isPdf === 'false' || (isPdf === false && preview)">
                    <div class="relative">
                        <img :src="preview" class="max-h-32 object-contain border rounded" />
                        <a x-bind:href="preview" target="_blank" class="absolute top-2 right-2 bg-white p-1 rounded-full shadow"
                           x-show="preview.startsWith('http')">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                </template>
            </div>

            <div class="flex flex-col items-center justify-center w-full">
                <label for="medical_card_file"
                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100"
                    :class="{ 'bg-blue-50 border-blue-300': preview }">

                    <div class="flex flex-col items-center justify-center pt-5 pb-6" x-show="!preview && !loading">
                        <svg class="w-8 h-8 mb-3 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                        </svg>
                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or
                            drag and drop</p>
                        <p class="text-xs text-gray-500">PDF, PNG, JPG or JPEG (MAX. 2MB)</p>
                    </div>

                    <div class="flex flex-col items-center justify-center pt-5 pb-6" x-show="preview && !loading">
                        <p class="mb-2 text-sm text-primary"><span class="font-semibold">Click to replace</span> current file</p>
                    </div>

                    <div x-show="loading" class="flex items-center justify-center pt-5 pb-6">
                        <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="text-sm text-gray-700">Uploading...</span>
                    </div>
                </label>

                <input id="medical_card_file" type="file" name="medical_card_file" class="hidden"
                    accept=".pdf,.png,.jpg,.jpeg" @change="uploadMedicalCard" />

                <input type="hidden" name="temp_medical_card_token" x-model="token">
            </div>

            <div x-show="filename && !loading" class="mt-2 flex items-center justify-between">
                <p class="text-sm text-gray-500">Selected file: <span x-text="filename"></span></p>
                <button type="button" @click="removeFile" class="text-sm text-red-500 hover:text-red-700">
                    Remove
                </button>
            </div>

            <p x-show="error" x-text="error" class="text-red-500 text-sm mt-1"></p>
        </div>
    </div>
</div>