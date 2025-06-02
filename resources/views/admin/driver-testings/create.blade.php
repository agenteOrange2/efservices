@extends('../themes/' . $activeTheme)
@section('title', 'Create New Drug Test')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Testing Drugs Management', 'url' => route('admin.driver-testings.index')],
        ['label' => 'Create New Test', 'active' => true],
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
                Create New Drug Test
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
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
                                <option value="{{ $carrier->id }}">
                                    {{ $carrier->name }} (USDOT: {{ $carrier->usdot ?: 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Selección de Driver -->
                    <div class="mb-5">
                        <label for="user_driver_detail_id" class="form-label">Driver <span class="text-danger">*</span></label>
                        <select id="user_driver_detail_id" name="user_driver_detail_id" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" disabled required>
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
                        <div class="card-body p-5">
                            <div class="mb-2">
                                <span class="text-gray-500">Full Name</span><br>
                                <span id="driver-fullname" class="font-medium">-</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-gray-500">Email</span><br>
                                <span id="driver-email" class="font-medium">-</span>
                            </div>
                            <div class="mb-2">
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
                        <form action="{{ route('admin.driver-testings.store') }}" method="POST" id="create-test-form" enctype="multipart/form-data">
                            @csrf
                            <!-- Campos ocultos para datos seleccionados -->
                            <input type="hidden" name="carrier_id" id="carrier_id_hidden">
                            <input type="hidden" name="user_driver_detail_id" id="user_driver_detail_id_hidden">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Tipo de Prueba -->
                                <div>
                                    <label for="test_type" class="form-label">Test Type</label>
                                    <select name="test_type" id="test_type" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                                        <option value="">-- Select test type --</option>
                                        @foreach ($testTypes as $testType)
                                            <option value="{{ $testType }}">{{ $testType }}</option>
                                        @endforeach
                                    </select>
                                    @error('test_type')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Administered By -->
                                <div>
                                    <label for="administered_by" class="form-label">Administered By <span class="text-danger">*</span></label>
                                    <input type="text" id="administered_by" name="administered_by" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('administered_by') is-invalid @enderror" value="{{ old('administered_by') }}" required>
                                    @error('administered_by')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Fecha del Test -->
                                <div>
                                    <label for="test_date" class="form-label">Test Date <span class="text-danger">*</span></label>
                                    <input type="date" id="test_date" name="test_date" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('test_date') is-invalid @enderror" value="{{ old('test_date', date('Y-m-d')) }}" required>
                                    @error('test_date')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Ubicación -->
                                <div>
                                    <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                    <select id="location" name="location" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('location') is-invalid @enderror" required>
                                        <option value="">-- Select location --</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location }}" {{ old('location') == $location ? 'selected' : '' }}>
                                                {{ $location }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('location')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Requester Name -->
                                <div>
                                    <label for="requester_name" class="form-label">Requester Name</label>
                                    <input type="text" id="requester_name" name="requester_name" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('requester_name') is-invalid @enderror" value="{{ old('requester_name') }}">
                                    @error('requester_name')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- MRO -->
                                <div>
                                    <label for="mro" class="form-label">MRO</label>
                                    <input type="text" id="mro" name="mro" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('mro') is-invalid @enderror" value="{{ old('mro') }}">
                                    @error('mro')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Bill To -->
                                <div>
                                    <label for="bill_to" class="form-label">Bill To <span class="text-danger">*</span></label>
                                    <select id="bill_to" name="bill_to" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('bill_to') is-invalid @enderror" required>
                                        <option value="">-- Select billing option --</option>
                                        @foreach ($billOptions as $option)
                                            <option value="{{ $option }}" {{ old('bill_to') == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bill_to')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Scheduled Time -->
                                <div>
                                    <label for="scheduled_time" class="form-label">Scheduled Time</label>
                                    <input type="datetime-local" id="scheduled_time" name="scheduled_time" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('scheduled_time') is-invalid @enderror" value="{{ old('scheduled_time') }}">
                                    @error('scheduled_time')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Test Result -->
                                <div>
                                    <label for="test_result" class="form-label">Test Result</label>
                                    <select id="test_result" name="test_result" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('test_result') is-invalid @enderror">
                                        <option value="">-- Select result --</option>
                                        <option value="Negative" {{ old('test_result') == 'Negative' ? 'selected' : '' }}>Negative</option>
                                        <option value="Positive" {{ old('test_result') == 'Positive' ? 'selected' : '' }}>Positive</option>
                                        <option value="Inconclusive" {{ old('test_result') == 'Inconclusive' ? 'selected' : '' }}>Inconclusive</option>
                                        <option value="Refused" {{ old('test_result') == 'Refused' ? 'selected' : '' }}>Refused</option>
                                        <option value="Pending" {{ old('test_result', 'Pending') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    </select>
                                    @error('test_result')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Next Test Due -->
                                <div>
                                    <label for="next_test_due" class="form-label">Next Test Due Date</label>
                                    <input type="date" id="next_test_due" name="next_test_due" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('next_test_due') is-invalid @enderror" value="{{ old('next_test_due') }}">
                                    @error('next_test_due')
                                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tipo de Test Checkboxes -->
                            <div class="mt-6">
                                <div class="font-medium mb-2">Test Details</div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_random_test" name="is_random_test" value="1" class="mr-2" {{ old('is_random_test') ? 'checked' : '' }}>
                                        <label for="is_random_test" class="cursor-pointer">Random Test</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_post_accident_test" name="is_post_accident_test" value="1" class="mr-2" {{ old('is_post_accident_test') ? 'checked' : '' }}>
                                        <label for="is_post_accident_test" class="cursor-pointer">Post-Accident Test</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="is_reasonable_suspicion_test" name="is_reasonable_suspicion_test" value="1" class="mr-2" {{ old('is_reasonable_suspicion_test') ? 'checked' : '' }}>
                                        <label for="is_reasonable_suspicion_test" class="cursor-pointer">Reasonable Suspicion Test</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Notas -->
                            <div class="mt-6">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea id="notes" name="notes" rows="3" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Document Upload with Livewire component -->
                            <div class="mt-6">
                                <div class="font-medium mb-2">Upload Files</div>
                                <livewire:components.file-uploader
                                    model-name="driver_testing_files"
                                    :model-index="0"
                                    :auto-upload="true"
                                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 cursor-pointer"
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
                                    Create Drug Test
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Referencias a elementos DOM
        const carrierSelect = document.getElementById('carrier_id');
        const driverSelect = document.getElementById('user_driver_detail_id');
        const carrierIdHidden = document.getElementById('carrier_id_hidden');
        const userDriverDetailIdHidden = document.getElementById('user_driver_detail_id_hidden');
        const driverLoading = document.getElementById('driver-loading');
        const driverDetailCard = document.getElementById('driver-detail-card');
        const createTestForm = document.getElementById('create-test-form');
        
        // Elementos del detalle del conductor
        const driverFullname = document.getElementById('driver-fullname');
        const driverLicense = document.getElementById('driver-license');
        const driverLicenseState = document.getElementById('driver-license-state');
        const driverLicenseExp = document.getElementById('driver-license-exp');
        const driverAddress = document.getElementById('driver-address');

        // Manejar cambio de carrier para cargar drivers
        carrierSelect.addEventListener('change', function() {
            const carrierId = this.value;
            carrierIdHidden.value = carrierId;
            
            // Resetear estado inicial
            driverSelect.disabled = true;
            userDriverDetailIdHidden.value = '';
            driverDetailCard.classList.add('hidden');
            
            // Limpiar opciones anteriores del select de drivers
            driverSelect.innerHTML = '<option value="">-- Select a driver --</option>';

            if (!carrierId) return;

            // Mostrar indicador de carga
            driverLoading.classList.remove('hidden');
            
            // Usar la ruta API alternativa que sabemos que existe
            fetch(`/api/active-drivers-by-carrier/${carrierId}`)
                .then(response => response.json())
                .then(data => {
                    driverLoading.classList.add('hidden');
                    
                    // Depurar estructura de respuesta
                    console.log('API Response:', data);
                    
                    // Guardar los datos completos para acceder a ellos luego
                    window.driversData = data;
                    
                    if (data && data.length > 0) {
                        // Agregar options al select
                        data.forEach(function(driver) {
                            const option = document.createElement('option');
                            option.value = driver.id;
                            
                            // Crear nombre completo del conductor
                            const fullName = `${driver.user.name} ${driver.user.middle_name || ''} ${driver.user.last_name || ''}`.replace(/\s+/g, ' ').trim();
                            option.textContent = fullName;
                            
                            driverSelect.appendChild(option);
                        });
                        
                        // Habilitar el select
                        driverSelect.disabled = false;
                    } else {
                        // Agregar un mensaje si no hay drivers
                        const option = document.createElement('option');
                        option.value = '';
                        option.disabled = true;
                        option.textContent = 'No active drivers found for this carrier';
                        driverSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error loading drivers:', error);
                    driverLoading.classList.add('hidden');
                    driverSelect.innerHTML = '<option value="">Error loading drivers</option>';
                });
        });

        // Evento al cambiar el driver - usando datos completos
        driverSelect.addEventListener('change', function() {
            const driverId = this.value;
            userDriverDetailIdHidden.value = driverId;
            
            if (!driverId) {
                driverDetailCard.classList.add('hidden');
                return;
            }

            // Buscar los datos completos del driver seleccionado
            const driverData = window.driversData.find(driver => driver.id == driverId);
            
            // Mostrar los detalles completos del driver
            driverDetailCard.classList.remove('hidden');
            
            if (driverData) {
                // Mostrar datos completos del driver
                // Crear nombre completo del conductor
                const fullName = `${driverData.user.name} ${driverData.user.middle_name || ''} ${driverData.user.last_name || ''}`.replace(/\s+/g, ' ').trim();
                driverFullname.textContent = fullName;
                
                // Mostrar información de contacto del conductor
                document.getElementById('driver-email').textContent = driverData.user.email || 'N/A';
                document.getElementById('driver-phone').textContent = driverData.phone || 'N/A';
            } else {
                // Fallback si no encontramos los datos
                const selectedOption = driverSelect.options[driverSelect.selectedIndex];
                driverFullname.textContent = selectedOption.textContent;
                document.getElementById('driver-email').textContent = 'N/A';
                document.getElementById('driver-phone').textContent = 'N/A';
            }
        });

        // Validación del formulario
        createTestForm.addEventListener('submit', function(e) {
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
    });
</script>
@endpush