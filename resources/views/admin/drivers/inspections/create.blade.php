@extends('../themes/' . $activeTheme)
@section('title', 'Add Inspection Record')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Inspections Management', 'url' => route('admin.inspections.index')],
        ['label' => 'Add Inspection Record', 'active' => true],
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

        <!-- Cabecera -->
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">
                Add New Inspection Record
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.inspections.index') }}" class="w-full sm:w-auto"
                    variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Inspections
                </x-base.button>
            </div>
        </div>

        <!-- Formulario de Creación -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Inspection Details</h3>
            </div>
            
            <div class="box-body p-5">
                <form action="{{ route('admin.inspections.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                            <select id="carrier_id" name="carrier_id"
                                class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Carrier</option>
                                @foreach ($carriers as $carrier)
                                    <option value="{{ $carrier->id }}">
                                        {{ $carrier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('carrier_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Driver Selection -->
                        <div>
                            <x-base.form-label for="user_driver_detail_id">Driver</x-base.form-label>
                            <select id="user_driver_detail_id" name="user_driver_detail_id"
                                class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
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
                        
                        <!-- Vehicle Selection (Optional) -->
                        <div>
                            <x-base.form-label for="vehicle_id">Vehicle (Optional)</x-base.form-label>
                            <select id="vehicle_id" name="vehicle_id"
                                class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Vehicle</option>
                                <!-- Los vehículos se cargarán dinámicamente por JavaScript -->
                            </select>
                            @error('vehicle_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="inspection_date">Inspection Date</x-base.form-label>
                            <x-base.litepicker id="inspection_date" name="inspection_date" 
                                value="{{ old('inspection_date') }}" data-format="MM-DD-YYYY" class="block w-full @error('inspection_date') border-danger @enderror" placeholder="MM-DD-YYYY" />
                            @error('inspection_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-base.form-label for="inspection_type">Inspection Type</x-base.form-label>
                            <select id="inspection_type" name="inspection_type"
                                class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Type</option>
                                <option value="DOT Roadside">DOT Roadside</option>
                                <option value="State Police">State Police</option>
                                <option value="Annual DOT">Annual DOT</option>
                                <option value="Pre-trip">Pre-trip</option>
                                <option value="Post-trip">Post-trip</option>
                                <option value="Border Crossing">Border Crossing</option>
                                <option value="Weigh Station">Weigh Station</option>
                            </select>
                            @error('inspection_type')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="inspection_level">Inspection Level</x-base.form-label>
                            <select id="inspection_level" name="inspection_level"
                                class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Level</option>
                                <option value="Level I">Level I - Full Inspection</option>
                                <option value="Level II">Level II - Walk-Around</option>
                                <option value="Level III">Level III - Driver Only</option>
                                <option value="Level IV">Level IV - Special Inspection</option>
                                <option value="Level V">Level V - Vehicle Only</option>
                                <option value="Level VI">Level VI - Hazmat Inspection</option>
                            </select>
                            @error('inspection_level')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <x-base.form-label for="inspector_name">Inspector Name</x-base.form-label>
                            <x-base.form-input id="inspector_name" name="inspector_name" type="text" 
                                value="{{ old('inspector_name') }}" class="block w-full" />
                            @error('inspector_name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="inspector_number">Inspector Number/Badge</x-base.form-label>
                            <x-base.form-input id="inspector_number" name="inspector_number" type="text" 
                                value="{{ old('inspector_number') }}" class="block w-full" />
                            @error('inspector_number')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="location">Inspection Location</x-base.form-label>
                            <x-base.form-input id="location" name="location" type="text" 
                                value="{{ old('location') }}" class="block w-full" />
                            @error('location')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-base.form-label for="status">Status</x-base.form-label>
                            <select id="status" name="status"
                                class="form-select block w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Status</option>
                                <option value="Pass">Pass</option>
                                <option value="Fail">Fail</option>
                                <option value="Conditional Pass">Conditional Pass</option>
                                <option value="Out of Service">Out of Service</option>
                                <option value="Pending">Pending</option>
                            </select>
                            @error('status')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="is_vehicle_safe_to_operate">Vehicle Safe to Operate?</x-base.form-label>
                            <div class="mt-2">
                                <label class="form-check mr-2 inline-block">
                                    <input id="vehicle_safe_yes" name="is_vehicle_safe_to_operate" type="radio" class="form-check-input" value="1" {{ old('is_vehicle_safe_to_operate') == '1' ? 'checked' : 'checked' }}>
                                    <span class="form-check-label">Yes</span>
                                </label>
                                <label class="form-check mr-2 inline-block">
                                    <input id="vehicle_safe_no" name="is_vehicle_safe_to_operate" type="radio" class="form-check-input" value="0" {{ old('is_vehicle_safe_to_operate') == '0' ? 'checked' : '' }}>
                                    <span class="form-check-label">No</span>
                                </label>
                            </div>
                            @error('is_vehicle_safe_to_operate')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Comentarios -->
                    <div class="mt-4">
                        <x-base.form-label for="comments">Comments/Notes</x-base.form-label>
                        <x-base.form-textarea id="comments" name="comments" 
                            rows="4" class="block w-full">{{ old('comments') }}</x-base.form-textarea>
                    </div>
                    
                    <!-- Sección de Documentos -->
                    <div class="mt-6">
                        <h4 class="font-medium mb-3">Documents</h4>
                        
                        <!-- Componente Livewire para carga de archivos -->
                        <div class="border border-slate-200 rounded-md p-4 bg-slate-50">
                            @livewire('components.file-uploader', [
                                'modelName' => 'inspection_files',
                                'modelIndex' => 0,
                                'label' => 'Upload Inspection Documents',
                                'existingFiles' => []
                            ])
                        </div>
                        <!-- Campo oculto para almacenar los archivos subidos -->
                        <input type="hidden" name="inspection_files" id="inspection_files_input">
                    </div>
                    
                    <!-- Submit Buttons -->
                    <div class="flex justify-end mt-5">
                        <x-base.button as="a" href="{{ route('admin.inspections.index') }}" variant="outline-secondary" class="mr-2">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Create Inspection Record
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables para almacenar los valores antiguos (si existen por errores de validación)
            const oldCarrierId = '{{ old("carrier_id") }}';
            const oldDriverId = '{{ old("user_driver_detail_id") }}';
            const oldVehicleId = '{{ old("vehicle_id") }}';
            
            // Manejar cambio de carrier para filtrar conductores
            document.getElementById('carrier_id').addEventListener('change', function() {
                const carrierId = this.value;
                
                // Limpiar el select de conductores usando JavaScript nativo
                const driverSelect = document.getElementById('user_driver_detail_id');
                driverSelect.innerHTML = '<option value="">Select Driver</option>';
                
                // Limpiar el select de vehículos
                const vehicleSelect = document.getElementById('vehicle_id');
                vehicleSelect.innerHTML = '<option value="">Select Vehicle (Optional)</option>';
                
                if (carrierId) {
                    // Hacer una petición AJAX para obtener los conductores activos de esta transportista
                    fetch(`{{ url('/admin/inspections/carrier') }}/${carrierId}/drivers`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                // Hay conductores activos, agregarlos al select
                                data.forEach(function(driver) {
                                    const option = document.createElement('option');
                                    option.value = driver.id;
                                    option.textContent = `${driver.user.name} ${driver.user.last_name || ''}`;
                                    
                                    // Si este driver es el que estaba seleccionado previamente, seleccionarlo
                                    if (driver.id.toString() === oldDriverId) {
                                        option.selected = true;
                                    }
                                    
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
            
            // Manejar cambio de conductor para filtrar vehículos
            document.getElementById('user_driver_detail_id').addEventListener('change', function() {
                const driverId = this.value;
                
                // Limpiar el select de vehículos usando JavaScript nativo
                const vehicleSelect = document.getElementById('vehicle_id');
                vehicleSelect.innerHTML = '<option value="">Select Vehicle (Optional)</option>';
                
                if (driverId) {
                    // Hacer una petición AJAX para obtener los vehículos relacionados con este conductor
                    fetch(`{{ url('/admin/inspections/driver') }}/${driverId}/vehicles`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                // Hay vehículos, agregarlos al select
                                data.forEach(function(vehicle) {
                                    const option = document.createElement('option');
                                    option.value = vehicle.id;
                                    option.textContent = `${vehicle.company_unit_number} - ${vehicle.year} ${vehicle.make} ${vehicle.model}`;
                                    
                                    // Si este vehículo es el que estaba seleccionado previamente, seleccionarlo
                                    if (vehicle.id.toString() === oldVehicleId) {
                                        option.selected = true;
                                    }
                                    
                                    vehicleSelect.appendChild(option);
                                });
                            } else {
                                // No hay vehículos para este conductor
                                const option = document.createElement('option');
                                option.value = '';
                                option.disabled = true;
                                option.textContent = 'No vehicles found for this driver';
                                vehicleSelect.appendChild(option);
                            }
                        })
                        .catch(error => {
                            console.error('Error loading vehicles:', error);
                            const option = document.createElement('option');
                            option.value = '';
                            option.disabled = true;
                            option.textContent = 'Error loading vehicles';
                            vehicleSelect.appendChild(option);
                        });
                }
            });
            
            // Inicializar selectores si hay valores antiguos (para errores de validación)
            if (oldCarrierId) {
                // Seleccionar carrier
                const carrierSelect = document.getElementById('carrier_id');
                carrierSelect.value = oldCarrierId;
                
                // Disparar manualmente el evento change para cargar los drivers
                carrierSelect.dispatchEvent(new Event('change'));
            }
            
            // Inicializar tipo de inspección
            const oldInspectionType = '{{ old("inspection_type") }}';
            if (oldInspectionType) {
                const typeSelect = document.getElementById('inspection_type');
                typeSelect.value = oldInspectionType;
                typeSelect.dispatchEvent(new Event('change'));
            }
            
            // Inicializar nivel de inspección
            const oldInspectionLevel = '{{ old("inspection_level") }}';
            if (oldInspectionLevel) {
                const levelSelect = document.getElementById('inspection_level');
                levelSelect.value = oldInspectionLevel;
            }
            
            // Inicializar status
            const oldStatus = '{{ old("status") }}';
            if (oldStatus) {
                const statusSelect = document.getElementById('status');
                statusSelect.value = oldStatus;
            }
            
            // Almacenar archivos subidos del componente Livewire
            const inspectionFilesInput = document.getElementById('inspection_files_input');
            let inspectionFiles = [];
            
            // Escuchar eventos emitidos por el componente Livewire
            // Este evento se dispara cuando se sube un nuevo archivo
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('fileUploaded', (data) => {
                    const fileData = data[0];
                    
                    if (fileData.modelName === 'inspection_files') {
                        // Agregar el archivo al array con la estructura correcta para MediaLibrary
                        inspectionFiles.push({
                            name: fileData.originalName,
                            original_name: fileData.originalName,
                            mime_type: fileData.mimeType,
                            size: fileData.size,
                            is_temp: true,
                            tempPath: fileData.tempPath,
                            path: fileData.tempPath,
                            // URL formateada para vista previa
                            url: '/storage/' + fileData.tempPath,
                            id: fileData.previewData.id
                        });
                        
                        // Actualizar el input hidden con los datos JSON
                        inspectionFilesInput.value = JSON.stringify(inspectionFiles);
                        console.log('Archivo agregado:', fileData.originalName);
                        console.log('Total archivos:', inspectionFiles.length);
                    }
                });
                
                // Este evento se dispara cuando se elimina un archivo
                Livewire.on('fileRemoved', (fileId) => {
                    // Remover el archivo del array por su ID
                    inspectionFiles = inspectionFiles.filter(file => file.id !== fileId);
                    
                    // Actualizar el input hidden con los datos JSON
                    inspectionFilesInput.value = JSON.stringify(inspectionFiles);
                    console.log('Archivo eliminado, ID:', fileId);
                    console.log('Total archivos restantes:', inspectionFiles.length);
                });
            });
        });
    </script>
@endpush

@pushOnce('scripts')
    @vite('resources/js/app.js')
    @vite('resources/js/pages/notification.js')
    @vite('resources/js/components/base/tom-select.js')
@endPushOnce
