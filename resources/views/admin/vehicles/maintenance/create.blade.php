@extends('../themes/' . $activeTheme)
@section('title', 'Nuevo Mantenimiento')
@php
$breadcrumbLinks = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Vehículos', 'url' => route('admin.vehicles.index')],
    ['label' => 'Mantenimiento', 'url' => route('admin.maintenance.index')],
    ['label' => 'Nuevo Mantenimiento', 'active' => true],
];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Nuevo Registro de Mantenimiento
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.maintenance.index') }}"
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowLeft" />
                    Volver a la Lista
                </x-base.button>
            </div>
        </div>
        
        <div class="intro-y box p-5 mt-5">
            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                <div class="font-medium text-base truncate">Información del Mantenimiento</div>
                <div class="ml-auto flex items-center">
                    <div class="dropdown">
                        <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="help-circle" class="w-4 h-4 mr-2"></i> Ayuda
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Guía
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="dropdown-item">
                                        <i data-lucide="info" class="w-4 h-4 mr-2"></i> Acerca de
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('admin.maintenance.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mt-3">
                    <label for="vehicle_id" class="form-label">Vehículo</label>
                    <select id="vehicle_id" name="vehicle_id" class="form-select w-full @error('vehicle_id') border-danger @enderror">
                        <option value="">Seleccionar vehículo</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">
                                {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->company_unit_number ?? $vehicle->vin }})
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="mt-3">
                    <label for="service_tasks" class="form-label">Tipo de Mantenimiento</label>
                    <select id="service_tasks" name="service_tasks" class="form-select w-full @error('service_tasks') border-danger @enderror">
                        <option value="">Seleccionar tipo de mantenimiento</option>
                        @foreach($maintenanceTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('service_tasks') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="service_date" class="form-label">Fecha de Mantenimiento</label>
                        <input id="service_date" type="datetime-local" name="service_date" value="{{ old('service_date', now()->format('Y-m-d\\TH:i')) }}" class="form-control w-full @error('service_date') border-danger @enderror">
                        @error('service_date') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                    </div>
                    
                    <div>
                        <label for="next_service_date" class="form-label">Fecha Próximo Mantenimiento</label>
                        <input id="next_service_date" type="datetime-local" name="next_service_date" value="{{ old('next_service_date', now()->addMonths(3)->format('Y-m-d\\TH:i')) }}" class="form-control w-full @error('next_service_date') border-danger @enderror">
                        @error('next_service_date') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="unit" class="form-label">Unidad</label>
                        <input id="unit" type="text" name="unit" value="{{ old('unit') }}" class="form-control w-full @error('unit') border-danger @enderror" placeholder="Número de unidad o identificador">
                        @error('unit') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                    </div>
                    
                    <div>
                        <label for="vendor_mechanic" class="form-label">Proveedor/Mecánico</label>
                        <input id="vendor_mechanic" type="text" name="vendor_mechanic" value="{{ old('vendor_mechanic') }}" class="form-control w-full @error('vendor_mechanic') border-danger @enderror" placeholder="Ej: Taller Automotriz XYZ">
                        @error('vendor_mechanic') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="cost" class="form-label">Costo</label>
                        <div class="input-group">
                            <div class="input-group-text">$</div>
                            <input id="cost" type="number" step="0.01" min="0" name="cost" value="{{ old('cost', 0) }}" class="form-control @error('cost') border-danger @enderror" placeholder="0.00">
                        </div>
                        @error('cost') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                    </div>
                    
                    <div>
                        <label for="odometer" class="form-label">Lectura de Odómetro</label>
                        <input id="odometer" type="number" min="0" name="odometer" value="{{ old('odometer') }}" class="form-control w-full @error('odometer') border-danger @enderror" placeholder="Ej: 50000">
                        @error('odometer') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea id="description" name="description" class="form-control w-full @error('description') border-danger @enderror" rows="4" placeholder="Detalles adicionales del mantenimiento">{{ old('description') }}</textarea>
                    @error('description') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="mt-3">
                    <div class="form-check">
                        <input id="status" type="checkbox" name="status" value="1" {{ old('status') ? 'checked' : '' }} class="form-check-input">
                        <label for="status" class="form-check-label">Marcar como Completado</label>
                    </div>
                    @error('status') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                </div>

                <div class="text-right mt-5">
                    <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary w-24 mr-1">Cancellar</a>
                    <button type="submit" class="btn btn-primary w-24">Guardar</button>
                </div>
            </form>
            
            <!-- Sección de documentos adjuntos -->
            <div class="mt-8 pt-5 border-t border-slate-200/60 dark:border-darkmode-400">
                <h3 class="text-lg font-medium mb-5">Documentos Adjuntos</h3>
                <div class="intro-y grid grid-cols-12 gap-3 sm:gap-6 mt-3">
                    <div class="intro-y col-span-6 sm:col-span-4 md:col-span-3 2xl:col-span-2">
                        <div class="file box rounded-md pt-8 pb-5 px-3 relative zoom-in">
                            <div class="w-3/5 file__icon file__icon--file mx-auto">
                                <div class="file__icon__file-name">+</div>
                            </div>
                            <a href="javascript:;" class="block font-medium mt-4 text-center truncate">Agregar Factura</a>
                            <div class="text-slate-500 text-xs text-center mt-0.5">Subir archivo PDF</div>
                        </div>
                    </div>
                    <div class="intro-y col-span-6 sm:col-span-4 md:col-span-3 2xl:col-span-2">
                        <div class="file box rounded-md pt-8 pb-5 px-3 relative zoom-in">
                            <div class="w-3/5 file__icon file__icon--image mx-auto">
                                <div class="file__icon__file-name">+</div>
                            </div>
                            <a href="javascript:;" class="block font-medium mt-4 text-center truncate">Agregar Foto</a>
                            <div class="text-slate-500 text-xs text-center mt-0.5">Subir imagen</div>
                        </div>
                    </div>
                    <div class="intro-y col-span-6 sm:col-span-4 md:col-span-3 2xl:col-span-2">
                        <div class="file box rounded-md pt-8 pb-5 px-3 relative zoom-in">
                            <div class="w-3/5 file__icon file__icon--empty-directory mx-auto">
                                <div class="file__icon__file-name">+</div>
                            </div>
                            <a href="javascript:;" class="block font-medium mt-4 text-center truncate">Otro Documento</a>
                            <div class="text-slate-500 text-xs text-center mt-0.5">Subir cualquier archivo</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener referencias a los elementos del DOM
        const vehicleSelect = document.getElementById('vehicle_id');
        const unitInput = document.getElementById('unit');
        
        // Datos de vehículos para autocompletar el campo de unidad
        const vehiclesData = @json($vehicles->map(function($vehicle) {
            return [
                'id' => $vehicle->id,
                'unit' => $vehicle->company_unit_number ?? ''
            ];
        }));
        
        // Función para actualizar el campo de unidad cuando se selecciona un vehículo
        vehicleSelect.addEventListener('change', function() {
            const selectedVehicleId = parseInt(this.value);
            if (!selectedVehicleId) return;
            
            // Si el campo de unidad está vacío o no ha sido modificado manualmente
            if (!unitInput.value || unitInput.dataset.autoFilled === 'true') {
                const selectedVehicle = vehiclesData.find(v => v.id === selectedVehicleId);
                if (selectedVehicle && selectedVehicle.unit) {
                    unitInput.value = selectedVehicle.unit;
                    unitInput.dataset.autoFilled = 'true';
                }
            }
        });
        
        // Marcar cuando el usuario modifica manualmente el campo de unidad
        unitInput.addEventListener('input', function() {
            this.dataset.autoFilled = 'false';
        });
    });
</script>
@endpush
@endsection