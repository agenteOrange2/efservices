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
            <h1 class="h3 mb-0 text-gray-800">Editar Registro de Mantenimiento</h1>
            <p class="mb-0">Vehículo: {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }}) - VIN: {{ $vehicle->vin }}</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.vehicles.service-items.index', $vehicle->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Mantenimientos
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Datos del Servicio</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.vehicles.service-items.update', [$vehicle->id, $serviceItem->id]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="unit">Unidad/Sistema <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unit" name="unit" 
                                value="{{ old('unit', $serviceItem->unit) }}" required>
                            <small class="form-text text-muted">Identifica el sistema o parte del vehículo atendida</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="service_tasks">Tareas realizadas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="service_tasks" name="service_tasks" 
                                value="{{ old('service_tasks', $serviceItem->service_tasks) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="service_date">Fecha del servicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="service_date" name="service_date" 
                                value="{{ old('service_date', $serviceItem->service_date->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="next_service_date">Próximo servicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="next_service_date" name="next_service_date" 
                                value="{{ old('next_service_date', $serviceItem->next_service_date->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="odometer">Odómetro (millas)</label>
                            <input type="number" class="form-control" id="odometer" name="odometer" 
                                value="{{ old('odometer', $serviceItem->odometer) }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vendor_mechanic">Proveedor/Mecánico <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="vendor_mechanic" name="vendor_mechanic" 
                                value="{{ old('vendor_mechanic', $serviceItem->vendor_mechanic) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cost">Costo ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="cost" name="cost" 
                                value="{{ old('cost', $serviceItem->cost) }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Descripción/Notas</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $serviceItem->description) }}</textarea>
                </div>

                <div class="form-group text-right mt-4">
                    <button type="submit" class="btn btn-primary">Actualizar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection