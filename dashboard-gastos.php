<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();

$user_id = getCurrentUserId();

// Detectar tipo de base de datos para compatibilidad
$isSqlite = isset($link->type) && $link->type === 'sqlite';
$isPostgres = isset($link->type) && $link->type === 'postgresql';
$isMysql = isset($link->type) && $link->type === 'mysql';

// Condición para activa según el tipo de BD
$activaCondition = $isPostgres ? 'activa = TRUE' : 'activa = 1';

// Fecha inicio del mes actual (compatible con todas las BD)
$fecha_inicio_mes = date('Y-m-01');

// 1. Balance Total (Suma de balances de todas las cuentas activas)
$sql_balance = "SELECT COALESCE(SUM(balance_actual), 0) as balance_total 
               FROM cuentas_bancarias 
               WHERE usuario_id = ? AND " . $activaCondition;

// Use PDO directly if available, otherwise use mysqli compatibility functions
if (isset($link->pdo)) {
    $stmt_balance = $link->pdo->prepare($sql_balance);
    $stmt_balance->execute([$user_id]);
    $balance_data = $stmt_balance->fetch(PDO::FETCH_ASSOC);
    $balance_total = floatval($balance_data['balance_total'] ?? 0);
} else {
    $stmt_balance = mysqli_prepare($link, $sql_balance);
    mysqli_stmt_bind_param($stmt_balance, "i", $user_id);
    mysqli_stmt_execute($stmt_balance);
    $result_balance = mysqli_stmt_get_result($stmt_balance);
    $balance_data = mysqli_fetch_assoc($result_balance);
    $balance_total = floatval($balance_data['balance_total'] ?? 0);
    mysqli_stmt_close($stmt_balance);
}

// 2. Ingresos del mes actual (compatible con todas las BD)
$sql_ingresos = "SELECT COALESCE(SUM(monto), 0) as total_ingresos 
                FROM transacciones 
                WHERE usuario_id = ? 
                AND tipo = 'ingreso' 
                AND fecha >= ?
                AND " . $activaCondition;

if (isset($link->pdo)) {
    $stmt_ingresos = $link->pdo->prepare($sql_ingresos);
    $stmt_ingresos->execute([$user_id, $fecha_inicio_mes]);
    $ingresos_data = $stmt_ingresos->fetch(PDO::FETCH_ASSOC);
    $total_ingresos = floatval($ingresos_data['total_ingresos'] ?? 0);
} else {
    $stmt_ingresos = mysqli_prepare($link, $sql_ingresos);
    mysqli_stmt_bind_param($stmt_ingresos, "is", $user_id, $fecha_inicio_mes);
    mysqli_stmt_execute($stmt_ingresos);
    $result_ingresos = mysqli_stmt_get_result($stmt_ingresos);
    $ingresos_data = mysqli_fetch_assoc($result_ingresos);
    $total_ingresos = floatval($ingresos_data['total_ingresos'] ?? 0);
    mysqli_stmt_close($stmt_ingresos);
}

// 3. Gastos del mes actual (compatible con todas las BD)
$sql_gastos = "SELECT COALESCE(SUM(monto), 0) as total_gastos 
              FROM transacciones 
              WHERE usuario_id = ? 
              AND tipo = 'gasto' 
              AND fecha >= ?
              AND " . $activaCondition;

if (isset($link->pdo)) {
    $stmt_gastos = $link->pdo->prepare($sql_gastos);
    $stmt_gastos->execute([$user_id, $fecha_inicio_mes]);
    $gastos_data = $stmt_gastos->fetch(PDO::FETCH_ASSOC);
    $total_gastos = floatval($gastos_data['total_gastos'] ?? 0);
} else {
    $stmt_gastos = mysqli_prepare($link, $sql_gastos);
    mysqli_stmt_bind_param($stmt_gastos, "is", $user_id, $fecha_inicio_mes);
    mysqli_stmt_execute($stmt_gastos);
    $result_gastos = mysqli_stmt_get_result($stmt_gastos);
    $gastos_data = mysqli_fetch_assoc($result_gastos);
    $total_gastos = floatval($gastos_data['total_gastos'] ?? 0);
    mysqli_stmt_close($stmt_gastos);
}

// 4. Ahorro del mes (Ingresos - Gastos)
$ahorro_mes = $total_ingresos - $total_gastos;

// 5. Ingresos y gastos de los últimos 6 meses para la gráfica
$six_months_ago = date('Y-m-01', strtotime('-5 months')); // 6 meses incluyendo el actual

// Función para agrupar por mes según el tipo de BD
if ($isSqlite) {
    $groupExpr = "strftime('%Y-%m', fecha)";
} elseif ($isPostgres) {
    $groupExpr = "to_char(fecha, 'YYYY-MM')";
} else {
    $groupExpr = "DATE_FORMAT(fecha, '%Y-%m')";
}

$sql_ingresos_meses = "SELECT " . $groupExpr . " as mes, COALESCE(SUM(monto), 0) as total
                      FROM transacciones 
                      WHERE usuario_id = ? 
                      AND tipo = 'ingreso' 
                      AND fecha >= ?
                      AND " . $activaCondition . "
                      GROUP BY mes
                      ORDER BY mes";

if (isset($link->pdo)) {
    $stmt_ingresos_meses = $link->pdo->prepare($sql_ingresos_meses);
    $stmt_ingresos_meses->execute([$user_id, $six_months_ago]);
    $ingresos_por_mes = [];
    while ($row = $stmt_ingresos_meses->fetch(PDO::FETCH_ASSOC)) {
        $ingresos_por_mes[$row['mes']] = floatval($row['total']);
    }
} else {
    $stmt_ingresos_meses = mysqli_prepare($link, $sql_ingresos_meses);
    mysqli_stmt_bind_param($stmt_ingresos_meses, "is", $user_id, $six_months_ago);
    mysqli_stmt_execute($stmt_ingresos_meses);
    $result_ingresos_meses = mysqli_stmt_get_result($stmt_ingresos_meses);
    $ingresos_por_mes = [];
    while ($row = mysqli_fetch_assoc($result_ingresos_meses)) {
        $ingresos_por_mes[$row['mes']] = floatval($row['total']);
    }
    mysqli_stmt_close($stmt_ingresos_meses);
}

$sql_gastos_meses = "SELECT " . $groupExpr . " as mes, COALESCE(SUM(monto), 0) as total
                    FROM transacciones 
                    WHERE usuario_id = ? 
                    AND tipo = 'gasto' 
                    AND fecha >= ?
                    AND " . $activaCondition . "
                    GROUP BY mes
                    ORDER BY mes";

if (isset($link->pdo)) {
    $stmt_gastos_meses = $link->pdo->prepare($sql_gastos_meses);
    $stmt_gastos_meses->execute([$user_id, $six_months_ago]);
    $gastos_por_mes = [];
    while ($row = $stmt_gastos_meses->fetch(PDO::FETCH_ASSOC)) {
        $gastos_por_mes[$row['mes']] = floatval($row['total']);
    }
} else {
    $stmt_gastos_meses = mysqli_prepare($link, $sql_gastos_meses);
    mysqli_stmt_bind_param($stmt_gastos_meses, "is", $user_id, $six_months_ago);
    mysqli_stmt_execute($stmt_gastos_meses);
    $result_gastos_meses = mysqli_stmt_get_result($stmt_gastos_meses);
    $gastos_por_mes = [];
    while ($row = mysqli_fetch_assoc($result_gastos_meses)) {
        $gastos_por_mes[$row['mes']] = floatval($row['total']);
    }
    mysqli_stmt_close($stmt_gastos_meses);
}

// Generar array de los últimos 6 meses con datos
$meses_labels = [];
$ingresos_data_chart = [];
$gastos_data_chart = [];
$meses_nombres = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

for ($i = 5; $i >= 0; $i--) {
    $fecha_mes = date('Y-m-01', strtotime("-$i months"));
    $mes_key = date('Y-m', strtotime("-$i months"));
    $mes_nombre = $meses_nombres[date('n', strtotime("-$i months")) - 1];
    
    $meses_labels[] = $mes_nombre;
    $ingresos_data_chart[] = isset($ingresos_por_mes[$mes_key]) ? $ingresos_por_mes[$mes_key] : 0;
    $gastos_data_chart[] = isset($gastos_por_mes[$mes_key]) ? $gastos_por_mes[$mes_key] : 0;
}

// 6. Gastos por categoría para la gráfica de donut
$sql_gastos_categoria = "SELECT c.nombre, c.color, COALESCE(SUM(t.monto), 0) as total
                         FROM transacciones t
                         INNER JOIN categorias c ON t.categoria_id = c.id
                         WHERE t.usuario_id = ? 
                         AND t.tipo = 'gasto' 
                         AND t.fecha >= ?
                         AND t." . $activaCondition . "
                         AND c." . $activaCondition . "
                         GROUP BY c.id, c.nombre, c.color
                         ORDER BY total DESC
                         LIMIT 10";

if (isset($link->pdo)) {
    $stmt_gastos_categoria = $link->pdo->prepare($sql_gastos_categoria);
    $stmt_gastos_categoria->execute([$user_id, $six_months_ago]);
    $gastos_por_categoria = [];
    $total_gastos_categorias = 0;
    while ($row = $stmt_gastos_categoria->fetch(PDO::FETCH_ASSOC)) {
        $gastos_por_categoria[] = [
            'nombre' => $row['nombre'],
            'total' => floatval($row['total']),
            'color' => $row['color'] ?: '#405189'
        ];
        $total_gastos_categorias += floatval($row['total']);
    }
} else {
    $stmt_gastos_categoria = mysqli_prepare($link, $sql_gastos_categoria);
    mysqli_stmt_bind_param($stmt_gastos_categoria, "is", $user_id, $six_months_ago);
    mysqli_stmt_execute($stmt_gastos_categoria);
    $result_gastos_categoria = mysqli_stmt_get_result($stmt_gastos_categoria);
    $gastos_por_categoria = [];
    $total_gastos_categorias = 0;
    while ($row = mysqli_fetch_assoc($result_gastos_categoria)) {
        $gastos_por_categoria[] = [
            'nombre' => $row['nombre'],
            'total' => floatval($row['total']),
            'color' => $row['color'] ?: '#405189'
        ];
        $total_gastos_categorias += floatval($row['total']);
    }
    mysqli_stmt_close($stmt_gastos_categoria);
}

// Calcular porcentajes para la gráfica
$gastos_categoria_labels = [];
$gastos_categoria_data = [];
$gastos_categoria_colors = [];
foreach ($gastos_por_categoria as $cat) {
    $gastos_categoria_labels[] = $cat['nombre'];
    $porcentaje = $total_gastos_categorias > 0 ? ($cat['total'] / $total_gastos_categorias * 100) : 0;
    $gastos_categoria_data[] = round($porcentaje, 1);
    $gastos_categoria_colors[] = $cat['color'];
}

// 7. Transacciones recientes (últimas 5)
$sql_transacciones = "SELECT t.descripcion, t.monto, t.tipo, t.fecha, c.nombre as categoria_nombre
                     FROM transacciones t
                     LEFT JOIN categorias c ON t.categoria_id = c.id
                     WHERE t.usuario_id = ? 
                     AND t." . $activaCondition . "
                     ORDER BY t.fecha DESC, t.id DESC 
                     LIMIT 5";

if (isset($link->pdo)) {
    $stmt_transacciones = $link->pdo->prepare($sql_transacciones);
    $stmt_transacciones->execute([$user_id]);
    $transacciones = $stmt_transacciones->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt_transacciones = mysqli_prepare($link, $sql_transacciones);
    mysqli_stmt_bind_param($stmt_transacciones, "i", $user_id);
    mysqli_stmt_execute($stmt_transacciones);
    $result_transacciones = mysqli_stmt_get_result($stmt_transacciones);
    $transacciones = mysqli_fetch_all($result_transacciones, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_transacciones);
}

