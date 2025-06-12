@extends('../themes/' . $activeTheme)
@section('title', 'Create Traffic Conviction')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Traffic Convictions', 'url' => route('admin.traffic.index')],
        ['label' => 'Create', 'active' => true],
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
                Create New Traffic Conviction
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.traffic.index') }}" class="w-full sm:w-auto"
                    variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Traffic Convictions
                </x-base.button>
            </div>
        </div>

        <!-- Formulario de Creación -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Conviction Details</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.traffic.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-base.form-label for="carrier">Carrier</x-base.form-label>
                            <select id="carrier" name="carrier"
                                class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Carrier</option>
                                @foreach ($carriers as $carrier)
                                    <option value="{{ $carrier->id }}">
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
                                class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                                <option value="">Select Driver</option>
                                @if (isset($drivers))
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
                        <div>
                            <x-base.form-label for="conviction_date">Conviction Date</x-base.form-label>
                            <x-base.litepicker id="conviction_date" name="conviction_date"
                                value="{{ old('conviction_date') }}"
                                class="@error('conviction_date') border-danger @enderror" placeholder="MM/DD/YYYY"
                                required />
                            @error('conviction_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <x-base.form-label for="location">Location</x-base.form-label>
                            <x-base.form-input id="location" name="location" type="text" placeholder="Enter location"
                                value="{{ old('location') }}" />
                            @error('location')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <x-base.form-label for="charge">Charge</x-base.form-label>
                            <x-base.form-input id="charge" name="charge" type="text" placeholder="Enter charge"
                                value="{{ old('charge') }}" />
                            @error('charge')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <x-base.form-label for="penalty">Penalty</x-base.form-label>
                            <x-base.form-input id="penalty" name="penalty" type="text" placeholder="Enter penalty"
                                value="{{ old('penalty') }}" />
                            @error('penalty')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <x-base.form-label>Traffic Conviction Images</x-base.form-label>
                            <div class="border border-dashed rounded-md p-4 mt-2">
                                <livewire:components.file-uploader model-name="traffic_images" :model-index="0"
                                    :auto-upload="true"
                                    class="border-2 border-dashed border-gray-300 rounded-lg p-6 cursor-pointer" />
                                <!-- Campo oculto para almacenar los archivos subidos - valor inicial vacío pero no null -->
                                <input type="hidden" name="traffic_image_files" id="traffic_image_files_input"
                                    value="">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-5">
                        <x-base.button as="a" href="{{ route('admin.traffic.index') }}" variant="outline-secondary"
                            class="mr-2">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Create Conviction
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Los selectores ya deberían estar inicializados por Tom Select a través de app.js

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
                            // Eliminar el archivo del array por nombre o índice
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

                // Manejar cambio de carrier para filtrar conductores
                document.getElementById('carrier').addEventListener('change', function() {
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
                                        option.textContent =
                                            `${driver.user.name} ${driver.user.last_name || ''}`;
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
@endsection
@pushOnce('scripts')
    @vite('resources/js/app.js') {{-- Este debe ir primero --}}
    @vite('resources/js/pages/notification.js')
    @vite('resources/js/components/base/tom-select.js')
@endPushOnce
