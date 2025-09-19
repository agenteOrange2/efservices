(function () {
    "use strict";

    // Litepicker - Configuración global para toda la aplicación
    $(".datepicker").each(function () {
        // Configuración base
        let options = {
            autoApply: true,
            singleMode: true,
            numberOfColumns: 1,
            numberOfMonths: 1,
            showWeekNumbers: false,
            format: "MM/DD/YYYY",
            allowRepick: true,
            dropdowns: {
                minYear: 1950,
                maxYear: 2050,
                months: true,
                years: true,
            },
            setup: (picker) => {
                picker.on('selected', (date1, date2) => {
                    // Prevenir bucles infinitos
                    const inputElement = picker.options.element;
                    if (!inputElement || inputElement._updating) {
                        return;
                    }
                    
                    inputElement._updating = true;
                    
                    // Disparar evento input para que Livewire detecte el cambio con delay
                    setTimeout(() => {
                        // Crear y disparar evento input
                        const event = new Event('input', { bubbles: true });
                        inputElement.dispatchEvent(event);
                        
                        // También disparar evento change para mayor compatibilidad
                        const changeEvent = new Event('change', { bubbles: true });
                        inputElement.dispatchEvent(changeEvent);
                        
                        // Limpiar flag después de los eventos
                        setTimeout(() => {
                            inputElement._updating = false;
                        }, 50);
                    }, 10);
                });
            }
        };

        // Permitir sobreescritura de opciones mediante atributos data
        if ($(this).data("format")) {
            options.format = $(this).data("format");
        }

        if ($(this).data("number-of-columns")) {
            options.numberOfColumns = $(this).data("number-of-columns");
        }

        if ($(this).data("number-of-months")) {
            options.numberOfMonths = $(this).data("number-of-months");
        }
        
        // Creación de la instancia de Litepicker
        let picker = new Litepicker({
            element: this,
            ...options,
        });
    });
})();
