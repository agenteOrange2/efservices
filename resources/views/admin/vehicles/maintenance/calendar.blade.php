@extends('../themes/' . $activeTheme)

@section('title', 'Calendario de Mantenimiento')
@php
$breadcrumbLinks = [
    ['label' => 'App', 'url' => route('admin.dashboard')],
    ['label' => 'Mantenimiento', 'active' => true],
];
@endphp



@section('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css">
    <style>
        .fc-event {
            cursor: pointer;
        }
        .fc-event-title {
            white-space: normal;
        }
        .maintenance-completed {
            background-color: rgba(52, 195, 143, 0.8);
            border-color: rgba(52, 195, 143, 1);
        }
        .maintenance-pending {
            background-color: rgba(241, 85, 108, 0.8);
            border-color: rgba(241, 85, 108, 1);
        }
    </style>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Calendario de Mantenimiento
        </h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('admin.maintenance.create') }}" class="btn btn-primary shadow-md mr-2">
                <i class="w-4 h-4 mr-2" data-lucide="plus"></i> Nuevo Mantenimiento
            </a>
            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-secondary shadow-md">
                <i class="w-4 h-4 mr-2" data-lucide="list"></i> Ver Lista
            </a>
        </div>
    </div>
    
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-5">
            <!-- Filtros -->
            <div class="col-span-12 lg:col-span-3">
                <div class="box p-5">
                    <h2 class="font-medium text-base mb-5">Filtros</h2>
                    <div class="mb-4">
                        <label class="form-label">Tipo de servicio</label>
                        <select id="service-type-filter" class="form-select w-full">
                            <option value="all">Todos</option>
                            <option value="oil_change">Cambio de aceite</option>
                            <option value="tire_rotation">Rotación de neumáticos</option>
                            <option value="brake_service">Servicio de frenos</option>
                            <option value="inspection">Inspección</option>
                            <option value="repair">Reparación</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Estado</label>
                        <select id="status-filter" class="form-select w-full">
                            <option value="all">Todos</option>
                            <option value="completed">Completados</option>
                            <option value="pending">Pendientes</option>
                        </select>
                    </div>
                    <button id="apply-filters" class="btn btn-primary w-full">Aplicar filtros</button>
                </div>
                
                <div class="box p-5 mt-5">
                    <h2 class="font-medium text-base mb-5">Próximos mantenimientos</h2>
                    <div class="space-y-4">
                        @forelse($maintenances as $maintenance)
                            <div class="border rounded-md p-3">
                                <div class="font-medium">{{ $maintenance->service_type }}</div>
                                <div class="text-slate-500 text-xs mt-1">
                                    <span class="font-medium">Vehículo:</span> {{ $maintenance->vehicle->make }} {{ $maintenance->vehicle->model }}
                                </div>
                                <div class="text-slate-500 text-xs mt-1">
                                    <span class="font-medium">Fecha:</span> {{ $maintenance->next_service_date ? $maintenance->next_service_date->format('d/m/Y') : 'No establecida' }}
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('admin.maintenance.show', $maintenance->id) }}" class="btn btn-sm btn-outline-primary w-full">Ver detalles</a>
                                </div>
                            </div>
                        @empty
                            <div class="text-slate-500 text-center py-4">
                                No hay mantenimientos programados próximamente
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Calendario -->
            <div class="col-span-12 lg:col-span-9">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
    
    <!-- Modal de detalles de mantenimiento -->
    <div id="maintenance-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto" id="modal-title">Detalles del Mantenimiento</h2>
                </div>
                <div class="modal-body p-5">
                    <div class="mb-4">
                        <div class="font-medium">Vehículo</div>
                        <div id="modal-vehicle" class="text-slate-600 mt-1"></div>
                    </div>
                    <div class="mb-4">
                        <div class="font-medium">Tipo de servicio</div>
                        <div id="modal-service-type" class="text-slate-600 mt-1"></div>
                    </div>
                    <div class="mb-4">
                        <div class="font-medium">Fecha de servicio</div>
                        <div id="modal-service-date" class="text-slate-600 mt-1"></div>
                    </div>
                    <div class="mb-4">
                        <div class="font-medium">Estado</div>
                        <div id="modal-status" class="mt-1"></div>
                    </div>
                    <div class="mb-4">
                        <div class="font-medium">Descripción</div>
                        <div id="modal-description" class="text-slate-600 mt-1"></div>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <a id="modal-view-link" href="#" class="btn btn-primary mr-1">Ver completo</a>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales/es.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Datos de ejemplo para el calendario (en una aplicación real, estos datos vendrían del backend)
            const maintenanceEvents = [
                {
                    id: '1',
                    title: 'Cambio de aceite - Ford F-150',
                    start: '2025-06-10',
                    className: 'maintenance-completed',
                    extendedProps: {
                        vehicle: 'Ford F-150 (2020) - ABC123',
                        serviceType: 'Cambio de aceite',
                        serviceDate: '10/06/2025',
                        status: 'completed',
                        description: 'Cambio de aceite y filtro programado.'
                    }
                },
                {
                    id: '2',
                    title: 'Rotación de neumáticos - Chevrolet Silverado',
                    start: '2025-06-15',
                    className: 'maintenance-pending',
                    extendedProps: {
                        vehicle: 'Chevrolet Silverado (2019) - XYZ789',
                        serviceType: 'Rotación de neumáticos',
                        serviceDate: '15/06/2025',
                        status: 'pending',
                        description: 'Rotación de neumáticos programada.'
                    }
                },
                {
                    id: '3',
                    title: 'Inspección - Kenworth T680',
                    start: '2025-06-20',
                    className: 'maintenance-pending',
                    extendedProps: {
                        vehicle: 'Kenworth T680 (2021) - DEF456',
                        serviceType: 'Inspección',
                        serviceDate: '20/06/2025',
                        status: 'pending',
                        description: 'Inspección completa del vehículo.'
                    }
                },
                {
                    id: '4',
                    title: 'Servicio de frenos - Ford F-150',
                    start: '2025-07-05',
                    className: 'maintenance-pending',
                    extendedProps: {
                        vehicle: 'Ford F-150 (2020) - ABC123',
                        serviceType: 'Servicio de frenos',
                        serviceDate: '05/07/2025',
                        status: 'pending',
                        description: 'Revisión y cambio de pastillas de freno.'
                    }
                },
                {
                    id: '5',
                    title: 'Cambio de aceite - Kenworth T680',
                    start: '2025-07-15',
                    className: 'maintenance-pending',
                    extendedProps: {
                        vehicle: 'Kenworth T680 (2021) - DEF456',
                        serviceType: 'Cambio de aceite',
                        serviceDate: '15/07/2025',
                        status: 'pending',
                        description: 'Cambio de aceite y filtro programado.'
                    }
                }
            ];
            
            // Inicializar el calendario
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                locale: 'es',
                events: maintenanceEvents,
                eventClick: function(info) {
                    // Mostrar modal con detalles del mantenimiento
                    const event = info.event;
                    const props = event.extendedProps;
                    
                    document.getElementById('modal-title').textContent = event.title;
                    document.getElementById('modal-vehicle').textContent = props.vehicle;
                    document.getElementById('modal-service-type').textContent = props.serviceType;
                    document.getElementById('modal-service-date').textContent = props.serviceDate;
                    
                    const statusEl = document.getElementById('modal-status');
                    if (props.status === 'completed') {
                        statusEl.innerHTML = '<span class="px-2 py-1 rounded-full bg-success text-white">Completado</span>';
                    } else {
                        statusEl.innerHTML = '<span class="px-2 py-1 rounded-full bg-warning text-white">Pendiente</span>';
                    }
                    
                    document.getElementById('modal-description').textContent = props.description;
                    document.getElementById('modal-view-link').href = `/admin/maintenance/${event.id}`;
                    
                    // Mostrar el modal
                    const modal = tailwind.Modal.getOrCreateInstance(document.getElementById('maintenance-modal'));
                    modal.show();
                },
                eventDidMount: function(info) {
                    // Tooltip para mostrar detalles al pasar el mouse
                    new tippy(info.el, {
                        content: `
                            <div class="p-2">
                                <div class="font-bold">${info.event.title}</div>
                                <div>Fecha: ${info.event.extendedProps.serviceDate}</div>
                                <div>Estado: ${info.event.extendedProps.status === 'completed' ? 'Completado' : 'Pendiente'}</div>
                            </div>
                        `,
                        allowHTML: true,
                        theme: 'light-border'
                    });
                }
            });
            calendar.render();
            
            // Filtrar eventos del calendario
            document.getElementById('apply-filters').addEventListener('click', function() {
                const serviceType = document.getElementById('service-type-filter').value;
                const status = document.getElementById('status-filter').value;
                
                calendar.getEvents().forEach(event => {
                    const props = event.extendedProps;
                    let visible = true;
                    
                    // Filtrar por tipo de servicio
                    if (serviceType !== 'all') {
                        const serviceTypeMap = {
                            'oil_change': 'Cambio de aceite',
                            'tire_rotation': 'Rotación de neumáticos',
                            'brake_service': 'Servicio de frenos',
                            'inspection': 'Inspección',
                            'repair': 'Reparación',
                            'other': 'Otro'
                        };
                        
                        if (props.serviceType !== serviceTypeMap[serviceType]) {
                            visible = false;
                        }
                    }
                    
                    // Filtrar por estado
                    if (status !== 'all' && props.status !== status) {
                        visible = false;
                    }
                    
                    // Mostrar u ocultar el evento
                    event.setProp('display', visible ? 'auto' : 'none');
                });
            });
        });
    </script>
@endsection
