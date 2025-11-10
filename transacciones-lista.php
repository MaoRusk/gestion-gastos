<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();

// Get user's transactions with filters
$user_id = getCurrentUserId();

// Detectar tipo de base de datos para compatibilidad
$isSqlite = isset($link->type) && $link->type === 'sqlite';
$isPostgres = isset($link->type) && $link->type === 'postgresql';
$isMysql = isset($link->type) && $link->type === 'mysql';

// Condición para activa según el tipo de BD
$activaCondition = $isPostgres ? 'activa = TRUE' : 'activa = 1';
$predefCondition = $isPostgres ? 'es_predefinida = TRUE' : 'es_predefinida = 1';

// Build filter conditions
$where_conditions = ["t.usuario_id = ?"];
$params = [$user_id];

// Filter by type
if (!empty($_GET['tipo'])) {
    $where_conditions[] = "t.tipo = ?";
    $params[] = $_GET['tipo'];
}

// Filter by category
if (!empty($_GET['categoria_id'])) {
    $where_conditions[] = "t.categoria_id = ?";
    $params[] = intval($_GET['categoria_id']);
}

// Filter by date
if (!empty($_GET['fecha'])) {
    $where_conditions[] = "t.fecha = ?";
    $params[] = $_GET['fecha'];
}

// Filter by search term
if (!empty($_GET['buscar'])) {
    $where_conditions[] = "t.descripcion LIKE ?";
    $params[] = "%" . $_GET['buscar'] . "%";
}

$where_clause = implode(" AND ", $where_conditions);

// Get transactions with related data
$sql = "SELECT t.*, c.nombre as categoria_nombre, c.color as categoria_color, c.icono as categoria_icono, 
               cb.nombre as cuenta_nombre, cb.tipo as cuenta_tipo
        FROM transacciones t
        LEFT JOIN categorias c ON t.categoria_id = c.id
        LEFT JOIN cuentas_bancarias cb ON t.cuenta_id = cb.id
        WHERE $where_clause
        ORDER BY t.fecha DESC, t.fecha_creacion DESC
        LIMIT 50";

