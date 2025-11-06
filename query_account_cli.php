<?php
// Simple CLI to fetch one account for current user and print JSON
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';

// Emulate logged user
session_start();
$_SESSION['user_id'] = 1;

$uid = getCurrentUserId();
if (!$uid) $uid = 1;

$account = null;
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare('SELECT * FROM cuentas_bancarias WHERE usuario_id = ? LIMIT 1');
    $stmt->execute([$uid]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, 'SELECT * FROM cuentas_bancarias WHERE usuario_id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $account = mysqli_fetch_assoc($res);
}

if (!$account) {
    echo "NO_ACCOUNT\n";
    exit(0);
}

echo json_encode($account, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
