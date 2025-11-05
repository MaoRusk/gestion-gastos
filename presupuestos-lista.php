<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();

// Get user's budgets
$user_id = getCurrentUserId();

// Get current month budgets with actual spending
$sql = "SELECT p.*, c.nombre as categoria_nombre, c.color as categoria_color, c.icono as categoria_icono,
               COALESCE(SUM(t.monto), 0) as gasto_real
        FROM presupuestos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN transacciones t ON p.categoria_id = t.categoria_id 
            AND t.usuario_id = p.usuario_id 
            AND t.tipo = 'gasto' 
            AND t.fecha >= p.fecha_inicio 
            AND t.fecha <= p.fecha_fin
        WHERE p.usuario_id = ? AND p.activo = 1
        GROUP BY p.id, p.nombre, p.monto_limite, p.fecha_inicio, p.fecha_fin, p.categoria_id, c.nombre, c.color, c.icono
        ORDER BY p.fecha_inicio DESC";

$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$presupuestos = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Calculate budget statistics
$total_presupuesto = 0;
$total_gastado = 0;
$presupuestos_sobrepasados = 0;

foreach ($presupuestos as $presupuesto) {
    $total_presupuesto += $presupuesto['monto_limite'];
    $total_gastado += $presupuesto['gasto_real'];
    if ($presupuesto['gasto_real'] > $presupuesto['monto_limite']) {
        $presupuestos_sobrepasados++;
    }
}

