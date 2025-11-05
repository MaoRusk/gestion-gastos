<?php
// Test login functionality
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';

echo "Testing login functionality...\n";

// Test login with admin credentials
$result = loginUser('admin@fime.com', 'admin123');

if ($result['success']) {
    echo "✅ Login successful!\n";
    echo "User ID: " . getCurrentUserId() . "\n";
    echo "User Name: " . getCurrentUserName() . "\n";
    echo "User Email: " . getCurrentUserEmail() . "\n";
} else {
    echo "❌ Login failed: " . $result['message'] . "\n";
}

// Test database connection
echo "\nTesting database connection...\n";
try {
    if (isset($link->pdo)) {
        $stmt = $link->pdo->query("SELECT COUNT(*) as count FROM usuarios");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Database connection OK - Users count: " . $result['count'] . "\n";
    } else {
        echo "❌ No database connection\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";
?>
