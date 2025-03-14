// Manejar tooltips
function setupTooltips() {
    const buttons = document.querySelectorAll('[data-tooltip]');
    
    buttons.forEach(button => {
        const tooltip = button.parentNode.querySelector('.tooltip-content');
        if (!tooltip) return;
        
        button.addEventListener('mouseenter', () => {
            tooltip.classList.remove('hidden');
        });
        
        button.addEventListener('mouseleave', () => {
            tooltip.classList.add('hidden');
        });
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    setupTooltips();
    // Otras inicializaciones...
});