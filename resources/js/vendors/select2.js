// Inicialización de Select2
// Esperar a que el DOM esté listo y jQuery esté disponible
document.addEventListener('DOMContentLoaded', function() {
    // Verificar que jQuery esté disponible
    if (typeof window.jQuery === 'undefined') {
        console.error('jQuery no está disponible para Select2');
        return;
    }
    
    // Usar jQuery una vez que está disponible
    (function($) {
    // Función para inicializar Select2 en elementos específicos
    function initSelect2() {
        $('.select2').each(function() {
            if (!$(this).data('select2')) {
                $(this).select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownParent: document.body,
                    placeholder: $(this).data('placeholder') || 'Selecciona una opción'
                });
            }
        });
    }

    // Inicializar al cargar el documento
    $(document).ready(function() {
        initSelect2();
    });

    // Exponer la función globalmente para poder llamarla desde otros scripts
    window.initSelect2 = initSelect2;
})(window.jQuery);
});
