@extends('../themes/' . $activeTheme)

@section('title', 'Calendario de Mantenimiento')
@php
$breadcrumbLinks = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
    ['label' => 'Maintenance', 'url' => route('admin.maintenance.index')],
    ['label' => 'Calendar', 'active' => true],
];
@endphp

@section('styles')
<!-- Estilos personalizados para los eventos del calendario de mantenimiento -->
<style>
    /* Estilos para eventos de mantenimiento - Mostrar como bloques completos */
    .maintenance-completed {
        background-color: rgba(16, 185, 129, 0.7) !important; /* Verde semi-transparente */
        border-color: #10b981 !important;
        color: white !important;
    }
    
    .maintenance-pending {
        background-color: rgba(239, 68, 68, 0.7) !important; /* Rojo semi-transparente */
        border-color: #ef4444 !important;
        color: white !important;
    }
    
    .maintenance-upcoming {
        background-color: rgba(245, 158, 11, 0.7) !important; /* Amarillo/naranja semi-transparente */
        border-color: #f59e0b !important;
        color: white !important;
    }
    
    /* Asegurar que todos los eventos se muestren como bloques completos */
    .fc-daygrid-event {
        white-space: normal !important;
        align-items: normal !important;
        display: block !important;
    }
    
    /* Mejorar la visibilidad del texto en los eventos */
    .fc-event-title {
        font-weight: 500;
        padding: 2px 0;
    }
    
    /* Ajustar altura mínima para eventos */
    .fc-daygrid-event-harness {
        min-height: 25px;
    }
