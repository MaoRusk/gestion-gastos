<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();

// Mode handling: add (default), edit, view
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'add';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$is_edit = ($mode === 'edit' && $id > 0);
$is_view = ($mode === 'view' && $id > 0);

// If editing or viewing, load the existing account and ensure ownership
if (($is_edit || $is_view) && $id > 0) {
    $sql = "SELECT * FROM cuentas_bancarias WHERE id = ? LIMIT 1";
    if (isset($link->pdo)) {
        $stmt = $link->pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
    }

    if (!$row) {
        // Not found
        header('Location: cuentas-lista.php');
        exit;
    }

    // Ownership check
    if ($row['usuario_id'] != getCurrentUserId()) {
        die('No tienes permiso para ver/editar esta cuenta');
    }

        // Prefill variables from DB when editing or viewing
        $nombre = $row['nombre'];
        // Map DB 'tipo' values to the form's option values when possible
        $raw_tipo = strtolower(trim($row['tipo'] ?? ''));
        $tipo_map = [
            'corriente' => 'cuenta_corriente',
            'cuenta_corriente' => 'cuenta_corriente',
            'ahorros' => 'cuenta_ahorros',
            'cuenta_ahorros' => 'cuenta_ahorros',
            'tarjeta_credito' => 'tarjeta_credito',
            'credito' => 'tarjeta_credito',
            'tarjeta' => 'tarjeta_credito',
            'efectivo' => 'efectivo',
            'inversion' => 'inversion',
            'inversión' => 'inversion'
        ];
        $tipo = isset($tipo_map[$raw_tipo]) ? $tipo_map[$raw_tipo] : ($row['tipo'] ?? '');
        $banco = $row['banco'];
        $numero_cuenta = $row['numero_cuenta'];
        // Use balance_actual as balance_inicial for display
        $balance_inicial = isset($row['balance_actual']) ? $row['balance_actual'] : '';
        $limite_credito = $row['limite_credito'];
    }
}

// Define variables and initialize with empty values (preserve any prefilled values from DB)
$nombre = isset($nombre) ? $nombre : '';
$tipo = isset($tipo) ? $tipo : '';
$banco = isset($banco) ? $banco : '';
$numero_cuenta = isset($numero_cuenta) ? $numero_cuenta : '';
$balance_inicial = isset($balance_inicial) ? $balance_inicial : '';
$limite_credito = isset($limite_credito) ? $limite_credito : '';

