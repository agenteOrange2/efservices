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

        <!-- Formulario de Creación con Livewire -->
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
                        <div x-data="{ showOtherField: false }">
                            <x-base.form-label for="organization_name">Organization Name</x-base.form-label>
                            <select id="organization_name_select" name="organization_name" 
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8"
                                x-on:change="showOtherField = ($event.target.value === 'Other')">
                                <option value="">Select Organization</option>
                                <option value="H2S" {{ old('organization_name') == 'H2S' ? 'selected' : '' }}>H2S</option>
                                <option value="PEC" {{ old('organization_name') == 'PEC' ? 'selected' : '' }}>PEC</option>
                                <option value="SANDTRAX" {{ old('organization_name') == 'SANDTRAX' ? 'selected' : '' }}>SANDTRAX</option>
                                <option value="OSHA10" {{ old('organization_name') == 'OSHA10' ? 'selected' : '' }}>OSHA10</option>
                                <option value="OSHA30" {{ old('organization_name') == 'OSHA30' ? 'selected' : '' }}>OSHA30</option>
                                <option value="Other" {{ old('organization_name') != 'H2S' && old('organization_name') != 'PEC' && old('organization_name') != 'SANDTRAX' && old('organization_name') != 'OSHA10' && old('organization_name') != 'OSHA30' && old('organization_name') ? 'selected' : '' }}>Other</option>
                            </select>
                            
                            <!-- Campo para "Other" que se muestra condicionalmente -->
                            <div x-show="showOtherField" class="mt-2">
                                <x-base.form-input id="organization_name_other" name="organization_name_other" type="text" 
                                    value="{{ old('organization_name_other') }}" class="block w-full" 
                                    placeholder="Specify organization name" />
                            </div>
                            
                            @error('organization_name')
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
                            <select id="state" name="state" 
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select State</option>
                                @foreach(\App\Helpers\Constants::usStates() as $code => $name)
                                    <option value="{{ $code }}" {{ old('state') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
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
                            <livewire:components.file-uploader
                                model-name="course_certificate"
                                :model-index="0"
                                :auto-upload="true"
                                class="border-2 border-dashed border-gray-300 rounded-lg p-6 cursor-pointer"
                            />
                            <!-- Campo oculto para almacenar los archivos subidos - valor inicial vacío pero no null -->
                            <input type="hidden" name="certificate_files" id="certificate_files_input" value="">
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
            
            // Inicializar el array para almacenar los archivos
            let uploadedFiles = [];
            // IMPORTANTE: Asegurarnos que el campo oculto esté accesible en toda la función
            const certificateFilesInput = document.getElementById('certificate_files_input');
            console.log('Campo oculto encontrado:', certificateFilesInput ? 'Sí' : 'No');
            
            // Escuchar eventos del componente Livewire
            window.addEventListener('livewire:initialized', () => {
                console.log('Livewire inicializado, preparando escucha de eventos');
                
                // Escuchar el evento fileUploaded del componente Livewire
                Livewire.on('fileUploaded', (eventData) => {
                    console.log('Archivo subido evento recibido:', eventData);
                    // Extraer los datos del evento
                    const data = eventData[0]; // Los datos vienen como primer elemento del array
                    
                    if (data.modelName === 'course_certificate') {
                        console.log('Archivo subido para course_certificate');
                        // Añadir el archivo al array de archivos
                        uploadedFiles.push({
                            name: data.originalName,
                            original_name: data.originalName,
                            mime_type: data.mimeType,
                            size: data.size,
                            path: data.tempPath,
                            tempPath: data.tempPath,
                            is_temp: true
                        });
                        
                        // Asegurarnos que el campo oculto sigue existiendo
                        const hiddenInput = document.getElementById('certificate_files_input');
                        if (hiddenInput) {
                            hiddenInput.value = JSON.stringify(uploadedFiles);
                            console.log('Campo actualizado con:', hiddenInput.value);
                        } else {
                            console.error('Campo oculto no encontrado en el DOM');
                        }
                    }
                });
                
                // Escuchar el evento fileRemoved del componente Livewire
                Livewire.on('fileRemoved', (eventData) => {
                    console.log('Archivo eliminado:', eventData);
                    // Extraer los datos del evento
                    const data = eventData[0]; // Los datos vienen como primer elemento del array
                    
                    if (data.modelName === 'course_certificate') {
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
                        certificateFilesInput.value = JSON.stringify(uploadedFiles);
                        console.log('Archivos actualizados después de eliminar:', certificateFilesInput.value);
                    }
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
