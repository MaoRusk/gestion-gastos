<?php
session_start();
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';

requireAuth();
$userId = getCurrentUserId();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: transacciones-lista.php'); exit; }

// Load accounts and categories for selects
$accounts = [];
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare("SELECT id, nombre FROM cuentas_bancarias WHERE usuario_id = ? AND activa = 1 ORDER BY nombre");
    $stmt->execute([$userId]);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, "SELECT id, nombre FROM cuentas_bancarias WHERE usuario_id = ? AND activa = 1 ORDER BY nombre");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) $accounts[] = $row;
}

$categories = [];
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare("SELECT id, nombre, tipo FROM categorias WHERE (usuario_id = ? OR es_predefinida = 1) AND activa = 1 ORDER BY tipo, nombre");
    $stmt->execute([$userId]);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, "SELECT id, nombre, tipo FROM categorias WHERE (usuario_id = ? OR es_predefinida = 1) AND activa = 1 ORDER BY tipo, nombre");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) $categories[] = $row;
}

// Load transaction
$sql = "SELECT * FROM transacciones WHERE id = ? AND usuario_id = ?";
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare($sql);
    $stmt->execute([$id, $userId]);
    $tx = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $id, $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $tx = mysqli_fetch_assoc($res);
}

if (!$tx) { header('Location: transacciones-lista.php'); exit; }

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = trim((string)($_POST['descripcion'] ?? ''));
    $monto = (float)($_POST['monto'] ?? 0);
    $tipo = $_POST['tipo'] ?? 'gasto';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $cuenta_id = (int)($_POST['cuenta_id'] ?? 0);
    $categoria_id = (int)($_POST['categoria_id'] ?? 0);
    $notas = $_POST['notas'] ?? null;

    if ($descripcion && $monto > 0 && $cuenta_id > 0 && $categoria_id > 0) {
        $upd = "UPDATE transacciones SET descripcion=?, monto=?, tipo=?, fecha=?, cuenta_id=?, categoria_id=?, notas=?, fecha_actualizacion=CURRENT_TIMESTAMP WHERE id=? AND usuario_id=?";
        if (isset($link->pdo)) {
            $stmt = $link->pdo->prepare($upd);
            $stmt->execute([$descripcion, $monto, $tipo, $fecha, $cuenta_id, $categoria_id, $notas, $id, $userId]);
        } else {
            $stmt = mysqli_prepare($link, $upd);
            mysqli_stmt_bind_param($stmt, 'sdssiiisii', $descripcion, $monto, $tipo, $fecha, $cuenta_id, $categoria_id, $notas, $id, $userId);
            mysqli_stmt_execute($stmt);
        }
        header('Location: transacciones-lista.php');
        exit;
    }
}
?>
<?php include 'layouts/head-main.php'; ?>
<head>
    <title>Editar Transacción | FIME</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>
<?php include 'layouts/body.php'; ?>
    <div id="layout-wrapper">
        <?php include 'layouts/topbar.php'; ?>
        <?php include 'layouts/sidebar-gastos.php'; ?>
        <div class="main-content">
            <div class="page-content"><div class="container-fluid">
                <div class="row"><div class="col-xl-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Editar Transacción</h5>
                            <a class="btn btn-sm btn-secondary" href="transacciones-lista.php">Cancelar</a>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Descripción</label>
                                        <input type="text" class="form-control" name="descripcion" value="<?php echo htmlspecialchars($tx['descripcion']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Fecha</label>
                                        <input type="date" class="form-control" name="fecha" value="<?php echo htmlspecialchars($tx['fecha']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tipo</label>
                                        <select class="form-select" name="tipo" required>
                                            <option value="ingreso" <?php echo $tx['tipo']==='ingreso'?'selected':''; ?>>Ingreso</option>
                                            <option value="gasto" <?php echo $tx['tipo']==='gasto'?'selected':''; ?>>Gasto</option>
                                            <option value="transferencia" <?php echo $tx['tipo']==='transferencia'?'selected':''; ?>>Transferencia</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Monto</label>
                                        <input type="number" step="0.01" class="form-control" name="monto" value="<?php echo htmlspecialchars((float)$tx['monto']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cuenta</label>
                                        <select class="form-select" name="cuenta_id" required>
                                            <option value="">Selecciona cuenta</option>
                                            <?php foreach ($accounts as $a): ?>
                                            <option value="<?php echo (int)$a['id']; ?>" <?php echo $tx['cuenta_id']==$a['id']?'selected':''; ?>><?php echo htmlspecialchars($a['nombre']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Categoría</label>
                                        <select class="form-select" name="categoria_id" required>
                                            <option value="">Selecciona categoría</option>
                                            <?php foreach ($categories as $c): ?>
                                            <option value="<?php echo (int)$c['id']; ?>" <?php echo $tx['categoria_id']==$c['id']?'selected':''; ?>><?php echo htmlspecialchars(ucfirst($c['tipo']).' - '.$c['nombre']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Notas</label>
                                        <textarea class="form-control" name="notas" rows="3"><?php echo htmlspecialchars($tx['notas']); ?></textarea>
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button class="btn btn-primary" type="submit">Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div></div>
            </div></div>
            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>
    <?php include 'layouts/vendor-scripts.php'; ?>
    <script src="assets/js/app.js"></script>
</body>
</html>

