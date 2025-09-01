@extends('../themes/' . $activeTheme)
@section('title', 'Add Training School')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Training Schools', 'url' => route('admin.training-schools.index')],
        ['label' => 'Create New', 'active' => true],
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
                Add New Training School
            </h2>
            <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.training-schools.index') }}" class="btn btn-outline-secondary" variant="primary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                    Back to Training Schools
                </x-base.button>
            </div>
        </div>

        <!-- Formulario -->
        <div class="box box--stacked mt-5">            
            <div class="box-body p-5">
                <div class="box-header mb-5">
                <h3 class="box-title text-2xl font-bold">Training School Information</h3>
            </div>
                <form action="{{ route('admin.training-schools.store') }}" method="post" enctype="multipart/form-data" id="schoolForm">
                    @csrf

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
                                        <option value="{{ $carrier->id }}" {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
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
                                    @if(isset($drivers))
                                        @foreach ($drivers as $driver)
                                            <option value="{{ $driver->id }}">
                                                {{ $driver->user->name }} {{ $driver->user->last_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('user_driver_detail_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nombre de la escuela -->
                            <div>
                                <x-base.form-label for="school_name" required>School Name</x-base.form-label>
                                <x-base.form-input type="text" id="school_name" name="school_name" placeholder="Enter school name" value="{{ old('school_name') }}" class="@error('school_name') border-danger @enderror" required />
                                @error('school_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Ciudad -->
                            <div>
                                <x-base.form-label for="city" required>City</x-base.form-label>
                                <x-base.form-input type="text" id="city" name="city" placeholder="Enter city" value="{{ old('city') }}" class="@error('city') border-danger @enderror" required />
                                @error('city')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div>
                                <x-base.form-label for="state" required>State</x-base.form-label>
                                <select id="state" name="state" class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('state') border-danger @enderror" required>
                                    <option value="">Select State</option>
                                    @foreach(\App\Helpers\Constants::usStates() as $code => $name)
                                        <option value="{{ $code }}" {{ old('state') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('state')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="space-y-4">

                            <!-- Fecha de inicio -->
                            <div>
                                <x-base.form-label for="date_start" required>Start Date</x-base.form-label>
                                <x-base.litepicker id="date_start" name="date_start" value="{{ old('date_start') }}" class="@error('date_start') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('date_start')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha de finalización -->
                            <div>
                                <x-base.form-label for="date_end" required>End Date</x-base.form-label>
                                <x-base.litepicker id="date_end" name="date_end" value="{{ old('date_end') }}" class="@error('date_end') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('date_end')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Checkboxes -->
                            <div class="mt-4">
                                <div class="form-check">
                                    <input type="checkbox" id="graduated" name="graduated" class="form-check-input" value="1" {{ old('graduated') ? 'checked' : '' }}>
                                    <x-base.form-label for="graduated" class="form-check-label">Graduated</x-base.form-label>
                                </div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" id="subject_to_safety_regulations" name="subject_to_safety_regulations" class="form-check-input" value="1" {{ old('subject_to_safety_regulations') ? 'checked' : '' }}>
                                    <x-base.form-label for="subject_to_safety_regulations" class="form-check-label">Subject to Safety Regulations</x-base.form-label>
                                </div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" id="performed_safety_functions" name="performed_safety_functions" class="form-check-input" value="1" {{ old('performed_safety_functions') ? 'checked' : '' }}>
                                    <x-base.form-label for="performed_safety_functions" class="form-check-label">Performed Safety Functions</x-base.form-label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Habilidades -->
                    <div class="mt-8">
                        <h4 class="font-medium">Training Skills</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div class="form-check">
                                <input type="checkbox" id="skill_driving" name="training_skills[]" class="form-check-input" value="driving" {{ old('training_skills') && in_array('driving', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_driving" class="form-check-label">Driving</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_safety" name="training_skills[]" class="form-check-input" value="safety" {{ old('training_skills') && in_array('safety', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_safety" class="form-check-label">Safety Procedures</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_maintenance" name="training_skills[]" class="form-check-input" value="maintenance" {{ old('training_skills') && in_array('maintenance', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_maintenance" class="form-check-label">Vehicle Maintenance</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_loading" name="training_skills[]" class="form-check-input" value="loading" {{ old('training_skills') && in_array('loading', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_loading" class="form-check-label">Loading/Unloading</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_regulations" name="training_skills[]" class="form-check-input" value="regulations" {{ old('training_skills') && in_array('regulations', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_regulations" class="form-check-label">DOT Regulations</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="skill_emergency" name="training_skills[]" class="form-check-input" value="emergency" {{ old('training_skills') && in_array('emergency', old('training_skills')) ? 'checked' : '' }}>
                                <x-base.form-label for="skill_emergency" class="form-check-label">Emergency Procedures</x-base.form-label>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Documentos -->
                    <div class="mt-8">
                        <h4 class="font-medium mb-3">Documents</h4>
                        
                        <!-- Componente Livewire para carga de archivos -->
                        <livewire:components.file-uploader model-name="training_files" model-index="0" label="Upload Training Documents" :existing-files="[]" />
                        <!-- Campo oculto para almacenar los archivos subidos -->
                        <input type="hidden" name="training_files" id="training_files_input">
                    </div>

                    <!-- Botones del formulario -->
                    <div class="flex justify-end mt-8">
                        <x-base.button type="button" class="mr-3" variant="outline-secondary" as="a" href="{{ route('admin.training-schools.index') }}">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Save Training School
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
            
            // Escuchar eventos emitidos por el componente Livewire
            // Este evento se dispara cuando se sube un nuevo archivo
            document.addEventListener('livewire:initialized', () => {
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
                Livewire.on('fileRemoved', (fileId) => {
                    // Encontrar y eliminar el archivo del array
                    trainingFiles = trainingFiles.filter(file => file.id !== fileId);
                    
                    // Actualizar el input hidden
                    trainingFilesInput.value = JSON.stringify(trainingFiles);
                    console.log('Archivo eliminado, ID:', fileId);
                    console.log('Total archivos restantes:', trainingFiles.length);
                });
            });
            
            // La inicialización de Litepicker está manejada globalmente por el componente
            
            const dateStartEl = document.getElementById('date_start');
            const dateEndEl = document.getElementById('date_end');

            // Formatear las fechas en formato estadounidense (m-d-Y) antes de enviar el formulario
            document.getElementById('schoolForm').addEventListener('submit', function(event) {
                const dateStartEl = document.getElementById('date_start');
                const dateEndEl = document.getElementById('date_end');
                
                // Verificar que la fecha de fin es posterior a la fecha de inicio
                const startDate = new Date(dateStartEl.value);
                const endDate = new Date(dateEndEl.value);
                
                if (endDate < startDate) {
                    event.preventDefault();
                    alert('End date must be after or equal to start date');
                    return;
                }
                
                // Asegurarse de que las fechas estén en formato YYYY-MM-DD que Laravel puede validar
                if (dateStartEl.value) {
                    // Solo reformatear si hay una fecha
                    const start = new Date(dateStartEl.value);
                    if (!isNaN(start.getTime())) {
                        const year = start.getFullYear();
                        const month = (start.getMonth() + 1).toString().padStart(2, '0');
                        const day = start.getDate().toString().padStart(2, '0');
                        dateStartEl.value = `${year}-${month}-${day}`;
                    }
                }
                
                if (dateEndEl.value) {
                    // Solo reformatear si hay una fecha
                    const end = new Date(dateEndEl.value);
                    if (!isNaN(end.getTime())) {
                        const year = end.getFullYear();
                        const month = (end.getMonth() + 1).toString().padStart(2, '0');
                        const day = end.getDate().toString().padStart(2, '0');
                        dateEndEl.value = `${year}-${month}-${day}`;
                    }
                }
            });
            
            // Manejar cambio de carrier para filtrar conductores
            document.getElementById('carrier_id').addEventListener('change', function() {
                const carrierId = this.value;
                
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
                                data.forEach(function(driver) {
                                    const option = document.createElement('option');
                                    option.value = driver.id;
                                    option.textContent = `${driver.user.name} ${driver.user.last_name || ''}`;
                                    driverSelect.appendChild(option);
                                });
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