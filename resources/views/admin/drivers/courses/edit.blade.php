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
                <form action="{{ route('admin.courses.update', $course->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                            <select id="carrier_id" name="carrier_id"
                                class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Carrier</option>
                                @foreach ($carriers as $carrier)
                                    <option value="{{ $carrier->id }}" {{ $course->driverDetail->carrier_id == $carrier->id ? 'selected' : '' }}>
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
                                        <option value="{{ $driver->id }}" {{ $course->user_driver_detail_id == $driver->id ? 'selected' : '' }}>
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
                        <div x-data="{ showOtherField: {{ in_array($course->organization_name, ['H2S', 'PEC', 'SANDTRAX', 'OSHA10', 'OSHA30']) ? 'false' : 'true' }} }">
                            <x-base.form-label for="organization_name">Organization Name</x-base.form-label>
                            <select id="organization_name_select" name="organization_name" 
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8"
                                x-on:change="showOtherField = ($event.target.value === 'Other')">
                                <option value="">Select Organization</option>
                                <option value="H2S" {{ $course->organization_name == 'H2S' ? 'selected' : '' }}>H2S</option>
                                <option value="PEC" {{ $course->organization_name == 'PEC' ? 'selected' : '' }}>PEC</option>
                                <option value="SANDTRAX" {{ $course->organization_name == 'SANDTRAX' ? 'selected' : '' }}>SANDTRAX</option>
                                <option value="OSHA10" {{ $course->organization_name == 'OSHA10' ? 'selected' : '' }}>OSHA10</option>
                                <option value="OSHA30" {{ $course->organization_name == 'OSHA30' ? 'selected' : '' }}>OSHA30</option>
                                <option value="Other" {{ !in_array($course->organization_name, ['H2S', 'PEC', 'SANDTRAX', 'OSHA10', 'OSHA30', '']) ? 'selected' : '' }}>Other</option>
                            </select>
                            
                            <!-- Campo para "Other" que se muestra condicionalmente -->
                            <div x-show="showOtherField" class="mt-2">
                                <x-base.form-input id="organization_name_other" name="organization_name_other" type="text" 
                                    value="{{ !in_array($course->organization_name, ['H2S', 'PEC', 'SANDTRAX', 'OSHA10', 'OSHA30', '']) ? $course->organization_name : old('organization_name_other') }}" 
                                    class="block w-full" placeholder="Specify organization name" />
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
                                value="{{ $course->city }}" class="block w-full" />
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
                                    <option value="{{ $code }}" {{ $course->state == $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('state')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="experience">Experience</x-base.form-label>
                            <x-base.form-input id="experience" name="experience" type="text" 
                                value="{{ $course->experience }}" class="block w-full" />
                            @error('experience')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <x-base.form-label for="certification_date">Certification Date</x-base.form-label>
                            <x-base.form-input id="certification_date" name="certification_date" type="date" 
                                value="{{ $course->certification_date ? date('Y-m-d', strtotime($course->certification_date)) : '' }}" class="block w-full" />
                            @error('certification_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="expiration_date">Expiration Date</x-base.form-label>
                            <x-base.form-input id="expiration_date" name="expiration_date" type="date" 
                                value="{{ $course->expiration_date ? date('Y-m-d', strtotime($course->expiration_date)) : '' }}" class="block w-full" />
                            @error('expiration_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="status">Status</x-base.form-label>
                            <select id="status" name="status"
                                class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="active" {{ $course->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $course->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <!-- Documentos con FileUploader de Livewire -->
                        <div class="mt-4">
                            @php
                                $existingFilesArray = [];
                                if($course->hasMedia('course_certificates')) {
                                    foreach($course->getMedia('course_certificates') as $certificate) {
                                        $existingFilesArray[] = [
                                            'id' => $certificate->id,
                                            'name' => $certificate->file_name,
                                            'original_name' => $certificate->file_name,
                                            'mime_type' => $certificate->mime_type,
                                            'size' => $certificate->size,
                                            'created_at' => $certificate->created_at->format('Y-m-d H:i:s'),
                                            'url' => $certificate->getUrl(),
                                            'is_temp' => false,
                                        ];
                                    }
                                }
                            @endphp
                            
                            <livewire:components.file-uploader
                                model-name="certificate_files"
                                :model-index="0"
                                label="Course Certificates"
                                :existing-files="$existingFilesArray"
                            />
                            
                            <!-- Campo oculto para almacenar los archivos -->
                            <input type="hidden" name="certificate_files" id="certificate_files_input">
                        </div>
                    </div>
                    
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
            // La variable certificateFilesInput se inicializará después de crear el elemento
            let certificateFilesInput;
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
    <script>
        // Declarar uploadedFiles a nivel global para que esté disponible en todos los eventos
        let uploadedFiles = [];
        
        document.addEventListener('DOMContentLoaded', function () {
            // Crear campo oculto para los archivos si no existe
            if (!document.getElementById('certificate_files_input')) {
                const inputElement = document.createElement('input');
                inputElement.type = 'hidden';
                inputElement.name = 'certificate_files';
                inputElement.id = 'certificate_files_input';
                document.querySelector('form').appendChild(inputElement);
            }
            
            // Asignar el elemento a la variable global
            certificateFilesInput = document.getElementById('certificate_files_input');
            console.log('Campo oculto encontrado:', certificateFilesInput ? 'Sí' : 'No');
            
            // Inicializar el valor del campo oculto si está vacío
            if (certificateFilesInput && (!certificateFilesInput.value || certificateFilesInput.value === '')) {
                certificateFilesInput.value = JSON.stringify([]);
            }
            
            // Escuchar eventos del componente Livewire
            window.addEventListener('livewire:initialized', () => {
                console.log('Livewire inicializado, preparando escucha de eventos');
                
                // Escuchar el evento fileRemoved del componente Livewire
                Livewire.on('fileRemoved', (eventData) => {
                    console.log('Archivo eliminado:', eventData);
                    // Extraer los datos del evento
                    const data = eventData[0]; // Los datos vienen como primer elemento del array
                    
                    if (data.modelName === 'certificate_files') {
                        const fileId = data.fileId;
                        
                        // Si es un archivo permanente (no temporal), eliminarlo de la base de datos
                        if (!data.isTemp) {
                            // Llamar a la API para eliminar el documento de manera segura
                            fetch('{{ route("api.documents.delete", "") }}/' + fileId, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
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
                        
                        // Eliminar el archivo del array de archivos temporales
                        uploadedFiles = uploadedFiles.filter(file => {
                            return !((data.isTemp && file.isTemp && file.id === fileId) || 
                                     (!data.isTemp && !file.isTemp && parseInt(file.id) === parseInt(fileId)));
                        });
                        
                        // Actualizar el campo oculto con el nuevo array
                        certificateFilesInput.value = JSON.stringify(uploadedFiles);
                        console.log('Archivos actualizados después de eliminar:', certificateFilesInput.value);
                    }
                });
                
                // Escuchar el evento fileUploaded del componente Livewire
                Livewire.on('fileUploaded', (eventData) => {
                    console.log('Archivo subido evento recibido:', eventData);
                    // Extraer los datos del evento
                    const data = eventData[0]; // Los datos vienen como primer elemento del array
                    
                    if (data.modelName === 'certificate_files') {
                        console.log('Archivo subido para certificate_files');
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
                
                // El evento fileRemoved ya está implementado arriba, así que eliminamos este duplicado
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
