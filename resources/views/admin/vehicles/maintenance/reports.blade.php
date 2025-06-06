@extends('../themes/' . $activeTheme)

@section('title', 'Reportes de Mantenimiento')

@section('breadcrumb')
    <x-base.breadcrumb>
        <x-base.breadcrumb.item href="{{ route('admin.dashboard') }}">Dashboard</x-base.breadcrumb.item>
        <x-base.breadcrumb.item href="{{ route('admin.maintenance.index') }}">Mantenimientos</x-base.breadcrumb.item>
        <x-base.breadcrumb.item active>Reportes</x-base.breadcrumb.item>
    </x-base.breadcrumb>
@endsection

@section('content')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Reportes de Mantenimiento
        </h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-secondary shadow-md">
                <i class="w-4 h-4 mr-2" data-lucide="arrow-left"></i> Volver
            </a>
        </div>
    </div>
    
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-6">
            <!-- Filtros -->
            <div class="col-span-12 lg:col-span-4">
                <div class="box p-5">
                    <h2 class="font-medium text-base mb-5">Filtros</h2>
                    <form action="{{ route('admin.maintenance.reports') }}" method="GET">
                        <div class="mb-4">
                            <label class="form-label">Período</label>
                            <select class="form-select w-full" name="period">
                                <option value="all">Todos</option>
                                <option value="month">Último mes</option>
                                <option value="quarter">Último trimestre</option>
                                <option value="year">Último año</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Tipo de servicio</label>
                            <select class="form-select w-full" name="service_type">
                                <option value="">Todos</option>
                                <option value="oil_change">Cambio de aceite</option>
                                <option value="tire_rotation">Rotación de neumáticos</option>
                                <option value="brake_service">Servicio de frenos</option>
                                <option value="inspection">Inspección</option>
                                <option value="repair">Reparación</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Estado</label>
                            <select class="form-select w-full" name="status">
                                <option value="">Todos</option>
                                <option value="1">Completado</option>
                                <option value="0">Pendiente</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-full">Aplicar filtros</button>
                    </form>
                </div>
            </div>
            
            <!-- Gráficos y Estadísticas -->
            <div class="col-span-12 lg:col-span-8">
                <div class="box p-5 mb-5">
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
