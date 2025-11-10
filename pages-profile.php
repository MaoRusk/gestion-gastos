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
                            <img src="assets/images/profile-bg.jpg" alt="" class="profile-wid-img" />
                        </div>
                    </div>
                    <div class="pt-4 mb-4 mb-lg-3 pb-lg-4">
                        <div class="row g-4">
                            <div class="col-auto">
                                <div class="avatar-lg">
                                    <img src="assets/images/users/avatar-1.jpg" alt="user-img"
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
                                                                        <th class="ps-0" scope="row">Full Name :</th>
                                                                        <td class="text-muted"><?php echo htmlspecialchars($profile_user['nombre'] ?? ''); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="ps-0" scope="row">Mobile :</th>
                                                                        <td class="text-muted"><?php echo htmlspecialchars($profile_user['telefono'] ?? 'N/A'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="ps-0" scope="row">E-mail :</th>
                                                                        <td class="text-muted"><?php echo htmlspecialchars($profile_user['email'] ?? ''); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="ps-0" scope="row">Location :</th>
                                                                        <td class="text-muted"><?php echo htmlspecialchars(($profile_user['ciudad'] ?? '') . ($profile_user['ciudad'] && $profile_user['estado'] ? ', ' : '') . ($profile_user['estado'] ?? '')); ?>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="ps-0" scope="row">Joining Date</th>
                                                                        <td class="text-muted"><?php echo isset($profile_user['fecha_creacion']) ? date('d M Y', strtotime($profile_user['fecha_creacion'])) : ''; ?></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div><!-- end card body -->
                                                </div><!-- end card -->

                                              
                                            </div>
                                        </div>
                                        <!--end row-->
                                    </div>
                                    <div class="tab-pane fade" id="activities" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title mb-3">Activities</h5>
                                                <div class="acitivity-timeline">
                                                    <div class="acitivity-item d-flex">
                                                        <div class="flex-shrink-0">
                                                            <img src="assets/images/users/avatar-1.jpg" alt=""
                                                                class="avatar-xs rounded-circle acitivity-avatar" />
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">Oliver Phillips <span
                                                                    class="badge bg-soft-primary text-primary align-middle">New</span>
                                                            </h6>
                                                            <p class="text-muted mb-2">We talked about a project on
                                                                linkedin.</p>
                                                            <small class="mb-0 text-muted">Today</small>
                                                        </div>
                                                    </div>
                                                    <div class="acitivity-item py-3 d-flex">
                                                        <div class="flex-shrink-0 avatar-xs acitivity-avatar">
                                                            <div
                                                                class="avatar-title bg-soft-success text-success rounded-circle">
                                                                N
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">Nancy Martino <span
                                                                    class="badge bg-soft-secondary text-secondary align-middle">In
                                                                    Progress</span></h6>
                                                            <p class="text-muted mb-2"><i
                                                                    class="ri-file-text-line align-middle ms-2"></i>
                                                                Create new project Buildng product</p>
                                                            <div class="avatar-group mb-2">
                                                                <a href="javascript: void(0);" class="avatar-group-item"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="" data-bs-original-title="Christi">
                                                                    <img src="assets/images/users/avatar-4.jpg" alt=""
                                                                        class="rounded-circle avatar-xs" />
                                                                </a>
                                                                <a href="javascript: void(0);" class="avatar-group-item"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="" data-bs-original-title="Frank Hook">
                                                                    <img src="assets/images/users/avatar-3.jpg" alt=""
                                                                        class="rounded-circle avatar-xs" />
                                                                </a>
                                                                <a href="javascript: void(0);" class="avatar-group-item"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="" data-bs-original-title=" Ruby">
                                                                    <div class="avatar-xs">
                                                                        <div
                                                                            class="avatar-title rounded-circle bg-light text-primary">
                                                                            R
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                                <a href="javascript: void(0);" class="avatar-group-item"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="" data-bs-original-title="more">
                                                                    <div class="avatar-xs">
                                                                        <div class="avatar-title rounded-circle">
                                                                            2+
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <small class="mb-0 text-muted">Yesterday</small>
                                                        </div>
                                                    </div>
                                                    <div class="acitivity-item py-3 d-flex">
                                                        <div class="flex-shrink-0">
                                                            <img src="assets/images/users/avatar-2.jpg" alt=""
                                                                class="avatar-xs rounded-circle acitivity-avatar" />
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">Natasha Carey <span
                                                                    class="badge bg-soft-success text-success align-middle">Completed</span>
                                                            </h6>
                                                            <p class="text-muted mb-2">Adding a new event with
                                                                attachments</p>
                                                            <div class="row">
                                                                <div class="col-xxl-4">
                                                                    <div class="row border border-dashed gx-2 p-2 mb-2">
                                                                        <div class="col-4">
                                                                            <img src="assets/images/small/img-2.jpg"
                                                                                alt="" class="img-fluid rounded" />
                                                                        </div>
                                                                        <!--end col-->
                                                                        <div class="col-4">
                                                                            <img src="assets/images/small/img-3.jpg"
                                                                                alt="" class="img-fluid rounded" />
                                                                        </div>
                                                                        <!--end col-->
                                                                        <div class="col-4">
                                                                            <img src="assets/images/small/img-4.jpg"
                                                                                alt="" class="img-fluid rounded" />
                                                                        </div>
                                                                        <!--end col-->
                                                                    </div>
                                                                    <!--end row-->
                                                                </div>
                                                            </div>
                                                            <small class="mb-0 text-muted">25 Nov</small>
                                                        </div>
                                                    </div>
                                                    <div class="acitivity-item py-3 d-flex">
                                                        <div class="flex-shrink-0">
                                                            <img src="assets/images/users/avatar-6.jpg" alt=""
                                                                class="avatar-xs rounded-circle acitivity-avatar" />
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">Bethany Johnson</h6>
                                                            <p class="text-muted mb-2">added a new member to velzon
                                                                dashboard</p>
                                                            <small class="mb-0 text-muted">19 Nov</small>
                                                        </div>
                                                    </div>
                                                    <div class="acitivity-item py-3 d-flex">
                                                        <div class="flex-shrink-0">
                                                            <div class="avatar-xs acitivity-avatar">
                                                                <div
                                                                    class="avatar-title rounded-circle bg-soft-danger text-danger">
                                                                    <i class="ri-shopping-bag-line"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">Your order is placed <span
                                                                    class="badge bg-soft-danger text-danger align-middle ms-1">Out
                                                                    of Delivery</span></h6>
                                                            <p class="text-muted mb-2">These customers can rest assured
                                                                their order has been placed.</p>
                                                            <small class="mb-0 text-muted">16 Nov</small>
                                                        </div>
                                                    </div>
                                                    <div class="acitivity-item py-3 d-flex">
                                                        <div class="flex-shrink-0">
                                                            <img src="assets/images/users/avatar-7.jpg" alt=""
                                                                class="avatar-xs rounded-circle acitivity-avatar" />
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">Lewis Pratt</h6>
                                                            <p class="text-muted mb-2">They all have something to say
                                                                beyond the words on the page. They can come across as
                                                                casual or neutral, exotic or graphic. </p>
                                                            <small class="mb-0 text-muted">22 Oct</small>
                                                        </div>
                                                    </div>
                                                    <div class="acitivity-item py-3 d-flex">
                                                        <div class="flex-shrink-0">
                                                            <div class="avatar-xs acitivity-avatar">
                                                                <div
                                                                    class="avatar-title rounded-circle bg-soft-info text-info">
                                                                    <i class="ri-line-chart-line"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">Monthly sales report</h6>
                                                            <p class="text-muted mb-2"><span class="text-danger">2 days
                                                                    left</span> notification to submit the monthly sales
                                                                report. <a href="javascript:void(0);"
                                                                    class="link-warning text-decoration-underline">Reports
                                                                    Builder</a></p>
                                                            <small class="mb-0 text-muted">15 Oct</small>
                                                        </div>
                                                    </div>
                                                    <div class="acitivity-item d-flex">
                                                        <div class="flex-shrink-0">
                                                            <img src="assets/images/users/avatar-8.jpg" alt=""
                                                                class="avatar-xs rounded-circle acitivity-avatar" />
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-1">New ticket received <span
                                                                    class="badge bg-soft-success text-success align-middle">Completed</span>
                                                            </h6>
                                                            <p class="text-muted mb-2">User <span
                                                                    class="text-secondary">Erica245</span> submitted a
                                                                ticket.</p>
                                                            <small class="mb-0 text-muted">26 Aug</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end card-body-->
                                        </div>
                                        <!--end card-->
                                    </div>
                                    <!--end tab-pane-->
                                    <div class="tab-pane fade" id="projects" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none profile-project-warning">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">Chat App Update</a>
                                                                        </h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">2 year
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-warning fs-10">
                                                                            Inprogress</div>
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-1.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-3.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <div
                                                                                            class="avatar-title rounded-circle bg-light text-primary">
                                                                                            J
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- end card body -->
                                                        </div>
                                                        <!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none profile-project-success">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">ABC Project
                                                                                Customization</a></h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">2 month
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-primary fs-10">
                                                                            Progress</div>
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-8.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-7.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-6.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <div
                                                                                            class="avatar-title rounded-circle bg-primary">
                                                                                            2+
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- end card body -->
                                                        </div>
                                                        <!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none profile-project-info">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">Client - Frank
                                                                                Hook</a></h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">1 hr
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-info fs-10">New
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-4.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <div
                                                                                            class="avatar-title rounded-circle bg-light text-primary">
                                                                                            M
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-3.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- end card body -->
                                                        </div>
                                                        <!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none profile-project-primary">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">Velzon Project</a>
                                                                        </h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">11 hr
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-success fs-10">
                                                                            Completed</div>
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-7.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-5.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- end card body -->
                                                        </div>
                                                        <!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none profile-project-danger">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">Brand Logo Design</a>
                                                                        </h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">10 min
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-info fs-10">New
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-7.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-6.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <div
                                                                                            class="avatar-title rounded-circle bg-light text-primary">
                                                                                            E
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- end card body -->
                                                        </div>
                                                        <!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none profile-project-primary">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">Chat App</a></h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">8 hr
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-warning fs-10">
                                                                            Inprogress</div>
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <div
                                                                                            class="avatar-title rounded-circle bg-light text-primary">
                                                                                            R
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-3.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-8.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- end card body -->
                                                        </div>
                                                        <!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none profile-project-warning">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">Project Update</a>
                                                                        </h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">48 min
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-warning fs-10">
                                                                            Inprogress</div>
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-6.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-5.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-4.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- end card body -->
                                                        </div>
                                                        <!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none profile-project-success">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">Client - Jennifer</a>
                                                                        </h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">30 min
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-primary fs-10">
                                                                            Process</div>
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-1.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- end card body -->
                                                        </div>
                                                        <!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none mb-xxl-0   profile-project-info">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">Bsuiness Template -
                                                                                UI/UX design</a></h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">7 month
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-success fs-10">
                                                                            Completed</div>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-2.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-3.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-4.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <div
                                                                                            class="avatar-title rounded-circle bg-primary">
                                                                                            2+
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- end card body -->
                                                        </div>
                                                        <!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none mb-xxl-0  profile-project-success">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">Update Project</a>
                                                                        </h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">1 month
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-info fs-10">New
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-7.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <div
                                                                                            class="avatar-title rounded-circle bg-light text-primary">
                                                                                            A
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div><!-- end card body -->
                                                        </div><!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none mb-sm-0  profile-project-danger">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">Bank Management
                                                                                System</a></h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">10 month
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-success fs-10">
                                                                            Completed</div>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-7.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-6.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-5.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <div
                                                                                            class="avatar-title rounded-circle bg-primary">
                                                                                            2+
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div><!-- end card body -->
                                                        </div><!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-xxl-3 col-sm-6">
                                                        <div
                                                            class="card profile-project-card shadow-none mb-0  profile-project-primary">
                                                            <div class="card-body p-4">
                                                                <div class="d-flex">
                                                                    <div class="flex-grow-1 text-muted overflow-hidden">
                                                                        <h5 class="fs-14 text-truncate"><a href="#"
                                                                                class="text-dark">PSD to HTML
                                                                                Convert</a></h5>
                                                                        <p class="text-muted text-truncate mb-0">Last
                                                                            Update : <span
                                                                                class="fw-semibold text-dark">29 min
                                                                                Ago</span></p>
                                                                    </div>
                                                                    <div class="flex-shrink-0 ms-2">
                                                                        <div class="badge badge-soft-info fs-10">New
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex mt-4">
                                                                    <div class="flex-grow-1">
                                                                        <div class="d-flex align-items-center gap-2">
                                                                            <div>
                                                                                <h5 class="fs-12 text-muted mb-0">
                                                                                    Members :</h5>
                                                                            </div>
                                                                            <div class="avatar-group">
                                                                                <div class="avatar-group-item">
                                                                                    <div class="avatar-xs">
                                                                                        <img src="assets/images/users/avatar-7.jpg"
                                                                                            alt=""
                                                                                            class="rounded-circle img-fluid" />
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div><!-- end card body -->
                                                        </div><!-- end card -->
                                                    </div>
                                                    <!--end col-->
                                                    <div class="col-lg-12">
                                                        <div class="mt-4">
                                                            <ul
                                                                class="pagination pagination-separated justify-content-center mb-0">
                                                                <li class="page-item disabled">
                                                                    <a href="javascript:void(0);" class="page-link"><i
                                                                            class="mdi mdi-chevron-left"></i></a>
                                                                </li>
                                                                <li class="page-item active">
                                                                    <a href="javascript:void(0);"
                                                                        class="page-link">1</a>
                                                                </li>
                                                                <li class="page-item">
                                                                    <a href="javascript:void(0);"
                                                                        class="page-link">2</a>
                                                                </li>
                                                                <li class="page-item">
                                                                    <a href="javascript:void(0);"
                                                                        class="page-link">3</a>
                                                                </li>
                                                                <li class="page-item">
                                                                    <a href="javascript:void(0);"
                                                                        class="page-link">4</a>
                                                                </li>
                                                                <li class="page-item">
                                                                    <a href="javascript:void(0);"
                                                                        class="page-link">5</a>
                                                                </li>
                                                                <li class="page-item">
                                                                    <a href="javascript:void(0);" class="page-link"><i
                                                                            class="mdi mdi-chevron-right"></i></a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--end row-->
                                            </div>
                                            <!--end card-body-->
                                        </div>
                                        <!--end card-->
                                    </div>
                                    <!--end tab-pane-->
                                    <div class="tab-pane fade" id="documents" role="tabpanel">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-4">
                                                    <h5 class="card-title flex-grow-1 mb-0">Documents</h5>
                                                    <div class="flex-shrink-0">
                                                        <input class="form-control d-none" type="file" id="formFile">
                                                        <label for="formFile" class="btn btn-danger"><i
                                                                class="ri-upload-2-fill me-1 align-bottom"></i> Upload
                                                            File</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="table-responsive">
                                                            <table class="table table-borderless align-middle mb-0">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th scope="col">File Name</th>
                                                                        <th scope="col">Type</th>
                                                                        <th scope="col">Size</th>
                                                                        <th scope="col">Upload Date</th>
                                                                        <th scope="col">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <div class="avatar-sm">
                                                                                    <div
                                                                                        class="avatar-title bg-soft-primary text-primary rounded fs-20">
                                                                                        <i class="ri-file-zip-fill"></i>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="ms-3 flex-grow-1">
                                                                                    <h6 class="fs-15 mb-0"><a
                                                                                            href="javascript:void(0)">Artboard-documents.zip</a>
                                                                                    </h6>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>Zip File</td>
                                                                        <td>4.57 MB</td>
                                                                        <td>12 Dec 2021</td>
                                                                        <td>
                                                                            <div class="dropdown">
                                                                                <a href="javascript:void(0);"
                                                                                    class="btn btn-light btn-icon"
                                                                                    id="dropdownMenuLink15"
                                                                                    data-bs-toggle="dropdown"
                                                                                    aria-expanded="true">
                                                                                    <i class="ri-equalizer-fill"></i>
                                                                                </a>
                                                                                <ul class="dropdown-menu dropdown-menu-end"
                                                                                    aria-labelledby="dropdownMenuLink15">
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-eye-fill me-2 align-middle text-muted"></i>View</a>
                                                                                    </li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-download-2-fill me-2 align-middle text-muted"></i>Download</a>
                                                                                    </li>
                                                                                    <li class="dropdown-divider"></li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-delete-bin-5-line me-2 align-middle text-muted"></i>Delete</a>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <div class="avatar-sm">
                                                                                    <div
                                                                                        class="avatar-title bg-soft-danger text-danger rounded fs-20">
                                                                                        <i class="ri-file-pdf-fill"></i>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="ms-3 flex-grow-1">
                                                                                    <h6 class="fs-15 mb-0"><a
                                                                                            href="javascript:void(0);">Bank
                                                                                            Management System</a></h6>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>PDF File</td>
                                                                        <td>8.89 MB</td>
                                                                        <td>24 Nov 2021</td>
                                                                        <td>
                                                                            <div class="dropdown">
                                                                                <a href="javascript:void(0);"
                                                                                    class="btn btn-light btn-icon"
                                                                                    id="dropdownMenuLink3"
                                                                                    data-bs-toggle="dropdown"
                                                                                    aria-expanded="true">
                                                                                    <i class="ri-equalizer-fill"></i>
                                                                                </a>
                                                                                <ul class="dropdown-menu dropdown-menu-end"
                                                                                    aria-labelledby="dropdownMenuLink3">
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-eye-fill me-2 align-middle text-muted"></i>View</a>
                                                                                    </li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-download-2-fill me-2 align-middle text-muted"></i>Download</a>
                                                                                    </li>
                                                                                    <li class="dropdown-divider"></li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-delete-bin-5-line me-2 align-middle text-muted"></i>Delete</a>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <div class="avatar-sm">
                                                                                    <div
                                                                                        class="avatar-title bg-soft-secondary text-secondary rounded fs-20">
                                                                                        <i class="ri-video-line"></i>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="ms-3 flex-grow-1">
                                                                                    <h6 class="fs-15 mb-0"><a
                                                                                            href="javascript:void(0);">Tour-video.mp4</a>
                                                                                    </h6>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>MP4 File</td>
                                                                        <td>14.62 MB</td>
                                                                        <td>19 Nov 2021</td>
                                                                        <td>
                                                                            <div class="dropdown">
                                                                                <a href="javascript:void(0);"
                                                                                    class="btn btn-light btn-icon"
                                                                                    id="dropdownMenuLink4"
                                                                                    data-bs-toggle="dropdown"
                                                                                    aria-expanded="true">
                                                                                    <i class="ri-equalizer-fill"></i>
                                                                                </a>
                                                                                <ul class="dropdown-menu dropdown-menu-end"
                                                                                    aria-labelledby="dropdownMenuLink4">
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-eye-fill me-2 align-middle text-muted"></i>View</a>
                                                                                    </li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-download-2-fill me-2 align-middle text-muted"></i>Download</a>
                                                                                    </li>
                                                                                    <li class="dropdown-divider"></li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-delete-bin-5-line me-2 align-middle text-muted"></i>Delete</a>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <div class="avatar-sm">
                                                                                    <div
                                                                                        class="avatar-title bg-soft-success text-success rounded fs-20">
                                                                                        <i
                                                                                            class="ri-file-excel-fill"></i>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="ms-3 flex-grow-1">
                                                                                    <h6 class="fs-15 mb-0"><a
                                                                                            href="javascript:void(0);">Account-statement.xsl</a>
                                                                                    </h6>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>XSL File</td>
                                                                        <td>2.38 KB</td>
                                                                        <td>14 Nov 2021</td>
                                                                        <td>
                                                                            <div class="dropdown">
                                                                                <a href="javascript:void(0);"
                                                                                    class="btn btn-light btn-icon"
                                                                                    id="dropdownMenuLink5"
                                                                                    data-bs-toggle="dropdown"
                                                                                    aria-expanded="true">
                                                                                    <i class="ri-equalizer-fill"></i>
                                                                                </a>
                                                                                <ul class="dropdown-menu dropdown-menu-end"
                                                                                    aria-labelledby="dropdownMenuLink5">
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-eye-fill me-2 align-middle text-muted"></i>View</a>
                                                                                    </li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-download-2-fill me-2 align-middle text-muted"></i>Download</a>
                                                                                    </li>
                                                                                    <li class="dropdown-divider"></li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-delete-bin-5-line me-2 align-middle text-muted"></i>Delete</a>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <div class="avatar-sm">
                                                                                    <div
                                                                                        class="avatar-title bg-soft-info text-info rounded fs-20">
                                                                                        <i class="ri-folder-line"></i>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="ms-3 flex-grow-1">
                                                                                    <h6 class="fs-15 mb-0"><a
                                                                                            href="javascript:void(0);">Project
                                                                                            Screenshots Collection</a>
                                                                                    </h6>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>Floder File</td>
                                                                        <td>87.24 MB</td>
                                                                        <td>08 Nov 2021</td>
                                                                        <td>
                                                                            <div class="dropdown">
                                                                                <a href="javascript:void(0);"
                                                                                    class="btn btn-light btn-icon"
                                                                                    id="dropdownMenuLink6"
                                                                                    data-bs-toggle="dropdown"
                                                                                    aria-expanded="true">
                                                                                    <i class="ri-equalizer-fill"></i>
                                                                                </a>
                                                                                <ul class="dropdown-menu dropdown-menu-end"
                                                                                    aria-labelledby="dropdownMenuLink6">
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-eye-fill me-2 align-middle"></i>View</a>
                                                                                    </li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-download-2-fill me-2 align-middle"></i>Download</a>
                                                                                    </li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-delete-bin-5-line me-2 align-middle"></i>Delete</a>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <div class="avatar-sm">
                                                                                    <div
                                                                                        class="avatar-title bg-soft-danger text-danger rounded fs-20">
                                                                                        <i class="ri-image-2-fill"></i>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="ms-3 flex-grow-1">
                                                                                    <h6 class="fs-15 mb-0"><a
                                                                                            href="javascript:void(0);">Velzon-logo.png</a>
                                                                                    </h6>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>PNG File</td>
                                                                        <td>879 KB</td>
                                                                        <td>02 Nov 2021</td>
                                                                        <td>
                                                                            <div class="dropdown">
                                                                                <a href="javascript:void(0);"
                                                                                    class="btn btn-light btn-icon"
                                                                                    id="dropdownMenuLink7"
                                                                                    data-bs-toggle="dropdown"
                                                                                    aria-expanded="true">
                                                                                    <i class="ri-equalizer-fill"></i>
                                                                                </a>
                                                                                <ul class="dropdown-menu dropdown-menu-end"
                                                                                    aria-labelledby="dropdownMenuLink7">
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-eye-fill me-2 align-middle"></i>View</a>
                                                                                    </li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-download-2-fill me-2 align-middle"></i>Download</a>
                                                                                    </li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-delete-bin-5-line me-2 align-middle"></i>Delete</a>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        <div class="text-center mt-3">
                                                            <a href="javascript:void(0);" class="text-success "><i
                                                                    class="mdi mdi-loading mdi-spin fs-20 align-middle me-2"></i>
                                                                Load more </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
    </body>

</html>