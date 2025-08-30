@extends('../themes/' . $activeTheme)
@section('title', 'Driver Accidents Management')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Accidents Management', 'active' => true],
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
                Driver Accidents Management
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <div class="flex gap-2">
                    <x-base.button as="a" href="{{ route('admin.accidents.documents.index') }}"
                        variant="outline-primary">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="file-text" />
                        View All Documents
                    </x-base.button>
                    <x-base.button as="a" href="{{ route('admin.accidents.create') }}" variant="primary"
                        class="flex items-center">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                        Add Accident
                    </x-base.button>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <form action="{{ route('admin.accidents.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div >
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative w-full">
                            <x-base.lucide
                                class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                icon="Search" />
                            <x-base.form-input class="rounded-[0.5rem] pl-9 w-full" name="search_term"
                                value="{{ request('search_term') }}" type="text" placeholder="Search accidents..." />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Carrier</label>
                        <select name="carrier_filter"
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
                    {{-- <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Driver</label>
                        <select name="driver_filter"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Drivers</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}"
                                    {{ request('driver_filter') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->user->name }} {{ $driver->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <x-base.litepicker id="date_from" name="date_from" class="w-full"
                            value="{{ request('date_from') }}" placeholder="Select Date" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <x-base.litepicker id="date_to" name="date_to" class="w-full" value="{{ request('date_to') }}" placeholder="Select Date" />
                    </div>
                    <div class="flex items-end">
                        <x-base.button type="submit" variant="outline-primary" class="mr-2">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="filter" />
                            Apply Filters
                        </x-base.button>
                        <a href="{{ route('admin.accidents.index') }}" class="btn btn-outline-secondary">
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
                                <th scope="col" class="px-6 py-3">Registration Date</th>
                                <th scope="col" class="px-6 py-3">Carrier</th>
                                <th scope="col" class="px-6 py-3">Driver</th>
                                <th scope="col" class="px-6 py-3">Nature of Accident</th>
                                <th class="whitespace-nowrap">
                                    <a href="{{ route(
                                        'admin.accidents.index',
                                        array_merge(request()->query(), [
                                            'sort_field' => 'accident_date',
                                            'sort_direction' =>
                                                request('sort_field') == 'accident_date' && request('sort_direction') == 'asc' ? 'desc' : 'asc',
                                        ]),
                                    ) }}"
                                        class="flex items-center">
                                        Date
                                        @if (request('sort_field') == 'accident_date')
                                            <x-base.lucide class="w-4 h-4 ml-1"
                                                icon="{{ request('sort_direction') == 'asc' ? 'chevron-up' : 'chevron-down' }}" />
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3">Injuries</th>
                                <th scope="col" class="px-6 py-3">Fatalities</th>
                                <th scope="col" class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accidents as $accident)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                    <td class="px-6 py-4">{{ $accident->accident_date->format('m/d/Y') }}</td>
                                    <td class="px-6 py-4">{{ $accident->userDriverDetail->carrier->name }}</td>
                                    <td class="px-6 py-4">
                                        {{ $accident->userDriverDetail->user->name }}
                                        {{ $accident->userDriverDetail->last_name }}
                                    </td>
                                    <td class="px-6 py-4">{{ $accident->nature_of_accident }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">{{ $accident->created_at->format('m/d/Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($accident->had_injuries)
                                            <span class="text-success">Yes ({{ $accident->number_of_injuries }})</span>
                                        @else
                                            <span class="text-danger">No</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($accident->had_fatalities)
                                            <span class="text-success">Yes ({{ $accident->number_of_fatalities }})</span>
                                        @else
                                            <span class="text-danger">No</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center items-center">
                                            <a href="{{ route('admin.drivers.accident-history', $accident->userDriverDetail->id) }}"
                                                class="btn btn-outline-secondary p-1 mr-2" title="View History">
                                                <x-base.lucide class="w-4 h-4" icon="eye" />
                                            </a>
                                            <a href="{{ route('admin.accidents.documents.show', $accident->id) }}"
                                                class="btn btn-outline-primary p-1" title="View Documents">
                                                <x-base.lucide class="w-4 h-4" icon="file-text" />
                                            </a>
                                            <a href="{{ route('admin.accidents.edit', $accident->id) }}"
                                                class="btn btn-primary mx-2 p-1" title="Edit Accident">
                                                <x-base.lucide class="w-4 h-4" icon="edit" />
                                            </a>
                                            <x-base.button data-tw-toggle="modal" data-tw-target="#delete-accident-modal"
                                                variant="danger" class="ml-2 p-1 delete-accident"
                                                data-accident-id="{{ $accident->id }}" title="Delete Accident">
                                                <x-base.lucide class="w-4 h-4" icon="trash" />
                                            </x-base.button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="flex flex-col items-center justify-center py-4">
                                            <x-base.lucide class="w-10 h-10 text-slate-300" icon="alert-triangle" />
                                            <p class="mt-2 text-slate-500">No accident records found</p>
                                            <x-base.button as="a" href="{{ route('admin.accidents.create') }}"
                                                variant="outline-primary" class="mt-3">
                                                <x-base.lucide class="w-4 h-4 mr-1" icon="plus" />
                                                Add First Accident
                                            </x-base.button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <div class="mt-5">
                    {{ $accidents->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Accidente -->
    <x-base.dialog id="delete-accident-modal">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Confirm Deletion</h2>
            </x-base.dialog.title>
            <x-base.dialog.description>
                Are you sure you want to delete this accident record? This action cannot be undone.
            </x-base.dialog.description>
            <x-base.dialog.footer>
                <form id="delete_accident_form" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <x-base.button data-tw-dismiss="modal" type="button" variant="outline-secondary" class="mr-1 w-20">
                        Cancel
                    </x-base.button>
                    <x-base.button type="submit" variant="danger" class="w-20">
                        Delete
                    </x-base.button>
                </form>
            </x-base.dialog.footer>
        </x-base.dialog.panel>
    </x-base.dialog>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                /**
                 * Configuración de botones de eliminación
                 */
                const deleteButtons = document.querySelectorAll('.delete-accident');
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const accidentId = this.getAttribute('data-accident-id');
                        const deleteForm = document.getElementById('delete_accident_form');
                        if (deleteForm && accidentId) {
                            deleteForm.action = `/admin/accidents/${accidentId}`;
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
