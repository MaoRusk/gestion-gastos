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

// Define variables and initialize with empty values (preserve any prefilled values when loading an existing record)
$descripcion = isset($descripcion) ? $descripcion : '';
$monto = isset($monto) ? $monto : '';
$tipo = isset($tipo) ? $tipo : '';
$categoria_id = isset($categoria_id) ? $categoria_id : '';
$cuenta_id = isset($cuenta_id) ? $cuenta_id : '';
$fecha = isset($fecha) ? $fecha : date('Y-m-d');
$notas = isset($notas) ? $notas : '';
$recurrente = false;
$frecuencia = '';
$fecha_fin = '';
$descripcion_err = $monto_err = $tipo_err = $categoria_err = $cuenta_err = "";
$success_message = "";

// If editing or viewing, load the existing transaction and ensure ownership, then prefill vars
if (($is_edit || $is_view) && $id > 0) {
    $sql = "SELECT * FROM transacciones WHERE id = ? LIMIT 1";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if (!$row) {
            header('Location: transacciones-lista.php');
            exit;
        }

        if ($row['usuario_id'] != getCurrentUserId()) {
            die('No tienes permiso para ver/editar esta transacción');
        }

        // Prefill form values
        $descripcion = $row['descripcion'];
        $monto = $row['monto'];
        $tipo = $row['tipo'];
        $categoria_id = $row['categoria_id'];
        $cuenta_id = $row['cuenta_id'];
        $fecha = $row['fecha'];
        $notas = $row['notas'];
        $recurrente = !empty($row['recurrente']);
        $frecuencia = $row['frecuencia'];
        $fecha_fin = $row['fecha_fin_recurrencia'];
    }
}

// Get user's accounts and categories
$user_id = getCurrentUserId();

// Get accounts
$activeCondition = (defined('DB_TYPE') && DB_TYPE === 'postgresql') ? 'cb.activa = TRUE' : 'cb.activa = 1';
$sql_accounts = "SELECT id, nombre, tipo, balance_actual FROM cuentas_bancarias cb WHERE usuario_id = ? AND " . $activeCondition . " ORDER BY nombre";
$stmt = mysqli_prepare($link, $sql_accounts);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cuentas = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Get categories
$activeCondition2 = (defined('DB_TYPE') && DB_TYPE === 'postgresql') ? 'c.activa = TRUE' : 'c.activa = 1';
$predefCondition = (defined('DB_TYPE') && DB_TYPE === 'postgresql') ? 'c.es_predefinida = TRUE' : 'c.es_predefinida = 1';
$sql_categories = "SELECT id, nombre, tipo, color, icono FROM categorias c WHERE (usuario_id = ? OR " . $predefCondition . ") AND " . $activeCondition2 . " ORDER BY tipo, nombre";
$stmt = mysqli_prepare($link, $sql_categories);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$categorias = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Get recent transactions for the sidebar
$sql_recent = "SELECT t.*, c.nombre as categoria_nombre, c.color as categoria_color, c.icono as categoria_icono, 
               cb.nombre as cuenta_nombre
        FROM transacciones t
        LEFT JOIN categorias c ON t.categoria_id = c.id
        LEFT JOIN cuentas_bancarias cb ON t.cuenta_id = cb.id
        WHERE t.usuario_id = ?
        ORDER BY t.fecha DESC, t.fecha_creacion DESC
        LIMIT 3";
