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
        @include('admin.dashboard.stats')
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
            stats: @json($stats),
            chartData: @json($chartData),

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
                        custom_date_end: this.customDateEnd
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
                        this.stats = {
                            ...this.stats, // Mantener valores anteriores si no vienen nuevos
                            ...data.stats // Sobrescribir con nuevos valores
                        };
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
