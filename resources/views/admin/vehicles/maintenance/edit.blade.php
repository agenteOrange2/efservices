@extends('../themes/' . $activeTheme)
@section('title', 'Edit Maintenance Record')
@php
    $breadcrumbLinks = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
        ['label' => 'Maintenance', 'url' => route('admin.maintenance.index')],
        ['label' => 'Edit Maintenance Record', 'active' => true],
    ];
@endphp
@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Edit Maintenance Record
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.maintenance.index') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="outline-secondary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowLeft" />
                        Back to List
                    </x-base.button>
                </div>
            </div>

            <div class="intro-y box p-5 mt-5">
                <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                    <div class="font-medium text-base truncate">Maintenance Information</div>
                </div>

                <form action="{{ route('admin.maintenance.update', $maintenance->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mt-3">
                        <x-base.form-label for="vehicle_id">Vehicle</x-base.form-label>
                        <select id="vehicle_id" name="vehicle_id"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('vehicle_id') border-danger @enderror">
                            <option value="">Select Vehicle</option>
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}"
                                    {{ $maintenance->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->make }} {{ $vehicle->model }}
                                    ({{ $vehicle->company_unit_number ?? $vehicle->vin }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-3">
                        <x-base.form-label for="service_tasks">Maintenance Type</x-base.form-label>
                        <select id="service_tasks" name="service_tasks"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 @error('service_tasks') border-danger @enderror">
                            <option value="">Select Maintenance Type</option>
                            @foreach ($maintenanceTypes as $type)
                                <option value="{{ $type }}"
                                    {{ $maintenance->service_tasks == $type ? 'selected' : '' }}>{{ $type }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_tasks')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-base.form-label for="service_date">Service Date</x-base.form-label>
                            <x-base.litepicker id="service_date" name="service_date" class="w-full"
                                value="{{ $maintenance->service_date }}" required />
                            @error('service_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- <div>
                            <label for="next_service_date" class="form-label">Fecha Próximo Mantenimiento</label>
                            <input id="next_service_date" type="datetime-local" name="next_service_date"
                                value="{{ old('next_service_date', $maintenance->next_service_date ? $maintenance->next_service_date->format('Y-m-d\\TH:i') : '') }}"
                                class="form-control w-full @error('next_service_date') border-danger @enderror">
                            @error('next_service_date')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div> --}}
                        <div>
                            <x-base.form-label for="next_service_date">Next Service Date</x-base.form-label>
                            <x-base.litepicker id="next_service_date" name="next_service_date" class="w-full"
                                value="{{ $maintenance->next_service_date }}" required />
                            @error('next_service_date')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-base.form-label for="unit">Unit</x-base.form-label>
                            <x-base.form-input id="unit" name="unit" type="text"
                                class="w-full" placeholder="Número de unidad o identificador"
                                value="{{ old('unit', $maintenance->unit) }}"
                                 required />
                            @error('unit')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <x-base.form-label for="vendor_mechanic">Proveedor/Mecánico</x-base.form-label>
                            <x-base.form-input id="vendor_mechanic" name="vendor_mechanic" type="text"
                                class="w-full" placeholder="Ej: Taller Automotriz XYZ"
                                value="{{ old('vendor_mechanic', $maintenance->vendor_mechanic) }}"
                                 required />
                            @error('vendor_mechanic')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>                        
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-base.form-label for="cost">Costo</x-base.form-label>
                            <x-base.form-input id="cost" name="cost" type="number"
                                class="w-full" placeholder="Ex: 5000"
                                value="{{ old('cost', $maintenance->cost) }}" required />
                            @error('cost')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>                        

                        <div>
                            <x-base.form-label for="odometer">Lectura de Odómetro</x-base.form-label>
                            <x-base.form-input id="odometer" name="odometer" type="number"
                                class="w-full" placeholder="Ej: 50000" min="0"
                                value="{{ old('odometer', $maintenance->odometer) }}" required />
                            @error('odometer')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <x-base.form-label for="description">Descripción</x-base.form-label>
                        <x-base.form-textarea id="description" name="description"
                            class="w-full @error('description') border-danger @enderror" rows="4"
                            placeholder="Detalles adicionales del mantenimiento" maxlength="1000">{{ old('description', $maintenance->description) }}</x-base.form-textarea>
                        @error('description')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="mt-3">
                        <div class="flex items-center">
                            <input id="status" type="checkbox" name="status" value="1"
                                {{ old('status', $maintenance->status) ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-primary border-gray-300 rounded mr-2">
                            <label for="status" class="ml-2 form-label">Mark as Completed</label>
                        </div>
                        @error('status')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Sección de documentos adjuntos usando Livewire file-uploader -->
                    <div class="mt-8 pt-5 border-t border-slate-200/60 dark:border-darkmode-400">
                        <h3 class="text-lg font-medium mb-5">Attachments</h3>

                        <!-- Campo oculto para almacenar la información de los archivos -->
                        <input type="hidden" name="livewire_files" id="livewire_files"
                            value="{{ json_encode(
                                $maintenance->getMedia('maintenance_files')->map(function ($media) {
                                        return [
                                            'id' => $media->id,
                                            'name' => $media->file_name,
                                            'size' => $media->size,
                                            'mime_type' => $media->mime_type,
                                            'url' => $media->getFullUrl(),
                                            'created_at' => $media->created_at,
                                        ];
                                    })->toArray(),
                            ) }}">

                        <!-- Componente Livewire para carga de archivos -->
                        <livewire:components.file-uploader 
                            model-name="maintenance_files" 
                            :model-index="$maintenance->id"
                            :auto-upload="true"
                            :existing-files="$maintenance->getMedia('maintenance_files')->map(function ($media) {
                                return [
                                    'id' => $media->id,
                                    'name' => $media->file_name,
                                    'size' => $media->size,
                                    'mime_type' => $media->mime_type,
                                    'url' => $media->getFullUrl(),
                                    'created_at' => $media->created_at->format('Y-m-d H:i:s'),
                                    'is_temp' => false
                                ];
                            })->toArray()"
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 cursor-pointer" />
                    </div>

                    <div class="flex justify-end mt-5">
                        <x-base.button as="a" href="{{ route('admin.maintenance.index') }}"
                            variant="outline-secondary" class="mr-2">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="primary">
                            Update Maintenance Record
                        </x-base.button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Obtener referencias a los elementos del DOM
                const vehicleSelect = document.getElementById('vehicle_id');
                const unitInput = document.getElementById('unit');
                const uploadedFilesInput = document.getElementById('livewire_files');

                // Datos de vehículos para autocompletar el campo de unidad
                const vehiclesData = @json(
                    $vehicles->map(function ($vehicle) {
                        return [
                            'id' => $vehicle->id,
                            'unit' => $vehicle->company_unit_number ?? '',
                        ];
                    }));

                // Función para actualizar el campo de unidad cuando se selecciona un vehículo
                vehicleSelect.addEventListener('change', function() {
                    const selectedVehicleId = parseInt(this.value);
                    if (!selectedVehicleId) return;

                    // Si el campo de unidad está vacío o no ha sido modificado manualmente
                    if (!unitInput.value || unitInput.dataset.autoFilled === 'true') {
                        const selectedVehicle = vehiclesData.find(v => v.id === selectedVehicleId);
                        if (selectedVehicle && selectedVehicle.unit) {
                            unitInput.value = selectedVehicle.unit;
                            unitInput.dataset.autoFilled = 'true';
                        }
                    }
                });

                // Marcar cuando el usuario modifica manualmente el campo de unidad
                unitInput.addEventListener('input', function() {
                    this.dataset.autoFilled = 'false';
                });

                // Manejar eventos de archivos subidos por Livewire
                // Inicializar con archivos existentes
                let uploadedFiles = [];

                // Actualizar campo oculto con los archivos iniciales
                if (uploadedFilesInput.value) {
                    try {
                        uploadedFiles = JSON.parse(uploadedFilesInput.value);
                    } catch (e) {
                        console.error('Error parsing uploaded files:', e);
                    }
                }

                // Escuchar evento cuando un archivo es subido
                window.addEventListener('fileUploaded', (event) => {
                    const fileData = event.detail;
                    uploadedFiles.push(fileData);
                    uploadedFilesInput.value = JSON.stringify(uploadedFiles);
                });

                // Escuchar evento cuando un archivo es eliminado
                window.addEventListener('fileRemoved', (event) => {
                    const fileId = event.detail;
                    uploadedFiles = uploadedFiles.filter(file => {
                        return file.id !== fileId && file.path !== fileId;
                    });
                    uploadedFilesInput.value = JSON.stringify(uploadedFiles);
                });
            });
        </script>
    @endpush
@endsection