</style>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Maintenance Calendar
        </h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('admin.maintenance.create') }}" class="btn btn-primary shadow-md mr-2">
                <i class="w-4 h-4 mr-2" data-lucide="plus"></i> New Maintenance
            </a>
            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-secondary shadow-md">
                <i class="w-4 h-4 mr-2" data-lucide="list"></i> List
            </a>
        </div>
    </div>
    
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-5">
            <!-- Filtros -->
            <div class="col-span-12 lg:col-span-3">
                <div class="box p-5">
                    <h2 class="font-medium text-base mb-5">Filters</h2>
                    <form id="filter-form" action="{{ route('admin.maintenance.calendar') }}" method="GET">
                        <div class="mb-4">
                            <label class="form-label">Vehicle</label>
                            <select name="vehicle_id" class="form-select w-full">
                                <option value="">All vehicles</option>
                                @php
                                   $availableVehicles = isset($vehicles) ? $vehicles : collect();
                                @endphp
                                @foreach($availableVehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ (isset($vehicleId) && $vehicleId == $vehicle->id) ? 'selected' : '' }}>
                                        {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->company_unit_number ?? $vehicle->vin }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select w-full">
                                @php $selectedStatus = $status ?? ''; @endphp
                            <option value="">All</option>
                                <option value="1" {{ $selectedStatus == '1' ? 'selected' : '' }}>Completed</option>
                                <option value="0" {{ $selectedStatus == '0' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-full">Apply filters</button>
                    </form>
                </div>
                
                <div class="box p-5 mt-5">
                    <h2 class="font-medium text-base mb-5">Next Maintenance</h2>
                    <div class="space-y-4">
                        @php
                            $upcomingMaintenances = $upcomingMaintenances ?? collect();
                        @endphp
                        @forelse($upcomingMaintenances as $maintenance)
                            <div class="border rounded-md p-3 bg-amber-50">
                                <div class="font-medium">{{ $maintenance->service_tasks }}</div>
                                <div class="text-slate-500 text-xs mt-1">
                                    <span class="font-medium">Vehicle:</span> {{ $maintenance->vehicle->make }} {{ $maintenance->vehicle->model }}
                                </div>
                                <div class="text-slate-500 text-xs mt-1">
                                    <span class="font-medium">Date:</span> {{ Carbon\Carbon::parse($maintenance->next_service_date)->format('d/m/Y') }}
                                </div>
                                <div class="text-slate-500 text-xs mt-1">
                                    <span class="font-medium">Cost:</span> ${{ number_format($maintenance->cost, 2) }}
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('admin.maintenance.edit', $maintenance->id) }}" class="btn btn-sm btn-secondary w-full">View details</a>
                                </div>
                            </div>
                        @empty
                            <div class="text-slate-500 text-center py-4">
                                No upcoming maintenance scheduled
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Calendario -->
            <div class="col-span-12 lg:col-span-9">
                <div class="box box--stacked flex flex-col p-5">
                    <!-- Datos de eventos almacenados para que el calendario los lea -->
                    <div id="maintenance-events-data" style="display: none;" data-events="{{ json_encode($events ?? []) }}"></div>
                    <x-calendar id="calendar" />
                </div>
            </div>
        </div>
    </div>    
    <!-- Modal de detalles de mantenimiento usando Alpine.js -->
    <div id="maintenance-modal-wrapper" x-data="{ show: false, title: '', vehicle: '', serviceType: '', serviceDate: '', status: '', cost: '', description: '', viewLink: '', showCost: false, showDescription: false }">
        <x-base.dialog id="maintenance-modal" :show="show" @open-modal.window="show = true" @close-modal.window="show = false">
            <x-base.dialog.panel>
                <div class="p-5 text-center">
                    <h2 class="font-medium text-base mr-auto text-left" x-text="title">Maintenance Details</h2>
                    <div class="mt-4 text-left">
                        <div class="mb-4">
                            <div class="font-medium">Vehicle</div>
                            <div class="text-slate-600 mt-1" x-text="vehicle"></div>
                        </div>
                        <div class="mb-4">
                            <div class="font-medium">Service type</div>
                            <div class="text-slate-600 mt-1" x-text="serviceType"></div>
                        </div>
                        <div class="mb-4">
                            <div class="font-medium">Service date</div>
                            <div class="text-slate-600 mt-1" x-text="serviceDate"></div>
                        </div>
                        <div class="mb-4">
                            <div class="font-medium">Status</div>
                            <div class="mt-1" x-html="status"></div>
                        </div>
                        <div class="mb-4" x-show="showCost">
                            <div class="font-medium">Cost</div>
                            <div class="text-slate-600 mt-1" x-text="cost"></div>
                        </div>
                        <div class="mb-4" x-show="showDescription">
                            <div class="font-medium">Description</div>
                            <div class="text-slate-600 mt-1" x-text="description"></div>
                        </div>
                    </div>
                    <div class="mt-5 text-right">
                        <a :href="viewLink" class="btn btn-primary mr-1">View complete</a>
                        <x-base.button
                            @click="show = false"
                            variant="outline-secondary"
                            type="button">
                            Close
                        </x-base.button>
                    </div>
                </div>
            </x-base.dialog.panel>
        </x-base.dialog>
    </div>
@endsection


