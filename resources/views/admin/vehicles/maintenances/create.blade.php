@extends('../themes/' . $activeTheme)
@section('title', 'New Maintenance')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
        ['label' => $vehicle->make . ' ' . $vehicle->model, 'url' => route('admin.vehicles.show', $vehicle->id)],
        ['label' => 'Maintenances', 'url' => route('admin.vehicles.maintenances.index', $vehicle->id)],
        ['label' => 'New Maintenance', 'active' => true],
    ];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">
                New Maintenance Record: {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.vehicles.maintenances.index', $vehicle->id) }}"
                    class="w-full sm:w-auto" variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Back to Maintenances
                </x-base.button>
            </div>
        </div>

        <div class="box box--stacked mt-5">
            <div class="box-header">
                <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">
                    Service Data
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
                            <x-base.form-label for="unit">Unit/System <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="unit" name="unit" value="{{ old('unit', $vehicle->company_unit_number) }}" 
                                placeholder="Ej: Motor, Transmisión, Frenos..." required  disabled/>
                            <small class="text-slate-500">Identifica el sistema o parte del vehículo atendida</small>
                        </div>
                        <div>
                            <x-base.form-label for="service_tasks">Tasks performed <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="service_tasks" name="service_tasks" value="{{ old('service_tasks') }}" 
                                placeholder="EX: Change oil, Brake adjustment..." required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <div>
                            <x-base.form-label for="service_date">Service Date <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input type="date" id="service_date" name="service_date" 
                                value="{{ old('service_date', date('Y-m-d')) }}" required />
                        </div>
                        <div>
                            <x-base.form-label for="next_service_date">Next Service Date <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input type="date" id="next_service_date" name="next_service_date" 
                                value="{{ old('next_service_date', date('Y-m-d', strtotime('+3 months'))) }}" required />
                        </div>
                        <div>
                            <x-base.form-label for="odometer">Odometer (miles)</x-base.form-label>
                            <x-base.form-input type="number" id="odometer" name="odometer" value="{{ old('odometer') }}" 
                                placeholder="Odometer reading" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <x-base.form-label for="vendor_mechanic">Vendor/Mechanic <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="vendor_mechanic" name="vendor_mechanic" value="{{ old('vendor_mechanic') }}" 
                                placeholder="EX: Mechanic Shop XYZ" required />
                        </div>
                        <div>
                            <x-base.form-label for="cost">Cost ($) <span class="text-danger">*</span></x-base.form-label>
                            <div class="input-group">                                
                                <x-base.form-input type="number" step="0.01" id="cost" name="cost" value="{{ old('cost', '0.00') }}" 
                                    min="0" required />
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <x-base.form-label for="description">Description/Notes</x-base.form-label>
                        <x-base.form-textarea id="description" name="description" rows="4">{{ old('description') }}</x-base.form-textarea>
                    </div>

                    <!-- Drag and drop para subir tickets de servicio -->
                    <div class="mb-4">
                        <x-base.form-label>Tickets of Service</x-base.form-label>
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
                                        alert('File type not allowed. Only PDF and images are allowed.');
                                        return false;
                                    }
                                    // Validar tamaño (max 10MB)
                                    if (file.size > 10 * 1024 * 1024) {
                                        alert('File is too large. Maximum size: 10MB');
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
                            class="border-2 border-dashed rounded-lg p-6"
                            :class="{'border-primary bg-primary/10': isDragging, 'border-slate-300': !isDragging}"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop="handleDrop"
                        >
                            <!-- Estado inicial - sin archivos -->
                            <div x-show="files.length === 0" class="text-center">
                                <x-base.lucide class="mx-auto h-12 w-12 text-slate-400" icon="Upload" />
                                <p class="mt-2 text-sm text-slate-500">
                                    Drag and drop files here or
                                    <label class="relative cursor-pointer text-primary hover:text-primary-dark">
                                        <span class="underline">select files</span>
                                        <input type="file" class="sr-only" multiple @change="handleFileSelect" name="maintenance_files[]">
                                    </label>
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    PDF, JPG, PNG (Max. 10MB per file)
                                </p>
                            </div>
                            
                            <!-- Estado con archivos -->
                            <div x-show="files.length > 0" x-cloak>
                                <!-- Lista de archivos -->
                                <div class="space-y-3 mb-4">
                                    <template x-for="(file, index) in files" :key="index">
                                        <div class="flex items-center justify-between p-3 border border-slate-200 rounded-lg bg-white shadow-sm">
                                            <div class="flex items-center min-w-0 flex-1">
                                                <!-- Icono/Preview del archivo -->
                                                <div class="flex-shrink-0 mr-3">
                                                    <template x-if="file.type === 'application/pdf'">
                                                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">                                                            
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text-icon lucide-file-text h-6 w-6 text-red-600"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                                                        </div>
                                                    </template>
                                                    <template x-if="file.type.startsWith('image/')">
                                                        <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100">
                                                            <img :src="file.preview" class="w-full h-full object-cover" alt="Preview" />
                                                        </div>
                                                    </template>
                                                </div>
                                                
                                                <!-- Información del archivo -->
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium text-slate-900 truncate" x-text="file.name"></p>
                                                    <p class="text-xs text-slate-500" x-text="file.size"></p>
                                                </div>
                                            </div>
                                            
                                            <!-- Botón eliminar -->
                                            <button 
                                                type="button" 
                                                @click="removeFile(index)" 
                                                class="flex-shrink-0 ml-3 p-1 text-slate-400 hover:text-red-500 rounded-full hover:bg-red-50 transition-colors"
                                            >
                                                <x-base.lucide class="h-5 w-5" icon="X" />
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                
                                <!-- Botones de acción -->
                                <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-200">
                                    <label class="inline-flex items-center px-3 py-2 text-sm font-medium text-primary bg-white border border-primary rounded-lg hover:bg-primary hover:text-white transition-colors cursor-pointer">
                                        <x-base.lucide class="mr-2 h-4 w-4" icon="Plus" />
                                        Add more files
                                        <input type="file" class="sr-only" multiple @change="handleFileSelect" name="maintenance_files[]">
                                    </label>
                                    
                                    <button 
                                        type="button" 
                                        @click="files = []" 
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors"
                                    >
                                        <x-base.lucide class="mr-2 h-4 w-4" icon="Trash" />
                                        Clear all
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center mt-5 pt-5 border-t border-slate-200/60">
                        <div class="form-check mr-4">                            
                            <input type="checkbox" id="status" name="status" value="1" class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary"
                            {{ old('status') ? 'checked' : '' }}>   
                            <label for="status" class="ml-2">Mark as completed</label>
                        </div>
                        <div class="ml-auto">
                            <x-base.button type="reset" variant="outline-secondary" class="mr-1 w-24">
                                Clear
                            </x-base.button>
                            <x-base.button type="submit" variant="primary" class="w-24">
                                Save
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