@extends('../themes/' . $activeTheme)
@section('title', 'Registrar Accidente')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Reportes', 'url' => route('admin.reports.index')],
        ['label' => 'Registrar Accidente', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h1 class="mb-2">Registrar Nuevo Accidente</h1>
            <p class="text-muted">Completa el formulario para registrar un nuevo accidente</p>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Volver a Reportes
            </a>
            <a href="{{ route('admin.reports.accidents-list') }}" class="btn btn-sm btn-info mb-3 ml-2">
                <i class="fas fa-list"></i> Ver Lista de Accidentes
            </a>
        </div>
    </div>

    <!-- Formulario de Registro de Accidente -->
    <div class="card shadow">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Datos del Accidente</h5>
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

            <form action="{{ route('admin.reports.store-accident') }}" method="POST" enctype="multipart/form-data" id="accident-form">
                @csrf
                
                <div class="row">
                    <!-- Selección de Carrier -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="carrier_id">Carrier <span class="text-danger">*</span></label>
                            <select name="carrier_id" id="carrier_id" class="form-control @error('carrier_id') is-invalid @enderror" required>
                                <option value="">Seleccionar Carrier</option>
                                @foreach($carriers as $carrier)
                                    <option value="{{ $carrier->id }}" {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                        {{ $carrier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('carrier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Selección de Conductor -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="driver_id">Conductor <span class="text-danger">*</span></label>
                            <select name="driver_id" id="driver_id" class="form-control @error('driver_id') is-invalid @enderror" required>
                                <option value="">Primero selecciona un Carrier</option>
                            </select>
                            @error('driver_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Fecha del accidente -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="accident_date">Fecha del Accidente <span class="text-danger">*</span></label>
                            <div x-data="{ datePickerOpen: false }">
                                <div class="input-group">
                                    <input 
                                        type="text" 
                                        id="accident_date" 
                                        name="accident_date" 
                                        class="form-control date-picker @error('accident_date') is-invalid @enderror" 
                                        placeholder="MM-DD-YYYY" 
                                        required
                                        autocomplete="off"
                                        value="{{ old('accident_date') }}"
                                        readonly
                                    >
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" @click="datePickerOpen = !datePickerOpen">
                                            <i class="fas fa-calendar"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('accident_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ubicación -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="location">Ubicación <span class="text-danger">*</span></label>
                            <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Descripción -->
                <div class="form-group">
                    <label for="description">Descripción del Accidente <span class="text-danger">*</span></label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row">
                    <!-- Descripción de daños -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="damage_description">Descripción de Daños</label>
                            <textarea name="damage_description" id="damage_description" class="form-control @error('damage_description') is-invalid @enderror" rows="3">{{ old('damage_description') }}</textarea>
                            @error('damage_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Descripción de lesiones -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="injury_description">Descripción de Lesiones</label>
                            <textarea name="injury_description" id="injury_description" class="form-control @error('injury_description') is-invalid @enderror" rows="3">{{ old('injury_description') }}</textarea>
                            @error('injury_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Número de reporte policial -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="police_report_number">Número de Reporte Policial</label>
                            <input type="text" name="police_report_number" id="police_report_number" class="form-control @error('police_report_number') is-invalid @enderror" value="{{ old('police_report_number') }}">
                            @error('police_report_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Citación emitida -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Citación Emitida</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="citation_issued" name="citation_issued" value="1" {{ old('citation_issued') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="citation_issued">Sí, se emitió citación</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prevenible -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Prevenible</label>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="preventable" name="preventable" value="1" {{ old('preventable') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="preventable">Sí, era prevenible</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Subida de documentos -->
                <div class="form-group">
                    <label for="documents">Documentos (PDF, DOC, DOCX, JPG, PNG)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @error('documents.*') is-invalid @enderror" id="documents" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <label class="custom-file-label" for="documents">Seleccionar archivos</label>
                    </div>
                    <small class="text-muted">Puedes seleccionar múltiples archivos. Tamaño máximo: 10MB por archivo.</small>
                    @error('documents.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Accidente
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Limpiar Formulario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar el datepicker para la fecha de accidente
        if (document.querySelector('.date-picker')) {
            const datepicker = new Pikaday({
                field: document.getElementById('accident_date'),
                format: 'MM-DD-YYYY',
                onSelect: function() {
                    document.getElementById('accident_date').dispatchEvent(new Event('change'));
                }
            });
        }

        // Manejo de la selección de archivos (mostrar nombres)
        const documentsInput = document.getElementById('documents');
        if (documentsInput) {
            documentsInput.addEventListener('change', function(e) {
                const fileLabel = document.querySelector('.custom-file-label');
                if (e.target.files.length > 1) {
                    fileLabel.textContent = `${e.target.files.length} archivos seleccionados`;
                } else if (e.target.files.length === 1) {
                    fileLabel.textContent = e.target.files[0].name;
                } else {
                    fileLabel.textContent = 'Seleccionar archivos';
                }
            });
        }

        // Evento para cargar conductores cuando se selecciona un carrier
        const carrierSelect = document.getElementById('carrier_id');
        const driverSelect = document.getElementById('driver_id');

        if (carrierSelect && driverSelect) {
            carrierSelect.addEventListener('change', async function() {
                const carrierId = this.value;
                driverSelect.innerHTML = '<option value="">Cargando conductores...</option>';
                
                if (!carrierId) {
                    driverSelect.innerHTML = '<option value="">Primero selecciona un Carrier</option>';
                    return;
                }

                try {
                    const response = await fetch(`/api/active-drivers-by-carrier/${carrierId}`);
                    if (!response.ok) {
                        throw new Error('Error al cargar los conductores');
                    }
                    
                    const drivers = await response.json();
                    
                    if (drivers.length === 0) {
                        driverSelect.innerHTML = '<option value="">No hay conductores disponibles</option>';
                        return;
                    }
                    
                    let options = '<option value="">Seleccionar Conductor</option>';
                    drivers.forEach(driver => {
                        options += `<option value="${driver.id}">${driver.user.name}</option>`;
                    });
                    
                    driverSelect.innerHTML = options;
                } catch (error) {
                    console.error('Error:', error);
                    driverSelect.innerHTML = '<option value="">Error al cargar conductores</option>';
                }
            });
        }
    });
</script>
@endsection
