@extends('../themes/' . $activeTheme)
@section('title', 'Vehículos')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Vehículos', 'active' => true],
];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Vehicles
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.vehicles-documents.index') }}"
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200 mr-2"
                    variant="outline-primary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" />
                    Documents
                </x-base.button>
                <x-base.button as="a"
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="primary" href="{{ route('admin.vehicles.create') }}">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                    Add New Vehicle
                </x-base.button>
            </div>
        </div>
        <div class="mt-3.5">
            <div class="box box--stacked flex flex-col">
                <form method="GET" action="{{ route('admin.vehicles.index') }}" id="filterForm">
                    <!-- Hidden filter fields -->
                    <input type="hidden" name="carrier_id" id="hidden_carrier_id" value="{{ request('carrier_id') }}">
                    <input type="hidden" name="status" id="hidden_status" value="{{ request('status') }}">
                    <input type="hidden" name="vehicle_type" id="hidden_vehicle_type" value="{{ request('vehicle_type') }}">
                    <input type="hidden" name="vehicle_make" id="hidden_vehicle_make" value="{{ request('vehicle_make') }}">
                    
                    <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                        <div>
                            <div class="relative">
                                <x-base.lucide
                                    class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                    icon="Search" />
                                <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" type="text"
                                    name="search" value="{{ request('search') }}"
                                    placeholder="Buscar por unit, marca, modelo, VIN, registro..." />
                            </div>
                        </div>
                        <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                            <x-base.menu>
                                <x-base.menu.button type="button" class="w-full sm:w-auto" as="x-base.button" variant="outline-secondary">
                                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Download" />
                                    Export
                                    <x-base.lucide class="ml-2 h-4 w-4 stroke-[1.3]" icon="ChevronDown" />
                                </x-base.menu.button>
                                <x-base.menu.items class="w-40">
                                    <x-base.menu.item>
                                        <x-base.lucide class="mr-2 h-4 w-4" icon="FileBarChart" />
                                        PDF
                                    </x-base.menu.item>
                                    <x-base.menu.item>
                                        <x-base.lucide class="mr-2 h-4 w-4" icon="FileBarChart" />
                                        CSV
                                    </x-base.menu.item>
                                </x-base.menu.items>
                            </x-base.menu>
                            <x-base.popover class="inline-block">
                                <x-base.popover.button type="button" class="w-full sm:w-auto" as="x-base.button"
                                    variant="outline-secondary">
                                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowDownWideNarrow" />
                                    Filter
                                    @php
                                    $activeFilters = 0;
                                    if (request('carrier_id')) $activeFilters++;
                                    if (request('status')) $activeFilters++;
                                    if (request('vehicle_type')) $activeFilters++;
                                    if (request('vehicle_make')) $activeFilters++;
                                    @endphp
                                    @if($activeFilters > 0)
                                    <span
                                        class="ml-2 flex h-5 items-center justify-center rounded-full border bg-slate-100 px-1.5 text-xs font-medium">
                                        {{ $activeFilters }}
                                    </span>
                                    @endif
                                </x-base.popover.button>
                                <x-base.popover.panel>
                                    <div class="p-2">
                                        <div>
                                            <div class="text-left text-slate-500">
                                                Carrier
                                            </div>
                                            <x-base.form-select id="popover_carrier_id" class="mt-2 flex-1" onchange="updateHiddenField('carrier_id', this.value)">
                                                <option value="">Todos los Carriers</option>
                                                @foreach ($carriers as $carrier)
                                                <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                                    {{ $carrier->name }}
                                                </option>
                                                @endforeach
                                            </x-base.form-select>
                                        </div>
                                        <div class="mt-3">
                                            <div class="text-left text-slate-500">
                                                Status
                                            </div>
                                            <x-base.form-select id="popover_status" class="mt-2 flex-1" onchange="updateHiddenField('status', this.value)">
                                                <option value="">Todos los Status</option>
                                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="out_of_service" {{ request('status') == 'out_of_service' ? 'selected' : '' }}>Out Of Service</option>
                                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                            </x-base.form-select>
                                        </div>
                                        <div class="mt-3">
                                            <div class="text-left text-slate-500">
                                                Vehicle Type
                                            </div>
                                            <x-base.form-select id="popover_vehicle_type" class="mt-2 flex-1" onchange="updateHiddenField('vehicle_type', this.value)">
                                                <option value="">Todos los Tipos</option>
                                                @foreach ($vehicleTypes as $type)
                                                <option value="{{ $type->name }}" {{ request('vehicle_type') == $type->name ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                                @endforeach
                                            </x-base.form-select>
                                        </div>
                                        <div class="mt-3">
                                            <div class="text-left text-slate-500">
                                                Brand
                                            </div>
                                            <x-base.form-select id="popover_vehicle_make" class="mt-2 flex-1" onchange="updateHiddenField('vehicle_make', this.value)">
                                                <option value="">Todas las Marcas</option>
                                                @foreach ($vehicleMakes as $make)
                                                <option value="{{ $make->name }}" {{ request('vehicle_make') == $make->name ? 'selected' : '' }}>
                                                    {{ $make->name }}
                                                </option>
                                                @endforeach
                                            </x-base.form-select>
                                        </div>
                                        <div class="mt-4 flex items-center">
                                            <x-base.button type="button" onclick="clearFilters()" class="ml-auto w-32" variant="secondary">
                                                Clear
                                            </x-base.button>
                                            <x-base.button type="button" onclick="applyFilters()" class="ml-2 w-32" variant="primary">
                                                Apply
                                            </x-base.button>
                                        </div>
                                    </div>
                                </x-base.popover.panel>
                            </x-base.popover>
                        </div>
                    </div>
                </form>
                <div class="overflow-auto xl:overflow-visible">
                    <x-base.table class="border-b border-slate-200/60">
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Brand/Model
                                </x-base.table.td>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Unit
                                </x-base.table.td>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Type
                                </x-base.table.td>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    VIN
                                </x-base.table.td>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Register
                                </x-base.table.td>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                    Driver
                                </x-base.table.td>
                                <x-base.table.td
                                    class="border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                    Status
                                </x-base.table.td>
                                <x-base.table.td
                                    class="w-36 border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                    Action
                                </x-base.table.td>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody>
                            @foreach ($vehicles as $vehicle)
                            <x-base.table.tr class="[&_td]:last:border-b-0">
                                <x-base.table.td class="border-dashed py-4"
                                    
                                    href="{{ route('admin.vehicles.show', $vehicle->id) }}">
                                    {{ $vehicle->make }} {{ $vehicle->model }}
                                    </a>
                                    <div class="mt-0.5 whitespace-nowrap text-xs text-slate-500">
                                        Year: {{ $vehicle->year }}
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="border-dashed py-4">
                                    <div class="font-medium">{{ $vehicle->company_unit_number }}</div>
                                </x-base.table.td>
                                <x-base.table.td class="border-dashed py-4">
                                    <div class="whitespace-nowrap">
                                        {{ $vehicle->type }}
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="border-dashed py-4">
                                    <div class="whitespace-nowrap font-mono text-xs">
                                        {{ $vehicle->vin }}
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="border-dashed py-4">
                                    <div>
                                        <div class="whitespace-nowrap">{{ $vehicle->registration_number }}</div>
                                        <div class="mt-0.5 whitespace-nowrap text-xs text-slate-500">
                                            Expires: {{ $vehicle->registration_expiration_date->format('m/d/Y') }}
                                        </div>
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="border-dashed py-4">
                                    <div class="whitespace-nowrap">
                                        @if ($vehicle->currentDriverAssignment && $vehicle->currentDriverAssignment->user)
                                        <div class="font-medium">{{ $vehicle->currentDriverAssignment->user->name }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">
                                            {{ ucfirst(str_replace('_', ' ', $vehicle->currentDriverAssignment->assignment_type)) }}
                                        </div>
                                        @else
                                        <span class="text-slate-400">Not assigned</span>
                                        @endif
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="border-dashed py-4">
                                    <div @class([ 'flex items-center justify-center' , 'text-success'=> !$vehicle->out_of_service && !$vehicle->suspended,
                                        'text-warning' => $vehicle->suspended,
                                        'text-danger' => $vehicle->out_of_service,
                                        ])>
                                        <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]"
                                            icon="{{ !$vehicle->out_of_service && !$vehicle->suspended ? 'CheckCircle' : ($vehicle->suspended ? 'AlertTriangle' : 'XCircle') }}" />
                                        <div class="ml-1.5 whitespace-nowrap">
                                            @if ($vehicle->out_of_service)
                                            Out Of Service
                                            @elseif($vehicle->suspended)
                                            Suspendend
                                            @else
                                            Active
                                            @endif
                                        </div>
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="relative border-dashed py-4">
                                    <div class="flex items-center justify-center">
                                        <x-base.menu class="h-5">
                                            <x-base.menu.button class="h-5 w-5 text-slate-500">
                                                <x-base.lucide
                                                    class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70"
                                                    icon="MoreVertical" />
                                            </x-base.menu.button>
                                            <x-base.menu.items class="w-40">
                                                <x-base.menu.item
                                                    href="{{ route('admin.vehicles.show', $vehicle->id) }}">
                                                    <x-base.lucide class="mr-2 h-4 w-4" icon="Eye" />
                                                    View Details
                                                </x-base.menu.item>
                                                <x-base.menu.item
                                                    href="{{ route('admin.vehicles.edit', $vehicle->id) }}">
                                                    <x-base.lucide class="mr-2 h-4 w-4" icon="CheckSquare" />
                                                    Edit
                                                </x-base.menu.item>
                                                <x-base.menu.item
                                                    href="{{ route('admin.vehicles.documents.index', $vehicle->id) }}">
                                                    <x-base.lucide class="mr-2 h-4 w-4" icon="FileText" />
                                                    Documents
                                                </x-base.menu.item>
                                                <x-base.menu.item
                                                    href="{{ route('admin.vehicles.maintenances.index', $vehicle->id) }}">
                                                    <x-base.lucide class="mr-2 h-4 w-4" icon="Tool" />
                                                    Services
                                                </x-base.menu.item>
                                                <x-base.menu.divider />
                                                <x-base.menu.item class="text-danger" data-tw-toggle="modal"
                                                    data-tw-target="#delete-confirmation-modal-{{ $vehicle->id }}">
                                                    <x-base.lucide class="mr-2 h-4 w-4" icon="Trash2" />
                                                    Delete
                                                </x-base.menu.item>
                                            </x-base.menu.items>
                                        </x-base.menu>
                                    </div>
                                </x-base.table.td>
                            </x-base.table.tr>

                            <!-- DELETE MODAL -->
                            <x-base.dialog id="delete-confirmation-modal-{{ $vehicle->id }}" size="md">
                                <x-base.dialog.panel>
                                    <div class="p-5 text-center">
                                        <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-danger"
                                            icon="XCircle" />
                                        <div class="mt-5 text-2xl">Are you sure?</div>
                                        <div class="mt-2 text-slate-500">
                                            Do you really want to eliminate this vehicle? <br>
                                            This process cannot be undone.
                                        </div>
                                    </div>
                                    <div class="px-5 pb-8 text-center">
                                        <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <x-base.button class="mr-1 w-24" data-tw-dismiss="modal"
                                                type="button" variant="outline-secondary">
                                                Cancel
                                            </x-base.button>
                                            <x-base.button class="w-24" type="submit" variant="danger">
                                                Delete
                                            </x-base.button>
                                        </form>
                                    </div>
                                </x-base.dialog.panel>
                            </x-base.dialog>
                            @endforeach
                        </x-base.table.tbody>
                    </x-base.table>
                </div>
                <div class="flex-reverse flex flex-col-reverse flex-wrap items-center gap-y-2 p-5 sm:flex-row">
                    <div class="w-full">
                        {{ $vehicles->links('custom.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit search form on input
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    document.getElementById('filterForm').submit();
                }, 500);
            });
        }
    });

    // Update hidden field function
    function updateHiddenField(fieldName, value) {
        const hiddenField = document.getElementById('hidden_' + fieldName);
        if (hiddenField) {
            hiddenField.value = value;
        }
    }

    // Clear all filters function
    function clearFilters() {
        const form = document.getElementById('filterForm');
        if (form) {
            // Clear search input
            const searchInput = form.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // Clear hidden fields
            const hiddenFields = form.querySelectorAll('input[type="hidden"]');
            hiddenFields.forEach(field => {
                if (field.name !== '_token') {
                    field.value = '';
                }
            });
            
            // Clear popover selects
            const popoverSelects = [
                document.getElementById('popover_carrier_id'),
                document.getElementById('popover_status'),
                document.getElementById('popover_vehicle_type'),
                document.getElementById('popover_vehicle_make')
            ];
            
            popoverSelects.forEach(select => {
                if (select) {
                    select.selectedIndex = 0;
                }
            });
            
            form.submit();
        }
    }

    // Apply filters function
    function applyFilters() {
        // Update hidden fields with popover values
        updateHiddenField('carrier_id', document.getElementById('popover_carrier_id').value);
        updateHiddenField('status', document.getElementById('popover_status').value);
        updateHiddenField('vehicle_type', document.getElementById('popover_vehicle_type').value);
        updateHiddenField('vehicle_make', document.getElementById('popover_vehicle_make').value);
        
        // Submit form
        document.getElementById('filterForm').submit();
    }

    // Make functions available globally
    window.clearFilters = clearFilters;
    window.applyFilters = applyFilters;
    window.updateHiddenField = updateHiddenField;
</script>
@endpush