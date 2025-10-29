@extends('../themes/' . $activeTheme)

@section('title', 'Assignment History')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Driver Types', 'url' => route('admin.driver-types.index')],
['label' => 'Assignment History', 'active' => true],
];
@endphp

@section('subcontent')
<div class="grid grid-cols-12 gap-y-10 gap-x-6">
    <div class="col-span-12">
        <!-- Header -->
        <div class="flex flex-col md:h-10 gap-y-3 md:items-center md:flex-row">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Vehicle Assignment History - {{ $driver->user->name ?? 'N/A' }}
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

        <!-- Assignment History -->
        <div class="box box--stacked mt-5">
            <div class="box-header p-5">
                <h3 class="box-title">Complete Assignment History ({{ $driver->vehicleAssignments->count() }} records)</h3>
            </div>
            <div class="box-body p-0">
                @if($driver->vehicleAssignments->count() > 0)
                <div class="overflow-x-auto">
                    <x-base.table class="border-separate border-spacing-y-[10px]">
                        <x-base.table.thead>
                            <x-base.table.tr>
                                <x-base.table.th class="whitespace-nowrap">Vehicle</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Assignment Period</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Duration</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Status</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Assigned By</x-base.table.th>
                                <x-base.table.th class="whitespace-nowrap">Notes</x-base.table.th>
                            </x-base.table.tr>
                        </x-base.table.thead>
                        <x-base.table.tbody>
                            @foreach($driver->vehicleAssignments as $assignment)
                            <x-base.table.tr>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    <div class="font-medium">
                                        Unit {{ $assignment->vehicle->company_unit_number ?? 'N/A' }}
                                    </div>
                                    <div class="text-slate-500 text-xs">
                                        {{ $assignment->vehicle->make ?? 'N/A' }} {{ $assignment->vehicle->model ?? '' }}
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    <div class="text-sm">
                                        <div class="font-medium">
                                            Start: {{ $assignment->start_date ? $assignment->start_date->format('M d, Y') : 'N/A' }}
                                        </div>
                                        @if($assignment->end_date)
                                        <div class="text-slate-500">
                                            End: {{ $assignment->end_date->format('M d, Y') }}
                                        </div>
                                        @else
                                        <div class="text-green-600">
                                            Current Assignment
                                        </div>
                                        @endif
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    @if($assignment->start_date)
                                    @php
                                    $startDate = $assignment->start_date;
                                    $endDate = $assignment->end_date ? $assignment->end_date : \Carbon\Carbon::now();
                                    $duration = round($startDate->diffInDays($endDate));
                                    @endphp
                                    <div class="text-sm">
                                        {{ $duration }} days
                                    </div>
                                    @if(!$assignment->end_date)
                                    <div class="text-xs text-green-600">
                                        (Active)
                                    </div>
                                    @endif
                                    @else
                                    <span class="text-slate-400">N/A</span>
                                    @endif
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    @if($assignment->status === 'active')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                    @elseif($assignment->status === 'terminated')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Terminated
                                    </span>
                                    @if($assignment->termination_reason)
                                    <div class="text-xs text-slate-500 mt-1">
                                        {{ $assignment->termination_reason }}
                                    </div>
                                    @endif
                                    @else
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                    @endif
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    <div class="text-sm">
                                        {{ $assignment->assignedByUser->name ?? 'System' }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ $assignment->created_at ? $assignment->created_at->format('M d, Y H:i') : 'N/A' }}
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                    @if($assignment->notes)
                                    <div class="text-sm text-slate-600 max-w-xs">
                                        {{ Str::limit($assignment->notes, 100) }}
                                        @if(strlen($assignment->notes) > 100)
                                        <button type="button" class="text-blue-600 hover:text-blue-800 ml-1" onclick="showFullNotes('{{ $assignment->id }}')">
                                            Read more
                                        </button>
                                        @endif
                                    </div>
                                    @else
                                    <span class="text-slate-400 text-sm">No notes</span>
                                    @endif
                                </x-base.table.td>
                            </x-base.table.tr>
                            @endforeach
                        </x-base.table.tbody>
                    </x-base.table>
                </div>
                @else
                <div class="p-10 text-center">
                    <x-base.lucide class="w-16 h-16 text-slate-300 mx-auto" icon="history" />
                    <div class="text-xl font-medium text-slate-500 mt-3">No Assignment History</div>
                    <div class="text-slate-400 mt-2">This driver has no vehicle assignment history.</div>
                    <x-base.button as="a" href="{{ route('admin.driver-types.assign-vehicle', $driver) }}" variant="primary" class="mt-4">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="truck" />
                        Assign First Vehicle
                    </x-base.button>
                </div>
                @endif
            </div>
        </div>

        <!-- Summary Statistics -->
        @if($driver->vehicleAssignments->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-5">
            <div class="box box--stacked">
                <div class="box-body p-5 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $driver->vehicleAssignments->count() }}</div>
                    <div class="text-slate-500 text-sm">Total Assignments</div>
                </div>
            </div>
            <div class="box box--stacked">
                <div class="box-body p-5 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $driver->vehicleAssignments->where('status', 'active')->count() }}</div>
                    <div class="text-slate-500 text-sm">Active Assignments</div>
                </div>
            </div>
            <div class="box box--stacked">
                <div class="box-body p-5 text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $driver->vehicleAssignments->where('status', 'terminated')->count() }}</div>
                    <div class="text-slate-500 text-sm">Terminated Assignments</div>
                </div>
            </div>
            <div class="box box--stacked">
                <div class="box-body p-5 text-center">
                    @php
                    $totalDays = 0;
                    foreach($driver->vehicleAssignments as $assignment) {
                    if($assignment->start_date) {
                    $startDate = $assignment->start_date;
                    $endDate = $assignment->end_date ? $assignment->end_date : \Carbon\Carbon::now();
                    $totalDays += $startDate->diffInDays($endDate);
                    }
                    }
                    $totalDays = round($totalDays);
                    @endphp
                    <div class="text-2xl font-bold text-purple-600">{{ $totalDays }}</div>
                    <div class="text-slate-500 text-sm">Total Days Assigned</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Assignment Notes Dialog -->
<x-base.dialog id="fullNotesModal" size="lg">
    <x-base.dialog.panel>
        <x-base.dialog.title>
            <h2 class="font-medium text-base mr-auto">Assignment Notes</h2>
        </x-base.dialog.title>
        <x-base.dialog.description class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12">
                <div id="fullNotesContent" class="text-slate-600 p-4 bg-slate-50 rounded-lg min-h-[100px] whitespace-pre-wrap"></div>
            </div>
        </x-base.dialog.description>
        <x-base.dialog.footer>
            <x-base.button class="mr-1 w-20" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                Close
            </x-base.button>
        </x-base.dialog.footer>
    </x-base.dialog.panel>
</x-base.dialog>
@endsection

@push('scripts')
<script>
    const assignmentNotes = {
        @foreach($driver->vehicleAssignments as $assignment)
        '{{ $assignment->id }}': @json($assignment->notes ?? ''),
        @endforeach
    };

    function showFullNotes(assignmentId) {
        const notes = assignmentNotes[assignmentId];
        document.getElementById('fullNotesContent').textContent = notes;

        const dialog = tailwind.Dialog.getOrCreateInstance(document.querySelector("#fullNotesModal"));
        dialog.show();
    }
</script>
@endpush