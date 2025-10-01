@extends('../themes/' . $activeTheme)
@section('title', 'Medical Records')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Medical Records', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="container-fluid">
    <!-- Mensajes flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Registros Médicos</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.medical-records.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Agregar Registro Médico
            </a>
            <a href="{{ route('admin.medical-records.docs.all') }}" class="btn btn-info">
                <i class="fas fa-file-alt"></i> Ver Documentos
            </a>
        </div>
    </div>

    <!-- Alertas de expiración -->
    @php
        $expiringSoon = $medicalRecords->filter(function($record) {
            return $record->expiration_date && now()->diffInDays($record->expiration_date, false) <= 30 && now()->diffInDays($record->expiration_date, false) >= 0;
        });
        $expired = $medicalRecords->filter(function($record) {
            return $record->expiration_date && now()->diffInDays($record->expiration_date, false) < 0;
        });
    @endphp

    @if($expired->count() > 0)
        <div class="alert alert-danger" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Registros Médicos Vencidos</h5>
            <p>Hay {{ $expired->count() }} registro(s) médico(s) vencido(s) que requieren atención inmediata:</p>
            <ul class="mb-0">
                @foreach($expired->take(5) as $record)
                    <li>
                        <strong>{{ $record->driver->first_name }} {{ $record->driver->last_name }}</strong> - 
                        Vencido hace {{ abs(now()->diffInDays($record->expiration_date, false)) }} días
                        <a href="{{ route('admin.medical-records.edit', $record) }}" class="btn btn-sm btn-outline-light ms-2">
                            <i class="fas fa-edit"></i> Renovar
                        </a>
                    </li>
                @endforeach
                @if($expired->count() > 5)
                    <li><em>Y {{ $expired->count() - 5 }} más...</em></li>
                @endif
            </ul>
        </div>
    @endif

    @if($expiringSoon->count() > 0)
        <div class="alert alert-warning" role="alert">
            <h5 class="alert-heading"><i class="fas fa-clock"></i> Registros Médicos Por Vencer</h5>
            <p>Hay {{ $expiringSoon->count() }} registro(s) médico(s) que vencen en los próximos 30 días:</p>
            <ul class="mb-0">
                @foreach($expiringSoon->take(5) as $record)
                    <li>
                        <strong>{{ $record->driver->first_name }} {{ $record->driver->last_name }}</strong> - 
                        Vence en {{ now()->diffInDays($record->expiration_date, false) }} días
                        <a href="{{ route('admin.medical-records.edit', $record) }}" class="btn btn-sm btn-outline-dark ms-2">
                            <i class="fas fa-edit"></i> Renovar
                        </a>
                    </li>
                @endforeach
                @if($expiringSoon->count() > 5)
                    <li><em>Y {{ $expiringSoon->count() - 5 }} más...</em></li>
                @endif
            </ul>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.medical-records.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Buscar por tipo de examen...">
                </div>
                <div class="col-md-3">
                    <label for="driver_id" class="form-label">Conductor</label>
                    <select class="form-select" id="driver_id" name="driver_id">
                        <option value="">Todos los conductores</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->first_name }} {{ $driver->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Desde</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Hasta</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.medical-records.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de registros médicos -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-white text-decoration-none">
                                    Fecha Creación
                                    @if(request('sort') === 'created_at')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'driver_id', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-white text-decoration-none">
                                    Conductor
                                    @if(request('sort') === 'driver_id')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Tipo de Examen</th>
                            <th>Fecha Examen</th>
                            <th>Resultado</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'expiration_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-white text-decoration-none">
                                    Fecha Expiración
                                    @if(request('sort') === 'expiration_date')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Estado</th>
                            <th>Documentos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicalRecords as $record)
                            <tr>
                                <td>{{ $record->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $record->driver->first_name }} {{ $record->driver->last_name }}</strong><br>
                                    <small class="text-muted">{{ $record->driver->carrier->name ?? 'Sin transportista' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $record->examination_type }}</span>
                                </td>
                                <td>{{ $record->examination_date ? $record->examination_date->format('d/m/Y') : 'No especificada' }}</td>
                                <td>
                                    <span class="badge bg-{{ $record->result === 'passed' ? 'success' : ($record->result === 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($record->result) }}
                                    </span>
                                </td>
                                <td>{{ $record->expiration_date ? $record->expiration_date->format('d/m/Y') : 'No especificada' }}</td>
                                <td>
                                    @if($record->expiration_date)
                                        @php
                                            $daysUntilExpiration = now()->diffInDays($record->expiration_date, false);
                                            if ($daysUntilExpiration < 0) {
                                                $statusClass = 'danger';
                                                $statusText = 'Vencido';
                                                $statusIcon = 'exclamation-triangle';
                                            } elseif ($daysUntilExpiration <= 30) {
                                                $statusClass = 'warning';
                                                $statusText = 'Por vencer';
                                                $statusIcon = 'clock';
                                            } else {
                                                $statusClass = 'success';
                                                $statusText = 'Vigente';
                                                $statusIcon = 'check-circle';
                                            }
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            <i class="fas fa-{{ $statusIcon }}"></i> {{ $statusText }}
                                        </span>
                                        @if($daysUntilExpiration <= 30)
                                            <br><small class="text-muted">
                                                {{ $daysUntilExpiration > 0 ? 
                                                   'Vence en ' . $daysUntilExpiration . ' días' : 
                                                   'Vencido hace ' . abs($daysUntilExpiration) . ' días' }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-question"></i> Sin fecha
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $record->getMedia('documents')->count() }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.medical-records.docs.show', $record) }}" 
                                           class="btn btn-sm btn-outline-info" title="Ver documentos">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                        <a href="{{ route('admin.medical-records.edit', $record) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.medical-records.destroy', $record) }}" 
                                              method="POST" class="d-inline" 
                                              onsubmit="return confirm('¿Está seguro de eliminar este registro médico?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-user-md fa-3x mb-3"></i>
                                        <p>No se encontraron registros médicos.</p>
                                        <a href="{{ route('admin.medical-records.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Agregar Primer Registro Médico
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($medicalRecords->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $medicalRecords->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit del formulario de filtros cuando cambian los selects
    document.getElementById('driver_id').addEventListener('change', function() {
        this.form.submit();
    });
</script>
@endpush