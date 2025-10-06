@extends('../layout/' . $layout)

@section('subhead')
    <title>Editar Driver Type - {{ config('app.name') }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Editar Driver Type #{{ $driverApplication->id }}</h2>
        <a href="{{ route('admin.driver-types.index') }}" class="btn btn-outline-secondary mr-2">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Volver al Listado
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger show mb-2" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success show mb-2" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.driver-types.update', $driverApplication->id) }}" class="grid grid-cols-12 gap-6 mt-5">
        @csrf
        @method('PUT')
        
        <!-- Vehicle Information -->
        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="intro-y box p-5">
                <div class="flex items-center pb-5 mb-5 border-b border-slate-200/60">
                    <div class="font-medium text-base mr-auto">Información del Vehículo</div>
                </div>
                
                <div class="mb-4">
                    <label for="vehicle_id" class="form-label">Vehículo *</label>
                    <select name="vehicle_id" id="vehicle_id" class="form-select" required>
                        <option value="">Seleccionar Vehículo</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ $driverApplication->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->unit_number }} - {{ $vehicle->carrier ? $vehicle->carrier->name : 'Sin Carrier' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="ownership_type" class="form-label">Tipo de Ownership *</label>
                    <select name="ownership_type" id="ownership_type" class="form-select" required>
                        <option value="">Seleccionar Tipo</option>
                        <option value="company_driver" {{ $driverApplication->ownership_type == 'company_driver' ? 'selected' : '' }}>Company Driver</option>
                        <option value="owner_operator" {{ $driverApplication->ownership_type == 'owner_operator' ? 'selected' : '' }}>Owner Operator</option>
                        <option value="third_party" {{ $driverApplication->ownership_type == 'third_party' ? 'selected' : '' }}>Third Party</option>
                        <option value="other" {{ $driverApplication->ownership_type == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Driver Details -->
        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="intro-y box p-5">
                <div class="flex items-center pb-5 mb-5 border-b border-slate-200/60">
                    <div class="font-medium text-base mr-auto">Detalles del Conductor</div>
                </div>
                
                <!-- Company Driver Fields -->
                <div id="company_driver_fields" class="ownership-fields" style="display: {{ $driverApplication->ownership_type == 'company_driver' ? 'block' : 'none' }}">
                    <div class="mb-4">
                        <label for="driver_name" class="form-label">Nombre del Conductor</label>
                        <input type="text" name="driver_name" id="driver_name" class="form-control" 
                               value="{{ $driverApplication->userDriverDetail ? $driverApplication->userDriverDetail->driver_name : '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="driver_phone" class="form-label">Teléfono del Conductor</label>
                        <input type="text" name="driver_phone" id="driver_phone" class="form-control" 
                               value="{{ $driverApplication->userDriverDetail ? $driverApplication->userDriverDetail->driver_phone : '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="license_number" class="form-label">Número de Licencia</label>
                        <input type="text" name="license_number" id="license_number" class="form-control" 
                               value="{{ $driverApplication->userDriverDetail ? $driverApplication->userDriverDetail->license_number : '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="license_expiration" class="form-label">Fecha de Expiración de Licencia</label>
                        <input type="date" name="license_expiration" id="license_expiration" class="form-control" 
                               value="{{ $driverApplication->userDriverDetail ? $driverApplication->userDriverDetail->license_expiration : '' }}">
                    </div>
                </div>

                <!-- Owner Operator Fields -->
                <div id="owner_operator_fields" class="ownership-fields" style="display: {{ $driverApplication->ownership_type == 'owner_operator' ? 'block' : 'none' }}">
                    <div class="mb-4">
                        <label for="owner_name" class="form-label">Nombre del Owner Operator</label>
                        <input type="text" name="owner_name" id="owner_name" class="form-control" 
                               value="{{ $driverApplication->ownerOperatorDetail ? $driverApplication->ownerOperatorDetail->owner_name : '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="owner_phone" class="form-label">Teléfono del Owner Operator</label>
                        <input type="text" name="owner_phone" id="owner_phone" class="form-control" 
                               value="{{ $driverApplication->ownerOperatorDetail ? $driverApplication->ownerOperatorDetail->owner_phone : '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="owner_license_number" class="form-label">Número de Licencia</label>
                        <input type="text" name="owner_license_number" id="owner_license_number" class="form-control" 
                               value="{{ $driverApplication->ownerOperatorDetail ? $driverApplication->ownerOperatorDetail->license_number : '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="owner_license_expiration" class="form-label">Fecha de Expiración de Licencia</label>
                        <input type="date" name="owner_license_expiration" id="owner_license_expiration" class="form-control" 
                               value="{{ $driverApplication->ownerOperatorDetail ? $driverApplication->ownerOperatorDetail->license_expiration : '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="owner_company_name" class="form-label">Nombre de la Compañía</label>
                        <input type="text" name="owner_company_name" id="owner_company_name" class="form-control" 
                               value="{{ $driverApplication->ownerOperatorDetail ? $driverApplication->ownerOperatorDetail->company_name : '' }}">
                    </div>
                </div>

                <!-- Third Party Fields -->
                <div id="third_party_fields" class="ownership-fields" style="display: {{ $driverApplication->ownership_type == 'third_party' ? 'block' : 'none' }}">
                    <div class="mb-4">
                        <label for="third_party_name" class="form-label">Nombre del Third Party</label>
                        <input type="text" name="third_party_name" id="third_party_name" class="form-control" 
                               value="{{ $driverApplication->thirdPartyDetail ? $driverApplication->thirdPartyDetail->third_party_name : '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="third_party_phone" class="form-label">Teléfono del Third Party</label>
                        <input type="text" name="third_party_phone" id="third_party_phone" class="form-control" 
                               value="{{ $driverApplication->thirdPartyDetail ? $driverApplication->thirdPartyDetail->third_party_phone : '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="third_party_license_number" class="form-label">Número de Licencia</label>
                        <input type="text" name="third_party_license_number" id="third_party_license_number" class="form-control" 
                               value="{{ $driverApplication->thirdPartyDetail ? $driverApplication->thirdPartyDetail->license_number : '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="third_party_license_expiration" class="form-label">Fecha de Expiración de Licencia</label>
                        <input type="date" name="third_party_license_expiration" id="third_party_license_expiration" class="form-control" 
                               value="{{ $driverApplication->thirdPartyDetail ? $driverApplication->thirdPartyDetail->license_expiration : '' }}">
                    </div>
                    <div class="mb-4">
                        <label for="third_party_company_name" class="form-label">Nombre de la Compañía</label>
                        <input type="text" name="third_party_company_name" id="third_party_company_name" class="form-control" 
                               value="{{ $driverApplication->thirdPartyDetail ? $driverApplication->thirdPartyDetail->company_name : '' }}">
                    </div>
                </div>

                <!-- Other Fields -->
                <div id="other_fields" class="ownership-fields" style="display: {{ $driverApplication->ownership_type == 'other' ? 'block' : 'none' }}">
                    <div class="mb-4">
                        <label for="other_description" class="form-label">Descripción</label>
                        <textarea name="other_description" id="other_description" class="form-control" rows="3">{{ $driverApplication->other_description ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="intro-y col-span-12">
            <div class="flex items-center justify-center sm:justify-end mt-5">
                <a href="{{ route('admin.driver-types.index') }}" class="btn btn-outline-secondary w-24 mr-1">Cancelar</a>
                <button type="submit" class="btn btn-primary w-24">Actualizar</button>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script type="module">
        import { createIcons, Lucide } from "@/base-components/Lucide";
        
        // Recreate icons
        createIcons({
            icons: {
                "Lucide": Lucide,
            },
        });

        // Handle ownership type change
        document.getElementById('ownership_type').addEventListener('change', function() {
            const selectedType = this.value;
            const allFields = document.querySelectorAll('.ownership-fields');
            
            // Hide all fields
            allFields.forEach(field => {
                field.style.display = 'none';
            });
            
            // Show selected fields
            if (selectedType) {
                const targetFields = document.getElementById(selectedType + '_fields');
                if (targetFields) {
                    targetFields.style.display = 'block';
                }
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const ownershipType = document.getElementById('ownership_type').value;
            if (ownershipType) {
                const targetFields = document.getElementById(ownershipType + '_fields');
                if (targetFields) {
                    targetFields.style.display = 'block';
                }
            }
        });
    </script>
@endsection