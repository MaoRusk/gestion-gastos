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
$isPostgres = isset($link->type) && $link->type === 'postgresql';
$six_months_ago = date('Y-m-d', strtotime($fecha_fin . ' -6 months'));
$activeCondition = $isPostgres ? 't.activa = TRUE' : 't.activa = 1';

if ($isSqlite) {
    $groupExpr = "strftime('%Y-%m', t.fecha)";
} elseif ($isPostgres) {
    // PostgreSQL: use to_char to format date to year-month
    $groupExpr = "to_char(t.fecha, 'YYYY-MM')";
} else {
    // MySQL (default)
    $groupExpr = "DATE_FORMAT(t.fecha, '%Y-%m')";
}

$sql_income = "SELECT " . $groupExpr . " as mes, COALESCE(SUM(t.monto), 0) as total
               FROM transacciones t
               WHERE t.usuario_id = ? AND t.tipo = 'ingreso' 
               AND t.fecha >= ? AND " . $activeCondition . "
               GROUP BY mes
               ORDER BY mes";
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare($sql_income);
    $stmt->execute([$user_id, $six_months_ago]);
    $monthly_income = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $monthly_income[$row['mes']] = floatval($row['total']);
    }
} else {
    $stmt = mysqli_prepare($link, $sql_income);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $six_months_ago);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $monthly_income = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $monthly_income[$row['mes']] = floatval($row['total']);
    }
    mysqli_stmt_close($stmt);
}

$sql_expenses = "SELECT " . $groupExpr . " as mes, COALESCE(SUM(t.monto), 0) as total
                 FROM transacciones t
                 WHERE t.usuario_id = ? AND t.tipo = 'gasto' 
                 AND t.fecha >= ? AND " . $activeCondition . "
                 GROUP BY mes
                 ORDER BY mes";
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare($sql_expenses);
    $stmt->execute([$user_id, $six_months_ago]);
    $monthly_expenses = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $monthly_expenses[$row['mes']] = floatval($row['total']);
    }
} else {
    $stmt = mysqli_prepare($link, $sql_expenses);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $six_months_ago);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $monthly_expenses = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $monthly_expenses[$row['mes']] = floatval($row['total']);
    }
    mysqli_stmt_close($stmt);
}

// Get expenses by category for the selected period
$activeCondition2 = $isPostgres ? 't.activa = TRUE' : 't.activa = 1';
$sql_categories = "SELECT c.nombre, c.color, COALESCE(SUM(t.monto), 0) as total
                   FROM transacciones t
                   JOIN categorias c ON t.categoria_id = c.id
                   WHERE t.usuario_id = ? AND t.tipo = 'gasto' 
                   AND t.fecha BETWEEN ? AND ? AND " . $activeCondition2 . "
                   GROUP BY c.id, c.nombre, c.color
                   ORDER BY total DESC";
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare($sql_categories);
    $stmt->execute([$user_id, $fecha_inicio, $fecha_fin]);
    $expenses_by_category = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, $sql_categories);
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $fecha_inicio, $fecha_fin);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $expenses_by_category = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Ensure all totals are floats
foreach ($expenses_by_category as &$category) {
    $category['total'] = floatval($category['total']);
}
unset($category);

// Get total income and expenses for the selected period
// Filter only active transactions - use separate queries for better reliability

// Get total income
if ($isPostgres) {
    $sql_income_total = "SELECT COALESCE(SUM(t.monto), 0) as total
                         FROM transacciones t
                         WHERE t.usuario_id = ? AND t.tipo = 'ingreso' 
                         AND t.fecha BETWEEN ? AND ? AND (t.activa = TRUE OR t.activa IS NULL)";
} else {
    $sql_income_total = "SELECT COALESCE(SUM(t.monto), 0) as total
                         FROM transacciones t
                         WHERE t.usuario_id = ? AND t.tipo = 'ingreso' 
                         AND t.fecha BETWEEN ? AND ? AND (t.activa = 1 OR t.activa IS NULL)";
}
if (isset($link->pdo)) {
    $stmt_income = $link->pdo->prepare($sql_income_total);
    $stmt_income->execute([$user_id, $fecha_inicio, $fecha_fin]);
    $row_income = $stmt_income->fetch(PDO::FETCH_ASSOC);
    $total_ingresos = floatval($row_income['total'] ?? 0);
} else {
    $stmt_income = mysqli_prepare($link, $sql_income_total);
    if ($stmt_income) {
        mysqli_stmt_bind_param($stmt_income, "iss", $user_id, $fecha_inicio, $fecha_fin);
        mysqli_stmt_execute($stmt_income);
        $result_income = mysqli_stmt_get_result($stmt_income);
        $row_income = mysqli_fetch_assoc($result_income);
        $total_ingresos = floatval($row_income['total'] ?? 0);
        mysqli_stmt_close($stmt_income);
    } else {
        $total_ingresos = 0;
    }
}

// Get total expenses
// Try with activa filter first, fallback without if needed
if ($isPostgres) {
    $sql_expenses_total = "SELECT COALESCE(SUM(t.monto), 0) as total
                           FROM transacciones t
                           WHERE t.usuario_id = ? AND t.tipo = 'gasto' 
                           AND t.fecha BETWEEN ? AND ? AND (t.activa = TRUE OR t.activa IS NULL)";
} else {
    $sql_expenses_total = "SELECT COALESCE(SUM(t.monto), 0) as total
                           FROM transacciones t
                           WHERE t.usuario_id = ? AND t.tipo = 'gasto' 
                           AND t.fecha BETWEEN ? AND ? AND (t.activa = 1 OR t.activa IS NULL)";
}

