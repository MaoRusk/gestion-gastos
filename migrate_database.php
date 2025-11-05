<?php
/**
 * Script de migración para crear la base de datos del sistema de gastos personales
 * Ejecutar desde la línea de comandos: php migrate_database.php
 */

// Configuración de la base de datos
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'fime_gastos';

try {
    // Conectar sin especificar base de datos para crearla
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conectado al servidor MySQL exitosamente.\n";
    
    // Leer el archivo SQL
    $sql = file_get_contents('database_schema.sql');
    
    if ($sql === false) {
        throw new Exception("No se pudo leer el archivo database_schema.sql");
    }
    
    // Dividir el SQL en declaraciones individuales
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Ejecutado: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                // Ignorar errores de "database already exists" y otros errores menores
                if (strpos($e->getMessage(), 'database exists') === false && 
                    strpos($e->getMessage(), 'Duplicate key name') === false) {
                    echo "⚠ Error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n✅ Migración completada exitosamente!\n";
    echo "Base de datos '$database' creada con todas las tablas necesarias.\n";
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
