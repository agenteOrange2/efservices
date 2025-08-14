@extends('../themes/' . $activeTheme)
@section('title', 'Reportes')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Reportes', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="mb-2">Reportes</h1>
            <p class="text-muted">Accede a los diferentes reportes del sistema</p>
        </div>
    </div>

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
@endsection
