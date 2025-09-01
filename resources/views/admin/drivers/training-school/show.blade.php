@extends('../themes/' . $activeTheme)
@section('title', 'Training School Details')

@php
    use Illuminate\Support\Facades\Storage;
    
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Training Schools', 'url' => route('admin.training-schools.index')],
        ['label' => $school->school_name, 'url' => route('admin.training-schools.edit', $school->id)],
        ['label' => 'Documents', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="container mx-auto">
        <!-- Mensajes Flash -->
        @if (session()->has('success'))
            <div class="alert alert-success show mb-2" role="alert">
                <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger show mb-2" role="alert">
                <x-base.lucide class="w-6 h-6 mr-2" icon="alert-circle" />
                {{ session('error') }}
            </div>
        @endif

        <!-- Header profesional -->
        <div class="intro-y flex flex-col sm:flex-row items-center mt-8 mb-6 p-3">
            <h2 class="text-lg font-medium mr-auto flex items-center">
                <x-base.lucide class="w-6 h-6 mr-2 text-slate-600" icon="graduation-cap" />
                Training School Details
                <span class="text-slate-500 ml-2">- {{ $school->school_name }}</span>
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.training-schools.index') }}" class="btn btn-outline-secondary mr-2">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                    Back to Schools
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.training-schools.edit', $school->id) }}" class="btn btn-primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="edit" />
                    Edit School
                </x-base.button>
            </div>
        </div>

        <!-- Información de la escuela -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title flex items-center">
                    <x-base.lucide class="w-5 h-5 mr-2 text-slate-600" icon="info" />
                    Training School Details
                </h3>
            </div>
            <div class="box-body p-5">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Información básica -->
                    <div class="space-y-4">
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <x-base.lucide class="w-5 h-5 text-slate-600 mr-3" icon="user" />
                            <div>
                                <p class="text-sm font-medium text-slate-500">Driver</p>
                                <p class="font-semibold text-slate-800">{{ $school->userDriverDetail->user->name }} {{ $school->userDriverDetail->middle_name ? $school->userDriverDetail->middle_name . ' ' : '' }}{{ $school->userDriverDetail->last_name ?? '' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <x-base.lucide class="w-5 h-5 text-slate-600 mr-3" icon="phone" />
                            <div>
                                <p class="text-sm font-medium text-slate-500">Driver Phone</p>
                                <p class="font-semibold text-slate-800">{{ $school->userDriverDetail->phone ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <x-base.lucide class="w-5 h-5 text-slate-600 mr-3" icon="building" />
                            <div>
                                <p class="text-sm font-medium text-slate-500">School Name</p>
                                <p class="font-semibold text-slate-800">{{ $school->school_name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <x-base.lucide class="w-5 h-5 text-slate-600 mr-3" icon="map-pin" />
                            <div>
                                <p class="text-sm font-medium text-slate-500">Location</p>
                                <p class="font-semibold text-slate-800">{{ $school->city }}, {{ $school->state }}</p>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Fechas y estados -->
                    <div class="space-y-4">
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <x-base.lucide class="w-5 h-5 text-slate-600 mr-3" icon="calendar-days" />
                            <div>
                                <p class="text-sm font-medium text-slate-500">Start Date</p>
                                <p class="font-semibold text-slate-800">{{ $school->date_start ? date('m/d/Y', strtotime($school->date_start)) : '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <x-base.lucide class="w-5 h-5 text-slate-600 mr-3" icon="calendar-x" />
                            <div>
                                <p class="text-sm font-medium text-slate-500">End Date</p>
                                <p class="font-semibold text-slate-800">{{ $school->date_end ? date('m/d/Y', strtotime($school->date_end)) : '-' }}</p>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <p class="text-sm font-medium text-slate-500 mb-2">Graduated</p>
                            @if ($school->graduated)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success text-white">
                                    <x-base.lucide class="w-4 h-4 mr-1" icon="check-circle" />
                                    Graduated
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-warning text-white">
                                    <x-base.lucide class="w-4 h-4 mr-1" icon="clock" />
                                    In Progress
                                </span>
                            @endif
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <p class="text-sm font-medium text-slate-500 mb-2">Safety Regulations</p>
                            @if ($school->subject_to_safety_regulations)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary text-white">
                                    <x-base.lucide class="w-4 h-4 mr-1" icon="shield-check" />
                                    Subject to Regulations
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-500 text-white">
                                    <x-base.lucide class="w-4 h-4 mr-1" icon="shield-x" />
                                    Not Subject
                                </span>
                            @endif
                        </div>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <p class="text-sm font-medium text-slate-500 mb-2">Safety Functions</p>
                            @if ($school->performed_safety_functions)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-info text-white">
                                    <x-base.lucide class="w-4 h-4 mr-1" icon="check-circle-2" />
                                    Functions Performed
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-danger text-white">
                                    <x-base.lucide class="w-4 h-4 mr-1" icon="x-circle" />
                                    Not Performed
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Training Skills -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title flex items-center">
                    <x-base.lucide class="w-5 h-5 mr-2 text-slate-600" icon="award" />
                    Training Skills
                    @php
                        $skills = $school->training_skills ?? [];
                        if (is_string($skills)) {
                            $skills = json_decode($skills, true) ?? [];
                        }
                    @endphp
                    @if($skills && count($skills) > 0)
                        <span class="ml-2 bg-primary text-white px-2 py-1 rounded-full text-xs font-medium">
                            {{ count($skills) }}
                        </span>
                    @endif
                </h3>
            </div>
            <div class="box-body p-5">
                @if($skills && count($skills) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($skills as $skill)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success text-white">
                                <x-base.lucide class="w-4 h-4 mr-1" icon="check-circle" />
                                {{ $skill }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="bg-slate-100 rounded-full p-3 mx-auto mb-3 w-12 h-12 flex items-center justify-center">
                            <x-base.lucide class="w-6 h-6 text-slate-500" icon="alert-circle" />
                        </div>
                        <p class="text-slate-600 font-medium">No training skills specified</p>
                        <p class="text-sm text-slate-500 mt-1">Skills will appear here once they are added to the training record.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Documentos -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title flex items-center">
                    <x-base.lucide class="w-5 h-5 mr-2 text-slate-600" icon="file-text" />
                    Documents
                    @php
                        $documents = \Spatie\MediaLibrary\MediaCollections\Models\Media::where('model_type', \App\Models\Admin\Driver\DriverTrainingSchool::class)
                            ->where('model_id', $school->id)
                            ->where('collection_name', 'school_certificates')
                            ->get();
                    @endphp
                    @if (count($documents) > 0)
                        <span class="ml-2 bg-info text-white px-2 py-1 rounded-full text-xs font-medium">
                            {{ count($documents) }}
                        </span>
                    @endif
                </h3>
            </div>
            <div class="box-body p-0">
                @if (count($documents) > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-striped w-full">
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
                                                    
                                                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                                                        $iconClass = 'image';
                                                    } elseif (in_array($extension, ['pdf'])) {
                                                        $iconClass = 'file-text';
                                                    } elseif (in_array($extension, ['doc', 'docx'])) {
                                                        $iconClass = 'file';
                                                    } elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
                                                        $iconClass = 'file-spreadsheet';
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
                                                <a href="{{ route('admin.training-schools.docs.preview', $document->id) }}" target="_blank" class="flex items-center text-primary mr-3" title="View">
                                                    <x-base.lucide class="w-4 h-4" icon="eye" />
                                                </a>
                                                <a href="{{ route('admin.training-schools.docs.preview', $document->id) }}?download=true" class="flex items-center text-info mr-3" title="Download">
                                                    <x-base.lucide class="w-4 h-4" icon="download" />
                                                </a>
                                                <button type="button" 
                                                    data-tw-toggle="modal"
                                                    data-tw-target="#delete-document-modal-{{ $document->id }}"
                                                    class="flex items-center text-danger" 
                                                    title="Delete">
                                                    <x-base.lucide class="w-4 h-4" icon="trash-2" />
                                                </button>
                                                
                                                <!-- Modal Eliminar Documento para cada registro -->
                                                <x-base.dialog id="delete-document-modal-{{ $document->id }}" size="md">
                                                    <x-base.dialog.panel>
                                                        <div class="p-5 text-center">
                                                            <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="x-circle" />
                                                            <div class="mt-5 text-2xl">Are you sure?</div>
                                                            <div class="mt-2 text-slate-500">
                                                                Do you really want to delete this document? <br>
                                                                This process cannot be undone.
                                                            </div>
                                                        </div>
                                                        <form action="{{ route('admin.training-schools.docs.delete', $document->id) }}" method="POST" class="px-5 pb-8 text-center">
                                                            @csrf
                                                            @method('DELETE')
                                                            <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-24">
                                                                Cancel
                                                            </x-base.button>
                                                            <x-base.button type="submit" variant="danger" class="w-24">
                                                                Delete
                                                            </x-base.button>
                                                        </form>
                                                    </x-base.dialog.panel>
                                                </x-base.dialog>
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

@endsection
