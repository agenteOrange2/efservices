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
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title">Filter Documents</h3>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.accidents.documents.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    @if (request()->has('accident_id'))
                        <input type="hidden" name="accident_id" value="{{ request('accident_id') }}">
                    @endif

                    <!-- Carrier Filter -->
                    <div>
                        <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                        <select id="carrier_id" name="carrier_id" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Carriers</option>
                            @foreach ($carriers ?? [] as $carrier)
                                <option value="{{ $carrier->id }}"
                                    {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Driver Filter -->
                    <div>
                        <x-base.form-label for="driver_id">Driver</x-base.form-label>
                        <select id="driver_id" name="driver_id" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Drivers</option>
                            @foreach ($drivers ?? [] as $driver)
                                <option value="{{ $driver->id }}"
                                    {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }} {{ $driver->user->last_name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Range Filters -->
                    <div>
                        <x-base.form-label for="start_date">Start Date</x-base.form-label>
                        <x-base.litepicker id="start_date" name="start_date" class="w-full"
                            value="{{ request('start_date') }}" data-format="MM-DD-YYYY" placeholder="MM-DD-YYYY" />

                    </div>

                    <div>
                        <x-base.form-label for="end_date">End Date</x-base.form-label>
                        <x-base.litepicker id="end_date" name="end_date" class="w-full" value="{{ request('end_date') }}"
                            data-format="MM-DD-YYYY" placeholder="MM-DD-YYYY" />

                    </div>

                    <!-- File Type Filter -->
                    <div>
                        <x-base.form-label for="file_type">File Type</x-base.form-label>
                        <select id="file_type" name="file_type" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Types</option>
                            <option value="image" {{ request('file_type') == 'image' ? 'selected' : '' }}>Images</option>
                            <option value="pdf" {{ request('file_type') == 'pdf' ? 'selected' : '' }}>PDFs</option>
                            <option value="document" {{ request('file_type') == 'document' ? 'selected' : '' }}>Documents
                            </option>
                        </select>
                    </div>

                    <!-- Submit Button -->
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
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <h3 class="box-title">Documents ({{ $documents->count() ?? 0 }})</h3>
                </div>
            </div>
            <div class="box-body ">
                @if ($documents->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-report mt-2 w-full">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">Date Created</th>
                                    <th class="whitespace-nowrap">Carrier</th>
                                    <th class="whitespace-nowrap">Driver</th>
                                    <th class="whitespace-nowrap">Accident Date</th>
                                    <th class="whitespace-nowrap">Nature of Accident</th>
                                    <th class="whitespace-nowrap">Documents</th>
                                    <th class="whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documents as $document)
                                    <tr class="intro-x" data-media-id="{{ $document->id }}">
                                        <td>
                                            @if (isset($document->created_at))
                                                <span>{{ is_string($document->created_at) ? date('m/d/Y', strtotime($document->created_at)) : $document->created_at->format('d/m/Y H:i') }}</span>
                                            @else
                                                <span class="text-slate-500">No disponible</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($document->carrier_name) && !empty($document->carrier_name))
                                                <span
                                                    class="font-medium whitespace-nowrap">{{ $document->carrier_name }}</span>
                                            @else
                                                <span class="text-slate-500">No disponible</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($document->driver) && !empty($document->driver))
                                                <a href="{{ route('admin.drivers.show', $document->driver_id) }}"
                                                    class="font-medium whitespace-nowrap">
                                                    {{ $document->driver }}
                                                </a>
                                            @elseif(isset($document->documentable) && isset($document->documentable->userDriverDetail))
                                                <a href="{{ route('admin.drivers.show', $document->documentable->userDriverDetail->id) }}"
                                                    class="font-medium whitespace-nowrap">
                                                    {{ $document->documentable->userDriverDetail->user->name }}
                                                    {{ $document->documentable->userDriverDetail->user->last_name ?? '' }}
                                                </a>
                                            @else
                                                <span class="text-slate-500">No disponible</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($document->accident_date))
                                                <span>{{ is_string($document->accident_date) ? date('d/m/Y', strtotime($document->accident_date)) : $document->accident_date->format('d/m/Y') }}</span>
                                            @elseif(isset($document->documentable) && isset($document->documentable->accident_date))
                                                <span>{{ $document->documentable->accident_date->format('d/m/Y') }}</span>
                                            @else
                                                <span class="text-slate-500">No disponible</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if (isset($document->nature))
                                                <span>{{ Str::limit($document->nature, 30) }}</span>
                                            @elseif(isset($document->documentable))
                                                <span>{{ Str::limit($document->documentable->nature_of_accident, 30) }}</span>
                                            @else
                                                <span class="text-slate-500">No disponible</span>
                                            @endif
                                        </td>
                                        <td class="w-40">
                                            <div class="flex items-center">
                                                @php
                                                    $iconClass = '';
                                                    $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);

                                                    if (
                                                        in_array(strtolower($extension), [
                                                            'jpg',
                                                            'jpeg',
                                                            'png',
                                                            'gif',
                                                            'bmp',
                                                            'svg',
                                                            'webp',
                                                        ])
                                                    ) {
                                                        $iconClass = 'image';
                                                    } elseif (strtolower($extension) === 'pdf') {
                                                        $iconClass = 'file-text';
                                                    } else {
                                                        $iconClass = 'file';
                                                    }
                                                @endphp

                                                <div class="w-10 h-10 flex-none image-fit mr-2">
                                                    <div
                                                        class="bg-primary/20 dark:bg-primary/10 rounded-full overflow-hidden">
                                                        <x-base.lucide class="w-6 h-6 text-primary mx-auto mt-2"
                                                            icon="{{ $iconClass }}" />
                                                    </div>
                                                </div>
                                                <div>
                                                    @if (isset($document->source) && $document->source === 'media_library')
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
                                                        {{ round($document->size / 1024, 2) }} KB ·
                                                        {{ strtoupper($extension) }}
                                                        @if (isset($document->source))
                                                            <span class="text-xs text-primary ml-1">
                                                                {{ $document->source === 'media_library' ? '(Media Library)' : '' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="table-report__action w-56">
                                            <div class="flex justify-center items-center">
                                                @if (isset($document->source) && $document->source === 'media_library')
                                                    <!-- Vista previa para archivos de Media Library -->
                                                    <a href="{{ route('admin.accidents.document.preview', $document->id) }}"
                                                        class="btn btn-sm btn-primary mr-2" target="_blank">
                                                        <x-base.lucide class="w-4 h-4" icon="eye" />
                                                    </a>
                                                @else
                                                    <!-- Acciones para documentos del sistema antiguo -->
                                                    <a href="{{ route('admin.accidents.document.preview', $document->id) }}"
                                                        class="btn btn-sm btn-primary mr-2" target="_blank">
                                                        <x-base.lucide class="w-4 h-4" icon="eye" />
                                                    </a>
                                                @endif

                                                <!-- Botón para ir a la edición del accidente -->
                                                @if (isset($document->accident_id))
                                                    <a href="{{ route('admin.accidents.edit', $document->accident_id) }}#documents"
                                                        class="btn btn-sm btn-warning mr-2">
                                                        <x-base.lucide class="w-4 h-4" icon="clipboard-list" />
                                                    </a>
                                                @endif

                                                <!-- Botón para eliminar el documento -->
                                                @if (isset($document->source) && $document->source === 'media_library')
                                                    <!-- Eliminar archivo de Media Library -->
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="deleteMedia('{{ $document->id }}', this)">
                                                        <x-base.lucide class="w-4 h-4" icon="trash-2" />
                                                    </button>
                                                @else
                                                    <!-- Eliminar documento del sistema antiguo -->
                                                    <form
                                                        action="{{ route('admin.accidents.documents.destroy', $document->id) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('¿Está seguro de eliminar este documento?')">
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
                        <div class="text-slate-500 mt-1">Pruebe con diferentes filtros o revise los registros de
                            accidentes.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal para Añadir Documento - Solo disponible cuando se ve un accidente específico -->
    @if (isset($accident))
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
                        <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary"
                            class="mr-1 w-20">
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
    <!-- Modal de confirmación para eliminar documento -->
    <x-base.dialog id="delete-confirmation-modal">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="w-16 h-16 text-danger mx-auto mt-3" icon="x-circle" />
                <div class="text-3xl mt-5">¿Estás seguro?</div>
                <div class="text-slate-500 mt-2">¿Realmente deseas eliminar este documento? <br>Esta acción no se puede
                    deshacer.</div>
            </div>
            <div class="px-5 pb-8 text-center">
                <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary"
                    class="w-24 mr-1">Cancelar</x-base.button>
                <x-base.button id="confirm-delete" type="button" variant="danger"
                    class="w-24">Eliminar</x-base.button>
            </div>
        </x-base.dialog.panel>
    </x-base.dialog>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Variable para almacenar el ID del documento a eliminar
                let mediaIdToDelete = null;
                let rowToDelete = null;
                const confirmDeleteButton = document.getElementById('confirm-delete');

                // Inicializar el modal de confirmación
                const deleteModal = document.getElementById('delete-confirmation-modal');

                // Función global para eliminar un documento de Media Library
                window.deleteMedia = function(mediaId, button) {
                    // Guardar el ID del documento a eliminar para usarlo en la confirmación
                    mediaIdToDelete = mediaId;
                    rowToDelete = button.closest('tr');

                    // Mostrar información de depuración
                    //console.log('ID del documento a eliminar:', mediaId);

                    // Mostrar el modal usando la API de Tailwind
                    const modal = tailwind.Modal.getOrCreateInstance(deleteModal);
                    modal.show();
                };

                // Configurar el evento para el botón de confirmación
                if (confirmDeleteButton) {
                    confirmDeleteButton.addEventListener('click', function() {
                        if (!mediaIdToDelete) return;

                        // Mostrar indicador de carga
                        confirmDeleteButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Eliminando...';
                        confirmDeleteButton.disabled = true;

                        // Extraer el ID numérico si es necesario
                        let mediaIdForDelete = mediaIdToDelete;
                        if (typeof mediaIdToDelete === 'string' && mediaIdToDelete.startsWith('media_')) {
                            mediaIdForDelete = mediaIdToDelete.replace('media_', '');
                        }

                        // Usar el endpoint de API para eliminar documentos de manera segura
                        const apiUrl = '{{ url('/api/documents/delete') }}';
                        /*
                        console.log('ID original:', mediaIdToDelete);
                        console.log('ID para eliminar:', mediaIdForDelete);
                        console.log('URL API:', apiUrl);
                        */

                        // Crear los datos para la solicitud POST
                        const formData = new FormData();
                        formData.append('mediaId', mediaIdForDelete);
                        formData.append('_token', '{{ csrf_token() }}');

                        // Realizar la solicitud AJAX para eliminar el documento usando el endpoint de API
                        fetch(apiUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: formData,
                                credentials: 'same-origin' // Asegurar que se envíen las cookies
                            })
                            .then(response => {
                                //console.log('Respuesta del servidor:', response);
                                // Verificar si la respuesta es exitosa
                                if (!response.ok) {
                                    throw new Error(
                                        `Error HTTP: ${response.status} ${response.statusText}`);
                                }
                                return response.json();
                            })
                            .then(data => {
                                //console.log('Datos recibidos:', data);
                                // Cerrar el modal
                                const modal = tailwind.Modal.getOrCreateInstance(deleteModal);
                                modal.hide();

                                // Restablecer el estado del botón de confirmación
                                confirmDeleteButton.innerHTML = 'Eliminar';
                                confirmDeleteButton.disabled = false;

                                if (data.success) {
                                    // Mostrar mensaje de éxito
                                    Toastify({
                                        text: "Documento eliminado correctamente",
                                        duration: 3000,
                                        close: true,
                                        gravity: "top",
                                        position: "right",
                                        style: {
                                            background: "#10b981",
                                        },
                                    }).showToast();

                                    // Eliminar la fila de la tabla
                                    const row = document.querySelector(
                                        `tr[data-media-id="${mediaIdToDelete}"]`);
                                    if (row) {
                                        row.classList.add('bg-red-100');
                                        setTimeout(() => {
                                            row.style.transition = 'opacity 0.5s';
                                            row.style.opacity = '0';
                                            setTimeout(() => {
                                                row.remove();
                                            }, 500);
                                        }, 300);
                                    } else {
                                        // Si no se encuentra la fila, recargar la página
                                        setTimeout(() => {
                                            window.location.reload();
                                        }, 1000);
                                    }
                                } else {
                                    // Mostrar mensaje de error
                                    Toastify({
                                        text: data.error || "Error al eliminar el documento",
                                        duration: 3000,
                                        close: true,
                                        gravity: "top",
                                        position: "right",
                                        backgroundColor: "#ef4444",
                                    }).showToast();
                                }
                            })
                            .catch(error => {
                                console.error('Error al eliminar documento:', error);

                                // Cerrar el modal
                                const modal = tailwind.Modal.getOrCreateInstance(deleteModal);
                                modal.hide();

                                // Restablecer el estado del botón de confirmación
                                confirmDeleteButton.innerHTML = 'Eliminar';
                                confirmDeleteButton.disabled = false;

                                // Mostrar mensaje de error
                                Toastify({
                                    text: "Error al procesar la solicitud",
                                    duration: 3000,
                                    close: true,
                                    gravity: "top",
                                    position: "right",
                                    style: {
                                        background: "#ef4444",
                                    },
                                }).showToast();
                            });
                    });
                }
            });
        </script>
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

