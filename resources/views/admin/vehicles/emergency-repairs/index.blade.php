@extends('../themes/' . $activeTheme)
@section('title', 'Emergency Repairs')
@php
    $breadcrumbLinks = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
        ['label' => 'Emergency Repairs', 'active' => true],
    ];
@endphp
@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Emergency Repairs
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.vehicles.emergency-repairs.create') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PlusCircle" />
                        New Emergency Repair
                    </x-base.button>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="intro-y box p-5 mt-5">
                <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                    <div class="font-medium text-base truncate">Filters</div>
                </div>

                <form method="GET" action="{{ route('admin.vehicles.emergency-repairs.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Carrier Filter -->
                    <div>
                        <x-base.form-label for="carrier_id">Carrier</x-base.form-label>
                        <select id="carrier_id" name="carrier_id" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Carriers</option>
                            @foreach ($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Driver Filter -->
                    <div>
                        <x-base.form-label for="driver_id">Driver</x-base.form-label>
                        <select id="driver_id" name="driver_id" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Drivers</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->first_name }} {{ $driver->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <x-base.form-label for="status">Status</x-base.form-label>
                        <select id="status" name="status" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div>
                        <x-base.form-label for="search">Search</x-base.form-label>
                        <x-base.form-input id="search" name="search" type="text" class="w-full" 
                            placeholder="Search by repair name, vehicle..." value="{{ request('search') }}" />
                    </div>

                    <!-- Date Range -->
                    <div>
                        <x-base.form-label for="start_date">Start Date</x-base.form-label>
                        <x-base.litepicker id="start_date" name="start_date" value="{{ request('start_date') }}"
                            placeholder="MM/DD/YYYY" />
                    </div>

                    <div>
                        <x-base.form-label for="end_date">End Date</x-base.form-label>
                        <x-base.litepicker id="end_date" name="end_date" value="{{ request('end_date') }}"
                            placeholder="MM/DD/YYYY" />
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-end gap-2">
                        <x-base.button type="submit" variant="primary" class="w-full">
                            <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Search" />
                            Filter
                        </x-base.button>
                    </div>

                    <div class="flex items-end">
                        <x-base.button as="a" href="{{ route('admin.vehicles.emergency-repairs.index') }}" 
                            variant="outline-secondary" class="w-full">
                            <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="RotateCcw" />
                            Clear
                        </x-base.button>
                    </div>
                </form>
            </div>

            <!-- Emergency Repairs List -->
            <div class="box box--stacked mt-5">
                <div class="box-body p-5">
                    <div class="overflow-x-auto">
                        <h2 class="text-lg font-medium truncate mr-5 mb-5">Emergency Repairs List</h2>
                        
                        @if($emergencyRepairs->count() > 0)
                            <table class="table table-striped w-full text-left">
                                <thead>
                                    <tr>
                                        <th class="whitespace-nowrap">Repair Name</th>
                                        <th class="whitespace-nowrap">Vehicle</th>
                                        <th class="whitespace-nowrap">Carrier</th>
                                        <th class="whitespace-nowrap">Driver</th>
                                        <th class="whitespace-nowrap">Repair Date</th>
                                        <th class="whitespace-nowrap">Cost</th>
                                        <th class="whitespace-nowrap">Status</th>
                                        <th class="whitespace-nowrap text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emergencyRepairs as $repair)
                                        <tr>
                                            <td class="font-medium">{{ $repair->repair_name }}</td>
                                            <td>
                                                <div class="font-medium">{{ $repair->vehicle->make }} {{ $repair->vehicle->model }}</div>
                                                <div class="text-slate-500 text-xs mt-0.5">
                                                    {{ $repair->vehicle->company_unit_number ?? $repair->vehicle->vin }}
                                                </div>
                                            </td>
                                            <td>{{ $repair->vehicle->carrier->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($repair->vehicle->driver)
                                                    {{ $repair->vehicle->driver->first_name }} {{ $repair->vehicle->driver->last_name }}
                                                @else
                                                    <span class="text-slate-500">No Driver</span>
                                                @endif
                                            </td>
                                            <td>{{ $repair->repair_date->format('M d, Y') }}</td>
                                            <td class="font-medium">${{ number_format($repair->cost, 2) }}</td>
                                            <td>
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'bg-warning/20 text-warning',
                                                        'in_progress' => 'bg-primary/20 text-primary',
                                                        'completed' => 'bg-success/20 text-success'
                                                    ];
                                                @endphp
                                                <div class="flex items-center justify-center {{ $statusClasses[$repair->status] ?? 'bg-slate-100 text-slate-500' }} rounded-full px-2 py-1 text-xs font-medium">
                                                    {{ ucfirst(str_replace('_', ' ', $repair->status)) }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <x-base.tippy content="View Details">
                                                        <a href="{{ route('admin.vehicles.emergency-repairs.show', $repair) }}" 
                                                           class="flex items-center text-primary hover:text-primary/70">
                                                            <x-base.lucide class="w-4 h-4" icon="Eye" />
                                                        </a>
                                                    </x-base.tippy>
                                                    
                                                    <x-base.tippy content="Edit">
                                                        <a href="{{ route('admin.vehicles.emergency-repairs.edit', $repair) }}" 
                                                           class="flex items-center text-slate-500 hover:text-slate-700">
                                                            <x-base.lucide class="w-4 h-4" icon="Edit" />
                                                        </a>
                                                    </x-base.tippy>
                                                    
                                                    <x-base.tippy content="Delete">
                                                        <form action="{{ route('admin.vehicles.emergency-repairs.destroy', $repair) }}" 
                                                              method="POST" class="inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this emergency repair?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="flex items-center text-danger hover:text-danger/70">
                                                                <x-base.lucide class="w-4 h-4" icon="Trash2" />
                                                            </button>
                                                        </form>
                                                    </x-base.tippy>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            <!-- Pagination -->
                            <div class="mt-5">
                                {{ $emergencyRepairs->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="text-center py-10">
                                <div class="text-slate-500 text-lg mb-3">
                                    <x-base.lucide class="w-16 h-16 mx-auto mb-4 text-slate-300" icon="Wrench" />
                                    No emergency repairs found
                                </div>
                                <p class="text-slate-400 mb-5">There are no emergency repairs matching your criteria.</p>
                                <x-base.button as="a" href="{{ route('admin.vehicles.emergency-repairs.create') }}" variant="primary">
                                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PlusCircle" />
                                    Create First Emergency Repair
                                </x-base.button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const carrierSelect = document.getElementById('carrier_id');
                const driverSelect = document.getElementById('driver_id');

                // Load drivers when carrier changes
                carrierSelect.addEventListener('change', function () {
                    const carrierId = this.value;
                    
                    // Clear driver options
                    driverSelect.innerHTML = '<option value="">All Drivers</option>';
                    
                    if (carrierId) {
                        fetch(`{{ route('admin.vehicles.emergency-repairs.index') }}/drivers-by-carrier?carrier_id=${carrierId}`)
                            .then(response => response.json())
                            .then(drivers => {
                                drivers.forEach(driver => {
                                    const option = document.createElement('option');
                                    option.value = driver.id;
                                    option.textContent = `${driver.first_name} ${driver.last_name}`;
                                    driverSelect.appendChild(option);
                                });
                            })
                            .catch(error => console.error('Error loading drivers:', error));
                    }
                });
            });
        </script>
    @endpush
@endsection