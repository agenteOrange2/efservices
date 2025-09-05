import { Litepicker } from "litepicker";

// Driver-specific datepicker component
// This is separate from the admin litepicker to avoid conflicts
(function () {
    "use strict";

    // Initialize driver datepicker for registration form
    const initDriverDatepicker = () => {
        // Inyectar estilos CSS de litepicker
        if (!document.getElementById('driver-litepicker-styles')) {
            const style = document.createElement('style');
            style.id = 'driver-litepicker-styles';
            style.textContent = `
                .litepicker {
                    background-color: white;
                    border-radius: 0.5rem;
                    font-size: 0.875rem;
                    z-index: 999999 !important;
                    margin-top: 7px;
                    box-shadow: 0px 3px 20px rgba(0, 0, 0, 0.08);
                    border: 1px solid #e2e8f0;
                }
                @media (max-width: 1023px) {
                    .litepicker {
                        width: 310px;
                    }
                }
                @media (max-width: 639px) {
                    .litepicker {
                        left: 0px !important;
                        right: 0px;
                        margin-left: auto;
                        margin-right: auto;
                    }
                }
                .litepicker .container__months,
                .litepicker .container__footer {
                    box-shadow: none;
                    background-color: transparent;
                    padding-top: 0;
                }
                .litepicker .container__footer {
                    border-top: 1px solid rgba(226, 232, 240, 0.6);
                    margin: 0;
                    padding: 0.75rem;
                }
                .litepicker .container__footer .button-apply,
                .litepicker .container__footer .button-cancel {
                    width: 5rem;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.375rem;
                    font-weight: 500;
                    margin-left: 0.25rem;
                    margin-right: 0;
                }
                .litepicker .container__footer .button-apply {
                    background-color: #374151;
                    color: white;
                }
                .litepicker .container__footer .button-cancel {
                    background-color: rgba(226, 232, 240, 0.7);
                    color: #6b7280;
                }
                .litepicker .container__months {
                    padding-left: 0.25rem;
                    padding-right: 0.25rem;
                }
                .litepicker .container__months .month-item-weekdays-row {
                    color: #9ca3af;
                    margin-top: 0.75rem;
                }
                .litepicker .container__months .month-item-header {
                    padding-left: 0;
                    padding-right: 0;
                    padding-bottom: 0;
                    padding-top: 0.5rem;
                }
                .litepicker .container__months .month-item-header .button-previous-month,
                .litepicker .container__months .month-item-header .button-next-month {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 2rem;
                    height: 2rem;
                }
                .litepicker .container__months .month-item-header .button-previous-month:hover,
                .litepicker .container__months .month-item-header .button-next-month:hover {
                    background-color: #f1f5f9;
                }
                .litepicker .container__months .month-item-header .button-previous-month svg {
                    transform: rotate(135deg);
                    margin-right: -0.25rem;
                }
                .litepicker .container__months .month-item-header .button-next-month svg {
                    transform: rotate(-45deg);
                    margin-left: -0.25rem;
                }
                .litepicker .container__months .month-item-header .button-previous-month svg,
                .litepicker .container__months .month-item-header .button-next-month svg {
                    fill: transparent;
                    border: solid black;
                    border-width: 0 2px 2px 0;
                    border-radius: 0;
                    display: inline-block;
                    border-color: #4a5568;
                    width: 0.5rem;
                    height: 0.5rem;
                }
                .litepicker .container__months .month-item-header div > .month-item-name,
                .litepicker .container__months .month-item-header div > .month-item-year {
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='rgb(74, 85, 104)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
                    background-size: 15px;
                    background-position: center right 0.6rem;
                    background-color: transparent;
                    font-size: 0.875rem;
                    border-width: 1px;
                    border-style: solid;
                    background-repeat: no-repeat;
                    padding: 0.25rem 2rem 0.25rem 0.5rem;
                    border-radius: 0.375rem;
                    border-color: #e2e8f0;
                    font-weight: 400;
                }
                .litepicker .container__months .month-item-header div > .month-item-name:focus,
                .litepicker .container__months .month-item-header div > .month-item-year:focus {
                    outline: none;
                    border-color: #e2e8f0;
                }
                .litepicker .container__days .day-item {
                    color: #1e293b;
                }
                .litepicker .container__days .day-item:hover {
                    box-shadow: none;
                    background-color: #f1f5f9;
                    color: #1e293b;
                }
                .litepicker .container__days .day-item.is-today,
                .litepicker .container__days .day-item.is-today:hover {
                    font-weight: 500;
                    color: #334155;
                }
                .litepicker .container__days .day-item.is-start-date,
                .litepicker .container__days .day-item.is-start-date:hover,
                .litepicker .container__days .day-item.is-end-date,
                .litepicker .container__days .day-item.is-end-date:hover {
                    background-color: #374151;
                    color: white;
                }
                .litepicker .container__days .day-item.is-in-range,
                .litepicker .container__days .day-item.is-in-range:hover {
                    background-color: #f1f5f9;
                }
                .litepicker .container__days .week-number {
                    color: #9ca3af;
                }
                @media (max-width: 1023px) {
                    :root {
                        --litepicker-day-width: 35px;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        const driverDatepickers = document.querySelectorAll(".driver-datepicker");
        
        if (driverDatepickers.length) {
            driverDatepickers.forEach((el) => {
                // Skip if already initialized
                if (el._litepicker) {
                    return;
                }

                const picker = new Litepicker({
                    element: el,
                    format: "MM/DD/YYYY",
                    singleMode: true,
                    numberOfColumns: 1,
                    numberOfMonths: 1,
                    showWeekNumbers: false,
                    autoApply: true,
                    allowRepick: true,
                    dropdowns: {
                        minYear: 1960,
                        maxYear: 2040,
                        months: true,
                        years: true
                    },
                    setup: (picker) => {
                        picker.on('selected', (date1, date2) => {
                            // FORZAR formato MM/DD/YYYY específicamente para drivers
                            const month = String(date1.getMonth() + 1).padStart(2, '0');
                            const day = String(date1.getDate()).padStart(2, '0');
                            const year = date1.getFullYear();
                            const formattedDate = `${month}/${day}/${year}`;
                            
                            // Actualizar el input con el formato correcto
                            picker.options.element.value = formattedDate;
                            
                            // Disparar eventos para Livewire
                            const inputEvent = new Event('input', { bubbles: true });
                            const changeEvent = new Event('change', { bubbles: true });
                            picker.options.element.dispatchEvent(inputEvent);
                            picker.options.element.dispatchEvent(changeEvent);
                            
                            console.log('Driver Datepicker: Fecha seleccionada:', formattedDate);
                        });
                    }
                });

                // Store reference to prevent re-initialization
                el._litepicker = picker;
            });
        }
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDriverDatepicker);
    } else {
        initDriverDatepicker();
    }

    // Re-initialize after Livewire updates
    document.addEventListener('livewire:navigated', initDriverDatepicker);
    document.addEventListener('livewire:load', initDriverDatepicker);
    
    // For Livewire v3
    if (window.Livewire) {
        window.Livewire.hook('morph.updated', () => {
            setTimeout(initDriverDatepicker, 100);
        });
    }
})();