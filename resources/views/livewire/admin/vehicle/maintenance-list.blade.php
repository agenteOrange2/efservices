<div>            
    <!-- Filtros y tabla de mantenimientos -->
    <div class="intro-y box p-5 mt-5">
        <!-- Filtros -->
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <div class="flex-1 mt-3 sm:mt-0">
                <div class="relative w-full sm:w-56 mx-auto">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control w-full box pr-10" placeholder="Buscar...">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
            </div>
            <div class="flex-1 mt-3 sm:mt-0 sm:ml-2">
                <select wire:model="vehicleId" class="form-select w-full sm:w-auto">
                    <option value="">All Vehicles</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->company_unit_number ?? $vehicle->vin }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 mt-3 sm:mt-0 sm:ml-2">
                <select wire:model="maintenanceType" class="form-select w-full sm:w-auto">
                    <option value="">All Types</option>
                    @foreach($maintenanceTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 mt-3 sm:mt-0 sm:ml-2">
                <select wire:model="status" class="form-select w-full sm:w-auto">
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
                                @if($sortField === 'vehicle_id')
                                    @if($sortDirection === 'asc')
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
                                @if($sortField === 'service_tasks')
                                    @if($sortDirection === 'asc')
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
                                @if($sortField === 'service_date')
                                    @if($sortDirection === 'asc')
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
                                @if($sortField === 'next_service_date')
                                    @if($sortDirection === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">Supplier</th>
                        <th scope="col" class="px-6 py-3">
                            <a href="#" wire:click.prevent="sortBy('cost')">
                                Cost
                                @if($sortField === 'cost')
                                    @if($sortDirection === 'asc')
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
                                @if($sortField === 'status')
                                    @if($sortDirection === 'asc')
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
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <td class="px-6 py-4">
                                {{ $maintenance->vehicle->make }} {{ $maintenance->vehicle->model }}
                                <div class="text-slate-500 text-xs mt-0.5">{{ $maintenance->vehicle->company_unit_number ?? $maintenance->vehicle->vin }}</div>
                            </td>
                            <td class="px-6 py-4">{{ $maintenance->service_tasks }}</td>
                            <td class="px-6 py-4">{{ $maintenance->service_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                @if($maintenance->next_service_date)
                                    <div class="{{ $maintenance->isOverdue() ? 'text-danger' : ($maintenance->isUpcoming() ? 'text-warning' : 'text-success') }}">
                                        {{ $maintenance->next_service_date->format('d/m/Y') }}
                                        @if($maintenance->isOverdue())
                                            <span class="badge bg-danger text-white">Expires</span>
                                        @elseif($maintenance->isUpcoming())
                                            <span class="badge bg-warning text-white">Next</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $maintenance->vendor_mechanic }}</td>
                            <td class="px-6 py-4">${{ number_format($maintenance->cost, 2) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               wire:click="toggleStatus({{ $maintenance->id }})"
                                               @if($maintenance->status) checked @endif>
                                        <label class="form-check-label">
                                            @if($maintenance->status)
                                                <span class="text-success">Completed</span>
                                            @else
                                                <span class="text-warning">Pending</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td class="table-report__action">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3" href="{{ route('admin.maintenance.edit', $maintenance->id) }}">
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