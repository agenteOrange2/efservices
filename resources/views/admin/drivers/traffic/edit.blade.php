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
                            <x-base.form-input id="conviction_date" name="conviction_date" type="date"
                                value="{{ old('conviction_date', $conviction->conviction_date->format('Y-m-d')) }}" />
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
                            @php
                            // Prepara los archivos existentes para el componente Livewire
                            $existingFilesArray = [];
                            $documents = $conviction->getMedia('traffic-tickets');
                            foreach($documents as $document) {
                                $existingFilesArray[] = [
                                    'id' => $document->id,
                                    'name' => $document->file_name,
                                    'file_name' => $document->file_name,
                                    'mime_type' => $document->mime_type,
                                    'size' => $document->size,
                                    'created_at' => $document->created_at->format('Y-m-d H:i:s'),
                                    'url' => $document->getUrl(),
                                    'is_temp' => false
                                ];
                            }
                            @endphp

                            <livewire:components.file-uploader
                                model-name="traffic_files"
                                :model-index="0"
                                :label="'Upload Documents'"
                                :existing-files="$existingFilesArray"
                            />
                            <!-- Campo oculto para almacenar los archivos subidos -->
                            <input type="hidden" name="traffic_files" id="traffic_files_input">
                        </div>
                    </div>

                    <div class="flex justify-end mt-5">
                        <x-base.button as="a" href="{{ route('admin.traffic.index') }}" variant="outline-secondary"
                            class="mr-2">
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
            // Inicializar el array para almacenar los archivos
            let uploadedFiles = [];
            
            document.addEventListener('DOMContentLoaded', function() {
                const trafficFilesInput = document.getElementById('traffic_files_input');
                
                // Escuchar eventos del componente Livewire
                window.addEventListener('livewire:initialized', () => {
                    // Escuchar el evento fileUploaded del componente Livewire
                    Livewire.on('fileUploaded', (eventData) => {
                        console.log('Archivo subido:', eventData);
                        // Extraer los datos del evento
                        const data = eventData[0]; // Los datos vienen como primer elemento del array
                        
                        if (data.modelName === 'traffic_files') {
                            // Añadir el archivo al array de archivos
                            uploadedFiles.push({
                                path: data.tempPath,
                                original_name: data.originalName,
                                mime_type: data.mimeType,
                                size: data.size
                            });
                            
                            // Actualizar el campo oculto con el nuevo array
                            trafficFilesInput.value = JSON.stringify(uploadedFiles);
                            console.log('Archivos actualizados:', trafficFilesInput.value);
                        }
                    });
                    
                    // Escuchar el evento fileRemoved del componente Livewire
                    Livewire.on('fileRemoved', (eventData) => {
                        console.log('Archivo eliminado:', eventData);
                        // Extraer los datos del evento
                        const data = eventData[0]; // Los datos vienen como primer elemento del array
                        
                        if (data.modelName === 'traffic_files') {
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
                            trafficFilesInput.value = JSON.stringify(uploadedFiles);
                            console.log('Archivos actualizados después de eliminar:', trafficFilesInput.value);
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
                                        fileIcon = `<img src="${e.target.result}" class="w-8 h-8 object-cover rounded" alt="${fileName}">`;
                                    } else if (['pdf'].includes(fileExtension)) {
                                        fileIcon = `<x-base.lucide class="w-8 h-8 text-red-500" icon="FileText" />`;
                                    } else if (['doc', 'docx'].includes(fileExtension)) {
                                        fileIcon = `<x-base.lucide class="w-8 h-8 text-blue-500" icon="File" />`;
                                    } else {
                                        fileIcon = `<x-base.lucide class="w-8 h-8 text-gray-500" icon="File" />`;
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