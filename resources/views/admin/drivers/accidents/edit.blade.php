@extends('../themes/' . $activeTheme)
@section('title', 'Edit Accident Record')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Accidents Management', 'url' => route('admin.accidents.index')],
        ['label' => 'Edit Accident Record', 'active' => true],
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
                Edit Accident Record
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.accidents.index') }}" class="w-full sm:w-auto"
                    variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Accidents
                </x-base.button>
            </div>
        </div>

        <!-- Formulario de Edición -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Accident Details</h3>
            </div>

            <div class="box-body p-5">

                <form action="{{ route('admin.accidents.update', $accident->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Carrier Selection -->
                        <div>
                            <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                            <select id="carrier_id" class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" disabled>
                                <option value="{{ $accident->userDriverDetail->carrier_id }}">
                                    {{ $accident->userDriverDetail->carrier->name }}</option>
                            </select>
                            <input type="hidden" name="carrier_id" value="{{ $accident->userDriverDetail->carrier_id }}">
                        </div>

                        <!-- Driver Selection -->
                        <div>
                            <x-base.form-label for="user_driver_detail_id">Driver</x-base.form-label>
                            <select id="user_driver_detail_id" name="user_driver_detail_id"
                                class="tom-select w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" required>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" {{ $driver->id == $accident->user_driver_detail_id ? 'selected' : '' }}>
                                        {{ $driver->user->name }} {{ $driver->user->lastname ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_driver_detail_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Accident Date -->
                        <div>
                            <x-base.form-label for="accident_date">Accident Date</x-base.form-label>
                            <x-base.form-input id="accident_date" name="accident_date" type="date" class="w-full"
                                value="{{ $accident->accident_date->format('m-d-Y') }}" required />
                            @error('accident_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Registration Date (Read-only) -->
                        <div>
                            <x-base.form-label>Registration Date</x-base.form-label>
                            <x-base.form-input type="text" class="w-full" value="{{ $accident->created_at->format('m-d-Y') }}"
                                readonly />
                        </div>

                        <!-- Nature of Accident -->
                        <div>
                            <x-base.form-label for="nature_of_accident">Nature of Accident</x-base.form-label>
                            <x-base.form-input id="nature_of_accident" name="nature_of_accident" type="text" class="w-full"
                                value="{{ $accident->nature_of_accident }}" required />
                            @error('nature_of_accident')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                        <!-- Had Injuries -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" id="had_injuries" name="had_injuries" class="form-check-input" value="1" 
                                    {{ $accident->had_injuries ? 'checked' : '' }}>
                                <label for="had_injuries" class="ml-2 form-label">Had Injuries?</label>
                            </div>
                            
                            <div id="injuries_container" class="mt-3 {{ $accident->had_injuries ? '' : 'hidden' }}">
                                <label for="number_of_injuries" class="form-label">Number of Injuries</label>
                                <input type="number" id="number_of_injuries" name="number_of_injuries" class="form-control w-full"
                                    min="0" value="{{ $accident->number_of_injuries }}">
                                @error('number_of_injuries')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Had Fatalities -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" id="had_fatalities" name="had_fatalities" class="form-check-input" value="1" 
                                    {{ $accident->had_fatalities ? 'checked' : '' }}>
                                <label for="had_fatalities" class="ml-2 form-label">Had Fatalities?</label>
                            </div>
                            
                            <div id="fatalities_container" class="mt-3 {{ $accident->had_fatalities ? '' : 'hidden' }}">
                                <label for="number_of_fatalities" class="form-label">Number of Fatalities</label>
                                <input type="number" id="number_of_fatalities" name="number_of_fatalities" class="form-control w-full"
                                    min="0" value="{{ $accident->number_of_fatalities }}">
                                @error('number_of_fatalities')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Comments -->
                    <div class="mt-6">
                        <label for="comments" class="form-label">Comments</label>
                        <textarea id="comments" name="comments" class="form-control w-full" rows="4">{{ $accident->comments }}</textarea>
                        @error('comments')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Documentos con FileUploader de Livewire -->
                    <div class="mt-8 border-t pt-6" id="documents">
                        <h3 class="text-lg font-medium mb-4">Documents</h3>
                        
                        <div class="mt-4">
                            @php
                            $existingFilesArray = [];
                            foreach ($documents as $document) {
                                // Verificar que document sea un objeto con las propiedades necesarias
                                if (is_object($document)) {
                                    try {
                                        $existingFilesArray[] = [
                                            'id' => $document->id,
                                            'name' => $document->file_name ?? 'Unknown',
                                            'file_name' => $document->file_name ?? 'Unknown',
                                            'mime_type' => $document->mime_type ?? 'application/octet-stream',
                                            'size' => $document->size ?? 0,
                                            'created_at' => $document->created_at ? $document->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                                            'url' => method_exists($document, 'getUrl') ? $document->getUrl() : route('admin.accidents.document.preview', $document->id),
                                            'is_temp' => false
                                        ];
                                    } catch (\Exception $e) {
                                        // Si hay error al acceder a alguna propiedad, lo ignoramos
                                        \Illuminate\Support\Facades\Log::error('Error al procesar documento para vista', [
                                            'document_id' => $document->id ?? 'unknown',
                                            'error' => $e->getMessage()
                                        ]);
                                    }
                                }
                            }
                            @endphp

                            <livewire:components.file-uploader
                                model-name="accident_files"
                                :model-index="0"
                                :label="'Upload Documents'"
                                :existing-files="$existingFilesArray"
                            />
                            <!-- Campo oculto para almacenar los archivos subidos -->
                            <input type="hidden" name="accident_files" id="accident_files_input">
                        </div>
                    </div>
                        
                        <!-- Ya no necesitamos este componente porque lo agregamos arriba -->        
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end mt-5">
                        <x-base.button as="a" href="{{ route('admin.accidents.index') }}" variant="outline-secondary" class="mr-2">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Update Accident Record
                        </x-base.button>
                    </div>
                </form>


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
                            const fileId = data.fileId;
                            
                            // Si es un archivo permanente (no temporal), eliminarlo de la base de datos
                            if (!data.isTemp) {
                                // Llamar al endpoint para eliminar el documento
                                fetch('{{ route("admin.accidents.documents.ajax-destroy") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        document_id: fileId
                                    })
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
                            uploadedFiles = uploadedFiles.filter((file, index) => {
                                // Para archivos temporales, el ID contiene un timestamp
                                if (fileId.startsWith('temp_') && index === uploadedFiles.length - 1) {
                                    // Eliminar el último archivo añadido si es temporal
                                    return false;
                                }
                                return true;
                            });
                            
                            // Actualizar el campo oculto con el nuevo array
                            accidentFilesInput.value = JSON.stringify(uploadedFiles);
                            console.log('Archivos actualizados después de eliminar:', accidentFilesInput.value);
                        }
                    });
                });
                
                // Mostrar/ocultar campos de lesiones y fatalidades
                const hadInjuriesCheckbox = document.getElementById('had_injuries');
                const injuriesContainer = document.getElementById('injuries_container');
                const hadFatalitiesCheckbox = document.getElementById('had_fatalities');
                const fatalitiesContainer = document.getElementById('fatalities_container');

                hadInjuriesCheckbox.addEventListener('change', function() {
                    injuriesContainer.classList.toggle('hidden', !this.checked);
                    if (!this.checked) {
                        document.getElementById('number_of_injuries').value = '';
                    }
                });

                hadFatalitiesCheckbox.addEventListener('change', function() {
                    fatalitiesContainer.classList.toggle('hidden', !this.checked);
                    if (!this.checked) {
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
