@extends('../themes/' . $activeTheme)
@section('title', 'Driver Courses Management')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Courses Management', 'active' => true],
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

        <!-- Cabecera -->
        <div class="flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">
                Driver Courses Management
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <x-base.button as="a" href="{{ route('admin.courses.create') }}" variant="primary"
                    class="flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add Course
                </x-base.button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <form action="{{ route('admin.courses.index') }}" method="GET" id="filter-form"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <x-base.lucide
                                class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                icon="Search" />
                            <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search_term"
                                value="{{ request('search_term') }}" type="text" placeholder="Search courses..." />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Carrier</label>
                        <select name="carrier_filter" id="carrier_filter"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Carriers</option>
                            @foreach ($carriers as $carrier)
                                <option value="{{ $carrier->id }}"
                                    {{ request('carrier_filter') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Driver</label>
                        <select name="driver_filter" id="driver_filter"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Drivers</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}"
                                    {{ request('driver_filter') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }} {{ $driver->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input name="date_from" type="date" value="{{ request('date_from') }}"
                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input name="date_to" type="date" value="{{ request('date_to') }}"
                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-span-1 md:col-span-3 flex items-end space-x-2">
                        <x-base.button variant="primary" type="submit" class="flex items-center">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="search" />
                            Filter
                        </x-base.button>
                        <button type="button" id="clear-filters"
                            class="py-2 px-3 bg-gray-200 text-gray-700 rounded-md text-sm flex items-center">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="x" />
                            Clear Filters
                        </button>
                    </div>

                    <!-- Valores de depuración para ver qué está pasando con los filtros -->
                    @if(config('app.debug'))
                        <div class="col-span-3 mt-4 p-3 bg-slate-100 rounded-md text-xs">
                            Debug - Filters: Search: {{ request('search_term') }}, 
                            Driver: {{ request('driver_filter') }}, 
                            Carrier: {{ request('carrier_filter') }}
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3 cursor-pointer" data-sort-field="user_driver_detail_id">
                                    Driver
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Carrier
                                </th>
                                <th scope="col" class="px-6 py-3 cursor-pointer" data-sort-field="organization_name">
                                    Organization
                                </th>
                                <th scope="col" class="px-6 py-3 cursor-pointer" data-sort-field="certification_date">
                                    Certification Date
                                </th>
                                <th scope="col" class="px-6 py-3 cursor-pointer" data-sort-field="expiration_date">
                                    Expiration Date
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Documents
                                </th>
                                <th scope="col" class="px-6 py-3 text-center">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($courses as $course)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    @if($course->driverDetail && $course->driverDetail->user)
                                        {{ $course->driverDetail->user->name ?? '---' }} 
                                        {{ $course->driverDetail->user->last_name ?? '' }}
                                    @else
                                        <span class="text-red-500">No driver assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($course->driverDetail && $course->driverDetail->carrier)
                                        {{ $course->driverDetail->carrier->name }}
                                    @else
                                        <span class="text-red-500">No carrier assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    {{ $course->organization_name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $course->certification_date ? $course->certification_date->format('m/d/Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $course->expiration_date ? $course->expiration_date->format('m/d/Y') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($course->status == 'active')
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-600">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-600">
                                            {{ ucfirst($course->status ?? 'inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($course->getDocuments('certificates')->count() > 0)
                                        <a href="{{ route('admin.courses.edit', $course) }}" class="bg-primary/20 text-primary rounded px-2 py-1 text-xs">
                                            <i class="fas fa-file-alt mr-1"></i>{{ $course->getDocuments('certificates')->count() }} {{ Str::plural('Document', $course->getDocuments('certificates')->count()) }}
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs">No documents</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center items-center">
                                        <a href="{{ route('admin.courses.edit', $course) }}" class="mr-2" title="Edit Course">
                                            <x-base.lucide class="w-4 h-4 mr-3" icon="edit" />                                            
                                        </a>
                                        <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this course?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500" title="Delete Course">
                                                <x-base.lucide class="w-4 h-4 mr-3" icon="trash-2" />                                                
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No courses found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-5">
                    {{ $courses->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <x-base.dialog id="delete-confirmation-modal">
        <x-base.dialog.panel>
            <form id="delete_course_form" method="POST" action="">
                @csrf
                @method('DELETE')
                <x-base.dialog.title>
                    Delete Course
                </x-base.dialog.title>
                <x-base.dialog.description class="mt-2">
                    Are you sure you want to delete this course? This action cannot be undone.
                </x-base.dialog.description>
                <x-base.dialog.footer class="mt-5 text-right">
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="danger" class="w-20">
                        Delete
                    </x-base.button>
                </x-base.dialog.footer>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Manejar el botón de limpiar filtros
                document.getElementById('clear-filters').addEventListener('click', function() {
                    // Seleccionar todos los inputs y selects del formulario de filtros
                    const form = document.getElementById('filter-form');
                    const inputs = form.querySelectorAll('input:not([type="submit"]), select');
                    
                    // Resetear el valor de cada campo
                    inputs.forEach(input => {
                        if (input.type === 'date' || input.type === 'text') {
                            input.value = '';
                        } else if (input.tagName === 'SELECT') {
                            input.selectedIndex = 0;
                        }
                    });
                    
                    // Enviar el formulario con valores limpios
                    form.submit();
                });

                // Configuración del modal de eliminación
                const deleteButtons = document.querySelectorAll('.delete-course');
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const courseId = this.getAttribute('data-course-id');
                        document.getElementById('delete_course_form').action = 
                            `/admin/courses/${courseId}`;
                    });
                });
            });
        </script>
    @endpush
@endsection
