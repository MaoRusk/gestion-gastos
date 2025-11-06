<?php
/**
 * Direct Import to MySQL
 * This script connects directly to MySQL and imports all data
 */

// MySQL connection parameters
$host = 'localhost';
$username = 'root';
$password = '1234';
$database = 'fime_gastos_db';

echo "🔄 Connecting to MySQL...\n";

try {
    // Connect to MySQL
    $mysql = new PDO("mysql:host=$host", $username, $password);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $mysql->exec("CREATE DATABASE IF NOT EXISTS $database");
    $mysql->exec("USE $database");
    
    echo "✅ Connected to MySQL\n";
    echo "✅ Database '$database' selected\n\n";
    
    // Read and execute SQL file
    $sql_file = 'fime_gastos_backup.sql';
    
    if (!file_exists($sql_file)) {
        die("❌ SQL file not found: $sql_file\n");
    }
    
    echo "📂 Reading SQL file: $sql_file\n";
    $sql_content = file_get_contents($sql_file);
    
    // Split by semicolons but keep the structure
    $statements = explode(';', $sql_content);
    
    $executed = 0;
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        // Skip empty statements and comments
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        // Skip SQLite-specific commands
        if (strpos($statement, 'PRAGMA') === 0 || 
            strpos($statement, 'BEGIN') === 0 || 
            strpos($statement, 'COMMIT') === 0) {
            continue;
        }
        
        try {
            $mysql->exec($statement);
            $executed++;
        } catch(PDOException $e) {
            // Skip errors for duplicate entries
            if (strpos($e->getMessage(), 'Duplicate') === false && 
                strpos($e->getMessage(), 'already exists') === false) {
                echo "⚠️ Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "✅ Executed $executed statements\n\n";
    
    // Verify data
    echo "📊 Verifying imported data:\n";
    echo str_repeat('-', 50) . "\n";
    
    $tables = ['usuarios', 'cuentas_bancarias', 'categorias', 'transacciones', 'presupuestos', 'metas_ahorro'];
    
    foreach ($tables as $table) {
        $stmt = $mysql->query("SELECT COUNT(*) as total FROM $table");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo sprintf("  %-20s: %d registros\n", $table, $count);
    }
    
    echo "\n✅ Import completed successfully!\n";
    echo "💡 You can now view the data in MySQL Workbench\n";
    echo "🌐 Connection: localhost / Database: $database\n";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n💡 Make sure MySQL is running and accessible.\n";
    echo "   Try: sudo systemctl start mysql\n";
}
?>

