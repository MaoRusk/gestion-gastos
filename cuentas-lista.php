<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();

// Get user's accounts
$user_id = getCurrentUserId();
$sql = "SELECT * FROM cuentas_bancarias WHERE usuario_id = ? ORDER BY fecha_creacion DESC";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cuentas = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Calculate totals
$total_balance = 0;
$total_debt = 0;

foreach ($cuentas as $cuenta) {
    if (in_array($cuenta['tipo'], ['tarjeta_credito', 'prestamo_personal'])) {
        // Para deudas: siempre sumar el valor absoluto del balance (puede ser negativo o positivo)
        $total_debt += abs($cuenta['balance_actual']);
    } else {
        // Para cuentas normales: sumar solo si el balance es positivo
        if ($cuenta['balance_actual'] > 0) {
            $total_balance += $cuenta['balance_actual'];
        }
    }
}

$patrimonio_neto = $total_balance - $total_debt;

?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Mis Cuentas | FIME - Gestión de Gastos Personales</title>
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
                                <h4 class="mb-sm-0">Mis Cuentas Bancarias</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard-gastos.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Cuentas</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <h5 class="card-title mb-0 flex-grow-1">Lista de Cuentas</h5>
                                        <div class="flex-shrink-0">
                                            <a href="cuentas-agregar.php" class="btn btn-primary">
                                                <i class="ri-add-line align-middle me-1"></i> Agregar Cuenta
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($_SESSION['success_message'])): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="ri-check-line me-2"></i>
                                            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                        <?php unset($_SESSION['success_message']); ?>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($_SESSION['error_message'])): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="ri-error-warning-line me-2"></i>
                                            <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                        <?php unset($_SESSION['error_message']); ?>
                                    <?php endif; ?>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-borderless table-nowrap align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Cuenta</th>
                                                    <th scope="col">Tipo</th>
                                                    <th scope="col">Banco</th>
                                                    <th scope="col">Balance</th>
                                                    <th scope="col">Estado</th>
                                                    <th scope="col">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($cuentas)): ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center py-4">
                                                            <div class="text-muted">
                                                                <i class="ri-bank-line fs-48 text-muted mb-3"></i>
                                                                <p>No tienes cuentas registradas</p>
                                                                <a href="cuentas-agregar.php" class="btn btn-primary btn-sm">Agregar Primera Cuenta</a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($cuentas as $cuenta): ?>
                                                        <?php
                                                        $tipo_labels = [
                                                            'cuenta_corriente' => 'Cuenta Corriente',
                                                            'tarjeta_credito' => 'Tarjeta de Crédito',
                                                            'prestamo_personal' => 'Préstamo Personal',
                                                            'efectivo' => 'Efectivo',
                                                            'cuenta_ahorros' => 'Cuenta de Ahorros'
                                                        ];
                                                        
                                                        $iconos = [
                                                            'cuenta_corriente' => 'ri-bank-line',
                                                            'tarjeta_credito' => 'ri-bank-card-line',
                                                            'prestamo_personal' => 'ri-hand-coin-line',
                                                            'efectivo' => 'ri-money-dollar-circle-line',
                                                            'cuenta_ahorros' => 'ri-money-dollar-box-line'
                                                        ];

                                                        $color = [
                                                            'cuenta_corriente' => 'primary',
                                                            'tarjeta_credito' => 'warning',
                                                            'prestamo_personal' => 'danger',
                                                            'efectivo' => 'success',
                                                            'cuenta_ahorros' => 'info'
                                                        ];
                                                        
                                                        $is_debt = in_array($cuenta['tipo'], ['tarjeta_credito', 'prestamo_personal']);
                                                        $balance_class = $cuenta['balance_actual'] < 0 ? 'text-danger' : 'text-success';
                                                        $balance_prefix = $is_debt && $cuenta['balance_actual'] < 0 ? '-' : '';
                                                        
                                                        // Calcular progreso de pago si es deuda
                                                        $monto_original = !empty($cuenta['limite_credito']) ? abs($cuenta['limite_credito']) : 0;
                                                        $deuda_actual = abs($cuenta['balance_actual']);
                                                        $pagado = $monto_original > 0 ? ($monto_original - $deuda_actual) : 0;
                                                        $porcentaje_pagado = $monto_original > 0 ? (($pagado * 100) / $monto_original) : 0;
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 me-2">
                                                                        <div class="avatar-xs">
                                                                            <span class="avatar-title bg-soft-<?php echo $color[$cuenta['tipo']]; ?> text-<?php echo $color[$cuenta['tipo']]; ?> rounded fs-3">
                                                                                <i class="<?php echo $iconos[$cuenta['tipo']]; ?>"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="mb-0"><?php echo htmlspecialchars($cuenta['nombre']); ?></h6>
                                                                        <?php if (!empty($cuenta['numero_cuenta'])): ?>
                                                                            <small class="text-muted">****<?php echo htmlspecialchars($cuenta['numero_cuenta']); ?></small>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><?php echo $tipo_labels[$cuenta['tipo']]; ?></td>
                                                            <td><?php echo !empty($cuenta['banco']) ? htmlspecialchars($cuenta['banco']) : '-'; ?></td>
                                                            <td class="fw-semibold <?php echo $balance_class; ?>">
                                                                <?php echo $balance_prefix; ?>$<?php echo number_format(abs($cuenta['balance_actual']), 2); ?>
                                                                <?php if ($is_debt && $monto_original > 0): ?>
                                                                    <br><small class="text-muted">
                                                                        Progreso: <?php echo number_format($porcentaje_pagado, 1); ?>% 
                                                                        (<?php echo number_format($pagado, 2); ?> / <?php echo number_format($monto_original, 2); ?>)
                                                                    </small>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge <?php echo $cuenta['activa'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                                                                    <?php echo $cuenta['activa'] ? 'Activa' : 'Inactiva'; ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="dropdown">
                                                                    <a href="#" class="btn btn-soft-secondary btn-sm dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <i class="ri-more-fill align-middle"></i>
                                                                    </a>
                                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                                        <li><a class="dropdown-item" href="cuentas-agregar.php?id=<?php echo (int)$cuenta['id']; ?>&amp;mode=view"><i class="ri-eye-fill align-bottom me-2 text-muted"></i> Ver Detalles</a></li>
                                                                        <li><a class="dropdown-item" href="cuentas-agregar.php?id=<?php echo (int)$cuenta['id']; ?>&amp;mode=edit"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar</a></li>
                                                                        <li>
                                                                            <form action="cuentas-eliminar.php" method="post" onsubmit="return confirm('¿Estás seguro de que deseas eliminar la cuenta \'<?php echo htmlspecialchars(addslashes($cuenta['nombre'])); ?>\'?\n\nEsta acción eliminará permanentemente la cuenta y todas las transacciones asociadas. Esta acción no se puede deshacer.');" style="margin:0;padding:0;">
                                                                                <input type="hidden" name="id" value="<?php echo (int)$cuenta['id']; ?>">
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de Cuentas -->
                    <div class="row">
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total en Cuentas</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-primary fs-14 mb-0">
                                                <i class="ri-arrow-up-line fs-13 align-middle"></i> $<?php echo number_format($total_balance, 2); ?>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">$<?php echo number_format($total_balance, 2); ?></h4>
                                            <span class="badge bg-success-subtle text-primary mb-0">
                                                <i class="ri-arrow-up-line align-middle"></i> Activo
                                            </span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-success rounded fs-3">
                                                <i class="ri-wallet-3-line text-primary"></i>
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
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Deuda Total</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="text-danger fs-14 mb-0">
                                                <i class="ri-arrow-down-line fs-13 align-middle"></i> $<?php echo number_format($total_debt, 2); ?>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">$<?php echo number_format($total_debt, 2); ?></h4>
                                            <span class="badge bg-danger-subtle text-danger mb-0">
                                                <i class="ri-arrow-down-line align-middle"></i> Deuda
                                            </span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-danger rounded fs-3">
                                                <i class="ri-bank-card-line text-danger"></i>
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
                                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Patrimonio Neto</p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <h5 class="<?php echo $patrimonio_neto >= 0 ? 'text-success' : 'text-danger'; ?> fs-14 mb-0">
                                                <i class="ri-arrow-<?php echo $patrimonio_neto >= 0 ? 'up' : 'down'; ?>-line fs-13 align-middle"></i> $<?php echo number_format($patrimonio_neto, 2); ?>
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">$<?php echo number_format($patrimonio_neto, 2); ?></h4>
                                            <span class="badge <?php echo $patrimonio_neto >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> mb-0">
                                                <i class="ri-arrow-<?php echo $patrimonio_neto >= 0 ? 'up' : 'down'; ?>-line align-middle"></i> <?php echo $patrimonio_neto >= 0 ? 'Neto' : 'Negativo'; ?>
                                            </span>
                                        </div>
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-soft-info rounded fs-3">
                                                <i class="ri-line-chart-line text-info"></i>
                                            </span>
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
