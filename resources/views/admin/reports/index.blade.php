@extends('../themes/' . $activeTheme)
@section('title', 'Dashboard de Reportes')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Dashboard de Reportes', 'active' => true],
    ];
@endphp

@push('styles')
<style>
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 15px;
    color: white;
    transition: transform 0.3s ease;
}
.stats-card:hover {
    transform: translateY(-5px);
}
.stats-card.success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}
.stats-card.warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
.stats-card.info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}
.stats-card.danger {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}
.chart-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    padding: 20px;
}
.metric-icon {
    font-size: 3rem;
    opacity: 0.8;
}
.progress-custom {
    height: 8px;
    border-radius: 10px;
}
</style>
@endpush

@section('subcontent')
<div class="container-fluid">
    <!-- Header con estadísticas principales -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">Dashboard de Reportes</h1>
                    <p class="text-muted">Estadísticas en tiempo real del sistema EF Services</p>
                </div>
                <div class="text-right">
                    <small class="text-muted">Última actualización: {{ now()->format('d/m/Y H:i') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Métricas principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-building metric-icon mb-3"></i>
                    <h2 class="mb-1">{{ $stats['carriers']['total'] }}</h2>
                    <p class="mb-2">Total Carriers</p>
                    <div class="progress progress-custom mb-2">
                        <div class="progress-bar bg-light" style="width: {{ $stats['carriers']['percentage_active'] }}%"></div>
                    </div>
                    <small>{{ $stats['carriers']['active'] }} activos ({{ $stats['carriers']['percentage_active'] }}%)</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card success h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users metric-icon mb-3"></i>
                    <h2 class="mb-1">{{ $stats['drivers']['total'] }}</h2>
                    <p class="mb-2">Total Conductores</p>
                    <div class="progress progress-custom mb-2">
                        <div class="progress-bar bg-light" style="width: {{ $stats['drivers']['percentage_active'] }}%"></div>
                    </div>
                    <small>{{ $stats['drivers']['active'] }} activos ({{ $stats['drivers']['percentage_active'] }}%)</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card info h-100">
                <div class="card-body text-center">
                    <i class="fas fa-truck metric-icon mb-3"></i>
                    <h2 class="mb-1">{{ $stats['vehicles']['total'] }}</h2>
                    <p class="mb-2">Total Vehículos</p>
                    <div class="progress progress-custom mb-2">
                        <div class="progress-bar bg-light" style="width: {{ $stats['vehicles']['percentage_active'] }}%"></div>
                    </div>
                    <small>{{ $stats['vehicles']['active'] }} activos ({{ $stats['vehicles']['percentage_active'] }}%)</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card warning h-100">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt metric-icon mb-3"></i>
                    <h2 class="mb-1">{{ $stats['documents']['total'] }}</h2>
                    <p class="mb-2">Total Documentos</p>
                    <div class="progress progress-custom mb-2">
                        <div class="progress-bar bg-light" style="width: {{ $stats['documents']['percentage_approved'] }}%"></div>
                    </div>
                    <small>{{ $stats['documents']['approved'] }} aprobados ({{ $stats['documents']['percentage_approved'] }}%)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de tendencias -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-3">
            <div class="chart-container">
                <h5 class="mb-3"><i class="fas fa-chart-line text-primary"></i> Conductores por Mes</h5>
                <canvas id="driversChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="chart-container">
                <h5 class="mb-3"><i class="fas fa-chart-bar text-success"></i> Vehículos por Mes</h5>
                <canvas id="vehiclesChart" height="200"></canvas>
            </div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="chart-container">
                <h5 class="mb-3"><i class="fas fa-chart-area text-info"></i> Documentos por Mes</h5>
                <canvas id="documentsChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Estadísticas detalladas -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="chart-container">
                <h5 class="mb-3"><i class="fas fa-users-cog text-primary"></i> Estado de Conductores</h5>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center p-3">
                            <h4 class="text-success">{{ $stats['drivers']['approved'] }}</h4>
                            <small class="text-muted">Aprobados</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3">
                            <h4 class="text-warning">{{ $stats['drivers']['pending'] }}</h4>
                            <small class="text-muted">Pendientes</small>
                        </div>
                    </div>
                </div>
                <canvas id="driversStatusChart" height="150"></canvas>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="chart-container">
                <h5 class="mb-3"><i class="fas fa-file-check text-info"></i> Estado de Documentos</h5>
                <div class="row">
                    <div class="col-4">
                        <div class="text-center p-2">
                            <h5 class="text-success">{{ $stats['documents']['approved'] }}</h5>
                            <small class="text-muted">Aprobados</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2">
                            <h5 class="text-warning">{{ $stats['documents']['pending'] }}</h5>
                            <small class="text-muted">Pendientes</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2">
                            <h5 class="text-danger">{{ $stats['documents']['rejected'] }}</h5>
                            <small class="text-muted">Rechazados</small>
                        </div>
                    </div>
                </div>
                <canvas id="documentsStatusChart" height="150"></canvas>
            </div>
        </div>
    </div>

    <!-- Alerta de accidentes recientes -->
    @if($stats['accidents']['recent'] > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Atención:</strong> Se han registrado {{ $stats['accidents']['recent'] }} accidentes en los últimos 30 días.
                <a href="{{ route('admin.reports.accidents') }}" class="alert-link">Ver detalles</a>
            </div>
        </div>
    </div>
    @endif

    <!-- Sección de Reportes Detallados -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="chart-container">
                <h4 class="mb-4"><i class="fas fa-chart-pie text-primary"></i> Reportes Detallados</h4>
                <p class="text-muted mb-4">Accede a los reportes específicos del sistema</p>
                
                <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow">
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                        <h4>Lista de Conductores Activos</h4>
                        <p>Consulta todos los conductores activos en el sistema</p>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.reports.active-drivers') }}" class="btn btn-primary">Ver Reporte</a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow">
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-user-edit fa-3x mb-3 text-warning"></i>
                        <h4>Prospectos de Conductores</h4>
                        <p>Conductores en proceso de verificación y reclutamiento</p>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.reports.driver-prospects') }}" class="btn btn-warning text-white">Ver Reporte</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow">
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-truck fa-3x mb-3 text-success"></i>
                        <h4>Lista de Equipamiento</h4>
                        <p>Consulta todos los vehículos y equipos registrados</p>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.reports.equipment-list') }}" class="btn btn-success">Ver Reporte</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow">
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-file-archive fa-3x mb-3 text-info"></i>
                        <h4>Documentos por Carrier</h4>
                        <p>Descarga los documentos de un carrier específico</p>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.reports.carrier-documents') }}" class="btn btn-info">Ver Reporte</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow">
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-car-crash fa-3x mb-3 text-danger"></i>
                        <h4>Registro de Accidentes</h4>
                        <p>Registra y consulta los accidentes de conductores</p>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.reports.accidents') }}" class="btn btn-danger">Ver Reporte</a>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Configuración de gráficos
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0,0,0,0.1)'
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    }
};

// Gráfico de Conductores por Mes
const driversCtx = document.getElementById('driversChart').getContext('2d');
new Chart(driversCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($stats['monthly']['drivers']['labels']) !!},
        datasets: [{
            label: 'Conductores',
            data: {!! json_encode($stats['monthly']['drivers']['data']) !!},
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: chartOptions
});

// Gráfico de Vehículos por Mes
const vehiclesCtx = document.getElementById('vehiclesChart').getContext('2d');
new Chart(vehiclesCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($stats['monthly']['vehicles']['labels']) !!},
        datasets: [{
            label: 'Vehículos',
            data: {!! json_encode($stats['monthly']['vehicles']['data']) !!},
            backgroundColor: 'rgba(17, 153, 142, 0.8)',
            borderColor: '#11998e',
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: chartOptions
});

// Gráfico de Documentos por Mes
const documentsCtx = document.getElementById('documentsChart').getContext('2d');
new Chart(documentsCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($stats['monthly']['documents']['labels']) !!},
        datasets: [{
            label: 'Documentos',
            data: {!! json_encode($stats['monthly']['documents']['data']) !!},
            borderColor: '#4facfe',
            backgroundColor: 'rgba(79, 172, 254, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: chartOptions
});

// Gráfico de Estado de Conductores (Donut)
const driversStatusCtx = document.getElementById('driversStatusChart').getContext('2d');
new Chart(driversStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Aprobados', 'Pendientes'],
        datasets: [{
            data: [{{ $stats['drivers']['approved'] }}, {{ $stats['drivers']['pending'] }}],
            backgroundColor: ['#28a745', '#ffc107'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Gráfico de Estado de Documentos (Donut)
const documentsStatusCtx = document.getElementById('documentsStatusChart').getContext('2d');
new Chart(documentsStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Aprobados', 'Pendientes', 'Rechazados'],
        datasets: [{
            data: [{{ $stats['documents']['approved'] }}, {{ $stats['documents']['pending'] }}, {{ $stats['documents']['rejected'] }}],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Animación de contadores
function animateCounters() {
    const counters = document.querySelectorAll('.stats-card h2');
    counters.forEach(counter => {
        const target = parseInt(counter.innerText);
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                counter.innerText = target;
                clearInterval(timer);
            } else {
                counter.innerText = Math.floor(current);
            }
        }, 20);
    });
}

// Ejecutar animación al cargar
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(animateCounters, 500);
});
</script>
@endpush
@endsection
