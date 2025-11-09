/**
 * Dashboard Gastos - ApexCharts
 */

// Line Chart - Ingresos vs Gastos
// Usar datos reales de dashboardData si están disponibles, sino usar datos por defecto
var ingresosData = (typeof dashboardData !== 'undefined' && dashboardData.ingresos) ? dashboardData.ingresos : [0, 0, 0, 0, 0, 0];
var gastosData = (typeof dashboardData !== 'undefined' && dashboardData.gastos) ? dashboardData.gastos : [0, 0, 0, 0, 0, 0];
var mesesData = (typeof dashboardData !== 'undefined' && dashboardData.meses) ? dashboardData.meses : ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'];

var lineChartOptions = {
    series: [{
        name: 'Ingresos',
        data: ingresosData
    }, {
        name: 'Gastos',
        data: gastosData
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
        categories: mesesData
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
// Usar datos reales de dashboardData si están disponibles, sino usar datos por defecto
var categoriaLabels = (typeof dashboardData !== 'undefined' && dashboardData.gastosCategoria && dashboardData.gastosCategoria.labels) 
    ? dashboardData.gastosCategoria.labels 
    : ['Sin datos'];
var categoriaData = (typeof dashboardData !== 'undefined' && dashboardData.gastosCategoria && dashboardData.gastosCategoria.data) 
    ? dashboardData.gastosCategoria.data 
    : [100];
var categoriaColors = (typeof dashboardData !== 'undefined' && dashboardData.gastosCategoria && dashboardData.gastosCategoria.colors) 
    ? dashboardData.gastosCategoria.colors 
    : ['#405189', '#0ab39c', '#f7b84b', '#f06548', '#299cdb'];

// Si no hay datos, usar colores por defecto
if (categoriaColors.length < categoriaData.length) {
    var defaultColors = ['#405189', '#0ab39c', '#f7b84b', '#f06548', '#299cdb', '#51d28c', '#f7b84b', '#f06548', '#299cdb', '#e83e8c'];
    for (var i = categoriaColors.length; i < categoriaData.length; i++) {
        categoriaColors.push(defaultColors[i % defaultColors.length]);
    }
}

var donutChartOptions = {
    series: categoriaData,
    chart: {
        type: 'donut',
        height: 300
    },
    labels: categoriaLabels,
    colors: categoriaColors,
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
