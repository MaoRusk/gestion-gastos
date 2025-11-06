<?php
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: presupuestos-lista.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    header('Location: presupuestos-lista.php');
    exit;
}

// Check ownership
$owner = null;
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare('SELECT usuario_id FROM presupuestos WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $owner = $row ? $row['usuario_id'] : null;
} else {
    $stmt = mysqli_prepare($link, 'SELECT usuario_id FROM presupuestos WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    $owner = $row ? $row['usuario_id'] : null;
}

if ($owner != getCurrentUserId()) {
    die('No tienes permiso para eliminar este presupuesto');
}

// Soft-delete: set activo = FALSE (postgres) or 0 (mysql)
if (isset($link->pdo)) {
    if (defined('DB_TYPE') && DB_TYPE === 'postgresql') {
        $stmt = $link->pdo->prepare('UPDATE presupuestos SET activo = FALSE WHERE id = ?');
        $stmt->execute([$id]);
    } else {
        $stmt = $link->pdo->prepare('UPDATE presupuestos SET activo = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }
} else {
    if (defined('DB_TYPE') && DB_TYPE === 'postgresql') {
        $ust = mysqli_prepare($link, 'UPDATE presupuestos SET activo = FALSE WHERE id = ?');
        mysqli_stmt_bind_param($ust, 'i', $id);
        mysqli_stmt_execute($ust);
        mysqli_stmt_close($ust);
    } else {
        $ust = mysqli_prepare($link, 'UPDATE presupuestos SET activo = 0 WHERE id = ?');
        mysqli_stmt_bind_param($ust, 'i', $id);
        mysqli_stmt_execute($ust);
        mysqli_stmt_close($ust);
    }
}

header('Location: presupuestos-lista.php');
exit;
