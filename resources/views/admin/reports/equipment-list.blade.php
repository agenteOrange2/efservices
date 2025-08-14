@extends('../themes/' . $activeTheme)
@section('title', 'Reporte de Equipamiento')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Reports', 'url' => route('admin.reports.index')],
        ['label' => 'Equipment List Report', 'active' => true],
    ];
    // Definir el tab actual
    $currentTab = request('tab', 'all');
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium">
                    Equipment List Report
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.reports.index') }}" class="w-full sm:w-auto"
                        variant="outline-primary">
                        <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                        Back to Reports
                    </x-base.button>
                </div>
            </div>
            <div class="mt-3.5 flex flex-col gap-8">
                <div class="box box--stacked flex flex-col p-5">
                    <div class="grid grid-cols-4 gap-5">
                        <!-- Clickable tab cards -->
                        <a href="{{ route('admin.reports.equipment-list', ['tab' => 'all'] + request()->except('tab', 'page')) }}"
                            class="box col-span-4 rounded-[0.6rem] border border-dashed {{ $currentTab == 'all' ? 'border-primary/80 bg-primary/5' : 'border-slate-300/80' }} p-5 shadow-sm md:col-span-2 xl:col-span-1 hover:border-primary/60 hover:bg-primary/5 transition-all duration-150 ease-in-out cursor-pointer">
                            <div class="text-base {{ $currentTab == 'all' ? 'text-primary' : 'text-slate-500' }}">Total
                                Vehicles</div>
                            <div class="mt-1.5 text-2xl font-medium">{{ $totalVehiclesCount }}</div>
                            <div class="absolute inset-y-0 right-0 mr-5 flex flex-col justify-center">
                                <div
                                    class="flex items-center rounded-full border border-success/10 bg-success/10 py-[2px] pl-[7px] pr-1 text-xs font-medium text-success">
                                    <i data-lucide="Truck" class="ml-px h-4 w-4 stroke-[1.5] mr-1"></i>
                                    All
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('admin.reports.equipment-list', ['tab' => 'active'] + request()->except('tab', 'page')) }}"
                            class="box col-span-4 rounded-[0.6rem] border border-dashed {{ $currentTab == 'active' ? 'border-primary/80 bg-primary/5' : 'border-slate-300/80' }} p-5 shadow-sm md:col-span-2 xl:col-span-1 hover:border-primary/60 hover:bg-primary/5 transition-all duration-150 ease-in-out cursor-pointer">
                            <div class="text-base {{ $currentTab == 'active' ? 'text-primary' : 'text-slate-500' }}">Active
                                Vehicles</div>
                            <div class="mt-1.5 text-2xl font-medium">{{ $activeVehiclesCount }}</div>
                            <div class="absolute inset-y-0 right-0 mr-5 flex flex-col justify-center">
                                <div
                                    class="flex items-center rounded-full border border-success/10 bg-success/10 py-[2px] pl-[7px] pr-1 text-xs font-medium text-success">
                                    <i data-lucide="CheckCircle" class="ml-px h-4 w-4 stroke-[1.5] mr-1"></i>
                                    Actives
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('admin.reports.equipment-list', ['tab' => 'out_of_service'] + request()->except('tab', 'page')) }}"
                            class="box col-span-4 rounded-[0.6rem] border border-dashed {{ $currentTab == 'out_of_service' ? 'border-primary/80 bg-primary/5' : 'border-slate-300/80' }} p-5 shadow-sm md:col-span-2 xl:col-span-1 hover:border-primary/60 hover:bg-primary/5 transition-all duration-150 ease-in-out cursor-pointer">
                            <div class="text-base {{ $currentTab == 'out_of_service' ? 'text-primary' : 'text-slate-500' }}">
                                Out of Service</div>
                            <div class="mt-1.5 text-2xl font-medium">{{ $outOfServiceVehiclesCount }}</div>
                            <div class="absolute inset-y-0 right-0 mr-5 flex flex-col justify-center">
                                <div
                                    class="flex items-center rounded-full border border-danger/10 bg-danger/10 py-[2px] pl-[7px] pr-1 text-xs font-medium text-danger">
                                    <i data-lucide="AlertTriangle" class="ml-px h-4 w-4 stroke-[1.5] mr-1"></i>
                                    Out of Service Vehicles
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('admin.reports.equipment-list', ['tab' => 'suspended'] + request()->except('tab', 'page')) }}"
                            class="box col-span-4 rounded-[0.6rem] border border-dashed {{ $currentTab == 'suspended' ? 'border-primary/80 bg-primary/5' : 'border-slate-300/80' }} p-5 shadow-sm md:col-span-2 xl:col-span-1 hover:border-primary/60 hover:bg-primary/5 transition-all duration-150 ease-in-out cursor-pointer">
                            <div class="text-base {{ $currentTab == 'suspended' ? 'text-primary' : 'text-slate-500' }}">Suspended
                                Vehicles</div>
                            <div class="mt-1.5 text-2xl font-medium">{{ $suspendedVehiclesCount }}</div>
                            <div class="absolute inset-y-0 right-0 mr-5 flex flex-col justify-center">
                                <div
                                    class="flex items-center rounded-full border border-warning/10 bg-warning/10 py-[2px] pl-[7px] pr-1 text-xs font-medium text-warning">
                                    <i data-lucide="MinusCircle" class="ml-px h-4 w-4 stroke-[1.5] mr-1"></i>
                                    Suspended
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="box box--stacked flex flex-col">
                    <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                        <div>
                            <div class="relative">
                                <form action="{{ route('admin.reports.equipment-list') }}" method="GET" id="search-form">
                                    <input type="hidden" name="tab" value="{{ $currentTab }}">
                                    @if (!empty($carrierFilter))
                                        <input type="hidden" name="carrier" value="{{ $carrierFilter }}">
                                    @endif
                                    <x-base.lucide
                                        class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
                                        icon="Search" />
                                    <x-base.form-input class="rounded-[0.5rem] pl-9 sm:w-64" type="text" name="search"
                                        value="{{ $search }}" placeholder="Search vehicles..."
                                        onchange="this.form.submit()" />
                                </form>
                            </div>
                        </div>
                        <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                            <!-- Botón de exportación PDF -->
                            <x-base.button id="export-pdf-inline" variant="outline-secondary" class="w-full sm:w-auto mr-2">
                                <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Download" />
                                Export PDF
                            </x-base.button>
                            
                            <x-base.popover class="inline-block">
                                <x-base.popover.button class="w-full sm:w-auto" as="x-base.button"
                                    variant="outline-secondary">
                                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowDownWideNarrow" />
                                    Filter by Carrier
                                    <span
                                        class="ml-2 flex h-5 items-center justify-center rounded-full border bg-slate-100 px-1.5 text-xs font-medium">
                                        {{ !empty($carrierFilter) ? '1' : '0' }}
                                    </span>
                                </x-base.popover.button>
                                <x-base.popover.panel>
                                    <div class="p-2">
                                        <form method="GET" action="{{ route('admin.reports.equipment-list') }}">
                                            @if (!empty($search))
                                                <input type="hidden" name="search" value="{{ $search }}">
                                            @endif
                                            <input type="hidden" name="tab" value="{{ $currentTab }}">
                                            <div>
                                                <div class="text-left text-slate-500">
                                                    Carrier
                                                </div>
                                                <x-base.form-select name="carrier" class="mt-2 flex-1">
                                                    <option value="">All Carriers</option>
                                                    @foreach ($carriers as $carrier)
                                                        <option value="{{ $carrier->id }}"
                                                            {{ $carrierFilter == $carrier->id ? 'selected' : '' }}>
                                                            {{ $carrier->name }}
                                                        </option>
                                                    @endforeach
                                                </x-base.form-select>
                                            </div>

                                            <div class="mt-3">
                                                <div class="text-left text-slate-500">
                                                    Date Range
                                                </div>
                                                <div class="grid grid-cols-2 gap-2 mt-2">
                                                    <div>
                                                        <label class="form-label">From</label>
                                                        <x-base.form-input type="date" name="date_from"
                                                            value="{{ request('date_from') }}" class="form-control" />
                                                    </div>
                                                    <div>
                                                        <label class="form-label">To</label>
                                                        <x-base.form-input type="date" name="date_to"
                                                            value="{{ request('date_to') }}" class="form-control" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4 flex items-center">
                                                <x-base.button class="ml-auto w-32" variant="secondary" as="a"
                                                    href="{{ route('admin.reports.equipment-list', ['tab' => $currentTab]) }}">
                                                    Clear
                                                </x-base.button>
                                                <x-base.button class="ml-2 w-32" variant="primary" type="submit">
                                                    Apply
                                                </x-base.button>
                                            </div>
                                        </form>
                                    </div>
                                </x-base.popover.panel>
                            </x-base.popover>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Equipment List</h3>
                                @if ($vehicles->count() > 0)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $vehicles->total() }} vehicle{{ $vehicles->total() !== 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if ($vehicles->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Type
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Make/Model
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Year
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                VIN
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Registration Status
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Registration Expiration
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Carrier
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Driver
                                            </th>

                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($vehicles as $vehicle)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $vehicle->vehicleType ? $vehicle->vehicleType->name : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @if($vehicle->vehicleMake)
                                                        {{ $vehicle->vehicleMake->name }} {{ $vehicle->model }}
                                                    @else
                                                        {{ $vehicle->model ?? 'N/A' }}
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $vehicle->year ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $vehicle->vin ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @if(isset($vehicle->registration_expiration_date))
                                                        @php
                                                            $expirationDate = \Carbon\Carbon::parse($vehicle->registration_expiration_date);
                                                            $today = \Carbon\Carbon::today();
                                                            $status = $expirationDate->isFuture() ? 'active' : 'expired';
                                                        @endphp
                                                        @if($status == 'active')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                Active
                                                            </span>
                                                        @elseif($status == 'expired')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                Expired
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-sm text-gray-500">Not Available</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $vehicle->registration_expiration_date ? date('Y-m-d', strtotime($vehicle->registration_expiration_date)) : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($vehicle->carrier)
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $vehicle->carrier->name }}
                                                        </span>
                                                    @else
                                                        <span class="text-sm text-gray-500">Not assigned</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @if($vehicle->driver && $vehicle->driver->user)
                                                        {{ $vehicle->driver->user->name }}
                                                    @else
                                                        <span class="text-muted">Not Assigned</span>
                                                    @endif
                                                </td>

                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('admin.admin-vehicles.show', $vehicle->id) }}"
                                                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            @if ($vehicles->hasPages())
                                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 flex justify-between sm:hidden">
                                            @if ($vehicles->onFirstPage())
                                                <span
                                                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                                    Previous
                                                </span>
                                            @else
                                                <a href="{{ $vehicles->previousPageUrl() }}"
                                                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500">
                                                    Previous
                                                </a>
                                            @endif

                                            @if ($vehicles->hasMorePages())
                                                <a href="{{ $vehicles->nextPageUrl() }}"
                                                    class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500">
                                                    Next
                                                </a>
                                            @else
                                                <span
                                                    class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md">
                                                    Next
                                                </span>
                                            @endif
                                        </div>

                                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-sm text-gray-700">
                                                    Showing
                                                    <span class="font-medium">{{ $vehicles->firstItem() }}</span>
                                                    to
                                                    <span class="font-medium">{{ $vehicles->lastItem() }}</span>
                                                    of
                                                    <span class="font-medium">{{ $vehicles->total() }}</span>
                                                    results
                                                </p>
                                            </div>
                                            <div>
                                                {{ $vehicles->appends(request()->query())->links() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No vehicles found</h3>
                                <p class="mt-1 text-sm text-gray-500">No vehicles found with the applied filters.</p>
                                <div class="mt-6">
                                    <a href="{{ route('admin.reports.equipment-list') }}"
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        Clear filters
                                    </a>
                                </div>
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
            const exportPdfInlineBtn = document.getElementById('export-pdf-inline');

            function getExportUrl() {
                const params = new URLSearchParams(window.location.search);

                // Eliminar el parámetro page si existe
                if (params.has('page')) {
                    params.delete('page');
                }

                // Asegurar que todos los parámetros relevantes se incluyan
                // El currentTab se encuentra en la URL o se usa 'all' por defecto
                if (!params.has('tab')) {
                    const currentTab = '{{ $currentTab }}';
                    params.append('tab', currentTab);
                }

                // La búsqueda y el carrier filter ya están en los params desde window.location.search

                let url = '{{ route('admin.reports.equipment-list.pdf') }}';
                const queryString = params.toString();

                return queryString ? `${url}?${queryString}` : url;
            }

            function handleExport() {
                const button = this;
                const originalHTML = button.innerHTML;

                button.innerHTML = `
                <svg class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a8 8 0 110 15.292V15.25a6 6 0 10-3.672-9.118A6 6 0 0012 4.354z" />
                </svg>
                Generando...
            `;
                button.disabled = true;

                window.open(getExportUrl(), '_blank');

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.disabled = false;
                }, 2000);
            }

            if (exportPdfInlineBtn) {
                exportPdfInlineBtn.addEventListener('click', handleExport);
            }
        });
    </script>
@endpush

@endsection
