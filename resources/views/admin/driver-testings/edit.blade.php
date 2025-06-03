@extends('../themes/' . $activeTheme)
@section('title', 'Edit Drug Test')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Testing Drugs Management', 'url' => route('admin.driver-testings.index')],
        ['label' => 'Edit Test', 'active' => true],
    ];
@endphp
@section('subcontent')
    <div>
        <!-- Mensajes Flash -->
        @if (session()->has('error'))
            <div class="alert alert-danger flex items-center mb-5">
                <x-base.lucide class="w-6 h-6 mr-2" icon="alert-triangle" />
                {{ session('error') }}
            </div>
        @endif

        <!-- Cabecera -->
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">
                Edit Drug Test #{{ $driverTesting->id }}
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0 gap-2">
                <a href="{{ route('admin.driver-testings.show', $driverTesting->id) }}">
                    <x-base.button variant="outline-primary" class="flex items-center">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="eye" />
                        View Details
                    </x-base.button>
                </a>
                <a href="{{ route('admin.driver-testings.index') }}">
                    <x-base.button variant="outline-secondary" class="flex items-center">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                        Back to List
                    </x-base.button>
                </a>
            </div>
        </div>

        <!-- Formulario y Selección de Carrier/Driver -->
        <div class="grid grid-cols-12 gap-6 mt-5">
            <!-- Panel de Selección de Carrier y Driver -->
            <div class="col-span-12 xl:col-span-4">
                <div class="box box--stacked p-5">
                    <div class="flex items-center border-b border-slate-200/60 pb-5 mb-5">
                        <div class="font-medium truncate text-base mr-5">Select Carrier & Driver</div>
                    </div>

                    <!-- Selección de Carrier -->
                    <div class="mb-5">
                        <label for="carrier_id" class="form-label">Carrier <span class="text-danger">*</span></label>
                        <select id="carrier_id" name="carrier_id" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">-- Select a carrier --</option>
                            @foreach ($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ old('carrier_id', $driverTesting->carrier_id) == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }} (USDOT: {{ $carrier->usdot ?: 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Selección de Driver -->
                    <div class="mb-5">
                        <label for="user_driver_detail_id" class="form-label">Driver <span class="text-danger">*</span></label>
                        <select id="user_driver_detail_id" name="user_driver_detail_id" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                            <option value="">-- Select a driver --</option>
                        </select>
                        <div id="driver-loading" class="mt-2 hidden">
                            <div class="flex items-center">
                                <div class="w-4 h-4 animate-spin mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg>
                                </div>
                                <span class="text-xs text-slate-500">Loading drivers...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Driver Details Card -->
                    <div id="driver-detail-card" class="card border shadow-sm mt-4 hidden">
                        <div class="card-header">
                            <h3 class="font-medium text-base">Driver Details</h3>
                        </div>
                        <div class="card-body grid grid-cols-1 gap-4">
                            <div>
                                <span class="text-gray-500">Name</span><br>
                                <span id="driver-fullname" class="font-medium">-</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Email</span><br>
                                <span id="driver-email" class="font-medium">-</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Phone</span><br>
                                <span id="driver-phone" class="font-medium">-</span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel del Formulario Principal -->
            <div class="col-span-12 xl:col-span-8">
                <div class="box box--stacked">
                    <div class="box-header box-header--transparent">
                        <div class="box-title">Drug & Alcohol Test Details</div>
                    </div>
                    <div class="box-body p-5">
                        <form action="{{ route('admin.driver-testings.update', $driverTesting->id) }}" method="POST" id="edit-test-form" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <!-- Campos ocultos para datos seleccionados -->
                            <input type="hidden" name="carrier_id" id="carrier_id_hidden" value="{{ old('carrier_id', $driverTesting->carrier_id) }}">
                            <input type="hidden" name="user_driver_detail_id" id="user_driver_detail_id_hidden" value="{{ old('user_driver_detail_id', $driverTesting->user_driver_detail_id) }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">                        

                        <!-- Fecha del Test -->
                        <div>
                            <label for="test_date" class="form-label">Test Date <span class="text-danger">*</span></label>
                            <input type="date" id="test_date" name="test_date" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('test_date') is-invalid @enderror" 
                                value="{{ old('test_date', $driverTesting->test_date->format('Y-m-d')) }}" required>
                            @error('test_date')
                                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Fecha programada -->
                        <div>
                            <label for="scheduled_time" class="form-label">Scheduled Time</label>
                            <input type="datetime-local" id="scheduled_time" name="scheduled_time" 
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('scheduled_time') is-invalid @enderror" 
                                value="{{ old('scheduled_time', $driverTesting->scheduled_time ? $driverTesting->scheduled_time->format('Y-m-d\TH:i') : '') }}">
                            @error('scheduled_time')
                                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tipo de Test -->
                        <div>
                            <label for="test_type" class="form-label">Test Type <span class="text-danger">*</span></label>
                            <select id="test_type" name="test_type" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('test_type') is-invalid @enderror" required>
                                <option value="">Select Test Type</option>
                                @foreach (\App\Models\Admin\Driver\DriverTesting::getTestTypes() as $type)
                                    <option value="{{ $type }}" {{ old('test_type', $driverTesting->test_type) == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('test_type')
                                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ubicación del Test -->
                        <div>
                            <label for="location" class="form-label">Test Location <span class="text-danger">*</span></label>
                            <select id="location" name="location" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('location') is-invalid @enderror" required>
                                <option value="">Select Location</option>
                                @foreach (\App\Models\Admin\Driver\DriverTesting::getLocations() as $location)
                                    <option value="{{ $location }}" {{ old('location', $driverTesting->location) == $location ? 'selected' : '' }}>
                                        {{ $location }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location')
                                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Solicita la prueba -->
                        <div>
                            <label for="requester_name" class="form-label">Test Requested By</label>
                            <input type="text" id="requester_name" name="requester_name" 
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('requester_name') is-invalid @enderror" 
                                value="{{ old('requester_name', $driverTesting->requester_name) }}" placeholder="Name of person requesting the test">
                            @error('requester_name')
                                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Administrado por -->
                        <div>
                            <label for="administered_by" class="form-label">Administered By</label>
                            <input type="text" id="administered_by" name="administered_by" 
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('administered_by') is-invalid @enderror" 
                                value="{{ old('administered_by', $driverTesting->administered_by) }}" placeholder="Name of test administrator">
                            @error('administered_by')
                                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Resultado del Test -->
                        <div>
                            <label for="test_result" class="form-label">Test Result</label>
                            <select id="test_result" name="test_result" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('test_result') is-invalid @enderror">
                                <option value="pending" {{ old('test_result', $driverTesting->test_result) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="passed" {{ old('test_result', $driverTesting->test_result) == 'passed' ? 'selected' : '' }}>Passed</option>
                                <option value="failed" {{ old('test_result', $driverTesting->test_result) == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                            @error('test_result')
                                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('status') is-invalid @enderror">
                                @foreach (\App\Models\Admin\Driver\DriverTesting::getStatuses() as $key => $value)
                                    <option value="{{ $key }}" {{ old('status', $driverTesting->status) == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Siguiente fecha de prueba -->
                        <div>
                            <label for="next_test_due" class="form-label">Next Test Due</label>
                            <input type="date" id="next_test_due" name="next_test_due" 
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8l @error('next_test_due') is-invalid @enderror" 
                                value="{{ old('next_test_due', $driverTesting->next_test_due ? $driverTesting->next_test_due->format('Y-m-d') : '') }}">
                            @error('next_test_due')
                                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Facturación a -->
                        <div>
                            <label for="bill_to" class="form-label">Bill To</label>
                            <select id="bill_to" name="bill_to" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('bill_to') is-invalid @enderror">
                                <option value="">Select Billing Option</option>
                                @foreach (\App\Models\Admin\Driver\DriverTesting::getBillOptions() as $option)
                                    <option value="{{ $option }}" {{ old('bill_to', $driverTesting->bill_to) == $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bill_to')
                                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="form-label">Test Type Categories</label>
                        <div class="flex flex-wrap gap-4">
                            <div class="form-check">
                                <input id="is_random_test" name="is_random_test" type="checkbox" class="form-check-input" 
                                    value="1" {{ old('is_random_test', $driverTesting->is_random_test) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_random_test">Random Test</label>
                            </div>
                            <div class="form-check">
                                <input id="is_post_accident_test" name="is_post_accident_test" type="checkbox" class="form-check-input" 
                                    value="1" {{ old('is_post_accident_test', $driverTesting->is_post_accident_test) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_post_accident_test">Post Accident Test</label>
                            </div>
                            <div class="form-check">
                                <input id="is_reasonable_suspicion_test" name="is_reasonable_suspicion_test" type="checkbox" class="form-check-input" 
                                    value="1" {{ old('is_reasonable_suspicion_test', $driverTesting->is_reasonable_suspicion_test) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_reasonable_suspicion_test">Reasonable Suspicion Test</label>
                            </div>
                            <div class="form-check">
                                <input id="is_pre_employment_test" name="is_pre_employment_test" type="checkbox" class="form-check-input" 
                                    value="1" {{ old('is_pre_employment_test', $driverTesting->is_pre_employment_test ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_pre_employment_test">Pre-Employment Test</label>
                            </div>
                            <div class="form-check">
                                <input id="is_follow_up_test" name="is_follow_up_test" type="checkbox" class="form-check-input" 
                                    value="1" {{ old('is_follow_up_test', $driverTesting->is_follow_up_test ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_follow_up_test">Follow-Up Test</label>
                            </div>
                            <div class="form-check">
                                <input id="is_return_to_duty_test" name="is_return_to_duty_test" type="checkbox" class="form-check-input" 
                                    value="1" {{ old('is_return_to_duty_test', $driverTesting->is_return_to_duty_test ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_return_to_duty_test">Return-To-Duty Test</label>
                            </div>
                            <div class="form-check">
                                <input id="is_other_reason_test" name="is_other_reason_test" type="checkbox" class="form-check-input" 
                                    value="1" {{ old('is_other_reason_test', $driverTesting->is_other_reason_test ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_other_reason_test">Other Reason</label>
                            </div>
                        </div>
                        <!-- Campo de descripción para Other Reason -->
                        <div id="other_reason_container" class="mt-3" style="display: none;">
                            <input type="text" id="other_reason_description" name="other_reason_description" 
                                   class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" 
                                   placeholder="Specify other reason" 
                                   value="{{ old('other_reason_description', $driverTesting->other_reason_description) }}">
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="4" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('notes') is-invalid @enderror"
                            placeholder="Add any additional notes here">{{ old('notes', $driverTesting->notes) }}</textarea>
                        @error('notes')
                            <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                        @enderror
                    </div>

                            <!-- Adjuntar Archivos -->
                            <div class="mt-6">
                                <label class="form-label">Attach Files (Optional)</label>
                                <p class="text-sm text-slate-500 mb-3">Upload any supporting documents such as test results or reports.</p>
                                
                                @php
                                $existingFilesArray = [];
                                foreach ($driverTesting->getMedia('document_attachments') as $document) {
                                    try {
                                        $existingFilesArray[] = [
                                            'id' => $document->id,
                                            'name' => $document->file_name ?? 'Unknown',
                                            'file_name' => $document->file_name ?? 'Unknown',
                                            'mime_type' => $document->mime_type ?? 'application/octet-stream',
                                            'size' => $document->size ?? 0,
                                            'created_at' => $document->created_at ? $document->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                                            'url' => $document->getUrl(),
                                            'is_temp' => false,
                                            'media_id' => $document->id
                                        ];
                                    } catch (\Exception $e) {
                                        \Illuminate\Support\Facades\Log::error('Error al procesar documento para vista', [
                                            'document_id' => $document->id ?? 'unknown',
                                            'error' => $e->getMessage()
                                        ]);
                                    }
                                }
                                @endphp

                                <livewire:components.file-uploader
                                    model-name="driver_testing_files"
                                    :model-index="0"
                                    :label="'Upload Files'"
                                    :existing-files="$existingFilesArray"
                                />
                                <!-- Campo oculto para almacenar los archivos subidos -->
                                <input type="hidden" name="driver_testing_files" id="driver_testing_files_input">
                                @error('driver_testing_files')
                                    <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="btn btn-primary">
                                    <x-base.lucide icon="save" class="w-4 h-4 mr-2" />
                                    Update Drug Test
                                </button>
                            </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Referencias a elementos del DOM
        const carrierSelect = document.getElementById('carrier_id');
        const driverSelect = document.getElementById('user_driver_detail_id');
        const editTestForm = document.getElementById('edit-test-form');
        const carrierIdHidden = document.getElementById('carrier_id_hidden');
        const userDriverDetailIdHidden = document.getElementById('user_driver_detail_id_hidden');
        const driverDetailCard = document.getElementById('driver-detail-card');
        const driverFullname = document.getElementById('driver-fullname');
        const driverLoading = document.getElementById('driver-loading');
        const currentDriverId = {{ $driverTesting->user_driver_detail_id }};

        // Función para cargar conductores según el carrier seleccionado
        function loadDrivers(carrierId, callback) {
            if (!carrierId) {
                driverSelect.innerHTML = '<option value="">-- Select a driver --</option>';
                driverSelect.disabled = true;
                driverDetailCard.classList.add('hidden');
                return;
            }

            // Mostrar indicador de carga
            driverSelect.disabled = false;
            driverLoading.classList.remove('hidden');
            driverSelect.innerHTML = '<option value="" disabled>Loading drivers...</option>';

            fetch(`/api/active-drivers-by-carrier/${carrierId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    driverSelect.innerHTML = '<option value="">Select Driver</option>';
                    
                    data.forEach(driver => {
                        const option = document.createElement('option');
                        option.value = driver.id;
                        option.textContent = `${driver.user.name} ${driver.last_name || ''}`;
                        option.dataset.email = driver.user.email || '';
                        option.dataset.phone = driver.phone || '';
                        option.selected = (driver.id == currentDriverId);
                        driverSelect.appendChild(option);
                    });
                    
                    driverLoading.classList.add('hidden');
                    
                    // Si hay un conductor seleccionado, mostrar sus detalles
                    if (driverSelect.value) {
                        showDriverDetails(driverSelect.selectedIndex);
                    }
                    
                    if (callback) callback();
                })
                .catch(error => {
                    console.error('Error loading drivers:', error);
                    driverSelect.innerHTML = '<option value="">Error loading drivers</option>';
                    driverLoading.classList.add('hidden');
                });
        }

        // Función para mostrar detalles del conductor
        function showDriverDetails(selectedIndex) {
            if (selectedIndex <= 0) {
                driverDetailCard.classList.add('hidden');
                return;
            }
            
            const selectedOption = driverSelect.options[selectedIndex];
            const driverName = selectedOption.textContent;
            const driverEmail = selectedOption.dataset.email || 'N/A';
            const driverPhone = selectedOption.dataset.phone || 'N/A';
            
            driverFullname.textContent = driverName;
            document.getElementById('driver-email').textContent = driverEmail;
            document.getElementById('driver-phone').textContent = driverPhone;
            
            driverDetailCard.classList.remove('hidden');
        }

        // Event listeners
        carrierSelect.addEventListener('change', function() {
            loadDrivers(this.value);
        });

        driverSelect.addEventListener('change', function() {
            showDriverDetails(this.selectedIndex);
        });

        // Validación del formulario
        editTestForm.addEventListener('submit', function(e) {
            // Obtener valores de campos
            const carrierId = carrierSelect.value;
            const driverId = driverSelect.value;
            
            // Validar que se haya seleccionado un carrier y un driver
            if (!carrierId || !driverId) {
                e.preventDefault();
                alert('Please select a carrier and a driver');
                return false;
            }
            
            // Actualizar campos ocultos
            carrierIdHidden.value = carrierId;
            userDriverDetailIdHidden.value = driverId;
            
            return true;
        });
        
        // Control de visibilidad para el campo Other Reason Description
        const otherReasonCheckbox = document.getElementById('is_other_reason_test');
        const otherReasonContainer = document.getElementById('other_reason_container');
        
        // Función para manejar la visibilidad del campo de descripción
        function toggleOtherReasonField() {
            if (otherReasonCheckbox.checked) {
                otherReasonContainer.style.display = 'block';
            } else {
                otherReasonContainer.style.display = 'none';
            }
        }
        
        // Manejar cambio en el checkbox
        otherReasonCheckbox.addEventListener('change', toggleOtherReasonField);
        
        // Inicializar estado al cargar la página
        toggleOtherReasonField();
        
        // Inicializar array para archivos subidos
        let uploadedFiles = [];
        const driverTestingFilesInput = document.getElementById('driver_testing_files_input');
        driverTestingFilesInput.value = JSON.stringify(uploadedFiles);
        
        // Escuchar eventos de Livewire 3
        window.addEventListener('livewire:initialized', () => {
            console.log('Livewire 3 initialized - registering event listeners');
            
            // Escuchar el evento fileUploaded del componente Livewire
            Livewire.on('fileUploaded', (eventData) => {
                console.log('Archivo subido:', eventData);
                // Extraer los datos del evento
                const data = eventData[0]; // Los datos vienen como primer elemento del array
                
                if (data.modelName === 'driver_testing_files') {
                    // Añadir el archivo al array con la estructura correcta que espera el controlador
                    uploadedFiles.push({
                        path: data.tempPath, // Mantener el nombre que envía el componente
                        original_name: data.originalName, // Mantener el nombre que envía el componente
                        mime_type: data.mimeType,
                        size: data.size
                    });
                    
                    // Actualizar el campo oculto con el nuevo array
                    driverTestingFilesInput.value = JSON.stringify(uploadedFiles);
                    console.log('Archivos actualizados:', driverTestingFilesInput.value);
                }
            });
            
            // Escuchar el evento fileRemoved del componente Livewire
            Livewire.on('fileRemoved', (eventData) => {
                console.log('Archivo eliminado:', eventData);
                // Extraer los datos del evento
                const data = eventData[0]; // Los datos vienen como primer elemento del array
                
                if (data.modelName === 'driver_testing_files') {
                    // Eliminar el archivo del array
                    const fileId = data.fileId;
                    uploadedFiles = uploadedFiles.filter((file, index) => {
                        // Para archivos temporales, el ID contiene un timestamp
                        if (fileId.startsWith('temp_') && index === uploadedFiles.length - 1) {
                            // Eliminar el último archivo añadido si es temporal
                            return false;
                        }
                        return true;
                    });
                    
                    // Actualizar el campo oculto con el nuevo array
                    driverTestingFilesInput.value = JSON.stringify(uploadedFiles);
                    console.log('Archivos actualizados después de eliminar:', driverTestingFilesInput.value);
                }
            });
        });

        // Cargar drivers al iniciar para mostrar el driver actual
        loadDrivers(carrierSelect.value, function() {
            // Si después de cargar los drivers, ninguno está seleccionado, seleccionamos el correcto
            if (!driverSelect.value && currentDriverId) {
                driverSelect.value = currentDriverId;
                showDriverDetails(driverSelect.selectedIndex);
            }
        });
    });
</script>
@endpush
