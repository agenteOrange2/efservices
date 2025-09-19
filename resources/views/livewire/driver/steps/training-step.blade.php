<div>
<div class="bg-white p-4 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Commercial Driver Training Schools</h3>

    <div class="mb-6">
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" wire:model.live="has_attended_training_school" class="sr-only peer">
            <div
                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
            </div>
            <span class="ms-3 text-sm font-medium">Have you attended a commercial driver training school?</span>
        </label>
    </div>

    <div x-show="$wire.has_attended_training_school" x-transition>
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

            <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mb-4">
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
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">City <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="training_schools.{{ $index }}.city"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="City">
                    @error("training_schools.{$index}.city")
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">State <span class="text-red-500">*</span></label>
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
                    <input type="text"
                        name="training_schools.{{ $index }}.date_start"
                        value="{{ $school['date_start'] ?? '' }}"
                        onchange="@this.set('training_schools.{{ $index }}.date_start', this.value)"
                        class="driver-datepicker w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                        placeholder="MM/DD/YYYY" />
                    @error("training_schools.{$index}.date_start")
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">End Date <span
                            class="text-red-500">*</span></label>
                    <input type="text"
                        name="training_schools.{{ $index }}.date_end"
                        value="{{ $school['date_end'] ?? '' }}"
                        onchange="@this.set('training_schools.{{ $index }}.date_end', this.value)"
                        class="driver-datepicker w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                        placeholder="MM/DD/YYYY" />
                    @error("training_schools.{$index}.date_end")
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <div class="flex items-center mb-2">
                    <input class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2" type="checkbox"
                        wire:model="training_schools.{{ $index }}.graduated"
                        id="graduated_{{ $index }}">
                    <label for="graduated_{{ $index }}" class="text-sm">
                        Did you graduate from this program?
                    </label>
                </div>
                <div class="flex items-center mb-2">
                    <input class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2" type="checkbox"
                        wire:model="training_schools.{{ $index }}.subject_to_safety_regulations"
                        id="safety_regulations_{{ $index }}">
                    <label for="safety_regulations_{{ $index }}" class="text-sm">
                        Was this position subject to Federal Motor Carrier Safety Regulations?
                    </label>
                </div>
                <div class="flex items-center mb-2">
                    <input class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2" type="checkbox"
                        wire:model="training_schools.{{ $index }}.performed_safety_functions"
                        id="safety_functions_{{ $index }}">
                    <label for="safety_functions_{{ $index }}" class="text-sm">
                        Did this job require you to perform safety-sensitive functions?
                    </label>
                </div>
            </div>

            @if(!empty($school['id']))
            <div class="mb-2">
                <label class="block text-sm font-medium mb-2">Which of the following skills were trained in your
                    program? (select all that apply)</label>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    <div class="flex items-center">
                        <input type="checkbox" id="skill_double_{{ $index }}" value="double_trailer"
                            wire:click="toggleTrainingSkill({{ $index }}, 'double_trailer')"
                            @if (in_array('double_trailer', $school['training_skills'] ?? [])) checked @endif
                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                        <label for="skill_double_{{ $index }}" class="text-sm">Double Trailer</label>
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
                        <input type="checkbox" id="skill_hazmat_{{ $index }}" value="hazardous_material"
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
                        <input type="checkbox" id="skill_airbrakes_{{ $index }}" value="air_brakes"
                            wire:click="toggleTrainingSkill({{ $index }}, 'air_brakes')"
                            @if (in_array('air_brakes', $school['training_skills'] ?? [])) checked @endif
                            class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                        <label for="skill_airbrakes_{{ $index }}" class="text-sm">Air Brakes</label>
                    </div>
                </div>
            </div>
            @endif

            @if(empty($school['id']))
            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Debe crear la escuela de entrenamiento antes de poder subir certificados.
                </p>
            </div>
            @endif

            <!-- Certificate Uploads -->
            @if(!empty($school['id']))
            <div class="mb-4" x-data="{
                    isUploading: false,
                    async uploadCertificate(event) {
                        const files = event.target.files;
                        if (!files || files.length === 0) return;
                        this.isUploading = true;
                        for (let i = 0; i < files.length; i++) {
                            const file = files[i];
                            // Validar tamaño del archivo
                            if (file.size > 10 * 1024 * 1024) {
                                alert('File size must be less than 10MB');
                                continue;
                            }
                            // Preparar FormData
                            const formData = new FormData();
                            formData.append('file', file);
                            formData.append('type', 'school_certificates');
                            formData.append('driver_id', '{{ $driverId }}');
                            formData.append('model_id', {{ $school['id'] ?? 'null' }});
                            formData.append('model_type', 'training_school');
                            try {
                                const response = await fetch('/api/documents/upload-certificate-direct', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                });
                
                                if (response.ok) {
                                    const data = await response.json();
                                    // Refrescar la vista para mostrar el nuevo certificado
                                    @this.call('refreshTrainingSchoolCertificates', {{ $index }});
                                    // Disparar evento para actualizar la vista automáticamente
                                    window.dispatchEvent(new CustomEvent('certificates-updated'));
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
                <div class="flex items-center mb-2 mt-4">
                    <input type="file" id="school_certificate_{{ $index }}"
                        @change="uploadCertificate($event)" class="hidden" multiple
                        accept=".pdf,.jpg,.jpeg,.png">
                    <label for="school_certificate_{{ $index }}"
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
            </div>

            <!-- Certificate List -->
            <div class="mt-6 border-t border-slate-200/60 bg-slate-50" x-data="{}"
                x-on:certificates-updated.window="$wire.$refresh()">
                <!-- Botón para eliminar todos los certificados, colocado fuera de los bucles -->
                @if (isset($school['certificates']) && count($school['certificates']) > 0)
                <div class="flex justify-end mb-2">
                    <button type="button" wire:click="clearAllCertificates({{ $index }})"
                        class="text-red-500 text-sm hover:text-red-700">
                        <i class="fas fa-trash mr-1"></i> Eliminar todos los certificados
                    </button>
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mt-3">
                    <!-- Temporary Certificate Tokens -->
                    @if (!empty($school['temp_certificate_tokens']))
                    @foreach ($school['temp_certificate_tokens'] as $tokenIndex => $token)
                    <div class="border rounded-md p-2 relative flex flex-col">
                        <!-- Preview Image -->
                        <div class="h-24 flex items-center justify-center mb-2 bg-gray-50 rounded">
                            @if (isset($token['preview_url']) && Str::startsWith($token['file_type'] ?? '', 'image/'))
                            <img src="{{ $token['preview_url'] }}" class="object-contain h-full w-full"
                                alt="Certificate preview">
                            @else
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-file-pdf text-red-500 text-3xl mb-1"></i>
                                <span class="text-xs text-gray-600">PDF Document</span>
                            </div>
                            @endif
                        </div>
                        <!-- Info del archivo y botón eliminar -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1 overflow-hidden">
                                <span class="text-sm truncate block">{{ $token['filename'] }}</span>
                            </div>
                            <button type="button"
                                wire:click="removeCertificate({{ $index }}, {{ $tokenIndex }})"
                                class="text-red-500 hover:text-red-700 ml-2">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                    @endif

                    <!-- Existing Certificates -->
                    @if (!empty($school['certificates']))
                    @foreach ($school['certificates'] as $cert)
                    <div class="border rounded-md p-2 relative flex flex-col">
                        <!-- Preview Image -->
                        <div class="h-24 flex items-center justify-center mb-2 bg-gray-50 rounded">
                            @if ($cert['is_image'])
                            <img src="{{ $cert['url'] }}" class="object-contain h-full w-full"
                                alt="Certificate preview">
                            @else
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-file-pdf text-red-500 text-3xl mb-1"></i>
                                <span class="text-xs text-gray-600">PDF Document</span>
                            </div>
                            @endif
                        </div>
                        <!-- Info del archivo y botón eliminar -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1 overflow-hidden">
                                <span class="text-sm truncate block">{{ $cert['filename'] }}</span>
                            </div>
                            <button type="button"
                                wire:click="removeCertificateById({{ $index }}, {{ $cert['id'] }})"
                                class="text-red-500 hover:text-red-700 ml-2">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
            @endif

            <!-- Create/Update Buttons - Moved after certificate preview -->
            <div class="my-4 flex gap-2">
                @if(empty($school['id']))
                <button type="button" wire:click="createTrainingSchool({{ $index }})"
                    class="border border-green-700 px-4 py-2 rounded text-primary hover:text-white hover:bg-green-700 transition">
                    New Training School
                </button>
                @else
                <button type="button" wire:click="updateTrainingSchool({{ $index }})"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Update Training School
                </button>
                @endif
            </div>
        </div>
        @endforeach

        <button type="button" wire:click="addTrainingSchool"
            class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
            <i class="fas fa-plus mr-1"></i> Add Another Training School
        </button>
    </div>
</div>
<!-- END TRAINING SCHOOLS SECTION -->

<!-- COURSES SECTION -->
<div class="bg-white p-4 rounded-lg shadow mt-6">
    <h3 class="text-lg font-semibold mb-4">Professional Courses and Certifications</h3>

    <div class="mb-6">
        <label class="inline-flex items-center cursor-pointer">
            <input type="checkbox" wire:model.live="has_completed_courses" class="sr-only peer">
            <div
                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
            </div>
            <span class="ms-3 text-sm font-medium">Have you completed any professional courses or certifications?</span>
        </label>
    </div>

    <div x-show="$wire.has_completed_courses" x-transition>
        @foreach ($courses as $index => $course)
        <div class="border p-4 rounded-lg mb-6">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-medium">Course/Certification #{{ $index + 1 }}</h4>
                @if (count($courses) > 1)
                <button type="button" wire:click="removeCourse({{ $index }})"
                    class="text-red-500 text-sm">
                    <i class="fas fa-trash mr-1"></i> Remove
                </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Organization Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" wire:model="courses.{{ $index }}.organization_name"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                        placeholder="Name of organization">
                    @error("courses.{$index}.organization_name")
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">City <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="courses.{{ $index }}.city"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3" placeholder="City">
                    @error("courses.{$index}.city")
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">State <span class="text-red-500">*</span></label>
                    <select wire:model="courses.{{ $index }}.state"
                        class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3">
                        <option value="">Select State</option>
                        @foreach ($usStates as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error("courses.{$index}.state")
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Expiration Date</label>
                    <input type="text" 
                        name="courses.{{ $index }}.expiration_date"
                        wire:model="courses.{{ $index }}.expiration_date"
                        class="driver-datepicker w-full px-3 py-2 border rounded"
                        placeholder="MM/DD/YYYY"
                        value="{{ $course['expiration_date'] ?? '' }}" />
                    @error("courses.{$index}.expiration_date")
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Certification Date</label>
                    <input type="text" 
                        name="courses.{{ $index }}.certification_date"
                        value="{{ $course['certification_date'] ?? '' }}"
                        onchange="@this.set('courses.{{ $index }}.certification_date', this.value)"
                        class="driver-datepicker w-full px-3 py-2 border rounded"
                        placeholder="MM/DD/YYYY" />
                    @error("courses.{$index}.certification_date")
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Years of Experience</label>
                <input type="number" wire:model="courses.{{ $index }}.years_experience"
                    class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3"
                    placeholder="Years of experience" min="0" step="0.1">
                @error("courses.{$index}.years_experience")
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            @if(empty($course['id']))
            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Debe crear el curso antes de poder subir certificados.
                </p>
            </div>
            @endif

            <!-- Certificate Uploads -->
            @if(!empty($course['id']))
            <div class="mb-4" x-data="{
                    isUploading: false,
                    async uploadCourseCertificate(event) {
                        const files = event.target.files;
                        if (!files || files.length === 0) return;
                        this.isUploading = true;
                        const courseId = {{ $course['id'] ?? 'null' }};
                        for (let i = 0; i < files.length; i++) {
                            const file = files[i];
                            // Validar tamaño del archivo
                            if (file.size > 10 * 1024 * 1024) {
                                alert('File size must be less than 10MB');
                                continue;
                            }
                            // Preparar FormData
                            const formData = new FormData();
                            formData.append('file', file);
                            formData.append('type', 'course_certificates');
                            formData.append('driver_id', '{{ $driverId }}');
                            formData.append('model_id', courseId);
                            formData.append('model_type', 'course');
                            try {
                                const response = await fetch('/api/documents/upload-certificate-direct', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                });
                
                                if (response.ok) {
                                    const data = await response.json();
                                    // Refrescar la vista para mostrar el nuevo certificado
                                    @this.call('refreshCourseCertificates', {{ $index }});
                                    // Disparar evento para actualizar la vista automáticamente
                                    window.dispatchEvent(new CustomEvent('certificates-updated'));
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
                <div class="flex items-center mb-2 mt-4">
                    <input type="file" id="course_certificate_{{ $index }}"
                        @change="uploadCourseCertificate($event)" class="hidden" multiple
                        accept=".pdf,.jpg,.jpeg,.png">
                    <label for="course_certificate_{{ $index }}"
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
            </div>

            <!-- Certificate List -->
            <div class="mt-6 border-t border-slate-200/60 bg-slate-50" x-data="{}"
                x-on:certificates-updated.window="$wire.$refresh()">
                <!-- Botón para eliminar todos los certificados, colocado fuera de los bucles -->
                @if (isset($course['certificates']) && count($course['certificates']) > 0)
                <div class="flex justify-end mb-2">
                    <button type="button" wire:click="clearAllCourseCertificates({{ $index }})"
                        class="text-red-500 text-sm hover:text-red-700">
                        <i class="fas fa-trash mr-1"></i> Eliminar todos los certificados
                    </button>
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mt-3">
                    <!-- Temporary Certificate Tokens -->
                    @if (!empty($course['temp_certificate_tokens']))
                    @foreach ($course['temp_certificate_tokens'] as $tokenIndex => $token)
                    <div class="border rounded-md p-2 relative flex flex-col">
                        <!-- Preview Image -->
                        <div class="h-24 flex items-center justify-center mb-2 bg-gray-50 rounded">
                            @if (isset($token['preview_url']) && Str::startsWith($token['file_type'] ?? '', 'image/'))
                            <img src="{{ $token['preview_url'] }}" class="object-contain h-full w-full"
                                alt="Certificate preview">
                            @else
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-file-pdf text-red-500 text-3xl mb-1"></i>
                                <span class="text-xs text-gray-600">PDF Document</span>
                            </div>
                            @endif
                        </div>
                        <!-- Info del archivo y botón eliminar -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1 overflow-hidden">
                                <span class="text-sm truncate block">{{ $token['filename'] }}</span>
                            </div>
                            <button type="button"
                                wire:click="removeCourseCertificate({{ $index }}, {{ $tokenIndex }})"
                                class="text-red-500 hover:text-red-700 ml-2">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                    @endif

                    <!-- Existing Certificates -->
                    @if (!empty($course['certificates']))
                    @foreach ($course['certificates'] as $cert)
                    <div class="border rounded-md p-2 relative flex flex-col">
                        <!-- Preview Image -->
                        <div class="h-24 flex items-center justify-center mb-2 bg-gray-50 rounded">
                            @if ($cert['is_image'])
                            <img src="{{ $cert['url'] }}" class="object-contain h-full w-full"
                                alt="Certificate preview">
                            @else
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-file-pdf text-red-500 text-3xl mb-1"></i>
                                <span class="text-xs text-gray-600">PDF Document</span>
                            </div>
                            @endif
                        </div>
                        <!-- Info del archivo y botón eliminar -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1 overflow-hidden">
                                <span class="text-sm truncate block">{{ $cert['filename'] }}</span>
                            </div>
                            <button type="button"
                                wire:click="removeCertificateByIdFromCourse({{ $index }}, {{ $cert['id'] }})"
                                class="text-red-500 hover:text-red-700 ml-2">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
            @endif

            <!-- Create/Update Buttons - Moved after certificate preview -->
            <div class="my-4 flex gap-2">
                @if(empty($course['id']))
                <button type="button" wire:click="createCourse({{ $index }})"
                    class="border border-green-700 px-4 py-2 rounded text-primary hover:text-white hover:bg-green-700 transition">
                    New Course
                </button>
                @else
                <button type="button" wire:click="updateCourse({{ $index }})"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Updated Course
                </button>
                @endif
            </div>
        </div>
        @endforeach

        <button type="button" wire:click="addCourse"
            class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
            <i class="fas fa-plus mr-1"></i> Add Another Course/Certification
        </button>
    </div>
</div>
<!-- END COURSES SECTION -->

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
