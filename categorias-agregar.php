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

// Define variables and initialize (preserve values when loaded from DB)
$nombre = isset($nombre) ? $nombre : '';
$tipo = isset($tipo) ? $tipo : '';
$color = isset($color) ? $color : '';
$icono = isset($icono) ? $icono : '';
$descripcion = isset($descripcion) ? $descripcion : '';
$nombre_err = $tipo_err = $color_err = $icono_err = "";
$success_message = "";

// If editing or viewing, load category and prefill
if (($is_edit || $is_view) && $id > 0) {
    $sql = "SELECT * FROM categorias WHERE id = ? LIMIT 1";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if (!$row) {
            header('Location: categorias-lista.php');
            exit;
        }

        // Ownership check: predefinidas shouldn't be editable
        if ($row['es_predefinida']) {
            if ($is_edit) die('No puedes editar una categoría predefinida');
            // viewing predefinida is allowed
        }

        if (!$row['es_predefinida'] && $row['usuario_id'] != getCurrentUserId()) {
            die('No tienes permiso para ver/editar esta categoría');
        }

        // Prefill values
        $nombre = $row['nombre'];
        $tipo = $row['tipo'];
        $color = $row['color'];
        $icono = $row['icono'];
        $descripcion = $row['descripcion'];
    }
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Determine if posting an edit or create
    $post_mode = isset($_POST['mode']) ? $_POST['mode'] : 'add';
    $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    // Validate nombre
    if (empty(trim($_POST["nombre"]))) {
        $nombre_err = "Por favor ingresa un nombre para la categoría.";
    } else {
        $nombre = sanitizeInput($_POST["nombre"]);
    }

    // Validate tipo
    if (empty($_POST["tipo"])) {
        $tipo_err = "Por favor selecciona el tipo de categoría.";
    } else {
        $tipo = $_POST["tipo"];
    }

    // Validate color
    if (empty($_POST["color"])) {
        $color_err = "Por favor selecciona un color.";
    } else {
        $color = $_POST["color"];
    }

    // Validate icono
    if (empty($_POST["icono"])) {
        $icono_err = "Por favor selecciona un ícono.";
    } else {
        $icono = $_POST["icono"];
    }

    // Validate descripcion
    $descripcion = !empty($_POST["descripcion"]) ? sanitizeInput($_POST["descripcion"]) : null;

    // If no errors, proceed to insert or update
    if (empty($nombre_err) && empty($tipo_err) && empty($color_err) && empty($icono_err)) {
        $user_id = getCurrentUserId();

        if ($post_mode === 'edit' && $post_id > 0) {
            // Update existing category (ownership checked on GET, re-check here)
            $check_sql = "SELECT usuario_id, es_predefinida FROM categorias WHERE id = ? LIMIT 1";
            if ($cstmt = mysqli_prepare($link, $check_sql)) {
                mysqli_stmt_bind_param($cstmt, 'i', $post_id);
                mysqli_stmt_execute($cstmt);
                $cres = mysqli_stmt_get_result($cstmt);
                $crow = mysqli_fetch_assoc($cres);
                mysqli_stmt_close($cstmt);

                if (!$crow) {
                    die('Categoría no encontrada');
                }
                if ($crow['es_predefinida']) {
                    die('No puedes editar una categoría predefinida');
                }
                if ($crow['usuario_id'] != $user_id) {
                    die('No tienes permiso para editar esta categoría');
                }
            }

            $update_sql = "UPDATE categorias SET nombre = ?, tipo = ?, color = ?, icono = ?, descripcion = ? WHERE id = ?";
            if ($ust = mysqli_prepare($link, $update_sql)) {
                mysqli_stmt_bind_param($ust, 'ssssis', $nombre, $tipo, $color, $icono, $descripcion, $post_id);
                if (mysqli_stmt_execute($ust)) {
                    $success_message = "Categoría actualizada exitosamente!";
                } else {
                    $success_message = "Error al actualizar: " . mysqli_error($link);
                }
                mysqli_stmt_close($ust);
            }
        } else {
            // Insert new category
            $sql = "INSERT INTO categorias (usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa) VALUES (?, ?, ?, ?, ?, ?, false, true)";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "isssss", $user_id, $nombre, $tipo, $color, $icono, $descripcion);
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Categoría creada exitosamente!";
                    $nombre = $tipo = $color = $icono = $descripcion = "";
                } else {
                    $success_message = "Error: " . mysqli_error($link);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Available icons
$iconos = [
    'ri-shopping-cart-line' => 'Carrito de compras',
    'ri-restaurant-line' => 'Restaurante',
    'ri-car-line' => 'Automóvil',
    'ri-home-line' => 'Casa',
    'ri-movie-line' => 'Entretenimiento',
    'ri-heart-pulse-line' => 'Salud',
    'ri-book-line' => 'Educación',
    'ri-phone-line' => 'Teléfono',
    'ri-wifi-line' => 'Internet',
    'ri-gas-station-line' => 'Gasolina',
    'ri-clothes-line' => 'Ropa',
    'ri-gamepad-line' => 'Juegos',
    'ri-music-line' => 'Música',
    'ri-camera-line' => 'Fotografía',
    'ri-gift-line' => 'Regalos',
    'ri-bank-line' => 'Banco',
    'ri-coin-line' => 'Dinero',
    'ri-wallet-line' => 'Billetera',
    'ri-credit-card-line' => 'Tarjeta de crédito',
    'ri-piggy-bank-line' => 'Ahorros'
];

// Available colors
$colores = [
    '#28a745' => 'Verde',
    '#dc3545' => 'Rojo',
    '#007bff' => 'Azul',
    '#ffc107' => 'Amarillo',
    '#6f42c1' => 'Púrpura',
    '#fd7e14' => 'Naranja',
    '#20c997' => 'Turquesa',
    '#e83e8c' => 'Rosa',
    '#6c757d' => 'Gris',
    '#17a2b8' => 'Cian'
];
?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Nueva Categoría | FIME - Gestión de Gastos Personales</title>
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
                                <h4 class="mb-sm-0">Nueva Categoría</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard-gastos.php">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="categorias-lista.php">Categorías</a></li>
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
                                    <h5 class="card-title mb-0">Información de la Categoría</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($success_message)): ?>
                                        <div class="alert <?php echo strpos($success_message, 'exitosamente') !== false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                                            <?php echo $success_message; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php $read_only = ($is_view); ?>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <?php if ($is_edit || $is_view): ?>
                                            <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
                                            <input type="hidden" name="mode" value="<?php echo $is_edit ? 'edit' : 'view'; ?>">
                                        <?php endif; ?>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($nombre_err)) ? 'has-error' : ''; ?>">
                                                    <label for="nombre" class="form-label">Nombre de la Categoría <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $nombre; ?>" placeholder="Ej: Supermercado" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                    <span class="text-danger"><?php echo $nombre_err; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($tipo_err)) ? 'has-error' : ''; ?>">
                                                    <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="tipo" name="tipo" <?php echo $read_only ? 'disabled' : ''; ?>>
                                                        <option value="">Seleccionar tipo</option>
                                                        <option value="ingreso" <?php echo ($tipo == 'ingreso') ? 'selected' : ''; ?>>Ingreso</option>
                                                        <option value="gasto" <?php echo ($tipo == 'gasto') ? 'selected' : ''; ?>>Gasto</option>
                                                    </select>
                                                    <span class="text-danger"><?php echo $tipo_err; ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($color_err)) ? 'has-error' : ''; ?>">
                                                    <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="color" name="color">
                                                        <option value="">Seleccionar color</option>
                                                        <?php foreach ($colores as $color_value => $color_name): ?>
                                                            <option value="<?php echo $color_value; ?>" <?php echo ($color == $color_value) ? 'selected' : ''; ?>>
                                                                <?php echo $color_name; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo $color_err; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3 <?php echo (!empty($icono_err)) ? 'has-error' : ''; ?>">
                                                    <label for="icono" class="form-label">Ícono <span class="text-danger">*</span></label>
                                                    <select class="form-select" id="icono" name="icono">
                                                        <option value="">Seleccionar ícono</option>
                                                        <?php foreach ($iconos as $icono_value => $icono_name): ?>
                                                            <option value="<?php echo $icono_value; ?>" <?php echo ($icono == $icono_value) ? 'selected' : ''; ?>>
                                                                <?php echo $icono_name; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <span class="text-danger"><?php echo $icono_err; ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="descripcion" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Descripción opcional de la categoría" <?php echo $read_only ? 'disabled' : ''; ?>><?php echo $descripcion; ?></textarea>
                                        </div>

                                        <div class="text-end">
                                            <?php if (!$read_only): ?>
                                                <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Actualizar categoría' : 'Crear Categoría'; ?></button>
                                            <?php endif; ?>
                                            <a href="categorias-lista.php" class="btn btn-soft-danger me-2">Cancelar</a>
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
                                                    <i class="ri-folder-line"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <h6 id="preview-nombre">Nombre de la categoría</h6>
                                        <p class="text-muted mb-0" id="preview-tipo">Tipo</p>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Consejos</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info" role="alert">
                                        <h6 class="alert-heading">Crear Categorías Efectivas</h6>
                                        <ul class="mb-0">
                                            <li>Usa nombres descriptivos</li>
                                            <li>Elige colores distintivos</li>
                                            <li>Selecciona íconos representativos</li>
                                            <li>Agrupa gastos similares</li>
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
            const nombre = document.getElementById('nombre').value || 'Nombre de la categoría';
            const tipo = document.getElementById('tipo').value || 'Tipo';
            const color = document.getElementById('color').value || '#007bff';
            const icono = document.getElementById('icono').value || 'ri-folder-line';
            
            document.getElementById('preview-nombre').textContent = nombre;
            document.getElementById('preview-tipo').textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1);
            
            const previewIcon = document.getElementById('preview-icon');
            previewIcon.style.backgroundColor = color + '20';
            previewIcon.style.color = color;
            previewIcon.innerHTML = '<i class="' + icono + '"></i>';
        }

        // Add event listeners
        document.getElementById('nombre').addEventListener('input', updatePreview);
        document.getElementById('tipo').addEventListener('change', updatePreview);
        document.getElementById('color').addEventListener('change', updatePreview);
        document.getElementById('icono').addEventListener('change', updatePreview);
    </script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

</body>

</html>
