@props([
    'model' => null,              // El modelo al que se asociarán los archivos
    'modelType' => '',            // Tipo de modelo (accident, traffic, testing, etc)
    'modelId' => 0,               // ID del modelo 
    'collection' => 'documents',  // Colección de Spatie Media Library
    'maxFiles' => 10,             // Número máximo de archivos
    'maxSize' => 10240,           // Tamaño máximo en KB
    'acceptedTypes' => '.jpg,.jpeg,.png,.pdf,.doc,.docx', // Tipos de archivos aceptados
    'route' => '',                // Ruta para el envío del formulario
    'showExisting' => true,       // Mostrar archivos existentes
    'deleteRoute' => '',          // Ruta para eliminar documentos
    'previewRoute' => '',         // Ruta para previsualizar documentos
    'dropzoneText' => 'Drag and drop files here or click to browse',
    'uploadButtonText' => 'Upload Documents'
])

<div class="file-uploader">
    <!-- Documentos existentes -->
    @if($showExisting && isset($model) && $model->getMedia($collection)->count() > 0)
        <div class="mt-6">
            <label class="form-label">Existing Documents</label>
            <div class="border rounded-lg p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($model->getMedia($collection) as $document)
                        <div class="border rounded p-3 flex flex-col">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium truncate" title="{{ $document->file_name }}">
                                    {{ $document->file_name }}
                                </span>
                                <div class="flex items-center space-x-2">
                                    @if($previewRoute)
                                        <a href="{{ route($previewRoute, $document->id) }}" 
                                            target="_blank" class="text-primary hover:text-primary-focus">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                    
                                    @if($deleteRoute)
                                        <form action="{{ route($deleteRoute, $document->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-danger hover:text-danger-focus border-0 bg-transparent p-0" 
                                                onclick="return confirm('¿Estás seguro que deseas eliminar este documento?');">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-grow flex items-center justify-center p-2 bg-slate-50 rounded">
                                @php
                                    $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                                @endphp
                                
                                @if($isImage && $previewRoute)
                                    <img src="{{ route($previewRoute, $document->id) }}" 
                                        alt="{{ $document->file_name }}" class="max-h-24 object-contain">
                                @else
                                    <div class="text-center">
                                        <i data-lucide="file-text" class="mx-auto w-12 h-12 text-slate-400"></i>
                                        <span class="block text-xs text-slate-500 mt-1">{{ strtoupper($extension) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Formulario simple para subir documentos -->
    <div class="mt-6">
        <h3 class="text-lg font-medium mb-4">Upload New Documents</h3>
        <form action="{{ route($route, $modelId) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="documents" class="form-label">Select Files</label>
                <input type="file" name="documents[]" id="documents" class="form-control w-full" multiple 
                    accept="{{ $acceptedTypes }}">
                <p class="text-xs text-gray-500 mt-1">Allowed: {{ str_replace(',', ', ', $acceptedTypes) }} (Max {{ $maxSize / 1024 }}MB each)</p>
            </div>
            
            @error('documents')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
            @error('documents.*')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
            
            <button type="submit" class="btn btn-primary mt-2">{{ $uploadButtonText }}</button>
        </form>
    </div>
</div>