$nombre_err = $tipo_err = $balance_inicial_err = "";
$success_message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate nombre
    if (empty(trim($_POST["nombre"]))) {
        $nombre_err = "Por favor ingresa el nombre de la cuenta.";
    } else {
        $nombre = sanitizeInput($_POST["nombre"]);
    }

    // Validate tipo
    if (empty($_POST["tipo"])) {
        $tipo_err = "Por favor selecciona el tipo de cuenta.";
    } else {
        $tipo = $_POST["tipo"];
    }

    // Validate balance_inicial
    if (empty($_POST["balance_inicial"])) {
        $balance_inicial_err = "Por favor ingresa el balance inicial.";
    } else {
        $balance_inicial = floatval($_POST["balance_inicial"]);
    }

    // Optional fields - banco and numero_cuenta are NOT NULL in DB, so provide defaults
    $banco = !empty($_POST["banco"]) ? sanitizeInput($_POST["banco"]) : 'N/A';
    $numero_cuenta = !empty($_POST["numero_cuenta"]) ? sanitizeInput($_POST["numero_cuenta"]) : 'N/A';

    if (isset($_POST["limite_credito"])) {
        $limite_credito = floatval($_POST["limite_credito"]);
    } else {
        $limite_credito = null;
    }
    $limite_credito = !empty($_POST["limite_credito"]) ? floatval($_POST["limite_credito"]) : null;

    // Check input errors before inserting/updating in database
    if (empty($nombre_err) && empty($tipo_err) && empty($balance_inicial_err)) {
        // Determine if this is an edit (mode=edit and id provided) coming from a POST hidden field
        $post_mode = isset($_POST['mode']) ? $_POST['mode'] : 'add';
        $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($post_mode === 'edit' && $post_id > 0) {
            // Update existing account (ownership checked earlier on GET; re-check here)
            $check_sql = "SELECT usuario_id FROM cuentas_bancarias WHERE id = ? LIMIT 1";
            if (isset($link->pdo)) {
                $cstmt = $link->pdo->prepare($check_sql);
                $cstmt->execute([$post_id]);
                $crow = $cstmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $cstmt = mysqli_prepare($link, $check_sql);
                mysqli_stmt_bind_param($cstmt, 'i', $post_id);
                mysqli_stmt_execute($cstmt);
                $cres = mysqli_stmt_get_result($cstmt);
                $crow = mysqli_fetch_assoc($cres);
                mysqli_stmt_close($cstmt);
            }

            if (!$crow || $crow['usuario_id'] != getCurrentUserId()) {
                die('No tienes permiso para editar esta cuenta');
            }

            $sql = "UPDATE cuentas_bancarias SET nombre = ?, tipo = ?, banco = ?, numero_cuenta = ?, balance_actual = ?, limite_credito = ? WHERE id = ?";
            if (isset($link->pdo)) {
                $ustmt = $link->pdo->prepare($sql);
                if ($ustmt->execute([$nombre, $tipo, $banco, $numero_cuenta, $balance_inicial, $limite_credito, $post_id])) {
                    $success_message = "Cuenta actualizada exitosamente!";
                } else {
                    $error_info = $ustmt->errorInfo();
                    $error_msg = isset($error_info[2]) ? $error_info[2] : 'Error desconocido';
                    $success_message = "Error al actualizar la cuenta: " . $error_msg;
                }
            } else {
                $ustmt = mysqli_prepare($link, $sql);
                mysqli_stmt_bind_param($ustmt, 'ssssdsi', $nombre, $tipo, $banco, $numero_cuenta, $balance_inicial, $limite_credito, $post_id);
                if (mysqli_stmt_execute($ustmt)) {
                    $success_message = "Cuenta actualizada exitosamente!";
                } else {
                    $error_msg = mysqli_error($link);
                    $success_message = "Error al actualizar la cuenta: " . $error_msg;
                }
                mysqli_stmt_close($ustmt);
            }
        } else {
            // Insert new account
            $sql = "INSERT INTO cuentas_bancarias (usuario_id, nombre, tipo, banco, numero_cuenta, balance_actual, limite_credito) VALUES (?, ?, ?, ?, ?, ?, ?)";

            if (isset($link->pdo)) {
                $stmt = $link->pdo->prepare($sql);
                $param_usuario_id = getCurrentUserId();
                if ($stmt->execute([$param_usuario_id, $nombre, $tipo, $banco, $numero_cuenta, $balance_inicial, $limite_credito])) {
                    $success_message = "Cuenta agregada exitosamente!";
                    // Clear form
                    $nombre = $tipo = $banco = $numero_cuenta = $balance_inicial = $limite_credito = "";
                } else {
                    $error_info = $stmt->errorInfo();
                    $error_msg = isset($error_info[2]) ? $error_info[2] : 'Error desconocido';
                    $success_message = "Error al agregar la cuenta: " . $error_msg;
                }
            } else {
                $stmt = mysqli_prepare($link, $sql);
                $param_usuario_id = getCurrentUserId();
                mysqli_stmt_bind_param($stmt, "issssdd", $param_usuario_id, $nombre, $tipo, $banco, $numero_cuenta, $balance_inicial, $limite_credito);
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Cuenta agregada exitosamente!";
                    // Clear form
                    $nombre = $tipo = $banco = $numero_cuenta = $balance_inicial = $limite_credito = "";
                } else {
                    $error_msg = mysqli_error($link);
                    $success_message = "Error al agregar la cuenta: " . $error_msg;
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Agregar Cuenta | FIME - Gestión de Gastos Personales</title>
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
                                <h4 class="mb-sm-0">Agregar Nueva Cuenta</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard-gastos.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="cuentas-lista.php">Cuentas</a></li>
                                        <li class="breadcrumb-item active">Agregar</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Información de la Cuenta</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($success_message)): ?>
                                        <div class="alert <?php echo strpos($success_message, 'exitosamente') !== false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                                            <?php echo $success_message; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                        <?php
                                        // Determine form action and read-only state
                                        $form_action = htmlspecialchars($_SERVER["PHP_SELF"]);
                                        $read_only = ($is_view);
                                        ?>

                                        <form action="<?php echo $form_action; ?>" method="post">
                                            <?php if ($is_edit || $is_view): ?>
                                                <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
                                                <input type="hidden" name="mode" value="<?php echo $is_edit ? 'edit' : 'view'; ?>">
                                            <?php endif; ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($nombre_err)) ? 'has-error' : ''; ?>">
                                                    <label for="nombre" class="form-label">Nombre de la Cuenta <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $nombre; ?>" placeholder="Ej: Cuenta Principal" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                    <span class="text-danger"><?php echo $nombre_err; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($tipo_err)) ? 'has-error' : ''; ?>">
                                                    <label for="tipo" class="form-label">Tipo de Cuenta <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="tipo" name="tipo" <?php echo $read_only ? 'disabled' : ''; ?> onchange="ocultarLimiteCredito(this.value)">
                                                        <option value="">Seleccionar tipo</option>
                                                        <option value="cuenta_corriente" <?php echo ($tipo == 'cuenta_corriente') ? 'selected' : ''; ?>>Cuenta Corriente</option>
                                                        <option value="cuenta_ahorros" <?php echo ($tipo == 'cuenta_ahorros') ? 'selected' : ''; ?>>Cuenta de Ahorros</option>
                                                        <option value="tarjeta_credito" <?php echo ($tipo == 'tarjeta_credito') ? 'selected' : ''; ?>>Tarjeta de Crédito</option>
                                                        <option value="prestamo_personal" <?php echo ($tipo == 'prestamo_personal') ? 'selected' : ''; ?>>Préstamo Personal</option>
                                                        <option value="efectivo" <?php echo ($tipo == 'efectivo') ? 'selected' : ''; ?>>Efectivo</option>
                                                    </select>
                                                    <span class="text-danger"><?php echo $tipo_err; ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="banco" class="form-label">Banco o Institución</label>
                                                    <input type="text" class="form-control" id="banco" name="banco" value="<?php echo $banco; ?>" placeholder="Ej: BBVA, Santander, HSBC" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="numero_cuenta" class="form-label">Número de Cuenta</label>
                                                    <input type="text" class="form-control" id="numero_cuenta" name="numero_cuenta" value="<?php echo $numero_cuenta; ?>" placeholder="Últimos 4 dígitos" <?php echo $read_only ? 'disabled' : ''; ?> maxlength="4">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($balance_inicial_err)) ? 'has-error' : ''; ?>">
                                                    <label for="balance_inicial" class="form-label">Balance Inicial <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" class="form-control" id="balance_inicial" name="balance_inicial" value="<?php echo $balance_inicial; ?>" placeholder="0.00" step="0.01" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                    </div>
                                                    <span class="text-danger"><?php echo $balance_inicial_err; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3" id="limite_credito_container" style="display: <?php echo in_array($tipo, ['tarjeta_credito', 'prestamo_personal']) ? 'block' : 'none'; ?>">
                                                    <label for="limite_credito" class="form-label" id="limite_credito_label"><?php echo $tipo === 'prestamo_personal' ? 'Monto Original del Préstamo' : 'Límite de Crédito'; ?></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" class="form-control" id="limite_credito" name="limite_credito" value="<?php echo $limite_credito; ?>" placeholder="0.00" step="0.01" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                    </div>
                                                    <small class="text-muted" id="limite_credito_help"><?php echo $tipo === 'prestamo_personal' ? 'Monto total que debes pagar' : 'Solo para tarjetas de crédito'; ?></small>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="text-end">
                                            <?php if (!$read_only): ?>
                                                <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Actualizar Cuenta' : 'Guardar Cuenta'; ?></button>
                                            <?php endif; ?>
                                            <a href="cuentas-lista.php" class="btn btn-soft-danger me-2">Cancelar</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Información</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info" role="alert">
                                        <h6 class="alert-heading">Tipos de Cuentas</h6>
                                        <p class="mb-0">Puedes agregar diferentes tipos de cuentas para tener un mejor control de tus finanzas:</p>
                                    </div>

                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="ri-bank-line text-primary me-2"></i>
                                            <strong>Cuenta Corriente:</strong> Para gastos diarios
                                        </li>
                                        <li class="mb-2">
                                            <i class="ri-bank-card-line text-warning me-2"></i>
                                            <strong>Tarjeta de Crédito:</strong> Para compras a crédito
                                        </li>
                                        <li class="mb-2">
                                            <i class="ri-money-dollar-circle-line text-success me-2"></i>
                                            <strong>Efectivo:</strong> Para dinero en efectivo
                                        </li>
                                        <li class="mb-2">
                                            <i class="ri-money-dollar-box-line text-info me-2"></i>
                                            <strong>Cuenta de Ahorros:</strong> Para ahorrar dinero
                                        </li>
                                        <li class="mb-2">
                                            <i class="ri-hand-coin-line text-danger me-2"></i>
                                            <strong>Préstamos Personales:</strong> Deudas a crédito
                                        </li>
                                    </ul>

                                    <div class="alert alert-warning" role="alert">
                                        <h6 class="alert-heading">Importante</h6>
                                        <p class="mb-0">El balance inicial puede ser negativo para tarjetas de crédito con deuda pendiente.</p>
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

<script>
    function ocultarLimiteCredito(tipo) {
        const container = document.getElementById('limite_credito_container');
        const label = document.getElementById('limite_credito_label');
        const help = document.getElementById('limite_credito_help');
        
        if (tipo === 'tarjeta_credito' || tipo === 'prestamo_personal') {
            container.style.display = 'block';
            if (tipo === 'prestamo_personal') {
                label.textContent = 'Monto Original del Préstamo';
                help.textContent = 'Monto total que debes pagar';
            } else {
                label.textContent = 'Límite de Crédito';
                help.textContent = 'Solo para tarjetas de crédito';
            }
        } else {
            container.style.display = 'none';
        }
    }
    
    // Inicializar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const tipoSelect = document.getElementById('tipo');
        if (tipoSelect) {
            ocultarLimiteCredito(tipoSelect.value);
        }
    });
</script>