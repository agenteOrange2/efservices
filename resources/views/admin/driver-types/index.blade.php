@extends('../themes/' . $activeTheme)
@section('title', 'All Drivers')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'All Drivers', 'active' => true],
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

    <!-- Cabecera -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between mt-8">
        <div>
            <h2 class="text-lg font-medium">All Drivers</h2>
            <div class="text-slate-500 text-sm mt-1">
                All drivers with carrier and company (with or without vehicle assignments)
            </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <x-base.button as="a" href="{{ route('admin.vehicles.index') }}" class="w-full sm:w-auto">
                <x-base.lucide class="w-4 h-4 mr-2" icon="truck" />
                Go to Vehicles
            </x-base.button>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="box box--stacked mt-5 p-3">
        <div class="box-header">
            <h3 class="box-title">Filter All Drivers</h3>
        </div>
        <div class="box-body p-5">
            <form action="{{ route('admin.driver-types.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <x-base.form-label for="search">Search Driver</x-base.form-label>
                    <x-base.form-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Driver name or email..." />
                </div>
                <div>
                    <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                    <x-base.form-select name="carrier_id" id="carrier_id">
                        <option value="">All Carriers</option>
                        @foreach($allCarriers as $carrier)
                        <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                            {{ $carrier->name }}
                        </option>
                        @endforeach
                    </x-base.form-select>
                </div>
                <div>
                    <x-base.form-label for="company_name">Company</x-base.form-label>
                    <x-base.form-input type="text" name="company_name" id="company_name" value="{{ request('company_name') }}" placeholder="Company name..." />
                </div>
                <div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <x-base.form-label for="date_from">Date (from)</x-base.form-label>
                            <x-base.litepicker name="date_from" value="{{ request('date_from') }}" placeholder="Select date" />
                        </div>
                        <div>
                            <x-base.form-label for="date_to">Date (to)</x-base.form-label>
                            <x-base.litepicker name="date_to" value="{{ request('date_to') }}" placeholder="Select date" />
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-4 flex gap-2">
                    <x-base.button type="submit" variant="primary" class="flex-1">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="filter" />
                        Apply Filters
                    </x-base.button>
                    <x-base.button as="a" href="{{ route('admin.driver-types.index') }}" variant="outline-secondary">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="x" />
                        Clear
                    </x-base.button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Drivers Disponibles -->
    <div class="box box--stacked mt-5">
        <div class="box-header p-3">
            <h3 class="box-title">All Drivers List ({{ $drivers->total() }} total)</h3>
        </div>
        <div class="box-body p-0">
            @if($drivers->count() > 0)
            <div class="overflow-x-auto">
                <x-base.table class="border-separate border-spacing-y-[10px]">
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <x-base.table.th class="whitespace-nowrap">Driver Name</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Email</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Carrier</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Company</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Vehicle Status</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Registration Date</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap text-center">Actions</x-base.table.th>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach($drivers as $driver)
                        <x-base.table.tr>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-slate-200 rounded-full flex items-center justify-center mr-3">
                                        <x-base.lucide class="w-5 h-5 text-slate-500" icon="user" />
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $driver->user->name ?? 'N/A' }}</div>
                                        <div class="text-slate-500 text-xs">ID: {{ $driver->id }}</div>
                                    </div>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                <div class="font-medium">{{ $driver->user->email ?? 'N/A' }}</div>
                                <div class="text-slate-500 text-xs">
                                    Status:
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                <div class="font-medium">{{ $driver->carrier->name ?? 'N/A' }}</div>
                                <div class="text-slate-500 text-xs">Carrier ID: {{ $driver->carrier_id }}</div>
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                @php
                                $company = $driver->driverEmploymentCompanies->first();
                                @endphp
                                @if($company)
                                <div class="font-medium">{{ $company->company_name }}</div>
                                <div class="text-slate-500 text-xs">
                                    Since: {{ $company->created_at ? $company->created_at->format('M d, Y') : 'N/A' }}
                                </div>
                                @else
                                <span class="text-slate-400">No company</span>
                                @endif
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                @php
                                $activeAssignment = $driver->vehicleAssignments->where('status', 'active')->first();
                                @endphp
                                @if($activeAssignment && $activeAssignment->vehicle)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Unit {{ $activeAssignment->vehicle->company_unit_number ?? 'N/A' }}
                                </span>
                                <div class="text-slate-500 text-xs mt-1">
                                    {{ $activeAssignment->vehicle->make }} {{ $activeAssignment->vehicle->model }}
                                </div>
                                @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    No Vehicle
                                </span>
                                <div class="text-slate-500 text-xs mt-1">
                                    Available for assignment
                                </div>
                                @endif
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                <div class="font-medium">{{ $driver->created_at->format('M d, Y') }}</div>
                                <div class="text-slate-500 text-xs">{{ $driver->created_at->format('H:i') }}</div>
                            </x-base.table.td>
                            <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <x-base.button as="a" href="{{ route('admin.driver-types.show', $driver) }}" variant="outline-primary" size="sm" title="View Driver Details">
                                        <x-base.lucide class="w-4 h-4" icon="eye" />
                                    </x-base.button>

                                    @if($activeAssignment && $activeAssignment->vehicle)
                                    <!-- Edit Assignment Button -->
                                    <x-base.button as="a" href="{{ route('admin.driver-types.edit-assignment', $driver) }}" variant="outline-warning" size="sm" title="Edit Vehicle Assignment">
                                        <x-base.lucide class="w-4 h-4" icon="edit" />
                                    </x-base.button>

                                    <!-- Cancel Assignment Button -->
                                    <x-base.button
                                        type="button"
                                        variant="outline-danger"
                                        size="sm"
                                        title="Cancel Vehicle Assignment"
                                        data-tw-toggle="modal"
                                        data-tw-target="#cancelAssignmentModal"
                                        onclick="confirmCancelAssignment({{ $driver->id }}, '{{ $driver->user->name ?? 'N/A' }}', '{{ $activeAssignment->vehicle->company_unit_number ?? 'N/A' }}')">
                                        <x-base.lucide class="w-4 h-4" icon="x-circle" />
                                    </x-base.button>
                                    @else
                                    <!-- Assign Vehicle Button -->
                                    <x-base.button as="a" href="{{ route('admin.driver-types.assign-vehicle', $driver) }}" variant="outline-success" size="sm" title="Assign to Vehicle">
                                        <x-base.lucide class="w-4 h-4" icon="truck" />
                                    </x-base.button>
                                    @endif

                                    <x-base.button as="a" href="{{ route('admin.driver-types.contact', $driver) }}" variant="outline-secondary" size="sm" title="Contact Driver">
                                        <x-base.lucide class="w-4 h-4" icon="mail" />
                                    </x-base.button>
                                </div>
                            </x-base.table.td>
                        </x-base.table.tr>
                        @endforeach
                    </x-base.table.tbody>
                </x-base.table>
            </div>

            <!-- Paginación -->
            <div class="p-5">
                {{ $drivers->appends(request()->query())->links() }}
            </div>
            @else
            <div class="p-10 text-center">
                <x-base.lucide class="w-16 h-16 text-slate-300 mx-auto" icon="users" />
                <div class="text-xl font-medium text-slate-500 mt-3">No drivers found</div>
                <div class="text-slate-400 mt-2">
                    @if(request()->hasAny(['search', 'carrier_id', 'company_name', 'date_from', 'date_to']))
                    Try adjusting your search criteria to find more drivers.
                    @else
                    No drivers with carriers and companies found in the system.
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Cancel Assignment Confirmation Modal -->
<x-base.dialog id="cancelAssignmentModal" size="md">
    <x-base.dialog.panel>
        <div class="p-5 text-center">
            <x-base.lucide class="mx-auto mt-3 h-16 w-16 text-warning" icon="alert-triangle" />
            <div class="mt-5 text-3xl">Are you sure?</div>
            <div class="mt-2 text-slate-500">
                You are about to cancel the vehicle assignment for <strong id="driverNameModal"></strong>.
                <br>
                Current vehicle: <strong id="vehicleUnitModal"></strong>
                <br><br>
                This action will terminate the current assignment and make both the driver and vehicle available for new assignments.
            </div>
        </div>
        <div class="px-5 pb-8 text-center">
            <x-base.button class="mr-1 w-24" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                Cancel
            </x-base.button>
            <x-base.button id="confirmCancelBtn" class="w-24" type="button" variant="danger">
                Yes, Cancel
            </x-base.button>
        </div>
    </x-base.dialog.panel>
