// Las importaciones de Pikaday y moment se han movido a app.js
// Este archivo ahora solo contiene funciones auxiliares para el manejo de fechas

// Función auxiliar para formatear fechas en formato MM-DD-YYYY
window.formatDateMMDDYYYY = function(date) {
    if (!date) return '';
    return moment(date).format('MM-DD-YYYY');
};

// Función auxiliar para parsear fechas desde formato MM-DD-YYYY
window.parseDateMMDDYYYY = function(dateString) {
    if (!dateString) return null;
    return moment(dateString, 'MM-DD-YYYY').toDate();
};

// Emitir evento personalizado cuando se agrega una nueva dirección
document.addEventListener('DOMContentLoaded', function() {
    if (window.Livewire) {
        Livewire.on('previousAddressAdded', function() {
            // Disparar evento para que se inicialicen los nuevos datepickers
            window.dispatchEvent(new CustomEvent('dom-updated'));
        });
    }
});