if (isset($link->pdo)) {
    $stmt_expenses = $link->pdo->prepare($sql_expenses_total);
    $stmt_expenses->execute([$user_id, $fecha_inicio, $fecha_fin]);
    $row_expenses = $stmt_expenses->fetch(PDO::FETCH_ASSOC);
    $total_gastos = floatval($row_expenses['total'] ?? 0);
    
    // If result is 0, try without activa filter as fallback
    if ($total_gastos == 0) {
        $sql_expenses_fallback = "SELECT COALESCE(SUM(t.monto), 0) as total
                                  FROM transacciones t
                                  WHERE t.usuario_id = ? AND t.tipo = 'gasto' 
                                  AND t.fecha BETWEEN ? AND ?";
        $stmt_fallback = $link->pdo->prepare($sql_expenses_fallback);
        $stmt_fallback->execute([$user_id, $fecha_inicio, $fecha_fin]);
        $row_fallback = $stmt_fallback->fetch(PDO::FETCH_ASSOC);
        $total_gastos = floatval($row_fallback['total'] ?? 0);
    }
} else {
    $stmt_expenses = mysqli_prepare($link, $sql_expenses_total);
    if ($stmt_expenses) {
        mysqli_stmt_bind_param($stmt_expenses, "iss", $user_id, $fecha_inicio, $fecha_fin);
        mysqli_stmt_execute($stmt_expenses);
        $result_expenses = mysqli_stmt_get_result($stmt_expenses);
        $row_expenses = mysqli_fetch_assoc($result_expenses);
        $total_gastos = floatval($row_expenses['total'] ?? 0);
        mysqli_stmt_close($stmt_expenses);
        
        // If result is 0, try without activa filter as fallback
        if ($total_gastos == 0) {
            $sql_expenses_fallback = "SELECT COALESCE(SUM(t.monto), 0) as total
                                      FROM transacciones t
                                      WHERE t.usuario_id = ? AND t.tipo = 'gasto' 
                                      AND t.fecha BETWEEN ? AND ?";
            $stmt_fallback = mysqli_prepare($link, $sql_expenses_fallback);
            if ($stmt_fallback) {
                mysqli_stmt_bind_param($stmt_fallback, "iss", $user_id, $fecha_inicio, $fecha_fin);
                mysqli_stmt_execute($stmt_fallback);
                $result_fallback = mysqli_stmt_get_result($stmt_fallback);
                $row_fallback = mysqli_fetch_assoc($result_fallback);
                $total_gastos = floatval($row_fallback['total'] ?? 0);
                mysqli_stmt_close($stmt_fallback);
            }
        }
    } else {
        $total_gastos = 0;
    }
}

// exit;
$balance_neto = $total_ingresos - $total_gastos;

// Prepare data for charts
$chart_labels = [];
$chart_income = [];
$chart_expenses = [];

for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    
    $chart_labels[] = $month_name;
    $chart_income[] = floatval($monthly_income[$month] ?? 0);
    $chart_expenses[] = floatval($monthly_expenses[$month] ?? 0);
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
                                                    <a href="reportes.php" class="btn btn-outline-secondary">Limpiar</a>
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
                                                <i class="ri-arrow-up-line fs-13 align-middle"></i> $<?php echo number_format($total_ingresos, 2); ?>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">$<?php echo number_format($total_ingresos, 2); ?></h4>
                                            <span class="badge bg-success-subtle text-success mb-0">
                                                <i class="ri-arrow-up-line align-middle"></i> Ingresos
                                            </span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded fs-3">
                                                <i class="ri-arrow-up-line text-success"></i>
                                            </span>
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
                                                <i class="ri-arrow-down-line fs-13 align-middle"></i> $<?php echo number_format($total_gastos, 2); ?>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">$<?php echo number_format($total_gastos, 2); ?></h4>
                                            <span class="badge bg-danger-subtle text-danger mb-0">
                                                <i class="ri-arrow-down-line align-middle"></i> Gastos
                                            </span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-danger rounded fs-3">
                                                <i class="ri-arrow-down-line text-danger"></i>
                                            </span>
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
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">$<?php echo number_format($balance_neto, 2); ?></h4>
                                            <span class="badge <?php echo $balance_neto >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> mb-0">
                                                <i class="ri-<?php echo $balance_neto >= 0 ? 'arrow-up' : 'arrow-down'; ?>-line align-middle"></i> <?php echo $balance_neto >= 0 ? 'Positivo' : 'Negativo'; ?>
                                            </span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-<?php echo $balance_neto >= 0 ? 'success' : 'danger'; ?> rounded fs-3">
                                                <i class="ri-<?php echo $balance_neto >= 0 ? 'arrow-up' : 'arrow-down'; ?>-line text-<?php echo $balance_neto >= 0 ? 'success' : 'danger'; ?>"></i>
                                            </span>
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
                                return context.dataset.label + ': $' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart');
        <?php if (!empty($expenses_by_category)): ?>
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($expenses_by_category, 'nombre')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map('floatval', array_column($expenses_by_category, 'total'))); ?>,
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
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return context.label + ': $' + context.parsed.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        <?php else: ?>
        // No data to display
        categoryCtx.getContext('2d').fillText('No hay datos disponibles', 10, 10);
        <?php endif; ?>
    </script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>

</html>
