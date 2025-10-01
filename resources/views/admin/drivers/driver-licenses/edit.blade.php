@extends('../themes/' . $activeTheme)
@section('title', 'Edit Driver License')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Licenses', 'url' => route('admin.driver-licenses.index')],
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
                Edit Driver License: {{ $driverLicense->license_number }}
            </h2>
            <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.driver-licenses.index') }}" class="btn btn-outline-secondary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                    Back to Driver Licenses
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.driver-licenses.show', $driverLicense->id) }}" class="btn btn-outline-primary ml-2">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="file-text" />
                    View Documents
                </x-base.button>
            </div>
        </div>

        <!-- Formulario -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Driver License Information</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.driver-licenses.update', $driverLicense->id) }}" method="post" enctype="multipart/form-data" id="licenseForm">
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
                                        <option value="{{ $driver->id }}" {{ old('user_driver_detail_id', $driverLicense->user_driver_detail_id) == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->user->name }} {{ $driver->user->last_name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_driver_detail_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Número de licencia -->
                            <div>
                                <x-base.form-label for="license_number" required>License Number</x-base.form-label>
                                <x-base.form-input type="text" id="license_number" name="license_number" placeholder="Enter license number" value="{{ old('license_number', $driverLicense->license_number) }}" class="@error('license_number') border-danger @enderror" required />
                                @error('license_number')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Clase de licencia -->
                            <div>
                                <x-base.form-label for="license_class" required>License Class</x-base.form-label>
                                <select id="license_class" name="license_class" class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('license_class') border-danger @enderror" required>
                                    <option value="">Select License Class</option>
                                    <option value="Class A" {{ old('license_class', $driverLicense->license_class) == 'Class A' ? 'selected' : '' }}>Class A</option>
                                    <option value="Class B" {{ old('license_class', $driverLicense->license_class) == 'Class B' ? 'selected' : '' }}>Class B</option>
                                    <option value="Class C" {{ old('license_class', $driverLicense->license_class) == 'Class C' ? 'selected' : '' }}>Class C</option>
                                </select>
                                @error('license_class')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado emisor -->
                            <div>
                                <x-base.form-label for="issuing_state" required>Issuing State</x-base.form-label>
                                <select id="issuing_state" name="issuing_state" class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('issuing_state') border-danger @enderror" required>
                                    <option value="">Select State</option>
                                    @foreach(\App\Helpers\Constants::usStates() as $code => $name)
                                        <option value="{{ $code }}" {{ (old('issuing_state', $driverLicense->issuing_state) == $code) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('issuing_state')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="space-y-4">                            
                            <!-- Fecha de emisión -->
                            <div>
                                <x-base.form-label for="issue_date" required>Issue Date</x-base.form-label>
                                <x-base.litepicker id="issue_date" name="issue_date" value="{{ old('issue_date', $driverLicense->issue_date) }}" class="@error('issue_date') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('issue_date')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha de expiración -->
                            <div>
                                <x-base.form-label for="expiration_date" required>Expiration Date</x-base.form-label>
                                <x-base.litepicker id="expiration_date" name="expiration_date" value="{{ old('expiration_date', $driverLicense->expiration_date) }}" class="@error('expiration_date') border-danger @enderror" placeholder="MM/DD/YYYY" required />
                                @error('expiration_date')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div>
                                <x-base.form-label for="status" required>Status</x-base.form-label>
                                <select id="status" name="status" class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('status') border-danger @enderror" required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status', $driverLicense->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expired" {{ old('status', $driverLicense->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="suspended" {{ old('status', $driverLicense->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="revoked" {{ old('status', $driverLicense->status) == 'revoked' ? 'selected' : '' }}>Revoked</option>
                                </select>
                                @error('status')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Restricciones -->
                            <div>
                                <x-base.form-label for="restrictions">Restrictions</x-base.form-label>
                                <x-base.form-textarea id="restrictions" name="restrictions" placeholder="Enter any license restrictions" class="@error('restrictions') border-danger @enderror" rows="4">{{ old('restrictions', $driverLicense->restrictions) }}</x-base.form-textarea>
                                @error('restrictions')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Endorsements -->
                    <div class="mt-8">
                        <h4 class="font-medium">Endorsements</h4>
                        @php
                            $endorsements = old('endorsements', $driverLicense->endorsements ?? []);
                            if (is_string($endorsements)) {
                                $endorsements = json_decode($endorsements, true) ?? [];
                            }
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_hazmat" name="endorsements[]" class="form-check-input" value="hazmat" {{ in_array('hazmat', $endorsements) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_hazmat" class="form-check-label">Hazmat (H)</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_passenger" name="endorsements[]" class="form-check-input" value="passenger" {{ in_array('passenger', $endorsements) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_passenger" class="form-check-label">Passenger (P)</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_school_bus" name="endorsements[]" class="form-check-input" value="school_bus" {{ in_array('school_bus', $endorsements) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_school_bus" class="form-check-label">School Bus (S)</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_tank" name="endorsements[]" class="form-check-input" value="tank" {{ in_array('tank', $endorsements) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_tank" class="form-check-label">Tank Vehicle (N)</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_double_triple" name="endorsements[]" class="form-check-input" value="double_triple" {{ in_array('double_triple', $endorsements) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_double_triple" class="form-check-label">Double/Triple (T)</x-base.form-label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="endorsement_motorcycle" name="endorsements[]" class="form-check-input" value="motorcycle" {{ in_array('motorcycle', $endorsements) ? 'checked' : '' }}>
                                <x-base.form-label for="endorsement_motorcycle" class="form-check-label">Motorcycle (M)</x-base.form-label>
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
                                model-name="license_files"
                                :model-index="0"
                                :label="'Upload License Documents'"
                                :existing-files="$existingFilesArray"
                            />
                            <!-- Campo oculto para almacenar los archivos subidos -->
                            <input type="hidden" name="license_files" id="license_files_input">
                        </div>
                    </div>

                    <!-- Botones del formulario -->
                    <div class="mt-8 flex justify-end">
                        <x-base.button as="a" href="{{ route('admin.driver-licenses.index') }}" variant="outline-secondary" class="mr-2">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Update Driver License
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
            
            // Inicializar con archivos existentes, si hay alguno
            @if(isset($existingFilesArray) && count($existingFilesArray) > 0)
                licenseFiles = @json($existingFilesArray);
                licenseFilesInput.value = JSON.stringify(licenseFiles);
            @endif
            
            // Escuchar eventos emitidos por el componente Livewire
            document.addEventListener('livewire:initialized', () => {
                // Este evento se dispara cuando se sube un nuevo archivo
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
                Livewire.on('fileRemoved', (eventData) => {
                    console.log('Evento fileRemoved recibido:', eventData);
                    const data = eventData[0]; // Los datos vienen como primer elemento del array
                    const fileId = data.fileId;
                    
                    // Verificar si el archivo es permanente (no temporal) y pertenece a nuestro modelo
                    if (data.modelName === 'license_files' && !data.isTemp) {
                        console.log('Eliminando documento permanente con ID:', fileId);
                        
                        // Hacer llamada AJAX para eliminar el documento físicamente
                        fetch(`{{ url('admin/driver-licenses/document') }}/${fileId}/ajax`, {
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
                    licenseFiles = licenseFiles.filter(file => file.id != fileId);
                    
                    // Actualizar el input hidden
                    licenseFilesInput.value = JSON.stringify(licenseFiles);
                    console.log('Archivo eliminado, ID:', fileId);
                    console.log('Total archivos restantes:', licenseFiles.length);
                });
            });
            
            // Verificar que la fecha de expiración es posterior a la fecha de emisión
            document.getElementById('licenseForm').addEventListener('submit', function(event) {
                // Obtener valores actuales (en formato MM/DD/YYYY)
                const issueDateInput = document.getElementById('issue_date');
                const expirationDateInput = document.getElementById('expiration_date');
                
                const issueDateValue = issueDateInput.value;
                const expirationDateValue = expirationDateInput.value;
                
                // Crear objetos Date para validación
                const issueDate = new Date(issueDateValue);
                const expirationDate = new Date(expirationDateValue);
                
                // Verificar que las fechas sean válidas
                if (isNaN(issueDate.getTime()) || isNaN(expirationDate.getTime())) {
                    event.preventDefault();
                    alert('Please enter valid dates');
                    return;
                }
                
                // Verificar que la fecha de expiración es posterior a la fecha de emisión
                if (expirationDate < issueDate) {
                    event.preventDefault();
                    alert('Expiration date must be after or equal to issue date');
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
                issueDateInput.value = formatDate(issueDateValue);
                expirationDateInput.value = formatDate(expirationDateValue);
            });
            
            // Manejar cambio de carrier para filtrar conductores
            document.getElementById('carrier_id').addEventListener('change', function() {
                const carrierId = this.value;
                const currentDriverId = "{{ $driverLicense->user_driver_detail_id }}";
                
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