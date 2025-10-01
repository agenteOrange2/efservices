@extends('../themes/' . $activeTheme)
@section('title', 'Add Driver License')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Licenses', 'url' => route('admin.driver-licenses.index')],
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
                Add New Driver License
            </h2>
            <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.driver-licenses.index') }}" class="btn btn-outline-secondary" variant="primary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                    Back to Driver Licenses
                </x-base.button>
            </div>
        </div>

        <!-- Formulario -->
        <div class="box box--stacked mt-5">            
            <div class="box-body p-5">
                <div class="box-header mb-5">
                <h3 class="box-title text-2xl font-bold">Driver License Information</h3>
            </div>
                <form action="{{ route('admin.driver-licenses.store') }}" method="post" enctype="multipart/form-data" id="licenseForm">
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

                            <!-- Número de licencia -->
                            <div>
                                <x-base.form-label for="license_number" required>License Number</x-base.form-label>
                                <x-base.form-input type="text" id="license_number" name="license_number" placeholder="Enter license number" value="{{ old('license_number') }}" class="@error('license_number') border-danger @enderror" required />
                                @error('license_number')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Clase de licencia -->
                            <div>
                                <x-base.form-label for="license_class" required>License Class</x-base.form-label>
                                <select id="license_class" name="license_class" class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('license_class') border-danger @enderror" required>
                                    <option value="">Select License Class</option>
                                    <option value="A" {{ old('license_class') == 'A' ? 'selected' : '' }}>Class A</option>
                                    <option value="B" {{ old('license_class') == 'B' ? 'selected' : '' }}>Class B</option>
                                    <option value="C" {{ old('license_class') == 'C' ? 'selected' : '' }}>Class C</option>
                                </select>
                                @error('license_class')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado emisor -->
                            <div>
                                <x-base.form-label for="state" required>Issuing State</x-base.form-label>
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
                            <!-- Fecha de emisión -->
                            <div>
                                <x-base.form-label for="issue_date" required>Issue Date</x-base.form-label>
                                <x-base.litepicker id="issue_date" name="issue_date" value="{{ old('issue_date') }}" class="@error('issue_date') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('issue_date')
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

                            <!-- Estado de la licencia -->
                            <div>
                                <x-base.form-label for="status" required>Status</x-base.form-label>
                                <select id="status" name="status" class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('status') border-danger @enderror" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="revoked" {{ old('status') == 'revoked' ? 'selected' : '' }}>Revoked</option>
                                    <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                                @error('status')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Restricciones -->
                            <div>
                                <x-base.form-label for="restrictions">Restrictions</x-base.form-label>
                                <x-base.form-textarea id="restrictions" name="restrictions" placeholder="Enter any license restrictions" class="@error('restrictions') border-danger @enderror" rows="3">{{ old('restrictions') }}</x-base.form-textarea>
                                @error('restrictions')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Endosos -->
                    <div class="mt-8">
                        <h4 class="font-medium">Endorsements</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_hazmat" name="endorsements[]" class="form-check-input" value="hazmat" {{ old('endorsements') && in_array('hazmat', old('endorsements')) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_hazmat" class="form-check-label">Hazmat (H)</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_passenger" name="endorsements[]" class="form-check-input" value="passenger" {{ old('endorsements') && in_array('passenger', old('endorsements')) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_passenger" class="form-check-label">Passenger (P)</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_school_bus" name="endorsements[]" class="form-check-input" value="school_bus" {{ old('endorsements') && in_array('school_bus', old('endorsements')) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_school_bus" class="form-check-label">School Bus (S)</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_tanker" name="endorsements[]" class="form-check-input" value="tanker" {{ old('endorsements') && in_array('tanker', old('endorsements')) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_tanker" class="form-check-label">Tanker (N)</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_double_triple" name="endorsements[]" class="form-check-input" value="double_triple" {{ old('endorsements') && in_array('double_triple', old('endorsements')) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_double_triple" class="form-check-label">Double/Triple (T)</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_motorcycle" name="endorsements[]" class="form-check-input" value="motorcycle" {{ old('endorsements') && in_array('motorcycle', old('endorsements')) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_motorcycle" class="form-check-label">Motorcycle (M)</x-base.form-label>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Documentos -->
                    <div class="mt-8">
                        <h4 class="font-medium mb-3">Documents</h4>
                        
                        <!-- Componente Livewire para carga de archivos -->
                        <livewire:components.file-uploader model-name="license_files" model-index="0" label="Upload License Documents" :existing-files="[]" />
                        <!-- Campo oculto para almacenar los archivos subidos -->
                        <input type="hidden" name="license_files" id="license_files_input">
                    </div>

                    <!-- Botones del formulario -->
                    <div class="flex justify-end mt-8">
                        <x-base.button type="button" class="mr-3" variant="outline-secondary" as="a" href="{{ route('admin.driver-licenses.index') }}">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Save Driver License
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
            const licenseFilesInput = document.getElementById('license_files_input');
            let licenseFiles = [];
            
            // Escuchar eventos emitidos por el componente Livewire
            // Este evento se dispara cuando se sube un nuevo archivo
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('fileUploaded', (data) => {
                    const fileData = data[0];
                    
                    if (fileData.modelName === 'license_files') {
                        // Agregar el archivo al array
                        licenseFiles.push({
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
                        licenseFilesInput.value = JSON.stringify(licenseFiles);
                        console.log('Archivo agregado:', fileData.originalName);
                        console.log('Total archivos:', licenseFiles.length);
                    }
                });
                
                // Este evento se dispara cuando se elimina un archivo
                Livewire.on('fileRemoved', (fileId) => {
                    // Encontrar y eliminar el archivo del array
                    licenseFiles = licenseFiles.filter(file => file.id !== fileId);
                    
                    // Actualizar el input hidden
                    licenseFilesInput.value = JSON.stringify(licenseFiles);
                    console.log('Archivo eliminado, ID:', fileId);
                    console.log('Total archivos restantes:', licenseFiles.length);
                });
            });
            
            const issueDateEl = document.getElementById('issue_date');
            const expirationDateEl = document.getElementById('expiration_date');

            // Formatear las fechas en formato estadounidense (m-d-Y) antes de enviar el formulario
            document.getElementById('licenseForm').addEventListener('submit', function(event) {
                const issueDateEl = document.getElementById('issue_date');
                const expirationDateEl = document.getElementById('expiration_date');
                
                // Verificar que la fecha de expiración es posterior a la fecha de emisión
                const issueDate = new Date(issueDateEl.value);
                const expirationDate = new Date(expirationDateEl.value);
                
                if (expirationDate < issueDate) {
                    event.preventDefault();
                    alert('Expiration date must be after issue date');
                    return;
                }
                
                // Asegurarse de que las fechas estén en formato YYYY-MM-DD que Laravel puede validar
                if (issueDateEl.value) {
                    const issue = new Date(issueDateEl.value);
                    if (!isNaN(issue.getTime())) {
                        const year = issue.getFullYear();
                        const month = (issue.getMonth() + 1).toString().padStart(2, '0');
                        const day = issue.getDate().toString().padStart(2, '0');
                        issueDateEl.value = `${year}-${month}-${day}`;
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