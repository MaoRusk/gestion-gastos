<?php
require_once __DIR__ . '/layouts/config.php';
if (!isset($link)) { echo "No DB link\n"; exit(1); }
$new = password_hash('admin123', PASSWORD_DEFAULT);
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare("UPDATE usuarios SET password_hash = ?, activo = TRUE WHERE email = ?");
    $stmt->execute([$new, 'admin@fime.com']);
    echo "Updated rows: " . $stmt->rowCount() . "\n";
} else {
    $stmt = mysqli_prepare($link, "UPDATE usuarios SET password_hash = ?, activo = 1 WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 'ss', $new, $email = 'admin@fime.com');
    mysqli_stmt_execute($stmt);
    echo "Updated rows: " . mysqli_stmt_affected_rows($stmt) . "\n";
}
