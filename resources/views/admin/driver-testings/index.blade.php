@extends('../themes/' . $activeTheme)
@section('title', 'Testing Drugs Management')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Testing Drugs Management', 'active' => true],
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
                Testing Drugs Management
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <a href="{{ route('admin.driver-testings.create') }}">
                    <x-base.button variant="primary" class="flex items-center">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                        Add New Test
                    </x-base.button>
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <form action="{{ route('admin.driver-testings.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <!-- Búsqueda general -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <x-base.lucide
                                class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                icon="Search" />
                            <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" name="search_term"
                                value="{{ request('search_term') }}" type="text" placeholder="Search tests..." />
                        </div>
                    </div>

                    <!-- Filtro por carrier -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Carrier</label>
                        <select name="carrier_id"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Carriers</option>
                            @foreach ($carriers as $id => $name)
                                <option value="{{ $id }}"
                                    {{ request('carrier_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                        <select name="status"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Statuses</option>
                            @foreach (\App\Models\Admin\Driver\DriverTesting::getStatuses() as $statusKey => $statusValue)
                                <option value="{{ $statusKey }}"
                                    {{ request('status') == $statusKey ? 'selected' : '' }}>
                                    {{ $statusValue }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por fecha inicio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input name="date_from" type="date" value="{{ request('date_from') }}"
                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                    </div>

                    <!-- Filtro por fecha fin -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input name="date_to" type="date" value="{{ request('date_to') }}"
                            class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm">
                    </div>

                    <!-- Filtro por Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Test Location</label>
                        <select name="location"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Locations</option>
                            @foreach (\App\Models\Admin\Driver\DriverTesting::getLocations() as $location)
                                <option value="{{ $location }}"
                                    {{ request('location') == $location ? 'selected' : '' }}>
                                    {{ $location }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="md:col-span-3 flex justify-start space-x-2">
                        <x-base.button type="submit" variant="primary" class="flex items-center">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="filter" />
                            Apply Filters
                        </x-base.button>
                        <a href="{{ route('admin.driver-testings.index') }}" class="btn btn-outline-secondary flex items-center">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="x" />
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead>
                            <tr class="bg-slate-50/60">
                                <th scope="col" class="px-6 py-3">ID</th>
                                <th scope="col" class="px-6 py-3">Date</th>
                                <th scope="col" class="px-6 py-3">Carrier</th>
                                <th scope="col" class="px-6 py-3">Driver</th>
                                <th scope="col" class="px-6 py-3">Test Type</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Result</th>
                                <th scope="col" class="px-6 py-3">Location</th>
                                <th scope="col" class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($driverTestings as $test)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ $test->id }}</td>
                                    <td class="px-6 py-4">{{ $test->test_date->format('m/d/Y') }}</td>
                                    <td class="px-6 py-4">{{ $test->carrier->name }}</td>
                                    <td class="px-6 py-4">
                                        {{ $test->userDriverDetail->user->name }} {{ $test->userDriverDetail->user->last_name }}
                                    </td>
                                    <td class="px-6 py-4">{{ $test->test_type }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            @if($test->status == 'approved') bg-green-100 text-green-800 
                                            @elseif($test->status == 'rejected') bg-red-100 text-red-800 
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ \App\Models\Admin\Driver\DriverTesting::getStatuses()[$test->status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium 
                                            @if($test->test_result == 'passed') bg-green-100 text-green-800 
                                            @elseif($test->test_result == 'failed') bg-red-100 text-red-800 
                                            @else bg-blue-100 text-blue-800 @endif">
                                            {{ ucfirst($test->test_result) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">{{ $test->location }}</td>
                                    <td class="px-6 py-4 text-right flex justify-end space-x-1">
                                        <!-- Ver detalles -->
                                        <a href="{{ route('admin.driver-testings.show', $test->id) }}" 
                                           class="btn btn-sm btn-outline-secondary">
                                            <x-base.lucide class="w-4 h-4" icon="eye" />
                                        </a>

                                        <!-- Descargar PDF -->
                                        <a href="{{ route('admin.driver-testings.download-pdf', $test->id) }}" 
                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                            <x-base.lucide class="w-4 h-4" icon="file-text" />
                                        </a>

                                        <!-- Editar -->
                                        <a href="{{ route('admin.driver-testings.edit', $test->id) }}" 
                                           class="btn btn-sm btn-outline-success">
                                            <x-base.lucide class="w-4 h-4" icon="edit" />
                                        </a>
                                            
                                        <!-- Eliminar -->
                                        <button type="button" data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal"
                                           class="btn btn-sm btn-outline-danger delete-testing" 
                                           data-testing-id="{{ $test->id }}">
                                            <x-base.lucide class="w-4 h-4" icon="trash" />
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center">No tests found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-5">
                    {{ $driverTestings->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <x-base.lucide class="w-16 h-16 text-danger mx-auto mt-3" icon="x-circle" />
                        <div class="text-3xl mt-5">Are you sure?</div>
                        <div class="text-slate-500 mt-2">
                            Do you really want to delete this test record? <br>
                            This process cannot be undone.
                        </div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <form id="delete_testing_form" method="POST" action="">
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

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Configuración del modal de eliminación
            const deleteButtons = document.querySelectorAll('.delete-testing');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const testingId = this.getAttribute('data-testing-id');
                    document.getElementById('delete_testing_form').action = 
                        `{{ route('admin.driver-testings.index') }}/${testingId}`;
                });
            });
        });
    </script>
@endpush
