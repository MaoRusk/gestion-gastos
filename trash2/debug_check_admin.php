<?php
require_once __DIR__ . '/layouts/config.php';

if (!isset($link)) {
    echo "No DB link found\n";
    exit(1);
}

echo "DB_TYPE=" . (defined('DB_TYPE')?DB_TYPE:'?') . "\n";
if (isset($link->pdo)) {
    try {
        $stmt = $link->pdo->prepare("SELECT id, nombre, email, password_hash, activo FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute(['admin@fime.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            echo "No admin user found\n";
            exit(0);
        }
        echo "Found user id=" . $user['id'] . " nombre=" . $user['nombre'] . " activo=" . $user['activo'] . "\n";
        echo "password_hash: " . $user['password_hash'] . "\n";
        $ok = password_verify('admin123', $user['password_hash']) ? 'OK' : 'FAIL';
        echo "password_verify('admin123') => " . $ok . "\n";
    } catch (Exception $e) {
        echo "Error querying users: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    // mysqli
    $email = 'admin@fime.com';
    $stmt = mysqli_prepare($link, "SELECT id, nombre, email, password_hash, activo FROM usuarios WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($res);
    if (!$user) {
        echo "No admin user found\n";
        exit(0);
    }
    echo "Found user id=" . $user['id'] . " nombre=" . $user['nombre'] . " activo=" . $user['activo'] . "\n";
    echo "password_hash: " . $user['password_hash'] . "\n";
    $ok = password_verify('admin123', $user['password_hash']) ? 'OK' : 'FAIL';
    echo "password_verify('admin123') => " . $ok . "\n";
}

// show current database (for pdo)
if (isset($link->pdo)) {
    try {
        $cur = $link->pdo->query('SELECT current_database()')->fetchColumn();
        echo "current_database() = " . $cur . "\n";
    } catch(Exception $e) {}
}

