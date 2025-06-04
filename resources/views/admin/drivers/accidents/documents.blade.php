@extends('../themes/' . $activeTheme)
@section('title', 'Accident Documents')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Accidents', 'url' => route('admin.accidents.index')],
        ['label' => 'All Documents', 'active' => true],
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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between mt-8">
            <h2 class="text-lg font-medium">
                All Accident Documents
            </h2>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <x-base.button as="a" href="{{ route('admin.accidents.index') }}" class="w-full sm:w-auto"
                    variant="outline-primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                    Back to Accidents
                </x-base.button>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Filter Documents</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.accidents.documents.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if(request()->has('accident_id'))
                    <input type="hidden" name="accident_id" value="{{ request('accident_id') }}">
                    @endif
                    <div>
                        <x-base.form-label for="driver_id">Driver</x-base.form-label>
                        <select id="driver_id" name="driver_id" class="tom-select w-full">
                            <option value="">All Drivers</option>
                            @foreach ($drivers ?? [] as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }} {{ $driver->user->last_name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <x-base.form-label for="file_type">File Type</x-base.form-label>
                        <select id="file_type" name="file_type" class="tom-select w-full">
                            <option value="">All Types</option>
                            <option value="image" {{ request('file_type') == 'image' ? 'selected' : '' }}>Images</option>
                            <option value="pdf" {{ request('file_type') == 'pdf' ? 'selected' : '' }}>PDFs</option>
                            <option value="document" {{ request('file_type') == 'document' ? 'selected' : '' }}>Documents</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <x-base.button type="submit" variant="primary" class="w-full">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="filter" />
                            Apply Filters
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lista de Documentos -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <h3 class="box-title">Documents ({{ $documents->count() ?? 0 }})</h3>
                </div>
            </div>
            <div class="box-body p-5">
                @if ($documents->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-report mt-2 w-full">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">Documento</th>
                                    <th class="whitespace-nowrap">Conductor</th>
                                    <th class="whitespace-nowrap">Fecha del Accidente</th>
                                    <th class="whitespace-nowrap">Naturaleza</th>
                                    <th class="whitespace-nowrap">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documents as $document)
                                    <tr class="intro-x">
                                        <td class="w-40">
                                            <div class="flex items-center">
                                                @php
                                                    $iconClass = '';
                                                    $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
                                                    
                                                    if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'])) {
                                                        $iconClass = 'image';
                                                    } elseif (strtolower($extension) === 'pdf') {
                                                        $iconClass = 'file-text';
                                                    } else {
                                                        $iconClass = 'file';
                                                    }
                                                @endphp
                                                
                                                <div class="w-10 h-10 flex-none image-fit mr-2">
                                                    <div class="bg-primary/20 dark:bg-primary/10 rounded-full overflow-hidden">
                                                        <x-base.lucide class="w-6 h-6 text-primary mx-auto mt-2" icon="{{ $iconClass }}" />
                                                    </div>
                                                </div>
                                                <div>
                                                    @if(isset($document->source) && $document->source === 'media_library')
                                                        <a href="{{ route('admin.accidents.document.preview', $document->id) }}" 
                                                           class="font-medium whitespace-nowrap truncate max-w-[250px] inline-block" 
                                                           target="_blank" title="{{ $document->original_name }}">
                                                            {{ $document->original_name ?? $document->file_name }}
                                                        </a>
                                                    @else
                                                        <a href="{{ route('admin.accidents.document.preview', $document->id) }}" 
                                                           class="font-medium whitespace-nowrap truncate max-w-[250px] inline-block" 
                                                           target="_blank" title="{{ $document->original_name }}">
                                                            {{ $document->original_name ?? $document->file_name }}
                                                        </a>
                                                    @endif
                                                    <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">
                                                        {{ round($document->size / 1024, 2) }} KB · {{ strtoupper($extension) }}
                                                        @if(isset($document->source))
                                                            <span class="text-xs text-primary ml-1">
                                                                {{ $document->source === 'media_library' ? '(Media Library)' : '' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if(isset($document->documentable) && isset($document->documentable->userDriverDetail))
                                                <a href="{{ route('admin.drivers.show', $document->documentable->userDriverDetail->id) }}" class="font-medium whitespace-nowrap">
                                                    {{ $document->documentable->userDriverDetail->user->name }} {{ $document->documentable->userDriverDetail->user->last_name ?? '' }}
                                                </a>
                                            @else
                                                <span class="text-slate-500">No disponible</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($document->documentable) && isset($document->documentable->accident_date))
                                                <span>{{ $document->documentable->accident_date->format('d/m/Y') }}</span>
                                            @else
                                                <span class="text-slate-500">No disponible</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($document->documentable))
                                                <span>{{ Str::limit($document->documentable->nature_of_accident, 30) }}</span>
                                            @else
                                                <span class="text-slate-500">No disponible</span>
                                            @endif
                                        </td>
                                        <td class="table-report__action w-56">
                                            <div class="flex justify-center items-center">
                                                @if(isset($document->source) && $document->source === 'media_library')
                                                    <!-- Vista previa para archivos de Media Library -->
                                                    <a href="{{ route('admin.accidents.document.preview', $document->id) }}" class="btn btn-sm btn-primary mr-2" target="_blank">
                                                        <x-base.lucide class="w-4 h-4" icon="eye" />
                                                    </a>
                                                @else
                                                    <!-- Acciones para documentos del sistema antiguo -->
                                                    <a href="{{ route('admin.accidents.document.preview', $document->id) }}" class="btn btn-sm btn-primary mr-2" target="_blank">
                                                        <x-base.lucide class="w-4 h-4" icon="eye" />
                                                    </a>
                                                @endif
                                                
                                                <!-- Botón para ir a la edición del accidente -->
                                                @if(isset($document->accident_id))
                                                    <a href="{{ route('admin.accidents.edit', $document->accident_id) }}#documents" class="btn btn-sm btn-warning mr-2">
                                                        <x-base.lucide class="w-4 h-4" icon="clipboard-list" />
                                                    </a>
                                                @endif
                                                
                                                <!-- Botón para eliminar el documento -->
                                                @if(isset($document->source) && $document->source === 'media_library')
                                                    <!-- Eliminar archivo de Media Library -->
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deleteMedia('{{ $document->media_id }}', this)">
                                                        <x-base.lucide class="w-4 h-4" icon="trash" />
                                                    </button>
                                                @else
                                                    <!-- Eliminar documento del sistema antiguo -->
                                                    <form action="{{ route('admin.accidents.documents.destroy', $document->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este documento?')">
                                                            <x-base.lucide class="w-4 h-4" icon="trash" />
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="mt-5">
                        {{ $documents->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-base.lucide class="h-16 w-16 text-slate-300 mx-auto mb-2" icon="file-text" />
                        <h2 class="text-lg font-medium mt-2">No se encontraron documentos</h2>
                        <div class="text-slate-500 mt-1">Pruebe con diferentes filtros o revise los registros de accidentes.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal para Añadir Documento - Solo disponible cuando se ve un accidente específico -->
    @if(isset($accident))
    <x-base.dialog id="add-document-modal" size="md">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Add Document</h2>
            </x-base.dialog.title>

            <form action="{{ route('admin.accidents.update', $accident->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_driver_detail_id" value="{{ $accident->user_driver_detail_id }}">
                <input type="hidden" name="accident_date" value="{{ $accident->accident_date->format('Y-m-d') }}">
                <input type="hidden" name="nature_of_accident" value="{{ $accident->nature_of_accident }}">
                <input type="hidden" name="had_injuries" value="{{ $accident->had_injuries ? '1' : '0' }}">
                <input type="hidden" name="number_of_injuries" value="{{ $accident->number_of_injuries }}">
                <input type="hidden" name="had_fatalities" value="{{ $accident->had_fatalities ? '1' : '0' }}">
                <input type="hidden" name="number_of_fatalities" value="{{ $accident->number_of_fatalities }}">
                <input type="hidden" name="comments" value="{{ $accident->comments }}">

                <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label class="form-label">Upload Documents</label>
                        <div class="border-2 border-dashed rounded-md p-6 text-center">
                            <div class="mx-auto cursor-pointer relative">
                                <input type="file" name="documents[]" multiple
                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                    class="w-full h-full opacity-0 absolute inset-0 cursor-pointer z-50">
                                <div class="text-center">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-600">Drag and drop files here or click to browse</p>
                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, PDF, DOC, DOCX (Max 10MB each)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-base.dialog.description>

                <x-base.dialog.footer>
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="primary" class="w-20">
                        Upload
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>
    @endif
    
    <!-- Scripts para manejo de Media Library -->
    <script>
        function deleteMedia(mediaId, button) {
            if (!confirm('¿Está seguro de eliminar este archivo?')) {
                return;
            }
            
            // Mostrar indicador de carga
            const originalContent = button.innerHTML;
            button.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            button.disabled = true;
            
            // Realizar la solicitud AJAX para eliminar el archivo
            fetch('/api/documents/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    mediaId: mediaId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Eliminar la fila de la tabla
                    const row = button.closest('tr');
                    row.classList.add('bg-red-100');
                    setTimeout(() => {
                        row.style.transition = 'opacity 0.5s';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                        }, 500);
                    }, 300);
                } else {
                    alert('Error al eliminar el archivo: ' + (data.message || 'Error desconocido'));
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el archivo. Consulte la consola para más detalles.');
                button.innerHTML = originalContent;
                button.disabled = false;
            });
        }
    </script>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Previsualización de documentos al hacer clic
                const previewLinks = document.querySelectorAll('a[href*="document.preview"]');
                previewLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        // Solo para imágenes y PDFs en nuevas pestañas
                        if (this.getAttribute('target') === '_blank') {
                            return true; // Continuar normalmente
                        }

                        // Para otros documentos, preguntar si desea descargar
                        if (!confirm('¿Desea descargar este documento?')) {
                            e.preventDefault();
                            return false;
                        }
                    });
                });
            });
        </script>
    @endpush

@endsection