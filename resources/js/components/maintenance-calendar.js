import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Emitir evento personalizado cuando el DOM está listo
    document.dispatchEvent(new CustomEvent('calendar:init'));

    // Inicializar calendario de mantenimiento
    function initMaintenanceCalendar() {
        // Buscar los contenedores del calendario
        const calendarContainers = document.querySelectorAll('.full-calendar');
        
        if (calendarContainers.length === 0) return;

        // Para cada contenedor de calendario
        calendarContainers.forEach(function(container) {
            // Obtener eventos del elemento de datos
            let maintenanceEvents = [];
            const maintenanceEventsElement = document.getElementById('maintenance-events-data');
            
            // Si hay datos disponibles desde el backend, usarlos
            if (maintenanceEventsElement && maintenanceEventsElement.dataset.events) {
                try {
                    maintenanceEvents = JSON.parse(maintenanceEventsElement.dataset.events);
                    console.log('Eventos cargados desde el backend:', maintenanceEvents);
                } catch (e) {
                    console.error('Error al parsear eventos:', e);
                }
            }
            
            // Si no hay eventos desde el backend o hay un error, usar ejemplos
            if (!maintenanceEvents || maintenanceEvents.length === 0) {
                console.log('No hay eventos reales, usando ejemplos');
                maintenanceEvents = [
                    {
                        id: '1',
                        title: 'Cambio de aceite - Ford F-150',
                        start: '2025-06-10',
                        className: 'maintenance-completed',
                        extendedProps: {
                            vehicle: 'Ford F-150 (2020) - ABC123',
                            serviceType: 'Cambio de aceite',
                            serviceDate: '10/06/2025',
                            status: 1,
                            cost: '$150.00',
                            description: 'Cambio de aceite y filtro programado.'
                        }
                    },
                    {
                        id: '2',
                        title: 'Revisión de frenos - Chevrolet Silverado',
                        start: '2025-06-15',
                        className: 'maintenance-pending',
                        extendedProps: {
                            vehicle: 'Chevrolet Silverado (2019) - XYZ789',
                            serviceType: 'Revisión de frenos',
                            serviceDate: '15/06/2025',
                            status: 0,
                            cost: '$250.00',
                            description: 'Revisión y posible cambio de pastillas de freno.'
                        }
                    },
                    {
                        id: '3',
                        title: 'Alineación y balanceo - Toyota Tacoma',
                        start: '2025-06-20',
                        className: 'maintenance-completed',
                        extendedProps: {
                            vehicle: 'Toyota Tacoma (2021) - DEF456',
                            serviceType: 'Alineación y balanceo',
                            serviceDate: '20/06/2025',
                            status: 1,
                            cost: '$120.00',
                            description: 'Alineación y balanceo de ruedas.'
                        }
                    }
                ];
            }

            // Crear instancia de calendario con los eventos correctos
            const calendarEl = container.children[0];
            
            if (calendarEl) {
                const calendar = new Calendar(calendarEl, {
                    plugins: [
                        interactionPlugin,
                        dayGridPlugin,
                        timeGridPlugin,
                        listPlugin,
                    ],
                    headerToolbar: {
                        left: "prev,next today",
                        center: "title",
                        right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
                    },
                    initialDate: new Date(), // Usar la fecha actual
                    navLinks: true,
                    editable: false, // No permite editar eventos
                    dayMaxEvents: true,
                    events: maintenanceEvents,
                    locale: 'es',
                    eventClick: function(info) {
                        // Mostrar modal con detalles del mantenimiento
                        const event = info.event;
                        const props = event.extendedProps;
                        
                        document.getElementById('modal-title').textContent = event.title;
                        document.getElementById('modal-vehicle').textContent = props.vehicle;
                        document.getElementById('modal-service-type').textContent = event.title.split(' - ')[0];
                        document.getElementById('modal-service-date').textContent = props.serviceDate;
                        
                        const statusEl = document.getElementById('modal-status');
                        if (props.status === 1 || props.status === true) {
                            statusEl.innerHTML = '<span class="px-2 py-1 rounded-full bg-success text-white">Completado</span>';
                        } else if (props.status === 2) {
                            statusEl.innerHTML = '<span class="px-2 py-1 rounded-full bg-warning text-white">Próximo</span>';
                        } else {
                            statusEl.innerHTML = '<span class="px-2 py-1 rounded-full bg-danger text-white">Pendiente</span>';
                        }
                        
                        // Mostrar costo si está disponible
                        if (props.cost) {
                            document.getElementById('modal-cost').textContent = props.cost;
                            document.getElementById('modal-cost-container').style.display = 'block';
                        } else {
                            document.getElementById('modal-cost-container').style.display = 'none';
                        }
                        
                        // Mostrar descripción si está disponible
                        if (props.description) {
                            document.getElementById('modal-description').textContent = props.description;
                            document.getElementById('modal-description-container').style.display = 'block';
                        } else {
                            document.getElementById('modal-description-container').style.display = 'none';
                        }

                        // Mostrar modal usando la API de TailwindUI
                        const modal = tailwind.Modal.getOrCreateInstance(document.getElementById('maintenance-modal'));
                        modal.show();
                    },
                    eventDidMount: function(info) {
                        // Crear tooltips para los eventos
                        tippy(info.el, {
                            content: info.event.title,
                            placement: 'top',
                        });
                    }
                });
                
                calendar.render();
            }
        });
    }

    // Inicializar el calendario cuando se cargue la página
    initMaintenanceCalendar();
});
