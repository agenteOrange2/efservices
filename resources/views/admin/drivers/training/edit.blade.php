@extends('../themes/' . $activeTheme)
@section('title', 'Edit Training School')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Training Schools', 'url' => route('admin.training-schools.index')],
        ['label' => 'Edit', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div>
        <!-- Mensajes Flash -->
        @if (session()->has('success'))
            <div class="alert alert-success flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="alert-circle" />
                {{ session('error') }}
            </div>
        @endif

        <!-- Título de la página -->
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium">
                Edit Training School: {{ $trainingSchool->school_name }}
            </h2>
            <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                <a href="{{ route('admin.training-schools.index') }}" class="btn btn-outline-secondary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                    Back to Training Schools
                </a>
                <a href="{{ route('admin.training-schools.show', $trainingSchool->id) }}" class="btn btn-outline-primary ml-2">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="file-text" />
                    View Documents
                </a>
            </div>
        </div>

        <!-- Formulario -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Training School Information</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.training-schools.update', $trainingSchool->id) }}" method="post" enctype="multipart/form-data" id="schoolForm">
                    @csrf
                    @method('PUT')

                    <!-- Información Básica -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Columna Izquierda -->
                        <div class="space-y-4">
                            <!-- Carrier -->
                            <div>
                                <x-base.form-label for="carrier_id" required>Carrier</x-base.form-label>
                                <select id="carrier_id" name="carrier_id" 
                                    class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('carrier_id') border-danger @enderror" required>
                                    <option value="">Select Carrier</option>
                                    @foreach ($carriers as $carrier)
                                        <option value="{{ $carrier->id }}" {{ $carrierId == $carrier->id ? 'selected' : '' }}>
                                            {{ $carrier->name }} (DOT: {{ $carrier->dot_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('carrier_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Conductor -->
                            <div>
                                <x-base.form-label for="user_driver_detail_id" required>Driver</x-base.form-label>
                                <select id="user_driver_detail_id" name="user_driver_detail_id" 
                                    class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('user_driver_detail_id') border-danger @enderror" required>
                                    <option value="">Select Driver</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id }}" {{ old('user_driver_detail_id', $trainingSchool->user_driver_detail_id) == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->user->name }} {{ $driver->user->last_name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_driver_detail_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nombre de la escuela -->
                            <div>
                                <x-base.form-label for="school_name" required>School Name</x-base.form-label>
                                <x-base.form-input type="text" id="school_name" name="school_name" placeholder="Enter school name" value="{{ old('school_name', $trainingSchool->school_name) }}" class="@error('school_name') border-danger @enderror" required />
                                @error('school_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ciudad -->
                            <div>
                                <x-base.form-label for="city" required>City</x-base.form-label>
                                <x-base.form-input type="text" id="city" name="city" placeholder="Enter city" value="{{ old('city', $trainingSchool->city) }}" class="@error('city') border-danger @enderror" required />
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div>
                                <x-base.form-label for="state" required>State</x-base.form-label>
                                <x-base.form-input type="text" id="state" name="state" placeholder="Enter state" value="{{ old('state', $trainingSchool->state) }}" class="@error('state') border-danger @enderror" required />
                                @error('state')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="space-y-4">
                            <!-- Teléfono -->
                            <div>
                                <x-base.form-label for="phone_number" required>Phone Number</x-base.form-label>
                                <x-base.form-input type="text" id="phone_number" name="phone_number" placeholder="Enter phone number" value="{{ old('phone_number', $trainingSchool->phone_number) }}" class="@error('phone_number') border-danger @enderror" required />
                                @error('phone_number')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha de inicio -->
                            <div>
                                <x-base.form-label for="date_start" required>Start Date</x-base.form-label>
                                <x-base.litepicker id="date_start" name="date_start" value="{{ old('date_start', $trainingSchool->date_start) }}" class="@error('date_start') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('date_start')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha de finalización -->
                            <div>
                                <x-base.form-label for="date_end" required>End Date</x-base.form-label>
                                <x-base.litepicker id="date_end" name="date_end" value="{{ old('date_end', $trainingSchool->date_end) }}" class="@error('date_end') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('date_end')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Checkboxes -->
                            <div class="mt-4">
                                <div class="form-check">
                                    <input type="checkbox" id="graduated" name="graduated" class="form-check-input" value="1" {{ old('graduated', $trainingSchool->graduated) ? 'checked' : '' }}>
                                    <x-base.form-label for="graduated" class="form-check-label">Graduated</x-base.form-label>
                                </div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" id="subject_to_safety_regulations" name="subject_to_safety_regulations" class="form-check-input" value="1" {{ old('subject_to_safety_regulations', $trainingSchool->subject_to_safety_regulations) ? 'checked' : '' }}>
                                    <x-base.form-label for="subject_to_safety_regulations" class="form-check-label">Subject to Safety Regulations</x-base.form-label>
                                </div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" id="performed_safety_functions" name="performed_safety_functions" class="form-check-input" value="1" {{ old('performed_safety_functions', $trainingSchool->performed_safety_functions) ? 'checked' : '' }}>
                                    <x-base.form-label for="performed_safety_functions" class="form-check-label">Performed Safety Functions</x-base.form-label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Habilidades -->
                    <div class="mt-8">
                        <h4 class="font-medium">Training Skills</h4>
                        @php
                            $trainingSkills = old('training_skills', $trainingSchool->training_skills ?? []);
                            if (is_string($trainingSkills)) {
                                $trainingSkills = json_decode($trainingSkills, true) ?? [];
                            }
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div class="form-check">
                                <input type="checkbox" id="skill_driving" name="training_skills[]" class="form-check-input" value="driving" {{ in_array('driving', $trainingSkills) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_driving" class="form-check-label">Driving</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_safety" name="training_skills[]" class="form-check-input" value="safety" {{ in_array('safety', $trainingSkills) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_safety" class="form-check-label">Safety Procedures</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_maintenance" name="training_skills[]" class="form-check-input" value="maintenance" {{ in_array('maintenance', $trainingSkills) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_maintenance" class="form-check-label">Vehicle Maintenance</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_loading" name="training_skills[]" class="form-check-input" value="loading" {{ in_array('loading', $trainingSkills) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_loading" class="form-check-label">Loading/Unloading</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_regulations" name="training_skills[]" class="form-check-input" value="regulations" {{ in_array('regulations', $trainingSkills) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_regulations" class="form-check-label">DOT Regulations</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_emergency" name="training_skills[]" class="form-check-input" value="emergency" {{ in_array('emergency', $trainingSkills) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_emergency" class="form-check-label">Emergency Procedures</x-base.form-label>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Documentos -->
                    <div class="mt-8">
                        <h4 class="font-medium">Documents</h4>
                        <div class="mt-3">
                            @php
                            // Los archivos existentes ya vienen preparados desde el controlador
                            // $existingFilesArray contiene los documentos de Spatie Media Library
                            @endphp

                            <livewire:components.file-uploader
                                model-name="training_files"
                                :model-index="0"
                                :label="'Upload Documents'"
                                :existing-files="$existingFilesArray"
                            />
                            <!-- Campo oculto para almacenar los archivos subidos -->
                            <input type="hidden" name="training_files" id="training_files_input">
                        </div>
                    </div>

                    <!-- Botones del formulario -->
                    <div class="mt-8 flex justify-end">
                        <x-base.button as="a" href="{{ route('admin.training-schools.index') }}" variant="outline-secondary" class="mr-2">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Update Training School
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Inicialización del formulario
        document.addEventListener('DOMContentLoaded', function() {
            // Almacenar archivos subidos del componente Livewire
            const trainingFilesInput = document.getElementById('training_files_input');
            let trainingFiles = [];
            
            // Inicializar con archivos existentes, si hay alguno
            @if(isset($existingFilesArray) && count($existingFilesArray) > 0)
                trainingFiles = @json($existingFilesArray);
                trainingFilesInput.value = JSON.stringify(trainingFiles);
            @endif
            
            // Escuchar eventos emitidos por el componente Livewire
            document.addEventListener('livewire:initialized', () => {
                // Este evento se dispara cuando se sube un nuevo archivo
                Livewire.on('fileUploaded', (data) => {
                    const fileData = data[0];
                    
                    if (fileData.modelName === 'training_files') {
                        // Agregar el archivo al array
                        trainingFiles.push({
                            name: fileData.originalName,
                            original_name: fileData.originalName,
                            mime_type: fileData.mimeType,
                            size: fileData.size,
                            is_temp: true,
                            tempPath: fileData.tempPath,
                            path: fileData.tempPath,
                            id: fileData.previewData.id
                        });
                        
                        // Actualizar el input hidden con los datos JSON
                        trainingFilesInput.value = JSON.stringify(trainingFiles);
                        console.log('Archivo agregado:', fileData.originalName);
                        console.log('Total archivos:', trainingFiles.length);
                    }
                });
                
                // Este evento se dispara cuando se elimina un archivo
                Livewire.on('fileRemoved', (eventData) => {
                    console.log('Evento fileRemoved recibido:', eventData);
                    const data = eventData[0]; // Los datos vienen como primer elemento del array
                    const fileId = data.fileId;
                    
                    // Verificar si el archivo es permanente (no temporal) y pertenece a nuestro modelo
                    if (data.modelName === 'training_files' && !data.isTemp) {
                        console.log('Eliminando documento permanente con ID:', fileId);
                        
                        // Hacer llamada AJAX para eliminar el documento físicamente
                        fetch(`{{ url('admin/training-schools/document') }}/${fileId}/ajax`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                console.log('Documento eliminado con éxito de la base de datos');
                            } else {
                                console.error('Error al eliminar documento:', result.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error en la solicitud AJAX:', error);
                        });
                    }
                    
                    // Encontrar y eliminar el archivo del array local (tanto temporales como permanentes)
                    trainingFiles = trainingFiles.filter(file => file.id != fileId);
                    
                    // Actualizar el input hidden
                    trainingFilesInput.value = JSON.stringify(trainingFiles);
                    console.log('Archivo eliminado, ID:', fileId);
                    console.log('Total archivos restantes:', trainingFiles.length);
                });
            });
            
            // Verificar que la fecha de fin es posterior a la fecha de inicio
            document.getElementById('schoolForm').addEventListener('submit', function(event) {
                // Obtener valores actuales (en formato MM/DD/YYYY)
                const dateStartInput = document.getElementById('date_start');
                const dateEndInput = document.getElementById('date_end');
                
                const startDateValue = dateStartInput.value;
                const endDateValue = dateEndInput.value;
                
                // Crear objetos Date para validación
                const startDate = new Date(startDateValue);
                const endDate = new Date(endDateValue);
                
                // Verificar que las fechas sean válidas
                if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                    event.preventDefault();
                    alert('Please enter valid dates');
                    return;
                }
                
                // Verificar que la fecha de fin es posterior a la fecha de inicio
                if (endDate < startDate) {
                    event.preventDefault();
                    alert('End date must be after or equal to start date');
                    return;
                }
                
                // Convertir fechas al formato YYYY-MM-DD para Laravel
                const formatDate = (date) => {
                    const d = new Date(date);
                    return d.getFullYear() + '-' + 
                           ('0' + (d.getMonth() + 1)).slice(-2) + '-' + 
                           ('0' + d.getDate()).slice(-2);
                };
                
                // Cambiar el valor del input al formato YYYY-MM-DD
                dateStartInput.value = formatDate(startDateValue);
                dateEndInput.value = formatDate(endDateValue);
            });
            
            // Manejar cambio de carrier para filtrar conductores
            document.getElementById('carrier_id').addEventListener('change', function() {
                const carrierId = this.value;
                const currentDriverId = "{{ $trainingSchool->user_driver_detail_id }}";
                
                // Limpiar el select de conductores usando JavaScript nativo
                const driverSelect = document.getElementById('user_driver_detail_id');
                driverSelect.innerHTML = '<option value="">Select Driver</option>';
                
                if (carrierId) {
                    // Hacer una petición AJAX para obtener los conductores activos de esta transportista
                    fetch(`/api/active-drivers-by-carrier/${carrierId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                // Hay conductores activos, agregarlos al select
                                let driverFound = false;
                                
                                data.forEach(function(driver) {
                                    const option = document.createElement('option');
                                    option.value = driver.id;
                                    option.textContent = `${driver.user.name} ${driver.user.last_name || ''}`;
                                    
                                    if (driver.id == currentDriverId) {
                                        option.selected = true;
                                        driverFound = true;
                                    }
                                    
                                    driverSelect.appendChild(option);
                                });
                                
                                // Si el conductor actual no está en la lista (puede estar inactivo o pertenecer a otro carrier)
                                if (!driverFound && currentDriverId) {
                                    // Mantener el conductor actual como opción seleccionada
                                    // El backend ya se encarga de incluirlo en la lista de drivers
                                }
                            } else {
                                // No hay conductores activos para este carrier
                                const option = document.createElement('option');
                                option.value = '';
                                option.disabled = true;
                                option.textContent = 'No active drivers found for this carrier';
                                driverSelect.appendChild(option);
                            }
                            
                            // Disparar un evento change para que se actualice la UI
                            driverSelect.dispatchEvent(new Event('change'));
                        })
                        .catch(error => {
                            console.error('Error loading drivers:', error);
                            const option = document.createElement('option');
                            option.value = '';
                            option.disabled = true;
                            option.textContent = 'Error loading drivers';
                            driverSelect.appendChild(option);
                            driverSelect.dispatchEvent(new Event('change'));
                        });
                }
            });
        });
    </script>
@endpush