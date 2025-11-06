<?php
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cuentas-lista.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    header('Location: cuentas-lista.php');
    exit;
}

// Ensure ownership
$ownerId = null;
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare('SELECT usuario_id FROM cuentas_bancarias WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $ownerId = $stmt->fetchColumn();
} else {
    $stmt = mysqli_prepare($link, 'SELECT usuario_id FROM cuentas_bancarias WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    $ownerId = $row ? $row['usuario_id'] : null;
}

if ($ownerId != getCurrentUserId()) {
    die('No tienes permiso para eliminar esta cuenta');
}

// Delete (or soft-delete). We'll delete the row.
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare('DELETE FROM cuentas_bancarias WHERE id = ?');
    $stmt->execute([$id]);
} else {
    $stmt = mysqli_prepare($link, 'DELETE FROM cuentas_bancarias WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
}

header('Location: cuentas-lista.php');
exit;
