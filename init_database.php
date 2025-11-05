<?php
/**
 * Script de inicialización de base de datos para producción (Render.com)
 * Este script se ejecuta automáticamente después del deploy
 * 
 * Acceso: https://tu-app.onrender.com/init_database.php
 */

// Incluir configuración
require_once "layouts/config.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inicialización de Base de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Inicialización de Base de Datos</h1>
        <p>Este script configurará la base de datos para el sistema de gastos personales.</p>
        <hr>

<?php
// Verificar conexión
if (!isset($link)) {
    die("<p class='error'>❌ No se pudo establecer conexión a la base de datos.</p>");
}

echo "<p class='info'>ℹ️ Tipo de base de datos: " . (isset($link->type) ? $link->type : 'mysql') . "</p>";

// Leer el esquema SQL - Priorizar el archivo completo de MySQL/MariaDB
$schema_file = null;
if (file_exists('database_completo_mariaDB.sql')) {
    $schema_file = 'database_completo_mariaDB.sql';
} elseif (file_exists('database_schema.sql')) {
    $schema_file = 'database_schema.sql';
} elseif (file_exists('fime_gastos_database.sql')) {
    $schema_file = 'fime_gastos_database.sql';
}

if (!$schema_file || !file_exists($schema_file)) {
    die("<p class='error'>❌ No se encontró ningún archivo SQL de esquema.</p>");
}

$sql = file_get_contents($schema_file);
if ($sql === false) {
    die("<p class='error'>❌ No se pudo leer el archivo " . htmlspecialchars($schema_file) . "</p>");
}

echo "<p class='info'>📄 Usando archivo: <strong>" . htmlspecialchars($schema_file) . "</strong></p>";

// Convertir esquema según el tipo de base de datos
if (isset($link->type)) {
    if ($link->type === 'mysql') {
        // Reemplazar tipos SQLite por MySQL
        $sql = str_replace('INTEGER PRIMARY KEY AUTOINCREMENT', 'INT AUTO_INCREMENT PRIMARY KEY', $sql);
        $sql = str_replace('BOOLEAN', 'TINYINT(1)', $sql);
        $sql = str_replace('DATETIME DEFAULT CURRENT_TIMESTAMP', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);
        $sql = str_replace('CREATE TABLE sqlite_sequence', '-- CREATE TABLE sqlite_sequence', $sql);
        $sql = preg_replace('/USE\s+\w+;/i', '', $sql); // Remover USE statements
    } elseif ($link->type === 'postgresql') {
        // Convertir MySQL/SQLite a PostgreSQL
        // Remover USE statements primero
        $sql = preg_replace('/USE\s+\w+;/i', '', $sql);
        
        // Convertir AUTO_INCREMENT a SERIAL (múltiples patrones)
        $sql = preg_replace('/\s+INT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', ' SERIAL PRIMARY KEY', $sql);
        $sql = preg_replace('/\s+INT\s+NOT\s+NULL\s+AUTO_INCREMENT/i', ' SERIAL', $sql);
        $sql = preg_replace('/\s+INT\s+AUTO_INCREMENT/i', ' SERIAL', $sql);
        $sql = str_replace('INT AUTO_INCREMENT PRIMARY KEY', 'SERIAL PRIMARY KEY', $sql);
        $sql = str_replace('INTEGER PRIMARY KEY AUTOINCREMENT', 'SERIAL PRIMARY KEY', $sql);
        $sql = str_replace('AUTO_INCREMENT', '', $sql);
        
        // Convertir tipos de datos
        $sql = str_replace('TINYINT(1)', 'BOOLEAN', $sql);
        $sql = str_replace('BOOLEAN DEFAULT 1', 'BOOLEAN DEFAULT TRUE', $sql);
        $sql = str_replace('BOOLEAN DEFAULT 0', 'BOOLEAN DEFAULT FALSE', $sql);
        $sql = str_replace('DATETIME', 'TIMESTAMP', $sql);
        
        // Remover ON UPDATE CURRENT_TIMESTAMP (no soportado en PostgreSQL)
        $sql = preg_replace('/\s+ON\s+UPDATE\s+CURRENT_TIMESTAMP/i', '', $sql);
        
        // Remover ENGINE, CHARSET, COLLATE
        $sql = preg_replace('/\s+ENGINE\s*=\s*\w+/i', '', $sql);
        $sql = preg_replace('/\s+DEFAULT\s+CHARSET\s*=\s*\w+/i', '', $sql);
        $sql = preg_replace('/\s+COLLATE\s*=\s*\w+/i', '', $sql);
        
        // Convertir UNIQUE NOT NULL a UNIQUE (PostgreSQL no necesita NOT NULL con UNIQUE)
        $sql = preg_replace('/UNIQUE\s+NOT\s+NULL/i', 'UNIQUE', $sql);
        
        // Convertir comentarios de MySQL a PostgreSQL
        $sql = str_replace('-- ============================================================================', '--', $sql);
    }
}