// Función para formatear moneda
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Dashboard | FIME - Gestión de Gastos Personales</title>
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
                                <h4 class="mb-sm-0">Dashboard - Gestión de Gastos</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">FIME</a></li>
                                        <li class="breadcrumb-item active">Dashboard</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <!-- Resumen Financiero -->
                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Balance Total</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-primary fs-14 mb-0">
                                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i> Balance
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?php echo formatCurrency($balance_total); ?></h4>
                                            <span class="badge bg-success-subtle text-primary mb-0">
                                                <i class="ri-money-dollar-circle-line align-middle"></i> Total
                                            </span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded fs-3">
                                                <i class="ri-money-dollar-circle-line text-primary"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Ingresos del Mes</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0">
                                                <i class="ri-arrow-up-line fs-13 align-middle"></i> Mes Actual
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?php echo formatCurrency($total_ingresos); ?></h4>
                                            <span class="badge bg-soft-success text-success mb-0">
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

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Gastos del Mes</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-danger fs-14 mb-0">
                                                <i class="ri-arrow-down-line fs-13 align-middle"></i> Mes Actual
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?php echo formatCurrency($total_gastos); ?></h4>
                                            <span class="badge bg-soft-success text-danger mb-0">
                                                <i class="ri-arrow-down-line align-middle"></i> Gastos
                                            </span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded fs-3">
                                                <i class="ri-arrow-down-line text-danger"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card card-animate">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Ahorro del Mes</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-success fs-14 mb-0">
                                                <i class="ri-arrow-up-line fs-13 align-middle"></i> <?php echo $ahorro_mes >= 0 ? '+' : ''; ?><?php echo formatCurrency($ahorro_mes); ?>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?php echo formatCurrency($ahorro_mes); ?></h4>
                                            <span class="badge bg-soft-success <?php echo $ahorro_mes >= 0 ? 'text-success' : 'text-danger'; ?> mb-0">
                                                <i class="ri-arrow-<?php echo $ahorro_mes >= 0 ? 'up' : 'down'; ?>-line align-middle"></i> <?php echo $ahorro_mes >= 0 ? 'Ahorro' : 'Déficit'; ?>
                                            </span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded fs-3">
                                                <i class="ri-wallet-3-line text-success"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Gráfico de Ingresos vs Gastos -->
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title mb-0 flex-grow-1">Ingresos vs Gastos - Últimos 6 Meses</h5>
                                        <div class="flex-shrink-0">
                                            <div class="dropdown card-header-dropdown">
                                                <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="text-muted fs-18"><i class="mdi mdi-dots-vertical"></i></span>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="#">Exportar</a>
                                                    <a class="dropdown-item" href="#">Imprimir</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="line_chart_basic" data-colors='["--vz-primary", "--vz-success", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Gastos por Categoría -->
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Gastos por Categoría</h5>
                                </div>
                                <div class="card-body">
                                    <div id="donut_chart" data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Transacciones Recientes -->
                        <div class="col-xl-8">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title mb-0 flex-grow-1">Transacciones Recientes</h5>
                                        <div class="flex-shrink-0">
                                            <a href="transacciones-lista.php" class="btn btn-primary btn-sm">Ver Todas</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-borderless table-nowrap align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Descripción</th>
                                                    <th scope="col">Categoría</th>
                                                    <th scope="col">Tipo</th>
                                                    <th scope="col">Monto</th>
                                                    <th scope="col">Fecha</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($transacciones)): ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">
                                                            <i class="ri-inbox-line fs-3 d-block mb-2"></i>
                                                            No hay transacciones recientes
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($transacciones as $trans): ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 me-2">
                                                                        <div class="avatar-xs">
                                                                            <span class="avatar-title <?php echo $trans['tipo'] === 'ingreso' ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger'; ?> rounded">
                                                                                <i class="ri-arrow-<?php echo $trans['tipo'] === 'ingreso' ? 'up' : 'down'; ?>-line"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="mb-0"><?php echo htmlspecialchars($trans['descripcion']); ?></h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($trans['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                                            <td>
                                                                <span class="badge <?php echo $trans['tipo'] === 'ingreso' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                                                    <?php echo ucfirst($trans['tipo']); ?>
                                                                </span>
                                                            </td>
                                                            <td class="<?php echo $trans['tipo'] === 'ingreso' ? 'text-success' : 'text-danger'; ?> fw-semibold">
                                                                <?php echo $trans['tipo'] === 'ingreso' ? '+' : '-'; ?><?php echo formatCurrency($trans['monto']); ?>
                                                            </td>
                                                            <td><?php echo date('d M Y', strtotime($trans['fecha'])); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones Rápidas -->
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Acciones Rápidas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="transacciones-agregar.php" class="btn btn-primary">
                                            <i class="ri-add-line align-middle me-1"></i> Nueva Transacción
                                        </a>
                                        <a href="cuentas-agregar.php" class="btn btn-outline-primary">
                                            <i class="ri-bank-line align-middle me-1"></i> Agregar Cuenta
                                        </a>
                                        <a href="presupuestos-agregar.php" class="btn btn-outline-success">
                                            <i class="ri-money-dollar-circle-line align-middle me-1"></i> Crear Presupuesto
                                        </a>
                                        <a href="reportes.php" class="btn btn-outline-info">
                                            <i class="ri-bar-chart-line align-middle me-1"></i> Ver Reportes
                                        </a>
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

    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <!-- apexcharts -->
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

    <!-- Dashboard data from PHP -->
    <script>
        // Pasar datos de PHP a JavaScript para las gráficas
        var dashboardData = {
            meses: <?php echo json_encode($meses_labels); ?>,
            ingresos: <?php echo json_encode($ingresos_data_chart); ?>,
            gastos: <?php echo json_encode($gastos_data_chart); ?>,
            gastosCategoria: {
                labels: <?php echo json_encode($gastos_categoria_labels); ?>,
                data: <?php echo json_encode($gastos_categoria_data); ?>,
                colors: <?php echo json_encode($gastos_categoria_colors); ?>
            }
        };
    </script>

    <!-- Dashboard init -->
    <script src="assets/js/pages/dashboard-gastos.init.js"></script>

</body>

</html>
