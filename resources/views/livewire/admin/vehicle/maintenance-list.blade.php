<div>
    <!-- Flash Messages -->
    @if (session()->has('message'))
    <div class="alert alert-success flex items-center mb-5">
        <x-base.lucide class="w-6 h-6 mr-2" icon="check-circle" />
        {{ session('message') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger flex items-center mb-5">
        <x-base.lucide class="w-6 h-6 mr-2" icon="alert-circle" />
        {{ session('error') }}
    </div>
    @endif

    <!-- Filtros y tabla de mantenimientos -->
    <div class="intro-y box p-5 mt-5">
        <!-- Filtros -->
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <div class="flex-1 mt-3 sm:mt-0">
                <div class="relative w-full sm:w-56 mx-auto">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-full box pr-10" placeholder="Search...">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="feather feather-search w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
            </div>
            <div class="flex-1 mt-3 sm:mt-0 sm:ml-2">
                <select wire:model.live="carrierId" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                    <option value="">All Carriers</option>
                    @foreach ($carriers as $carrier)
                        <option value="{{ $carrier->id }}">{{ $carrier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 mt-3 sm:mt-0 sm:ml-2">
                <select wire:model.live="status" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                    <option value="">All States</option>
                    <option value="1">Completed</option>
                    <option value="0">Pending</option>
                </select>
            </div>
        </div>

        <!-- Tabla de Mantenimientos -->
        <div class="overflow-x-auto mt-5">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            <a href="#" wire:click.prevent="sortBy('vehicle_id')">
                                Vehicle
                                @if ($sortField === 'vehicle_id')
                                    @if ($sortDirection === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <a href="#" wire:click.prevent="sortBy('service_tasks')">
                                Type
                                @if ($sortField === 'service_tasks')
                                    @if ($sortDirection === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <a href="#" wire:click.prevent="sortBy('service_date')">
                                Date
                                @if ($sortField === 'service_date')
                                    @if ($sortDirection === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <a href="#" wire:click.prevent="sortBy('next_service_date')">
                                Next
                                @if ($sortField === 'next_service_date')
                                    @if ($sortDirection === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <a href="#" wire:click.prevent="sortBy('cost')">
                                Cost
                                @if ($sortField === 'cost')
                                    @if ($sortDirection === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <a href="#" wire:click.prevent="sortBy('status')">
                                Status
                                @if ($sortField === 'status')
                                    @if ($sortDirection === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maintenances as $maintenance)
                        <tr wire:key="maintenance-{{ $maintenance->id }}"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div
                                            class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $maintenance->vehicle ? $maintenance->vehicle->make . ' ' . $maintenance->vehicle->model : 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $maintenance->vehicle ? $maintenance->vehicle->company_unit_number ?? $maintenance->vehicle->vin : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">{{ $maintenance->service_tasks }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900">{{ $maintenance->service_date->format('d/m/Y') }}</span>
                                    <span class="text-xs text-gray-500">Service Date</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($maintenance->next_service_date)
                                    <div
                                        class="{{ $maintenance->isOverdue() ? 'text-danger' : ($maintenance->isUpcoming() ? 'text-warning' : 'text-success') }}">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-yellow-600">{{ $maintenance->next_service_date->format('m/d/Y') }}</span>
                                            @if ($maintenance->isOverdue())
                                                <span class="my-2 rounded-lg border border-white/20 bg-pending/80 px-2.5 py-1 text-xs font-medium text-white">Expires</span>
                                            @elseif($maintenance->isUpcoming())
                                                <span class="my-2 text-center rounded-lg border border-white/20 bg-pending/80 px-2.5 py-1 text-xs font-medium text-white">Next</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">${{ number_format($maintenance->cost, 2) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <div class="flex items-center">
                                        <label for="toggle-{{ $maintenance->id }}"
                                            class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" id="toggle-{{ $maintenance->id }}"
                                                class="sr-only peer"
                                                wire:click="toggleStatus({{ $maintenance->id }})"
                                                @if ($maintenance->status) checked @endif>
                                            <div
                                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                            </div>
                                        </label>
                                        <span
                                            class="ml-2 text-xs font-medium {{ $maintenance->status ? 'text-green-600' : 'text-amber-600' }}">
                                            {{ $maintenance->status ? 'Completed' : 'Pending' }}
                                        </span>
                                    </div>
                                </div>

                            </td>
                            <td class="table-report__action">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3"
                                        href="{{ route('admin.maintenance.edit', $maintenance->id) }}">
                                        <i class="fas fa-pencil-alt w-4 h-4 mr-1"></i> Edit
                                    </a>
                                    <a class="flex items-center text-danger" href="#"
                                        onclick="if(confirm('¿Está seguro de eliminar este registro?')) { 
                                            document.getElementById('delete-form-{{ $maintenance->id }}').submit(); 
                                        }">
                                        <i class="fas fa-trash-alt w-4 h-4 mr-1"></i> Delete
                                    </a>
                                    <form id="delete-form-{{ $maintenance->id }}"
                                        action="{{ route('admin.maintenance.destroy', $maintenance->id) }}"
                                        method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">No maintenance records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="mt-5">
            {{ $maintenances->links() }}
        </div>
    </div>
</div>
