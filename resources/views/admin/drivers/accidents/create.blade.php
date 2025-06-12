@extends('../themes/' . $activeTheme)
@section('title', 'Add Accident Record')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Accidents Management', 'url' => route('admin.accidents.index')],
        ['label' => 'Add Accident Record', 'active' => true],
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
                Add New Accident Record
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.accidents.index') }}" class="w-full sm:w-auto"
                    variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Accidents
                </x-base.button>
            </div>
        </div>

        <!-- Formulario de Creación -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title">Accident Details</h3>
            </div>

            <div class="box-body">
                <form action="{{ route('admin.accidents.store') }}" method="POST" enctype="multipart/form-data">
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
                            <x-base.form-label for="accident_date">Accident Date</x-base.form-label>
                            <x-base.litepicker id="accident_date" name="accident_date" value="{{ old('accident_date') }}"
                                class="@error('accident_date') border-danger @enderror" placeholder="MM/DD/YYYY"
                                required />
                            @error('accident_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <x-base.form-label for="nature_of_accident">Nature of Accident</x-base.form-label>
                            <x-base.form-input id="nature_of_accident" name="nature_of_accident" type="text"
                                class="w-full" required />
                            @error('nature_of_accident')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                        <!-- Had Injuries -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" id="had_injuries" name="had_injuries" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
                                    value="1">
                                <label for="had_injuries" class="ml-2 form-label">Had Injuries?</label>
                            </div>

                            <div id="injuries_container" class="mt-3 hidden">
                                <label for="number_of_injuries" class="form-label">Number of Injuries</label>
                                <x-base.form-input id="number_of_injuries" name="number_of_injuries" type="number"
                                    class="w-full" min="0" />
                                @error('number_of_injuries')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Had Fatalities -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" id="had_fatalities" name="had_fatalities" class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2"
                                    value="1">
                                <label for="had_fatalities" class="ml-2 form-label">Had Fatalities?</label>
                            </div>

                            <div id="fatalities_container" class="mt-3 hidden">
                                <label for="number_of_fatalities" class="form-label">Number of Fatalities</label>
                                <x-base.form-input id="number_of_fatalities" name="number_of_fatalities" type="number"
                                    class="w-full" min="0" />
                                @error('number_of_fatalities')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Comments -->
                    <div class="mt-6">
                        <x-base.form-label for="comments">Comments</x-base.form-label>
                        <x-base.form-textarea id="comments" name="comments" class="w-full"
                            rows="4"></x-base.form-textarea>
                        @error('comments')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Document Upload with Livewire component -->
                    <div class="col-span-1 md:col-span-2">
                        <livewire:components.file-uploader model-name="accident_files" :model-index="0" :auto-upload="true"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 cursor-pointer" />
                        <!-- Campo oculto para almacenar los archivos subidos -->
                        <input type="hidden" name="accident_files" id="accident_files_input">
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end mt-5">
                        <x-base.button as="a" href="{{ route('admin.accidents.index') }}"
                            variant="outline-secondary" class="mr-2">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Create Accident Record
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
            // Inicializar el array para almacenar los archivos
            let uploadedFiles = [];
            const accidentFilesInput = document.getElementById('accident_files_input');

            // Escuchar eventos del componente Livewire
            window.addEventListener('livewire:initialized', () => {
                // Escuchar el evento fileUploaded del componente Livewire
                Livewire.on('fileUploaded', (eventData) => {
                    console.log('Archivo subido:', eventData);
                    // Extraer los datos del evento
                    const data = eventData[0]; // Los datos vienen como primer elemento del array

                    if (data.modelName === 'accident_files') {
                        // Añadir el archivo al array de archivos
                        uploadedFiles.push({
                            path: data.tempPath,
                            original_name: data.originalName,
                            mime_type: data.mimeType,
                            size: data.size
                        });

                        // Actualizar el campo oculto con el nuevo array
                        accidentFilesInput.value = JSON.stringify(uploadedFiles);
                        console.log('Archivos actualizados:', accidentFilesInput.value);
                    }
                });

                // Escuchar el evento fileRemoved del componente Livewire
                Livewire.on('fileRemoved', (eventData) => {
                    console.log('Archivo eliminado:', eventData);
                    // Extraer los datos del evento
                    const data = eventData[0]; // Los datos vienen como primer elemento del array

                    if (data.modelName === 'accident_files') {
                        // Eliminar el archivo del array
                        const fileId = data.fileId;
                        uploadedFiles = uploadedFiles.filter((file, index) => {
                            // Para archivos temporales, el ID contiene un timestamp
                            if (fileId.startsWith('temp_') && index === uploadedFiles
                                .length - 1) {
                                // Eliminar el último archivo añadido si es temporal
                                return false;
                            }
                            return true;
                        });

                        // Actualizar el campo oculto con el nuevo array
                        accidentFilesInput.value = JSON.stringify(uploadedFiles);
                        console.log('Archivos actualizados después de eliminar:', accidentFilesInput
                            .value);
                    }
                });
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

            // Injuries/Fatalities Checkbox Logic
            const hadInjuriesCheckbox = document.getElementById('had_injuries');
            const injuriesContainer = document.getElementById('injuries_container');
            const hadFatalitiesCheckbox = document.getElementById('had_fatalities');
            const fatalitiesContainer = document.getElementById('fatalities_container');

            hadInjuriesCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    injuriesContainer.classList.remove('hidden');
                } else {
                    injuriesContainer.classList.add('hidden');
                    document.getElementById('number_of_injuries').value = '';
                }
            });

            hadFatalitiesCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    fatalitiesContainer.classList.remove('hidden');
                } else {
                    fatalitiesContainer.classList.add('hidden');
                    document.getElementById('number_of_fatalities').value = '';
                }
            });
        });
    </script>
@endpush

@pushOnce('scripts')
    @vite('resources/js/app.js') {{-- Este debe ir primero --}}
    @vite('resources/js/pages/notification.js')
    @vite('resources/js/components/base/tom-select.js')
@endPushOnce
