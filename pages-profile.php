<?php include 'layouts/session.php'; ?>
<?php
// Load config and auth helpers so we can fetch the requested user's data
require_once __DIR__ . '/layouts/config.php';
require_once __DIR__ . '/includes/auth_functions.php';

// If this page is reached from usuarios-lista.php it passes ?id=NN
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$profile_user = null;
if ($profile_id) {
    // Safely fetch the user by id
    $sql = "SELECT id, nombre, email, telefono, fecha_nacimiento, genero, ciudad, estado, activo, fecha_creacion FROM usuarios WHERE id = ? LIMIT 1";
    if (isset($link->pdo)) {
        $stmt = $link->pdo->prepare($sql);
        $stmt->execute([$profile_id]);
        $profile_user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = mysqli_prepare($link, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $profile_id);
            if (mysqli_stmt_execute($stmt)) {
                $res = mysqli_stmt_get_result($stmt);
                if ($res && ($row = mysqli_fetch_assoc($res))) {
                    $profile_user = $row;
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fallback to current logged user if no profile found
if (!$profile_user) {
    $profile_user = [
        'nombre' => getCurrentUserName(),
        'email' => getCurrentUserEmail(),
        'telefono' => '',
        'ciudad' => '',
        'estado' => '',
        'fecha_creacion' => date('Y-m-d')
    ];
}
?>
<?php include 'layouts/head-main.php'; ?>

    <head>
        
        <title>Profile | Velzon - Admin & Dashboard Template</title>
        <?php include 'layouts/title-meta.php'; ?>

        <!-- swiper css -->
        <link rel="stylesheet" href="assets/libs/swiper/swiper-bundle.min.css">

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
                    <div class="profile-foreground position-relative mx-n4 mt-n4">
                        <div class="profile-wid-bg">
                            <img src="assets/images/background-logo.png" alt="" class="profile-wid-img" />
                        </div>
                    </div>
                    <div class="pt-4 mb-4 mb-lg-3 pb-lg-4">
                        <div class="row g-4">
                            <div class="col-auto">
                                <div class="avatar-lg">
                                    <img src="assets/images/users/user-logo.png" alt="user-img"
                                        class="img-thumbnail rounded-circle" />
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col">
                                <div class="p-2">
                                    <h3 class="text-white mb-1"><?php echo htmlspecialchars($profile_user['nombre'] ?? 'Usuario'); ?></h3>
                                    <p class="text-white-75"><?php echo htmlspecialchars($profile_user['genero'] ?? ''); ?></p>
                                    <div class="hstack text-white-50 gap-1">
                                        <div class="me-2"><i
                                                class="ri-map-pin-user-line me-1 text-white-75 fs-16 align-middle"></i><?php echo htmlspecialchars($profile_user['ciudad'] ?? ''); ?>,
                                            <?php echo htmlspecialchars($profile_user['estado'] ?? ''); ?></div>
                                        <div><i
                                                class="ri-building-line me-1 text-white-75 fs-16 align-middle"></i>&nbsp;
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-12 col-lg-12 order-last order-lg-0">
                                <div class="row text text-white-50 text-center">
                                    <div class="d-flex">
                                        <ul class="nav nav-pills animation-nav profile-nav gap-2 gap-lg-3 flex-grow-1" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link fs-14 active" data-bs-toggle="tab" href="#overview-tab" role="tab">
                                                    <i class="ri-airplay-fill d-inline-block d-md-none"></i> <span class="d-none d-md-inline-block">General</span>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="flex-shrink-0">
                                            <!-- <a href="pages-profile-settings.php" class="btn btn-success"><i class="ri-edit-box-line align-bottom"></i> Edit Profile</a> -->
                                        </div>
                                    </div>
                                    
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3">Info</h5>
                                            <div class="table-responsive">
                                                <table class="table table-borderless mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <th class="ps-0" scope="row">Nombre:</th>
                                                            <td class="text-muted"><?php echo htmlspecialchars($profile_user['nombre'] ?? ''); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-0" scope="row">Teléfono:</th>
                                                            <td class="text-muted"><?php echo htmlspecialchars($profile_user['telefono'] ?? 'N/A'); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-0" scope="row">E-mail:</th>
                                                            <td class="text-muted"><?php echo htmlspecialchars($profile_user['email'] ?? ''); ?></td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-0" scope="row">Ubicación:</th>
                                                            <td class="text-muted"><?php echo htmlspecialchars(($profile_user['ciudad'] ?? '') . ($profile_user['ciudad'] && $profile_user['estado'] ? ', ' : '') . ($profile_user['estado'] ?? '')); ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="ps-0" scope="row">Fecha de Ingreso:</th>
                                                            <td class="text-muted"><?php echo isset($profile_user['fecha_creacion']) ? date('d M Y', strtotime($profile_user['fecha_creacion'])) : ''; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div><!-- end card body -->
                                    </div><!-- end card -->

                                    <div class="col-lg-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-soft-danger" onclick="backToUsersList();">Regresar</button>
                                        </div>
                                    </div>

                                              
                                            </div>
                                        </div>
                                        <!--end row-->
                                    </div>
                                    <!--end tab-pane-->
                                </div>
                                <!--end tab-content-->
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->

                    </div><!-- container-fluid -->
                </div><!-- End Page-content -->

                <?php include 'layouts/footer.php'; ?>
            </div><!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <?php // include 'layouts/customizer.php'; ?>

        <?php include 'layouts/vendor-scripts.php'; ?>

        <!-- swiper js -->
        <script src="assets/libs/swiper/swiper-bundle.min.js"></script>

        <!-- profile init js -->
        <script src="assets/js/pages/profile.init.js"></script>
        
        <!-- App js -->
        <script src="assets/js/app.js"></script>

        <script>
            function backToUsersList(){
                document.location.href = "usuarios-lista.php";
            }
        </script>
    </body>

</html>