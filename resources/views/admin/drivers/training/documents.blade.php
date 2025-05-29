@extends('../themes/' . $activeTheme)
@section('title', 'All Training School Documents')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Training Schools', 'url' => route('admin.training-schools.index')],
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
                All Training School Documents
            </h2>
            <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                <a href="{{ route('admin.training-schools.index') }}" class="btn btn-outline-secondary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                    Back to Training Schools
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Filters</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.training-schools.documents') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-base.form-label for="school_filter">School</x-base.form-label>
                        <select id="school_filter" name="school" class="form-select">
                            <option value="">All Schools</option>
                            @foreach ($schools as $school)
                                <option value="{{ $school->id }}" {{ request()->query('school') == $school->id ? 'selected' : '' }}>
                                    {{ $school->school_name }}
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
                        <a href="{{ route('admin.training-schools.documents') }}" class="btn btn-outline-secondary">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="refresh-cw" />
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Documentos -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Documents ({{ $documents->total() }})</h3>
            </div>
            <div class="box-body p-0">
                @if ($documents->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">#</th>
                                    <th class="whitespace-nowrap">Document</th>
                                    <th class="whitespace-nowrap">Type</th>
                                    <th class="whitespace-nowrap">Size</th>
                                    <th class="whitespace-nowrap">School</th>
                                    <th class="whitespace-nowrap">Driver</th>
                                    <th class="whitespace-nowrap">Uploaded</th>
                                    <th class="whitespace-nowrap text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documents as $index => $document)
                                    <tr id="document-row-{{ $document->id }}">
                                        <td>{{ $loop->iteration + ($documents->currentPage() - 1) * $documents->perPage() }}</td>
                                        <td>
                                            <div class="flex items-center">
                                                @php
                                                    $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
                                                    $iconClass = 'file-text';
                                                    
                                                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                                        $iconClass = 'image';
                                                    } elseif (in_array($extension, ['doc', 'docx'])) {
                                                        $iconClass = 'file-text';
                                                    } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                        $iconClass = 'file-spreadsheet';
                                                    } elseif ($extension == 'pdf') {
                                                        $iconClass = 'file-text';
                                                    }
                                                @endphp
                                                
                                                <x-base.lucide class="w-5 h-5 mr-2 text-primary" icon="{{ $iconClass }}" />
                                                {{ $document->file_name }}
                                            </div>
                                        </td>
                                        <td>{{ strtoupper(pathinfo($document->file_name, PATHINFO_EXTENSION)) }}</td>
                                        <td>{{ $document->human_readable_size }}</td>
                                        <td>
                                            <a href="{{ route('admin.training-schools.show', $document->model->id) }}" class="text-primary hover:underline">
                                                {{ $document->model->school_name }}
                                            </a>
                                        </td>
                                        <td>
                                            {{ $document->model->driver->user->name }}
                                            {{ $document->model->driver->user->last_name ?? '' }}
                                        </td>
                                        <td>{{ $document->created_at->format('m/d/Y H:i') }}</td>
                                        <td>
                                            <div class="flex justify-center items-center">
                                                <a href="{{ route('admin.training-schools.preview.document', $document->id) }}" target="_blank" class="flex items-center text-primary mr-3" title="View">
                                                    <x-base.lucide class="w-4 h-4" icon="eye" />
                                                </a>
                                                <a href="{{ route('admin.training-schools.preview.document', ['id' => $document->id, 'download' => true]) }}" class="flex items-center text-info mr-3" title="Download">
                                                    <x-base.lucide class="w-4 h-4" icon="download" />
                                                </a>
                                                <button type="button" 
                                                    data-document-id="{{ $document->id }}"
                                                    class="flex items-center text-danger delete-document-btn" 
                                                    title="Delete">
                                                    <x-base.lucide class="w-4 h-4" icon="trash-2" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="p-5">
                        {{ $documents->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="p-10 text-center">
                        <div class="flex flex-col items-center justify-center py-8">
                            <x-base.lucide class="w-16 h-16 text-slate-300" icon="file-text" />
                            <div class="mt-5 text-slate-500">
                                No documents found matching your criteria.
                            </div>
                            <a href="{{ route('admin.training-schools.index') }}" class="btn btn-primary mt-5">
                                <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                                Back to Training Schools
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar documento -->
    <div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <x-base.lucide class="w-16 h-16 text-danger mx-auto mt-3" icon="x-circle" />
                        <div class="text-3xl mt-5">Are you sure?</div>
                        <div class="text-slate-500 mt-2">
                            Do you really want to delete this document? <br>
                            This process cannot be undone.
                        </div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <form id="delete-document-form" action="{{ route('admin.training-schools.ajax-destroy.document', 0) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" id="delete-document-id" name="id" value="">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Cancel</button>
                            <button type="submit" class="btn btn-danger w-24">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tom-select para selectores
            if (document.querySelector('#school_filter')) {
                new TomSelect('#school_filter', {
                    plugins: {
                        'dropdown_input': {}
                    }
                });
            }
            
            if (document.querySelector('#driver_filter')) {
                new TomSelect('#driver_filter', {
                    plugins: {
                        'dropdown_input': {}
                    }
                });
            }
            
            if (document.querySelector('#file_type')) {
                new TomSelect('#file_type');
            }
            
            // Configurar el modal de eliminación
            const modal = document.getElementById('delete-confirmation-modal');
            const deleteForm = document.getElementById('delete-document-form');
            const deleteIdInput = document.getElementById('delete-document-id');
            
            document.querySelectorAll('.delete-document-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const documentId = this.getAttribute('data-document-id');
                    deleteIdInput.value = documentId;
                    
                    // Actualizar la ruta del formulario
                    deleteForm.action = "{{ route('admin.training-schools.ajax-destroy.document', '') }}/" + documentId;
                    
                    // Mostrar modal
                    const instance = tailwind.Modal.getInstance(modal);
                    instance.show();
                });
            });
            
            // Manejar la eliminación AJAX
            deleteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const documentId = deleteIdInput.value;
                const formAction = this.action;
                const formData = new FormData(this);
                
                fetch(formAction, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Ocultar el modal
                        const instance = tailwind.Modal.getInstance(modal);
                        instance.hide();
                        
                        // Eliminar la fila de la tabla
                        const documentRow = document.getElementById('document-row-' + documentId);
                        if (documentRow) {
                            documentRow.remove();
                        }
                        
                        // Mostrar mensaje de éxito
                        const successAlert = `
                            <div class="alert alert-success flex items-center mb-5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                ${data.message}
                            </div>
                        `;
                        
                        document.querySelector('.box').insertAdjacentHTML('beforebegin', successAlert);
                        
                        // Eliminar el mensaje después de 5 segundos
                        setTimeout(() => {
                            const alertElement = document.querySelector('.alert');
                            if (alertElement) {
                                alertElement.remove();
                            }
                        }, 5000);
                    } else {
                        console.error('Error:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    </script>
@endpush