@push('scripts')
<script>
    // Reemplazar directamente la inicialización del calendario para usar eventos de mantenimiento
    document.addEventListener('DOMContentLoaded', function() {
        // Sobrescribir la función que inicializa el calendario
        const originalInit = window.initCalendar;
        
        window.initCalendar = function() {
            // Obtener los eventos de mantenimiento
            let maintenanceEvents = [];
            const maintenanceEventsElement = document.getElementById('maintenance-events-data');
            
            if (maintenanceEventsElement && maintenanceEventsElement.dataset.events) {
                try {
                    maintenanceEvents = JSON.parse(maintenanceEventsElement.dataset.events);
                    console.log('Eventos cargados desde el backend:', maintenanceEvents);
                } catch (e) {
                    console.error('Error al parsear eventos de mantenimiento:', e);
                }
            }
            
            // Si no hay eventos, usar los predeterminados
            if (!maintenanceEvents || !maintenanceEvents.length) {
                console.warn('No hay eventos de mantenimiento para mostrar');
            }
            
            // Buscar todas las instancias de calendario
            $(".full-calendar").each(function() {
                // Obtener el elemento del DOM para el calendario
                const el = $(this).children()[0];
                
                // Configuración básica del calendario
                const calendarOptions = {
                    plugins: [
                        interactionPlugin,
                        dayGridPlugin,
                        timeGridPlugin,
                        listPlugin,
                    ],
                    droppable: true,
                    headerToolbar: {
                        left: "prev,next today",
                        center: "title",
                        right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
                    },
                    initialDate: new Date(), // Usar la fecha actual
                    navLinks: true,
                    editable: false, // No editable para mantenimientos
                    dayMaxEvents: true,
                    events: maintenanceEvents, // USAR NUESTROS EVENTOS DE MANTENIMIENTO
                    
                    // Configuraciones adicionales para mejorar la visualización de eventos
                    displayEventTime: false, // No mostrar la hora en eventos
                    eventDisplay: 'block', // Mostrar eventos como bloques
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        meridiem: 'short'
                    },
                    // Manejar clic en una fecha vacía
                    dateClick: function(info) {
                        // Obtener el wrapper de Alpine.js
                        const modalWrapper = document.getElementById('maintenance-modal-wrapper');
                        const modalData = Alpine.data(modalWrapper);
                        
                        if (modalData) {
                            // Formatear la fecha
                            const formattedDate = info.date.toLocaleDateString();
                            
                            // Actualizar estado de Alpine
                            modalData.title = 'New Maintenance';
                            modalData.vehicle = 'Select a vehicle';
                            modalData.serviceType = 'New service';
                            modalData.serviceDate = formattedDate;
                            modalData.status = '<span class="px-2 py-1 rounded-full bg-primary text-white">New</span>';
                            modalData.showCost = false;
                            modalData.showDescription = false;
                            modalData.viewLink = `{{ route('admin.maintenance.create') }}?date=${info.dateStr}`;
                            
                            // Mostrar el modal a través de Alpine
                            modalData.show = true;
                        }
                    },
                    selectable: true, // Permitir seleccionar fechas
                    // Deshabilitar el manejo de rango de selección para evitar confusión
                    select: function(info) {
                        // No hacemos nada aquí ya que dateClick maneja el clic en fechas individuales
                        info.view.calendar.unselect(); // Deshace la selección visual usando el objeto calendar del view
                    },
                    eventClick: function(info) {
                        // Obtener el wrapper de Alpine.js
                        const modalWrapper = document.getElementById('maintenance-modal-wrapper');
                        const modalData = Alpine.data(modalWrapper);
                        
                        if (modalData) {
                            // Recopilar las propiedades extendidas del evento
                            const event = info.event;
                            const props = event.extendedProps || {};
                            
                            // Actualizar estado de Alpine con la información del evento
                            modalData.title = event.title;
                            modalData.vehicle = props.vehicle || '';
                            modalData.serviceType = props.serviceType || event.title.split(' - ')[0];
                            modalData.serviceDate = props.serviceDate || (event.start ? event.start.toLocaleDateString() : '');
                            
                            // Definir el estado según las propiedades del evento
                            let statusHtml = '';
                            if (props.status === 1 || props.status === true) {
                                statusHtml = '<span class="px-2 py-1 rounded-full bg-success text-white">Completed</span>';
                            } else if (props.status === 2) {
                                statusHtml = '<span class="px-2 py-1 rounded-full bg-warning text-white">Upcoming</span>';
                            } else {
                                statusHtml = '<span class="px-2 py-1 rounded-full bg-danger text-white">Pending</span>';
                            }
                            modalData.status = statusHtml;
                            
                            // Actualizar costo y descripción
                            modalData.cost = props.cost || '';
                            modalData.description = props.description || '';
                            modalData.showCost = Boolean(props.cost);
                            modalData.showDescription = Boolean(props.description);
                            
                            // Configurar el enlace
                            if (props.id) {
                                const id = props.id.replace('service-', '');
                                modalData.viewLink = `{{ url('admin/maintenance') }}/${id}/edit`;
                            } else {
                                modalData.viewLink = '';
                            }
                            
                            // Mostrar el modal
                            modalData.show = true;
                        }
                    }
                };
                
                // Crear el calendario con nuestras opciones
                let calendar = new Calendar(el, calendarOptions);
                calendar.render();
                
                // Almacenar el calendario en una variable global para referencia
                window.calendar = calendar;
            });
        };
        
        // Si el calendario ya fue inicializado, reinicializarlo
        if (typeof interactionPlugin !== 'undefined') {
            window.initCalendar();
        }
    });
</script>
@endpush
