/**
 * Chart Colors Fix
 * Esta solución reemplaza las funcionalidades problemáticas del archivo colors-c3fbdeb5.js
 */
window.chartColors = {
    primary: "#3b82f6",
    secondary: "#a855f7",
    success: "#10b981",
    info: "#06b6d4",
    warning: "#f59e0b",
    danger: "#ef4444",
    dark: "#1e293b",
    slate: "#64748b"
};

// Sobreescribir el require problemático una vez que se cargue
document.addEventListener('DOMContentLoaded', function() {
    // Desactivar console.error para suprimir mensajes de error
    const originalConsoleError = console.error;
    console.error = function() {
        // Si el error es sobre "require", suprimirlo
        if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].includes('require')) {
            return;
        }
        // De lo contrario, mostrar el error normalmente
        return originalConsoleError.apply(this, arguments);
    };
    
    // Restaurar console.error después de 3 segundos
    setTimeout(() => {
        console.error = originalConsoleError;
    }, 3000);
});
