@extends('../themes/' . $activeTheme)
@section('title', 'Nuevo Mantenimiento')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehículos', 'url' => route('admin.vehicles.index')],
        ['label' => $vehicle->make . ' ' . $vehicle->model, 'url' => route('admin.vehicles.show', $vehicle->id)],
        ['label' => 'Mantenimientos', 'url' => route('admin.vehicles.maintenances.index', $vehicle->id)],
        ['label' => 'Nuevo Mantenimiento', 'active' => true],
    ];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">
                Nuevo Registro de Mantenimiento: {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.vehicles.maintenances.index', $vehicle->id) }}"
                    class="w-full sm:w-auto" variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Volver a Mantenimientos
                </x-base.button>
            </div>
        </div>

        <div class="box box--stacked mt-5">
            <div class="box-header">
                <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">
                    Datos del Servicio
                </div>
            </div>
            <div class="box-body p-5">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="ml-4 list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.vehicles.maintenances.store', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <x-base.form-label for="unit">Unidad/Sistema <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="unit" name="unit" value="{{ old('unit', $vehicle->company_unit_number) }}" 
                                placeholder="Ej: Motor, Transmisión, Frenos..." required />
                            <small class="text-slate-500">Identifica el sistema o parte del vehículo atendida</small>
                        </div>
                        <div>
                            <x-base.form-label for="service_tasks">Tareas realizadas <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="service_tasks" name="service_tasks" value="{{ old('service_tasks') }}" 
                                placeholder="Ej: Cambio de aceite, ajuste de frenos..." required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <div>
                            <x-base.form-label for="service_date">Fecha del servicio <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input type="date" id="service_date" name="service_date" 
                                value="{{ old('service_date', date('Y-m-d')) }}" required />
                        </div>
                        <div>
                            <x-base.form-label for="next_service_date">Próximo servicio <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input type="date" id="next_service_date" name="next_service_date" 
                                value="{{ old('next_service_date', date('Y-m-d', strtotime('+3 months'))) }}" required />
                        </div>
                        <div>
                            <x-base.form-label for="odometer">Odómetro (millas)</x-base.form-label>
                            <x-base.form-input type="number" id="odometer" name="odometer" value="{{ old('odometer') }}" 
                                placeholder="Lectura del odómetro" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <x-base.form-label for="vendor_mechanic">Proveedor/Mecánico <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="vendor_mechanic" name="vendor_mechanic" value="{{ old('vendor_mechanic') }}" 
                                placeholder="Ej: Taller Mecánico XYZ" required />
                        </div>
                        <div>
                            <x-base.form-label for="cost">Costo ($) <span class="text-danger">*</span></x-base.form-label>
                            <div class="input-group">
                                <div class="input-group-text">$</div>
                                <x-base.form-input type="number" step="0.01" id="cost" name="cost" value="{{ old('cost', '0.00') }}" 
                                    min="0" required />
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <x-base.form-label for="description">Descripción/Notas</x-base.form-label>
                        <x-base.form-textarea id="description" name="description" rows="4">{{ old('description') }}</x-base.form-textarea>
                    </div>

                    <!-- Drag and drop para subir tickets de servicio -->
                    <div class="mb-4">
                        <x-base.form-label>Tickets de Servicio</x-base.form-label>
                        <div 
                            x-data="{
                                files: [],
                                isUploading: false,
                                isDragging: false,
                                progress: 0,
                                handleFileSelect(e) {
                                    if (e.target.files.length) {
                                        this.addFiles(e.target.files);
                                    }
                                },
                                addFiles(fileList) {
                                    for (let i = 0; i < fileList.length; i++) {
                                        if (this.validateFile(fileList[i])) {
                                            this.files.push({
                                                file: fileList[i],
                                                name: fileList[i].name,
                                                size: this.formatFileSize(fileList[i].size),
                                                type: fileList[i].type,
                                                preview: this.createPreview(fileList[i])
                                            });
                                        }
                                    }
                                },
                                validateFile(file) {
                                    // Validar tipo de archivo (PDF, imágenes)
                                    const validTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                                    if (!validTypes.includes(file.type)) {
                                        alert('Tipo de archivo no permitido. Solo se permiten PDF e imágenes.');
                                        return false;
                                    }
                                    // Validar tamaño (max 10MB)
                                    if (file.size > 10 * 1024 * 1024) {
                                        alert('El archivo es demasiado grande. Tamaño máximo: 10MB');
                                        return false;
                                    }
                                    return true;
                                },
                                formatFileSize(size) {
                                    if (size < 1024) return size + ' bytes';
                                    else if (size < 1024 * 1024) return (size / 1024).toFixed(2) + ' KB';
                                    else return (size / (1024 * 1024)).toFixed(2) + ' MB';
                                },
                                createPreview(file) {
                                    if (file.type.startsWith('image/')) {
                                        return URL.createObjectURL(file);
                                    }
                                    return null;
                                },
                                removeFile(index) {
                                    this.files.splice(index, 1);
                                },
                                handleDrop(e) {
                                    e.preventDefault();
                                    this.isDragging = false;
                                    if (e.dataTransfer.files.length) {
                                        this.addFiles(e.dataTransfer.files);
                                    }
                                }
                            }"
                            class="border-2 border-dashed rounded-lg p-6 text-center"
                            :class="{'border-primary bg-primary/10': isDragging, 'border-slate-300': !isDragging}"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop="handleDrop"
                        >
                            <div x-show="files.length === 0">
                                <x-base.lucide class="mx-auto h-12 w-12 text-slate-400" icon="Upload" />
                                <p class="mt-2 text-sm text-slate-500">
                                    Arrastra y suelta archivos aquí o
                                    <label class="relative cursor-pointer text-primary">
                                        <span>selecciona archivos</span>
                                        <input type="file" class="sr-only" multiple @change="handleFileSelect" name="maintenance_files[]">
                                    </label>
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    PDF, JPG, PNG (Máx. 10MB por archivo)
                                </p>
                            </div>
                            
                            <div x-show="files.length > 0" class="mt-4">
                                <template x-for="(file, index) in files" :key="index">
                                    <div class="flex items-center justify-between p-2 border rounded mb-2 bg-white">
                                        <div class="flex items-center">
                                            <div class="mr-2">
                                                <template x-if="file.type === 'application/pdf'">
                                                    <x-base.lucide class="h-8 w-8 text-danger" icon="FileText" />
                                                </template>
                                                <template x-if="file.type.startsWith('image/')">
                                                    <img :src="file.preview" class="h-8 w-8 object-cover rounded" />
                                                </template>
                                            </div>
                                            <div class="text-left">
                                                <p class="text-sm font-medium truncate" x-text="file.name"></p>
                                                <p class="text-xs text-slate-500" x-text="file.size"></p>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeFile(index)" class="text-danger">
                                            <x-base.lucide class="h-5 w-5" icon="X" />
                                        </button>
                                    </div>
                                </template>
                                
                                <button type="button" @click="files = []" class="btn btn-sm btn-outline-secondary mt-2">
                                    <x-base.lucide class="mr-1 h-4 w-4" icon="Trash" /> Limpiar todos
                                </button>
                                
                                <div class="mt-2">
                                    <label class="relative cursor-pointer btn btn-sm btn-outline-primary">
                                        <x-base.lucide class="mr-1 h-4 w-4" icon="Plus" /> Agregar más archivos
                                        <input type="file" class="sr-only" multiple @change="handleFileSelect" name="maintenance_files[]">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center mt-5 pt-5 border-t border-slate-200/60">
                        <div class="form-check mr-4">
                            <input type="checkbox" id="status" name="status" value="1" class="form-check-input" {{ old('status') ? 'checked' : '' }}>
                            <label for="status" class="form-check-label">Marcar como completado</label>
                        </div>
                        <div class="ml-auto">
                            <x-base.button type="reset" variant="outline-secondary" class="mr-1 w-24">
                                Limpiar
                            </x-base.button>
                            <x-base.button type="submit" variant="primary" class="w-24">
                                Guardar
                            </x-base.button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Calcular fecha de próximo servicio (ejemplo: +3 meses desde servicio actual)
    document.getElementById('service_date').addEventListener('change', function() {
        const serviceDate = new Date(this.value);
        const nextServiceDate = new Date(serviceDate);
        nextServiceDate.setMonth(nextServiceDate.getMonth() + 3);
        
        const formattedDate = nextServiceDate.toISOString().split('T')[0];
        document.getElementById('next_service_date').value = formattedDate;
    });
</script>
@endpush