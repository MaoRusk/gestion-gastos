<?php
require_once __DIR__ . '/../layouts/config.php';
require_once __DIR__ . '/../includes/auth_functions.php';

$email = 'debug_test_' . time() . '@example.com';
$result = registerUser('Debug User', $email, 'pass1234', null, null, null, null, null);

echo "Register result:\n";
print_r($result);

// Check DB for inserted user
global $link;
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare('SELECT id, nombre, email, activo FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "DB lookup result:\n";
    var_export($user);
} else {
    $stmt = mysqli_prepare($link, 'SELECT id, nombre, email, activo FROM usuarios WHERE email = ?');
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);
    echo "DB lookup result:\n";
    var_export($user);
}

?>