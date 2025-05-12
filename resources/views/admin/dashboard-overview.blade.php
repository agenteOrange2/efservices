@extends('../themes/' . $activeTheme)
@section('title', 'Dashboard EF Services')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Dashboard', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="grid grid-cols-10 gap-x-6 gap-y-10" x-data="dashboardApp()">
        <!-- Overlay de carga -->
        <div x-show="isLoading" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
            <div class="bg-white p-5 rounded-lg shadow-lg">
                <div class="flex items-center space-x-3">
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-r-transparent"></div>
                    <span>Loading data...</span>
                </div>
            </div>
        </div>

        <!-- Header y filtros -->
        <div class="col-span-10 flex flex-col md:flex-row justify-between items-center mb-4">
            <div>
                <h2 class="text-lg font-medium">Dashboard Overview</h2>
                <div class="relative">
                    <x-base.lucide class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3]" icon="Filter" />
                    <select x-model="dateRange"
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="custom">Custom Date</option>
                    </select>
                </div>
            </div>

            <div x-show="showCustomDateFields" class="flex items-center gap-2 mt-3 md:mt-0">
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

            <div class="flex items-center gap-2 mt-3 md:mt-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-2 bg-primary text-white rounded-lg py-2 px-4 hover:bg-primary/80 transition duration-300 mr-2">
                    <x-base.lucide icon="LayoutGrid" class="h-4 w-4" />
                    Classic Dashboard
                </a>
                <a href="{{ route('admin.dashboard.metrics') }}"
                    class="flex items-center gap-2 bg-info text-white rounded-lg py-2 px-4 hover:bg-info/80 transition duration-300 mr-2">
                    <x-base.lucide icon="BarChart" class="h-4 w-4" />
                    Advanced Metrics
                </a>
                <button @click="exportPdf()"
                    class="flex items-center gap-2 bg-danger text-white rounded-lg py-2 px-4 hover:bg-danger/80 transition duration-300">
                    <x-base.lucide icon="FileText" class="h-4 w-4" />
                    Export PDF
                </button>
            </div>
        </div>

        <!-- Contenido principal del dashboard -->
        @include('admin.dashboard.overview-content', ['stats' => $stats, 'chartData' => $chartData])
    </div>
@endsection

@push('scripts')
<!-- Corrección DIRECTA para el error de gráficos -->
<script src="{{ asset('js/dashboard-chart-fix.js') }}"></script>
<script src="{{ asset('js/chart-direct-fix.js') }}"></script>
<script>
    function dashboardApp() {
        return {
            dateRange: '{{ $dateRange }}',
            customDateStart: '{{ $customDateStart }}',
            customDateEnd: '{{ $customDateEnd }}',
            showCustomDateFields: {{ $dateRange == 'custom' ? 'true' : 'false' }},
            isLoading: false,
            stats: Object.assign({
                // Valores predeterminados para evitar errores de undefined
                totalOrders: 0,
                completedOrders: 0,
                totalServices: 0,
                completedServices: 0,
                totalFeedback: 0,
                currentRevenue: 0,
                targetRevenue: 100,
                ordersGrowthRate: 0,
                servicesGrowthRate: 0,
                feedbackGrowthRate: 0,
                carriersGrowthRate: 0,
                revenueGrowthRate: 0,
                serviceTypes: 0,
                serviceTypesGrowthRate: 0
            }, @json($stats)),
            chartData: @json($chartData),
            dashboardType: 'overview-6', // Nuevo campo para controlar el tipo de dashboard

            init() {
                // Carga inicial de datos para asegurar que las tablas y gráficos se muestren correctamente
                this.$nextTick(() => {
                    // La primera vez, usamos los datos del servidor
                    console.log('Inicializando dashboard con datos del servidor');
                });
                
                // Observador para cambios en el selector de fecha
                this.$watch('dateRange', (value) => {
                    this.showCustomDateFields = value === 'custom';
                    if (value !== 'custom') {
                        this.updateDashboard();
                    }
                });
            },

            updateDashboard() {
                this.isLoading = true;
                
                fetch('{{ route("admin.dashboard.ajax-update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        date_range: this.dateRange,
                        custom_date_start: this.customDateStart,
                        custom_date_end: this.customDateEnd,
                        dashboard_type: this.dashboardType
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.stats) {
                        // Asegurarse de que todas las propiedades existan para evitar errores
                        var defaultStats = {
                            // Valores predeterminados para evitar errores de undefined
                            totalOrders: 0,
                            completedOrders: 0,
                            totalServices: 0,
                            completedServices: 0,
                            totalFeedback: 0,
                            currentRevenue: 0,
                            targetRevenue: 100,
                            ordersGrowthRate: 0,
                            servicesGrowthRate: 0,
                            feedbackGrowthRate: 0,
                            carriersGrowthRate: 0,
                            revenueGrowthRate: 0,
                            serviceTypes: 0,
                            serviceTypesGrowthRate: 0
                        };
                        // Combinar los valores predeterminados con los datos actuales y los nuevos datos
                        this.stats = Object.assign({}, defaultStats, this.stats, data.stats);
                        this.chartData = data.chartData;
                        
                        // Forzar actualización de Alpine para las tablas
                        this.$nextTick(() => {
                            // El DOM se ha actualizado
                            console.log('Datos actualizados correctamente');
                        });
                    } else {
                        console.error('Datos recibidos inválidos:', data);
                    }
                    this.isLoading = false;
                })
                .catch(error => {
                    console.error('Error en la actualización:', error);
                    this.isLoading = false;
                    alert('Error al cargar los datos. Por favor, intente nuevamente.');
                });
            },

            applyCustomDateFilter() {
                if (this.customDateStart && this.customDateEnd) {
                    this.updateDashboard();
                }
            },

            exportPdf() {
                this.isLoading = true;
                
                try {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("admin.dashboard.export-pdf") }}';
                    form.target = '_blank';
                    
                    // CSRF Token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrfToken);
                    
                    // Date Range
                    const dateRangeInput = document.createElement('input');
                    dateRangeInput.type = 'hidden';
                    dateRangeInput.name = 'date_range';
                    dateRangeInput.value = this.dateRange;
                    form.appendChild(dateRangeInput);
                    
                    // Dashboard Type
                    const dashboardTypeInput = document.createElement('input');
                    dashboardTypeInput.type = 'hidden';
                    dashboardTypeInput.name = 'dashboard_type';
                    dashboardTypeInput.value = this.dashboardType;
                    form.appendChild(dashboardTypeInput);
                    
                    // Custom Dates (if applicable)
                    if (this.dateRange === 'custom') {
                        const startDateInput = document.createElement('input');
                        startDateInput.type = 'hidden';
                        startDateInput.name = 'custom_date_start';
                        startDateInput.value = this.customDateStart;
                        form.appendChild(startDateInput);
                        
                        const endDateInput = document.createElement('input');
                        endDateInput.type = 'hidden';
                        endDateInput.name = 'custom_date_end';
                        endDateInput.value = this.customDateEnd;
                        form.appendChild(endDateInput);
                    }
                    
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                    
                    console.log('Formulario de exportación enviado correctamente');
                    
                    // Indicador de éxito
                    setTimeout(() => {
                        this.isLoading = false;
                    }, 1000);
                } catch (error) {
                    console.error('Error al exportar PDF:', error);
                    this.isLoading = false;
                    alert('Error al generar el PDF. Por favor, intente nuevamente.');
                }
            }
        };
    }
</script>
@endpush
