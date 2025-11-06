<?php
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';
requireAuth();

// Get id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die('Cuenta no encontrada');
}

// Fetch account, ensure ownership
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare('SELECT * FROM cuentas_bancarias WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, 'SELECT * FROM cuentas_bancarias WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $cuenta = mysqli_fetch_assoc($res);
}

if (!$cuenta || $cuenta['usuario_id'] != getCurrentUserId()) {
    die('No tienes permiso para ver esta cuenta');
}

include 'layouts/head-main.php';
?>

<head>
    <title>Cuenta - <?php echo htmlspecialchars($cuenta['nombre']); ?></title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/topbar.php'; ?>
    <?php include 'layouts/sidebar-gastos.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4"><?php echo htmlspecialchars($cuenta['nombre']); ?></h4>
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($cuenta['tipo']); ?></p>
                                <p><strong>Banco:</strong> <?php echo htmlspecialchars($cuenta['banco']); ?></p>
                                <p><strong>Número de cuenta:</strong> <?php echo htmlspecialchars($cuenta['numero_cuenta']); ?></p>
                                <p><strong>Balance actual:</strong> $<?php echo number_format($cuenta['balance_actual'], 2); ?></p>
                                <p><strong>Estado:</strong> <?php echo $cuenta['activa'] ? 'Activa' : 'Inactiva'; ?></p>
                                <a href="cuentas-editar.php?id=<?php echo (int)$cuenta['id']; ?>" class="btn btn-primary">Editar</a>
                                <a href="cuentas-lista.php" class="btn btn-outline-secondary">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include 'layouts/footer.php'; ?>

</body>
</html>