$porcentaje_gastado = $total_presupuesto > 0 ? ($total_gastado / $total_presupuesto) * 100 : 0;
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Presupuestos | FIME - Gestión de Gastos Personales</title>
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
                                <h4 class="mb-sm-0">Gestión de Presupuestos</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard-gastos.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Presupuestos</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <!-- Resumen de Presupuestos -->
                    <div class="row">
                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Presupuesto Total</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-primary fs-14 mb-0">
                                                <i class="ri-wallet-line fs-13 align-middle"></i> $<?php echo number_format($total_presupuesto, 2); ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Gastado</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-warning fs-14 mb-0">
                                                <i class="ri-money-dollar-circle-line fs-13 align-middle"></i> $<?php echo number_format($total_gastado, 2); ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Restante</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="<?php echo ($total_presupuesto - $total_gastado) >= 0 ? 'text-success' : 'text-danger'; ?> fs-14 mb-0">
                                                <i class="ri-<?php echo ($total_presupuesto - $total_gastado) >= 0 ? 'arrow-up' : 'arrow-down'; ?>-line fs-13 align-middle"></i> $<?php echo number_format($total_presupuesto - $total_gastado, 2); ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Sobrepasados</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-danger fs-14 mb-0">
                                                <i class="ri-alert-line fs-13 align-middle"></i> <?php echo $presupuestos_sobrepasados; ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Barra de Progreso General -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">Progreso General del Presupuesto</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 me-3">
                                            <div class="progress progress-lg">
                                                <div class="progress-bar <?php echo $porcentaje_gastado > 100 ? 'bg-danger' : ($porcentaje_gastado > 80 ? 'bg-warning' : 'bg-success'); ?>" 
                                                     style="width: <?php echo min($porcentaje_gastado, 100); ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="fw-semibold <?php echo $porcentaje_gastado > 100 ? 'text-danger' : ($porcentaje_gastado > 80 ? 'text-warning' : 'text-success'); ?>">
                                                <?php echo number_format($porcentaje_gastado, 1); ?>%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title mb-0 flex-grow-1">Mis Presupuestos</h5>
                                        <div class="flex-shrink-0">
                                            <a href="presupuestos-agregar.php" class="btn btn-primary">
                                                <i class="ri-add-line align-middle me-1"></i> Nuevo Presupuesto
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($presupuestos)): ?>
                                        <div class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="ri-pie-chart-line fs-48 text-muted mb-3"></i>
                                                <p>No hay presupuestos configurados</p>
                                                <a href="presupuestos-agregar.php" class="btn btn-primary btn-sm">Crear Primer Presupuesto</a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="row g-3">
                                            <?php foreach ($presupuestos as $presupuesto): ?>
                                                <?php
                                                $porcentaje = $presupuesto['monto_limite'] > 0 ? ($presupuesto['gasto_real'] / $presupuesto['monto_limite']) * 100 : 0;
                                                $is_over_budget = $presupuesto['gasto_real'] > $presupuesto['monto_limite'];
                                                $progress_class = $is_over_budget ? 'bg-danger' : ($porcentaje > 80 ? 'bg-warning' : 'bg-success');
                                                $text_class = $is_over_budget ? 'text-danger' : ($porcentaje > 80 ? 'text-warning' : 'text-success');
                                                ?>
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="card border">
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-center mb-3">
                                                                <div class="flex-shrink-0 me-3">
                                                                    <div class="avatar-sm">
                                                                        <span class="avatar-title rounded" style="background-color: <?php echo $presupuesto['categoria_color']; ?>20; color: <?php echo $presupuesto['categoria_color']; ?>">
                                                                            <i class="<?php echo $presupuesto['categoria_icono']; ?>"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <h6 class="mb-1"><?php echo htmlspecialchars($presupuesto['nombre']); ?></h6>
                                                                    <p class="text-muted mb-0 small"><?php echo htmlspecialchars($presupuesto['categoria_nombre']); ?></p>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <div class="dropdown">
                                                                        <a href="#" class="btn btn-soft-secondary btn-sm dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <i class="ri-more-fill align-middle"></i>
                                                                        </a>
                                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                                            <li><a class="dropdown-item" href="presupuestos-editar.php?id=<?php echo $presupuesto['id']; ?>"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar</a></li>
                                                                            <li><a class="dropdown-item" href="#" onclick="eliminarPresupuesto(<?php echo $presupuesto['id']; ?>)"><i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Eliminar</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <div class="d-flex justify-content-between mb-1">
                                                                    <span class="text-muted small">Progreso</span>
                                                                    <span class="fw-semibold small <?php echo $text_class; ?>">
                                                                        $<?php echo number_format($presupuesto['gasto_real'], 2); ?> / $<?php echo number_format($presupuesto['monto_limite'], 2); ?>
                                                                    </span>
                                                                </div>
                                                                <div class="progress progress-sm">
                                                                    <div class="progress-bar <?php echo $progress_class; ?>" style="width: <?php echo min($porcentaje, 100); ?>%"></div>
                                                                </div>
                                                                <div class="d-flex justify-content-between mt-1">
                                                                    <span class="small text-muted"><?php echo date('M d', strtotime($presupuesto['fecha_inicio'])); ?> - <?php echo date('M d', strtotime($presupuesto['fecha_fin'])); ?></span>
                                                                    <span class="small fw-semibold <?php echo $text_class; ?>">
                                                                        <?php echo number_format($porcentaje, 1); ?>%
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="small text-muted">
                                                                    Restante: $<?php echo number_format($presupuesto['monto_limite'] - $presupuesto['gasto_real'], 2); ?>
                                                                </span>
                                                                <?php if ($is_over_budget): ?>
                                                                    <span class="badge bg-danger-subtle text-danger">
                                                                        <i class="ri-alert-line align-middle"></i> Sobrepasado
                                                                    </span>
                                                                <?php elseif ($porcentaje > 80): ?>
                                                                    <span class="badge bg-warning-subtle text-warning">
                                                                        <i class="ri-alert-line align-middle"></i> Cerca del límite
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-success-subtle text-success">
                                                                        <i class="ri-check-line align-middle"></i> En control
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
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

    <script>
        function eliminarPresupuesto(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este presupuesto?')) {
                // Aquí iría la lógica para eliminar el presupuesto
                console.log('Eliminar presupuesto:', id);
            }
        }
    </script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>

</html>
