@extends('../themes/' . $activeTheme)
@section('title', 'Training School Documents')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Training Schools', 'url' => route('admin.training-schools.index')],
        ['label' => $school->school_name, 'url' => route('admin.training-schools.edit', $school->id)],
        ['label' => 'Documents', 'active' => true],
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
                Documents for {{ $school->school_name }}
            </h2>
            <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                <a href="{{ route('admin.training-schools.index') }}" class="btn btn-outline-secondary mr-2">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="arrow-left" />
                    Back to Training Schools
                </a>
                <a href="{{ route('admin.training-schools.edit', $school->id) }}" class="btn btn-outline-primary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="edit" />
                    Edit Training School
                </a>
            </div>
        </div>

        <!-- Información de la escuela -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Training School Details</h3>
            </div>
            <div class="box-body p-5">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th class="whitespace-nowrap w-40">Driver</th>
                                    <td>{{ $school->driver->user->name }} {{ $school->driver->user->last_name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th class="whitespace-nowrap">School Name</th>
                                    <td>{{ $school->school_name }}</td>
                                </tr>
                                <tr>
                                    <th class="whitespace-nowrap">Location</th>
                                    <td>{{ $school->city }}, {{ $school->state }}</td>
                                </tr>
                                <tr>
                                    <th class="whitespace-nowrap">Phone</th>
                                    <td>{{ $school->phone_number }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th class="whitespace-nowrap w-40">Start Date</th>
                                    <td>{{ $school->date_start ? date('m/d/Y', strtotime($school->date_start)) : '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="whitespace-nowrap">End Date</th>
                                    <td>{{ $school->date_end ? date('m/d/Y', strtotime($school->date_end)) : '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="whitespace-nowrap">Graduated</th>
                                    <td>
                                        @if ($school->graduated)
                                            <span class="text-success flex items-center">
                                                <x-base.lucide class="w-4 h-4 mr-1" icon="check-circle" />
                                                Yes
                                            </span>
                                        @else
                                            <span class="text-warning flex items-center">
                                                <x-base.lucide class="w-4 h-4 mr-1" icon="clock" />
                                                Not yet
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="whitespace-nowrap">Safety Regulations</th>
                                    <td>
                                        @if ($school->subject_to_safety_regulations)
                                            <span class="text-success flex items-center">
                                                <x-base.lucide class="w-4 h-4 mr-1" icon="check-circle" />
                                                Subject to Safety Regulations
                                            </span>
                                        @else
                                            <span class="text-danger flex items-center">
                                                <x-base.lucide class="w-4 h-4 mr-1" icon="x-circle" />
                                                Not Subject to Safety Regulations
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="whitespace-nowrap">Safety Functions</th>
                                    <td>
                                        @if ($school->performed_safety_functions)
                                            <span class="text-success flex items-center">
                                                <x-base.lucide class="w-4 h-4 mr-1" icon="check-circle" />
                                                Performed Safety Functions
                                            </span>
                                        @else
                                            <span class="text-danger flex items-center">
                                                <x-base.lucide class="w-4 h-4 mr-1" icon="x-circle" />
                                                Did Not Perform Safety Functions
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Habilidades de entrenamiento -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Training Skills</h3>
            </div>
            <div class="box-body p-5">
                @php
                    $trainingSkills = $school->training_skills ?? [];
                    if (is_string($trainingSkills)) {
                        $trainingSkills = json_decode($trainingSkills, true) ?? [];
                    }
                @endphp
                
                @if (count($trainingSkills) > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($trainingSkills as $skill)
                            <div class="flex items-center">
                                <x-base.lucide class="w-4 h-4 mr-2 text-success" icon="check" />
                                <span>{{ ucfirst($skill) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-slate-500">No training skills specified.</div>
                @endif
            </div>
        </div>

        <!-- Documentos -->
        <div class="box box--stacked mt-5">
            <div class="box-header">
                <h3 class="box-title">Documents</h3>
            </div>
            <div class="box-body p-0">
                @php
                    $documents = $school->getMedia('training_files');
                @endphp
                
                @if (count($documents) > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">#</th>
                                    <th class="whitespace-nowrap">Name</th>
                                    <th class="whitespace-nowrap">Type</th>
                                    <th class="whitespace-nowrap">Size</th>
                                    <th class="whitespace-nowrap">Uploaded</th>
                                    <th class="whitespace-nowrap text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($documents as $index => $document)
                                    <tr id="document-row-{{ $document->id }}">
                                        <td>{{ $index + 1 }}</td>
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
                @else
                    <div class="p-10 text-center">
                        <div class="flex flex-col items-center justify-center py-8">
                            <x-base.lucide class="w-16 h-16 text-slate-300" icon="file-text" />
                            <div class="mt-5 text-slate-500">
                                No documents uploaded for this training school.
                            </div>
                            <a href="{{ route('admin.training-schools.edit', $school->id) }}" class="btn btn-primary mt-5">
                                <x-base.lucide class="w-4 h-4 mr-1" icon="upload" />
                                Upload Documents
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
                        <form id="delete-document-form" action="{{ route('admin.training-schools.destroy.document', 0) }}" method="POST" style="display: inline-block;">
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
            const modal = document.getElementById('delete-confirmation-modal');
            const deleteForm = document.getElementById('delete-document-form');
            const deleteIdInput = document.getElementById('delete-document-id');
            
            document.querySelectorAll('.delete-document-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const documentId = this.getAttribute('data-document-id');
                    deleteIdInput.value = documentId;
                    
                    // Actualizar la ruta del formulario
                    deleteForm.action = "{{ route('admin.training-schools.destroy.document', '') }}/" + documentId;
                    
                    // Mostrar modal
                    const instance = tailwind.Modal.getInstance(modal);
                    instance.show();
                });
            });
        });
    </script>
@endpush
