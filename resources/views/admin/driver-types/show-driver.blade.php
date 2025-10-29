@extends('../themes/' . $activeTheme)

@section('title', 'Driver Details')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Driver Types', 'url' => route('admin.driver-types.index')],
['label' => 'Driver Details', 'active' => true],
];
@endphp

@section('subcontent')
<div class="grid grid-cols-12 gap-y-10 gap-x-6">
    <div class="col-span-12">
        <!-- Header -->
        <div class="flex flex-col md:h-10 gap-y-3 md:items-center md:flex-row">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Driver Details - {{ $driver->user->name ?? 'N/A' }}
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.driver-types.index') }}" variant="outline-secondary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="arrow-left" />
                    Back to List
                </x-base.button>
                <x-base.button as="a" href="{{ route('admin.driver-types.assign-vehicle', $driver) }}" variant="primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="truck" />
                    Assign Vehicle
                </x-base.button>
            </div>
        </div>

        <!-- Driver Information Card -->
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Driver Information</h3>
            </div>
            <div class="box-body p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Information -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-slate-700 border-b pb-2">Personal Information</h4>

                        <div class="flex items-center space-x-3">
                            <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center">
                                <x-base.lucide class="w-8 h-8 text-slate-500" icon="user" />
                            </div>
                            <div>
                                <div class="font-medium text-lg">{{ $driver->user->name ?? 'N/A' }}</div>
                                <div class="text-slate-500">Driver ID: {{ $driver->id }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label class="text-sm font-medium text-slate-600">Email</label>
                                <div class="text-slate-800">{{ $driver->user->email ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-600">Phone</label>
                                <div class="text-slate-800">{{ $driver->phone ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-600">Date of Birth</label>
                                <div class="text-slate-800">
                                    {{ $driver->date_of_birth ? \Carbon\Carbon::parse($driver->date_of_birth)->format('M d, Y') : 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-600">Status</label>
                                <div>
                                    @if($driver->status == 1)
                                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                    @else
                                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Information -->
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-slate-700 border-b pb-2">Company Information</h4>

                        <div class="w-full">
                            <div>
                                <label class="text-sm font-medium text-slate-600">Carrier</label>
                                <div class="text-slate-800">{{ $driver->carrier->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            @if($driver->driverEmploymentCompanies && $driver->driverEmploymentCompanies->count() > 0)
                            @foreach($driver->driverEmploymentCompanies as $employment)
                            <div>
                                <label class="text-sm font-medium text-slate-600">Company</label>
                                <div class="text-slate-800">{{ $employment->company->company_name ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-600">Employment Date</label>
                                <div class="text-slate-800">{{ $employment->created_at->format('M d, Y') }}</div>
                            </div>
                            @endforeach
                            @else
                            <div>
                                <label class="text-sm font-medium text-slate-600">Company</label>
                                <div class="text-slate-400">No company assigned</div>
                            </div>
                            @endif

                            <div>
                                <label class="text-sm font-medium text-slate-600">Registration Date</label>
                                <div class="text-slate-800">{{ $driver->created_at->format('M d, Y H:i') }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-600">Application Status</label>
                                <div>
                                    @if($driver->application_completed)
                                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                    @else
                                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                    @endif
                                </div>
                            </div>

                            @if($driver->hire_date)
                            <div>
                                <label class="text-sm font-medium text-slate-600">Hire Date</label>
                                <div class="text-slate-800">{{ $driver->hire_date->format('M d, Y') }}</div>
                            </div>
                            @endif

                            @if($driver->termination_date)
                            <div>
                                <label class="text-sm font-medium text-slate-600">Termination Date</label>
                                <div class="text-slate-800">{{ $driver->termination_date->format('M d, Y') }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Vehicle Assignment -->
        @php
        $activeAssignment = $driver->vehicleAssignments->where('status', 'active')->first();
        @endphp

        <div class="box box--stacked mt-5">
            <div class="box-header p-5 flex justify-between items-center">
                <h3 class="box-title">Current Vehicle Assignment</h3>
                @if($activeAssignment)
                <div class="flex gap-2">
                    <x-base.button as="a" href="{{ route('admin.driver-types.edit-assignment', $driver) }}" variant="outline-warning" size="sm">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="edit" />
                        Edit Assignment
                    </x-base.button>
                    <x-base.button
                        type="button"
                        variant="outline-danger"
                        size="sm"
                        onclick="confirmCancelAssignment({{ $driver->id }}, '{{ $driver->user->name ?? 'N/A' }}', '{{ $activeAssignment->vehicle->company_unit_number ?? 'N/A' }}')">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="x-circle" />
                        Cancel Assignment
                    </x-base.button>
                </div>
                @endif
            </div>
            <div class="box-body p-5">
                @if($activeAssignment && $activeAssignment->vehicle)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <x-base.lucide class="w-6 h-6 text-green-600" icon="truck" />
                            </div>
                            <div>
                                <div class="font-semibold text-lg text-green-800">
                                    Unit {{ $activeAssignment->vehicle->company_unit_number ?? 'N/A' }}
                                </div>
                                <div class="text-green-600">
                                    {{ $activeAssignment->vehicle->make }} {{ $activeAssignment->vehicle->model }} ({{ $activeAssignment->vehicle->year }})
                                </div>
                                <div class="text-sm text-green-600 mt-1">
                                    VIN: {{ $activeAssignment->vehicle->vin ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-green-600">Assigned Since</div>
                            <div class="font-medium text-green-800">
                                {{ $activeAssignment->start_date ? \Carbon\Carbon::parse($activeAssignment->start_date)->format('M d, Y') : 'N/A' }}
                            </div>
                            <div class="text-xs text-green-600 mt-1">
                                {{ $activeAssignment->start_date ? \Carbon\Carbon::parse($activeAssignment->start_date)->diffForHumans() : '' }}
                            </div>
                        </div>
                    </div>
                    @if($activeAssignment->notes)
                    <div class="mt-3 pt-3 border-t border-green-200">
                        <div class="text-sm text-green-600">Assignment Notes:</div>
                        <div class="text-green-800">{{ $activeAssignment->notes }}</div>
                    </div>
                    @endif
                </div>
                @else
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                    <x-base.lucide class="w-12 h-12 text-gray-400 mx-auto mb-3" icon="truck" />
                    <div class="text-lg font-medium text-gray-600">No Active Vehicle Assignment</div>
                    <div class="text-gray-500 mt-1 mb-4">This driver is currently not assigned to any vehicle.</div>
                    <x-base.button as="a" href="{{ route('admin.driver-types.assign-vehicle', $driver) }}" variant="primary">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="truck" />
                        Assign Vehicle
                    </x-base.button>
                </div>
                @endif
            </div>
        </div>

        <!-- Vehicle Assignment History -->
        <div class="box box--stacked mt-5">
            <div class="box-header p-5 flex justify-between items-center">
                <h3 class="box-title">Vehicle Assignment History</h3>
                <x-base.button as="a" href="{{ route('admin.driver-types.assignment-history', $driver) }}" variant="outline-secondary" size="sm">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="history" />
                    View Full History
                </x-base.button>
            </div>
            <div class="box-body p-5">
                @if($driver->vehicleAssignments && $driver->vehicleAssignments->count() > 0)
                <div class="overflow-x-auto">
                    <x-base.table class="border-separate border-spacing-y-[10px]">
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.th class="whitespace-nowrap">Vehicle</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Assignment Period</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Status</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Duration</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Notes</x-base.table.th>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody>
                            @foreach($driver->vehicleAssignments->sortByDesc('start_date')->take(5) as $assignment)
                            <x-base.table.tr>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    @if($assignment->vehicle)
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-slate-200 rounded-full flex items-center justify-center mr-3">
                                            <x-base.lucide class="w-5 h-5 text-slate-500" icon="truck" />
                                        </div>
                                        <div>
                                            <div class="font-medium">Unit {{ $assignment->vehicle->company_unit_number ?? 'N/A' }}</div>
                                            <div class="text-slate-500 text-sm">{{ $assignment->vehicle->make }} {{ $assignment->vehicle->model }}</div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-red-200 rounded-full flex items-center justify-center mr-3">
                                            <x-base.lucide class="w-5 h-5 text-red-500" icon="alert-circle" />
                                        </div>
                                        <div>
                                            <div class="font-medium text-red-600">Vehicle N/A</div>
                                            <div class="text-red-500 text-sm">Vehicle information not available</div>
                                        </div>
                                    </div>
                                    @endif
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    <div class="font-medium">
                                        {{ $assignment->start_date ? \Carbon\Carbon::parse($assignment->start_date)->format('M d, Y') : 'N/A' }}
                                    </div>
                                    <div class="text-slate-500 text-sm">
                                        to {{ $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date)->format('M d, Y') : 'Present' }}
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    @if($assignment->status === 'active')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                    @elseif($assignment->status === 'terminated')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Terminated</span>
                                    @else
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($assignment->status) }}</span>
                                    @endif
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    @if($assignment->start_date)
                                    @php
                                    $startDate = \Carbon\Carbon::parse($assignment->start_date);
                                    $endDate = $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date) : \Carbon\Carbon::now();
                                    $duration = $startDate->diffInDays($endDate);
                                    @endphp
                                    <div class="font-medium">{{ $duration }} days</div>
                                    <div class="text-slate-500 text-sm">
                                        {{ $startDate->diffForHumans($endDate, true) }}
                                    </div>
                                    @else
                                    <span class="text-slate-400">N/A</span>
                                    @endif
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    <div class="max-w-xs truncate" title="{{ $assignment->notes ?? 'No notes' }}">
                                        {{ $assignment->notes ?? 'No notes' }}
                                    </div>
                                </x-base.table.td>
                            </x-base.table.tr>
                            @endforeach
                        </x-base.table.tbody>
                    </x-base.table>
                </div>
                @if($driver->vehicleAssignments->count() > 5)
                <div class="mt-4 text-center">
                    <x-base.button as="a" href="{{ route('admin.driver-types.assignment-history', $driver) }}" variant="outline-primary">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="eye" />
                        View All {{ $driver->vehicleAssignments->count() }} Assignments
                    </x-base.button>
                </div>
                @endif
                @else
                <div class="text-center py-8">
                    <x-base.lucide class="w-16 h-16 text-slate-300 mx-auto" icon="truck" />
                    <div class="text-xl font-medium text-slate-500 mt-3">No Vehicle Assignment History</div>
                    <div class="text-slate-400 mt-2">This driver has not been assigned to any vehicles yet.</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Emergency Contact Information -->
        @if($driver->emergency_contact_name || $driver->emergency_contact_phone)
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Emergency Contact</h3>
            </div>
            <div class="box-body p-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Name</label>
                        <div class="text-slate-800">{{ $driver->emergency_contact_name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Phone</label>
                        <div class="text-slate-800">{{ $driver->emergency_contact_phone ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-600">Relationship</label>
                        <div class="text-slate-800">{{ $driver->emergency_contact_relationship ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Notes -->
        @if($driver->notes)
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Notes</h3>
            </div>
            <div class="box-body p-5">
                <div class="text-slate-700">{{ $driver->notes }}</div>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Actions</h3>
            </div>
            <div class="box-body p-5">
                <div class="flex flex-wrap gap-3">
                    @if($activeAssignment && $activeAssignment->vehicle)
                    <x-base.button as="a" href="{{ route('admin.driver-types.edit-assignment', $driver) }}" variant="outline-warning">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="edit" />
                        Edit Vehicle Assignment
                    </x-base.button>
                    <x-base.button
                        type="button"
                        variant="danger"
                        data-tw-toggle="modal"
                        data-tw-target="#cancelAssignmentModal"
                        onclick="confirmCancelAssignment({{ $driver->id }}, '{{ $driver->user->name ?? 'N/A' }}', '{{ $activeAssignment->vehicle->company_unit_number ?? 'N/A' }}')">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="x-circle" />
                        Cancel Assignment
                    </x-base.button>
                    @else
                    <x-base.button as="a" href="{{ route('admin.driver-types.assign-vehicle', $driver) }}" variant="primary">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="truck" />
                        Assign Vehicle
                    </x-base.button>
                    @endif
                    <x-base.button as="a" href="{{ route('admin.driver-types.contact', $driver) }}" variant="outline-secondary">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="mail" />
                        Contact Driver
                    </x-base.button>
                    <x-base.button as="a" href="{{ route('admin.driver-types.assignment-history', $driver) }}" variant="outline-primary">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="history" />
                        Assignment History
                    </x-base.button>
                </div>
            </div>
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