$stmt = mysqli_prepare($link, $sql_recent);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transacciones_recientes = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate descripcion
    if (empty(trim($_POST["descripcion"]))) {
        $descripcion_err = "Por favor ingresa una descripción.";
    } else {
        $descripcion = sanitizeInput($_POST["descripcion"]);
    }

    // Validate monto
    if (empty($_POST["monto"]) || floatval($_POST["monto"]) <= 0) {
        $monto_err = "Por favor ingresa un monto válido.";
    } else {
        $monto = floatval($_POST["monto"]);
    }

    // Validate tipo
    if (empty($_POST["tipo"])) {
        $tipo_err = "Por favor selecciona el tipo de transacción.";
    } else {
        $tipo = $_POST["tipo"];
    }

    // Validate categoria
    if (empty($_POST["categoria_id"])) {
        $categoria_err = "Por favor selecciona una categoría.";
    } else {
        $categoria_id = intval($_POST["categoria_id"]);
    }

    // Validate cuenta
    if (empty($_POST["cuenta_id"])) {
        $cuenta_err = "Por favor selecciona una cuenta.";
    } else {
        $cuenta_id = intval($_POST["cuenta_id"]);
    }

    // Validate fecha
    $fecha = !empty($_POST["fecha"]) ? $_POST["fecha"] : date('Y-m-d');
    $notas = !empty($_POST["notas"]) ? sanitizeInput($_POST["notas"]) : null;

    // Check input errors before inserting/updating in database
    if (empty($descripcion_err) && empty($monto_err) && empty($tipo_err) && empty($categoria_err) && empty($cuenta_err)) {
        $post_mode = isset($_POST['mode']) ? $_POST['mode'] : 'add';
        $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        // Start transaction
        mysqli_begin_transaction($link);

        try {
            $recurrente = isset($_POST["recurrente"]) ? 1 : 0;
            $frecuencia = !empty($_POST["frecuencia"]) ? $_POST["frecuencia"] : null;
            $fecha_fin = !empty($_POST["fecha_fin"]) ? $_POST["fecha_fin"] : null;

            if ($post_mode === 'edit' && $post_id > 0) {
                // Fetch original transaction to compute balance deltas
                $orig = null;
                if ($s = mysqli_prepare($link, "SELECT * FROM transacciones WHERE id = ? LIMIT 1")) {
                    mysqli_stmt_bind_param($s, 'i', $post_id);
                    mysqli_stmt_execute($s);
                    $r = mysqli_stmt_get_result($s);
                    $orig = mysqli_fetch_assoc($r);
                    mysqli_stmt_close($s);
                }

                if (!$orig || $orig['usuario_id'] != $user_id) {
                    throw new Exception('No tienes permiso para editar esta transacción');
                }

                // Update transacciones row
                $update_sql = "UPDATE transacciones SET cuenta_id = ?, categoria_id = ?, descripcion = ?, monto = ?, tipo = ?, fecha = ?, notas = ?, recurrente = ?, frecuencia = ?, fecha_fin_recurrencia = ? WHERE id = ?";
                if ($ust = mysqli_prepare($link, $update_sql)) {
                    mysqli_stmt_bind_param($ust, 'iisdsiisssi', $cuenta_id, $categoria_id, $descripcion, $monto, $tipo, $fecha, $notas, $recurrente, $frecuencia, $fecha_fin, $post_id);
                    mysqli_stmt_execute($ust);
                    mysqli_stmt_close($ust);
                }

                // Determinar si las cuentas son deudas
                $orig_account_type_query = "SELECT tipo FROM cuentas_bancarias WHERE id = ? LIMIT 1";
                $orig_account_stmt = mysqli_prepare($link, $orig_account_type_query);
                mysqli_stmt_bind_param($orig_account_stmt, "i", $orig['cuenta_id']);
                mysqli_stmt_execute($orig_account_stmt);
                $orig_account_result = mysqli_stmt_get_result($orig_account_stmt);
                $orig_account_data = mysqli_fetch_assoc($orig_account_result);
                mysqli_stmt_close($orig_account_stmt);
                
                $new_account_type_query = "SELECT tipo FROM cuentas_bancarias WHERE id = ? LIMIT 1";
                $new_account_stmt = mysqli_prepare($link, $new_account_type_query);
                mysqli_stmt_bind_param($new_account_stmt, "i", $cuenta_id);
                mysqli_stmt_execute($new_account_stmt);
                $new_account_result = mysqli_stmt_get_result($new_account_stmt);
                $new_account_data = mysqli_fetch_assoc($new_account_result);
                mysqli_stmt_close($new_account_stmt);
                
                $orig_is_debt = in_array($orig_account_data['tipo'] ?? '', ['tarjeta_credito', 'prestamo_personal']);
                $new_is_debt = in_array($new_account_data['tipo'] ?? '', ['tarjeta_credito', 'prestamo_personal']);
                
                // Compute balance adjustments considerando tipo de cuenta
                if ($orig_is_debt) {
                    $orig_change = ($orig['tipo'] == 'gasto') ? floatval($orig['monto']) : -floatval($orig['monto']);
                } else {
                    $orig_change = ($orig['tipo'] == 'ingreso') ? floatval($orig['monto']) : -floatval($orig['monto']);
                }
                
                if ($new_is_debt) {
                    $new_change = ($tipo == 'gasto') ? floatval($monto) : -floatval($monto);
                } else {
                    $new_change = ($tipo == 'ingreso') ? floatval($monto) : -floatval($monto);
                }

                if ($orig['cuenta_id'] == $cuenta_id) {
                    $delta = $new_change - $orig_change;
                    if ($delta != 0) {
                        $upd = mysqli_prepare($link, "UPDATE cuentas_bancarias SET balance_actual = balance_actual + ? WHERE id = ?");
                        mysqli_stmt_bind_param($upd, 'di', $delta, $cuenta_id);
                        mysqli_stmt_execute($upd);
                        mysqli_stmt_close($upd);
                    }
                } else {
                    // Revert original on old account
                    $revert = mysqli_prepare($link, "UPDATE cuentas_bancarias SET balance_actual = balance_actual - ? WHERE id = ?");
                    mysqli_stmt_bind_param($revert, 'di', $orig_change, $orig['cuenta_id']);
                    mysqli_stmt_execute($revert);
                    mysqli_stmt_close($revert);

                    // Apply new change to new account
                    $apply = mysqli_prepare($link, "UPDATE cuentas_bancarias SET balance_actual = balance_actual + ? WHERE id = ?");
                    mysqli_stmt_bind_param($apply, 'di', $new_change, $cuenta_id);
                    mysqli_stmt_execute($apply);
                    mysqli_stmt_close($apply);
                }

                // Note: transfer records table not updated here (out of scope)

                mysqli_commit($link);
                $success_message = "Transacción actualizada exitosamente!";

            } else {
                // Insert transaction
                $sql = "INSERT INTO transacciones (usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                if ($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "iiisdsissss", $user_id, $cuenta_id, $categoria_id, $descripcion, $monto, $tipo, $fecha, $notas, $recurrente, $frecuencia, $fecha_fin);
                    if (mysqli_stmt_execute($stmt)) {
                        $transaction_id = mysqli_insert_id($link);

                        // Determinar si la cuenta es una deuda (tarjeta_credito o prestamo_personal)
                        $account_type_query = "SELECT tipo FROM cuentas_bancarias WHERE id = ? LIMIT 1";
                        $account_stmt = mysqli_prepare($link, $account_type_query);
                        mysqli_stmt_bind_param($account_stmt, "i", $cuenta_id);
                        mysqli_stmt_execute($account_stmt);
                        $account_result = mysqli_stmt_get_result($account_stmt);
                        $account_data = mysqli_fetch_assoc($account_result);
                        mysqli_stmt_close($account_stmt);
                        
                        $is_debt_account = in_array($account_data['tipo'] ?? '', ['tarjeta_credito', 'prestamo_personal']);
                        
                        // Lógica de actualización de balance:
                        // - Para cuentas normales: ingreso aumenta, gasto disminuye
                        // - Para cuentas de deuda: ingreso reduce deuda, gasto aumenta deuda
                        //   PERO si es un "gasto" en una deuda, normalmente es un pago que reduce la deuda
                        if ($is_debt_account) {
                            // En cuentas de deuda, un "gasto" es realmente un pago que reduce la deuda
                            // Un "ingreso" sería un cargo que aumenta la deuda
                            $balance_change = ($tipo == 'gasto') ? $monto : -$monto;
                        } else {
                            // En cuentas normales, lógica estándar
                            $balance_change = ($tipo == 'ingreso') ? $monto : -$monto;
                        }
                        
                        $update_balance = "UPDATE cuentas_bancarias SET balance_actual = balance_actual + ? WHERE id = ?";
                        $stmt2 = mysqli_prepare($link, $update_balance);
                        mysqli_stmt_bind_param($stmt2, "di", $balance_change, $cuenta_id);
                        mysqli_stmt_execute($stmt2);
                        mysqli_stmt_close($stmt2);

                        // If it's a transfer, handle the destination account
                        if ($tipo == 'transferencia' && !empty($_POST["cuenta_destino_id"])) {
                            $cuenta_destino_id = intval($_POST["cuenta_destino_id"]);
                            $update_dest_balance = "UPDATE cuentas_bancarias SET balance_actual = balance_actual + ? WHERE id = ?";
                            $stmt3 = mysqli_prepare($link, $update_dest_balance);
                            mysqli_stmt_bind_param($stmt3, "di", $monto, $cuenta_destino_id);
                            mysqli_stmt_execute($stmt3);
                            mysqli_stmt_close($stmt3);

                            $insert_transfer = "INSERT INTO transferencias (usuario_id, cuenta_origen_id, cuenta_destino_id, monto, descripcion, fecha) VALUES (?, ?, ?, ?, ?, ?)";
                            $stmt4 = mysqli_prepare($link, $insert_transfer);
                            mysqli_stmt_bind_param($stmt4, "iiisss", $user_id, $cuenta_id, $cuenta_destino_id, $monto, $descripcion, $fecha);
                            mysqli_stmt_execute($stmt4);
                            mysqli_stmt_close($stmt4);
                        }

                        mysqli_commit($link);
                        $success_message = "Transacción agregada exitosamente!";

                        // Clear form
                        $descripcion = $monto = $tipo = $categoria_id = $cuenta_id = $notas = "";
                        $fecha = date('Y-m-d');
                    } else {
                        throw new Exception("Error al insertar transacción: " . mysqli_error($link));
                    }
                    mysqli_stmt_close($stmt);
                }
            }

        } catch (Exception $e) {
            mysqli_rollback($link);
            $success_message = "Error: " . $e->getMessage();
        }
    }
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Nueva Transacción | FIME - Gestión de Gastos Personales</title>
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
                                <h4 class="mb-sm-0">Nueva Transacción</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard-gastos.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="transacciones-lista.php">Transacciones</a></li>
                                        <li class="breadcrumb-item active">Nueva</li>
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
                                    <h5 class="card-title mb-0">Información de la Transacción</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($success_message)): ?>
                                        <div class="alert <?php echo strpos($success_message, 'exitosamente') !== false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                                            <?php echo $success_message; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php
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
                                                <div class="mb-3 <?php echo (!empty($tipo_err)) ? 'has-error' : ''; ?>">
                                                    <label for="tipo" class="form-label">Tipo de Transacción <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="tipo" name="tipo" onchange="toggleTipoTransaccion()" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                        <option value="">Seleccionar tipo</option>
                                                        <option value="ingreso" <?php echo ($tipo == 'ingreso') ? 'selected' : ''; ?>>Ingreso</option>
                                                        <option value="gasto" <?php echo ($tipo == 'gasto') ? 'selected' : ''; ?>>Gasto</option>
                                                        <option value="transferencia" <?php echo ($tipo == 'transferencia') ? 'selected' : ''; ?>>Transferencia</option>
                                                    </select>
                                                    <span class="text-danger"><?php echo $tipo_err; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($monto_err)) ? 'has-error' : ''; ?>">
                                                    <label for="monto" class="form-label">Monto <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" class="form-control" id="monto" name="monto" value="<?php echo $monto; ?>" placeholder="0.00" step="0.01" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                    </div>
                                                    <span class="text-danger"><?php echo $monto_err; ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($descripcion_err)) ? 'has-error' : ''; ?>">
                                                    <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="descripcion" name="descripcion" value="<?php echo $descripcion; ?>" placeholder="Ej: Compra en supermercado" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                    <span class="text-danger"><?php echo $descripcion_err; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($categoria_err)) ? 'has-error' : ''; ?>">
                                                    <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="categoria_id" name="categoria_id" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                        <option value="">Seleccionar categoría</option>
                                                        <?php 
                                                        $current_tipo = '';
                                                        foreach ($categorias as $categoria): 
                                                            if ($categoria['tipo'] != $current_tipo):
                                                                if ($current_tipo != '') echo '</optgroup>';
                                                                echo '<optgroup label="' . ucfirst($categoria['tipo']) . 's">';
                                                                $current_tipo = $categoria['tipo'];
                                                            endif;
                                                        ?>
                                                            <option value="<?php echo $categoria['id']; ?>" <?php echo ($categoria_id == $categoria['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                            </option>
                                                        <?php endforeach; 
                                                        if ($current_tipo != '') echo '</optgroup>';
                                                        ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo $categoria_err; ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($cuenta_err)) ? 'has-error' : ''; ?>">
                                                    <label for="cuenta_id" class="form-label">Cuenta <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="cuenta_id" name="cuenta_id" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                        <option value="">Seleccionar cuenta</option>
                                                        <?php foreach ($cuentas as $cuenta): ?>
                                                            <option value="<?php echo $cuenta['id']; ?>" <?php echo ($cuenta_id == $cuenta['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($cuenta['nombre']); ?> - $<?php echo number_format($cuenta['balance_actual'], 2); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo $cuenta_err; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Campos para transferencia -->
                                        <div id="transferenciaFields" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="cuenta_destino_id" class="form-label">Cuenta de Destino</label>
                                                        <select class="form-select" id="cuenta_destino_id" name="cuenta_destino_id" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                            <option value="">Seleccionar cuenta destino</option>
                                                            <?php foreach ($cuentas as $cuenta): ?>
                                                                <option value="<?php echo $cuenta['id']; ?>">
                                                                    <?php echo htmlspecialchars($cuenta['nombre']); ?> - $<?php echo number_format($cuenta['balance_actual'], 2); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="notas" class="form-label">Notas</label>
                                            <textarea class="form-control" id="notas" name="notas" rows="3" placeholder="Información adicional sobre la transacción" <?php echo $read_only ? 'disabled' : ''; ?>><?php echo $notas; ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="recurrente" name="recurrente" <?php echo ($recurrente ? 'checked' : ''); ?> <?php echo $read_only ? 'disabled' : ''; ?>>
                                                <label class="form-check-label" for="recurrente">
                                                    Transacción recurrente
                                                </label>
                                            </div>
                                        </div>

                                        <div id="recurrenciaFields" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="frecuencia" class="form-label">Frecuencia</label>
                                                        <select class="form-select" id="frecuencia" name="frecuencia" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                            <option value="semanal">Semanal</option>
                                                            <option value="mensual">Mensual</option>
                                                            <option value="anual">Anual</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <?php if (!$read_only): ?>
                                                <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Actualizar Transacción' : 'Guardar Transacción'; ?></button>
                                            <?php endif; ?>
                                            <a href="transacciones-lista.php" class="btn btn-soft-danger me-2">Cancelar</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Resumen</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info" role="alert">
                                        <h6 class="alert-heading">Tipos de Transacciones</h6>
                                        <ul class="mb-0">
                                            <li><strong>Ingreso:</strong> Dinero que recibes</li>
                                            <li><strong>Gasto:</strong> Dinero que gastas</li>
                                            <li><strong>Transferencia:</strong> Movimiento entre cuentas</li>
                                        </ul>
                                    </div>

                                    <div class="alert alert-warning" role="alert">
                                        <h6 class="alert-heading">Consejos</h6>
                                        <ul class="mb-0">
                                            <li>Usa descripciones claras</li>
                                            <li>Selecciona la categoría correcta</li>
                                            <li>Verifica el monto antes de guardar</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Transacciones Recientes -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Transacciones Recientes</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($transacciones_recientes)): ?>
                                        <div class="text-center py-3">
                                            <div class="text-muted">
                                                <i class="ri-file-list-line fs-24 text-muted mb-2"></i>
                                                <p class="mb-0 small">No hay transacciones recientes</p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php 
                                        $tipo_colors = [
                                            'ingreso' => 'success',
                                            'gasto' => 'danger',
                                            'transferencia' => 'info'
                                        ];
                                        
                                        $tipo_icons = [
                                            'ingreso' => 'ri-arrow-up-line',
                                            'gasto' => 'ri-arrow-down-line',
                                            'transferencia' => 'ri-exchange-line'
                                        ];
                                        
                                        foreach ($transacciones_recientes as $index => $trans): 
                                            $monto_class = $trans['tipo'] == 'ingreso' ? 'text-success' : ($trans['tipo'] == 'transferencia' ? 'text-info' : 'text-danger');
                                            $monto_prefix = $trans['tipo'] == 'ingreso' ? '+' : '-';
                                            $categoria_color = $trans['categoria_color'] ?: '#6c757d';
                                            $categoria_icono = $trans['categoria_icono'] ?: 'ri-file-list-line';
                                            $is_last = ($index === count($transacciones_recientes) - 1);
                                        ?>
                                            <div class="d-flex align-items-center <?php echo $is_last ? 'mb-0' : 'mb-3'; ?>">
                                                <div class="flex-shrink-0 me-2">
                                                    <div class="avatar-xs">
                                                        <span class="avatar-title rounded" style="background-color: <?php echo $categoria_color; ?>20; color: <?php echo $categoria_color; ?>">
                                                            <i class="<?php echo $categoria_icono; ?>"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($trans['descripcion']); ?></h6>
                                                    <small class="text-muted"><?php echo date('d M Y', strtotime($trans['fecha'])); ?></small>
                                                </div>
                                                <div class="fw-semibold <?php echo $monto_class; ?>">
                                                    <?php echo $monto_prefix; ?>$<?php echo number_format($trans['monto'], 2); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="text-center mt-3">
                                            <a href="transacciones-lista.php" class="btn btn-soft-primary btn-sm">
                                                Ver todas las transacciones
                                            </a>
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
        function toggleTipoTransaccion() {
            const tipo = document.getElementById('tipo').value;
            const transferenciaFields = document.getElementById('transferenciaFields');
            
            if (tipo === 'transferencia') {
                transferenciaFields.style.display = 'block';
            } else {
                transferenciaFields.style.display = 'none';
            }
        }

        document.getElementById('recurrente').addEventListener('change', function() {
            const recurrenciaFields = document.getElementById('recurrenciaFields');
            if (this.checked) {
                recurrenciaFields.style.display = 'block';
            } else {
                recurrenciaFields.style.display = 'none';
            }
        });
    </script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>

</html>
