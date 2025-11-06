<?php
// Debug user data
require_once 'layouts/config.php';

echo "Debugging user data...\n";

try {
    $stmt = $link->pdo->query("SELECT id, nombre, email, password_hash FROM usuarios WHERE email = 'admin@fime.com'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User found:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Nombre: " . $user['nombre'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Password Hash: " . substr($user['password_hash'], 0, 20) . "...\n";
        
        // Test password verification
        $test_password = 'admin123';
        if (password_verify($test_password, $user['password_hash'])) {
            echo "✅ Password verification successful!\n";
        } else {
            echo "❌ Password verification failed!\n";
        }
    } else {
        echo "❌ User not found!\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
