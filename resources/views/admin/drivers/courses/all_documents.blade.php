@extends('../themes/' . $activeTheme)
@section('title', 'All Course Documents')
@php
    use Illuminate\Support\Facades\Storage;
    
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Courses', 'url' => route('admin.courses.index')],
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

        <!-- Título de la página -->
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium">
                All Course Documents
            </h2>
            <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                    Back to Courses
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Filters</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.courses.all-documents') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-base.form-label for="course_filter">Course</x-base.form-label>
                        <select id="course_filter" name="course" class="form-select">
                            <option value="">All Courses</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" {{ request()->query('course') == $course->id ? 'selected' : '' }}>
                                    {{ $course->organization_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <x-base.form-label for="driver_filter">Driver</x-base.form-label>
                        <select id="driver_filter" name="driver" class="form-select">
                            <option value="">All Drivers</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request()->query('driver') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }} {{ $driver->user->last_name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-base.form-label for="file_type">File Type</x-base.form-label>
                        <select id="file_type" name="file_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="pdf" {{ request()->query('file_type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                            <option value="image" {{ request()->query('file_type') == 'image' ? 'selected' : '' }}>Images</option>
                            <option value="doc" {{ request()->query('file_type') == 'doc' ? 'selected' : '' }}>Documents</option>
                        </select>
                    </div>

                    <div>
                        <x-base.form-label for="upload_date_from">Upload Date (From)</x-base.form-label>
                        <x-base.litepicker id="upload_date_from" name="upload_from" value="{{ request()->query('upload_from') }}" placeholder="MM/DD/YYYY" />
                    </div>

                    <div>
                        <x-base.form-label for="upload_date_to">Upload Date (To)</x-base.form-label>
                        <x-base.litepicker id="upload_date_to" name="upload_to" value="{{ request()->query('upload_to') }}" placeholder="MM/DD/YYYY" />
                    </div>

                    <div class="flex items-end">
                        <x-base.button type="submit" variant="primary" class="mr-2">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="search" />
                            Filter
                        </x-base.button>
                        <a href="{{ route('admin.courses.all-documents') }}" class="btn btn-outline-secondary">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="refresh-cw" />
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de documentos -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Documents</h3>
            </div>
            <div class="box-body p-5">
                @if($documents->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">#</th>
                                    <th class="whitespace-nowrap">Driver</th>
                                    <th class="whitespace-nowrap">Course</th>
                                    <th class="whitespace-nowrap">File Name</th>
                                    <th class="whitespace-nowrap">Type</th>
                                    <th class="whitespace-nowrap">Size</th>
                                    <th class="whitespace-nowrap">Upload Date</th>
                                    <th class="whitespace-nowrap text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $index => $document)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            @if($document->model && $document->model->driverDetail && $document->model->driverDetail->user)
                                                {{ $document->model->driverDetail->user->name }} {{ $document->model->driverDetail->user->last_name ?? '' }}
                                            @else
                                                <span class="text-gray-400">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($document->model)
                                                <a href="{{ route('admin.courses.edit', $document->model->id) }}" class="text-primary">
                                                    {{ $document->model->organization_name }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">Unknown</span>
                                            @endif
                                        </td>
                                        <td>{{ $document->file_name }}</td>
                                        <td>
                                            @php
                                                $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
                                                $iconClass = 'fa-file';
                                                
                                                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'])) {
                                                    $iconClass = 'fa-file-image';
                                                } elseif (in_array($extension, ['pdf'])) {
                                                    $iconClass = 'fa-file-pdf';
                                                } elseif (in_array($extension, ['doc', 'docx'])) {
                                                    $iconClass = 'fa-file-word';
                                                } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                    $iconClass = 'fa-file-excel';
                                                }
                                            @endphp
                                            <span class="flex items-center">
                                                <i class="fas {{ $iconClass }} mr-2"></i>
                                                {{ strtoupper($extension) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($document->size / 1024, 2) }} KB</td>
                                        <td>{{ $document->created_at->format('M d, Y H:i') }}</td>
                                        <td class="table-report__action">
                                            <div class="flex justify-center items-center">
                                                <a href="{{ route('admin.courses.documents.preview', $document->id) }}" 
                                                   class="flex items-center text-primary mr-3" 
                                                   target="_blank">
                                                    <x-base.lucide class="w-4 h-4 mr-1" icon="eye" />
                                                    View
                                                </a>
                                                <a href="#" 
                                                   class="flex items-center text-danger delete-document" 
                                                   data-document-id="{{ $document->id }}"
                                                   data-document-name="{{ $document->file_name }}">
                                                    <x-base.lucide class="w-4 h-4 mr-1" icon="trash-2" />
                                                    Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-5">
                        {{ $documents->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-500 mb-2">
                            <x-base.lucide class="w-12 h-12 mx-auto" icon="file-x" />
                        </div>
                        <h3 class="text-lg font-medium mt-2">No Documents Found</h3>
                        <p class="text-gray-500 mt-1">No documents match your search criteria.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar documento -->
    <x-base.dialog id="delete-confirmation-modal">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="w-16 h-16 text-danger mx-auto mt-3" icon="x-circle" />
                <div class="text-3xl mt-5">¿Estás seguro?</div>
                <div class="text-slate-500 mt-2" id="delete-confirmation-text">
                    ¿Realmente deseas eliminar este documento? Este proceso no se puede deshacer.
                </div>
            </div>
            <div class="px-5 pb-8 text-center">
                <form id="delete-document-form" method="POST" action="{{ route('api.documents.delete.post') }}">
                    @csrf
                    <input type="hidden" name="mediaId" id="media-id-field" value="">
                    <x-base.button type="button" data-tw-dismiss="modal" variant="outline-secondary" class="w-24 mr-1">Cancelar</x-base.button>
                    <x-base.button id="confirm-delete" type="button" variant="danger" class="w-24">Eliminar</x-base.button>
                </form>
            </div>
        </x-base.dialog.panel>
    </x-base.dialog>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar el modal de confirmación para eliminar documentos
        const deleteButtons = document.querySelectorAll('.delete-document');
        const deleteForm = document.getElementById('delete-document-form');
        const deleteConfirmationText = document.getElementById('delete-confirmation-text');
        const confirmDeleteBtn = document.getElementById('confirm-delete');
        const mediaIdField = document.getElementById('media-id-field');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const documentId = this.getAttribute('data-document-id');
                const documentName = this.getAttribute('data-document-name');
                
                // Actualizar el texto de confirmación con el nombre del documento
                deleteConfirmationText.textContent = `¿Realmente deseas eliminar el documento "${documentName}"? Este proceso no se puede deshacer.`;
                
                // Actualizar el campo oculto con el ID del documento
                mediaIdField.value = documentId;
                
                // Mostrar el modal usando el componente x-base.dialog
                const modal = tailwind.Modal.getOrCreateInstance(document.querySelector('#delete-confirmation-modal'));
                modal.show();
            });
        });
        
        // Manejar el clic en el botón de confirmación
        confirmDeleteBtn.addEventListener('click', function() {
            const formData = new FormData(deleteForm);
            
            fetch(deleteForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cerrar el modal
                    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector('#delete-confirmation-modal'));
                    modal.hide();
                    
                    // Mostrar mensaje de éxito
                    Toastify({
                        text: 'Documento eliminado correctamente',
                        duration: 3000,
                        close: true,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#10b981',
                        stopOnFocus: true
                    }).showToast();
                    
                    // Recargar la página después de un breve retraso
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Mostrar mensaje de error
                    Toastify({
                        text: data.message || 'Error al eliminar el documento',
                        duration: 3000,
                        close: true,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#ef4444',
                        stopOnFocus: true
                    }).showToast();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Toastify({
                    text: 'Error al procesar la solicitud',
                    duration: 3000,
                    close: true,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#ef4444',
                    stopOnFocus: true
                }).showToast();
            });
        });
    });
</script>
@endpush
