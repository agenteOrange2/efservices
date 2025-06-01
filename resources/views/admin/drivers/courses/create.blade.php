@extends('../themes/' . $activeTheme)
@section('title', 'Add Course Record')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Courses Management', 'url' => route('admin.courses.index')],
        ['label' => 'Add Course Record', 'active' => true],
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
                Add New Course Record
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.courses.index') }}" class="w-full sm:w-auto"
                    variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Courses
                </x-base.button>
            </div>
        </div>

        <!-- Formulario de Creación -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Course Details</h3>
            </div>
            
            <div class="box-body p-5">
                <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                            <select id="carrier_id" name="carrier_id"
                                class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Carrier</option>
                                @foreach ($carriers as $carrier)
                                    <option value="{{ $carrier->id }}" {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
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
                                        <option value="{{ $driver->id }}" {{ old('user_driver_detail_id') == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->user->name }} {{ $driver->last_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('user_driver_detail_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-base.form-label for="organization_name">Organization Name</x-base.form-label>
                            <x-base.form-input id="organization_name" name="organization_name" type="text" 
                                value="{{ old('organization_name') }}" class="block w-full" />
                            @error('organization_name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="phone">Phone</x-base.form-label>
                            <x-base.form-input id="phone" name="phone" type="text" 
                                value="{{ old('phone') }}" class="block w-full" />
                            @error('phone')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <x-base.form-label for="city">City</x-base.form-label>
                            <x-base.form-input id="city" name="city" type="text" 
                                value="{{ old('city') }}" class="block w-full" />
                            @error('city')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="state">State</x-base.form-label>
                            <x-base.form-input id="state" name="state" type="text" 
                                value="{{ old('state') }}" class="block w-full" />
                            @error('state')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="experience">Experience</x-base.form-label>
                            <x-base.form-input id="experience" name="experience" type="text" 
                                value="{{ old('experience') }}" class="block w-full" />
                            @error('experience')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <x-base.form-label for="certification_date">Certification Date</x-base.form-label>
                            <x-base.form-input id="certification_date" name="certification_date" type="date" 
                                value="{{ old('certification_date') }}" class="block w-full" />
                            @error('certification_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="expiration_date">Expiration Date</x-base.form-label>
                            <x-base.form-input id="expiration_date" name="expiration_date" type="date" 
                                value="{{ old('expiration_date') }}" class="block w-full" />
                            @error('expiration_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="status">Status</x-base.form-label>
                            <select id="status" name="status"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <x-base.form-label>Course Certificate</x-base.form-label>
                        <div class="border border-dashed rounded-md p-4 mt-2">                            
                                <input type="hidden" id="certificate_files_input" name="certificate_files">
                                <livewire:components.file-uploader :modelName="'certificate_files'" :modelIndex="0" :label="'Upload Certificate Files'" :multiple="true" />                            
                        </div>
                        @error('certificate_files')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <x-base.button as="a" href="{{ route('admin.courses.index') }}" class="mr-2"
                            variant="outline-secondary">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Save Course
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const carrierSelect = document.getElementById('carrier_id');
            const driverSelect = document.getElementById('user_driver_detail_id');
            const oldCarrierId = '{{ old('carrier_id') }}';
            const oldDriverId = '{{ old('user_driver_detail_id') }}';
            
            // Cargar drivers cuando se selecciona un carrier
            carrierSelect.addEventListener('change', function() {
                const carrierId = this.value;
                
                // Limpiar el select de conductores
                driverSelect.innerHTML = '<option value="">Select Driver</option>';
                
                if (carrierId) {
                    // Obtener drivers del carrier seleccionado
                    fetch(`/admin/courses/carrier/${carrierId}/drivers`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.drivers && data.drivers.length > 0) {
                                data.drivers.forEach(driver => {
                                    const option = document.createElement('option');
                                    option.value = driver.id;
                                    option.textContent = `${driver.user.name} ${driver.last_name}`;
                                    
                                    // Seleccionar driver si coincide con el valor antiguo
                                    if (oldDriverId && oldDriverId == driver.id) {
                                        option.selected = true;
                                    }
                                    
                                    driverSelect.appendChild(option);
                                });
                            } else {
                                const option = document.createElement('option');
                                option.value = '';
                                option.textContent = 'No drivers available';
                                driverSelect.appendChild(option);
                            }
                        })
                        .catch(error => {
                            console.error('Error loading drivers:', error);
                            const option = document.createElement('option');
                            option.value = '';
                            option.textContent = 'Error loading drivers';
                            driverSelect.appendChild(option);
                        });
                }
            });
            
            // Inicializar selectores si hay valores antiguos (para errores de validación)
            if (oldCarrierId) {
                // Seleccionar carrier
                carrierSelect.value = oldCarrierId;
                
                // Disparar manualmente el evento change para cargar los drivers
                carrierSelect.dispatchEvent(new Event('change'));
            }
            
            // Almacenar archivos subidos del componente Livewire
            const certificateFilesInput = document.getElementById('certificate_files_input');
            let certificateFiles = [];
            
            // Escuchar eventos emitidos por el componente Livewire
            // Este evento se dispara cuando se sube un nuevo archivo
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('fileUploaded', (data) => {
                    const fileData = data[0];
                    
                    if (fileData.modelName === 'certificate_files') {
                        // Agregar el archivo al array
                        certificateFiles.push({
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
                        certificateFilesInput.value = JSON.stringify(certificateFiles);
                        console.log('Archivo agregado:', fileData.originalName);
                        console.log('Total archivos:', certificateFiles.length);
                    }
                });
                
                // Este evento se dispara cuando se elimina un archivo
                Livewire.on('fileRemoved', (fileId) => {
                    // Remover el archivo del array por su ID
                    certificateFiles = certificateFiles.filter(file => file.id !== fileId);
                    
                    // Actualizar el input hidden con los datos JSON
                    certificateFilesInput.value = JSON.stringify(certificateFiles);
                    console.log('Archivo eliminado, ID:', fileId);
                    console.log('Total archivos restantes:', certificateFiles.length);
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
@endsection
