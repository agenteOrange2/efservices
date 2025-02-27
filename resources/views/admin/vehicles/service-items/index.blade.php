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
            <h1 class="h3 mb-0 text-gray-800">Historial de Mantenimiento</h1>
            <p class="mb-0">Vehículo: {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }}) - VIN: {{ $vehicle->vin }}</p>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Vehículo
            </a>
            <a href="{{ route('admin.vehicles.service-items.create', $vehicle->id) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Añadir Mantenimiento
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Registros de Servicio</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($serviceItems->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="serviceItemsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Unidad</th>
                                <th>Fecha Servicio</th>
                                <th>Próximo Servicio</th>
                                <th>Tareas</th>
                                <th>Proveedor</th>
                                <th>Costo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceItems as $item)
                                <tr>
                                    <td>{{ $item->unit }}</td>
                                    <td>{{ $item->service_date->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $item->next_service_date->format('d/m/Y') }}
                                        @if($item->next_service_date->isPast())
                                            <span class="badge badge-danger">Vencido</span>
                                        @elseif($item->next_service_date->diffInDays(now()) < 15)
                                            <span class="badge badge-warning">Próximo</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->service_tasks }}</td>
                                    <td>{{ $item->vendor_mechanic }}</td>
                                    <td>${{ number_format($item->cost, 2) }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.vehicles.service-items.show', [$vehicle->id, $item->id]) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.vehicles.service-items.edit', [$vehicle->id, $item->id]) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.vehicles.service-items.destroy', [$vehicle->id, $item->id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este registro?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $serviceItems->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    No hay registros de mantenimiento para este vehículo. 
                    <a href="{{ route('admin.vehicles.service-items.create', $vehicle->id) }}">Agregar el primer registro</a>.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#serviceItemsTable').DataTable({
            paging: false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json"
            }
        });
    });
</script>
@endsection