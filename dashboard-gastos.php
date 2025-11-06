<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();
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
                                                <i class="ri-arrow-right-up-line fs-13 align-middle"></i> +$12,350
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">$45,678</h4>
                                            <span class="badge bg-success-subtle text-primary mb-0">
                                                <i class="ri-arrow-up-line align-middle"></i> +2.4%
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
                                                <i class="ri-arrow-up-line fs-13 align-middle"></i> +$2,500
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">$8,500</h4>
                                            <span class="badge bg-soft-success text-success mb-0">
                                                <i class="ri-arrow-up-line align-middle"></i> +15.2%
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
                                                <i class="ri-arrow-down-line fs-13 align-middle"></i> -$1,200
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">$3,200</h4>
                                            <span class="badge bg-soft-success text-danger mb-0">
                                                <i class="ri-arrow-down-line align-middle"></i> -8.1%
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
                                                <i class="ri-arrow-up-line fs-13 align-middle"></i> +$1,300
                                            </h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end justify-content-between mt-4">
                                        <div>
                                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">$5,300</h4>
                                            <span class="badge bg-soft-success text-success mb-0">
                                                <i class="ri-arrow-up-line align-middle"></i> +32.5%
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
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-2">
                                                                <div class="avatar-xs">
                                                                    <span class="avatar-title bg-soft-success text-success rounded">
                                                                        <i class="ri-arrow-up-line"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Salario</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>Ingresos</td>
                                                    <td><span class="badge bg-success-subtle text-success">Ingreso</span></td>
                                                    <td class="text-success fw-semibold">+$8,500</td>
                                                    <td>15 Nov 2024</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-2">
                                                                <div class="avatar-xs">
                                                                    <span class="avatar-title bg-soft-danger text-danger rounded">
                                                                        <i class="ri-arrow-down-line"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Supermercado</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>Alimentación</td>
                                                    <td><span class="badge bg-danger-subtle text-danger">Gasto</span></td>
                                                    <td class="text-danger fw-semibold">-$450</td>
                                                    <td>14 Nov 2024</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-2">
                                                                <div class="avatar-xs">
                                                                    <span class="avatar-title bg-soft-danger text-danger rounded">
                                                                        <i class="ri-arrow-down-line"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Transporte</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>Transporte</td>
                                                    <td><span class="badge bg-danger-subtle text-danger">Gasto</span></td>
                                                    <td class="text-danger fw-semibold">-$120</td>
                                                    <td>13 Nov 2024</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-2">
                                                                <div class="avatar-xs">
                                                                    <span class="avatar-title bg-soft-danger text-danger rounded">
                                                                        <i class="ri-arrow-down-line"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-0">Renta</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>Vivienda</td>
                                                    <td><span class="badge bg-danger-subtle text-danger">Gasto</span></td>
                                                    <td class="text-danger fw-semibold">-$2,000</td>
                                                    <td>12 Nov 2024</td>
                                                </tr>
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

                            <!-- Metas de Ahorro -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Metas de Ahorro</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Vacaciones</span>
                                            <span class="text-muted">$2,500 / $5,000</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 50%"></div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Laptop Nueva</span>
                                            <span class="text-muted">$800 / $1,500</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 53%"></div>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Fondo de Emergencia</span>
                                            <span class="text-muted">$1,200 / $3,000</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 40%"></div>
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

    <!-- apexcharts -->
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

    <!-- Dashboard init -->
    <script src="assets/js/pages/dashboard-gastos.init.js"></script>

</body>

</html>
