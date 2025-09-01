@extends('../themes/' . $activeTheme)
@section('title', 'Maintenance Details')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Maintenance', 'url' => route('admin.maintenance.index')],
        ['label' => 'Details #' . $maintenance->id, 'active' => true],
    ];

    // Definir clases de estado para los indicadores visuales
    $statusClass = $maintenance->status ? 'success' : ($maintenance->isOverdue() ? 'danger' : ($maintenance->isUpcoming() ? 'warning' : 'primary'));
    $statusText = $maintenance->status ? 'Completed' : ($maintenance->isOverdue() ? 'Overdue' : ($maintenance->isUpcoming() ? 'Upcoming' : 'Scheduled'));
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-y-10">
        <div class="col-span-12">
            <!-- Encabezado con botones de acción -->
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="text-xl font-medium">
                        <span class="text-slate-600">Maintenance #{{ $maintenance->id }}</span>
                        <span class="badge badge-{{ $statusClass }} ml-2">{{ $statusText }}</span>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2 mt-4 md:mt-0">
                    @if (!$maintenance->status)
                    <x-base.button type="button" id="open-reschedule-modal" variant="outline-primary" 
                            class="btn btn-warning w-full sm:w-auto">
                            <x-base.lucide class="w-4 h-4 mr-2" icon="Calendar" />
                            Reschedule
                        </x-base.button>                        
                    @endif
                    <x-base.button as="a" href="{{ route('admin.maintenance.edit', $maintenance->id) }}" 
                        variant="primary" class="btn btn-primary w-full sm:w-auto">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="Edit" />
                        Editar Maintenance
                    </x-base.button>
                    <x-base.button as="a" href="{{ route('admin.maintenance.index') }}" 
                        variant="outline-secondary" class="btn btn-outline-secondary w-full sm:w-auto">
                        <x-base.lucide class="w-4 h-4 mr-2" icon="ArrowLeft" />
                        Back
                    </x-base.button>
                </div>
            </div>
            
            <!-- Contenedor principal -->
            <div class="box box--stacked mt-5">
                <div class="box-header">
                    <div class="box-title p-5 border-b border-slate-200/60 bg-slate-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-base font-medium">Maintenance Information</span>
                            </div>
                            <!-- Indicador de estado -->
                            <div class="inline-flex items-center rounded-md bg-{{ $statusClass }}/10 px-2 py-1 text-xs font-medium text-{{ $statusClass }}">
                                <x-base.lucide class="w-4 h-4 mr-1" icon="{{ $maintenance->status ? 'CheckCircle' : ($maintenance->isOverdue() ? 'AlertOctagon' : 'Clock') }}" />
                                {{ $statusText }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 xl:grid-cols-1 gap-6">
                        <!-- Información del Vehículo -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-slate-900 border-b pb-2">Vehicle Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-slate-50/50 p-3 rounded-lg">
                                    <div class="text-sm text-slate-500">Vehicle</div>
                                    <div class="font-medium">{{ $vehicle->make }} {{ $vehicle->model }}</div>
                                    <div class="text-sm">{{ $vehicle->year }}</div>
                                </div>                                                                
                                
                                <div class="bg-slate-50/50 p-3 rounded-lg col-span-full">
                                    <div class="text-sm text-slate-500">VIN</div>
                                    <div class="font-medium">{{ $vehicle->vin }}</div>
                                </div>
                                
                                <div class="bg-slate-50/50 p-3 rounded-lg">
                                    <div class="text-sm text-slate-500">Odometer Reading</div>
                                    <div class="font-medium">{{ number_format($maintenance->odometer_reading) }} km</div>
                                </div>
                                
                                <div class="bg-slate-50/50 p-3 rounded-lg">
                                    <div class="text-sm text-slate-500">Cost</div>
                                    <div class="font-medium">${{ number_format($maintenance->cost, 2) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Servicio -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-slate-900 border-b pb-2">Service Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-slate-50/50 p-3 rounded-lg">
                                    <div class="text-sm text-slate-500">Service Type</div>
                                    <div class="font-medium">{{ $maintenance->service_type }}</div>
                                </div>
                                
                                <div class="bg-slate-50/50 p-3 rounded-lg">
                                    <div class="text-sm text-slate-500">Vendor/Mechanic</div>
                                    <div class="font-medium">{{ $maintenance->vendor_mechanic }}</div>
                                </div>
                                
                                <div class="bg-slate-50/50 p-3 rounded-lg">
                                    <div class="text-sm text-slate-500">Service Date</div>
                                    <div class="font-medium">{{ $maintenance->service_date ? $maintenance->service_date->format('d/m/Y') : 'Not established' }}</div>
                                </div>
                                
                                <div class="bg-slate-50/50 p-3 rounded-lg {{ $maintenance->isOverdue() ? 'bg-danger/10' : ($maintenance->isUpcoming() ? 'bg-warning/10' : '') }}">
                                    <div class="text-sm text-slate-500">Next Service</div>
                                    <div class="font-medium">{{ $maintenance->next_service_date ? $maintenance->next_service_date->format('d/m/Y') : 'No establecida' }}</div>
                                    @if(!$maintenance->status)
                                        @if($maintenance->isOverdue())
                                            <div class="text-xs text-danger mt-1">Overdue by {{ $maintenance->next_service_date->diffInDays(now()) }} days</div>
                                        @elseif($maintenance->isUpcoming())
                                            <div class="text-xs text-warning mt-1">In {{ $maintenance->next_service_date->diffInDays(now()) }} days</div>
                                        @else
                                            <div class="text-xs text-success mt-1">Up to date</div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Descripción y notas -->
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-slate-900 border-b pb-2 mb-4">Descripción y Notas</h3>
                        <div class="bg-slate-50/60 p-4 rounded-lg">
                            @if(!empty($maintenance->description))
                                {!! nl2br(e($maintenance->description)) !!}
                            @else
                                <p class="text-slate-500 italic">No hay notas o descripción disponibles para este mantenimiento.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Documentos adjuntos -->
                    @if($maintenance->getMedia('maintenance_documents')->count() > 0 || $maintenance->getMedia('maintenance_files')->count() > 0)
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-slate-900 border-b pb-2 mb-4">Documentos Adjuntos</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($maintenance->getMedia('maintenance_documents')->merge($maintenance->getMedia('maintenance_files')) as $media)
                                    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
                                        <div class="p-4">
                                            <div class="flex items-center mb-3">
                                                @if(str_contains($media->mime_type, 'image'))
                                                    <div class="w-10 h-10 flex-shrink-0 mr-3 bg-primary/10 rounded-lg flex items-center justify-center">
                                                        <x-base.lucide class="w-5 h-5 text-primary" icon="Image" />
                                                    </div>
                                                @elseif(str_contains($media->mime_type, 'pdf'))
                                                    <div class="w-10 h-10 flex-shrink-0 mr-3 bg-danger/10 rounded-lg flex items-center justify-center">
                                                        <x-base.lucide class="w-5 h-5 text-danger" icon="FileText" />
                                                    </div>
                                                @else
                                                    <div class="w-10 h-10 flex-shrink-0 mr-3 bg-warning/10 rounded-lg flex items-center justify-center">
                                                        <x-base.lucide class="w-5 h-5 text-warning" icon="File" />
                                                    </div>
                                                @endif
                                                <div class="flex-grow overflow-hidden">
                                                    <p class="font-medium text-sm truncate">{{ $media->file_name }}</p>
                                                    <p class="text-xs text-slate-500">{{ $media->human_readable_size }}</p>
                                                </div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <a href="{{ $media->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary flex-1 flex items-center justify-center">
                                                    <x-base.lucide class="w-4 h-4 mr-1" icon="Eye" /> Ver
                                                </a>
                                                {{-- <a href="{{ route('admin.maintenance.documents.download', $media->id) }}" class="btn btn-sm btn-outline-primary flex-1 flex items-center justify-center">
                                                    <x-base.lucide class="w-4 h-4 mr-1" icon="Download" /> Descargar
                                                </a> --}}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Reprogramación Mejorado -->
    <x-base.dialog id="reschedule-modal" size="md">
        <x-base.dialog.panel>
            <form id="reschedule-form" action="{{ route('admin.maintenance-system.reschedule', $maintenance->id) }}" method="POST">
                @csrf
                <div class="p-5">
                    <div class="text-center">
                        <x-base.lucide class="mx-auto h-16 w-16 text-warning" icon="Calendar" />
                        <div class="mt-2 text-xl font-medium">Reschedule Maintenance # {{ $maintenance->id }}</div>
                        <div class="mt-1 text-slate-500">
                            Select a new date for the maintenance and indicate the reason for the change.
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="next_service_date" class="form-label">New service date</label>
                            <input type="date" id="next_service_date" name="next_service_date" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm"
                                min="{{ now()->addDay()->format('m/d/Y') }}" required>
                        </div>
                        <div class="col-span-12">
                            <label for="reschedule_reason" class="form-label">Reason for rescheduling</label>
                            <textarea id="reschedule_reason" name="reschedule_reason" class="py-2 px-3 block w-full border-gray-200 rounded-md text-sm" rows="4"
                                placeholder="Explain why this maintenance is being rescheduled..." required></textarea>
                        </div>
                    </div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <x-base.button class="mr-1 w-24" data-tw-dismiss="modal" type="button" variant="outline-secondary">
                        Cancel
                    </x-base.button>
                    <x-base.button class="w-24" type="submit" variant="primary">
                        Reschedule
                    </x-base.button>
                </div>
            </form>
        </x-base.dialog.panel>
    </x-base.dialog>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Referencias a elementos
        const openModalBtn = document.getElementById('open-reschedule-modal');
        const modal = document.getElementById('reschedule-modal');
        
        // Inicializar componentes que requieran inicialización
        if (typeof tailwind !== 'undefined') {
            tailwind.Modal.getInstance(document.querySelector('#reschedule-modal'));
        }
        
        // Manejar apertura del modal
        if (openModalBtn) {
            openModalBtn.addEventListener('click', function() {
                const modalInstance = tailwind.Modal.getInstance(document.querySelector('#reschedule-modal'));
                if (modalInstance) {
                    modalInstance.show();
                }
            });
        }
        
        // Validación del formulario
        const form = document.getElementById('reschedule-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const dateInput = document.getElementById('next_service_date');
                const reasonInput = document.getElementById('reschedule_reason');
                
                let isValid = true;
                
                // Validar fecha
                if (!dateInput.value) {
                    isValid = false;
                    dateInput.classList.add('border-danger');
                } else {
                    dateInput.classList.remove('border-danger');
                }
                
                // Validar razón
                if (!reasonInput.value || reasonInput.value.trim().length < 10) {
                    isValid = false;
                    reasonInput.classList.add('border-danger');
                } else {
                    reasonInput.classList.remove('border-danger');
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endpush
