<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();

// Get user's categories
$user_id = getCurrentUserId();

// Get categories
$sql = "SELECT * FROM categorias WHERE (usuario_id = ? OR es_predefinida = 1) AND activa = 1 ORDER BY tipo, nombre";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$categorias = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Group categories by type
$categorias_por_tipo = [];
foreach ($categorias as $categoria) {
    $categorias_por_tipo[$categoria['tipo']][] = $categoria;
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Categorías | FIME - Gestión de Gastos Personales</title>
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
                                <h4 class="mb-sm-0">Categorías de Transacciones</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard-gastos.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Categorías</li>
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
                                        <h5 class="card-title mb-0 flex-grow-1">Mis Categorías</h5>
                                        <div class="flex-shrink-0">
                                            <a href="categorias-agregar.php" class="btn btn-primary">
                                                <i class="ri-add-line align-middle me-1"></i> Nueva Categoría
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($categorias_por_tipo as $tipo => $categorias_tipo): ?>
                                        <div class="mb-4">
                                            <h6 class="text-uppercase fw-semibold text-muted mb-3">
                                                <i class="ri-<?php echo $tipo == 'ingreso' ? 'arrow-up' : 'arrow-down'; ?>-line me-1"></i>
                                                <?php echo ucfirst($tipo); ?>s
                                            </h6>
                                            <div class="row g-3">
                                                <?php foreach ($categorias_tipo as $categoria): ?>
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="card border">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-shrink-0 me-3">
                                                                        <div class="avatar-sm">
                                                                            <span class="avatar-title rounded" style="background-color: <?php echo $categoria['color']; ?>20; color: <?php echo $categoria['color']; ?>">
                                                                                <i class="<?php echo $categoria['icono']; ?>"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="mb-1"><?php echo htmlspecialchars($categoria['nombre']); ?></h6>
                                                                        <p class="text-muted mb-0 small">
                                                                            <?php echo $categoria['es_predefinida'] ? 'Predefinida' : 'Personalizada'; ?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="flex-shrink-0">
                                                                        <div class="dropdown">
                                                                            <a href="#" class="btn btn-soft-secondary btn-sm dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                                                <i class="ri-more-fill align-middle"></i>
                                                                            </a>
                                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                                <?php if (!$categoria['es_predefinida']): ?>
                                                                                    <li><a class="dropdown-item" href="categorias-editar.php?id=<?php echo $categoria['id']; ?>"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Editar</a></li>
                                                                                    <li><a class="dropdown-item" href="#" onclick="eliminarCategoria(<?php echo $categoria['id']; ?>)"><i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Eliminar</a></li>
                                                                                <?php else: ?>
                                                                                    <li><span class="dropdown-item text-muted"><i class="ri-lock-line align-bottom me-2"></i> No editable</span></li>
                                                                                <?php endif; ?>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if (empty($categorias)): ?>
                                        <div class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="ri-folder-line fs-48 text-muted mb-3"></i>
                                                <p>No hay categorías registradas</p>
                                                <a href="categorias-agregar.php" class="btn btn-primary btn-sm">Crear Primera Categoría</a>
                                            </div>
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
        function eliminarCategoria(id) {
            if (confirm('¿Estás seguro de que quieres eliminar esta categoría?')) {
                // Aquí iría la lógica para eliminar la categoría
                console.log('Eliminar categoría:', id);
            }
        }
    </script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>

</html>
