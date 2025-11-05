<?php
/**
 * Export SQLite database to MySQL
 * This script exports all data from SQLite to MySQL for viewing in MySQL Workbench
 */

require_once 'layouts/config.php';

// Check if SQLite database exists
if (!file_exists(DB_FILE)) {
    die("❌ SQLite database not found at: " . DB_FILE . "\n");
}

echo "🔄 Starting export from SQLite to MySQL...\n\n";

try {
    // Connect to SQLite
    $sqlite = new PDO("sqlite:" . DB_FILE);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to SQLite\n";
    
    // Try to connect to MySQL
    try {
        $mysql = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
        $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Connected to MySQL\n\n";
    } catch(PDOException $e) {
        echo "⚠️ Could not connect to MySQL: " . $e->getMessage() . "\n";
        echo "📝 Creating SQL file instead...\n\n";
        
        // Create SQL file with all data
        $sql_file = "fime_gastos_backup.sql";
        $output = fopen($sql_file, 'w');
        
        fwrite($output, "-- FIME Gastos Database Export\n");
        fwrite($output, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
        
        // Get all tables
        $tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            fwrite($output, "-- Table: $table\n");
            
            // Get table structure
            $create_table = $sqlite->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
            
            // Convert SQLite syntax to MySQL
            $create_table = str_replace('INTEGER PRIMARY KEY AUTOINCREMENT', 'INT AUTO_INCREMENT PRIMARY KEY', $create_table);
            $create_table = str_replace('BOOLEAN', 'TINYINT(1)', $create_table);
            $create_table = str_replace('TEXT', 'TEXT', $create_table);
            $create_table = str_replace('DATETIME DEFAULT CURRENT_TIMESTAMP', 'DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', $create_table);
            
            fwrite($output, $create_table . ";\n\n");
            
            // Get table data
            $data = $sqlite->query("SELECT * FROM $table");
            while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
                $columns = implode(', ', array_keys($row));
                $values = array_map(function($v) use ($sqlite) {
                    if ($v === null) return 'NULL';
                    return $sqlite->quote($v);
                }, array_values($row));
                $values_str = implode(', ', $values);
                
                fwrite($output, "INSERT INTO $table ($columns) VALUES ($values_str);\n");
            }
            
            fwrite($output, "\n");
        }
        
        fclose($output);
        echo "✅ SQL file created: $sql_file\n";
        echo "📄 You can now import this file into MySQL Workbench\n";
        
        exit;
    }
    
    // If we get here, MySQL connection was successful
    echo "📊 Exporting data to MySQL...\n\n";
    
    // Get all tables from SQLite
    $tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "📋 Exporting table: $table...\n";
        
        // Create table in MySQL (if it doesn't exist)
        $create_sql = $sqlite->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
        
        // Convert SQLite syntax to MySQL
        $create_sql = str_replace('INTEGER PRIMARY KEY AUTOINCREMENT', 'INT AUTO_INCREMENT PRIMARY KEY', $create_sql);
        $create_sql = str_replace('BOOLEAN', 'TINYINT(1)', $create_sql);
        $create_sql = str_replace('DATETIME DEFAULT CURRENT_TIMESTAMP', 'DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', $create_sql);
        
        try {
            $mysql->exec("DROP TABLE IF EXISTS $table");
            $mysql->exec($create_sql);
        } catch(PDOException $e) {
            echo "⚠️ Warning creating table: " . $e->getMessage() . "\n";
        }
        
        // Get data from SQLite
        $data = $sqlite->query("SELECT * FROM $table");
        $rows = $data->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) > 0) {
            // Prepare insert statement
            $columns = array_keys($rows[0]);
            $placeholders = str_repeat('?,', count($columns) - 1) . '?';
            $insert_sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES ($placeholders)";
            $stmt = $mysql->prepare($insert_sql);
            
            foreach ($rows as $row) {
                $stmt->execute(array_values($row));
            }
            
            echo "   ✅ Exported " . count($rows) . " rows\n";
        } else {
            echo "   ℹ️ No data to export\n";
        }
    }
    
    echo "\n🎉 Export completed successfully!\n";
    echo "💡 You can now view the data in MySQL Workbench\n";
    echo "🌐 Connection details:\n";
    echo "   - Server: " . DB_SERVER . "\n";
    echo "   - Database: " . DB_NAME . "\n";
    echo "   - Username: " . DB_USERNAME . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>



