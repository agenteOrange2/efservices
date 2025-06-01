@extends('../themes/' . $activeTheme)
@section('title', 'Edit Course Record')
@php
    use Illuminate\Support\Facades\Storage;
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Courses Management', 'url' => route('admin.courses.index')],
        ['label' => 'Edit Course Record', 'active' => true],
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
                Edit Course Record
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.courses.index') }}" class="w-full sm:w-auto"
                    variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Courses
                </x-base.button>
            </div>
        </div>

        <!-- Formulario de Edición -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Course Details</h3>
            </div>
            
            <div class="box-body p-5">
                <form action="{{ route('admin.courses.update', $course) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                            <select id="carrier_id" name="carrier_id"
                                class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Carrier</option>
                                @foreach ($carriers as $carrier)
                                    <option value="{{ $carrier->id }}" {{ isset($course->userDriverDetail) && $course->userDriverDetail->carrier_id == $carrier->id ? 'selected' : '' }}>
                                        {{ $carrier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('carrier_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="user_driver_detail_id">Driver</x-base.form-label>
                            <select id="user_driver_detail_id" name="user_driver_detail_id"
                                class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Driver</option>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}" {{ $course->user_driver_detail_id == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->user->name }} {{ $driver->last_name }}
                                    </option>
                                @endforeach
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
                                value="{{ old('organization_name', $course->organization_name) }}" class="block w-full" />
                            @error('organization_name')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="phone">Phone</x-base.form-label>
                            <x-base.form-input id="phone" name="phone" type="text" 
                                value="{{ old('phone', $course->phone) }}" class="block w-full" />
                            @error('phone')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <x-base.form-label for="city">City</x-base.form-label>
                            <x-base.form-input id="city" name="city" type="text" 
                                value="{{ old('city', $course->city) }}" class="block w-full" />
                            @error('city')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="state">State</x-base.form-label>
                            <x-base.form-input id="state" name="state" type="text" 
                                value="{{ old('state', $course->state) }}" class="block w-full" />
                            @error('state')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="experience">Experience</x-base.form-label>
                            <x-base.form-input id="experience" name="experience" type="text" 
                                value="{{ old('experience', $course->experience) }}" class="block w-full" />
                            @error('experience')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <x-base.form-label for="certification_date">Certification Date</x-base.form-label>
                            <x-base.form-input id="certification_date" name="certification_date" type="date" 
                                value="{{ old('certification_date', $course->certification_date ? $course->certification_date->format('Y-m-d') : '') }}" class="block w-full" />
                            @error('certification_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="expiration_date">Expiration Date</x-base.form-label>
                            <x-base.form-input id="expiration_date" name="expiration_date" type="date" 
                                value="{{ old('expiration_date', $course->expiration_date ? $course->expiration_date->format('Y-m-d') : '') }}" class="block w-full" />
                            @error('expiration_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="status">Status</x-base.form-label>
                            <select id="status" name="status"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="active" {{ old('status', $course->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $course->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <x-base.form-label>Course Certificates</x-base.form-label>
                        <div class="border border-dashed rounded-md p-4 mt-2">                            
                                <input type="hidden" id="certificate_files_input" name="certificate_files">
                                <livewire:components.file-uploader 
                                    :modelName="'certificate_files'" 
                                    :modelIndex="0" 
                                    :label="'Upload Certificate Files'" 
                                    :multiple="true" 
                                    :existing-files="$existingFilesArray ?? []" 
                                />                            
                        </div>
                        @error('certificate_files')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Nota: El componente Livewire FileUploader arriba ya maneja los documentos existentes -->
                    
                    <div class="mt-6 flex justify-end">
                        <x-base.button as="a" href="{{ route('admin.courses.index') }}" class="mr-2"
                            variant="outline-secondary">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Update Course
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Referencias a elementos DOM
            const carrierSelect = document.getElementById('carrier_id');
            const driverSelect = document.getElementById('user_driver_detail_id');
            const oldCarrierId = '{{ old('carrier_id', isset($course->userDriverDetail) ? $course->userDriverDetail->carrier_id : '') }}';
            const oldDriverId = '{{ old('user_driver_detail_id', $course->user_driver_detail_id) }}';
            const certificateFilesInput = document.getElementById('certificate_files_input');
            let certificateFiles = [];
            
            // Inicializar los certificados existentes
            @if(isset($existingFilesArray) && count($existingFilesArray) > 0)
                certificateFiles = @json($existingFilesArray);
                certificateFilesInput.value = JSON.stringify(certificateFiles);
                
                // Notificar al componente Livewire sobre los archivos existentes
                document.addEventListener('livewire:initialized', () => {
                    setTimeout(() => {
                        certificateFiles.forEach(file => {
                            Livewire.dispatch('certificate_files-file-exists', {
                                name: file.name,
                                fileName: file.name,
                                originalName: file.original_name,
                                isExisting: true,
                                docId: file.id,
                                filePath: file.file_path,
                                modelIndex: 0
                            });
                        });
                    }, 500); // Pequeño retraso para asegurar que Livewire esté listo
                });
            @endif
            
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
            
            // Manejar la eliminación de documentos existentes
            document.querySelectorAll('.delete-document').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this document?')) {
                        const documentId = this.getAttribute('data-document-id');
                        const courseId = this.getAttribute('data-course-id');
                        
                        console.log('Deleting document:', { documentId, courseId });
                        
                        // Eliminar el documento mediante AJAX
                        fetch('/admin/courses/document/delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                document_id: documentId,
                                course_id: courseId
                            })
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                // Eliminar el elemento del DOM
                                this.closest('.p-3').remove();
                                
                                // Actualizar la lista de archivos
                                certificateFiles = certificateFiles.filter(file => {
                                    return file.id != documentId;
                                });
                                
                                certificateFilesInput.value = JSON.stringify(certificateFiles);
                                console.log('Document deleted successfully');
                                
                                // Mostrar notificación de éxito
                                alert('Document deleted successfully');
                            } else {
                                console.error('Error deleting document:', result.message);
                                alert('Error deleting document: ' + (result.message || 'Please try again'));
                            }
                        })
                        .catch(error => {
                            console.error('AJAX request error:', error);
                            alert('An error occurred. Please try again.');
                        });
                    }
                });
            });
            
            // Escuchar eventos emitidos por el componente Livewire
            document.addEventListener('livewire:initialized', () => {
                // Este evento se dispara cuando se sube un nuevo archivo
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
                    }
                });
                
                // Este evento se dispara cuando se elimina un archivo desde el componente Livewire
                Livewire.on('fileRemoved', (eventData) => {
                    console.log('Evento fileRemoved recibido:', eventData);
                    const data = eventData[0]; // Los datos vienen como primer elemento del array
                    const fileId = data.fileId;
                    
                    // Verificar si el archivo es permanente (no temporal) y pertenece a nuestro modelo
                    if (data.modelName === 'certificate_files' && !data.isTemp) {
                        console.log('Eliminando documento permanente con ID:', fileId);
                        
                        // Obtener el ID del curso actual
                        const courseId = {{ $course->id }};
                        
                        // Hacer llamada AJAX para eliminar el documento físicamente
                        fetch('/admin/courses/document/delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                document_id: fileId,
                                course_id: courseId
                            })
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                console.log('Documento eliminado con éxito de la base de datos');
                                // Disparar un evento para que otros componentes sepan que el documento fue eliminado
                                document.dispatchEvent(new CustomEvent('document-deleted', { 
                                    detail: { mediaId: fileId }
                                }));
                            } else {
                                console.error('Error al eliminar documento:', result.message);
                                alert('Error al eliminar documento: ' + (result.message || 'Intente nuevamente'));
                            }
                        })
                        .catch(error => {
                            console.error('Error en la solicitud AJAX:', error);
                            alert('Error al procesar la solicitud. Por favor, intente nuevamente.');
                        });
                    }
                    
                    // Encontrar y eliminar el archivo del array local (tanto temporales como permanentes)
                    certificateFiles = certificateFiles.filter(file => file.id != fileId);
                    
                    // Actualizar el input hidden
                    certificateFilesInput.value = JSON.stringify(certificateFiles);
                    console.log('Archivo eliminado, ID:', fileId);
                    console.log('Total archivos restantes:', certificateFiles.length);
                });
            });
            
            // Inicializar selectores si hay valores antiguos (para errores de validación)
            if (oldCarrierId) {
                // Seleccionar carrier
                carrierSelect.value = oldCarrierId;
                
                // Disparar manualmente el evento change para cargar los drivers
                carrierSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endpush

@pushOnce('scripts')
    @vite('resources/js/app.js')
    @vite('resources/js/pages/notification.js')
    @vite('resources/js/components/base/tom-select.js')
@endPushOnce
@endsection
