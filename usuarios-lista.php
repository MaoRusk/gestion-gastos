<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();
if (!isAdmin()) {
    http_response_code(403);
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Acceso denegado</title></head><body style="font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;padding:2rem">';
    echo '<h2>403 - Acceso denegado</h2><p>No tienes permisos para ver esta sección.</p>';
    echo '<p><a href="dashboard-gastos.php">Volver al dashboard</a></p>';
    echo '</body></html>';
    exit;
}

// Check if user is admin (for now, we'll assume all logged users can see users)
$user_id = getCurrentUserId();

// Detectar tipo de base de datos para compatibilidad
$isPostgres = isset($link->type) && $link->type === 'postgresql';

// Get all users
$sql = "SELECT id, nombre, email, telefono, fecha_nacimiento, genero, ciudad, estado, 
               activo, fecha_creacion, fecha_actualizacion
        FROM usuarios 
        ORDER BY fecha_creacion DESC";
if (isset($link->pdo)) {
    $stmt = $link->pdo->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $result = mysqli_query($link, $sql);
    $users = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
}

// Handle user activation/deactivation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_user'])) {
    $target_user_id = intval($_POST['user_id']);
    $new_status = intval($_POST['new_status']);
    
    $update_sql = "UPDATE usuarios SET activo = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = ?";
    if (isset($link->pdo)) {
        $stmt = $link->pdo->prepare($update_sql);
        if ($stmt->execute([$new_status, $target_user_id])) {
            $message = $new_status ? "Usuario activado exitosamente" : "Usuario desactivado exitosamente";
            $message_type = "success";
        } else {
            $message = "Error al actualizar el usuario";
            $message_type = "error";
        }
    } else {
        $stmt = mysqli_prepare($link, $update_sql);
        mysqli_stmt_bind_param($stmt, "ii", $new_status, $target_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = $new_status ? "Usuario activado exitosamente" : "Usuario desactivado exitosamente";
            $message_type = "success";
        } else {
            $message = "Error al actualizar el usuario";
            $message_type = "error";
        }
        mysqli_stmt_close($stmt);
    }
    
    // Redirect to avoid resubmission
    header("Location: usuarios-lista.php");
    exit;
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Usuarios | FIME - Gestión de Gastos Personales</title>
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
                                <h4 class="mb-sm-0 font-size-18">Gestión de Usuarios</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard-gastos.php">Inicio</a></li>
                                        <li class="breadcrumb-item active">Usuarios</li>
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
                                    <h4 class="card-title">Lista de Usuarios del Sistema</h4>
                                    <p class="card-title-desc">Administra los usuarios registrados en el sistema</p>
                                </div>
                                <div class="card-body">
                                    
                                    <!-- Statistics Cards -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <div class="card border border-success">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">
                                                            <p class="text-truncate font-size-14 mb-0">Total Usuarios</p>
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title bg-success-subtle text-success rounded-circle">
                                                                    <i class="ri-user-2-line font-size-18"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <h5 class="mb-0"><?php echo count($users); ?></h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border border-primary">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">
                                                            <p class="text-truncate font-size-14 mb-0">Usuarios Activos</p>
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                                    <i class="ri-check-line font-size-18"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <h5 class="mb-0"><?php 
                                                            $active_count = count(array_filter($users, function($u) { return $u['activo']; }));
                                                            echo $active_count;
                                                        ?></h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border border-warning">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">
                                                            <p class="text-truncate font-size-14 mb-0">Usuarios Inactivos</p>
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                                                    <i class="ri-close-line font-size-18"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <h5 class="mb-0"><?php 
                                                            $inactive_count = count(array_filter($users, function($u) { return !$u['activo']; }));
                                                            echo $inactive_count;
                                                        ?></h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card border border-info">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-grow-1">
                                                            <p class="text-truncate font-size-14 mb-0">Mi Usuario</p>
                                                        </div>
                                                        <div class="flex-shrink-0">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title bg-info-subtle text-info rounded-circle">
                                                                    <i class="ri-account-circle-line font-size-18"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <h5 class="mb-0"><?php echo htmlspecialchars(getCurrentUserName()); ?></h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Users Table -->
                                    <div class="table-responsive">
                                        <table class="table table-striped table-nowrap align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col">ID</th>
                                                    <th scope="col">Nombre</th>
                                                    <th scope="col">Email</th>
                                                    <th scope="col">Teléfono</th>
                                                    <th scope="col">Ciudad</th>
                                                    <th scope="col">Estado</th>
                                                    <th scope="col">Fecha Registro</th>
                                                    <th scope="col">Estado</th>
                                                    <th scope="col">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($users)): ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center">
                                                            <p class="text-muted mb-0">No hay usuarios registrados</p>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($users as $user): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="avatar-xs">
                                                                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                                            <i class="ri-user-line font-size-18"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div class="ms-2">
                                                                        <strong><?php echo htmlspecialchars($user['nombre']); ?></strong>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                            <td><?php echo htmlspecialchars($user['telefono'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($user['ciudad'] ?? 'N/A'); ?></td>
                                                            <td><?php echo htmlspecialchars($user['estado'] ?? 'N/A'); ?></td>
                                                            <td><?php echo date('d/m/Y', strtotime($user['fecha_creacion'])); ?></td>
                                                            <td>
                                                                <?php if ($user['activo']): ?>
                                                                    <span class="badge bg-success-subtle text-success">
                                                                        <i class="ri-check-line me-1"></i>Activo
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger-subtle text-danger">
                                                                        <i class="ri-close-line me-1"></i>Inactivo
                                                                    </span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <form method="POST" class="d-inline">
                                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                    <input type="hidden" name="new_status" value="<?php echo $user['activo'] ? 0 : 1; ?>">
                                                                    <button type="submit" name="toggle_user" class="btn btn-sm <?php echo $user['activo'] ? 'btn-danger' : 'btn-success'; ?>">
                                                                        <?php if ($user['activo']): ?>
                                                                            <i class="ri-toggle-fill me-1"></i>Desactivar
                                                                        <?php else: ?>
                                                                            <i class="ri-toggle-fill me-1"></i>Activar
                                                                        <?php endif; ?>
                                                                    </button>
                                                                </form>
                                                                <a href="pages-profile.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                                                                    <i class="ri-eye-line me-1"></i>Ver
                                                                </a>
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

    <script>
        // DataTables initialization
        $(document).ready(function() {
            $('.table').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/es_ES.json'
                },
                order: [[0, 'desc']]
            });
        });
    </script>

</body>

</html>

