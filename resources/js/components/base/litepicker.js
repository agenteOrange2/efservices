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
                minYear: 1990,
                maxYear: 2050,
                months: true,
                years: true,
            },
            setup: (picker) => {
                picker.on('selected', (date) => {
                    // Simplemente seleccionar la fecha sin modificarla
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
