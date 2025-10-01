@extends('../themes/' . $activeTheme)
@section('title', 'Details License')
@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Licenses', 'url' => route('admin.licenses.index')],
['label' => 'Details License', 'active' => true],
];
@endphp

@section('subcontent')
<div class="container-fluid">
    <!-- Flash Messages -->
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
        <div>
            <h1 class="h3 mb-0">Detalles de Licencia</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.licenses.index') }}">Licencias</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detalles</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('admin.licenses.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
            <a href="{{ route('admin.licenses.edit', $license->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Editar
            </a>
            <a href="{{ route('admin.licenses.docs.show', $license->id) }}" class="btn btn-info">
                <i class="fas fa-file-alt me-1"></i>Documentos ({{ $license->getMedia('license_documents')->count() }})
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información Básica -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-id-card me-2"></i>Información de la Licencia
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Conductor:</label>
                            <p class="mb-0">{{ $license->driver->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Transportista:</label>
                            <p class="mb-0">{{ $license->driver->carrier->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Número de Licencia Actual:</label>
                            <p class="mb-0">{{ $license->current_license_number ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Clase de Licencia:</label>
                            <p class="mb-0">
                                @if($license->license_class)
                                    <span class="badge bg-primary">{{ $license->license_class }}</span>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Estado de Emisión:</label>
                            <p class="mb-0">{{ $license->state_issued ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha de Emisión:</label>
                            <p class="mb-0">
                                @if($license->issue_date)
                                    {{ \Carbon\Carbon::parse($license->issue_date)->format('d/m/Y') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha de Expiración:</label>
                            <p class="mb-0">
                                @if($license->expiration_date)
                                    {{ \Carbon\Carbon::parse($license->expiration_date)->format('d/m/Y') }}
                                    @php
                                        $expirationDate = \Carbon\Carbon::parse($license->expiration_date);
                                        $now = \Carbon\Carbon::now();
                                        $daysUntilExpiration = $now->diffInDays($expirationDate, false);
                                    @endphp
                                    @if($daysUntilExpiration < 0)
                                        <span class="badge bg-danger ms-2">Expirada</span>
                                    @elseif($daysUntilExpiration <= 30)
                                        <span class="badge bg-warning ms-2">Expira pronto</span>
                                    @else
                                        <span class="badge bg-success ms-2">Vigente</span>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Restricciones:</label>
                            <p class="mb-0">{{ $license->restrictions ?? 'Ninguna' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado de la Licencia -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Estado de la Licencia
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($license->expiration_date)
                        @php
                            $expirationDate = \Carbon\Carbon::parse($license->expiration_date);
                            $now = \Carbon\Carbon::now();
                            $daysUntilExpiration = $now->diffInDays($expirationDate, false);
                        @endphp
                        @if($daysUntilExpiration < 0)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <h6>Licencia Expirada</h6>
                                <p class="mb-0">Expiró hace {{ abs($daysUntilExpiration) }} días</p>
                            </div>
                        @elseif($daysUntilExpiration <= 30)
                            <div class="alert alert-warning">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h6>Expira Pronto</h6>
                                <p class="mb-0">{{ $daysUntilExpiration }} días restantes</p>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h6>Licencia Vigente</h6>
                                <p class="mb-0">{{ $daysUntilExpiration }} días restantes</p>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-secondary">
                            <i class="fas fa-question-circle fa-2x mb-2"></i>
                            <h6>Estado Desconocido</h6>
                            <p class="mb-0">No se ha establecido fecha de expiración</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Documentos -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>Documentos Adjuntos
                    </h5>
                    <a href="{{ route('admin.licenses.docs.show', $license->id) }}" class="btn btn-sm btn-outline-primary">
                        Ver todos los documentos
                    </a>
                </div>
                <div class="card-body">
                    @if($license->getMedia('license_documents')->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre del Documento</th>
                                        <th>Tipo</th>
                                        <th>Tamaño</th>
                                        <th>Fecha de Subida</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($license->getMedia('license_documents')->take(5) as $document)
                                        <tr>
                                            <td>
                                                <i class="fas fa-file me-2"></i>
                                                {{ $document->name }}
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ strtoupper($document->mime_type) }}</span>
                                            </td>
                                            <td>{{ $document->human_readable_size }}</td>
                                            <td>{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.licenses.doc.preview', $document->id) }}" 
                                                       class="btn btn-outline-primary" 
                                                       target="_blank" 
                                                       title="Ver documento">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            onclick="confirmDelete({{ $document->id }})" 
                                                            title="Eliminar documento">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($license->getMedia('license_documents')->count() > 5)
                            <div class="text-center mt-3">
                                <p class="text-muted">Mostrando 5 de {{ $license->getMedia('license_documents')->count() }} documentos</p>
                                <a href="{{ route('admin.licenses.docs.show', $license->id) }}" class="btn btn-outline-primary">
                                    Ver todos los documentos
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No hay documentos adjuntos</h6>
                            <p class="text-muted mb-3">No se han subido documentos para esta licencia.</p>
                            <a href="{{ route('admin.licenses.edit', $license->id) }}" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i>Subir Documentos
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar documento -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar este documento? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(documentId) {
    const form = document.getElementById('deleteForm');
    form.action = `{{ url('admin/licenses/document') }}/${documentId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush