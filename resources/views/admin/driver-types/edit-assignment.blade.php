@extends('../themes/' . $activeTheme)

@section('title', 'Edit Vehicle Assignment')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Driver Types', 'url' => route('admin.driver-types.index')],
['label' => 'Edit Assignment', 'active' => true],
];
@endphp

@section('subcontent')
<div class="grid grid-cols-12 gap-y-10 gap-x-6">
    <div class="col-span-12">
        <!-- Header -->
        <div class="flex flex-col md:h-10 gap-y-3 md:items-center md:flex-row">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Edit Vehicle Assignment for {{ $driver->user->name ?? 'N/A' }}
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.driver-types.index') }}" variant="outline-secondary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                    Back to List
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.driver-types.show', $driver) }}" variant="outline-primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="eye" />
                    View Driver
                </x-base.button>
            </div>
        </div>

        <!-- Driver Information Summary -->
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Driver Information</h3>
            </div>
            <div class="box-body p-5">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center">
                        <x-base.lucide class="w-6 h-6 text-slate-500" icon="user" />
                    </div>
                    <div>
                        <div class="font-medium text-lg">{{ $driver->user->name ?? 'N/A' }}</div>
                        <div class="text-slate-500">{{ $driver->user->email ?? 'N/A' }} | Carrier: {{ $driver->carrier->name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Assignment Information -->
        @if($driver->activeVehicleAssignment)
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Current Vehicle Assignment</h3>
            </div>
            <div class="box-body p-5">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <x-base.lucide class="w-5 h-5 text-blue-600 mr-3" icon="truck" />
                        <div>
                            <div class="font-medium text-blue-900">
                                Unit {{ $driver->activeVehicleAssignment->vehicle->company_unit_number ?? 'N/A' }} -
                                {{ $driver->activeVehicleAssignment->vehicle->make }} {{ $driver->activeVehicleAssignment->vehicle->model }}
                            </div>
                            <div class="text-sm text-blue-700 mt-1">
                                Assigned: {{ $driver->activeVehicleAssignment->assignment_date ? \Carbon\Carbon::parse($driver->activeVehicleAssignment->assignment_date)->format('M d, Y') : 'N/A' }}
                                @if($driver->activeVehicleAssignment->notes)
                                | Notes: {{ $driver->activeVehicleAssignment->notes }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Edit Assignment Form -->
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Change Vehicle Assignment</h3>
            </div>
            <div class="box-body p-5">
                @if(session('error'))
                <div class="alert alert-danger mb-4">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="alert-circle" />
                    {{ session('error') }}
                </div>
                @endif

                <form action="{{ route('admin.driver-types.update-assignment', $driver) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- New Vehicle Selection -->
                        <div class="space-y-4">
                            <div>
                                <x-base.form-label for="vehicle_id">Select New Vehicle *</x-base.form-label>
                                <x-base.form-select id="vehicle_id" name="vehicle_id" required>
                                    <option value="">Choose a vehicle...</option>
                                    @foreach($availableVehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                        Unit {{ $vehicle->company_unit_number ?? 'N/A' }} - {{ $vehicle->make }} {{ $vehicle->model }}
                                        @if($vehicle->carrier)
                                        ({{ $vehicle->carrier->name }})
                                        @endif
                                    </option>
                                    @endforeach
                                </x-base.form-select>
                                @error('vehicle_id')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <x-base.form-label for="effective_date">Effective Date *</x-base.form-label>
                                <x-base.litepicker
                                    name="effective_date"
                                    value="{{ old('effective_date', date('Y-m-d')) }}"
                                    placeholder="Select effective date" />
                                @error('effective_date')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Termination Details -->
                        <div class="space-y-4">
                            <div>
                                <x-base.form-label for="termination_reason">Reason for Change *</x-base.form-label>
                                <x-base.form-select id="termination_reason" name="termination_reason" required>
                                    <option value="">Select reason...</option>
                                    <option value="Vehicle change requested" {{ old('termination_reason') == 'Vehicle change requested' ? 'selected' : '' }}>Vehicle change requested</option>
                                    <option value="Vehicle maintenance" {{ old('termination_reason') == 'Vehicle maintenance' ? 'selected' : '' }}>Vehicle maintenance</option>
                                    <option value="Vehicle upgrade" {{ old('termination_reason') == 'Vehicle upgrade' ? 'selected' : '' }}>Vehicle upgrade</option>
                                    <option value="Operational requirements" {{ old('termination_reason') == 'Operational requirements' ? 'selected' : '' }}>Operational requirements</option>
                                    <option value="Driver request" {{ old('termination_reason') == 'Driver request' ? 'selected' : '' }}>Driver request</option>
                                    <option value="Other" {{ old('termination_reason') == 'Other' ? 'selected' : '' }}>Other</option>
                                </x-base.form-select>
                                @error('termination_reason')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <x-base.form-label for="notes">Additional Notes</x-base.form-label>
                                <x-base.form-textarea
                                    id="notes"
                                    name="notes"
                                    rows="4"
                                    placeholder="Enter any additional notes about this assignment change...">{{ old('notes') }}</x-base.form-textarea>
                                @error('notes')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                                <div class="text-slate-500 text-sm mt-1">Maximum 1000 characters</div>
                            </div>
                        </div>
                    </div>

                    <!-- Warning Notice -->
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start">
                            <x-base.lucide class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" icon="alert-triangle" />
                            <div>
                                <div class="font-medium text-yellow-900">Important Notice</div>
                                <div class="text-sm text-yellow-800 mt-1">
                                    This action will:
                                    <ul class="list-disc list-inside mt-2 space-y-1">
                                        <li>Terminate the current vehicle assignment</li>
                                        <li>Make the current vehicle available for other drivers</li>
                                        <li>Create a new assignment with the selected vehicle</li>
                                        <li>Update the assignment history for both vehicles</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                        <x-base.button as="a" href="{{ route('admin.driver-types.show', $driver) }}" variant="outline-secondary">
                            Cancel
                        </x-base.button>
                        <x-base.button type="submit" variant="warning">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="refresh-cw" />
                            Update Assignment
                        </x-base.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Available Vehicles List -->
        @if($availableVehicles->count() > 0)
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Available Vehicles ({{ $availableVehicles->count() }})</h3>
            </div>
            <div class="box-body p-0">
                <div class="overflow-x-auto">
                    <x-base.table class="border-separate border-spacing-y-[10px]">
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.th class="whitespace-nowrap">Make & Model</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Vehicle Number</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Type</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Carrier</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Status</x-base.table.th>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody>
                            @foreach($availableVehicles as $vehicle)
                            <x-base.table.tr>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    <div class="font-medium">{{ $vehicle->make }} {{ $vehicle->model }}</div>
                                    <div class="text-slate-500 text-xs">Year: {{ $vehicle->year ?? 'N/A' }}</div>
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    <div class="font-medium">Unit {{ $vehicle->company_unit_number ?? 'N/A' }}</div>
                                    <div class="text-slate-500 text-xs">ID: {{ $vehicle->id }}</div>
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    {{ $vehicle->vehicleType->name ?? 'N/A' }}
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    {{ $vehicle->carrier->name ?? 'N/A' }}
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Available
                                    </span>
                                </x-base.table.td>
                            </x-base.table.tr>
                            @endforeach
                        </x-base.table.tbody>
                    </x-base.table>
                </div>
            </div>
        </div>
        @else
        <div class="box box--stacked mt-5">
            <div class="box-body p-10 text-center">
                <x-base.lucide class="w-16 h-16 text-slate-300 mx-auto" icon="truck" />
                <div class="text-xl font-medium text-slate-500 mt-3">No Available Vehicles</div>
                <div class="text-slate-400 mt-2">All vehicles are currently assigned to other drivers.</div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection