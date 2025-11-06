<?php
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: categorias-lista.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    header('Location: categorias-lista.php');
    exit;
}

// Check ownership and whether it's predefinida
$owner = null;
$es_predef = false;
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare('SELECT usuario_id, es_predefinida FROM categorias WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $owner = $row ? $row['usuario_id'] : null;
    $es_predef = $row ? (bool)$row['es_predefinida'] : false;
} else {
    $stmt = mysqli_prepare($link, 'SELECT usuario_id, es_predefinida FROM categorias WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    $owner = $row ? $row['usuario_id'] : null;
    $es_predef = $row ? (bool)$row['es_predefinida'] : false;
}

if ($es_predef) {
    die('No puedes eliminar una categoría predefinida');
}

if ($owner != getCurrentUserId()) {
    die('No tienes permiso para eliminar esta categoría');
}

// Soft-delete: set activa = false (DB-aware)
if (defined('DB_TYPE') && DB_TYPE === 'postgresql') {
    $sql = 'UPDATE categorias SET activa = TRUE WHERE id = ?';
    // Wait — for soft-delete we should set activa = FALSE; fix below
}

if (isset($link->pdo)) {
    // Use boolean false for postgres or 0 for others
    if (defined('DB_TYPE') && DB_TYPE === 'postgresql') {
        $stmt = $link->pdo->prepare('UPDATE categorias SET activa = FALSE WHERE id = ?');
        $stmt->execute([$id]);
    } else {
        $stmt = $link->pdo->prepare('UPDATE categorias SET activa = 0 WHERE id = ?');
        $stmt->execute([$id]);
    }
} else {
    if (defined('DB_TYPE') && DB_TYPE === 'postgresql') {
        $ust = mysqli_prepare($link, 'UPDATE categorias SET activa = FALSE WHERE id = ?');
        mysqli_stmt_bind_param($ust, 'i', $id);
        mysqli_stmt_execute($ust);
        mysqli_stmt_close($ust);
    } else {
        $ust = mysqli_prepare($link, 'UPDATE categorias SET activa = 0 WHERE id = ?');
        mysqli_stmt_bind_param($ust, 'i', $id);
        mysqli_stmt_execute($ust);
        mysqli_stmt_close($ust);
    }
}

header('Location: categorias-lista.php');
exit;
