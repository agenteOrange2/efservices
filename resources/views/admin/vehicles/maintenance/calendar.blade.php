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
    <div id="maintenance-modal-wrapper" x-data="modalData">
        <x-base.dialog id="maintenance-modal">
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
                        <a x-bind:href="viewLink" class="btn btn-primary mr-1">View complete</a>
                        <x-base.button
                            data-tw-dismiss="modal"
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
                    // Parsear los eventos directamente sin modificarlos
                    maintenanceEvents = JSON.parse(maintenanceEventsElement.dataset.events);
                    console.log('Eventos cargados desde el backend:', maintenanceEvents);
                    
                    // Formatear los eventos para el calendario si es necesario
                    if (maintenanceEvents.length > 0 && !maintenanceEvents[0].hasOwnProperty('extendedProps')) {
                        maintenanceEvents = maintenanceEvents.map(event => {
                            return {
                                id: `service-${event.id}`,
                                title: event.title || `${event.service_type} - ${event.vehicle_name || ''}`,
                                start: event.start || event.date,
                                backgroundColor: event.color || (event.completed ? '#1E40AF' : '#991B1B'),
                                borderColor: event.color || (event.completed ? '#1E40AF' : '#991B1B'),
                                // Almacenar todos los datos originales como extendedProps
                                extendedProps: {
                                    ...event,  // Mantener todos los datos originales
                                    // Asegurar que tengamos las propiedades más importantes
                                    id: event.id,
                                    vehicle: event.vehicle_name || '',
                                    vehicle_name: event.vehicle_name || '',
                                    serviceType: event.service_type || '',
                                    service_type: event.service_type || '',
                                    serviceDate: event.date || event.start,
                                    date: event.date || event.start
                                }
                            };
                        });
                        console.log('Eventos formateados para el calendario:', maintenanceEvents);
                    }
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
                        console.log('CLICK EN FECHA:', info);
                        
                        // Formatear la fecha correctamente para la URL y para la visualización
                        const formattedDate = info.date.toLocaleDateString();
                        // Formato ISO para la URL (YYYY-MM-DD)
                        const dateForUrl = info.dateStr; // ya viene en formato YYYY-MM-DD
                        
                        // Establecer atributos directamente en elementos del DOM sin depender de Alpine
                        document.querySelector('#maintenance-modal-wrapper h2').textContent = 'New Maintenance';
                        document.querySelector('#maintenance-modal-wrapper [x-text="vehicle"]').textContent = 'Select a vehicle';
                        document.querySelector('#maintenance-modal-wrapper [x-text="serviceType"]').textContent = 'New service';
                        document.querySelector('#maintenance-modal-wrapper [x-text="serviceDate"]').textContent = formattedDate;
                        document.querySelector('#maintenance-modal-wrapper [x-html="status"]').innerHTML = 
                            '<span class="px-2 py-1 rounded-full bg-primary text-white">New</span>';
                        
                        // Ocultar elementos cost y description
                        document.querySelectorAll('#maintenance-modal-wrapper [x-show="showCost"]').forEach(el => {
                            el.style.display = 'none';
                        });
                        document.querySelectorAll('#maintenance-modal-wrapper [x-show="showDescription"]').forEach(el => {
                            el.style.display = 'none';
                        });
                        
                        // Establecer el enlace del botón "View complete"
                        const viewCompleteUrl = `{{ route('admin.maintenance.create') }}?date=${dateForUrl}`;
                        const viewCompleteButton = document.querySelector('#maintenance-modal-wrapper a.btn-primary');
                        if (viewCompleteButton) {
                            viewCompleteButton.href = viewCompleteUrl;
                        }
                        
                        console.log('Datos actualizados directamente en el DOM');
                        
                        // Abrir el modal usando Tailwind API sin depender de Alpine
                        const modal = document.getElementById('maintenance-modal');
                        if (modal && window.tailwind) {
                            console.log('Abriendo modal con Tailwind API');
                            window.tailwind.Modal.getOrCreateInstance(modal).show();
                        } else {
                            console.error('No se pudo abrir el modal con Tailwind API');
                        }
                    },
                    selectable: true, // Permitir seleccionar fechas
                    // Deshabilitar el manejo de rango de selección para evitar confusión
                    select: function(info) {
                        // No hacemos nada aquí ya que dateClick maneja el clic en fechas individuales
                        info.view.calendar.unselect(); // Deshace la selección visual usando el objeto calendar del view
                    },
                    eventClick: function(info) {
                        // Recopilamos todos los datos necesarios
                        const event = info.event;
                        const props = event.extendedProps || {};
                        
                        console.log('CLICK EN EVENTO - Evento completo:', event);
                        console.log('CLICK EN EVENTO - Props extendidos:', props);
                        
                        // Preparar los valores con fallbacks para garantizar que siempre tengamos datos
                        const title = event.title || props.title || 'Maintenance Details';
                        const vehicle = props.vehicle_name || props.vehicle || '';
                        const serviceType = props.service_type || props.serviceType || (title ? title.split(' - ')[0] : '');
                        
                        // Para la fecha, intentamos varios formatos posibles
                        let serviceDate = '';
                        if (props.date) {
                            serviceDate = props.date;
                        } else if (props.serviceDate) {
                            serviceDate = props.serviceDate;
                        } else if (event.start) {
                            serviceDate = event.start.toLocaleDateString();
                        }
                        
                        // Obtener el costo y descripción con fallbacks
                        const cost = props.cost || '';
                        const description = props.description || '';
                        
                        // Calcular el estado HTML basado en varias posibles propiedades
                        let statusHtml = '';
                        const status = props.status || props.completed;
                        
                        if (status === 1 || status === true || props.completed === 1 || props.completed === true) {
                            statusHtml = '<span class="px-2 py-1 rounded-full bg-success text-white">Completed</span>';
                        } else if (status === 2 || props.upcoming === true) {
                            statusHtml = '<span class="px-2 py-1 rounded-full bg-warning text-white">Upcoming</span>';
                        } else {
                            statusHtml = '<span class="px-2 py-1 rounded-full bg-danger text-white">Pending</span>';
                        }
                        
                        // Preparar el enlace de vista - IMPORTANTE: usar el ID correcto
                        let viewLink = '#';
                        
                        // Obtener el ID real del mantenimiento
                        let maintenanceId = props.id;
                        // Si el ID está en el formato de prefijo 'service-', extraerlo
                        if (typeof maintenanceId === 'string' && maintenanceId.startsWith('service-')) {
                            maintenanceId = maintenanceId.replace('service-', '');
                        } else if (event.id && event.id.startsWith('service-')) {
                            maintenanceId = event.id.replace('service-', '');
                        }
                        
                        // Crear la URL correcta para ver/editar
                        if (maintenanceId) {
                            viewLink = `{{ url('admin/maintenance') }}/${maintenanceId}/edit`;
                            console.log('URL para ver mantenimiento:', viewLink);
                        } else {
                            console.error('No se pudo determinar el ID del mantenimiento');
                        }
                        
                        // Establecer atributos directamente en elementos del DOM sin depender de Alpine
                        document.querySelector('#maintenance-modal-wrapper h2').textContent = title;
                        document.querySelector('#maintenance-modal-wrapper [x-text="vehicle"]').textContent = vehicle;
                        document.querySelector('#maintenance-modal-wrapper [x-text="serviceType"]').textContent = serviceType;
                        document.querySelector('#maintenance-modal-wrapper [x-text="serviceDate"]').textContent = serviceDate;
                        document.querySelector('#maintenance-modal-wrapper [x-html="status"]').innerHTML = statusHtml;
                        
                        // Manejar costo y descripción
                        const costEl = document.querySelector('#maintenance-modal-wrapper [x-text="cost"]');
                        const showCostEls = document.querySelectorAll('#maintenance-modal-wrapper [x-show="showCost"]');
                        const descriptionEl = document.querySelector('#maintenance-modal-wrapper [x-text="description"]');
                        const showDescriptionEls = document.querySelectorAll('#maintenance-modal-wrapper [x-show="showDescription"]');
                        
                        if (cost && cost.trim() !== '') {
                            costEl.textContent = cost;
                            showCostEls.forEach(el => { el.style.display = 'block'; });
                        } else {
                            showCostEls.forEach(el => { el.style.display = 'none'; });
                        }
                        
                        if (description && description.trim() !== '') {
                            descriptionEl.textContent = description;
                            showDescriptionEls.forEach(el => { el.style.display = 'block'; });
                        } else {
                            showDescriptionEls.forEach(el => { el.style.display = 'none'; });
                        }
                        
                        // Establecer el enlace del botón "View complete"
                        const viewCompleteButton = document.querySelector('#maintenance-modal-wrapper a.btn-primary');
                        if (viewCompleteButton) {
                            viewCompleteButton.href = viewLink;
                        }
                        
                        console.log('Datos de evento actualizados directamente en el DOM');
                        
                        // Abrir el modal usando Tailwind API sin depender de Alpine
                        const modal = document.getElementById('maintenance-modal');
                        if (modal && window.tailwind) {
                            console.log('Abriendo modal de evento con Tailwind API');
                            window.tailwind.Modal.getOrCreateInstance(modal).show();
                        } else {
                            console.error('No se pudo abrir el modal con Tailwind API');
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

<script>
    // Definir variables iniciales para Alpine.js
    document.addEventListener('alpine:init', () => {
        Alpine.data('modalData', () => ({
            title: 'Maintenance Details',
            vehicle: 'No vehicle selected',
            serviceType: 'No service selected',
            serviceDate: '-',
            status: '<span class="px-2 py-1 rounded-full bg-primary text-white">Default</span>',
            cost: '',
            description: '',
            viewLink: '#',
            showCost: false,
            showDescription: false
        }));
    });
    
    // Función global para abrir el modal usando la API de Tailwind
    window.openMaintenanceModal = function() {
        // Utilizamos setTimeout para evitar reflow forzado
        setTimeout(() => {
            const modal = document.getElementById('maintenance-modal');
            if (modal && window.tailwind) {
                window.tailwind.Modal.getOrCreateInstance(modal).show();
            }
        }, 10);
    };

</script>
@endpush