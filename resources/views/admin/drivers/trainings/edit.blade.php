@extends('../themes/' . $activeTheme)

@section('title', 'Edit Training')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Trainings', 'url' => route('admin.trainings.index')],
        ['label' => 'Editar', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Edit Training</h1>
                <p class="mt-1 text-sm text-gray-600">Modify the training information</p>
            </div>
            <div>
                <x-base.button as="a" href="{{ route('admin.trainings.index') }}" variant="outline">
                    <x-base.lucide class="w-5 h-5 mr-2" icon="arrow-left" />
                    Volver
                </x-base.button>
            </div>
        </div>

        <div class="box box--stacked mt-5 p-3">
            <div class="box-content">
                <form action="{{ route('admin.trainings.update', $training->id) }}" method="POST" enctype="multipart/form-data" x-data="trainingForm()">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2">
                            <x-base.form-label for="title" required>Title</x-base.form.label>
                            <x-base.form-input type="text" name="title" id="title" value="{{ old('title', $training->title) }}" required />
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="col-span-2">
                            <x-base.form-label for="description">Description</x-base.form.label>
                            <x-base.form-textarea name="description" id="description" rows="4">{{ old('description', $training->description) }}</x-base.form.textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="content_type" required>Content Type</x-base.form.label>
                            <x-base.form-select name="content_type" id="content_type" x-model="contentType" required>
                                <option value="">Select type</option>
                                <option value="file" {{ old('content_type', $training->content_type) === 'file' ? 'selected' : '' }}>File</option>
                                <option value="video" {{ old('content_type', $training->content_type) === 'video' ? 'selected' : '' }}>Video</option>
                                <option value="url" {{ old('content_type', $training->content_type) === 'url' ? 'selected' : '' }}>URL</option>
                            </x-base.form.select>
                            @error('content_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <x-base.form-label for="status" required>Status</x-base.form.label>
                            <x-base.form-select name="status" id="status" required>
                                <option value="active" {{ old('status', $training->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $training->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </x-base.form.select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Campo de URL para videos -->
                        <div class="col-span-2" x-show="contentType === 'video'">
                            <x-base.form-label for="video_url" x-bind:required="contentType === 'video'">Video URL</x-base.form.label>
                            <x-base.form-input type="url" name="video_url" id="video_url" value="{{ old('video_url', $training->video_url) }}" 
                                x-bind:required="contentType === 'video'" placeholder="https://www.youtube.com/watch?v=..." />
                            <p class="mt-1 text-sm text-gray-500">Insert the URL of YouTube, Vimeo or another video platform</p>
                            @error('video_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Campo de URL para enlaces directos -->
                        <div class="col-span-2" x-show="contentType === 'url'">
                            <x-base.form-label for="url" x-bind:required="contentType === 'url'">URL of Content</x-base.form.label>
                            <x-base.form-input type="url" name="url" id="url" value="{{ old('url', $training->url ?? '') }}" 
                                x-bind:required="contentType === 'url'" placeholder="https://..." />
                            <p class="mt-1 text-sm text-gray-500">Insert the URL of external content</p>
                            @error('url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Existing files -->
                        <div class="col-span-2" x-show="contentType === 'file'">
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Current files:</h4>
                                @if($training->getMedia('training_files')->count() > 0)
                                    <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                                        @foreach($training->getMedia('training_files') as $media)
                                            <li class="px-4 py-3 flex items-center justify-between text-sm">
                                                <div class="flex items-center">
                                                    <x-base.lucide class="flex-shrink-0 h-5 w-5 text-gray-400 mr-3" icon="file-text" />
                                                    <span class="truncate">{{ $media->file_name }}</span>
                                                    <span class="ml-2 text-xs text-gray-500">{{ number_format($media->size / 1024, 2) }} KB</span>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('admin.trainings.preview-document', $media->id) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                                        <x-base.lucide class="w-5 h-5" icon="eye" />
                                                    </a>
                                                    <button type="button" 
                                                        onclick="if(confirm('¿Está seguro de que desea eliminar este archivo?')) { 
                                                            fetch('{{ route('api.documents.delete.post') }}', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                    'Content-Type': 'application/json',
                                                                    'Accept': 'application/json'
                                                                },
                                                                body: JSON.stringify({ 
                                                                    mediaId: {{ $media->id }},
                                                                    _token: '{{ csrf_token() }}'
                                                                })
                                                            })
                                                            .then(response => response.json())
                                                            .then(data => {
                                                                if(data.success) {
                                                                    window.location.reload();
                                                                } else {
                                                                    alert('Error deleting file: ' + (data.message || 'Unknown error'));
                                                                }
                                                            })
                                                            .catch(error => {
                                                                alert('Error: ' + error);
                                                            });
                                                        }"
                                                        class="text-red-600 hover:text-red-900">
                                                        <x-base.lucide class="w-5 h-5" icon="trash-2" />
                                                    </button>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-sm text-gray-500">No files attached</p>
                                @endif
                            </div>
                            
                            <!-- Carga de nuevos archivos con Livewire FileUploader -->
                            <div class="mb-4">
                                <x-base.form-label for="files">Add new files</x-base.form.label>
                                <div class="border border-dashed rounded-md p-4 mt-2">
                                    <livewire:components.file-uploader
                                        model-name="training_files"
                                        :model-index="0"
                                        :auto-upload="true"
                                    />
                                    <!-- Campo oculto para almacenar información de archivos en formato JSON -->
                                    <input type="hidden" name="files_data" id="files_data" value="">
                                </div>
                                
                                @error('files')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('files.*')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <x-base.button type="button" variant="outline" class="mr-3" onclick="window.location.href='{{ route('admin.trainings.index') }}'">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit">
                            <x-base.lucide class="w-5 h-5 mr-2" icon="save" />
                            Update Training
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function trainingForm() {
        return {
            contentType: '{{ old('content_type', $training->content_type) }}'
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar el array para almacenar los archivos
        let uploadedFiles = [];
        // IMPORTANTE: Asegurarnos que el campo oculto esté accesible en toda la función
        const filesDataInput = document.getElementById('files_data');
        console.log('Campo oculto encontrado:', filesDataInput ? 'Sí' : 'No');
        
        // Inicializar el campo oculto como un array vacío
        if (filesDataInput) {
            filesDataInput.value = JSON.stringify([]);
        }
        
        // Escuchar eventos del componente Livewire
        if (typeof Livewire !== 'undefined') {
            console.log('Livewire detectado, preparando escucha de eventos');
            
            // Escuchar el evento fileUploaded del componente Livewire
            Livewire.on('fileUploaded', (eventData) => {
                console.log('Archivo subido evento recibido:', eventData);
                // Extraer los datos del evento
                const data = eventData[0]; // Los datos vienen como primer elemento del array
                
                if (data.modelName === 'training_files') {
                    console.log('Archivo subido para training_files');
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
                    const hiddenInput = document.getElementById('files_data');
                    if (hiddenInput) {
                        hiddenInput.value = JSON.stringify(uploadedFiles);
                        console.log('Campo actualizado con:', hiddenInput.value);
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
                
                if (data.modelName === 'training_files') {
                    console.log('Archivo eliminado para training_files');
                    // Filtrar el archivo eliminado del array
                    uploadedFiles = uploadedFiles.filter(file => file.tempPath !== data.tempPath);
                    
                    // Actualizar el campo oculto
                    const hiddenInput = document.getElementById('files_data');
                    if (hiddenInput) {
                        hiddenInput.value = JSON.stringify(uploadedFiles);
                        console.log('Campo actualizado después de eliminar:', hiddenInput.value);
                    } else {
                        console.error('Campo oculto no encontrado en el DOM');
                    }
                }
            });
        } else {
            console.warn('Livewire no está definido todavía, los eventos no se registrarán');
        }
    });
</script>
@endpush
