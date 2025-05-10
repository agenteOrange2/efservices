/**
 * Dashboard Chart Fix
 * Solución para el error de colors en las gráficas del dashboard
 */

// Colores que serán utilizados por los gráficos
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
