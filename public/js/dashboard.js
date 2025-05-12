/**
 * Main Dashboard - EF Services
 * Script to handle all dashboard functionalities
 */

document.addEventListener('DOMContentLoaded', function() {
    // Asegurarse de que Alpine.js esté disponible
    if (typeof Alpine === 'undefined') {
        console.error('Alpine.js no está cargado. El dashboard requiere Alpine.js para funcionar correctamente.');
        return;
    }

    // Colores para gráficos
    const chartColors = {
        primary: "rgb(59, 130, 246)",
        secondary: "rgb(168, 85, 247)",
        success: "rgb(16, 185, 129)",
        info: "rgb(6, 182, 212)",
        warning: "rgb(245, 158, 11)",
        danger: "rgb(239, 68, 68)",
        dark: "rgb(30, 41, 59)",
        slate: "rgb(100, 116, 139)"
    };

    // Función para obtener color con opacidad
    window.getColor = function(colorName, opacity = 1) {
        if (!chartColors[colorName]) {
            console.warn(`Color ${colorName} no encontrado, usando fallback`);
            return `rgba(0, 0, 0, ${opacity})`;
        }
        
        // Extraer los valores RGB del string (formato "rgb(r, g, b)")
        const rgbMatch = chartColors[colorName].match(/rgb\((.+)\)/);
        if (rgbMatch && rgbMatch[1]) {
            return `rgba(${rgbMatch[1]}, ${opacity})`;
        }
        
        return chartColors[colorName];
    };

    // Inicializar los gráficos del dashboard
    function initializeCharts(data) {
        // Verificar si Chart.js está disponible
        if (typeof Chart === 'undefined') {
            console.error('Chart.js no está cargado. Los gráficos requieren Chart.js para funcionar correctamente.');
            return;
        }

        // Gráfico de usuarios
        const userChartCtx = document.getElementById('userChart');
        if (userChartCtx) {
            const userData = data.users || {active: 0, pending: 0, inactive: 0};
            
            // Ensure we have at least some data to display (even if it's zero)
            const chartData = [userData.active || 0, userData.pending || 0, userData.inactive || 0];
            
            // If all values are zero, set minimal values to show the chart segments
            const allZero = chartData.every(val => val === 0);
            const displayData = allZero ? [1, 1, 1] : chartData;
            
            // Destruir gráfico existente si hay uno
            if (userChartCtx._chart) {
                userChartCtx._chart.destroy();
            }
            
            userChartCtx._chart = new Chart(userChartCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Pending', 'Inactive'],
                    datasets: [{
                        data: displayData,
                        backgroundColor: [
                            getColor('success'),
                            getColor('warning'),
                            getColor('danger')
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '80%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = allZero ? 0 : context.raw || 0;
                                    const total = allZero ? 0 : context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de vehículos
        const vehicleChartCtx = document.getElementById('vehicleChart');
        if (vehicleChartCtx) {
            const vehicleData = data.vehicles || {active: 0, suspended: 0, outOfService: 0};
            
            // Ensure we have at least some data to display (even if it's zero)
            const chartData = [vehicleData.active || 0, vehicleData.suspended || 0, vehicleData.outOfService || 0];
            
            // If all values are zero, set minimal values to show the chart segments
            const allZero = chartData.every(val => val === 0);
            const displayData = allZero ? [1, 1, 1] : chartData;
            
            // Destroy existing chart if there is one
            if (vehicleChartCtx._chart) {
                vehicleChartCtx._chart.destroy();
            }
            
            vehicleChartCtx._chart = new Chart(vehicleChartCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Suspended', 'Out of Service'],
                    datasets: [{
                        data: displayData,
                        backgroundColor: [
                            getColor('success'),
                            getColor('warning'),
                            getColor('danger')
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '80%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = allZero ? 0 : context.raw || 0;
                                    const total = allZero ? 0 : context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de mantenimiento
        const maintenanceChartCtx = document.getElementById('maintenanceChart');
        if (maintenanceChartCtx) {
            const maintenanceData = data.maintenance || {
                completed: 0, 
                pending: 0, 
                upcoming: 0, 
                overdue: 0
            };
            
            // Ensure we have at least some data to display (even if it's zero)
            const chartData = [
                maintenanceData.completed || 0, 
                maintenanceData.pending || 0, 
                maintenanceData.upcoming || 0, 
                maintenanceData.overdue || 0
            ];
            
            // If all values are zero, set minimal values to show the chart segments
            const allZero = chartData.every(val => val === 0);
            const displayData = allZero ? [1, 1, 1, 1] : chartData;
            
            // Destroy existing chart if there is one
            if (maintenanceChartCtx._chart) {
                maintenanceChartCtx._chart.destroy();
            }
            
            maintenanceChartCtx._chart = new Chart(maintenanceChartCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Pending', 'Upcoming', 'Overdue'],
                    datasets: [{
                        data: displayData,
                        backgroundColor: [
                            getColor('success'),
                            getColor('info'),
                            getColor('warning'),
                            getColor('danger')
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '80%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = allZero ? 0 : context.raw || 0;
                                    const total = allZero ? 0 : context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

    // Registrar el componente Alpine.js para el dashboard
    window.dashboardApp = function() {
        return {
            dateRange: 'daily',
            customDateStart: '',
            customDateEnd: '',
            showCustomDateFields: false,
            isLoading: false,
            stats: {},
            chartData: {},

            init() {
                // Inicializar fechas predeterminadas
                const today = new Date();
                const lastWeek = new Date();
                lastWeek.setDate(today.getDate() - 7);
                
                this.customDateStart = this.formatDate(lastWeek);
                this.customDateEnd = this.formatDate(today);
                
                // Observador para cambios en el selector de fecha
                this.$watch('dateRange', (value) => {
                    this.showCustomDateFields = value === 'custom';
                    if (value !== 'custom') {
                        this.updateDashboard();
                    }
                });

                // Cargar datos iniciales después de que el DOM esté listo
                this.$nextTick(() => {
                    // Inicializar los gráficos con los datos del servidor
                    if (this.chartData && Object.keys(this.chartData).length > 0) {
                        initializeCharts(this.chartData);
                    }
                });
            },

            formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            },

            updateDashboard() {
                this.isLoading = true;
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch('/admin/dashboard/ajax-update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        date_range: this.dateRange,
                        custom_date_start: this.customDateStart,
                        custom_date_end: this.customDateEnd
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.stats) {
                        this.stats = data.stats;
                        this.chartData = data.chartData;
                        
                        // Actualizar gráficos con los nuevos datos
                        this.$nextTick(() => {
                            initializeCharts(this.chartData);
                            console.log('Dashboard actualizado correctamente');
                        });
                    } else {
                        console.error('Datos recibidos inválidos:', data);
                    }
                    this.isLoading = false;
                })
                .catch(error => {
                    console.error('Error en la actualización del dashboard:', error);
                    this.isLoading = false;
                    alert('Error al cargar los datos. Por favor, intente nuevamente.');
                });
            },

            applyCustomDateFilter() {
                if (this.customDateStart && this.customDateEnd) {
                    this.updateDashboard();
                } else {
                    alert('Por favor, seleccione fechas de inicio y fin válidas.');
                }
            },

            exportPdf() {
                this.isLoading = true;
                
                try {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/admin/dashboard/export-pdf';
                    form.target = '_blank';
                    
                    // CSRF Token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrfToken);
                    
                    // Date Range
                    const dateRangeInput = document.createElement('input');
                    dateRangeInput.type = 'hidden';
                    dateRangeInput.name = 'date_range';
                    dateRangeInput.value = this.dateRange;
                    form.appendChild(dateRangeInput);
                    
                    // Custom Dates (if applicable)
                    if (this.dateRange === 'custom') {
                        const startDateInput = document.createElement('input');
                        startDateInput.type = 'hidden';
                        startDateInput.name = 'custom_date_start';
                        startDateInput.value = this.customDateStart;
                        form.appendChild(startDateInput);
                        
                        const endDateInput = document.createElement('input');
                        endDateInput.type = 'hidden';
                        endDateInput.name = 'custom_date_end';
                        endDateInput.value = this.customDateEnd;
                        form.appendChild(endDateInput);
                    }
                    
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                    
                    // Indicador de éxito
                    setTimeout(() => {
                        this.isLoading = false;
                    }, 1000);
                } catch (error) {
                    console.error('Error al exportar PDF:', error);
                    this.isLoading = false;
                    alert('Error al generar el PDF. Por favor, intente nuevamente.');
                }
            }
        };
    };
});
