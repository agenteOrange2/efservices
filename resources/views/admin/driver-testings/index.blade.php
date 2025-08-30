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
                            <x-base.form-input class="rounded-[0.5rem] pl-9" name="search_term"
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
                                <option value="{{ $id }}" {{ request('carrier_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                        <select name="status" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Statuses</option>
                            @foreach (\App\Models\Admin\Driver\DriverTesting::getStatuses() as $statusKey => $statusValue)
                                <option value="{{ $statusKey }}" {{ request('status') == $statusKey ? 'selected' : '' }}>
                                    {{ $statusValue }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por fecha inicio -->
                    <div class="mt-3">
                        <x-base.form-label for="test_date_from">From Date</x-base.form-label>
                        <x-base.litepicker id="test_date_from" name="test_date_from" class="w-full"
                            value="{{ request('test_date_from') }}" placeholder="Select Date" />
                    </div>

                    <!-- Filtro por fecha fin -->
                    <div class="mt-3">
                        <x-base.form-label for="test_date_to">To Date</x-base.form-label>
                        <x-base.litepicker id="test_date_to" name="test_date_to" class="w-full"
                            value="{{ request('test_date_to') }}" placeholder="Select Date" />
                    </div>

                    <!-- Filtro por Location -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Test Location</label>
                        <select name="location" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
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
                        <a href="{{ route('admin.driver-testings.index') }}"
                            class="btn btn-outline-secondary flex items-center">
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


                    <x-base.table class="border-separate border-spacing-y-[10px]">
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.th class="whitespace-nowrap">Registration Date</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Carrier</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Driver</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Test Type</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Status</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Test Result</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Actions</x-base.table.th>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody>
                            @forelse ($driverTestings as $test)
                                <x-base.table.tr>
                                    <x-base.table.td>
                                        {{ $test->test_date ? date('m/d/Y', strtotime($test->test_date)) : 'N/A' }}
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        @if ($test->userDriverDetail && $test->userDriverDetail->carrier)
                                            {{ $test->userDriverDetail->carrier->name }}
                                        @else
                                            <span class="text-red-500">Carrier data missing</span>
                                        @endif
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        @if ($test->userDriverDetail && $test->userDriverDetail->user)
                                            {{ $test->userDriverDetail->user->name }}
                                            {{ $test->userDriverDetail->user->last_name ?? '' }}
                                        @else
                                            <span class="text-red-500">Driver data missing</span>
                                        @endif
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        {{ $test->test_type }}
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        <span
                                            class="px-2 py-1 rounded-full text-xs
                                              {{ $test->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                            {{ \App\Models\Admin\Driver\DriverTesting::getStatuses()[$test->status] }}
                                        </span>
                                    </x-base.table.td>
                                    <x-base.table.td>
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-medium {{ $test->test_result == 'Positive' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $test->test_result }}
                                        </span>
                                    </x-base.table.td>

                                    <x-base.table.td class="flex">
                                        <div class="flex items-center">
                                            <a href="{{ route('admin.driver-testings.download-pdf', $test->id) }}"
                                                class="btn-sm btn-danger p-1 mr-2 flex" title="Download PDF"
                                                target="_blank">
                                                <x-base.lucide class="w-4 h-4" icon="file-text" />
                                            </a>
                                        </div>
                                        <x-base.menu class="h-5">
                                            <x-base.menu.button class="h-5 w-5 text-slate-500">
                                                <x-base.lucide class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70"
                                                    icon="MoreVertical" />
                                            </x-base.menu.button>
                                            <x-base.menu.items class="w-40">
                                                <!-- Ver detalles -->
                                                <a href="{{ route('admin.driver-testings.show', $test->id) }}"
                                                    class="btn-sm btn-danger mr-2 flex gap-2 items-center text-primary p-3">
                                                    <x-base.lucide class="mr-2 h-4 w-4" icon="Eye" />
                                                    View Details
                                                </a>
                                                <a href="{{ route('admin.driver-testings.download-pdf', $test->id) }}"
                                                    class="btn-sm btn-danger mr-2 flex gap-2 items-center text-primary p-3"
                                                    target="_blank">
                                                    <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" />
                                                    Download PDF
                                                </a>

                                                <!-- Editar -->
                                                <a href="{{ route('admin.driver-testings.edit', $test->id) }}"
                                                    class="btn-sm btn-danger mr-2 flex gap-2 items-center text-primary p-3">
                                                    <x-base.lucide class="mr-2 h-4 w-4" icon="Edit" />
                                                    Edit Test
                                                </a>

                                                <!-- Eliminar -->
                                                <button type="button" data-tw-toggle="modal"
                                                    data-tw-target="#delete-confirmation-modal"
                                                    class="btn-sm btn-danger mr-2 flex gap-2 items-center text-danger p-3 delete-testing"
                                                    data-testing-id="{{ $test->id }}">
                                                    <x-base.lucide class="mr-2 h-4 w-4" icon="Trash" />
                                                    Delete
                                                </button>
                                            </x-base.menu.items>
                                        </x-base.menu>

                                    </x-base.table.td>
                                </x-base.table.tr>
                            @empty
                                <x-base.table.tr>
                                    <x-base.table.td colspan="7" class="text-center">
                                        <div class="flex flex-col items-center justify-center py-16">
                                            <x-base.lucide class="h-8 w-8 text-slate-400" icon="Vial" />
                                            No tests found
                                        </div>
                                    </x-base.table.td>
                                </x-base.table.tr>
                            @endforelse
                        </x-base.table.tbody>
                    </x-base.table>
                </div>

                <!-- Paginación -->
                <div class="mt-5">
                    {{ $driverTestings->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <x-base.dialog id="delete-confirmation-modal" size="md">
        <x-base.dialog.panel>
            <div class="p-5 text-center">
                <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger" icon="x-circle" />
                <div class="mt-5 text-2xl">Are you sure?</div>
                <div class="mt-2 text-slate-500">
                    Do you really want to delete this test record?
                    <br>
                    This process cannot be undone.
                </div>
            </div>
            <form id="delete_testing_form" method="POST" action="" class="px-5 pb-8 text-center">
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

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuración del modal de eliminación
            const deleteButtons = document.querySelectorAll('.delete-testing');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const testingId = this.getAttribute('data-testing-id');
                    document.getElementById('delete_testing_form').action = 
                        '{{ url("admin/driver-testings") }}/' + testingId;
                });
            });
        });
    </script>
@endpush
