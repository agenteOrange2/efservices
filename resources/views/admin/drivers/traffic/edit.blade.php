@extends('../themes/' . $activeTheme)
@section('title', 'Edit Traffic Conviction')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Traffic Convictions', 'url' => route('admin.traffic.index')],
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

        <!-- Cabecera -->
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">
                Edit Traffic Conviction: {{ $conviction->charge }}
                ({{ $conviction->conviction_date->format('M d, Y') }})
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.traffic.index') }}" class="w-full sm:w-auto mr-2"
                    variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Traffic Convictions
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.traffic.documents', $conviction->id) }}"
                    class="w-full sm:w-auto" variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" />
                    View Documents
                </x-base.button>
            </div>
        </div>

        <!-- Formulario de Edición -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Conviction Details</h3>
            </div>
            <div class="box-body p-5">
                <form method="POST" action="{{ route('admin.traffic.update', $conviction) }}" enctype="multipart/form-data"
                    id="updateForm">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-base.form-label for="carrier">Carrier</x-base.form-label>
                            <select id="carrier" name="carrier_id" onchange="updateDrivers(this.value)"
                                class="form-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Carrier</option>
                                @foreach ($carriers as $carrier)
                                    <option value="{{ $carrier->id }}"
                                        {{ $conviction->userDriverDetail->carrier_id == $carrier->id ? 'selected' : '' }}>
                                        {{ $carrier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('carrier')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <x-base.form-label for="user_driver_detail_id">Driver</x-base.form-label>
                            <select id="user_driver_detail_id" name="user_driver_detail_id"
                                class="form-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Driver</option>
                                @if (isset($drivers))
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id }}"
                                            {{ $conviction->user_driver_detail_id == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->user->name }} {{ $driver->user->last_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('user_driver_detail_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <x-base.form-label for="conviction_date">Conviction Date</x-base.form-label>
                            <x-base.litepicker id="conviction_date" name="conviction_date"
                                value="{{ old('conviction_date', $conviction->conviction_date ? $conviction->conviction_date->format('m/d/Y') : '') }}"
                                class="@error('conviction_date') border-danger @enderror" placeholder="MM/DD/YYYY"
                                required />
                            @error('conviction_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <x-base.form-label for="location">Location</x-base.form-label>
                            <x-base.form-input id="location" name="location" type="text" placeholder="Enter location"
                                value="{{ old('location', $conviction->location) }}" />
                            @error('location')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <x-base.form-label for="charge">Charge</x-base.form-label>
                            <x-base.form-input id="charge" name="charge" type="text" placeholder="Enter charge"
                                value="{{ old('charge', $conviction->charge) }}" />
                            @error('charge')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <x-base.form-label for="penalty">Penalty</x-base.form-label>
                            <x-base.form-input id="penalty" name="penalty" type="text" placeholder="Enter penalty"
                                value="{{ old('penalty', $conviction->penalty) }}" />
                            @error('penalty')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <x-base.form-label>Traffic Conviction Images</x-base.form-label>
                            <div class="border border-dashed rounded-md p-4 mt-2">
                                @php
                                    // Prepara los archivos existentes para el componente Livewire desde Spatie Media Library
                                    $existingFiles = [];

                                    // Obtener todos los archivos de media para esta infracción
                                    $mediaItems = $conviction->media->where('collection_name', 'traffic_convictions');

                                    // Si no hay archivos en la colección específica, buscar en todas las colecciones
                                    // Esto es útil cuando los archivos se suben desde diferentes partes del sistema
                                    if ($mediaItems->isEmpty()) {
                                        $mediaItems = $conviction->media;
                                    }

                                    foreach ($mediaItems as $media) {
                                        $existingFiles[] = [
                                            'id' => $media->id,
                                            'name' => $media->file_name,
                                            'size' => $media->size,
                                            'mime_type' => $media->mime_type,
                                            'original_name' => $media->file_name,
                                            'url' => $media->getUrl(),
                                            'is_temp' => false,
                                            'created_at' => $media->created_at->toDateTimeString(),
                                        ];
                                    }
                                @endphp

                                <livewire:components.file-uploader model-name="traffic_images" :model-index="0"
                                    :auto-upload="true"
                                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 cursor-pointer"
                                    :existing-files="$existingFiles" />
                                <!-- Campo oculto para almacenar los archivos subidos - valor inicial vacío pero no null -->
                                <input type="hidden" name="traffic_image_files" id="traffic_image_files_input"
                                    value="">
                            </div>
                        </div>                        
                    </div>
                    <div class="flex justify-end mt-5">
                        <x-base.button as="a" href="{{ route('admin.traffic.index') }}"
                            variant="outline-secondary" class="mr-2">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Update Conviction
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Inicializar carrier y drivers
                const carrierId = {{ $conviction->userDriverDetail->carrier_id }};
                if (carrierId) {
                    document.getElementById('carrier').value = carrierId;
                    updateDrivers(carrierId);
                }

                // Inicializar el array para almacenar los archivos
                let uploadedFiles = [];
                const trafficImagesInput = document.getElementById('traffic_image_files_input');
                console.log('Campo oculto encontrado:', trafficImagesInput ? 'Sí' : 'No');

                // Escuchar eventos del componente Livewire
                window.addEventListener('livewire:initialized', () => {
                    console.log('Livewire inicializado, preparando escucha de eventos');

                    // Escuchar el evento fileUploaded del componente Livewire
                    Livewire.on('fileUploaded', (eventData) => {
                        console.log('Archivo subido evento recibido:', eventData);
                        // Extraer los datos del evento
                        const data = eventData[0]; // Los datos vienen como primer elemento del array

                        if (data.modelName === 'traffic_images') {
                            console.log('Archivo subido para traffic_images');
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
                            if (trafficImagesInput) {
                                trafficImagesInput.value = JSON.stringify(uploadedFiles);
                                console.log('Campo actualizado con:', trafficImagesInput.value);
                            } else {
                                console.error('Campo oculto no encontrado en el DOM');
                            }
                        }
                    });

                    // Escuchar el evento fileRemoved del componente Livewire
                    Livewire.on('fileRemoved', (eventData) => {
                        console.log('Archivo eliminado evento recibido:', eventData);
                        // Extraer los datos del evento
                        const data = eventData[0]; // Los datos vienen como primer elemento del array

                        if (data.modelName === 'traffic_images') {
                            console.log('Eliminando archivo de traffic_images');

                            // Si no es un archivo temporal, eliminarlo mediante AJAX
                            if (!data.isTemp && data.fileId) {
                                console.log('Eliminando archivo permanente con ID:', data.fileId);
                                const mediaId = data.fileId;

                                // Llamada AJAX para eliminar el archivo usando la nueva ruta ajaxDestroyDocument
                                fetch('{{ route('admin.traffic.ajax-destroy-document', '') }}/' +
                                        mediaId, {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                            },
                                        })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            console.log('Documento eliminado con éxito');
                                        } else {
                                            console.error('Error al eliminar documento:', data
                                                .message);
                                            // Mostrar algún mensaje de error si es necesario
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error en la petición AJAX:', error);
                                    });
                            }

                            // Eliminar archivo del array por nombre o índice (para temporales)
                            const fileIndex = uploadedFiles.findIndex(file =>
                                file.name === data.originalName ||
                                file.original_name === data.originalName);

                            if (fileIndex > -1) {
                                uploadedFiles.splice(fileIndex, 1);
                                console.log('Archivo encontrado y eliminado del arreglo');
                            } else {
                                // Si no se encuentra por nombre, eliminar el último (para archivos temporales)
                                console.log('Archivo no encontrado por nombre, eliminando el último');
                                uploadedFiles.pop();
                            }

                            // Actualizar el campo oculto
                            if (trafficImagesInput) {
                                trafficImagesInput.value = JSON.stringify(uploadedFiles);
                                console.log('Campo actualizado después de eliminar:', trafficImagesInput
                                    .value);
                            } else {
                                console.error(
                                    'Campo oculto no encontrado en el DOM después de eliminar');
                            }
                        }
                    });
                });
            });

            // Función para cargar conductores cuando cambia el carrier
            function updateDrivers(carrierId) {
                const driverSelect = document.getElementById('user_driver_detail_id');
                const currentDriverId = {{ $conviction->user_driver_detail_id }};
                const currentCarrierId = {{ $conviction->userDriverDetail->carrier_id }};

                // Limpiar el select
                driverSelect.innerHTML = '<option value="">Select Driver</option>';

                if (!carrierId) return;

                // Hacer petición AJAX para obtener conductores
                fetch(`/api/active-drivers-by-carrier/${carrierId}`)
                    .then(response => response.json())
                    .then(data => {
                        let driverFound = false;

                        // Agregar conductores activos
                        data.forEach(driver => {
                            const option = document.createElement('option');
                            option.value = driver.id;
                            option.textContent = `${driver.user.name} ${driver.user.last_name || ''}`;

                            if (driver.id == currentDriverId) {
                                option.selected = true;
                                driverFound = true;
                            }

                            driverSelect.appendChild(option);
                        });

                        // Si el conductor actual no está en la lista y estamos en su carrier original
                        if (!driverFound && carrierId == currentCarrierId) {
                            const option = document.createElement('option');
                            option.value = currentDriverId;
                            option.textContent =
                                `{{ $conviction->userDriverDetail->user->name }} {{ $conviction->userDriverDetail->user->last_name }} (Inactive)`;
                            option.selected = true;
                            driverSelect.appendChild(option);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading drivers:', error);
                        driverSelect.innerHTML = '<option value="">Error loading drivers</option>';
                    });
            }

            // Inicializar cuando el DOM esté listo
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('updateForm');
                const carrierSelect = document.getElementById('carrier');
                const fileInput = document.getElementById('document-upload');
                const filePreview = document.getElementById('file-preview');

                // Agregar manejador de submit al formulario
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Mostrar los datos que se van a enviar
                    const formData = new FormData(this);
                    console.log('Enviando datos:');
                    for (let pair of formData.entries()) {
                        console.log(pair[0] + ':', pair[1]);
                    }

                    // Hacer el submit normal del formulario
                    this.submit();
                });

                // Cargar conductores iniciales si hay un carrier seleccionado
                if (carrierSelect.value) {
                    updateDrivers(carrierSelect.value);
                }

                // Vista previa de archivos
                if (fileInput) {
                    fileInput.addEventListener('change', function(event) {
                        if (event.target.files.length > 0) {
                            filePreview.style.display = 'grid';
                            filePreview.innerHTML = ''; // Limpiar vista previa anterior

                            Array.from(event.target.files).forEach(file => {
                                const reader = new FileReader();
                                const fileSize = (file.size / 1024).toFixed(2); // Convertir a KB
                                const fileName = file.name;
                                const fileExtension = fileName.split('.').pop().toLowerCase();

                                reader.onload = function(e) {
                                    let fileIcon;

                                    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                                        fileIcon =
                                            `<img src="${e.target.result}" class="w-8 h-8 object-cover rounded" alt="${fileName}">`;
                                    } else if (['pdf'].includes(fileExtension)) {
                                        fileIcon =
                                            `<x-base.lucide class="w-8 h-8 text-red-500" icon="FileText" />`;
                                    } else if (['doc', 'docx'].includes(fileExtension)) {
                                        fileIcon =
                                            `<x-base.lucide class="w-8 h-8 text-blue-500" icon="File" />`;
                                    } else {
                                        fileIcon =
                                            `<x-base.lucide class="w-8 h-8 text-gray-500" icon="File" />`;
                                    }

                                    // Crear la tarjeta de vista previa
                                    const previewCardHTML = `
                                        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0">
                                                    <div class="h-16 w-16 flex items-center justify-center bg-gray-50 rounded-md border border-gray-200">
                                                        ${fileIcon}
                                                    </div>
                                                </div>
                                                <div class="ml-3 flex-1">
                                                    <p class="text-sm font-medium truncate" title="${fileName}">${fileName}</p>
                                                    <p class="text-xs text-gray-500">${fileSize} KB</p>
                                                    <p class="text-xs text-gray-500">${new Date().toLocaleString()}</p>
                                                    <p class="text-xs text-gray-500 mt-1"><span class="font-semibold">Status:</span> New Upload</p>
                                                </div>
                                            </div>
                                        </div>
                                    `;

                                    // Crear un div temporal para convertir el HTML en un elemento DOM
                                    const tempDiv = document.createElement('div');
                                    tempDiv.innerHTML = previewCardHTML;
                                    const previewCard = tempDiv.firstElementChild;

                                    // Añadir la tarjeta al contenedor de vista previa
                                    filePreview.appendChild(previewCard);
                                };

                                reader.readAsDataURL(file);
                            });
                        } else {
                            filePreview.style.display = 'none';
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
@pushOnce('scripts')
    @vite('resources/js/app.js') {{-- Este debe ir primero --}}
    @vite('resources/js/pages/notification.js')
@endPushOnce
