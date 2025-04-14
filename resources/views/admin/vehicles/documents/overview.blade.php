@extends('../themes/' . $activeTheme)
@section('title', 'Vehicle Documents Overview')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
    ['label' => 'Documents Overview', 'active' => true],
];
@endphp
@section('subcontent')
<div class="grid grid-cols-1 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">
                Vehicle Documents Overview
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.vehicles.index') }}"
                    class="w-full sm:w-auto" variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="Truck" />
                    All Vehicles
                </x-base.button>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="mt-5">
            <div class="box box--stacked">
                <div class="box-header bg-slate-50 p-5 border-b border-slate-200/60">
                    <div class="flex flex-col gap-y-3 md:flex-row md:items-center">
                        <div class="box-title font-medium">Filters</div>
                    </div>
                </div>
                <div class="box-body p-5">
                    <form action="{{ route('admin.vehicles-documents.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        {{-- Carrier Filter --}}
                        <div>
                            <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                            <x-base.form-select id="carrier_id" name="carrier_id">
                                <option value="">All Carriers</option>
                                @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }}
                                </option>
                                @endforeach
                            </x-base.form-select>
                        </div>

                        {{-- Vehicle Status Filter --}}
                        <div>
                            <x-base.form-label for="status">Vehicle Status</x-base.form-label>
                            <x-base.form-select id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="out_of_service" {{ request('status') == 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </x-base.form-select>
                        </div>

                        {{-- Document Type Filter --}}
                        <div>
                            <x-base.form-label for="document_type">Document Type</x-base.form-label>
                            <x-base.form-select id="document_type" name="document_type">
                                <option value="">All Document Types</option>
                                @foreach($documentTypes as $value => $label)
                                <option value="{{ $value }}" {{ request('document_type') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </x-base.form-select>
                        </div>

                        {{-- Document Status Filter --}}
                        <div>
                            <x-base.form-label for="document_status">Document Status</x-base.form-label>
                            <x-base.form-select id="document_status" name="document_status">
                                <option value="">All Document Statuses</option>
                                @foreach($documentStatuses as $value => $label)
                                <option value="{{ $value }}" {{ request('document_status') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </x-base.form-select>
                        </div>

                        <div class="md:col-span-4 flex justify-end">
                            <x-base.button type="submit" variant="primary" class="w-full md:w-auto">
                                <x-base.lucide class="mr-2 h-4 w-4" icon="Search" />
                                Apply Filters
                            </x-base.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Document Status Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mt-5">
            <div class="box box--stacked p-5 bg-white rounded-lg shadow">
                <div class="flex items-center">
                    <div class="rounded-full bg-success/20 p-3 mr-3">
                        <x-base.lucide class="h-5 w-5 text-success" icon="CheckCircle" />
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Active Documents</div>
                        <div class="font-medium text-xl">
                            {{ $vehicles->reduce(function ($carry, $vehicle) {
                                return $carry + $vehicle->documents->where('status', 'active')->count();
                            }, 0) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box--stacked p-5 bg-white rounded-lg shadow">
                <div class="flex items-center">
                    <div class="rounded-full bg-danger/20 p-3 mr-3">
                        <x-base.lucide class="h-5 w-5 text-danger" icon="AlertOctagon" />
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Expired Documents</div>
                        <div class="font-medium text-xl">
                            {{ $vehicles->reduce(function ($carry, $vehicle) {
                                return $carry + $vehicle->documents->where('status', 'expired')->count();
                            }, 0) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box--stacked p-5 bg-white rounded-lg shadow">
                <div class="flex items-center">
                    <div class="rounded-full bg-warning/20 p-3 mr-3">
                        <x-base.lucide class="h-5 w-5 text-warning" icon="Clock" />
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Pending Documents</div>
                        <div class="font-medium text-xl">
                            {{ $vehicles->reduce(function ($carry, $vehicle) {
                                return $carry + $vehicle->documents->where('status', 'pending')->count();
                            }, 0) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box--stacked p-5 bg-white rounded-lg shadow">
                <div class="flex items-center">
                    <div class="rounded-full bg-primary/20 p-3 mr-3">
                        <x-base.lucide class="h-5 w-5 text-primary" icon="FileText" />
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Total Documents</div>
                        <div class="font-medium text-xl">
                            {{ $vehicles->reduce(function ($carry, $vehicle) {
                                return $carry + $vehicle->documents->count();
                            }, 0) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vehicles Documents Table --}}
        <div class="mt-7">
            <div class="box box--stacked">
                <div class="box-header bg-slate-50 p-5 border-b border-slate-200/60">
                    <div class="flex flex-col gap-y-3 md:flex-row md:items-center">
                        <div class="box-title font-medium">Vehicles Documents</div>
                        <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                            {{-- <div class="relative">
                                <input type="text" class="form-input pl-9 w-full sm:w-64" placeholder="Search vehicles..." id="vehicle-search">
                                <x-base.lucide class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-400" icon="Search" />
                            </div>--}}
                            <div class="relative"> 
                                <x-base.lucide
                                    class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                    icon="Search" />
                                <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" id="vehicle-search" type="text"
                                    placeholder="Search vehicles..." />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-body p-5">
                    @if($vehicles->isEmpty())
                    <div class="text-center py-10">
                        <x-base.lucide class="h-12 w-12 mx-auto text-slate-300" icon="Truck" />
                        <div class="mt-3 text-slate-500">No vehicles found</div>
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400" id="vehicles-table">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Vehicle</th>
                                    <th scope="col" class="px-6 py-3">Carrier</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3">Documents</th>
                                    <th scope="col" class="px-6 py-3">Expired</th>
                                    <th scope="col" class="px-6 py-3">Expiring Soon</th>
                                    <th class="whitespace-nowrap text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicles as $vehicle)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 vehicle-row">
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ $vehicle->make }} {{ $vehicle->model }}</div>
                                        <div class="text-slate-500 text-xs">{{ $vehicle->year }} • {{ $vehicle->vin }}</div>
                                    </td>
                                    <td class="px-6 py-4">{{ $vehicle->carrier->name }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center whitespace-nowrap">
                                            <div class="w-2 h-2 rounded-full mr-2 {{ $vehicle->out_of_service ? 'bg-danger' : ($vehicle->suspended ? 'bg-warning' : 'bg-success') }}">
                                            </div>
                                            {{ $vehicle->out_of_service ? 'Out of Service' : ($vehicle->suspended ? 'Suspended' : 'Active') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="bg-primary/10 text-primary rounded-full p-1 mr-2">
                                                <x-base.lucide class="h-4 w-4" icon="FileText" />
                                            </div>
                                            {{ $vehicle->documents->count() }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $expiredCount = $vehicle->documents->where('status', 'expired')->count();
                                        @endphp
                                        @if($expiredCount > 0)
                                            <div class="flex items-center">
                                                <div class="bg-danger/10 text-danger rounded-full p-1 mr-2">
                                                    <x-base.lucide class="h-4 w-4" icon="AlertOctagon" />
                                                </div>
                                                {{ $expiredCount }}
                                            </div>
                                        @else
                                        <div class="flex items-center">
                                            <div class="bg-danger/10 text-danger rounded-full p-1 mr-2">
                                                <x-base.lucide class="h-4 w-4" icon="AlertOctagon" />
                                            </div>
                                            0
                                        </div>                                            
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $expiringCount = $vehicle->documents->filter(function($doc) {
                                                return $doc->isAboutToExpire() && !$doc->isExpired();
                                            })->count();
                                        @endphp
                                        @if($expiringCount > 0)
                                            <div class="flex items-center">
                                                <div class="bg-warning/10 text-warning rounded-full p-1 mr-2">
                                                    <x-base.lucide class="h-4 w-4" icon="Clock" />
                                                </div>
                                                {{ $expiringCount }}
                                            </div>
                                        @else
                                        <div class="flex items-center">
                                            <div class="bg-warning/10 text-warning rounded-full p-1 mr-2">
                                                <x-base.lucide class="h-4 w-4" icon="Clock" />
                                            </div>                                            
                                            <div class="text-slate-400">0</div>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="flex justify-center space-x-1">
                                            <a href="{{ route('admin.vehicles.documents.index', $vehicle->id) }}"
                                                class="btn btn-primary btn-sm">
                                                <x-base.lucide class="h-4 w-4" icon="FileText" />
                                            </a>
                                            <a href="{{ route('admin.vehicles.show', $vehicle->id) }}"
                                                class="btn btn-secondary btn-sm">
                                                <x-base.lucide class="h-4 w-4" icon="Truck" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-5">
                        {{ $vehicles->withQueryString()->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filtro de búsqueda de vehículos
        const searchInput = document.getElementById('vehicle-search');
        const rows = document.querySelectorAll('.vehicle-row');
        
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });
</script>
@endpush
@endsection
