<?php
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';
requireAuth();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: cuentas-lista.php');
    exit;
}

// Fetch account and ensure owner
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
    die('No tienes permiso para editar esta cuenta');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $banco = isset($_POST['banco']) ? trim($_POST['banco']) : '';
    $numero_cuenta = isset($_POST['numero_cuenta']) ? trim($_POST['numero_cuenta']) : '';
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : $cuenta['tipo'];
    $color = isset($_POST['color']) ? $_POST['color'] : $cuenta['color'];
    $balance_actual = isset($_POST['balance_actual']) ? floatval($_POST['balance_actual']) : $cuenta['balance_actual'];
    $activa = isset($_POST['activa']) ? 1 : 0;

    if ($nombre === '') $errors[] = 'El nombre es obligatorio.';

    if (empty($errors)) {
        if (isset($link->pdo)) {
            $upd = 'UPDATE cuentas_bancarias SET nombre = ?, banco = ?, numero_cuenta = ?, tipo = ?, color = ?, balance_actual = ?, activa = ? WHERE id = ?';
            $stmt = $link->pdo->prepare($upd);
            $stmt->execute([$nombre, $banco, $numero_cuenta, $tipo, $color, $balance_actual, $activa, $id]);
        } else {
            $upd = 'UPDATE cuentas_bancarias SET nombre = ?, banco = ?, numero_cuenta = ?, tipo = ?, color = ?, balance_actual = ?, activa = ? WHERE id = ?';
            $stmt = mysqli_prepare($link, $upd);
            mysqli_stmt_bind_param($stmt, 'ssssdiii', $nombre, $banco, $numero_cuenta, $tipo, $color, $balance_actual, $activa, $id);
            mysqli_stmt_execute($stmt);
        }
        header('Location: cuentas-ver.php?id=' . $id);
        exit;
    }
}

include 'layouts/head-main.php';
?>

<head>
    <title>Editar Cuenta - <?php echo htmlspecialchars($cuenta['nombre']); ?></title>
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
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Editar Cuenta</h4>
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <?php foreach ($errors as $e) echo '<div>' . htmlspecialchars($e) . '</div>'; ?>
                                    </div>
                                <?php endif; ?>

                                <form method="post" action="">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars(isset($nombre) ? $nombre : $cuenta['nombre']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="banco" class="form-label">Banco</label>
                                        <input type="text" id="banco" name="banco" class="form-control" value="<?php echo htmlspecialchars(isset($banco) ? $banco : $cuenta['banco']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="numero_cuenta" class="form-label">Número de cuenta</label>
                                        <input type="text" id="numero_cuenta" name="numero_cuenta" class="form-control" value="<?php echo htmlspecialchars(isset($numero_cuenta) ? $numero_cuenta : $cuenta['numero_cuenta']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="balance_actual" class="form-label">Balance actual</label>
                                        <input type="number" step="0.01" id="balance_actual" name="balance_actual" class="form-control" value="<?php echo htmlspecialchars(isset($balance_actual) ? $balance_actual : $cuenta['balance_actual']); ?>">
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="activa" name="activa" <?php echo ((isset($activa) ? $activa : $cuenta['activa']) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="activa">Activa</label>
                                    </div>
                                    <button class="btn btn-primary" type="submit">Guardar</button>
                                    <a href="cuentas-ver.php?id=<?php echo (int)$cuenta['id']; ?>" class="btn btn-outline-secondary">Cancelar</a>
                                </form>

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
