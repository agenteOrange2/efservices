/**
 * Main Dashboard - EF Services
 * Script to handle all dashboard functionalities
 */

document.addEventListener('DOMContentLoaded', function() {
    // Make sure Alpine.js is available
    if (typeof Alpine === 'undefined') {
        console.error('Alpine.js is not loaded. The dashboard requires Alpine.js to function properly.');
        return;
    }

    // Colors for charts
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

    // Function to get color with opacity
    window.getColor = function(colorName, opacity = 1) {
        if (!chartColors[colorName]) {
            console.warn(`Color ${colorName} not found, using fallback`);
            return `rgba(0, 0, 0, ${opacity})`;
        }
        
        // Extract RGB values from string (format "rgb(r, g, b)")
        const rgbMatch = chartColors[colorName].match(/rgb\((.+)\)/);
        if (rgbMatch && rgbMatch[1]) {
            return `rgba(${rgbMatch[1]}, ${opacity})`;
        }
        
        return chartColors[colorName];
    };

    // Initialize dashboard charts
    function initializeCharts(data) {
        // Check if Chart.js is available
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded. Charts require Chart.js to function properly.');
            return;
        }

        // User chart
        const userChartCtx = document.getElementById('userChart');
        if (userChartCtx) {
            console.log('User chart data:', data.users);
            const userData = data.users || {active: 0, pending: 0, inactive: 0};
            
            // Ensure we have at least some data to display (even if it's zero)
            const chartData = [userData.active || 0, userData.pending || 0, userData.inactive || 0];
            console.log('User chart values:', chartData);
            
            // If all values are zero, set minimal values to show the chart segments
            const allZero = chartData.every(val => val === 0);
            const displayData = allZero ? [1, 1, 1] : chartData;
            
            // Calculate total for center display
            const total = chartData.reduce((sum, val) => sum + val, 0);
            
            // Destroy existing chart if there is one
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

        // Vehicle chart
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

        // Maintenance chart
        const maintenanceChartCtx = document.getElementById('maintenanceChart');
        if (maintenanceChartCtx) {
            const maintenanceData = data.maintenance || {completed: 0, pending: 0, upcoming: 0, overdue: 0};
            
            // Ensure we have at least some data to display (even if it's zero)
            const chartData = [maintenanceData.completed || 0, maintenanceData.pending || 0, maintenanceData.upcoming || 0, maintenanceData.overdue || 0];
            
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
    }

    // Register Alpine.js component for the dashboard
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
                // Initialize default dates
                const today = new Date();
                const lastWeek = new Date();
                lastWeek.setDate(today.getDate() - 7);
                
                this.customDateStart = this.formatDate(lastWeek);
                this.customDateEnd = this.formatDate(today);
                
                // Watch for changes in date selector
                this.$watch('dateRange', (value) => {
                    this.showCustomDateFields = value === 'custom';
                    if (value !== 'custom') {
                        this.updateDashboard();
                    }
                });

                // Load initial data after DOM is ready
                this.$nextTick(() => {
                    // Initialize charts with server data
                    if (window.dashboardData) {
                        this.stats = window.dashboardData.stats;
                        this.chartData = window.dashboardData.chartData;
                        this.dateRange = window.dashboardData.dateRange;
                        this.customDateStart = window.dashboardData.customDateStart;
                        this.customDateEnd = window.dashboardData.customDateEnd;
                        this.showCustomDateFields = this.dateRange === 'custom';
                        
                        // Ensure charts are initialized with data
                        if (this.chartData && Object.keys(this.chartData).length > 0) {
                            console.log('Initializing charts with data:', this.chartData);
                            initializeCharts(this.chartData);
                        } else {
                            console.error('No chart data available');
                        }
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
                
                console.log('Updating dashboard with filters:', {
                    date_range: this.dateRange,
                    custom_date_start: this.customDateStart,
                    custom_date_end: this.customDateEnd
                });
                
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
                        throw new Error('Server response error: ' + response.statusText);
                    }
                    console.log('Server response received');
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (!data) {
                        throw new Error('No data received from server');
                    }
                    return data;
                })
                .then(data => {
                    if (data && data.stats) {
                        this.stats = data.stats;
                        this.chartData = data.chartData;
                        
                        this.$nextTick(() => {
                            initializeCharts(this.chartData);
                            console.log('Dashboard updated successfully');
                        });
                    } else {
                        console.error('Invalid data received:', data);
                    }
                    this.isLoading = false;
                })
                .catch(error => {
                    console.error('Error updating dashboard:', error);
                    this.isLoading = false;
                    
                    // Show more detailed error message
                    let errorMessage = 'Error loading data. ';
                    if (error.response && error.response.data && error.response.data.message) {
                        errorMessage += error.response.data.message;
                    } else if (error.message) {
                        errorMessage += error.message;
                    } else {
                        errorMessage += 'Please try again.';
                    }
                    
                    alert(errorMessage);
                });
            },

            applyCustomDateFilter() {
                if (this.customDateStart && this.customDateEnd) {
                    this.updateDashboard();
                } else {
                    alert('Please select valid start and end dates.');
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
                    
                    // Success indicator
                    setTimeout(() => {
                        this.isLoading = false;
                    }, 1000);
                } catch (error) {
                    console.error('Error exporting PDF:', error);
                    this.isLoading = false;
                    alert('Error generating PDF. Please try again.');
                }
            }
        };
    };
});