</x-base.dialog>
@endsection

@push('scripts')
<script>
    function confirmCancelAssignment(driverId, driverName, vehicleUnit) {
        // Update modal content
        document.getElementById('driverNameModal').textContent = driverName;
        document.getElementById('vehicleUnitModal').textContent = 'Unit ' + vehicleUnit;

        // Set up the confirm button action
        document.getElementById('confirmCancelBtn').onclick = function() {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/driver-types/${driverId}/cancel-assignment`;

            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            // Add method override for DELETE
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);

            // Add termination_date (current date)
            const terminationDate = document.createElement('input');
            terminationDate.type = 'hidden';
            terminationDate.name = 'termination_date';
            terminationDate.value = new Date().toISOString().split('T')[0];
            form.appendChild(terminationDate);

            // Add termination_reason
            const terminationReason = document.createElement('input');
            terminationReason.type = 'hidden';
            terminationReason.name = 'termination_reason';
            terminationReason.value = 'Assignment cancelled by administrator';
            form.appendChild(terminationReason);

            // Add notes
            const notes = document.createElement('input');
            notes.type = 'hidden';
            notes.name = 'notes';
            notes.value = 'Assignment cancelled via admin panel';
            form.appendChild(notes);

            document.body.appendChild(form);
            form.submit();
        };
    }
</script>
@endpush