// Dividir en declaraciones
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && 
               !preg_match('/^(CREATE DATABASE|USE)/i', $stmt) &&
               !preg_match('/^--/', $stmt);
    }
);

$success_count = 0;
$error_count = 0;
$errors = [];

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    try {
        if (extension_loaded('mysqli') && is_object($link) && !isset($link->pdo)) {
            // Usando mysqli
            if (mysqli_query($link, $statement)) {
                $success_count++;
                echo "<p class='success'>✅ " . htmlspecialchars(substr($statement, 0, 60)) . "...</p>";
            } else {
                $error_count++;
                $error_msg = mysqli_error($link);
                $errors[] = $error_msg;
                // Ignorar errores de "table already exists" y "duplicate key"
                if (strpos($error_msg, 'already exists') === false && 
                    strpos($error_msg, 'Duplicate') === false) {
                    echo "<p class='error'>⚠️ Error: " . htmlspecialchars($error_msg) . "</p>";
                    echo "<pre>" . htmlspecialchars(substr($statement, 0, 200)) . "...</pre>";
                }
            }
        } else {
            // Usando PDO
            if (isset($link->pdo)) {
                try {
                    $link->pdo->exec($statement);
                    $success_count++;
                    echo "<p class='success'>✅ " . htmlspecialchars(substr($statement, 0, 60)) . "...</p>";
                } catch (PDOException $pdo_e) {
                    $error_count++;
                    $error_msg = $pdo_e->getMessage();
                    $errors[] = $error_msg;
                    // Ignorar errores de "table already exists", "relation already exists", etc.
                    if (strpos($error_msg, 'already exists') === false && 
                        strpos($error_msg, 'Duplicate') === false &&
                        strpos($error_msg, 'relation') === false &&
                        strpos($error_msg, 'does not exist') === false) {
                        echo "<p class='error'>⚠️ Error: " . htmlspecialchars($error_msg) . "</p>";
                        echo "<pre>" . htmlspecialchars(substr($statement, 0, 200)) . "...</pre>";
                    }
                }
            }
        }
    } catch (Exception $e) {
        $error_count++;
        $error_msg = $e->getMessage();
        $errors[] = $error_msg;
        // Ignorar errores de "table already exists" y "duplicate key"
        if (strpos($error_msg, 'already exists') === false && 
            strpos($error_msg, 'Duplicate') === false &&
            strpos($error_msg, 'Unknown table') === false &&
            strpos($error_msg, 'relation') === false) {
            echo "<p class='error'>⚠️ Error: " . htmlspecialchars($error_msg) . "</p>";
        }
    }
}

echo "<hr>";
echo "<h2>📊 Resumen</h2>";
echo "<p class='success'>✅ Comandos ejecutados exitosamente: <strong>$success_count</strong></p>";
echo "<p class='error'>⚠️ Errores encontrados: <strong>$error_count</strong></p>";

if ($error_count == 0 || (count($errors) > 0 && 
    (strpos(implode(' ', $errors), 'already exists') !== false || 
     strpos(implode(' ', $errors), 'Duplicate') !== false))) {
    echo "<p class='success'><strong>🎉 ¡Base de datos configurada completamente!</strong></p>";
    echo "<p>Puedes acceder al sistema en: <a href='index.php'>Iniciar Sesión</a></p>";
} else {
    echo "<p class='error'>Por favor, revisa los errores anteriores.</p>";
}
?>

    </div>
</body>
</html>

