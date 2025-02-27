@extends('../themes/' . $activeTheme)
@section('title', 'Vehículos')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehículos', 'active' => true],
    ];
@endphp
@section('subcontent')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">Detalle de Mantenimiento</h1>
            <p class="mb-0">Vehículo: {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }}) - VIN: {{ $vehicle->vin }}</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.vehicles.service-items.index', $vehicle->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Mantenimientos
            </a>
            <a href="{{ route('admin.vehicles.service-items.edit', [$vehicle->id, $serviceItem->id]) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información del Servicio</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">Unidad/Sistema:</h5>
                            <p>{{ $serviceItem->unit }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">Tareas Realizadas:</h5>
                            <p>{{ $serviceItem->service_tasks }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <h5 class="font-weight-bold">Fecha del Servicio:</h5>
                            <p>{{ $serviceItem->service_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h5 class="font-weight-bold">Próximo Servicio:</h5>
                            <p>
                                {{ $serviceItem->next_service_date->format('d/m/Y') }}
                                @if($serviceItem->next_service_date->isPast())
                                    <span class="badge badge-danger">Vencido</span>
                                @elseif($serviceItem->next_service_date->diffInDays(now()) < 15)
                                    <span class="badge badge-warning">Próximo</span>
                                @else
                                    <span class="badge badge-success">Al día</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h5 class="font-weight-bold">Odómetro:</h5>
                            <p>{{ $serviceItem->odometer ? number_format($serviceItem->odometer) . ' millas' : 'No registrado' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">Proveedor/Mecánico:</h5>
                            <p>{{ $serviceItem->vendor_mechanic }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">Costo:</h5>
                            <p>${{ number_format($serviceItem->cost, 2) }}</p>
                        </div>
                    </div>

                    @if($serviceItem->description)
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="font-weight-bold">Descripción/Notas:</h5>
                            <p>{{ $serviceItem->description }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                        <div>
                            <small class="text-muted">Creado: {{ $serviceItem->created_at->format('d/m/Y H:i') }}</small>
                            @if($serviceItem->updated_at->ne($serviceItem->created_at))
                                <br>
                                <small class="text-muted">Actualizado: {{ $serviceItem->updated_at->format('d/m/Y H:i') }}</small>
                            @endif
                        </div>
                        
                        <form action="{{ route('admin.vehicles.service-items.destroy', [$vehicle->id, $serviceItem->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este registro?')">
                                <i class="fas fa-trash"></i> Eliminar Registro
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Vehículo</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-truck fa-3x text-gray-500 mb-2"></i>
                    </div>
                    
                    <h5 class="font-weight-bold">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</h5>
                    <p>
                        <strong>VIN:</strong> {{ $vehicle->vin }}<br>
                        <strong>Tipo:</strong> {{ $vehicle->type }}<br>
                        <strong>Placa:</strong> {{ $vehicle->registration_number }} ({{ $vehicle->registration_state }})<br>
                        <strong>Vencimiento registro:</strong> {{ \Carbon\Carbon::parse($vehicle->registration_expiration_date)->format('d/m/Y') }}
                    </p>
                    
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-info-circle"></i> Ver Detalles del Vehículo
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recordatorio</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-calendar-alt fa-3x text-{{ $serviceItem->next_service_date->isPast() ? 'danger' : ($serviceItem->next_service_date->diffInDays(now()) < 15 ? 'warning' : 'success') }} mb-2"></i>
                    </div>
                    
                    <p class="text-center">
                        @if($serviceItem->next_service_date->isPast())
                            <span class="font-weight-bold text-danger">Este servicio está vencido.</span><br>
                            Debió realizarse hace {{ $serviceItem->next_service_date->diffInDays(now()) }} días.
                        @elseif($serviceItem->next_service_date->diffInDays(now()) < 15)
                            <span class="font-weight-bold text-warning">Próximo mantenimiento cercano.</span><br>
                            Programado para {{ $serviceItem->next_service_date->diffInDays(now()) }} días.
                        @else
                            <span class="font-weight-bold text-success">Mantenimiento al día.</span><br>
                            Próximo servicio en {{ $serviceItem->next_service_date->diffInDays(now()) }} días.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection