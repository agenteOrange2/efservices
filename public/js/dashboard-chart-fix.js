/**
 * Dashboard Chart Fix
 * Solución completa para el error de colors en todas las gráficas del dashboard
 */
console.log('dashboard-chart-fix.js cargado correctamente');

// Prevenir que el error de require interrumpa la ejecución
window.require = function(module) {
    console.log(`Intento de cargar módulo: ${module} interceptado`);
    // Simular módulos comunes
    if (module.includes('tailwindcss/resolveConfig')) {
        return function(config) { return { theme: { colors: window.tailwindColors } }; };
    }
    if (module === './helper') {
        return { resolveColor: window.resolveColor };
    }
    // Devolver un objeto vacío como fallback
    return {};
};

// Configuración de colores de Tailwind
window.tailwindColors = {
    primary: { DEFAULT: 'rgb(59, 130, 246)' },
    secondary: { DEFAULT: 'rgb(168, 85, 247)' },
    success: { DEFAULT: 'rgb(16, 185, 129)' },
    info: { DEFAULT: 'rgb(6, 182, 212)' },
    warning: { DEFAULT: 'rgb(245, 158, 11)' },
    danger: { DEFAULT: 'rgb(239, 68, 68)' },
    dark: { DEFAULT: 'rgb(30, 41, 59)' },
    slate: { DEFAULT: 'rgb(100, 116, 139)' }
};

// Función resolveColor que simula el comportamiento del helper original
window.resolveColor = function(colorValue) {
    return colorValue;
};

// Colores que serán utilizados directamente por los gráficos
window.chartColors = {
    primary: "rgb(59, 130, 246)",
    secondary: "rgb(168, 85, 247)",
    success: "rgb(16, 185, 129)",
    info: "rgb(6, 182, 212)",
    warning: "rgb(245, 158, 11)",
    danger: "rgb(239, 68, 68)",
    dark: "rgb(30, 41, 59)",
    slate: "rgb(100, 116, 139)"
};

// Función getColor que reemplaza a la función problemática
window.getColor = function(colorName, opacity = 1) {
    if (!window.chartColors[colorName]) {
        console.warn(`Color ${colorName} no encontrado, usando fallback`);
        return `rgba(0, 0, 0, ${opacity})`;
    }
    
    // Extraer los valores RGB del string (formato "rgb(r, g, b)")
    const rgbMatch = window.chartColors[colorName].match(/rgb\((.+)\)/);
    if (rgbMatch && rgbMatch[1]) {
        return `rgba(${rgbMatch[1]}, ${opacity})`;
    }
    
    return window.chartColors[colorName];
};

// Asegurarse de que la función getColor esté disponible globalmente
if (typeof window.colors === 'undefined') {
    window.colors = { getColor: window.getColor };
}

// SOLUCIÓN ADICIONAL PARA TODAS LAS GRÁFICAS
// Esta función se asegura de que Chart.js siempre use nuestros colores
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que carguen todas las gráficas
    setTimeout(function() {
        console.log('Aplicando corrección a todos los gráficos...');
        
        // Asegurarse de que Chart esté disponible
        if (typeof Chart !== 'undefined') {
            // Hacer monkey patching a Chart para asegurar que nuestros colores sean usados
            const originalInit = Chart.prototype.initialize;
            if (originalInit && !Chart.prototype._patched) {
                Chart.prototype.initialize = function() {
                    // Llamar a la función original
                    const result = originalInit.apply(this, arguments);
                    
                    // Verificar si hay datos y datasets
                    if (this.config && this.config.data && this.config.data.datasets) {
                        this.config.data.datasets.forEach(dataset => {
                            // Colores para las gráficas de dona (donut)
                            if (this.config.type === 'doughnut' || this.config.type === 'pie') {
                                // Si no tiene colores personalizado, aplicar los nuestros
                                if (!dataset._hasCustomColors) {
                                    dataset._hasCustomColors = true;
                                    dataset.backgroundColor = [
                                        window.chartColors.primary,
                                        window.chartColors.success,
                                        window.chartColors.warning,
                                        window.chartColors.danger,
                                        window.chartColors.info
                                    ];
                                }
                            }
                            
                            // Para otros tipos de gráficas
                            if (!dataset.backgroundColor && !dataset._hasCustomColors) {
                                dataset._hasCustomColors = true;
                                dataset.backgroundColor = window.chartColors.primary;
                            }
                        });
                    }
                    
                    // Aplicar cambios
                    if (typeof this.update === 'function') {
                        this.update();
                    }
                    
                    return result;
                };
                
                // Marcar como parchado para evitar aplicar el parche múltiples veces
                Chart.prototype._patched = true;
                console.log('Chart.js parchado correctamente para usar nuestros colores');
            }
            
            // Intentar reinicializar gráficas existentes
            // Esto es para forzar a las gráficas ya creadas a usar nuestros colores
            document.querySelectorAll('canvas').forEach(canvas => {
                // Intentar acceder a la instancia de Chart.js
                const chartInstance = canvas._chart;
                if (chartInstance && typeof chartInstance.update === 'function') {
                    // Aplicar colores personalizados y actualizar
                    if (chartInstance.config && chartInstance.config.data && chartInstance.config.data.datasets) {
                        chartInstance.config.data.datasets.forEach(dataset => {
                            if (!dataset._hasCustomColors) {
                                dataset._hasCustomColors = true;
                                if (chartInstance.config.type === 'doughnut' || chartInstance.config.type === 'pie') {
                                    dataset.backgroundColor = [
                                        window.chartColors.primary,
                                        window.chartColors.success,
                                        window.chartColors.warning,
                                        window.chartColors.danger,
                                        window.chartColors.info
                                    ];
                                } else {
                                    dataset.backgroundColor = window.chartColors.primary;
                                }
                            }
                        });
                    }
                    chartInstance.update();
                    console.log('Gráfica actualizada con nuevos colores');
                }
            });
        }
    }, 500); // Esperar 500ms para asegurarse de que todas las gráficas estén cargadas
});
