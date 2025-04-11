@extends('../themes/' . $activeTheme)
@section('title', 'Editar Mantenimiento')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehículos', 'url' => route('admin.vehicles.index')],
        ['label' => $vehicle->make . ' ' . $vehicle->model, 'url' => route('admin.vehicles.show', $vehicle->id)],
        ['label' => 'Mantenimientos', 'url' => route('admin.vehicles.service-items.index', $vehicle->id)],
        ['label' => 'Editar Mantenimiento', 'active' => true],
    ];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium">
                Editar Registro de Mantenimiento: {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.vehicles.service-items.index', $vehicle->id) }}"
                    class="w-full sm:w-auto" variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4" icon="ArrowLeft" />
                    Volver a Mantenimientos
                </x-base.button>
            </div>
        </div>

        <div class="box box--stacked mt-5">
            <div class="box-header">
                <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">
                    Datos del Servicio
                </div>
            </div>
            <div class="box-body p-5">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="ml-4 list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.vehicles.service-items.update', [$vehicle->id, $serviceItem->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <x-base.form-label for="unit">Unidad/Sistema <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="unit" name="unit" value="{{ old('unit', $serviceItem->unit) }}" required />
                            <small class="text-slate-500">Identifica el sistema o parte del vehículo atendida</small>
                        </div>
                        <div>
                            <x-base.form-label for="service_tasks">Tareas realizadas <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="service_tasks" name="service_tasks" 
                                value="{{ old('service_tasks', $serviceItem->service_tasks) }}" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <div>
                            <x-base.form-label for="service_date">Fecha del servicio <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input type="date" id="service_date" name="service_date" 
                                value="{{ old('service_date', $serviceItem->service_date->format('Y-m-d')) }}" required />
                        </div>
                        <div>
                            <x-base.form-label for="next_service_date">Próximo servicio <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input type="date" id="next_service_date" name="next_service_date" 
                                value="{{ old('next_service_date', $serviceItem->next_service_date->format('Y-m-d')) }}" required />
                        </div>
                        <div>
                            <x-base.form-label for="odometer">Odómetro (millas)</x-base.form-label>
                            <x-base.form-input type="number" id="odometer" name="odometer" 
                                value="{{ old('odometer', $serviceItem->odometer) }}" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <x-base.form-label for="vendor_mechanic">Proveedor/Mecánico <span class="text-danger">*</span></x-base.form-label>
                            <x-base.form-input id="vendor_mechanic" name="vendor_mechanic" 
                                value="{{ old('vendor_mechanic', $serviceItem->vendor_mechanic) }}" required />
                        </div>
                        <div>
                            <x-base.form-label for="cost">Costo ($) <span class="text-danger">*</span></x-base.form-label>
                            <div class="input-group">
                                <div class="input-group-text">$</div>
                                <x-base.form-input type="number" step="0.01" id="cost" name="cost" 
                                    value="{{ old('cost', $serviceItem->cost) }}" min="0" required />
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <x-base.form-label for="description">Descripción/Notas</x-base.form-label>
                        <x-base.form-textarea id="description" name="description" rows="4">{{ old('description', $serviceItem->description) }}</x-base.form-textarea>
                    </div>

                    <div class="flex items-center mt-5 pt-5 border-t border-slate-200/60">
                        <div class="form-check mr-4">
                            <input type="checkbox" id="status" name="status" value="1" class="form-check-input" 
                                {{ old('status', $serviceItem->status) ? 'checked' : '' }}>
                            <label for="status" class="form-check-label">Marcar como completado</label>
                        </div>
                        <div class="ml-auto">
                            <x-base.button as="a" href="{{ route('admin.vehicles.service-items.index', $vehicle->id) }}" 
                                variant="outline-secondary" class="mr-1 w-24">
                                Cancelar
                            </x-base.button>
                            <x-base.button type="submit" variant="primary" class="w-24">
                                Actualizar
                            </x-base.button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Validación para asegurar que la fecha del próximo servicio sea posterior a la fecha de servicio
    document.getElementById('service_date').addEventListener('change', validateDates);
    document.getElementById('next_service_date').addEventListener('change', validateDates);
    
    function validateDates() {
        const serviceDate = new Date(document.getElementById('service_date').value);
        const nextServiceDate = new Date(document.getElementById('next_service_date').value);
        
        if (serviceDate && nextServiceDate && nextServiceDate <= serviceDate) {
            alert('La fecha del próximo servicio debe ser posterior a la fecha de servicio.');
            document.getElementById('next_service_date').value = '';
        }
    }
    
    // Validar al cargar la página
    document.addEventListener('DOMContentLoaded', validateDates);
</script>
@endpush