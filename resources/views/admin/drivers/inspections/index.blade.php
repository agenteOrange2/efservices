@extends('../themes/' . $activeTheme)
@section('title', 'Driver Inspections Management')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Inspections Management', 'active' => true],
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
                Driver Inspections Management
            </h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0 gap-2">
                <x-base.button as="a" href="{{ route('admin.inspections.documents') }}" variant="outline-secondary"
                    class="flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="file-text" />
                    All Documents
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.inspections.create') }}" variant="primary"
                    class="flex items-center">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add Inspection
                </x-base.button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-5">
                <form action="{{ route('admin.inspections.index') }}" method="GET" id="filter-form"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <x-base.lucide
                                class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                icon="Search" />
                            <x-base.form-input class="rounded-[0.5rem] pl-9" name="search_term"
                                value="{{ request('search_term') }}" type="text" placeholder="Search inspections..." />
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Vehicle</label>
                        <select name="vehicle_filter" id="vehicle_filter"
                            class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Vehicles</option>
                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}"
                                    {{ request('vehicle_filter') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->unit_number }} {{ $vehicle->make }} {{ $vehicle->model }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <x-base.litepicker id="date_from" name="date_from" class="w-full"
                            value="{{ request('date_from') }}" data-format="MM-DD-YYYY" placeholder="MM-DD-YYYY" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <x-base.litepicker id="date_to" name="date_to" class="w-full" 
                            value="{{ request('date_to') }}" data-format="MM-DD-YYYY" placeholder="MM-DD-YYYY" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Inspection Type</label>
                        <select name="inspection_type" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Types</option>
                            @foreach ($inspectionTypes as $type)
                                <option value="{{ $type }}" {{ request('inspection_type') == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Statuses</option>
                            <option value="Passed" {{ request('status') == 'Passed' ? 'selected' : '' }}>Passed</option>
                            <option value="Failed" {{ request('status') == 'Failed' ? 'selected' : '' }}>Failed</option>
                            <option value="Pending Repairs" {{ request('status') == 'Pending Repairs' ? 'selected' : '' }}>
                                Pending Repairs</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <x-base.button type="submit" variant="primary" class="mr-2">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="filter" />
                            Apply Filters
                        </x-base.button>
                        
                        <x-base.button type="button" id="clear-filters" class="btn btn-outline-secondary" variant="primary">
                            <x-base.lucide class="w-4 h-4 mr-1" icon="x" />
                            Clear Filters
                        </x-base.button>
                    </div>

                    <!-- Valores de depuración para ver qué está pasando con los filtros -->
                    @if(config('app.debug'))
                        <div class="col-span-3 mt-4 p-3 bg-slate-100 rounded-md text-xs">
                            <strong>Debug:</strong> Status: {{ request('status') }}, 
                            Driver: {{ request('driver_filter') }}, 
                            Carrier: {{ request('carrier_filter') }}, 
                            Vehicle: {{ request('vehicle_filter') }}
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Tabla -->
        <div class="box box--stacked mt-5">
            <div class="box-body p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3">Created At</th>
                                <th scope="col" class="px-6 py-3">Carrier</th>
                                <th scope="col" class="px-6 py-3">Driver</th>
                                <th scope="col" class="px-6 py-3">Vehicle</th>
                                <th scope="col" class="px-6 py-3">Inspection Type</th>                                
                                <th scope="col" class="px-6 py-3">Status</th>                                
                                <th class="whitespace-nowrap">
                                    <a href="{{ route(
                                        'admin.inspections.index',
                                        array_merge(request()->query(), [
                                            'sort_field' => 'inspection_date',
                                            'sort_direction' => request('sort_field') == 'inspection_date' && request('sort_direction') == 'asc' ? 'desc' : 'asc',
                                        ]),
                                    ) }}" class="flex items-center">
                                        Inspection Date
                                        @if (request('sort_field') == 'inspection_date')
                                            <x-base.lucide class="w-4 h-4 ml-1" icon="{{ request('sort_direction') == 'asc' ? 'arrow-up' : 'arrow-down' }}" />
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspections as $inspection)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ $inspection->created_at->format('m/d/Y') }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $carrier = null;
                                            if ($inspection->userDriverDetail && $inspection->userDriverDetail->user && $inspection->userDriverDetail->user->carrier) {
                                                $carrier = $inspection->userDriverDetail->user->carrier->name;
                                            } elseif ($inspection->userDriverDetail && $inspection->userDriverDetail->carrier) {
                                                $carrier = $inspection->userDriverDetail->carrier->name;
                                            }
                                        @endphp
                                        {{ $carrier ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if ($inspection->userDriverDetail)
                                            <div class="flex items-center">
                                                <span>{{ $inspection->userDriverDetail->user->name }} {{ $inspection->userDriverDetail->last_name }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($inspection->vehicle)
                                            {{ $inspection->vehicle->unit_number }}
                                            {{ $inspection->vehicle->make }}
                                            {{ $inspection->vehicle->model }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $inspection->inspection_type }}</td>                                    
                                    <td class="px-6 py-4">
                                        @if ($inspection->status == 'Passed')
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-success/20 text-success">
                                                Passed
                                            </span>
                                        @elseif ($inspection->status == 'Failed')
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-danger/20 text-danger">
                                                Failed
                                            </span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-warning/20 text-warning">
                                                {{ $inspection->status }}
                                            </span>
                                        @endif
                                    </td>                                    
                                    <td class="px-6 py-4">{{ $inspection->inspection_date->format('m/d/Y') }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center items-center">
                                            <a href="{{ route('admin.inspections.driver.documents', $inspection->userDriverDetail) }}" 
                                                class="btn btn-primary mr-2 p-1 text-blue-600 hover:text-blue-800" title="View driver documents">
                                                 <x-base.lucide class="w-4 h-4" icon="file-text" />
                                             </a>
                                            <a href="{{ route('admin.inspections.edit', $inspection) }}"
                                                class="btn btn-primary mr-2 p-1">
                                                <x-base.lucide class="w-4 h-4" icon="edit" />
                                            </a>
                                            <form action="{{ route('admin.inspections.destroy', $inspection) }}" method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this inspection?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger mr-2 p-1">
                                                    <x-base.lucide class="w-4 h-4" icon="trash" />
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.drivers.inspection-history', $inspection->userDriverDetail->id) }}"
                                                class="btn btn-outline-secondary p-1">
                                                <x-base.lucide class="w-4 h-4" icon="eye" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="bg-white border-b">
                                    <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                                        No inspection records found.
                                        <x-base.button as="a" href="{{ route('admin.inspections.create') }}"
                                            variant="outline-primary" class="mt-2">
                                            <x-base.lucide class="w-4 h-4 mr-1" icon="plus" />
                                            Add First Inspection Record
                                        </x-base.button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <div class="mt-5">
                    {{ $inspections->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Inspección -->
    <x-base.dialog id="delete-inspection-modal">
        <x-base.dialog.panel>
            <x-base.dialog.title>
                <h2 class="mr-auto text-base font-medium">Delete Inspection Record</h2>
            </x-base.dialog.title>
            <form id="delete_inspection_form" action="" method="POST">
                @csrf
                @method('DELETE')
                <x-base.dialog.description>
                    Are you sure you want to delete this inspection record? This action cannot be undone.
                </x-base.dialog.description>
                <x-base.dialog.footer>
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

                // Eliminar archivos
                document.querySelectorAll('.delete-file').forEach(button => {
                    button.addEventListener('click', function() {
                        if (confirm('Are you sure you want to delete this file?')) {
                            const mediaId = this.getAttribute('data-media-id');
                            const inspectionId = this.getAttribute('data-inspection-id');
                            
                            fetch(`/admin/inspections/${inspectionId}/documents/${mediaId}`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (data.success) {
                                        this.closest('.p-3').remove();
                                    } else {
                                        alert('Error deleting file');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error deleting file:', error);
                                    alert('An error occurred while deleting the file. Please try again.');
                                });
                        }
                    });
                });

                // Configuración del modal de eliminación
                const deleteButtons = document.querySelectorAll('.delete-inspection');
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const inspectionId = this.getAttribute('data-inspection-id');
                        document.getElementById('delete_inspection_form').action = 
                            `/admin/inspections/${inspectionId}`;
                    });
                });
            });
        </script>
    @endpush
@endsection
