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
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Unidad</th>
                                <th scope="col" class="px-6 py-3">Fecha Servicio</th>
                                <th scope="col" class="px-6 py-3">Próximo Servicio</th>
                                <th scope="col" class="px-6 py-3">Tareas</th>
                                <th scope="col" class="px-6 py-3">Proveedor</th>
                                <th scope="col" class="px-6 py-3">Costo</th>
                                <th scope="col" class="px-6 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            @foreach($serviceItems as $item)
                                <tr>
                                    <td class="px-6 py-4">{{ $item->unit }}</td>
                                    <td class="px-6 py-4">{{ $item->service_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4">
                                        {{ $item->next_service_date->format('d/m/Y') }}
                                        @if($item->next_service_date->isPast())
                                            <span class="badge badge-danger">Vencido</span>
                                        @elseif($item->next_service_date->diffInDays(now()) < 15)
                                            <span class="badge badge-warning">Próximo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $item->service_tasks }}</td>
                                    <td class="px-6 py-4">{{ $item->vendor_mechanic }}</td>
                                    <td class="px-6 py-4">${{ number_format($item->cost, 2) }}</td>
                                    <td class="px-6 py-4">
                                        
                                        <div class="btn-group flex gap-3" role="group">
                                            <a href="{{ route('admin.vehicles.service-items.show', [$vehicle->id, $item->id]) }}" class="btn btn-info btn-sm">
                                                <x-base.lucide class="h-4 w-4" icon="Eye" />
                                            </a>
                                            <a href="{{ route('admin.vehicles.service-items.edit', [$vehicle->id, $item->id]) }}" class="btn btn-primary btn-sm">
                                                <x-base.lucide class="h-4 w-4" icon="Edit" />
                                            </a>
                                            <form action="{{ route('admin.vehicles.service-items.destroy', [$vehicle->id, $item->id]) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este registro?')">
                                                    <x-base.lucide class="h-4 w-4" icon="Trash" />                                                    
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