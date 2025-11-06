<?php
// Test database connection
require_once 'layouts/config.php';

echo "Testing database connection...\n";

if (extension_loaded('mysqli')) {
    echo "✅ Using mysqli extension\n";
    echo "Connection: " . (mysqli_ping($link) ? "OK" : "Failed") . "\n";
} elseif (extension_loaded('pdo_mysql')) {
    echo "✅ Using PDO MySQL extension\n";
    echo "Connection: " . ($link->pdo ? "OK" : "Failed") . "\n";
} else {
    echo "❌ No MySQL extensions available\n";
}

// Test a simple query
try {
    if (extension_loaded('mysqli')) {
        $result = mysqli_query($link, "SELECT 1 as test");
        $row = mysqli_fetch_assoc($result);
        echo "Query test: " . ($row['test'] == 1 ? "OK" : "Failed") . "\n";
    } else {
        $stmt = $link->pdo->query("SELECT 1 as test");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Query test: " . ($row['test'] == 1 ? "OK" : "Failed") . "\n";
    }
} catch (Exception $e) {
    echo "Query test: Failed - " . $e->getMessage() . "\n";
}

echo "\nDatabase test completed!\n";
?>
