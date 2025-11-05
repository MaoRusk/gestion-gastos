<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();

// Define variables and initialize with empty values
$nombre = $monto_limite = $categoria_id = $fecha_inicio = $fecha_fin = $descripcion = "";
$nombre_err = $monto_limite_err = $categoria_err = $fecha_inicio_err = $fecha_fin_err = "";
$success_message = "";

// Get user's expense categories
$user_id = getCurrentUserId();
$sql_categories = "SELECT id, nombre, color, icono FROM categorias WHERE (usuario_id = ? OR es_predefinida = 1) AND activa = 1 AND tipo = 'gasto' ORDER BY nombre";
$stmt = mysqli_prepare($link, $sql_categories);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$categorias = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate nombre
    if (empty(trim($_POST["nombre"]))) {
        $nombre_err = "Por favor ingresa un nombre para el presupuesto.";
    } else {
        $nombre = sanitizeInput($_POST["nombre"]);
    }

    // Validate monto_limite
    if (empty($_POST["monto_limite"]) || floatval($_POST["monto_limite"]) <= 0) {
        $monto_limite_err = "Por favor ingresa un monto límite válido.";
    } else {
        $monto_limite = floatval($_POST["monto_limite"]);
    }

    // Validate categoria
    if (empty($_POST["categoria_id"])) {
        $categoria_err = "Por favor selecciona una categoría.";
    } else {
        $categoria_id = intval($_POST["categoria_id"]);
    }

    // Validate fecha_inicio
    if (empty($_POST["fecha_inicio"])) {
        $fecha_inicio_err = "Por favor selecciona la fecha de inicio.";
    } else {
        $fecha_inicio = $_POST["fecha_inicio"];
    }

    // Validate fecha_fin
    if (empty($_POST["fecha_fin"])) {
        $fecha_fin_err = "Por favor selecciona la fecha de fin.";
    } else {
        $fecha_fin = $_POST["fecha_fin"];
    }

    // Validate date range
    if (empty($fecha_inicio_err) && empty($fecha_fin_err) && $fecha_inicio >= $fecha_fin) {
        $fecha_fin_err = "La fecha de fin debe ser posterior a la fecha de inicio.";
    }

    // Validate descripcion
    $descripcion = !empty($_POST["descripcion"]) ? sanitizeInput($_POST["descripcion"]) : null;

    // Check input errors before inserting in database
    if (empty($nombre_err) && empty($monto_limite_err) && empty($categoria_err) && empty($fecha_inicio_err) && empty($fecha_fin_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO presupuestos (usuario_id, nombre, monto_limite, categoria_id, fecha_inicio, fecha_fin, descripcion, activo) VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "isdisss", $user_id, $nombre, $monto_limite, $categoria_id, $fecha_inicio, $fecha_fin, $descripcion);
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Presupuesto creado exitosamente!";
                
                // Clear form
                $nombre = $monto_limite = $categoria_id = $fecha_inicio = $fecha_fin = $descripcion = "";
                
            } else {
                $success_message = "Error: " . mysqli_error($link);
            }
            
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Nuevo Presupuesto | FIME - Gestión de Gastos Personales</title>
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
                                <h4 class="mb-sm-0">Nuevo Presupuesto</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard-gastos.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="presupuestos-lista.php">Presupuestos</a></li>
                                        <li class="breadcrumb-item active">Nuevo</li>
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
                                    <h5 class="card-title mb-0">Información del Presupuesto</h5>
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
                                                <div class="mb-3 <?php echo (!empty($nombre_err)) ? 'has-error' : ''; ?>">
                                                    <label for="nombre" class="form-label">Nombre del Presupuesto <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $nombre; ?>" placeholder="Ej: Presupuesto de Alimentación">
                                                    <span class="text-danger"><?php echo $nombre_err; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($monto_limite_err)) ? 'has-error' : ''; ?>">
                                                    <label for="monto_limite" class="form-label">Monto Límite <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" class="form-control" id="monto_limite" name="monto_limite" value="<?php echo $monto_limite; ?>" placeholder="0.00" step="0.01">
                                                    </div>
                                                    <span class="text-danger"><?php echo $monto_limite_err; ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($categoria_err)) ? 'has-error' : ''; ?>">
                                                    <label for="categoria_id" class="form-label">Categoría <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="categoria_id" name="categoria_id">
                                                        <option value="">Seleccionar categoría</option>
                                                        <?php foreach ($categorias as $categoria): ?>
                                                            <option value="<?php echo $categoria['id']; ?>" <?php echo ($categoria_id == $categoria['id']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo $categoria_err; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="tipo_presupuesto" class="form-label">Tipo de Presupuesto</label>
                                                    <select class="form-select" id="tipo_presupuesto" onchange="toggleTipoPresupuesto()">
                                                        <option value="mensual">Mensual</option>
                                                        <option value="semanal">Semanal</option>
                                                        <option value="anual">Anual</option>
                                                        <option value="personalizado">Personalizado</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($fecha_inicio_err)) ? 'has-error' : ''; ?>">
                                                    <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                                                    <span class="text-danger"><?php echo $fecha_inicio_err; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($fecha_fin_err)) ? 'has-error' : ''; ?>">
                                                    <label for="fecha_fin" class="form-label">Fecha de Fin <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                                                    <span class="text-danger"><?php echo $fecha_fin_err; ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="descripcion" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Descripción opcional del presupuesto"><?php echo $descripcion; ?></textarea>
                                        </div>

                                        <div class="text-end">
                                            <a href="presupuestos-lista.php" class="btn btn-light me-2">Cancelar</a>
                                            <button type="submit" class="btn btn-primary">Crear Presupuesto</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Vista Previa</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <div class="avatar-lg mx-auto">
                                                <span class="avatar-title rounded" id="preview-icon" style="background-color: #007bff20; color: #007bff;">
                                                    <i class="ri-pie-chart-line"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <h6 id="preview-nombre">Nombre del presupuesto</h6>
                                        <p class="text-muted mb-2" id="preview-categoria">Categoría</p>
                                        <h5 class="text-primary" id="preview-monto">$0.00</h5>
                                        <p class="text-muted small" id="preview-periodo">Período</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Consejos</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info" role="alert">
                                        <h6 class="alert-heading">Crear Presupuestos Efectivos</h6>
                                        <ul class="mb-0">
                                            <li>Establece límites realistas</li>
                                            <li>Revisa gastos históricos</li>
                                            <li>Considera ingresos mensuales</li>
                                            <li>Deja margen para imprevistos</li>
                                        </ul>
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
        // Update preview when form changes
        function updatePreview() {
            const nombre = document.getElementById('nombre').value || 'Nombre del presupuesto';
            const monto = document.getElementById('monto_limite').value || '0.00';
            const categoria = document.getElementById('categoria_id');
            const categoria_nombre = categoria.options[categoria.selectedIndex].text || 'Categoría';
            const fecha_inicio = document.getElementById('fecha_inicio').value;
            const fecha_fin = document.getElementById('fecha_fin').value;
            
            document.getElementById('preview-nombre').textContent = nombre;
            document.getElementById('preview-categoria').textContent = categoria_nombre;
            document.getElementById('preview-monto').textContent = '$' + parseFloat(monto).toLocaleString('en-US', {minimumFractionDigits: 2});
            
            if (fecha_inicio && fecha_fin) {
                const inicio = new Date(fecha_inicio).toLocaleDateString('es-ES', {month: 'short', day: 'numeric'});
                const fin = new Date(fecha_fin).toLocaleDateString('es-ES', {month: 'short', day: 'numeric'});
                document.getElementById('preview-periodo').textContent = `${inicio} - ${fin}`;
            } else {
                document.getElementById('preview-periodo').textContent = 'Período';
            }
        }

        // Toggle budget type
        function toggleTipoPresupuesto() {
            const tipo = document.getElementById('tipo_presupuesto').value;
            const fechaInicio = document.getElementById('fecha_inicio');
            const fechaFin = document.getElementById('fecha_fin');
            const hoy = new Date();
            
            switch(tipo) {
                case 'mensual':
                    fechaInicio.value = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
                    fechaFin.value = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().split('T')[0];
                    break;
                case 'semanal':
                    const inicioSemana = new Date(hoy);
                    inicioSemana.setDate(hoy.getDate() - hoy.getDay());
                    const finSemana = new Date(inicioSemana);
                    finSemana.setDate(inicioSemana.getDate() + 6);
                    fechaInicio.value = inicioSemana.toISOString().split('T')[0];
                    fechaFin.value = finSemana.toISOString().split('T')[0];
                    break;
                case 'anual':
                    fechaInicio.value = new Date(hoy.getFullYear(), 0, 1).toISOString().split('T')[0];
                    fechaFin.value = new Date(hoy.getFullYear(), 11, 31).toISOString().split('T')[0];
                    break;
                case 'personalizado':
                    fechaInicio.value = '';
                    fechaFin.value = '';
                    break;
            }
            updatePreview();
        }

        // Add event listeners
        document.getElementById('nombre').addEventListener('input', updatePreview);
        document.getElementById('monto_limite').addEventListener('input', updatePreview);
        document.getElementById('categoria_id').addEventListener('change', updatePreview);
        document.getElementById('fecha_inicio').addEventListener('change', updatePreview);
        document.getElementById('fecha_fin').addEventListener('change', updatePreview);
        document.getElementById('tipo_presupuesto').addEventListener('change', toggleTipoPresupuesto);

        // Initialize with monthly budget
        document.addEventListener('DOMContentLoaded', function() {
            toggleTipoPresupuesto();
        });
    </script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>

</html>
