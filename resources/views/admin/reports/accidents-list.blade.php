@extends('../themes/' . $activeTheme)
@section('title', 'Lista de Accidentes')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Reportes', 'url' => route('admin.reports.index')],
        ['label' => 'Lista de Accidentes', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="mb-2">Lista de Accidentes</h1>
            <p class="text-muted">Listado completo de accidentes registrados en el sistema</p>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Volver a Reportes
            </a>
            <a href="{{ route('admin.reports.register-accident') }}" class="btn btn-sm btn-primary mb-3 ml-2">
                <i class="fas fa-plus-circle"></i> Registrar Nuevo Accidente
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.accidents-list') }}" method="GET" id="filter-form">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="carrier_id">Carrier:</label>
                            <select name="carrier_id" id="filter_carrier_id" class="form-control">
                                <option value="">Todos los Carriers</option>
                                @foreach($carriers as $carrier)
                                <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                    {{ $carrier->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="driver_id">Conductor:</label>
                            <select name="driver_id" id="filter_driver_id" class="form-control">
                                <option value="">Todos los Conductores</option>
                                @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ optional($driver->user)->name ?? 'N/A' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.reports.accidents-list') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-sync"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Accidentes -->
    <div class="card shadow">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Accidentes Registrados</h5>
        </div>
        <div class="card-body">
            @if($accidents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Carrier</th>
                                <th>Conductor</th>
                                <th>Ubicación</th>
                                <th>Prevenible</th>
                                <th>Documentos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($accidents as $accident)
                            <tr>
                                <td>{{ $accident->id }}</td>
                                <td>{{ $accident->accident_date->format('m-d-Y') }}</td>
                                <td>
                                    @if($accident->carrier)
                                        {{ $accident->carrier->name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($accident->driver && $accident->driver->user)
                                        {{ $accident->driver->user->name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $accident->location }}</td>
                                <td>
                                    @if($accident->preventable)
                                        <span class="badge badge-danger">Sí</span>
                                    @else
                                        <span class="badge badge-success">No</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $accident->getMedia('accident_documents')->count() }} documento(s)
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.accidents.edit', $accident->id) }}" class="btn btn-sm btn-info" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.accidents.documents.show', $accident->id) }}" class="btn btn-sm btn-secondary" title="Ver documentos">
                                            <i class="fas fa-file"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-accident" data-id="{{ $accident->id }}" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <div class="mt-4">
                    {{ $accidents->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    No hay accidentes registrados que coincidan con los filtros.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Evento para cargar conductores cuando se selecciona un carrier en el filtro
        const filterCarrierSelect = document.getElementById('filter_carrier_id');
        const filterDriverSelect = document.getElementById('filter_driver_id');
        
        if (filterCarrierSelect && filterDriverSelect) {
            filterCarrierSelect.addEventListener('change', async function() {
                const carrierId = this.value;
                
                if (!carrierId) {
                    // No hacemos nada si no hay carrier seleccionado, mantener todos los conductores
                    return;
                }

                try {
                    filterDriverSelect.innerHTML = '<option value="">Cargando conductores...</option>';
                    const response = await fetch(`/api/active-drivers-by-carrier/${carrierId}`);
                    
                    if (!response.ok) {
                        throw new Error('Error al cargar los conductores');
                    }
                    
                    const drivers = await response.json();
                    
                    let options = '<option value="">Todos los Conductores</option>';
                    drivers.forEach(driver => {
                        options += `<option value="${driver.id}">${driver.user.name}</option>`;
                    });
                    
                    filterDriverSelect.innerHTML = options;
                } catch (error) {
                    console.error('Error:', error);
                    filterDriverSelect.innerHTML = '<option value="">Error al cargar conductores</option>';
                }
            });
        }

        // Confirmar eliminación de accidentes
        const deleteButtons = document.querySelectorAll('.delete-accident');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const accidentId = this.getAttribute('data-id');
                if (confirm('¿Estás seguro que deseas eliminar este accidente? Esta acción no se puede deshacer.')) {
                    // Crear formulario para enviar la solicitud DELETE
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/accidents/${accidentId}`;
                    form.style.display = 'none';
                    
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    
                    const tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = '_token';
                    tokenInput.value = document.querySelector('meta[name="csrf-token"]').content;
                    
                    form.appendChild(methodInput);
                    form.appendChild(tokenInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
