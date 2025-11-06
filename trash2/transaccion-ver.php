<?php
session_start();
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';

requireAuth();
$userId = getCurrentUserId();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: transacciones-lista.php'); exit; }

$sql = "SELECT t.*, c.nombre AS categoria_nombre, c.color AS categoria_color, c.icono AS categoria_icono,
               cb.nombre AS cuenta_nombre
        FROM transacciones t
        LEFT JOIN categorias c ON t.categoria_id = c.id
        LEFT JOIN cuentas_bancarias cb ON t.cuenta_id = cb.id
        WHERE t.id = ? AND t.usuario_id = ?";

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
?>
<?php include 'layouts/head-main.php'; ?>
<head>
    <title>Transacción #<?php echo (int)$tx['id']; ?> | FIME</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>
<?php include 'layouts/body.php'; ?>
    <div id="layout-wrapper">
        <?php include 'layouts/topbar.php'; ?>
        <?php include 'layouts/sidebar-gastos.php'; ?>
        <div class="main-content">
            <div class="page-content"><div class="container-fluid">
                <div class="row"><div class="col-12">
                    <div class="card"><div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detalle de Transacción</h5>
                        <div>
                            <a class="btn btn-sm btn-secondary" href="transacciones-lista.php">Volver</a>
                            <a class="btn btn-sm btn-primary" href="transaccion-editar.php?id=<?php echo (int)$tx['id']; ?>">Editar</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="text-muted d-block">Descripción</label>
                                <div class="fw-semibold"><?php echo htmlspecialchars($tx['descripcion']); ?></div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted d-block">Fecha</label>
                                <div class="fw-semibold"><?php echo date('d/m/Y', strtotime($tx['fecha'])); ?></div>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted d-block">Tipo</label>
                                <div class="fw-semibold text-<?php echo $tx['tipo']==='ingreso'?'success':'danger'; ?>"><?php echo ucfirst($tx['tipo']); ?></div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted d-block">Monto</label>
                                <div class="fw-semibold <?php echo $tx['tipo']==='ingreso'?'text-success':'text-danger'; ?>">$<?php echo number_format((float)$tx['monto'],2); ?></div>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted d-block">Categoría</label>
                                <span class="badge" style="background-color: <?php echo $tx['categoria_color']; ?>20; color: <?php echo $tx['categoria_color']; ?>">
                                    <i class="<?php echo $tx['categoria_icono']; ?> me-1"></i>
                                    <?php echo htmlspecialchars($tx['categoria_nombre']); ?>
                                </span>
                            </div>
                            <div class="col-md-4">
                                <label class="text-muted d-block">Cuenta</label>
                                <div class="fw-semibold"><?php echo htmlspecialchars($tx['cuenta_nombre']); ?></div>
                            </div>
                            <?php if (!empty($tx['notas'])): ?>
                            <div class="col-12">
                                <label class="text-muted d-block">Notas</label>
                                <div><?php echo nl2br(htmlspecialchars($tx['notas'])); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div></div>
                </div></div>
            </div></div>
            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>
    <?php include 'layouts/vendor-scripts.php'; ?>
    <script src="assets/js/app.js"></script>
</body>
</html>

