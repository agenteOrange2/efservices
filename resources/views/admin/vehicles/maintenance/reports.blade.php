@extends('../themes/' . $activeTheme)

@section('title', 'Reportes de Mantenimiento')

@php
$breadcrumbLinks = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Vehículos', 'url' => route('admin.vehicles.index')],
    ['label' => 'Mantenimiento', 'url' => route('admin.maintenance.index')],
    ['label' => 'Reportes', 'active' => true],
];
@endphp

@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Reportes de Mantenimiento
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.maintenance.index') }}"
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowLeft" />
                    Volver
                </x-base.button>
                <form action="{{ route('admin.maintenance.export-pdf') }}" method="POST">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period ?? 'monthly' }}">
                    <input type="hidden" name="vehicle_id" value="{{ $vehicleId ?? '' }}">
                    <input type="hidden" name="start_date" value="{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                    <input type="hidden" name="end_date" value="{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}">
                    <x-base.button type="submit" variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="FileText" />
                        Exportar PDF
                    </x-base.button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-span-12">
        <div class="intro-y box p-5">
            <!-- Filtros -->
            <form action="{{ route('admin.maintenance.reports') }}" method="GET" class="mb-5">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="form-label">Período</label>
                        <select class="form-select w-full" name="period" id="period-select">
                            @php $selectedPeriod = $period ?? 'monthly'; @endphp
                            <option value="daily" {{ $selectedPeriod == 'daily' ? 'selected' : '' }}>Diario</option>
                            <option value="weekly" {{ $selectedPeriod == 'weekly' ? 'selected' : '' }}>Semanal</option>
                            <option value="monthly" {{ $selectedPeriod == 'monthly' ? 'selected' : '' }}>Mensual</option>
                            <option value="yearly" {{ $selectedPeriod == 'yearly' ? 'selected' : '' }}>Anual</option>
                            <option value="custom" {{ $selectedPeriod == 'custom' ? 'selected' : '' }}>Personalizado</option>
                        </select>
                    </div>
                    
                    <div class="flex-1">
                        <label class="form-label">Vehículo</label>
                        <select class="form-select w-full" name="vehicle_id">
                            <option value="">Todos los vehículos</option>
                            @php
                                $availableVehicles = isset($vehicles) ? $vehicles : collect();
                                $selectedVehicleId = $vehicleId ?? '';
                            @endphp
                            @foreach($availableVehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ $selectedVehicleId == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->company_unit_number ?? $vehicle->vin }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex-1">
                        <label class="form-label">Estado</label>
                        <select class="form-select w-full" name="status">
                            @php $selectedStatus = $status ?? ''; @endphp
                        <option value="">Todos</option>
                            <option value="1" {{ $selectedStatus == '1' ? 'selected' : '' }}>Completados</option>
                            <option value="0" {{ $selectedStatus == '0' ? 'selected' : '' }}>Pendientes</option>
                        </select>
                    </div>
                </div>
                
                <!-- Fechas personalizadas (mostrar/ocultar con Alpine.js) -->
                <div id="custom-date-range" class="mt-4" x-data="{ showDateRange: '{{ $period ?? 'monthly' }}' === 'custom' }" x-show="showDateRange">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <label class="form-label">Fecha inicial</label>
                            <input type="date" class="form-control" name="start_date" value="{{ $startDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="flex-1">
                            <label class="form-label">Fecha final</label>
                            <input type="date" class="form-control" name="end_date" value="{{ $endDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary w-24 mr-2">Filtrar</button>
                    <a href="{{ route('admin.maintenance.reports') }}" class="btn btn-outline-secondary w-24">Restablecer</a>
                </div>
            </form>

            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mt-5">
                <div class="intro-y box p-5">
                    <div class="flex items-center">
                        <div class="w-12 h-12 flex-none image-fit">
                            <x-base.lucide class="w-10 h-10 text-primary" icon="WrenchIcon" />
                        </div>
                        <div class="ml-4 mr-auto">
                            <div class="font-medium text-base">{{ $totalMaintenances }}</div>
                            <div class="text-slate-500">Total Mantenimientos</div>
                        </div>
                    </div>
                </div>
                
                <div class="intro-y box p-5">
                    <div class="flex items-center">
                        <div class="w-12 h-12 flex-none image-fit">
                            <x-base.lucide class="w-10 h-10 text-success" icon="TruckIcon" />
                        </div>
                        <div class="ml-4 mr-auto">
                            <div class="font-medium text-base">{{ $totalVehiclesServiced }}</div>
                            <div class="text-slate-500">Vehículos Servidos</div>
                        </div>
                    </div>
                </div>
                
                <div class="intro-y box p-5">
                    <div class="flex items-center">
                        <div class="w-12 h-12 flex-none image-fit">
                            <x-base.lucide class="w-10 h-10 text-warning" icon="DollarSignIcon" />
                        </div>
                        <div class="ml-4 mr-auto">
                            <div class="font-medium text-base">${{ number_format($totalCost, 2) }}</div>
                            <div class="text-slate-500">Costo Total</div>
                        </div>
                    </div>
                </div>
                
                <div class="intro-y box p-5">
                    <div class="flex items-center">
                        <div class="w-12 h-12 flex-none image-fit">
                            <x-base.lucide class="w-10 h-10 text-danger" icon="TrendingUpIcon" />
                        </div>
                        <div class="ml-4 mr-auto">
                            <div class="font-medium text-base">${{ number_format($avgCostPerVehicle, 2) }}</div>
                            <div class="text-slate-500">Costo Promedio/Vehículo</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de costos por mes -->
            <div class="box p-5 mt-5">
                <h2 class="font-medium text-base mb-5">Costos de mantenimiento por mes</h2>
                <div class="h-[400px]">
                    <canvas id="maintenance-costs-chart"></canvas>
                </div>
            </div>
            
                <div class="box p-5">
                    <h2 class="font-medium text-base mb-5">Distribución por tipo de servicio</h2>
                    <div class="grid grid-cols-12 gap-6">
                        <div class="col-span-12 lg:col-span-6">
                            <div class="h-[300px]">
                                <canvas id="service-type-chart"></canvas>
                            </div>
                        </div>
                        <div class="col-span-12 lg:col-span-6">
                            <div class="overflow-x-auto">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="whitespace-nowrap">Tipo de servicio</th>
                                            <th class="whitespace-nowrap text-right">Cantidad</th>
                                            <th class="whitespace-nowrap text-right">Costo total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Cambio de aceite</td>
                                            <td class="text-right">12</td>
                                            <td class="text-right">$1,200.00</td>
                                        </tr>
                                        <tr>
                                            <td>Rotación de neumáticos</td>
                                            <td class="text-right">8</td>
                                            <td class="text-right">$800.00</td>
                                        </tr>
                                        <tr>
                                            <td>Servicio de frenos</td>
                                            <td class="text-right">5</td>
                                            <td class="text-right">$2,500.00</td>
                                        </tr>
                                        <tr>
                                            <td>Inspección</td>
                                            <td class="text-right">15</td>
                                            <td class="text-right">$750.00</td>
                                        </tr>
                                        <tr>
                                            <td>Reparación</td>
                                            <td class="text-right">3</td>
                                            <td class="text-right">$4,500.00</td>
                                        </tr>
                                        <tr>
                                            <td>Otro</td>
                                            <td class="text-right">7</td>
                                            <td class="text-right">$3,500.00</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            <th class="text-right">50</th>
                                            <th class="text-right">$13,250.00</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="box p-5 mt-5">
            <h2 class="font-medium text-base mb-5">Próximos mantenimientos programados</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">Vehículo</th>
                            <th class="whitespace-nowrap">Tipo de servicio</th>
                            <th class="whitespace-nowrap">Fecha programada</th>
                            <th class="whitespace-nowrap">Días restantes</th>
                            <th class="whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Ford F-150 (2020) - ABC123</td>
                            <td>Cambio de aceite</td>
                            <td>15/07/2025</td>
                            <td>39</td>
                            <td>
                                <div class="flex">
                                    <a href="#" class="btn btn-sm btn-primary mr-1">Ver</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Chevrolet Silverado (2019) - XYZ789</td>
                            <td>Rotación de neumáticos</td>
                            <td>22/07/2025</td>
                            <td>46</td>
                            <td>
                                <div class="flex">
                                    <a href="#" class="btn btn-sm btn-primary mr-1">Ver</a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Kenworth T680 (2021) - DEF456</td>
                            <td>Inspección</td>
                            <td>30/07/2025</td>
                            <td>54</td>
                            <td>
                                <div class="flex">
                                    <a href="#" class="btn btn-sm btn-primary mr-1">Ver</a>
                                </div>
                            </td>
                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Mantenimientos -->
            <div class="intro-y box p-5 mt-5">
                <h2 class="font-medium text-base mb-5">Lista de Mantenimientos</h2>
                
                <div class="overflow-x-auto">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">Vehículo</th>
                                <th class="whitespace-nowrap">Tipo</th>
                                <th class="whitespace-nowrap">Fecha</th>
                                <th class="whitespace-nowrap">Próximo</th>
                                <th class="whitespace-nowrap">Proveedor</th>
                                <th class="whitespace-nowrap">Costo</th>
                                <th class="whitespace-nowrap">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($maintenances as $maintenance)
                            <tr>
                                <td>
                                    {{ $maintenance->vehicle->make }} {{ $maintenance->vehicle->model }}
                                    <div class="text-slate-500 text-xs">{{ $maintenance->vehicle->company_unit_number ?? $maintenance->vehicle->vin }}</div>
                                </td>
                                <td>{{ $maintenance->service_tasks }}</td>
                                <td>{{ $maintenance->service_date->format('d/m/Y') }}</td>
                                <td>
                                    @if($maintenance->next_service_date)
                                        {{ $maintenance->next_service_date->format('d/m/Y') }}
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td>{{ $maintenance->vendor_mechanic }}</td>
                                <td>${{ number_format($maintenance->cost, 2) }}</td>
                                <td>
                                    @if($maintenance->status)
                                        <span class="text-success flex items-center">
                                            <x-base.lucide class="w-4 h-4 mr-1" icon="CheckCircle" /> Completado
                                        </span>
                                    @else
                                        <span class="text-warning flex items-center">
                                            <x-base.lucide class="w-4 h-4 mr-1" icon="Clock" /> Pendiente
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No se encontraron registros de mantenimiento</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-5">
                    {{ $maintenances->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de costos de mantenimiento por mes
            const costsCtx = document.getElementById('maintenance-costs-chart').getContext('2d');
            const costsChart = new Chart(costsCtx, {
                type: 'bar',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    datasets: [{
                        label: 'Costos de mantenimiento ($)',
                        data: [1200, 1500, 800, 1300, 2200, 1800, 2500, 1900, 2100, 1700, 2300, 1600],
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
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });
            
            // Gráfico de distribución por tipo de servicio
            const typeCtx = document.getElementById('service-type-chart').getContext('2d');
            const typeChart = new Chart(typeCtx, {
                type: 'pie',
                data: {
                    labels: ['Cambio de aceite', 'Rotación de neumáticos', 'Servicio de frenos', 'Inspección', 'Reparación', 'Otro'],
                    datasets: [{
                        data: [12, 8, 5, 15, 3, 7],
                        backgroundColor: [
                            'rgba(45, 125, 246, 0.7)',
                            'rgba(52, 195, 143, 0.7)',
                            'rgba(241, 85, 108, 0.7)',
                            'rgba(252, 185, 44, 0.7)',
                            'rgba(119, 93, 208, 0.7)',
                            'rgba(130, 134, 139, 0.7)'
                        ],
                        borderColor: [
                            'rgba(45, 125, 246, 1)',
                            'rgba(52, 195, 143, 1)',
                            'rgba(241, 85, 108, 1)',
                            'rgba(252, 185, 44, 1)',
                            'rgba(119, 93, 208, 1)',
                            'rgba(130, 134, 139, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
@endsection
