<div>
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Registros de Mantenimiento</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            {{-- <a href="{{ route('admin.vehicles.maintenance.create') }}" class="btn btn-primary shadow-md mr-2">
                <i class="fas fa-plus mr-2"></i> Nuevo Mantenimiento
            </a> --}}
        </div>
    </div>
    
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
                    <option value="">Todos los Vehículos</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->company_unit_number ?? $vehicle->vin }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 mt-3 sm:mt-0 sm:ml-2">
                <select wire:model="maintenanceType" class="form-select w-full sm:w-auto">
                    <option value="">Todos los Tipos</option>
                    @foreach($maintenanceTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 mt-3 sm:mt-0 sm:ml-2">
                <select wire:model="status" class="form-select w-full sm:w-auto">
                    <option value="">Todos los Estados</option>
                    <option value="1">Completados</option>
                    <option value="0">Pendientes</option>
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
                                Vehículo
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
                                Tipo
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
                                Fecha
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
                                Próximo
                                @if($sortField === 'next_service_date')
                                    @if($sortDirection === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">Proveedor</th>
                        <th scope="col" class="px-6 py-3">
                            <a href="#" wire:click.prevent="sortBy('cost')">
                                Costo
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
                                Estado
                                @if($sortField === 'status')
                                    @if($sortDirection === 'asc')
                                        <i class="fas fa-sort-up"></i>
                                    @else
                                        <i class="fas fa-sort-down"></i>
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3">Acciones</th>
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
                                            <span class="badge bg-danger text-white">Vencido</span>
                                        @elseif($maintenance->isUpcoming())
                                            <span class="badge bg-warning text-white">Próximo</span>
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
                                                <span class="text-success">Completado</span>
                                            @else
                                                <span class="text-warning">Pendiente</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            </td>
                            <td class="table-report__action">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3" href="{{ route('admin.maintenance.edit', $maintenance->id) }}">
                                        <i class="fas fa-pencil-alt w-4 h-4 mr-1"></i> Editar
                                    </a>
                                    <a class="flex items-center text-danger" href="#"
                                        onclick="confirm('¿Está seguro de eliminar este registro?') || event.stopImmediatePropagation()"
                                        wire:click="delete({{ $maintenance->id }})">
                                        <i class="fas fa-trash-alt w-4 h-4 mr-1"></i> Eliminar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">No se encontraron registros de mantenimiento</td>
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