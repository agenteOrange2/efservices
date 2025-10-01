@extends('../themes/' . $activeTheme)
@section('title', 'Edit Medical Record')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Medical Records', 'url' => route('admin.medical-records.index')],
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
                Edit Medical Record: {{ $medicalRecord->examination_type }}
            </h2>
            <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.medical-records.index') }}" class="btn btn-outline-secondary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                    Back to Medical Records
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.medical-records.show', $medicalRecord->id) }}" class="btn btn-outline-primary ml-2">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="file-text" />
                    View Documents
                </x-base.button>
            </div>
        </div>

        <!-- Formulario -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Medical Record Information</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.medical-records.update', $medicalRecord->id) }}" method="post" enctype="multipart/form-data" id="medicalForm">
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
                                        <option value="{{ $driver->id }}" {{ old('user_driver_detail_id', $medicalRecord->user_driver_detail_id) == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->user->name }} {{ $driver->user->last_name ?? '' }}
                                        </option>
                                    @endforeach
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
                                    <option value="DOT Physical" {{ old('examination_type', $medicalRecord->examination_type) == 'DOT Physical' ? 'selected' : '' }}>DOT Physical</option>
                                    <option value="Drug Test" {{ old('examination_type', $medicalRecord->examination_type) == 'Drug Test' ? 'selected' : '' }}>Drug Test</option>
                                    <option value="Alcohol Test" {{ old('examination_type', $medicalRecord->examination_type) == 'Alcohol Test' ? 'selected' : '' }}>Alcohol Test</option>
                                    <option value="Vision Test" {{ old('examination_type', $medicalRecord->examination_type) == 'Vision Test' ? 'selected' : '' }}>Vision Test</option>
                                    <option value="Hearing Test" {{ old('examination_type', $medicalRecord->examination_type) == 'Hearing Test' ? 'selected' : '' }}>Hearing Test</option>
                                    <option value="Other" {{ old('examination_type', $medicalRecord->examination_type) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('examination_type')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nombre del examinador -->
                            <div>
                                <x-base.form-label for="examiner_name" required>Examiner Name</x-base.form-label>
                                <x-base.form-input type="text" id="examiner_name" name="examiner_name" placeholder="Enter examiner name" value="{{ old('examiner_name', $medicalRecord->examiner_name) }}" class="@error('examiner_name') border-danger @enderror" required />
                                @error('examiner_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Número de registro del examinador -->
                            <div>
                                <x-base.form-label for="examiner_registry_number">Examiner Registry Number</x-base.form-label>
                                <x-base.form-input type="text" id="examiner_registry_number" name="examiner_registry_number" placeholder="Enter registry number" value="{{ old('examiner_registry_number', $medicalRecord->examiner_registry_number) }}" class="@error('examiner_registry_number') border-danger @enderror" />
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
                                <x-base.litepicker id="examination_date" name="examination_date" value="{{ old('examination_date', $medicalRecord->examination_date) }}" class="@error('examination_date') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('examination_date')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha de expiración -->
                            <div>
                                <x-base.form-label for="expiration_date" required>Expiration Date</x-base.form-label>
                                <x-base.litepicker id="expiration_date" name="expiration_date" value="{{ old('expiration_date', $medicalRecord->expiration_date) }}" class="@error('expiration_date') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('expiration_date')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Resultado -->
                            <div>
                                <x-base.form-label for="result" required>Result</x-base.form-label>
                                <select id="result" name="result" class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('result') border-danger @enderror" required>
                                    <option value="">Select Result</option>
                                    <option value="passed" {{ old('result', $medicalRecord->result) == 'passed' ? 'selected' : '' }}>Passed</option>
                                    <option value="failed" {{ old('result', $medicalRecord->result) == 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="pending" {{ old('result', $medicalRecord->result) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="conditional" {{ old('result', $medicalRecord->result) == 'conditional' ? 'selected' : '' }}>Conditional</option>
                                </select>
                                @error('result')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notas -->
                            <div>
                                <x-base.form-label for="notes">Notes</x-base.form-label>
                                <x-base.form-textarea id="notes" name="notes" placeholder="Enter any additional notes" class="@error('notes') border-danger @enderror" rows="4">{{ old('notes', $medicalRecord->notes) }}</x-base.form-textarea>
                                @error('notes')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Restricciones Médicas -->
                    <div class="mt-8">
                        <h4 class="font-medium">Medical Restrictions</h4>
                        @php
                            $restrictions = old('medical_restrictions', $medicalRecord->medical_restrictions ?? []);
                            if (is_string($restrictions)) {
                                $restrictions = json_decode($restrictions, true) ?? [];
                            }
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div class="form-check">
                                <input type="checkbox" id="restriction_corrective_lenses" name="medical_restrictions[]" class="form-check-input" value="corrective_lenses" {{ in_array('corrective_lenses', $restrictions) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_corrective_lenses" class="form-check-label">Corrective Lenses Required</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="restriction_hearing_aid" name="medical_restrictions[]" class="form-check-input" value="hearing_aid" {{ in_array('hearing_aid', $restrictions) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_hearing_aid" class="form-check-label">Hearing Aid Required</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="restriction_diabetes" name="medical_restrictions[]" class="form-check-input" value="diabetes" {{ in_array('diabetes', $restrictions) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_diabetes" class="form-check-label">Diabetes Monitoring</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="restriction_blood_pressure" name="medical_restrictions[]" class="form-check-input" value="blood_pressure" {{ in_array('blood_pressure', $restrictions) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_blood_pressure" class="form-check-label">Blood Pressure Monitoring</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="restriction_sleep_apnea" name="medical_restrictions[]" class="form-check-input" value="sleep_apnea" {{ in_array('sleep_apnea', $restrictions) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_sleep_apnea" class="form-check-label">Sleep Apnea Treatment</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="restriction_other" name="medical_restrictions[]" class="form-check-input" value="other" {{ in_array('other', $restrictions) ? 'checked' : '' }}>
                                <x-base.form-label for="restriction_other" class="form-check-label">Other Restrictions</x-base.form-label>
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
                                model-name="medical_files"
                                :model-index="0"
                                :label="'Upload Medical Documents'"
                                :existing-files="$existingFilesArray"
                            />
                            <!-- Campo oculto para almacenar los archivos subidos -->
                            <input type="hidden" name="medical_files" id="medical_files_input">
                        </div>
                    </div>

                    <!-- Botones del formulario -->
                    <div class="mt-8 flex justify-end">
                        <x-base.button as="a" href="{{ route('admin.medical-records.index') }}" variant="outline-secondary" class="mr-2">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Update Medical Record
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
            
            // Inicializar con archivos existentes, si hay alguno
            @if(isset($existingFilesArray) && count($existingFilesArray) > 0)
                medicalFiles = @json($existingFilesArray);
                medicalFilesInput.value = JSON.stringify(medicalFiles);
            @endif
            
            // Escuchar eventos emitidos por el componente Livewire
            document.addEventListener('livewire:initialized', () => {
                // Este evento se dispara cuando se sube un nuevo archivo
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
                Livewire.on('fileRemoved', (eventData) => {
                    console.log('Evento fileRemoved recibido:', eventData);
                    const data = eventData[0]; // Los datos vienen como primer elemento del array
                    const fileId = data.fileId;
                    
                    // Verificar si el archivo es permanente (no temporal) y pertenece a nuestro modelo
                    if (data.modelName === 'medical_files' && !data.isTemp) {
                        console.log('Eliminando documento permanente con ID:', fileId);
                        
                        // Hacer llamada AJAX para eliminar el documento físicamente
                        fetch(`{{ url('admin/medical-records/document') }}/${fileId}/ajax`, {
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
                    medicalFiles = medicalFiles.filter(file => file.id != fileId);
                    
                    // Actualizar el input hidden
                    medicalFilesInput.value = JSON.stringify(medicalFiles);
                    console.log('Archivo eliminado, ID:', fileId);
                    console.log('Total archivos restantes:', medicalFiles.length);
                });
            });
            
            // Verificar que la fecha de expiración es posterior a la fecha de examen
            document.getElementById('medicalForm').addEventListener('submit', function(event) {
                // Obtener valores actuales (en formato MM/DD/YYYY)
                const examinationDateInput = document.getElementById('examination_date');
                const expirationDateInput = document.getElementById('expiration_date');
                
                const examinationDateValue = examinationDateInput.value;
                const expirationDateValue = expirationDateInput.value;
                
                // Crear objetos Date para validación
                const examinationDate = new Date(examinationDateValue);
                const expirationDate = new Date(expirationDateValue);
                
                // Verificar que las fechas sean válidas
                if (isNaN(examinationDate.getTime()) || isNaN(expirationDate.getTime())) {
                    event.preventDefault();
                    alert('Please enter valid dates');
                    return;
                }
                
                // Verificar que la fecha de expiración es posterior a la fecha de examen
                if (expirationDate < examinationDate) {
                    event.preventDefault();
                    alert('Expiration date must be after or equal to examination date');
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
                examinationDateInput.value = formatDate(examinationDateValue);
                expirationDateInput.value = formatDate(expirationDateValue);
            });
            
            // Manejar cambio de carrier para filtrar conductores
            document.getElementById('carrier_id').addEventListener('change', function() {
                const carrierId = this.value;
                const currentDriverId = "{{ $medicalRecord->user_driver_detail_id }}";
                
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