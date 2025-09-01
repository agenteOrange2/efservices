@extends('../themes/' . $activeTheme)

@section('title', 'Maintenance Reports')

@php
$breadcrumbLinks = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
    ['label' => 'Maintenance', 'url' => route('admin.maintenance.index')],
    ['label' => 'Reports', 'active' => true],
];
@endphp

@section('subcontent')
<div class="grid grid-cols-2 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Maintenance Reports
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.maintenance.index') }}"
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowLeft" />
                    Back
                </x-base.button>
                <form action="{{ route('admin.maintenance.export-pdf') }}" method="POST">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period ?? 'monthly' }}">
                    <input type="hidden" name="vehicle_id" value="{{ $vehicleId ?? '' }}">
                    <input type="hidden" name="start_date" value="{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                    <input type="hidden" name="end_date" value="{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}">
                    <x-base.button type="submit" variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="FileText" />
                        Export PDF
                    </x-base.button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-span-12">
        <div class="intro-y box p-5">
            <!-- Filtros -->
            <h3 class="text-lg font-medium mb-4">Maintenance Reports Filters</h3>   
            <form method="GET" action="{{ route('admin.maintenance.reports') }}" class="mb-5">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="form-label">Period</label>
                        <select name="period" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="all" {{ ($period ?? 'all') == 'all' ? 'selected' : '' }}>All periods</option>
                            <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Today</option>
                            <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>This week</option>
                            <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>This month</option>
                            <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>This year</option>
                            <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom period</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Vehicle</label>
                        <select name="vehicle_id" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All vehicles</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ $vehicleId == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }}) {{ $vehicle->license_plate }}     
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Estado</label>
                        <select name="status" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8">
                            <option value="">All status</option>
                            <option value="1" {{ $status == '1' ? 'selected' : '' }}>Completed</option>
                            <option value="0" {{ $status == '0' ? 'selected' : '' }}>Pending</option>
                            <option value="2" {{ $status == '2' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <x-base.button type="submit" class="btn btn-primary w-full" variant="primary">Filtrar</x-base.button>
                    </div>
                </div>
                
                <!-- Fechas personalizadas -->
                @if($period == 'custom')
                <div id="custom-date-range" class="mt-4">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <label class="form-label">Fecha inicial</label>
                            <input type="date" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" name="start_date" value="{{ $startDate }}">
                        </div>
                        <div class="flex-1">
                            <label class="form-label">Fecha final</label>
                            <input type="date" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" name="end_date" value="{{ $endDate }}">
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary w-24 mr-2">Filtrar</button>
                    <a href="{{ route('admin.maintenance.reports') }}" class="btn btn-outline-secondary w-24">Restablecer</a>
                </div>
            </form>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-5">
                <div class="intro-y box p-5">
                    <div class="flex items-center justify-center text-center">
                        <div>
                            <div class="text-slate-500 text-xs mb-2">Total Mantenimientos</div>
                            <div class="text-2xl font-medium text-primary">{{ $totalMaintenances }}</div>
                        </div>
                    </div>
                </div>
                <div class="intro-y box p-5">
                    <div class="flex items-center justify-center text-center">
                        <div>
                            <div class="text-slate-500 text-xs mb-2">Vehículos Atendidos</div>
                            <div class="text-2xl font-medium text-success">{{ $vehiclesServiced }}</div>
                        </div>
                    </div>
                </div>
                <div class="intro-y box p-5">
                    <div class="flex items-center justify-center text-center">
                        <div>
                            <div class="text-slate-500 text-xs mb-2">Costo Total</div>
                            <div class="text-2xl font-medium text-warning">${{ number_format($totalCost, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="intro-y box p-5">
                    <div class="flex items-center justify-center text-center">
                        <div>
                            <div class="text-slate-500 text-xs mb-2">Costo Promedio por Vehículo</div>
                            <div class="text-2xl font-medium text-info">${{ number_format($avgCostPerVehicle ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
                <!-- Costos de mantenimiento por mes -->
                <div class="intro-y box p-5">
                    <h3 class="text-lg font-medium mb-4">Costos de mantenimiento por mes</h3>
                    <div class="mt-4">
                        <canvas id="maintenanceCostChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <!-- Distribución por tipo de servicio -->
                <div class="intro-y box p-5">
                    <h3 class="text-lg font-medium mb-4">Distribución por tipo de servicio</h3>
                    <div class="overflow-x-auto mt-4">
                        <table class="table table-bordered w-full">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="whitespace-nowrap font-medium">Tipo de Servicio</th>
                                    <th class="whitespace-nowrap font-medium text-center">Cantidad</th>
                                    <th class="whitespace-nowrap font-medium text-center">Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($serviceTypeDistribution as $type => $data)
                                    <tr>
                                        <td class="font-medium">{{ ucfirst($type) }}</td>
                                        <td class="text-center">{{ $data['count'] }}</td>
                                        <td class="text-center">{{ number_format($data['percentage'], 1) }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-slate-500">No hay datos de servicios disponibles</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($serviceTypeDistribution) > 0)
                            <tfoot>
                                <tr class="bg-slate-100">
                                    <td class="font-bold">Total</td>
                                    <td class="font-bold text-center">{{ array_sum(array_column($serviceTypeDistribution, 'count')) }}</td>
                                    <td class="font-bold text-center">100%</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        
        <!-- Próximos mantenimientos programados -->
        <div class="intro-y box p-5 mb-6">
            <h3 class="text-lg font-medium mb-4">Próximos Mantenimientos Programados</h3>
            @if($upcomingMaintenances->count() > 0)
                <div class="overflow-x-auto mt-4">
                    <table class="table table-striped table-hover w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="whitespace-nowrap font-medium">Vehículo</th>
                                <th class="whitespace-nowrap font-medium">Tipo de Servicio</th>
                                <th class="whitespace-nowrap font-medium text-center">Fecha Programada</th>
                                <th class="whitespace-nowrap font-medium text-center">Días Restantes</th>
                                <th class="whitespace-nowrap font-medium text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingMaintenances as $maintenance)
                                <tr>
                                    <td class="font-medium">{{ $maintenance->vehicle->make }} {{ $maintenance->vehicle->model }} ({{ $maintenance->vehicle->year }})</td>
                                    <td>{{ $maintenance->service_tasks }}</td>
                                    <td class="text-center">{{ $maintenance->next_service_date ? \Carbon\Carbon::parse($maintenance->next_service_date)->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="text-center">
                                        @if($maintenance->next_service_date)
                                            @php
                                                $daysRemaining = now()->diffInDays(\Carbon\Carbon::parse($maintenance->next_service_date), false);
                                            @endphp
                                            <span class="font-medium {{ $daysRemaining < 0 ? 'text-danger' : ($daysRemaining <= 7 ? 'text-warning' : 'text-success') }}">
                                                {{ $daysRemaining < 0 ? 'Vencido (' . abs($daysRemaining) . ' días)' : $daysRemaining . ' días' }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $maintenance->status ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $maintenance->status ? 'Completado' : 'Pendiente' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-slate-500 text-lg">No hay mantenimientos programados próximamente</div>
                </div>
            @endif
        </div>
                    </div>
                </div>
            </div>

            <!-- Lista detallada de mantenimientos -->
            <div class="intro-y box p-5 mt-5">
                <h3 class="text-lg font-medium mb-4">Lista Detallada de Mantenimientos</h3>
                @if($maintenances->count() > 0)
                    <div class="overflow-x-auto mt-4">
                        <table class="table table-striped table-hover w-full">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="whitespace-nowrap font-medium text-center">Fecha</th>
                                    <th class="whitespace-nowrap font-medium">Vehículo</th>
                                    <th class="whitespace-nowrap font-medium">Tipo de Servicio</th>
                                    <th class="whitespace-nowrap font-medium">Descripción</th>
                                    <th class="whitespace-nowrap font-medium text-center">Costo</th>
                                    <th class="whitespace-nowrap font-medium text-center">Estado</th>
                                    <th class="whitespace-nowrap font-medium text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($maintenances as $maintenance)
                                    <tr>
                                        <td class="text-center">{{ $maintenance->service_date ? \Carbon\Carbon::parse($maintenance->service_date)->format('d/m/Y') : 'N/A' }}</td>
                                        <td class="font-medium">{{ $maintenance->vehicle->make }} {{ $maintenance->vehicle->model }} ({{ $maintenance->vehicle->year }})</td>
                                        <td>{{ $maintenance->service_tasks }}</td>
                                        <td class="max-w-xs">
                                            <span title="{{ $maintenance->notes }}">
                                                {{ Str::limit($maintenance->notes ?? 'Sin descripción', 50) }}
                                            </span>
                                        </td>
                                        <td class="text-center font-medium text-success">${{ number_format($maintenance->cost, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $maintenance->status ? 'badge-success' : 'badge-secondary' }}">
                                                {{ $maintenance->status ? 'Completado' : 'Pendiente' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('admin.maintenance.show', $maintenance->id) }}" class="btn btn-primary btn-sm" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.maintenance.edit', $maintenance->id) }}" class="btn btn-secondary btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <div class="mt-5 flex justify-center">
                        {{ $maintenances->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-slate-500 text-lg mb-2">
                            <i class="fas fa-search text-4xl mb-4 text-slate-300"></i>
                        </div>
                        <div class="text-slate-500 text-lg">No se encontraron mantenimientos con los filtros aplicados</div>
                        <div class="text-slate-400 text-sm mt-2">Intenta ajustar los filtros para obtener resultados</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de costos de mantenimiento por mes
            const costsCtx = document.getElementById('maintenanceCostChart').getContext('2d');
            const costData = @json($costByMonth ?? []);
            const costLabels = Object.keys(costData);
            const costValues = Object.values(costData);
            
            const costsChart = new Chart(costsCtx, {
                type: 'bar',
                data: {
                    labels: costLabels.length > 0 ? costLabels : ['Sin datos'],
                    datasets: [{
                        label: 'Costos de mantenimiento ($)',
                        data: costValues.length > 0 ? costValues : [0],
                        backgroundColor: 'rgba(45, 125, 246, 0.7)',
                        borderColor: 'rgba(45, 125, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            // Funcionalidad para mostrar/ocultar campos de fecha personalizada
            const periodSelect = document.querySelector('select[name="period"]');
            const customDateRange = document.getElementById('custom-date-range');
            
            function toggleCustomDateRange() {
                if (periodSelect.value === 'custom') {
                    if (!customDateRange) {
                        // Crear el div de fechas personalizadas si no existe
                        const customDiv = document.createElement('div');
                        customDiv.id = 'custom-date-range';
                        customDiv.className = 'mt-4';
                        customDiv.innerHTML = `
                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="flex-1">
                                    <label class="form-label">Fecha inicial</label>
                                    <input type="date" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" name="start_date" value="{{ $startDate ?? '' }}">
                                </div>
                                <div class="flex-1">
                                    <label class="form-label">Fecha final</label>
                                    <input type="date" class="w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8" name="end_date" value="{{ $endDate ?? '' }}">
                                </div>
                            </div>
                        `;
                        periodSelect.closest('.grid').parentNode.insertBefore(customDiv, periodSelect.closest('.grid').nextSibling);
                    } else {
                        customDateRange.style.display = 'block';
                    }
                } else {
                    if (customDateRange) {
                        customDateRange.style.display = 'none';
                    }
                }
            }
            
            if (periodSelect) {
                periodSelect.addEventListener('change', toggleCustomDateRange);
                // Ejecutar al cargar la página
                toggleCustomDateRange();
            }
        });
    </script>
@endsection
