/**
 * Dashboard Gastos - ApexCharts
 */

// Line Chart - Ingresos vs Gastos
var lineChartOptions = {
    series: [{
        name: 'Ingresos',
        data: [6500, 7200, 6800, 7500, 8200, 8500]
    }, {
        name: 'Gastos',
        data: [3200, 3800, 3500, 4200, 3800, 3200]
    }],
    chart: {
        height: 350,
        type: 'line',
        toolbar: {
            show: false
        }
    },
    colors: ['#0ab39c', '#f06548'],
    stroke: {
        curve: 'smooth',
        width: 3
    },
    xaxis: {
        categories: ['Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov']
    },
    yaxis: {
        labels: {
            formatter: function (val) {
                return '$' + val.toLocaleString();
            }
        }
    },
    grid: {
        borderColor: '#f1f1f1',
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right'
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return '$' + val.toLocaleString();
            }
        }
    }
};

var lineChart = new ApexCharts(document.querySelector("#line_chart_basic"), lineChartOptions);
lineChart.render();

// Donut Chart - Gastos por Categoría
var donutChartOptions = {
    series: [35, 25, 20, 12, 8],
    chart: {
        type: 'donut',
        height: 300
    },
    labels: ['Alimentación', 'Transporte', 'Vivienda', 'Entretenimiento', 'Otros'],
    colors: ['#405189', '#0ab39c', '#f7b84b', '#f06548', '#299cdb'],
    legend: {
        position: 'bottom',
        horizontalAlign: 'center'
    },
    plotOptions: {
        pie: {
            donut: {
                size: '70%'
            }
        }
    },
    dataLabels: {
        enabled: false
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return val + '%';
            }
        }
    }
};

var donutChart = new ApexCharts(document.querySelector("#donut_chart"), donutChartOptions);
donutChart.render();
