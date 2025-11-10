<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
// Load config and auth helpers
require_once __DIR__ . '/layouts/config.php';
require_once __DIR__ . '/includes/auth_functions.php';

// Ensure user is logged in
requireAuth();

// Get current user id from session
$current_user_id = getCurrentUserId();
if (!$current_user_id) {
    // If not available, redirect to signin
    header('Location: auth-signin-basic.php');
    exit;
}

// Handle POST for profile update
$update_message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    // Single full name field — stored in `nombre` column
    $nombre = isset($_POST['nombreInput']) ? trim($_POST['nombreInput']) : '';
    $telefono = isset($_POST['phonenumberInput']) ? trim($_POST['phonenumberInput']) : null;
    $city = isset($_POST['cityInput']) ? trim($_POST['cityInput']) : null;
    $country = isset($_POST['countryInput']) ? trim($_POST['countryInput']) : null;
    $fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? trim($_POST['fecha_nacimiento']) : null;
    $genero = isset($_POST['genero']) ? trim($_POST['genero']) : null;

    // Do not allow changing email from this form. Only update allowed fields.
    $sql = "UPDATE usuarios SET nombre = ?, telefono = ?, fecha_nacimiento = ?, genero = ?, ciudad = ?, estado = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = ?";
    if (isset($link->pdo)) {
        $stmt = $link->pdo->prepare($sql);
        if ($stmt->execute([$nombre, $telefono, $fecha_nacimiento, $genero, $city, $country, $current_user_id])) {
            $update_message = ['type' => 'success', 'text' => 'Perfil actualizado correctamente.'];
            // update session name/email
            $_SESSION['user_name'] = $nombre;
        } else {
            $error_info = $stmt->errorInfo();
            $update_message = ['type' => 'error', 'text' => 'Error al actualizar el perfil: ' . (isset($error_info[2]) ? $error_info[2] : 'Error desconocido')];
        }
    } else {
        $stmt = mysqli_prepare($link, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssssi", $nombre, $telefono, $fecha_nacimiento, $genero, $city, $country, $current_user_id);
            if (mysqli_stmt_execute($stmt)) {
                $update_message = ['type' => 'success', 'text' => 'Perfil actualizado correctamente.'];
                // update session name/email
                $_SESSION['user_name'] = $nombre;
            } else {
                $update_message = ['type' => 'error', 'text' => 'Error al actualizar el perfil: ' . mysqli_error($link)];
            }
            mysqli_stmt_close($stmt);
        } else {
            $update_message = ['type' => 'error', 'text' => 'Error preparando la consulta: ' . mysqli_error($link)];
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $old = $_POST['oldpasswordInput'] ?? '';
    $new = $_POST['newpasswordInput'] ?? '';
    $confirm = $_POST['confirmpasswordInput'] ?? '';

    if ($new !== $confirm) {
        $update_message = ['type' => 'error', 'text' => 'La nueva contraseña y la confirmación no coinciden.'];
    } else {
        // fetch current hash
        $sql = "SELECT password_hash FROM usuarios WHERE id = ? LIMIT 1";
        if (isset($link->pdo)) {
            $stmt = $link->pdo->prepare($sql);
            $stmt->execute([$current_user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($old, $row['password_hash'])) {
                $newhash = password_hash($new, PASSWORD_DEFAULT);
                $up = "UPDATE usuarios SET password_hash = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = ?";
                $ust = $link->pdo->prepare($up);
                if ($ust->execute([$newhash, $current_user_id])) {
                    $update_message = ['type' => 'success', 'text' => 'Contraseña actualizada correctamente.'];
                } else {
                    $update_message = ['type' => 'error', 'text' => 'Error al actualizar la contraseña.'];
                }
            } else {
                $update_message = ['type' => 'error', 'text' => 'Contraseña actual incorrecta.'];
            }
        } else {
            $stmt = mysqli_prepare($link, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $current_user_id);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                $row = $res ? mysqli_fetch_assoc($res) : null;
                if ($row && password_verify($old, $row['password_hash'])) {
                    $newhash = password_hash($new, PASSWORD_DEFAULT);
                    $up = "UPDATE usuarios SET password_hash = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = ?";
                    $ust = mysqli_prepare($link, $up);
                    if ($ust) {
                        mysqli_stmt_bind_param($ust, 'si', $newhash, $current_user_id);
                        if (mysqli_stmt_execute($ust)) {
                            $update_message = ['type' => 'success', 'text' => 'Contraseña actualizada correctamente.'];
                        } else {
                            $update_message = ['type' => 'error', 'text' => 'Error al actualizar la contraseña.'];
                        }
                        mysqli_stmt_close($ust);
                    }
                } else {
                    $update_message = ['type' => 'error', 'text' => 'Contraseña actual incorrecta.'];
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Load current user data to prefill the form
$user = null;
$sql = "SELECT nombre, email, telefono, fecha_nacimiento, genero, ciudad, estado, fecha_creacion FROM usuarios WHERE id = ? LIMIT 1";
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare($sql);
    $stmt->execute([$current_user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $current_user_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res) $user = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
    }
}

// Prepare nombre for UI (full name stored in single column)
$nombre = $user['nombre'] ?? '';
?>

    <head>
        
        <title>Profile Settings | Velzon - Admin & Dashboard Template</title>
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

                        <div class="position-relative mx-n4 mt-n4">
                            <div class="profile-wid-bg profile-setting-img">
                                <img src="assets/images/profile-bg.jpg" class="profile-wid-img" alt="">  
                                <div class="overlay-content">  
                                    <div class="text-end p-3">  
                                        <div class="p-0 ms-auto rounded-circle profile-photo-edit">  
                                            <input id="profile-foreground-img-file-input" type="file" class="profile-foreground-img-file-input" >  
                                            <!-- <label for="profile-foreground-img-file-input" class="profile-photo-edit btn btn-light">  
                                                    <i class="ri-image-edit-line align-bottom me-1"></i> Change Cover
                                            </label>   -->
                                        </div>  
                                    </div>  
                                </div>  
                            </div>
                        </div>

                    <div class="row">
                        <div class="col-xxl-3">
                            <div class="card mt-n5">
                                <div class="card-body p-4">
                                    <div class="text-center">
                                        <div class="profile-user position-relative d-inline-block mx-auto  mb-4">
                                            <img src="assets/images/users/user-logo.png"
                                                class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                                alt="user-profile-image">
                                            <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                                <input id="profile-img-file-input" type="file"
                                                    class="profile-img-file-input">
                                                <label for="profile-img-file-input"
                                                    class="profile-photo-edit avatar-xs">
                                                    <span class="avatar-title rounded-circle bg-light text-body">
                                                        <i class="ri-camera-fill"></i>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                        <h5 class="fs-16 mb-1"><?php echo htmlspecialchars($nombre); ?></h5>
                                        <!-- <p class="text-muted mb-0">Lead Designer / Developer</p> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                        <div class="col-xxl-9">
                            <div class="card mt-xxl-n5">
                                <div class="card-header">
                                    <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0"
                                        role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails"
                                                role="tab">
                                                <i class="fas fa-home"></i>
                                                Información General
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                                                <i class="far fa-user"></i>
                                                Cambiar Contraseña
                                            </a>
                                        </li>
                                        <!-- <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#experience" role="tab">
                                                <i class="far fa-envelope"></i>
                                                Experience
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#privacy" role="tab">
                                                <i class="far fa-envelope"></i>
                                                Privacy Policy
                                            </a>
                                        </li> -->
                                    </ul>
                                </div>
                                <div class="card-body p-4">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="personalDetails" role="tabpanel">
                                            <?php if (!empty(
                                                $update_message)) : ?>
                                                <div class="alert alert-<?php echo $update_message['type'] === 'success' ? 'success' : 'danger'; ?>" role="alert">
                                                    <?php echo htmlspecialchars($update_message['text']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <form method="POST" action="pages-profile-settings.php">
                                                <input type="hidden" name="action" value="update_profile">
                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="nombreInput" class="form-label">Nombre Completo</label>
                                                            <input type="text" class="form-control" name="nombreInput" id="nombreInput"
                                                                placeholder="Ingresa tu nombre completo" value="<?php echo htmlspecialchars($nombre); ?>">
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="emailInput" class="form-label">Email</label>
                                                            <input type="email" class="form-control" name="emailInput" id="emailInput"
                                                                placeholder="Enter your email"
                                                                value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                                                            <small class="text-muted">El correo no se puede modificar desde aquí.</small>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="phonenumberInput" class="form-label">Celular</label>
                                                            <input type="text" class="form-control" name="phonenumberInput"
                                                                id="phonenumberInput"
                                                                placeholder="Enter your phone number"
                                                                value="<?php echo htmlspecialchars($user['telefono'] ?? ''); ?>">
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-8">
                                                        <div class="mb-3">
                                                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                                            <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento"
                                                                value="<?php echo htmlspecialchars($user['fecha_nacimiento'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="genero" class="form-label">Género</label>
                                                            <select class="form-control" name="genero" id="genero">
                                                                <option value="" <?php echo empty($user['genero']) ? 'selected' : ''; ?>>No especificar</option>
                                                                <option value="Masculino" <?php echo (isset($user['genero']) && $user['genero'] === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                                                                <option value="Femenino" <?php echo (isset($user['genero']) && $user['genero'] === 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                                                                <option value="Otro" <?php echo (isset($user['genero']) && $user['genero'] === 'Otro') ? 'selected' : ''; ?>>Otro</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="cityInput" class="form-label">City</label>
                                                            <input type="text" class="form-control" name="cityInput" id="cityInput"
                                                                placeholder="City" value="<?php echo htmlspecialchars($user['ciudad'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="countryInput" class="form-label">Country</label>
                                                            <input type="text" class="form-control" name="countryInput" id="countryInput"
                                                                placeholder="Country" value="<?php echo htmlspecialchars($user['estado'] ?? ''); ?>" />
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-12">
                                                        <div class="hstack gap-2 justify-content-end">
                                                            <button type="submit"
                                                                class="btn btn-success">Actualizar</button>
                                                            <button type="button"
                                                                class="btn btn-soft-danger" onclick=backToDashboard();>Cancelar</button>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                </div>
                                                <!--end row-->
                                            </form>
                                        </div>
                                        <!--end tab-pane-->
                                        <div class="tab-pane" id="changePassword" role="tabpanel">
                                            <?php if (!empty($update_message) && isset($_POST['action']) && $_POST['action'] === 'change_password') : ?>
                                                <div class="alert alert-<?php echo $update_message['type'] === 'success' ? 'success' : 'danger'; ?>" role="alert">
                                                    <?php echo htmlspecialchars($update_message['text']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <form method="POST" action="pages-profile-settings.php">
                                                <input type="hidden" name="action" value="change_password">
                                                <div class="row g-2">
                                                    <div class="col-lg-4">
                                                        <div>
                                                            <label for="oldpasswordInput" class="form-label">Contraseña Actual*</label>
                                                            <input type="password" class="form-control"
                                                                id="oldpasswordInput" name="oldpasswordInput"
                                                                placeholder="Enter current password">
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-4">
                                                        <div>
                                                            <label for="newpasswordInput" class="form-label">Nueva Contraseña*</label>
                                                            <input type="password" class="form-control"
                                                                id="newpasswordInput" name="newpasswordInput" placeholder="Enter new password">
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-4">
                                                        <div>
                                                            <label for="confirmpasswordInput" class="form-label">Confimar Contraseña*</label>
                                                            <input type="password" class="form-control"
                                                                id="confirmpasswordInput" name="confirmpasswordInput"
                                                                placeholder="Confirm password">
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-12">
                                                        <div class="text-end">
                                                            <button type="submit" class="btn btn-success">Cambiar Contraseña</button>
                                                        </div>
                                                    </div>
                                                    <!--end col-->
                                                </div>
                                                <!--end row-->
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div><!-- End Page-content -->

                <?php include 'layouts/footer.php'; ?>
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <?php include 'layouts/vendor-scripts.php'; ?>

        <!-- profile-setting init js -->
        <script src="assets/js/pages/profile-setting.init.js"></script>

        <!-- App js -->
        <script src="assets/js/app.js"></script>
    </body>

    <script>
        function backToDashboard(){
            document.location.href = 'dashboard-gastos.php';
        }
    </script>
</html>