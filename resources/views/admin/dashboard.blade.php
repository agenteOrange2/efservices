@extends('../themes/' . $activeTheme)
@section('title', 'Dashboard EF Services')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Dashboard', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="p-0" x-data="dashboardApp()">
        <!-- Header y filtros -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-medium mb-2">Dashboard EF Services</h2>
                <div class="flex items-center space-x-2">
                    <div class="relative">
                        <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]" icon="Filter" />
                        <select x-model="dateRange"
                            class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    
                    <div x-show="showCustomDateFields" class="flex items-center gap-2">
                        <input type="date" x-model="customDateStart"
                            class="border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary" />
                        <span>to</span>
                        <input type="date" x-model="customDateEnd"
                            class="border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-primary" />
                        <button @click="applyCustomDateFilter()"
                            class="bg-primary text-white rounded-lg py-2 px-4 hover:bg-primary-focus transition duration-300">
                            Apply
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 mt-3 md:mt-0">
                <button @click="exportPdf()"
                    class="flex items-center gap-2 bg-danger text-white rounded-lg py-2 px-4 hover:bg-danger/80 transition duration-300">
                    <x-base.lucide icon="FileText" class="h-4 w-4" />
                    Export PDF
                </button>
            </div>
        </div>

        <!-- Indicador de carga -->
        <div x-show="isLoading" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-5 rounded-lg shadow-lg">
                <div class="flex items-center space-x-3">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-r-transparent"></div>
                    <span>Loading data...</span>
                </div>
            </div>
        </div>

        <!-- Resumen de métricas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Usuarios -->
            <div class="box p-5 bg-gradient-to-tr from-primary/20 via-primary/10 to-white">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary">
                        <x-base.lucide icon="Users" class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="text-slate-500">Total Users</div>
                        <div class="text-2xl font-medium mt-1" x-text="stats.totalUsers || 0"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4 text-xs sm:text-sm">
                    <div class="flex items-center gap-1">
                        <div class="bg-success/20 text-success px-2 py-0.5 rounded-full"
                            x-text="stats.activeUserCarriers || 0"></div>
                        <span class="text-slate-500">Activos</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="bg-warning/20 text-warning px-2 py-0.5 rounded-full"
                            x-text="stats.pendingUserCarriers || 0"></div>
                        <span class="text-slate-500">Pendientes</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="bg-danger/20 text-danger px-2 py-0.5 rounded-full"
                            x-text="stats.inactiveUserCarriers || 0"></div>
                        <span class="text-slate-500">Inactivos</span>
                    </div>
                </div>
            </div>

            <!-- Vehículos -->
            <div class="box p-5 bg-gradient-to-tr from-success/20 via-success/10 to-white">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-success/10 text-success">
                        <x-base.lucide icon="Truck" class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="text-slate-500">Total Vehicles</div>
                        <div class="text-2xl font-medium mt-1" x-text="stats.totalVehicles || 0"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4 text-xs sm:text-sm">
                    <div class="flex items-center gap-1">
                        <div class="bg-success/20 text-success px-2 py-0.5 rounded-full"
                            x-text="stats.activeVehicles || 0"></div>
                        <span class="text-slate-500">Activos</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="bg-warning/20 text-warning px-2 py-0.5 rounded-full"
                            x-text="stats.suspendedVehicles || 0"></div>
                        <span class="text-slate-500">Suspendidos</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="bg-danger/20 text-danger px-2 py-0.5 rounded-full"
                            x-text="stats.outOfServiceVehicles || 0"></div>
                        <span class="text-slate-500">Fuera de servicio</span>
                    </div>
                </div>
            </div>

            <!-- Transportistas -->
            <div class="box p-5 bg-gradient-to-tr from-warning/20 via-warning/10 to-white">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-warning/10 text-warning">
                        <x-base.lucide icon="Building" class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="text-slate-500">Total Carriers</div>
                        <div class="text-2xl font-medium mt-1" x-text="stats.totalCarriers || 0"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4 text-xs sm:text-sm">
                    <div class="flex items-center gap-1">
                        <div class="bg-success/20 text-success px-2 py-0.5 rounded-full"
                            x-text="stats.activeUserCarriers || 0"></div>
                        <span class="text-slate-500">Activos</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="bg-warning/20 text-warning px-2 py-0.5 rounded-full"
                            x-text="stats.pendingUserCarriers || 0"></div>
                        <span class="text-slate-500">Pendientes</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="bg-danger/20 text-danger px-2 py-0.5 rounded-full"
                            x-text="stats.inactiveUserCarriers || 0"></div>
                        <span class="text-slate-500">Inactivos</span>
                    </div>
                </div>
            </div>

            <!-- Conductores -->
            <div class="box p-5 bg-gradient-to-tr from-info/20 via-info/10 to-white">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-info/10 text-info">
                        <x-base.lucide icon="UserCheck" class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="text-slate-500">Total Drivers</div>
                        <div class="text-2xl font-medium mt-1" x-text="stats.totalUserDrivers || 0"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4 text-xs sm:text-sm">
                    <div class="flex items-center gap-1">
                        <div class="bg-success/20 text-success px-2 py-0.5 rounded-full"
                            x-text="stats.activeUserDrivers || 0"></div>
                        <span class="text-slate-500">Activos</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="bg-warning/20 text-warning px-2 py-0.5 rounded-full"
                            x-text="stats.pendingUserDrivers || 0"></div>
                        <span class="text-slate-500">Pendientes</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="bg-danger/20 text-danger px-2 py-0.5 rounded-full"
                            x-text="stats.inactiveUserDrivers || 0"></div>
                        <span class="text-slate-500">Inactivos</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos y tablas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Gráfico de usuarios -->
            <div class="box p-5">
                <h3 class="text-lg font-medium mb-3">User Distribution</h3>
                <div class="relative h-64">
                    <canvas id="userChart"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-3xl font-medium" x-text="stats.totalUsers || 0"></div>
                            <div class="text-slate-500">Total</div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-center gap-x-5 gap-y-3">
                    <div class="flex items-center text-slate-500">
                        <div class="mr-2 h-3 w-3 rounded-full bg-success"></div>
                        <span>Active</span>
                    </div>
                    <div class="flex items-center text-slate-500">
                        <div class="mr-2 h-3 w-3 rounded-full bg-warning"></div>
                        <span>Pending</span>
                    </div>
                    <div class="flex items-center text-slate-500">
                        <div class="mr-2 h-3 w-3 rounded-full bg-danger"></div>
                        <span>Inactive</span>
                    </div>
                </div>
            </div>

            <!-- Gráfico de vehículos -->
            <div class="box p-5">
                <h3 class="text-lg font-medium mb-3">Vehicle Status</h3>
                <div class="relative h-64">
                    <canvas id="vehicleChart"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-3xl font-medium" x-text="stats.totalVehicles || 0"></div>
                            <div class="text-slate-500">Total</div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-center gap-x-5 gap-y-3">
                    <div class="flex items-center text-slate-500">
                        <div class="mr-2 h-3 w-3 rounded-full bg-success"></div>
                        <span>Activos</span>
                    </div>
                    <div class="flex items-center text-slate-500">
                        <div class="mr-2 h-3 w-3 rounded-full bg-warning"></div>
                        <span>Suspendidos</span>
                    </div>
                    <div class="flex items-center text-slate-500">
                        <div class="mr-2 h-3 w-3 rounded-full bg-danger"></div>
                        <span>Fuera de servicio</span>
                    </div>
                </div>
            </div>

            <!-- Gráfico de mantenimiento -->
            <div class="box p-5">
                <h3 class="text-lg font-medium mb-3">Maintenance Status</h3>
                <div class="relative h-64">
                    <canvas id="maintenanceChart"></canvas>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-3xl font-medium" x-text="stats.totalMaintenance || 0"></div>
                            <div class="text-slate-500">Total</div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-center gap-x-5 gap-y-3">
                    <div class="flex items-center text-slate-500">
                        <div class="mr-2 h-3 w-3 rounded-full bg-success"></div>
                        <span>Completed</span>
                    </div>
                    <div class="flex items-center text-slate-500">
                        <div class="mr-2 h-3 w-3 rounded-full bg-info"></div>
                        <span>Pending</span>
                    </div>
                    <div class="flex items-center text-slate-500">
                        <div class="mr-2 h-3 w-3 rounded-full bg-warning"></div>
                        <span>Upcoming</span>
                    </div>
                    <div class="flex items-center text-slate-500">
                        <div class="mr-2 h-3 w-3 rounded-full bg-danger"></div>
                        <span>Overdue</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tablas de datos recientes -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Vehículos recientes -->
            <div class="box p-5">
                <h3 class="text-lg font-medium mb-3">Recent Vehicles</h3>
                <div class="overflow-x-auto">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">Vehicle</th>
                                <th class="whitespace-nowrap">VIN</th>
                                <th class="whitespace-nowrap">Carrier</th>
                                <th class="whitespace-nowrap">Status</th>
                                <th class="whitespace-nowrap">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(vehicle, index) in stats.recentVehicles || []" :key="index">
                                <tr>
                                    <td x-text="`${vehicle.make} ${vehicle.model} ${vehicle.year}`"></td>
                                    <td x-text="vehicle.vin"></td>
                                    <td x-text="vehicle.carrier"></td>
                                    <td>
                                        <span :class="vehicle.status_class" x-text="vehicle.status"></span>
                                    </td>
                                    <td x-text="vehicle.created_at"></td>
                                </tr>
                            </template>
                            <tr x-show="!stats.recentVehicles || stats.recentVehicles.length === 0">
                                <td colspan="5" class="text-center py-4">No recent vehicles</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mantenimientos recientes -->
            <div class="box p-5">
                <h3 class="text-lg font-medium mb-3">Recent Maintenance</h3>
                <div class="overflow-x-auto">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">Vehicle</th>
                                <th class="whitespace-nowrap">Service Date</th>
                                <th class="whitespace-nowrap">Next Service</th>
                                <th class="whitespace-nowrap">Cost</th>
                                <th class="whitespace-nowrap">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(maintenance, index) in stats.recentMaintenance || []" :key="index">
                                <tr>
                                    <td x-text="maintenance.vehicle"></td>
                                    <td x-text="maintenance.service_date"></td>
                                    <td x-text="maintenance.next_service_date"></td>
                                    <td x-text="maintenance.cost"></td>
                                    <td>
                                        <span :class="maintenance.status_class" x-text="maintenance.status"></span>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!stats.recentMaintenance || stats.recentMaintenance.length === 0">
                                <td colspan="5" class="text-center py-4">No recent maintenance</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/dashboard-new.js') }}"></script>
<!-- <script>

</script> -->
@endpush