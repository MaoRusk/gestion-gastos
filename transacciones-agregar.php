<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();

// Define variables and initialize with empty values
$descripcion = $monto = $tipo = $categoria_id = $cuenta_id = $fecha = $notas = "";
$descripcion_err = $monto_err = $tipo_err = $categoria_err = $cuenta_err = "";
$success_message = "";

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

    // Check input errors before inserting in database
    if (empty($descripcion_err) && empty($monto_err) && empty($tipo_err) && empty($categoria_err) && empty($cuenta_err)) {
        
        // Start transaction
        mysqli_begin_transaction($link);
        
        try {
            // Insert transaction
            $sql = "INSERT INTO transacciones (usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $recurrente = isset($_POST["recurrente"]) ? 1 : 0;
            $frecuencia = !empty($_POST["frecuencia"]) ? $_POST["frecuencia"] : null;
            $fecha_fin = !empty($_POST["fecha_fin"]) ? $_POST["fecha_fin"] : null;
            
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "iiisdsissss", $user_id, $cuenta_id, $categoria_id, $descripcion, $monto, $tipo, $fecha, $notas, $recurrente, $frecuencia, $fecha_fin);
                
                if (mysqli_stmt_execute($stmt)) {
                    $transaction_id = mysqli_insert_id($link);
                    
                    // Update account balance
                    $balance_change = ($tipo == 'ingreso') ? $monto : -$monto;
                    $update_balance = "UPDATE cuentas_bancarias SET balance_actual = balance_actual + ? WHERE id = ?";
                    $stmt2 = mysqli_prepare($link, $update_balance);
                    mysqli_stmt_bind_param($stmt2, "di", $balance_change, $cuenta_id);
                    mysqli_stmt_execute($stmt2);
                    mysqli_stmt_close($stmt2);
                    
                    // If it's a transfer, handle the destination account
                    if ($tipo == 'transferencia' && !empty($_POST["cuenta_destino_id"])) {
                        $cuenta_destino_id = intval($_POST["cuenta_destino_id"]);
                        
                        // Add to destination account
                        $update_dest_balance = "UPDATE cuentas_bancarias SET balance_actual = balance_actual + ? WHERE id = ?";
                        $stmt3 = mysqli_prepare($link, $update_dest_balance);
                        mysqli_stmt_bind_param($stmt3, "di", $monto, $cuenta_destino_id);
                        mysqli_stmt_execute($stmt3);
                        mysqli_stmt_close($stmt3);
                        
                        // Insert transfer record
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
                                    
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($tipo_err)) ? 'has-error' : ''; ?>">
                                                    <label for="tipo" class="form-label">Tipo de Transacción <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="tipo" name="tipo" onchange="toggleTipoTransaccion()">
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
                                                        <input type="number" class="form-control" id="monto" name="monto" value="<?php echo $monto; ?>" placeholder="0.00" step="0.01">
                                                    </div>
                                                    <span class="text-danger"><?php echo $monto_err; ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($descripcion_err)) ? 'has-error' : ''; ?>">
                                                    <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="descripcion" name="descripcion" value="<?php echo $descripcion; ?>" placeholder="Ej: Compra en supermercado">
                                                    <span class="text-danger"><?php echo $descripcion_err; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($categoria_err)) ? 'has-error' : ''; ?>">
                                                    <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="categoria_id" name="categoria_id">
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
                                                    <select class="form-select" id="cuenta_id" name="cuenta_id">
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
                                                    <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Campos para transferencia -->
                                        <div id="transferenciaFields" style="display: none;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="cuenta_destino_id" class="form-label">Cuenta de Destino</label>
                                                        <select class="form-select" id="cuenta_destino_id" name="cuenta_destino_id">
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
                                            <textarea class="form-control" id="notas" name="notas" rows="3" placeholder="Información adicional sobre la transacción"><?php echo $notas; ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="recurrente" name="recurrente">
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
                                                        <select class="form-select" id="frecuencia" name="frecuencia">
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
                                            <a href="transacciones-lista.php" class="btn btn-light me-2">Cancelar</a>
                                            <button type="submit" class="btn btn-primary">Guardar Transacción</button>
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
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0 me-2">
                                            <div class="avatar-xs">
                                                <span class="avatar-title bg-success-subtle text-success rounded">
                                                    <i class="ri-arrow-up-line"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">Salario</h6>
                                            <small class="text-muted">15 Nov 2024</small>
                                        </div>
                                        <div class="text-success fw-semibold">+$8,500</div>
                                    </div>

                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0 me-2">
                                            <div class="avatar-xs">
                                                <span class="avatar-title bg-danger-subtle text-danger rounded">
                                                    <i class="ri-arrow-down-line"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">Supermercado</h6>
                                            <small class="text-muted">14 Nov 2024</small>
                                        </div>
                                        <div class="text-danger fw-semibold">-$450</div>
                                    </div>

                                    <div class="d-flex align-items-center mb-0">
                                        <div class="flex-shrink-0 me-2">
                                            <div class="avatar-xs">
                                                <span class="avatar-title bg-danger-subtle text-danger rounded">
                                                    <i class="ri-arrow-down-line"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">Transporte</h6>
                                            <small class="text-muted">13 Nov 2024</small>
                                        </div>
                                        <div class="text-danger fw-semibold">-$120</div>
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
