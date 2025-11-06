<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();

// Get user ID
$user_id = getCurrentUserId();

// Get date range from URL parameters or default to current month
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');

// Get monthly income and expenses for the last 6 months (portable for SQLite/MySQL/Postgres)
$isSqlite = isset($link->type) && $link->type === 'sqlite';
$isPostgres = defined('DB_TYPE') && DB_TYPE === 'postgresql';
$six_months_ago = date('Y-m-d', strtotime($fecha_fin . ' -6 months'));
if ($isSqlite) {
    $groupExpr = "strftime('%Y-%m', fecha)";
} elseif ($isPostgres) {
    // PostgreSQL: use to_char to format date to year-month
    $groupExpr = "to_char(fecha, 'YYYY-MM')";
} else {
    // MySQL (default)
    $groupExpr = "DATE_FORMAT(fecha, '%Y-%m')";
}

$sql_income = "SELECT " . $groupExpr . " as mes, SUM(monto) as total
               FROM transacciones 
               WHERE usuario_id = ? AND tipo = 'ingreso' 
               AND fecha >= ?
               GROUP BY mes
               ORDER BY mes";
$stmt = mysqli_prepare($link, $sql_income);
mysqli_stmt_bind_param($stmt, "is", $user_id, $six_months_ago);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$monthly_income = [];
while ($row = mysqli_fetch_assoc($result)) {
    $monthly_income[$row['mes']] = $row['total'];
}
mysqli_stmt_close($stmt);

$sql_expenses = "SELECT " . $groupExpr . " as mes, SUM(monto) as total
                 FROM transacciones 
                 WHERE usuario_id = ? AND tipo = 'gasto' 
                 AND fecha >= ?
                 GROUP BY mes
                 ORDER BY mes";
$stmt = mysqli_prepare($link, $sql_expenses);
mysqli_stmt_bind_param($stmt, "is", $user_id, $six_months_ago);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$monthly_expenses = [];
while ($row = mysqli_fetch_assoc($result)) {
    $monthly_expenses[$row['mes']] = $row['total'];
}
mysqli_stmt_close($stmt);

// Get expenses by category for the selected period
$sql_categories = "SELECT c.nombre, c.color, SUM(t.monto) as total
                   FROM transacciones t
                   JOIN categorias c ON t.categoria_id = c.id
                   WHERE t.usuario_id = ? AND t.tipo = 'gasto' 
                   AND t.fecha BETWEEN ? AND ?
                   GROUP BY c.id, c.nombre, c.color
                   ORDER BY total DESC";
$stmt = mysqli_prepare($link, $sql_categories);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $fecha_inicio, $fecha_fin);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$expenses_by_category = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Get total income and expenses for the selected period
$sql_totals = "SELECT 
               SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END) as total_ingresos,
               SUM(CASE WHEN tipo = 'gasto' THEN monto ELSE 0 END) as total_gastos
               FROM transacciones 
               WHERE usuario_id = ? AND fecha BETWEEN ? AND ?";
$stmt = mysqli_prepare($link, $sql_totals);
mysqli_stmt_bind_param($stmt, "iss", $user_id, $fecha_inicio, $fecha_fin);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$totals = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$total_ingresos = $totals['total_ingresos'] ?? 0;
$total_gastos = $totals['total_gastos'] ?? 0;
$balance_neto = $total_ingresos - $total_gastos;

// Prepare data for charts
$chart_labels = [];
$chart_income = [];
$chart_expenses = [];

for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    
    $chart_labels[] = $month_name;
    $chart_income[] = $monthly_income[$month] ?? 0;
    $chart_expenses[] = $monthly_expenses[$month] ?? 0;
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Reportes | FIME - Gestión de Gastos Personales</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include 'layouts/topbar.php'; ?>
        <?php include 'layouts/sidebar-gastos.php'; ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Reportes y Análisis</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard-gastos.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Reportes</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <!-- Filtros de Fecha -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="GET" action="">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">&nbsp;</label>
                                                <div>
                                                    <button type="submit" class="btn btn-primary me-2">Actualizar</button>
                                                    <a href="reportes.php" class="btn btn-outline-secondary">Resetear</a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen del Período -->
                    <div class="row">
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Ingresos</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0">
                                                <i class="ri-arrow-up-line fs-13 align-middle"></i> +$<?php echo number_format($total_ingresos, 2); ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Gastos</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-danger fs-14 mb-0">
                                                <i class="ri-arrow-down-line fs-13 align-middle"></i> -$<?php echo number_format($total_gastos, 2); ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Balance Neto</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="<?php echo $balance_neto >= 0 ? 'text-success' : 'text-danger'; ?> fs-14 mb-0">
                                                <i class="ri-<?php echo $balance_neto >= 0 ? 'arrow-up' : 'arrow-down'; ?>-line fs-13 align-middle"></i> $<?php echo number_format($balance_neto, 2); ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Evolución de Ingresos vs Gastos (Últimos 6 Meses)</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="incomeExpenseChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Gastos por Categoría</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="categoryChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Gastos por Categoría -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Detalle de Gastos por Categoría</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-borderless table-nowrap align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Categoría</th>
                                                    <th scope="col">Monto</th>
                                                    <th scope="col">Porcentaje</th>
                                                    <th scope="col">Tendencia</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($expenses_by_category)): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center py-4">
                                                            <div class="text-muted">
                                                                <i class="ri-pie-chart-line fs-48 text-muted mb-3"></i>
                                                                <p>No hay gastos en el período seleccionado</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($expenses_by_category as $category): ?>
                                                        <?php
                                                        $percentage = $total_gastos > 0 ? ($category['total'] / $total_gastos) * 100 : 0;
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 me-2">
                                                                        <div class="avatar-xs">
                                                                            <span class="avatar-title rounded" style="background-color: <?php echo $category['color']; ?>20; color: <?php echo $category['color']; ?>">
                                                                                <i class="ri-folder-line"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="mb-0"><?php echo htmlspecialchars($category['nombre']); ?></h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="fw-semibold">$<?php echo number_format($category['total'], 2); ?></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-grow-1 me-2">
                                                                        <div class="progress progress-sm">
                                                                            <div class="progress-bar" style="width: <?php echo $percentage; ?>%; background-color: <?php echo $category['color']; ?>"></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-shrink-0">
                                                                        <span class="text-muted"><?php echo number_format($percentage, 1); ?>%</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-success-subtle text-success">
                                                                    <i class="ri-arrow-up-line align-middle"></i> Estable
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?php include 'layouts/footer.php'; ?>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <?php include 'layouts/vendor-scripts.php'; ?>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Income vs Expenses Chart
        const incomeExpenseCtx = document.getElementById('incomeExpenseChart').getContext('2d');
        new Chart(incomeExpenseCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Ingresos',
                    data: <?php echo json_encode($chart_income); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Gastos',
                    data: <?php echo json_encode($chart_expenses); ?>,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($expenses_by_category, 'nombre')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($expenses_by_category, 'total')); ?>,
                    backgroundColor: <?php echo json_encode(array_column($expenses_by_category, 'color')); ?>,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': $' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    </script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>

</html>
