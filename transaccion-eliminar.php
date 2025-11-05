<?php
session_start();
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';

requireAuth();
$userId = getCurrentUserId();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: transacciones-lista.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) { header('Location: transacciones-lista.php'); exit; }

// Ensure the transaction belongs to the current user
$checkSql = "SELECT id FROM transacciones WHERE id = ? AND usuario_id = ?";
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare($checkSql);
    $stmt->execute([$id, $userId]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, $checkSql);
    mysqli_stmt_bind_param($stmt, 'ii', $id, $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $exists = mysqli_fetch_assoc($res);
}

if (!$exists) {
    header('Location: transacciones-lista.php');
    exit;
}

$delSql = "DELETE FROM transacciones WHERE id = ? AND usuario_id = ?";
try {
    if (isset($link->pdo)) {
        $stmt = $link->pdo->prepare($delSql);
        $stmt->execute([$id, $userId]);
    } else {
        $stmt = mysqli_prepare($link, $delSql);
        mysqli_stmt_bind_param($stmt, 'ii', $id, $userId);
        mysqli_stmt_execute($stmt);
    }
} catch (Exception $e) {
    // Optionally log
}

header('Location: transacciones-lista.php');
exit;
?>


