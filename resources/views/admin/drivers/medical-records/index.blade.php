@extends('../themes/' . $activeTheme)
@section('title', 'Medical Record')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Medical Record', 'active' => true],
];
@endphp

@section('subcontent')
<div class="container-fluid">
    <!-- Flash Messages -->
    @if(session('success'))
    <x-base.alert variant="success" dismissible>
        {{ session('success') }}
    </x-base.alert>
    @endif

    @if(session('error'))
    <x-base.alert variant="danger" dismissible>
        {{ session('error') }}
    </x-base.alert>
    @endif

    <!-- Header -->
    <div class="box box--stacked">
        <div class="box-header">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 p-5">
                <h2 class="box-title">Medical Records Management</h2>
                <x-base.button as="a" href="{{ route('admin.medical-records.create') }}" variant="primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add Medical Record
                </x-base.button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-5">
        <div class="box p-5">
            <div class="flex items-center justify-between">
                <!-- <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div> -->
                <div class="">
                    <h2 class="text-slate-500 text-lg">Total Records</h2>
                    <div class="font-medium text-slate-600 mt-1">{{ $totalCount }}</div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center bg-blue-100 rounded-lg ml-6">
                    <x-base.lucide class="w-6 h-6 text-blue-600" icon="file-text" />
                </div>
            </div>
        </div>
        <div class="box p-5">
            <div class="flex items-center justify-between">
                <div class="">
                    <!-- <div class="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div> -->
                    <h2 class="text-slate-500 text-lg">Expiring Soon</h2>
                    <div class="font-medium text-slate-600 mt-1">{{ $expiringCount }}</div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center bg-yellow-100 rounded-lg ml-6">
                    <x-base.lucide class="w-6 h-6 text-yellow-600" icon="clock" />
                </div>
            </div>
        </div>
        <div class="box p-5">
            <div class="flex items-center justify-between">
                <!-- <div class="w-2 h-2 bg-red-500 rounded-full mr-3"></div> -->
                <div class="">
                    <h2 class="text-slate-500 text-lg">Expired</h2>
                    <div class="font-medium text-slate-600 mt-1">{{ $expiredCount }}</div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center bg-red-100 rounded-lg ml-6">
                    <x-base.lucide class="w-6 h-6 text-red-600" icon="alert-circle" />
                </div>
            </div>
        </div>
    </div>

    <!-- Expiration Alerts -->
    @if($expiringCount > 0)
    <x-base.alert variant="warning" class="mt-5">
        <x-base.lucide class="w-4 h-4 mr-2" icon="alert-triangle" />
        <strong>{{ $expiringCount }}</strong> medical records are expiring within 30 days.
    </x-base.alert>
    @endif

    @if($expiredCount > 0)
    <x-base.alert variant="danger" class="mt-5">
        <x-base.lucide class="w-4 h-4 mr-2" icon="x-circle" />
        <strong>{{ $expiredCount }}</strong> medical records have expired.
    </x-base.alert>
    @endif

    <!-- Filters -->
    <div class="box box--stacked mt-5 p-3">
        <div class="box-header">
            <h3 class="box-title">Filters</h3>
        </div>
        <div class="box-body p-5">
            <form action="{{ route('admin.medical-records.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-base.form-label for="search_term">Search</x-base.form-label>
                    <x-base.form-input type="text" name="search_term" id="search_term" value="{{ request('search_term') }}" placeholder="Examiner name, registry number..." />
                </div>
                <div>
                    <x-base.form-label for="driver_filter">Driver</x-base.form-label>
                    <select id="driver_filter" name="driver_filter" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                        <option value="">All Drivers</option>
                        @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ request('driver_filter') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->user->name }} {{ $driver->user->last_name ?? '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <x-base.form-label for="date_from">Date (from)</x-base.form-label>
                            <x-base.litepicker name="date_from" value="{{ request('date_from') }}" placeholder="Select a date" />
                        </div>
                        <div>
                            <x-base.form-label for="date_to">Date (to)</x-base.form-label>
                            <x-base.litepicker name="date_to" value="{{ request('date_to') }}" placeholder="Select a date" />
                        </div>
                    </div>
                </div>
                <div class="flex items-end">
                    <x-base.button type="submit" variant="primary" class="w-full">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="filter" />
                        Apply Filters
                    </x-base.button>
                </div>
            </form>
        </div>
    </div>

    <!-- Medical Records Table -->
    <div class="box box--stacked mt-5 p-3">
        <div class="box-header">
            <h3 class="box-title">Medical Records ({{ $medicalRecords->total() }})</h3>
        </div>
        <div class="box-body p-0">
            @if($medicalRecords->count() > 0)
            <div class="overflow-x-auto">
                <x-base.table class="table-auto">
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <x-base.table.th class="whitespace-nowrap">
                                <a href="{{ route('admin.medical-records.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="flex items-center text-slate-500 hover:text-slate-700">
                                    Created Date
                                    @if(request('sort') == 'created_at')
                                    <x-base.lucide class="w-4 h-4 ml-1" icon="{{ request('direction') == 'asc' ? 'chevron-up' : 'chevron-down' }}" />
                                    @else
                                    <x-base.lucide class="w-4 h-4 ml-1 text-slate-400" icon="chevrons-up-down" />
                                    @endif
                                </a>
                            </x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Driver</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Carrier</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Examiner</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Registry Number</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">
                                <a href="{{ route('admin.medical-records.index', array_merge(request()->query(), ['sort' => 'medical_card_expiration_date', 'direction' => request('sort') == 'medical_card_expiration_date' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                                    class="flex items-center text-slate-500 hover:text-slate-700">
                                    Expiration Date
                                    @if(request('sort') == 'medical_card_expiration_date')
                                    <x-base.lucide class="w-4 h-4 ml-1" icon="{{ request('direction') == 'asc' ? 'chevron-up' : 'chevron-down' }}" />
                                    @else
                                    <x-base.lucide class="w-4 h-4 ml-1 text-slate-400" icon="chevrons-up-down" />
                                    @endif
                                </a>
                            </x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Status</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Documents</x-base.table.th>
                            <x-base.table.th class="whitespace-nowrap">Actions</x-base.table.th>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach($medicalRecords as $record)
                        @php
                        $expirationDate = \Carbon\Carbon::parse($record->medical_card_expiration_date);
                        $now = \Carbon\Carbon::now();
                        $daysUntilExpiration = $now->diffInDays($expirationDate, false);

                        if ($daysUntilExpiration < 0) {
                            $statusClass='bg-red-100 text-red-800' ;
                            $statusText='Expired' ;
                            } elseif ($daysUntilExpiration <=30) {
                            $statusClass='bg-yellow-100 text-yellow-800' ;
                            $statusText='Expiring Soon' ;
                            } else {
                            $statusClass='bg-green-100 text-green-800' ;
                            $statusText='Active' ;
                            }
                            @endphp
                            <x-base.table.tr>
                            <x-base.table.td class="whitespace-nowrap">
                                {{ $record->created_at->format('M d, Y') }}
                            </x-base.table.td>
                            <x-base.table.td class="whitespace-nowrap">
                                @if($record->userDriverDetail && $record->userDriverDetail->user)
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-slate-300 flex items-center justify-center">
                                            <span class="text-xs font-medium text-slate-700">
                                                {{ strtoupper(substr($record->userDriverDetail->user->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-slate-900">
                                            {{ $record->userDriverDetail->user->name }}
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            {{ $record->userDriverDetail->user->email }}
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span class="text-slate-400">No driver assigned</span>
                                @endif
                            </x-base.table.td>
                            <x-base.table.td class="whitespace-nowrap">
                                @if($record->userDriverDetail && $record->userDriverDetail->carrier)
                                <div class="text-sm font-medium text-slate-900">
                                    {{ $record->userDriverDetail->carrier->name }}
                                </div>
                                <div class="text-sm text-slate-500">
                                    {{ $record->userDriverDetail->carrier->mc_number ?? 'No MC Number' }}
                                </div>
                                @else
                                <span class="text-slate-400">No carrier assigned</span>
                                @endif
                            </x-base.table.td>
                            <x-base.table.td class="whitespace-nowrap">
                                {{ $record->medical_examiner_name ?? 'N/A' }}
                            </x-base.table.td>
                            <x-base.table.td class="whitespace-nowrap">
                                <span class="font-mono text-sm">{{ $record->medical_examiner_registry_number ?? 'N/A' }}</span>
                            </x-base.table.td>
                            <x-base.table.td class="whitespace-nowrap">
                                @if($record->medical_card_expiration_date)
                                {{ $expirationDate->format('M d, Y') }}
                                @else
                                <span class="text-slate-400">N/A</span>
                                @endif
                            </x-base.table.td>
                            <x-base.table.td class="whitespace-nowrap">
                                @if($record->medical_card_expiration_date)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                                @else
                                <span class="text-slate-400">N/A</span>
                                @endif
                            </x-base.table.td>
                            <x-base.table.td class="text-center">
                                @if($record->documents_count > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $record->documents_count }} {{ $record->documents_count == 1 ? 'document' : 'documents' }}
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    0 documents
                                </span>
                                @endif
                            </x-base.table.td>
                            <x-base.table.td class="whitespace-nowrap">
                                <x-base.menu>
                                    <x-base.menu.button as="x-base.button" variant="outline-secondary" size="sm">
                                        <x-base.lucide class="w-4 h-4" icon="more-horizontal" />
                                    </x-base.menu.button>
                                    <x-base.menu.items class="w-48">
                                        <x-base.menu.item as="a" href="{{ route('admin.medical-records.show', $record) }}">
                                            <x-base.lucide class="w-4 h-4 mr-2" icon="eye" />
                                            View Details
                                        </x-base.menu.item>
                                        <x-base.menu.item as="a" href="{{ route('admin.medical-records.edit', $record) }}">
                                            <x-base.lucide class="w-4 h-4 mr-2" icon="edit" />
                                            Edit
                                        </x-base.menu.item>
                                        @if($record->documents_count > 0)
                                        <x-base.menu.item as="a" href="{{ route('admin.medical-records.docs.all', $record->id) }}">
                                            <x-base.lucide class="w-4 h-4 mr-2" icon="file-text" />
                                            View Documents ({{ $record->documents_count }})
                                        </x-base.menu.item>
                                        <x-base.menu.divider />
                                        @endif
                                        <x-base.menu.item>
                                            <form action="{{ route('admin.medical-records.destroy', $record) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this medical record?')" class="w-full">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center w-full text-red-600 hover:text-red-700">
                                                    <x-base.lucide class="w-4 h-4 mr-2" icon="trash-2" />
                                                    Delete
                                                </button>
                                            </form>
                                        </x-base.menu.item>
                                    </x-base.menu.items>
                                </x-base.menu>
                            </x-base.table.td>
                            </x-base.table.tr>
                            @endforeach
                    </x-base.table.tbody>
                </x-base.table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-16">
                <x-base.lucide class="w-16 h-16 text-slate-300 mb-4" icon="file-x" />
                <h3 class="text-lg font-medium text-slate-500 mb-2">No medical records found</h3>
                <p class="text-slate-400 mb-6 text-center max-w-md">
                    No medical records match your current filters. Try adjusting your search criteria or create a new medical record.
                </p>
                <x-base.button as="a" href="{{ route('admin.medical-records.create') }}" variant="primary">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Add First Medical Record
                </x-base.button>
            </div>
            @endif
        </div>
        <!-- Pagination -->
        @if($medicalRecords->hasPages())
        <div class="w-full">
            {{ $medicalRecords->links('custom.pagination') }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Auto-submit form when driver filter changes
    document.getElementById('driver_filter').addEventListener('change', function() {
        this.form.submit();
    });
</script>
@endpush