@extends('../themes/' . $activeTheme)
@section('title', 'Driver Course History')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Driver Courses', 'url' => route('admin.courses.index')],
    ['label' => 'Driver Course History', 'active' => true],
];
@endphp
@section('subcontent')
<div>
    <!-- Mensajes Flash -->
    @if(session()->has('success'))
    <div class="alert alert-success flex items-center mb-5">
        <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
        {{ session('success') }}
    </div>
    @endif

    <!-- Cabecera -->
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Course History for {{ $driver->user->name }} {{ $driver->last_name }}
        </h2>
        <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
            <x-base.button as="a" href="{{ route('admin.drivers.show', $driver->id) }}"
                class="w-full sm:w-auto" variant="outline-primary">
                <x-base.lucide class="w-4 h-4 mr-2" icon="user" />
                Driver Profile
            </x-base.button>
            <x-base.button as="a" href="{{ route('admin.courses.index') }}"
                class="w-full sm:w-auto" variant="primary">
                <x-base.lucide class="w-4 h-4 mr-2" icon="list" />
                All Courses
            </x-base.button>
        </div>
    </div>

    <!-- Info del Conductor -->
    <div class="box box--stacked p-5 mt-5">
        <div class="flex flex-col md:flex-row items-center">
            <div class="w-24 h-24 md:w-16 md:h-16 rounded-full overflow-hidden mr-5 mb-4 md:mb-0">
                @if ($driver->getFirstMediaUrl('profile_photo_driver'))
                <img src="{{ $driver->getFirstMediaUrl('profile_photo_driver') }}" alt="{{ $driver->user->name }}"
                    class="w-full h-full object-cover">
                @else
                <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-500">
                    <x-base.lucide class="h-8 w-8" icon="user" />
                </div>
                @endif
            </div>
            <div class="text-center md:text-left md:mr-auto">
                <div class="text-lg font-medium">{{ $driver->user->name }} {{ $driver->last_name }}</div>
                <div class="text-gray-500">{{ $driver->phone }}</div>
                <div class="text-gray-500">{{ $driver->carrier->name }}</div>
            </div>
            <div class="mt-4 md:mt-0">
                <div class="flex items-center">
                    <div class="text-gray-500 mr-2">Total Courses:</div>
                    <div class="text-lg font-medium">{{ $courses->total() }}</div>
                </div>
                @if ($courses->count() > 0)
                <div class="flex items-center mt-1">
                    <div class="text-gray-500 mr-2">Last Course:</div>
                    <div class="text-blue-600">
                        {{ $courses->first()->certification_date->format('M d, Y') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Cabecera y Búsqueda -->
    <div class="flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Driver Course Records
        </h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <form action="{{ route('admin.drivers.course-history', $driver->id) }}" method="GET"
                class="mr-2 flex gap-2">
                <div class="relative">
                    <x-base.lucide
                        class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                        icon="Search" />
                    <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search_term"
                        type="text" placeholder="Search by organization..."
                        value="{{ request('search_term') }}" />
                </div>
                
                <select name="status" class="mr-2 text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                
                <x-base.button type="submit" variant="outline-primary">
                    <x-base.lucide class="w-4 h-4 mr-1" icon="filter" />
                    Filter
                </x-base.button>
            </form>
            
            <x-base.button as="a" href="{{ route('admin.courses.create') }}"
                variant="primary" class="flex items-center">
                <x-base.lucide class="h-4 w-4 mr-2" icon="plus" />
                Add Course
            </x-base.button>
        </div>
    </div>

    <!-- Tabla de Cursos -->
    <div class="box box--stacked p-5 mt-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead>
                    <tr class="bg-slate-50/60">
                        <th scope="col" class="px-6 py-3">
                            <a href="{{ route('admin.drivers.course-history', [
                                'driver' => $driver->id,
                                'sort' => 'certification_date',
                                'direction' => request('sort') === 'certification_date' && request('direction') === 'asc' ? 'desc' : 'asc',
                                'search_term' => request('search_term'),
                                'status' => request('status'),
                            ]) }}" class="flex items-center">
                                Certification Date
                                @if (request('sort') === 'certification_date')
                                    <x-base.lucide class="w-4 h-4 ml-1" 
                                        icon="{{ request('direction') === 'asc' ? 'ArrowUp' : 'ArrowDown' }}" />
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">Organization</th>
                        <th scope="col" class="px-6 py-3">Expiration Date</th>
                        <th scope="col" class="px-6 py-3">
                            <a href="{{ route('admin.drivers.course-history', [
                                'driver' => $driver->id,
                                'sort' => 'status',
                                'direction' => request('sort') === 'status' && request('direction') === 'asc' ? 'desc' : 'asc',
                                'search_term' => request('search_term'),
                                'status' => request('status'),
                            ]) }}" class="flex items-center">
                                Status
                                @if (request('sort') === 'status')
                                    <x-base.lucide class="w-4 h-4 ml-1" 
                                        icon="{{ request('direction') === 'asc' ? 'ArrowUp' : 'ArrowDown' }}" />
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">Certificates</th>
                        <th scope="col" class="px-6 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($courses as $course)
                        <tr class="border-b border-slate-200/60 hover:bg-slate-50 dark:hover:bg-slate-900/20">
                            <td class="px-6 py-4">
                                {{ $course->certification_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $course->organization_name }}
                                <div class="text-xs text-gray-500">
                                    {{ $course->city }}, {{ $course->state }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                {{ $course->expiration_date->format('M d, Y') }}
                                @php
                                    $daysLeft = now()->diffInDays($course->expiration_date, false);
                                @endphp
                                
                                @if ($daysLeft < 0)
                                    <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Expired
                                    </span>
                                @elseif ($daysLeft <= 30)
                                    <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $daysLeft }} days left
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    {{ $course->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($course->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($course->getMedia('certificates')->count() > 0)
                                    <button type="button" class="text-blue-600 hover:text-blue-900 view-certificates" 
                                        data-course-id="{{ $course->id }}">
                                        <div class="flex items-center">
                                            <x-base.lucide class="w-4 h-4 mr-1" icon="file-text" />
                                            {{ $course->getMedia('certificates')->count() }} 
                                            {{ Str::plural('Certificate', $course->getMedia('certificates')->count()) }}
                                        </div>
                                    </button>
                                @else
                                    <span class="text-gray-500">No certificates</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('admin.courses.edit', $course->id) }}" 
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                        <x-base.lucide class="h-4 w-4" icon="edit" />
                                    </a>
                                    <button type="button" class="text-red-600 hover:text-red-900 delete-course" 
                                        data-course-id="{{ $course->id }}" data-bs-toggle="modal" data-bs-target="#delete-confirmation-modal">
                                        <x-base.lucide class="h-4 w-4" icon="trash" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center">
                                No course records found for this driver.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="mt-5">
            {{ $courses->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Modal de Visualización de Certificados -->
<div id="certificates-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Course Certificates</h2>
                <button class="btn btn-outline-secondary hidden sm:flex">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="file-text" /> Download All
                </button>
                <button class="btn btn-outline-secondary ml-3" data-tw-dismiss="modal">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="x" /> Close
                </button>
            </div>
            <div class="modal-body p-5">
                <div id="certificates-container" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Los certificados se cargarán aquí dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <x-base.lucide class="w-16 h-16 text-danger mx-auto mt-3" icon="x-circle" />
                    <div class="text-3xl mt-5">Are you sure?</div>
                    <div class="text-slate-500 mt-2">
                        Do you really want to delete this course record? <br>
                        This process cannot be undone.
                    </div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <form id="delete_course_form" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger w-24">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuración del modal de eliminación
        const deleteButtons = document.querySelectorAll('.delete-course');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const courseId = this.getAttribute('data-course-id');
                document.getElementById('delete_course_form').action = 
                    `/admin/courses/${courseId}`;
            });
        });

        // Configuración del modal de certificados
        const certificateButtons = document.querySelectorAll('.view-certificates');
        const certificatesContainer = document.getElementById('certificates-container');
        const certificatesModal = document.getElementById('certificates-modal');
        
        certificateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const courseId = this.getAttribute('data-course-id');
                
                // Limpiar el contenedor antes de cargar nuevos certificados
                certificatesContainer.innerHTML = '<div class="col-span-2 text-center">Loading certificates...</div>';
                
                // Mostrar el modal
                const modal = tailwind.Modal.getOrCreateInstance(certificatesModal);
                modal.show();
                
                // Cargar los certificados del curso
                fetch(`/admin/courses/${courseId}/documents`)
                    .then(response => response.json())
                    .then(data => {
                        certificatesContainer.innerHTML = '';
                        
                        if (data.documents && data.documents.length > 0) {
                            data.documents.forEach(media => {
                                const certificateCard = document.createElement('div');
                                certificateCard.className = 'p-4 border rounded-lg';
                                
                                const fileIcon = media.mime_type.startsWith('image') ? 'image' : 'file-text';
                                
                                certificateCard.innerHTML = `
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <x-base.lucide class="h-6 w-6 mr-2 text-primary" icon="${fileIcon}" />
                                            <div>
                                                <div class="font-medium">Certificate</div>
                                                <div class="text-xs text-gray-500 truncate max-w-[150px]">${media.file_name}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <a href="${media.original_url}" target="_blank" class="text-blue-600 hover:text-blue-900 mr-2">
                                                <x-base.lucide class="h-4 w-4" icon="eye" />
                                            </a>
                                            <a href="${media.original_url}" download class="text-green-600 hover:text-green-900 mr-2">
                                                <x-base.lucide class="h-4 w-4" icon="download" />
                                            </a>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-2">
                                        Size: ${Math.round(media.size / 1024)} KB | 
                                        Type: ${media.mime_type} | 
                                        Uploaded: ${new Date(media.created_at).toLocaleDateString()}
                                    </div>
                                `;
                                
                                certificatesContainer.appendChild(certificateCard);
                            });
                        } else {
                            certificatesContainer.innerHTML = '<div class="col-span-2 text-center text-gray-500">No certificates found</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading certificates:', error);
                        certificatesContainer.innerHTML = '<div class="col-span-2 text-center text-red-500">Error loading certificates</div>';
                    });
            });
        });
    });
</script>
@endpush

@pushOnce('scripts')
    @vite('resources/js/app.js')
    @vite('resources/js/pages/notification.js')
@endPushOnce
@endsection