if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare($sql);
    $stmt->execute($params);
    $transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, $sql);
    if (!empty($params)) {
        $param_types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $transacciones = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Get categories for filter
$sql_categories = "SELECT id, nombre, tipo FROM categorias c WHERE (usuario_id = ? OR " . $predefCondition . ") AND " . $activaCondition . " ORDER BY tipo, nombre";
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare($sql_categories);
    $stmt->execute([$user_id]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, $sql_categories);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $categorias = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Calculate totals
$total_ingresos = 0;
$total_gastos = 0;
foreach ($transacciones as $transaccion) {
    if ($transaccion['tipo'] == 'ingreso') {
        $total_ingresos += $transaccion['monto'];
    } elseif ($transaccion['tipo'] == 'gasto') {
        $total_gastos += $transaccion['monto'];
    }
}
$balance_neto = $total_ingresos - $total_gastos;
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Transacciones | FIME - Gestión de Gastos Personales</title>
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
                                <h4 class="mb-sm-0">Todas las Transacciones</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard-gastos.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Transacciones</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <!-- Resumen de Transacciones -->
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

                    <!-- Filtros -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="GET" action="">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label for="tipo" class="form-label">Tipo</label>
                                                <select class="form-select" id="tipo" name="tipo">
                                                    <option value="">Todos</option>
                                                    <option value="ingreso" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'ingreso') ? 'selected' : ''; ?>>Ingresos</option>
                                                    <option value="gasto" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'gasto') ? 'selected' : ''; ?>>Gastos</option>
                                                    <option value="transferencia" <?php echo (isset($_GET['tipo']) && $_GET['tipo'] == 'transferencia') ? 'selected' : ''; ?>>Transferencias</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="categoria_id" class="form-label">Categoría</label>
                                                <select class="form-select" id="categoria_id" name="categoria_id">
                                                    <option value="">Todas</option>
                                                    <?php foreach ($categorias as $categoria): ?>
                                                        <option value="<?php echo $categoria['id']; ?>" <?php echo (isset($_GET['categoria_id']) && $_GET['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="fecha" class="form-label">Fecha</label>
                                                <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo isset($_GET['fecha']) ? $_GET['fecha'] : ''; ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label for="buscar" class="form-label">Buscar</label>
                                                <input type="text" class="form-control" id="buscar" name="buscar" placeholder="Descripción..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                                                <a href="transacciones-lista.php" class="btn btn-outline-secondary">Limpiar</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title mb-0 flex-grow-1">Lista de Transacciones</h5>
                                        <div class="flex-shrink-0">
                                            <a href="transacciones-agregar.php" class="btn btn-primary">
                                                <i class="ri-add-line align-middle me-1"></i> Nueva Transacción
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-borderless table-nowrap align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Fecha</th>
                                                    <th scope="col">Descripción</th>
                                                    <th scope="col">Categoría</th>
                                                    <th scope="col">Cuenta</th>
                                                    <th scope="col">Tipo</th>
                                                    <th scope="col">Monto</th>
                                                    <th scope="col">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($transacciones)): ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center py-4">
                                                            <div class="text-muted">
                                                                <i class="ri-exchange-line fs-48 text-muted mb-3"></i>
                                                                <p>No hay transacciones registradas</p>
                                                                <a href="transacciones-agregar.php" class="btn btn-primary btn-sm">Agregar Primera Transacción</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($transacciones as $transaccion): ?>
                                                        <?php
                                                        $tipo_labels = [
                                                            'ingreso' => 'Ingreso',
                                                            'gasto' => 'Gasto',
                                                            'transferencia' => 'Transferencia'
                                                        ];
                                                        
                                                        $tipo_colors = [
                                                            'ingreso' => 'success',
                                                            'gasto' => 'danger',
                                                            'transferencia' => 'info'
                                                        ];
                                                        
                                                        $tipo_icons = [
                                                            'ingreso' => 'ri-arrow-up-line',
                                                            'gasto' => 'ri-arrow-down-line',
                                                            'transferencia' => 'ri-exchange-line'
                                                        ];
                                                        
                                                        $monto_class = $transaccion['tipo'] == 'ingreso' ? 'text-success' : 'text-danger';
                                                        $monto_prefix = $transaccion['tipo'] == 'ingreso' ? '+' : '-';
                                                        ?>
                                                        <tr>
                                                            <td><?php echo date('d M Y', strtotime($transaccion['fecha'])); ?></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 me-2">
                                                                        <div class="avatar-xs">
                                                                            <span class="avatar-title rounded" style="background-color: <?php echo $transaccion['categoria_color']; ?>20; color: <?php echo $transaccion['categoria_color']; ?>">
                                                                                <i class="<?php echo $transaccion['categoria_icono']; ?>"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="mb-0"><?php echo htmlspecialchars($transaccion['descripcion']); ?></h6>
                                                                        <?php if (!empty($transaccion['notas'])): ?>
                                                                            <small class="text-muted"><?php echo htmlspecialchars(substr($transaccion['notas'], 0, 50)) . (strlen($transaccion['notas']) > 50 ? '...' : ''); ?></small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge" style="background-color: <?php echo $transaccion['categoria_color']; ?>20; color: <?php echo $transaccion['categoria_color']; ?>">
                                                                    <?php echo htmlspecialchars($transaccion['categoria_nombre']); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($transaccion['cuenta_nombre']); ?></td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $tipo_colors[$transaccion['tipo']]; ?>-subtle text-<?php echo $tipo_colors[$transaccion['tipo']]; ?>">
                                                                    <?php echo $tipo_labels[$transaccion['tipo']]; ?>
                                                                </span>
                                                            </td>
                                                            <td class="fw-semibold <?php echo $monto_class; ?>">
                                                                <?php echo $monto_prefix; ?>$<?php echo number_format($transaccion['monto'], 2); ?>
                                                            </td>
                                                            <td>
                                                                <div class="dropdown">
                                                                    <a href="#" class="btn btn-soft-secondary btn-sm dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <i class="ri-more-fill align-middle"></i>
                                                                    </a>
                                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                                        <li><a class="dropdown-item" href="transacciones-agregar.php?id=<?php echo (int)$transaccion['id']; ?>&amp;mode=view"><i class="ri-eye-fill align-bottom me-2 text-muted"></i> Ver Detalles</a></li>
                                                                        <li><a class="dropdown-item" href="transacciones-agregar.php?id=<?php echo (int)$transaccion['id']; ?>&amp;mode=edit"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar</a></li>
                                                                        <li>
                                                                            <form action="transaccion-eliminar.php" method="post" onsubmit="return confirm('¿Seguro que deseas eliminar esta transacción?');">
                                                                                <input type="hidden" name="id" value="<?php echo (int)$transaccion['id']; ?>">
                                                                                <button type="submit" class="dropdown-item text-danger"><i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Eliminar</button>
                                                                            </form>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Paginación -->
                                    <div class="row align-items-center mt-4">
                                        <div class="col-sm">
                                            <div class="text-muted">
                                                Mostrando <span class="fw-semibold"><?php echo count($transacciones); ?></span> transacciones
                                            </div>
                                        </div>
                                        <div class="col-sm-auto">
                                            <div class="text-muted">
                                                <small>Últimas 50 transacciones</small>
                                            </div>
                                        </div>
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

</body>

</html>
