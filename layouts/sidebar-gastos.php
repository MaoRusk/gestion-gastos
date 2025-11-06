<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="dashboard-gastos.php" class="logo logo-dark">
            <span class="logo-sm">
                <img src="assets/images/oso-logo.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="assets/images/fime.png" alt="" height="80">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="dashboard-gastos.php" class="logo logo-light">
            <span class="logo-sm">
                <img src="assets/images/oso-logo.png" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="assets/images/fime.png" alt="" height="80">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>Menú Principal</span></li>
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="dashboard-gastos.php">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>

                <!-- Gestión de Cuentas -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarCuentas" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarCuentas">
                        <i class="ri-bank-line"></i> <span>Cuentas Bancarias</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarCuentas">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="cuentas-lista.php" class="nav-link">Mis Cuentas</a>
                            </li>
                            <li class="nav-item">
                                <a href="cuentas-agregar.php" class="nav-link">Agregar Cuenta</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Gestión de Transacciones -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarTransacciones" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarTransacciones">
                        <i class="ri-exchange-line"></i> <span>Transacciones</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarTransacciones">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="transacciones-lista.php" class="nav-link">Lista de Transacciones</a>
                            </li>
                            <li class="nav-item">
                                <a href="transacciones-agregar.php" class="nav-link">Nueva Transacción</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Categorías -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarCategorias" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarCategorias">
                        <i class="ri-folder-line"></i> <span>Categorías</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarCategorias">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="categorias-lista.php" class="nav-link">Gestionar Categorías</a>
                            </li>
                            <li class="nav-item">
                                <a href="categorias-agregar.php" class="nav-link">Nueva Categoría</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Presupuestos -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarPresupuestos" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarPresupuestos">
                        <i class="ri-money-dollar-circle-line"></i> <span>Presupuestos</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarPresupuestos">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="presupuestos-lista.php" class="nav-link">Mis Presupuestos</a>
                            </li>
                            <li class="nav-item">
                                <a href="presupuestos-agregar.php" class="nav-link">Nuevo Presupuesto</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Reportes -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarReportes" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarReportes">
                        <i class="ri-bar-chart-line"></i> <span>Reportes</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarReportes">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="reportes.php" class="nav-link">Análisis y Reportes</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Gestión de Usuarios (solo administradores) -->
                <?php if (function_exists('isAdmin') && isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="usuarios-lista.php">
                        <i class="ri-user-settings-line"></i> <span>Usuarios del Sistema</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Configuración del Sistema -->
                <!-- <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarConfig" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarConfig">
                        <i class="ri-settings-3-line"></i> <span>Configuración</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarConfig">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="pages-profile-settings.php" class="nav-link">Mi Perfil</a>
                            </li>
                            <li class="nav-item">
                                <a href="pages-profile.php" class="nav-link">Configuración</a>
                            </li>
                        </ul>
                    </div>
                </li> -->

                <li class="menu-title"><i class="ri-more-fill"></i> <span>Acciones Rápidas</span></li>

                <!-- Nueva Transacción Rápida -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="transacciones-agregar.php">
                        <i class="ri-add-circle-line"></i> <span>Nueva Transacción</span>
                    </a>
                </li>

                <!-- Nueva Cuenta Rápida -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="cuentas-agregar.php">
                        <i class="ri-bank-line"></i> <span>Nueva Cuenta</span>
                    </a>
                </li>

                <!-- <li class="menu-title"><i class="ri-more-fill"></i> <span>Acciones</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="logout.php">
                        <i class="ri-logout-box-line"></i> <span>Cerrar Sesión</span>
                    </a>
                </li> -->

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>