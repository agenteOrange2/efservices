<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Commercial Driver Training Schools</h3>

    <div class="flex items-center mb-4">
        <input type="checkbox"
        wire:model.live="has_attended_training_school"
        id="has_attended_training_school"
        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
        <label for="has_attended_training_school" class="text-sm">
            Have you attended a commercial driver training school?
        </label>
    </div>

    @if ($has_attended_training_school)
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mb-4">
            <div x-show="has_attended_training_school" x-transition>
                @foreach ($training_schools as $index => $school)
                    <div class="border p-4 rounded-lg mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-medium">Training School #{{ $index + 1 }}</h4>
                            @if (count($training_schools) > 1)
                                <button type="button" wire:click="removeTrainingSchool({{ $index }})"
                                    class="text-red-500 text-sm">
                                    <i class="fas fa-trash mr-1"></i> Remove
                                </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">School Name <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model="training_schools.{{ $index }}.school_name"
                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                    placeholder="Name of school">
                                @error("training_schools.{$index}.school_name")
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Phone Number</label>
                                <input type="text" wire:model="training_schools.{{ $index }}.phone_number"
                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                    placeholder="(555) 555-5555">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">City <span
                                        class="text-red-500">*</span></label>
                                <input type="text" wire:model="training_schools.{{ $index }}.city"
                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                                    placeholder="City">
                                @error("training_schools.{$index}.city")
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">State <span
                                        class="text-red-500">*</span></label>
                                <select wire:model="training_schools.{{ $index }}.state"
                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                                    <option value="">Select State</option>
                                    @foreach ($usStates as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error("training_schools.{$index}.state")
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Start Date <span
                                        class="text-red-500">*</span></label>
                                <input type="date" wire:model="training_schools.{{ $index }}.date_start"
                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                                @error("training_schools.{$index}.date_start")
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">End Date <span
                                        class="text-red-500">*</span></label>
                                <input type="date" wire:model="training_schools.{{ $index }}.date_end"
                                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                                @error("training_schools.{$index}.date_end")
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center mb-2">
                                <input class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
                                    type="checkbox" wire:model="training_schools.{{ $index }}.graduated"
                                    id="graduated_{{ $index }}">
                                <label for="graduated_{{ $index }}" class="text-sm">
                                    Did you graduate from this program?
                                </label>
                            </div>

                            <div class="flex items-center mb-2">
                                <input class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
                                    type="checkbox"
                                    wire:model="training_schools.{{ $index }}.subject_to_safety_regulations"
                                    id="safety_regulations_{{ $index }}">
                                <label for="safety_regulations_{{ $index }}" class="text-sm">
                                    Was this position subject to Federal Motor Carrier Safety Regulations?
                                </label>
                            </div>

                            <div class="flex items-center mb-2">
                                <input class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
                                    type="checkbox"
                                    wire:model="training_schools.{{ $index }}.performed_safety_functions"
                                    id="safety_functions_{{ $index }}">
                                <label for="safety_functions_{{ $index }}" class="text-sm">
                                    Did this job require you to perform safety-sensitive functions?
                                </label>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="block text-sm font-medium mb-2">Which of the following skills were trained in
                                your
                                program? (select all that apply)</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                <div class="flex items-center">
                                    <input type="checkbox" id="skill_double_{{ $index }}" value="double_trailer"
                                        wire:click="toggleTrainingSkill({{ $index }}, 'double_trailer')"
                                        @if (in_array('double_trailer', $school['training_skills'] ?? [])) checked @endif
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                                    <label for="skill_double_{{ $index }}" class="text-sm">Double
                                        Trailer</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="skill_passenger_{{ $index }}" value="passenger"
                                        wire:click="toggleTrainingSkill({{ $index }}, 'passenger')"
                                        @if (in_array('passenger', $school['training_skills'] ?? [])) checked @endif
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                                    <label for="skill_passenger_{{ $index }}" class="text-sm">Passenger</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="skill_tank_{{ $index }}" value="tank_vehicle"
                                        wire:click="toggleTrainingSkill({{ $index }}, 'tank_vehicle')"
                                        @if (in_array('tank_vehicle', $school['training_skills'] ?? [])) checked @endif
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                                    <label for="skill_tank_{{ $index }}" class="text-sm">Tank Vehicle</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="skill_hazmat_{{ $index }}"
                                        value="hazardous_material"
                                        wire:click="toggleTrainingSkill({{ $index }}, 'hazardous_material')"
                                        @if (in_array('hazardous_material', $school['training_skills'] ?? [])) checked @endif
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                                    <label for="skill_hazmat_{{ $index }}" class="text-sm">Hazardous
                                        Material</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="skill_combination_{{ $index }}"
                                        value="combination_vehicle"
                                        wire:click="toggleTrainingSkill({{ $index }}, 'combination_vehicle')"
                                        @if (in_array('combination_vehicle', $school['training_skills'] ?? [])) checked @endif
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                                    <label for="skill_combination_{{ $index }}" class="text-sm">Combination
                                        Vehicle</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="skill_airbrakes_{{ $index }}"
                                        value="air_brakes"
                                        wire:click="toggleTrainingSkill({{ $index }}, 'air_brakes')"
                                        @if (in_array('air_brakes', $school['training_skills'] ?? [])) checked @endif
                                        class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                                    <label for="skill_airbrakes_{{ $index }}" class="text-sm">Air
                                        Brakes</label>
                                </div>
                            </div>
                        </div>

                        <!-- Certificate Uploads -->
                        <div class="mb-4" x-data="{
                            isUploading: false,
                        
                            async uploadCertificate(event) {
                                const files = event.target.files;
                                if (!files || files.length === 0) return;
                        
                                this.isUploading = true;
                        
                                for (let i = 0; i < files.length; i++) {
                                    const file = files[i];
                        
                                    // Validar tamaño del archivo
                                    if (file.size > 2 * 1024 * 1024) {
                                        alert('File size must be less than 2MB');
                                        continue;
                                    }
                        
                                    // Preparar FormData
                                    const formData = new FormData();
                                    formData.append('file', file);
                                    formData.append('type', 'school_certificates');
                        
                                    try {
                                        const response = await fetch('{{ route('admin.temp.upload') }}', {
                                            method: 'POST',
                                            body: formData,
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            }
                                        });
                        
                                        if (response.ok) {
                                            const data = await response.json();
                        
                                            // Generar URL de vista previa para imágenes
                                            let previewUrl = null;
                                            if (file.type.startsWith('image/')) {
                                                previewUrl = URL.createObjectURL(file);
                                            }
                        
                                            // Añadir el certificado a Livewire
                                            @this.call('addCertificate', {{ $index }}, data.token, file.name, previewUrl, file.type);
                                        } else {
                                            console.error('Error uploading file:', await response.text());
                                            alert('Error uploading file. Please try again.');
                                        }
                                    } catch (error) {
                                        console.error('Error:', error);
                                        alert('Error uploading file. Please try again.');
                                    }
                                }
                        
                                this.isUploading = false;
                                event.target.value = '';
                            }
                        }">
                            <label class="block text-sm font-medium mb-1">School Certificates</label>
                            <div class="flex items-center mb-2 mt-4">
                                <input type="file" id="training_certificate_{{ $index }}"
                                    @change="uploadCertificate($event)" class="hidden" multiple
                                    accept=".pdf,.jpg,.jpeg,.png">
                                <label for="training_certificate_{{ $index }}"
                                    class="cursor-pointer bg-blue-600 text-white px-3 py-2 rounded-md shadow-sm text-sm hover:bg-blue-700 inline-flex items-center">
                                    <span x-show="!isUploading">
                                        <i class="fas fa-upload mr-2"></i> Upload Certificate(s)
                                    </span>
                                    <span x-show="isUploading" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        Uploading...
                                    </span>
                                </label>
                            </div>

                            <!-- Lista de certificados cargados con previsualización -->
                            <div
                                class="mt-6 border-t border-slate-200/60 bg-slate-50 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach ($school['temp_certificate_tokens'] ?? [] as $tokenIndex => $certFile)
                                    <div class="flex flex-col bg-gray-50 p-2 rounded mb-1 relative">
                                        <!-- Miniatura de previsualización -->
                                        <div
                                            class="w-full h-32 mb-2 bg-gray-200 flex items-center justify-center rounded overflow-hidden">
                                            @if (isset($certFile['preview_url']) && Str::startsWith($certFile['filename'] ?? '', ['jpg', 'jpeg', 'png', 'gif']))
                                                <img src="{{ $certFile['preview_url'] }}"
                                                    class="object-contain h-full w-full" alt="Certificate preview">
                                            @else
                                                <div class="flex flex-col items-center justify-center">
                                                    <i class="fas fa-file-pdf text-red-500 text-3xl mb-1"></i>
                                                    <span class="text-xs text-gray-600">PDF Document</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Información del archivo -->
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 overflow-hidden">
                                                <span
                                                    class="text-sm truncate block">{{ $certFile['filename'] ?? 'Document' }}</span>
                                            </div>
                                            <button type="button"
                                                wire:click="removeCertificate({{ $index }}, {{ $tokenIndex }})"
                                                class="text-red-500 hover:text-red-700 ml-2">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <button type="button" wire:click="addTrainingSchool"
                    class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
                    <i class="fas fa-plus mr-1"></i> Add Another Training School
                </button>
            </div>
        </div>
    @endif

</div>
