@extends('../themes/' . $activeTheme)
@section('title', 'All Training School Documents')
@php
    use Illuminate\Support\Facades\Storage;
    
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
                <h3 class="box-title">Documents ({{ $documents->count() }})</h3>
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
                                            <a href="{{ route('admin.training-schools.show', $document->documentable->id) }}" class="text-primary hover:underline">
                                                {{ $document->documentable->school_name }}
                                            </a>
                                        </td>
                                        <td>
                                            {{ $document->documentable->userDriverDetail->user->name }}
                                            {{ $document->documentable->userDriverDetail->user->last_name ?? '' }}
                                        </td>
                                        <td>{{ $document->created_at->format('m/d/Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="flex justify-center">
                                                <a href="{{ Storage::url($document->file_path) }}" class="btn btn-sm btn-primary mr-2" target="_blank">
                                                    <x-base.lucide class="w-4 h-4" icon="eye" />
                                                </a>
                                                <a href="{{ route('admin.training-schools.edit', $document->documentable_id) }}" class="btn btn-sm btn-warning mr-2">
                                                    <x-base.lucide class="w-4 h-4" icon="clipboard-list" />
                                                </a>
                                                <form action="{{ route('admin.training-schools.documents.delete', $document->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este documento?')">
                                                        <x-base.lucide class="w-4 h-4" icon="trash" />
                                                    </button>
                                                </form>
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
            
            // Inicializar tom-select para selectores que quedan
        });
    </script>
@endpush

@endsection