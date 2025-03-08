/*
(function () {
    "use strict";

    // Chart
    const chartEl = $(".report-donut-chart-5");

    if (chartEl.length) {
        chartEl.each(function () {
            const ctx = $(this)[0].getContext("2d");
            const chartData = [35, 65];

            const reportDonutChart5 = new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: [
                        "31 - 50 Years old",
                        ">= 50 Years old",
                        "17 - 30 Years old",
                    ],
                    datasets: [
                        {
                            data: chartData,
                            backgroundColor: [
                                getColor("primary", 0.6),
                                getColor("success", 0.6),
                                getColor("danger", 0.6),
                            ],
                            hoverBackgroundColor: [
                                getColor("primary", 0.6),
                                getColor("success", 0.6),
                                getColor("danger", 0.6),
                            ],
                            borderWidth: 1,
                            borderColor: [
                                getColor("primary", 0.6),
                                getColor("success", 0.6),
                                getColor("danger", 0.6),
                            ],
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    cutout: "90%",
                },
            });

            // Watch CSS variable color changes
            helper.watchCssVariables(
                "html",
                ["color-primary", "color-success", "color-danger"],
                (newValues) => {
                    reportDonutChart5.data.datasets[0].borderColor[0] =
                        getColor("primary", 0.6);
                    reportDonutChart5.data.datasets[0].hoverBackgroundColor[0] =
                        getColor("primary", 0.6);
                    reportDonutChart5.data.datasets[0].backgroundColor[0] =
                        getColor("primary", 0.6);

                    reportDonutChart5.data.datasets[0].borderColor[1] =
                        getColor("success", 0.6);
                    reportDonutChart5.data.datasets[0].hoverBackgroundColor[1] =
                        getColor("success", 0.6);
                    reportDonutChart5.data.datasets[0].backgroundColor[1] =
                        getColor("success", 0.6);

                    reportDonutChart5.data.datasets[0].borderColor[1] =
                        getColor("danger", 0.6);
                    reportDonutChart5.data.datasets[0].hoverBackgroundColor[1] =
                        getColor("danger", 0.6);
                    reportDonutChart5.data.datasets[0].backgroundColor[1] =
                        getColor("danger", 0.6);

                    reportDonutChart5.update();
                }
            );
        });
    }
})();
*/
// En resources/js/components/report-donut-chart-5.js
import Chart from "chart.js/auto";

const initDonutChart = () => {
    // Obtener los datos del backend (puedes pasarlos como data attributes)
    const chartData = JSON.parse(document.querySelector('.report-donut-chart-5').dataset.values);
    
    const el = document.querySelector('.report-donut-chart-5');
    if (el) {
        const ctx = el.getContext('2d');
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Pending', 'Inactive'],
                datasets: [{
                    data: chartData,
                    backgroundColor: [
                        'rgb(110, 191, 184)',
                        'rgb(234, 179, 8)',
                        'rgb(213, 119, 119)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '80%'
            }
        });
    }
};

// Inicializar la gráfica
document.addEventListener("DOMContentLoaded", () => {
    initDonutChart();
});