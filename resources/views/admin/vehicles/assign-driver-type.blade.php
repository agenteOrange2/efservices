@extends('../themes/' . $activeTheme)
@section('title', 'Asign type vehicle')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
        ['label' => 'Assign type Vehicle', 'active' => true],
    ];
@endphp
@section('subcontent')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-user-plus"></i> Asignar Tipo de Conductor
                        </h3>
                        <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver al Vehículo
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información del vehículo -->
                    <div class="alert alert-info border-left-info">
                        <div class="d-flex align-items-center">
                            <div class="alert-icon">
                                <i class="fas fa-car fa-2x text-info"></i>
                            </div>
                            <div class="ml-3">
                                <h5 class="alert-heading mb-2">Información del Vehículo</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Marca:</strong> {{ $vehicle->make }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Modelo:</strong> {{ $vehicle->model }}
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Año:</strong> {{ $vehicle->year }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>VIN:</strong> {{ $vehicle->vin }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-clipboard-list"></i> Formulario de Asignación
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.vehicles.store-driver-type', $vehicle->id) }}" method="POST" id="driverTypeForm">
                        @csrf
                        
                        <!-- Selección del tipo de conductor -->
                        <div class="card mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user-tag"></i> Selección de Tipo de Conductor
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="ownership_type" class="font-weight-bold">Tipo de Conductor <span class="text-danger">*</span></label>
                                    <select name="ownership_type" id="ownership_type" class="form-control form-control-lg @error('ownership_type') is-invalid @enderror" required>
                                        <option value="">-- Seleccione un tipo de conductor --</option>
                                        <option value="company_driver" {{ old('ownership_type') == 'company_driver' ? 'selected' : '' }}>
                                            <i class="fas fa-building"></i> Company Driver
                                        </option>
                                        <option value="owner_operator" {{ old('ownership_type') == 'owner_operator' ? 'selected' : '' }}>
                                            <i class="fas fa-user-tie"></i> Owner Operator
                                        </option>
                                        <option value="third_party" {{ old('ownership_type') == 'third_party' ? 'selected' : '' }}>
                                            <i class="fas fa-handshake"></i> Third Party
                                        </option>
                                        <option value="other" {{ old('ownership_type') == 'other' ? 'selected' : '' }}>
                                            <i class="fas fa-question-circle"></i> Other
                                        </option>
                                    </select>
                                    @error('ownership_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Seleccione el tipo de conductor que mejor describa la relación con el vehículo.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Campos para Owner Operator -->
                        <div id="owner_operator_fields" class="conditional-fields" style="display: none;">
                            <div class="card border-success mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-tie"></i> Información del Owner Operator
                                    </h5>
                                    <small class="text-light">Complete la información del propietario-operador del vehículo</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="owner_name" class="font-weight-bold">
                                                    <i class="fas fa-user"></i> Nombre Completo <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="owner_name" id="owner_name" 
                                                       class="form-control @error('owner_name') is-invalid @enderror" 
                                                       value="{{ old('owner_name') }}" 
                                                       placeholder="Ingrese el nombre completo">
                                                @error('owner_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="owner_phone" class="font-weight-bold">
                                                    <i class="fas fa-phone"></i> Teléfono <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="owner_phone" id="owner_phone" 
                                                       class="form-control @error('owner_phone') is-invalid @enderror" 
                                                       value="{{ old('owner_phone') }}" 
                                                       placeholder="(555) 123-4567">
                                                @error('owner_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="owner_email" class="font-weight-bold">
                                                    <i class="fas fa-envelope"></i> Email <span class="text-danger">*</span>
                                                </label>
                                                <input type="email" name="owner_email" id="owner_email" 
                                                       class="form-control @error('owner_email') is-invalid @enderror" 
                                                       value="{{ old('owner_email') }}" 
                                                       placeholder="ejemplo@correo.com">
                                                @error('owner_email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Nota:</strong> Esta información será utilizada para contactar al propietario-operador del vehículo.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Campos para Third Party -->
                        <div id="third_party_fields" class="conditional-fields" style="display: none;">
                            <div class="card border-warning mb-4">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">
                                        <i class="fas fa-handshake"></i> Información de Third Party Company
                                    </h5>
                                    <small>Complete la información de la empresa tercera que maneja el vehículo</small>
                                </div>
                                <div class="card-body">
                                    <!-- Información Básica -->
                                    <div class="mb-4">
                                        <h6 class="text-warning font-weight-bold mb-3">
                                            <i class="fas fa-info-circle"></i> Información Básica
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="third_party_name" class="font-weight-bold">
                                                        <i class="fas fa-building"></i> Nombre de la Compañía <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="third_party_name" id="third_party_name" 
                                                           class="form-control @error('third_party_name') is-invalid @enderror" 
                                                           value="{{ old('third_party_name') }}" 
                                                           placeholder="Nombre de la empresa">
                                                    @error('third_party_name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="third_party_phone" class="font-weight-bold">
                                                        <i class="fas fa-phone"></i> Teléfono <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="third_party_phone" id="third_party_phone" 
                                                           class="form-control @error('third_party_phone') is-invalid @enderror" 
                                                           value="{{ old('third_party_phone') }}" 
                                                           placeholder="(555) 123-4567">
                                                    @error('third_party_phone')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="third_party_email" class="font-weight-bold">
                                                        <i class="fas fa-envelope"></i> Email <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="email" name="third_party_email" id="third_party_email" 
                                                           class="form-control @error('third_party_email') is-invalid @enderror" 
                                                           value="{{ old('third_party_email') }}" 
                                                           placeholder="empresa@correo.com">
                                                    @error('third_party_email')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Información Legal -->
                                    <div class="mb-4">
                                        <h6 class="text-warning font-weight-bold mb-3">
                                            <i class="fas fa-gavel"></i> Información Legal
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="third_party_dba" class="font-weight-bold">
                                                        <i class="fas fa-certificate"></i> DBA (Doing Business As)
                                                    </label>
                                                    <input type="text" name="third_party_dba" id="third_party_dba" 
                                                           class="form-control @error('third_party_dba') is-invalid @enderror" 
                                                           value="{{ old('third_party_dba') }}" 
                                                           placeholder="Nombre comercial">
                                                    @error('third_party_dba')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="third_party_fein" class="font-weight-bold">
                                                        <i class="fas fa-id-card"></i> FEIN (Federal Employer ID Number)
                                                    </label>
                                                    <input type="text" name="third_party_fein" id="third_party_fein" 
                                                           class="form-control @error('third_party_fein') is-invalid @enderror" 
                                                           value="{{ old('third_party_fein') }}" 
                                                           placeholder="XX-XXXXXXX">
                                                    @error('third_party_fein')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Información de Contacto -->
                                    <div class="mb-3">
                                        <h6 class="text-warning font-weight-bold mb-3">
                                            <i class="fas fa-map-marker-alt"></i> Información de Contacto
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="third_party_address" class="font-weight-bold">
                                                        <i class="fas fa-home"></i> Dirección
                                                    </label>
                                                    <textarea name="third_party_address" id="third_party_address" 
                                                              class="form-control @error('third_party_address') is-invalid @enderror" 
                                                              rows="2" 
                                                              placeholder="Dirección completa de la empresa">{{ old('third_party_address') }}</textarea>
                                                    @error('third_party_address')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="third_party_contact" class="font-weight-bold">
                                                        <i class="fas fa-user-circle"></i> Persona de Contacto
                                                    </label>
                                                    <input type="text" name="third_party_contact" id="third_party_contact" 
                                                           class="form-control @error('third_party_contact') is-invalid @enderror" 
                                                           value="{{ old('third_party_contact') }}" 
                                                           placeholder="Nombre del contacto">
                                                    @error('third_party_contact')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Importante:</strong> Asegúrese de que toda la información legal de la empresa sea correcta y esté actualizada.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje para Company Driver y Other -->
                        <div id="company_driver_message" class="conditional-fields" style="display: none;">
                            <div class="card border-info mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-tie"></i> Company Driver
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Información:</strong> Este vehículo será manejado por un conductor de la empresa. 
                                        No se requiere información adicional del conductor ya que está registrado en el sistema de empleados.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="other_message" class="conditional-fields" style="display: none;">
                            <div class="card border-secondary mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-question-circle"></i> Otro Tipo de Conductor
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Atención:</strong> Se ha seleccionado un tipo de conductor personalizado. 
                                        Por favor contacte al administrador del sistema para configurar los detalles específicos de este tipo de conductor.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="card mt-4">
                            <div class="card-body text-center">
                                <button type="submit" class="btn btn-success btn-lg mr-3">
                                    <i class="fas fa-check-circle"></i> Asignar Tipo de Conductor
                                </button>
                                <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-arrow-left"></i> Cancelar y Volver
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Función para mostrar/ocultar campos según el tipo seleccionado
    function toggleFields() {
        const selectedType = $('#ownership_type').val();
        
        // Ocultar todos los campos condicionales
        $('.conditional-fields').hide();
        
        // Mostrar campos según la selección
        if (selectedType === 'owner_operator') {
            $('#owner_operator_fields').show();
        } else if (selectedType === 'third_party') {
            $('#third_party_fields').show();
        } else if (selectedType === 'company_driver') {
            $('#company_driver_message').show();
        } else if (selectedType === 'other') {
            $('#other_message').show();
        }
    }
    
    // Ejecutar al cargar la página
    toggleFields();
    
    // Ejecutar cuando cambie la selección
    $('#ownership_type').change(function() {
        toggleFields();
    });
    
    // Validación del formulario
    $('#driverTypeForm').submit(function(e) {
        const selectedType = $('#ownership_type').val();
        
        if (!selectedType) {
            e.preventDefault();
            alert('Por favor seleccione un tipo de conductor.');
            return false;
        }
        
        // Validar campos requeridos para owner_operator
        if (selectedType === 'owner_operator') {
            const ownerName = $('#owner_name').val().trim();
            const ownerPhone = $('#owner_phone').val().trim();
            const ownerEmail = $('#owner_email').val().trim();
            
            if (!ownerName || !ownerPhone || !ownerEmail) {
                e.preventDefault();
                alert('Por favor complete todos los campos requeridos para Owner Operator.');
                return false;
            }
        }
        
        // Validar campos requeridos para third_party
        if (selectedType === 'third_party') {
            const thirdPartyName = $('#third_party_name').val().trim();
            const thirdPartyPhone = $('#third_party_phone').val().trim();
            const thirdPartyEmail = $('#third_party_email').val().trim();
            
            if (!thirdPartyName || !thirdPartyPhone || !thirdPartyEmail) {
                e.preventDefault();
                alert('Por favor complete todos los campos requeridos para Third Party Company.');
                return false;
            }
        }
    });
});
</script>
@endpush