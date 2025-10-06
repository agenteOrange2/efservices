@extends('../themes/' . $activeTheme)
@section('title', 'Driver Types')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Driver Types', 'active' => true],
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
            <h2 class="text-lg font-medium">
                Driver Types
            </h2>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <x-base.button as="a" href="{{ route('admin.vehicles.index') }}" class="w-full sm:w-auto">
                    <x-base.lucide class="w-4 h-4 mr-2" icon="plus" />
                    Go to Vehicles
                </x-base.button>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="box box--stacked mt-5 p-3">
            <div class="box-header">
                <h3 class="box-title">Filter Driver Types</h3>
            </div>
            <div class="box-body p-5">
                <form action="{{ route('admin.driver-types.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-base.form-label for="search_term">Search</x-base.form-label>
                        <x-base.form-input type="text" name="search_term" id="search_term" value="{{ request('search_term') }}" placeholder="Vehicle, driver, license..." />
                    </div>
                    <div>
                        <x-base.form-label for="ownership_filter">Ownership Type</x-base.form-label>
                        <select id="ownership_filter" name="ownership_filter" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Types</option>
                            <option value="owner_operator" {{ request('ownership_filter') == 'owner_operator' ? 'selected' : '' }}>Owner Operator</option>
                            <option value="third_party" {{ request('ownership_filter') == 'third_party' ? 'selected' : '' }}>Third Party</option>
                            <option value="company_driver" {{ request('ownership_filter') == 'company_driver' ? 'selected' : '' }}>Company Driver</option>
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

        <!-- Tabla de Driver Types -->
        <div class="box box--stacked mt-5">
            <div class="box-header p-3">
                <h3 class="box-title">Driver Types List ({{ $driverTypes->total() }} total)</h3>
            </div>
            <div class="box-body p-0">
                @if($driverTypes->count() > 0)
                    <div class="overflow-x-auto">
                        <x-base.table class="border-separate border-spacing-y-[10px]">
                            <x-base.table.thead>
                                <x-base.table.tr>
                                    <x-base.table.th class="whitespace-nowrap">Creation Date</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Vehicle</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Driver/Operator</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">Ownership Type</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap">License</x-base.table.th>
                                    <x-base.table.th class="whitespace-nowrap text-center">Actions</x-base.table.th>
                                </x-base.table.tr>
                            </x-base.table.thead>
                            <x-base.table.tbody>
                                @foreach($driverTypes as $driverType)
                                    <x-base.table.tr>
                                        <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                            {{ $driverType->created_at->format('M d, Y') }}
                                        </x-base.table.td>
                                        <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                            @if($driverType->details && $driverType->details->vehicle)
                                                <div class="font-medium">{{ $driverType->details->vehicle->unit_number ?? 'N/A' }}</div>
                                                <div class="text-slate-500 text-xs">{{ $driverType->details->vehicle->make }} {{ $driverType->details->vehicle->model }}</div>
                                            @else
                                                <span class="text-slate-400">No vehicle</span>
                                            @endif
                                        </x-base.table.td>
                                        <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                            @if($driverType->ownerOperatorDetail)
                                                <div class="font-medium">{{ $driverType->ownerOperatorDetail->owner_name }}</div>
                                                <div class="text-slate-500 text-xs">{{ $driverType->ownerOperatorDetail->owner_email }}</div>
                                            @elseif($driverType->thirdPartyDetail)
                                                <div class="font-medium">{{ $driverType->thirdPartyDetail->third_party_name }}</div>
                                                <div class="text-slate-500 text-xs">{{ $driverType->thirdPartyDetail->third_party_email }}</div>
                                            @else
                                                <span class="text-slate-400">No driver</span>
                                            @endif
                                        </x-base.table.td>
                                        <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                            @php
                                                $ownershipType = 'other';
                                                if($driverType->ownerOperatorDetail) {
                                                    $ownershipType = 'owner_operator';
                                                } elseif($driverType->thirdPartyDetail) {
                                                    $ownershipType = 'third_party';
                                                }
                                            @endphp
                                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                                @if($ownershipType == 'owner_operator') bg-blue-100 text-blue-800
                                                @elseif($ownershipType == 'third_party') bg-green-100 text-green-800
                                                @elseif($ownershipType == 'company_driver') bg-purple-100 text-purple-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $ownershipType)) }}
                                            </span>
                                        </x-base.table.td>
                                        <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b]">
                                            @if($driverType->ownerOperatorDetail && $driverType->ownerOperatorDetail->license_number)
                                                <div class="font-medium">{{ $driverType->ownerOperatorDetail->license_number }}</div>
                                                <div class="text-slate-500 text-xs">
                                                    Exp: {{ $driverType->ownerOperatorDetail->license_expiration ? \Carbon\Carbon::parse($driverType->ownerOperatorDetail->license_expiration)->format('M d, Y') : 'N/A' }}
                                                </div>
                                            @elseif($driverType->thirdPartyDetail && $driverType->thirdPartyDetail->license_number)
                                                <div class="font-medium">{{ $driverType->thirdPartyDetail->license_number }}</div>
                                                <div class="text-slate-500 text-xs">
                                                    Exp: {{ $driverType->thirdPartyDetail->license_expiration ? \Carbon\Carbon::parse($driverType->thirdPartyDetail->license_expiration)->format('M d, Y') : 'N/A' }}
                                                </div>
                                            @else
                                                <span class="text-slate-400">No license</span>
                                            @endif
                                        </x-base.table.td>
                                        <x-base.table.td class="px-6 py-4 first:rounded-l-md last:rounded-r-md bg-white border-b-0 dark:bg-darkmode-600 shadow-[20px_3px_20px_#0000000b] text-center">
                                            <div class="flex justify-center items-center gap-2">
                                                <x-base.button as="a" href="{{ route('admin.driver-types.show', $driverType->id) }}" variant="outline-primary" size="sm">
                                                    <x-base.lucide class="w-4 h-4" icon="eye" />
                                                </x-base.button>
                                                <x-base.button as="a" href="{{ route('admin.driver-types.edit', $driverType->id) }}" variant="outline-secondary" size="sm">
                                                    <x-base.lucide class="w-4 h-4" icon="edit" />
                                                </x-base.button>
                                                <form action="{{ route('admin.driver-types.destroy', $driverType->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this driver type assignment?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-base.button type="submit" variant="outline-danger" size="sm">
                                                        <x-base.lucide class="w-4 h-4" icon="trash-2" />
                                                    </x-base.button>
                                                </form>
                                            </div>
                                        </x-base.table.td>
                                    </x-base.table.tr>
                                @endforeach
                            </x-base.table.tbody>
                        </x-base.table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="p-5">
                        {{ $driverTypes->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="p-10 text-center">
                        <x-base.lucide class="w-16 h-16 text-slate-300 mx-auto" icon="database" />
                        <div class="text-xl font-medium text-slate-500 mt-3">No driver types found</div>
                        <div class="text-slate-400 mt-2">Try adjusting your search criteria or add a new driver type assignment.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection