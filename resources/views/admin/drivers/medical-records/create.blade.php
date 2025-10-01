@extends('../themes/' . $activeTheme)
@section('title', 'Add Medical Record')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Medical Records', 'url' => route('admin.medical-records.index')],
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
                Add New Medical Record
            </h2>
            <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.medical-records.index') }}" class="btn btn-outline-secondary" variant="primary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                    Back to Medical Records
                </x-base.button>
            </div>
        </div>

        <!-- Formulario -->
        <div class="box box--stacked mt-5">            
            <div class="box-body p-5">
                <div class="box-header mb-5">
                <h3 class="box-title text-2xl font-bold">Medical Record Information</h3>
            </div>
                <form action="{{ route('admin.medical-records.store') }}" method="post" enctype="multipart/form-data" id="medicalForm">
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

                            <!-- Tipo de examen -->
                            <div>
                                <x-base.form-label for="examination_type" required>Examination Type</x-base.form-label>
                                <select id="examination_type" name="examination_type" class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('examination_type') border-danger @enderror" required>
                                    <option value="">Select Examination Type</option>
                                    <option value="DOT Physical" {{ old('examination_type') == 'DOT Physical' ? 'selected' : '' }}>DOT Physical</option>
                                    <option value="Drug Test" {{ old('examination_type') == 'Drug Test' ? 'selected' : '' }}>Drug Test</option>
                                    <option value="Alcohol Test" {{ old('examination_type') == 'Alcohol Test' ? 'selected' : '' }}>Alcohol Test</option>
                                    <option value="Vision Test" {{ old('examination_type') == 'Vision Test' ? 'selected' : '' }}>Vision Test</option>
                                    <option value="Hearing Test" {{ old('examination_type') == 'Hearing Test' ? 'selected' : '' }}>Hearing Test</option>
                                    <option value="Sleep Apnea" {{ old('examination_type') == 'Sleep Apnea' ? 'selected' : '' }}>Sleep Apnea</option>
                                    <option value="Other" {{ old('examination_type') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('examination_type')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Médico examinador -->
                            <div>
                                <x-base.form-label for="examiner_name" required>Examiner Name</x-base.form-label>
                                <x-base.form-input type="text" id="examiner_name" name="examiner_name" placeholder="Enter examiner name" value="{{ old('examiner_name') }}" class="@error('examiner_name') border-danger @enderror" required />
                                @error('examiner_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Número de registro del médico -->
                            <div>
                                <x-base.form-label for="examiner_registry_number">Examiner Registry Number</x-base.form-label>
                                <x-base.form-input type="text" id="examiner_registry_number" name="examiner_registry_number" placeholder="Enter registry number" value="{{ old('examiner_registry_number') }}" class="@error('examiner_registry_number') border-danger @enderror" />
                                @error('examiner_registry_number')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="space-y-4">
                            <!-- Fecha de examen -->
                            <div>
                                <x-base.form-label for="examination_date" required>Examination Date</x-base.form-label>
                                <x-base.litepicker id="examination_date" name="examination_date" value="{{ old('examination_date') }}" class="@error('examination_date') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('examination_date')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha de expiración -->
                            <div>
                                <x-base.form-label for="expiration_date" required>Expiration Date</x-base.form-label>
                                <x-base.litepicker id="expiration_date" name="expiration_date" value="{{ old('expiration_date') }}" class="@error('expiration_date') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('expiration_date')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Resultado -->
                            <div>
                                <x-base.form-label for="result" required>Result</x-base.form-label>
                                <select id="result" name="result" class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('result') border-danger @enderror" required>
                                    <option value="">Select Result</option>
                                    <option value="pass" {{ old('result') == 'pass' ? 'selected' : '' }}>Pass</option>
                                    <option value="fail" {{ old('result') == 'fail' ? 'selected' : '' }}>Fail</option>
                                    <option value="conditional" {{ old('result') == 'conditional' ? 'selected' : '' }}>Conditional</option>
                                    <option value="pending" {{ old('result') == 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                                @error('result')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notas -->
                            <div>
                                <x-base.form-label for="notes">Notes</x-base.form-label>
                                <x-base.form-textarea id="notes" name="notes" placeholder="Enter any additional notes" class="@error('notes') border-danger @enderror" rows="4">{{ old('notes') }}</x-base.form-textarea>
                                @error('notes')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Restricciones Médicas -->
                    <div class="mt-8">
                        <h4 class="font-medium">Medical Restrictions</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div class="form-check">
                                <input type="checkbox" id="restriction_corrective_lenses" name="restrictions[]" class="form-check-input" value="corrective_lenses" {{ old('restrictions') && in_array('corrective_lenses', old('restrictions')) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_corrective_lenses" class="form-check-label">Corrective Lenses Required</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="restriction_hearing_aid" name="restrictions[]" class="form-check-input" value="hearing_aid" {{ old('restrictions') && in_array('hearing_aid', old('restrictions')) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_hearing_aid" class="form-check-label">Hearing Aid Required</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="restriction_daylight_only" name="restrictions[]" class="form-check-input" value="daylight_only" {{ old('restrictions') && in_array('daylight_only', old('restrictions')) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_daylight_only" class="form-check-label">Daylight Driving Only</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="restriction_no_interstate" name="restrictions[]" class="form-check-input" value="no_interstate" {{ old('restrictions') && in_array('no_interstate', old('restrictions')) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_no_interstate" class="form-check-label">No Interstate Driving</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="restriction_limited_distance" name="restrictions[]" class="form-check-input" value="limited_distance" {{ old('restrictions') && in_array('limited_distance', old('restrictions')) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_limited_distance" class="form-check-label">Limited Distance</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="restriction_medical_review" name="restrictions[]" class="form-check-input" value="medical_review" {{ old('restrictions') && in_array('medical_review', old('restrictions')) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_medical_review" class="form-check-label">Periodic Medical Review</x-base.form-label>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Documentos -->
                    <div class="mt-8">
                        <h4 class="font-medium mb-3">Documents</h4>
                        
                        <!-- Componente Livewire para carga de archivos -->
                        <livewire:components.file-uploader model-name="medical_files" model-index="0" label="Upload Medical Documents" :existing-files="[]" />
                        <!-- Campo oculto para almacenar los archivos subidos -->
                        <input type="hidden" name="medical_files" id="medical_files_input">
                    </div>

                    <!-- Botones del formulario -->
                    <div class="flex justify-end mt-8">
                        <x-base.button type="button" class="mr-3" variant="outline-secondary" as="a" href="{{ route('admin.medical-records.index') }}">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Save Medical Record
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
            const medicalFilesInput = document.getElementById('medical_files_input');
            let medicalFiles = [];
            
            // Escuchar eventos emitidos por el componente Livewire
            // Este evento se dispara cuando se sube un nuevo archivo
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('fileUploaded', (data) => {
                    const fileData = data[0];
                    
                    if (fileData.modelName === 'medical_files') {
                        // Agregar el archivo al array
                        medicalFiles.push({
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
                        medicalFilesInput.value = JSON.stringify(medicalFiles);
                        console.log('Archivo agregado:', fileData.originalName);
                        console.log('Total archivos:', medicalFiles.length);
                    }
                });
                
                // Este evento se dispara cuando se elimina un archivo
                Livewire.on('fileRemoved', (fileId) => {
                    // Encontrar y eliminar el archivo del array
                    medicalFiles = medicalFiles.filter(file => file.id !== fileId);
                    
                    // Actualizar el input hidden
                    medicalFilesInput.value = JSON.stringify(medicalFiles);
                    console.log('Archivo eliminado, ID:', fileId);
                    console.log('Total archivos restantes:', medicalFiles.length);
                });
            });
            
            const examinationDateEl = document.getElementById('examination_date');
            const expirationDateEl = document.getElementById('expiration_date');

            // Formatear las fechas en formato estadounidense (m-d-Y) antes de enviar el formulario
            document.getElementById('medicalForm').addEventListener('submit', function(event) {
                const examinationDateEl = document.getElementById('examination_date');
                const expirationDateEl = document.getElementById('expiration_date');
                
                // Verificar que la fecha de expiración es posterior a la fecha de examen
                const examinationDate = new Date(examinationDateEl.value);
                const expirationDate = new Date(expirationDateEl.value);
                
                if (expirationDate < examinationDate) {
                    event.preventDefault();
                    alert('Expiration date must be after examination date');
                    return;
                }
                
                // Asegurarse de que las fechas estén en formato YYYY-MM-DD que Laravel puede validar
                if (examinationDateEl.value) {
                    const examination = new Date(examinationDateEl.value);
                    if (!isNaN(examination.getTime())) {
                        const year = examination.getFullYear();
                        const month = (examination.getMonth() + 1).toString().padStart(2, '0');
                        const day = examination.getDate().toString().padStart(2, '0');
                        examinationDateEl.value = `${year}-${month}-${day}`;
                    }
                }
                
                if (expirationDateEl.value) {
                    const expiration = new Date(expirationDateEl.value);
                    if (!isNaN(expiration.getTime())) {
                        const year = expiration.getFullYear();
                        const month = (expiration.getMonth() + 1).toString().padStart(2, '0');
                        const day = expiration.getDate().toString().padStart(2, '0');
                        expirationDateEl.value = `${year}-${month}-${day}`;
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