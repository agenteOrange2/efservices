import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";

(function () {
    "use strict";

    // Función para obtener eventos de mantenimiento
    function getMaintenanceEvents() {
        let maintenanceEvents = [];
        const maintenanceEventsElement = document.getElementById('maintenance-events-data');
        
        if (maintenanceEventsElement && maintenanceEventsElement.dataset.events) {
            try {
                maintenanceEvents = JSON.parse(maintenanceEventsElement.dataset.events);
                console.log('Eventos de mantenimiento cargados:', maintenanceEvents);
                console.log('Número de eventos:', maintenanceEvents.length);
            } catch (e) {
                console.error('Error al parsear eventos de mantenimiento:', e);
            }
        }
        
        // Si no hay eventos de mantenimiento, usar eventos por defecto
        if (!maintenanceEvents || !maintenanceEvents.length) {
            console.warn('No hay eventos de mantenimiento, usando eventos por defecto');
            return [
                {
                    title: "Vue Vixens Day",
                    start: "2045-01-05",
                    end: "2045-01-08",
                },
                {
                    title: "VueConfUS",
                    start: "2045-01-11",
                    end: "2045-01-15",
                },
            ];
        }
        
        return maintenanceEvents;
    }

    $(".full-calendar").each(function () {
        const events = getMaintenanceEvents();
        
        let calendar = new Calendar($(this).children()[0], {
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
            initialDate: new Date(), // Usar fecha actual en lugar de 2045
            navLinks: true,
            editable: false, // No editable para mantenimientos
            dayMaxEvents: true,
            events: events,
            drop: function (info) {
                if (
                    $("#checkbox-events").length &&
                    $("#checkbox-events")[0].checked
                ) {
                    $(info.draggedEl).parent().remove();

                    if ($("#calendar-events").children().length == 1) {
                        $("#calendar-no-events").removeClass("hidden");
                    }
                }
            },
        });

        console.log('Renderizando calendario con eventos:', events);
        calendar.render();
    });
})();
