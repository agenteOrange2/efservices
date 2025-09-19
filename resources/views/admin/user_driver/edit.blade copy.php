@extends('../themes/' . $activeTheme)
@section('title', 'Driver Carriers for: ' . $carrier->name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.index')],
        ['label' => 'Driver Carriers: ' . $carrier->name, 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Editar Conductor</h1>
                    <p class="text-muted mb-0">
                        Transportista: {{ $carrier->name }} | 
                        Conductor: {{ $userDriverDetail->user->name ?? 'N/A' }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.carrier.user_drivers.index', $carrier) }}" 
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Lista
                    </a>
                </div>
            </div>

            <!-- Driver Status Info -->
            <div class="alert alert-{{ $userDriverDetail->status === 'active' ? 'success' : ($userDriverDetail->status === 'pending' ? 'warning' : 'secondary') }} mb-4">
                <i class="fas fa-{{ $userDriverDetail->status === 'active' ? 'check-circle' : ($userDriverDetail->status === 'pending' ? 'clock' : 'times-circle') }}"></i>
                <strong>Estado del Conductor:</strong> 
                {{ ucfirst($userDriverDetail->getStatusNameAttribute()) }}
                @if($userDriverDetail->application_completed)
                    <span class="badge badge-success ml-2">Aplicación Completada</span>
                @else
                    <span class="badge badge-warning ml-2">Aplicación Pendiente</span>
                @endif
            </div>

            <!-- Main Form Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-edit"></i> Información del Conductor
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Livewire Component -->
                    @livewire('admin.admin-driver-form', [
                        'carrier' => $carrier,
                        'userDriverDetail' => $userDriverDetail,
                        'mode' => 'edit'
                    ])
                </div>
            </div>

            <!-- Additional Actions Card -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Acciones Peligrosas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Eliminar Conductor</h6>
                            <p class="text-muted small">
                                Esta acción eliminará permanentemente el conductor y toda su información asociada.
                            </p>
                            <form method="POST" 
                                  action="{{ route('admin.carrier.user_drivers.destroy', [$carrier, $userDriverDetail]) }}"
                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar este conductor? Esta acción no se puede deshacer.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Eliminar Conductor
                                </button>
                            </form>
                        </div>
                        @if($userDriverDetail->getProfilePhotoUrlAttribute())
                        <div class="col-md-6">
                            <h6>Eliminar Foto de Perfil</h6>
                            <p class="text-muted small">
                                Eliminar la foto de perfil actual del conductor.
                            </p>
                            <form method="POST" 
                                  action="{{ route('admin.carrier.user_drivers.delete-photo', [$carrier, $userDriverDetail]) }}"
                                  onsubmit="return confirm('¿Estás seguro de que deseas eliminar la foto de perfil?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-image"></i> Eliminar Foto
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .nav-tabs .nav-link {
        border: 1px solid transparent;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
    }
    
    .nav-tabs .nav-link.active {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }
    
    .tab-content {
        border: 1px solid #dee2e6;
        border-top: none;
        padding: 1.5rem;
        background-color: #fff;
    }
    
    .form-group label {
        font-weight: 600;
        color: #374151;
    }
    
    .required::after {
        content: " *";
        color: #e53e3e;
    }
    
    .alert {
        border-left: 4px solid;
    }
    
    .alert-success {
        border-left-color: #28a745;
    }
    
    .alert-warning {
        border-left-color: #ffc107;
    }
    
    .alert-secondary {
        border-left-color: #6c757d;
    }
</style>
@endpush