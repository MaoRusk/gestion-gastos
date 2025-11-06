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

// Mostrar información de conexión
echo "<p class='info'>🔌 Conectado a: <strong>" . htmlspecialchars(DB_SERVER) . ":" . (defined('DB_PORT') ? DB_PORT : '5432') . "/" . htmlspecialchars(DB_NAME) . "</strong></p>";

// Verificar conexión actual
try {
    if (isset($link->pdo)) {
        $stmt = $link->pdo->query("SELECT current_database()");
        $current_db = $stmt->fetchColumn();
        echo "<p class='info'>📊 Base de datos actual: <strong>" . htmlspecialchars($current_db) . "</strong></p>";
        
        // Listar tablas existentes antes de crear
        if (isset($link->type) && $link->type === 'postgresql') {
            $stmt = $link->pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public'");
            $table_count = $stmt->fetchColumn();
            echo "<p class='info'>📋 Tablas existentes antes de la inicialización: <strong>$table_count</strong></p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='warning'>⚠️ No se pudo verificar la base de datos actual: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Leer el esquema SQL - Priorizar archivo PostgreSQL si existe, sino MySQL/MariaDB
$schema_file = null;
if (isset($link->type) && $link->type === 'postgresql' && file_exists('database_completo_postgresql.sql')) {
    $schema_file = 'database_completo_postgresql.sql';
} elseif (file_exists('database_completo_mariaDB.sql')) {
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
        
        // 1. Remover USE statements (no existe en PostgreSQL)
        $sql = preg_replace('/USE\s+\w+;/i', '', $sql);
        
        // 2. Convertir AUTO_INCREMENT a SERIAL (múltiples patrones)
        $sql = preg_replace('/\s+INT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', ' SERIAL PRIMARY KEY', $sql);
        $sql = preg_replace('/\s+INT\s+NOT\s+NULL\s+AUTO_INCREMENT/i', ' SERIAL', $sql);
        $sql = preg_replace('/\s+INT\s+AUTO_INCREMENT/i', ' SERIAL', $sql);
        $sql = str_replace('INT AUTO_INCREMENT PRIMARY KEY', 'SERIAL PRIMARY KEY', $sql);
        $sql = str_replace('INTEGER PRIMARY KEY AUTOINCREMENT', 'SERIAL PRIMARY KEY', $sql);
        $sql = str_replace('AUTO_INCREMENT', '', $sql);
        
        // 3. Convertir tipos de datos
        $sql = str_replace('TINYINT(1)', 'BOOLEAN', $sql);
        $sql = preg_replace('/BOOLEAN\s+DEFAULT\s+1/i', 'BOOLEAN DEFAULT TRUE', $sql);
        $sql = preg_replace('/BOOLEAN\s+DEFAULT\s+0/i', 'BOOLEAN DEFAULT FALSE', $sql);
        $sql = str_replace('DATETIME', 'TIMESTAMP', $sql);
        
        // 4. Remover ON UPDATE CURRENT_TIMESTAMP (no soportado en PostgreSQL)
        $sql = preg_replace('/\s+ON\s+UPDATE\s+CURRENT_TIMESTAMP/i', '', $sql);
        
        // 5. Remover ENGINE, CHARSET, COLLATE
        $sql = preg_replace('/\s+ENGINE\s*=\s*\w+/i', '', $sql);
        $sql = preg_replace('/\s+DEFAULT\s+CHARSET\s*=\s*\w+/i', '', $sql);
        $sql = preg_replace('/\s+COLLATE\s*=\s*\w+/i', '', $sql);
        
        // 6. Convertir UNIQUE NOT NULL a UNIQUE (PostgreSQL no necesita NOT NULL con UNIQUE)
        $sql = preg_replace('/UNIQUE\s+NOT\s+NULL/i', 'UNIQUE', $sql);
        
        // 7. Convertir comentarios de MySQL a PostgreSQL
        $sql = str_replace('-- ============================================================================', '--', $sql);
        
        // 8. Convertir TRUE/FALSE en INSERTs (asegurar que sean válidos para PostgreSQL)
        // TRUE y FALSE ya son válidos en PostgreSQL, pero asegurémonos
        
        // 9. Remover SELECT statements al final (verificación de datos)
        $sql = preg_replace('/SELECT\s+.*?FROM\s+.*?;/is', '', $sql);
        
        // 10. Asegurar que los FOREIGN KEY constraints funcionen
        // PostgreSQL requiere que las tablas referenciadas existan primero
        // El script ya maneja esto ejecutando las declaraciones en orden
    }
}

// Dividir en declaraciones - mejor manejo para PostgreSQL
// Para PostgreSQL, necesitamos dividir cuidadosamente porque pueden tener funciones con ;
$statements = [];
if (isset($link->type) && $link->type === 'postgresql') {
    // Para PostgreSQL, dividir por punto y coma pero mantener funciones completas
    $lines = explode("\n", $sql);
    $current_statement = '';
    $in_function = false;
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        // Saltar comentarios y líneas vacías
        if (empty($trimmed) || preg_match('/^--/', $trimmed)) {
            continue;
        }
        
        // Detectar inicio de función
        if (preg_match('/CREATE\s+(OR\s+REPLACE\s+)?FUNCTION/i', $trimmed)) {
            $in_function = true;
            $current_statement = $trimmed . "\n";
            continue;
        }
        
        // Detectar fin de función
        if ($in_function && preg_match('/\$\$/', $trimmed)) {
            $current_statement .= $trimmed;
            if (preg_match('/\$\$.*?;/', $trimmed)) {
                $in_function = false;
                $statements[] = trim($current_statement);
                $current_statement = '';
            }
            continue;
        }
        
        if ($in_function) {
            $current_statement .= $trimmed . "\n";
            continue;
        }
        
        // Agregar línea al statement actual
        $current_statement .= $trimmed . "\n";
        
        // Si termina con ;, es el final del statement
        if (preg_match('/;\s*$/', $trimmed)) {
            $stmt = trim($current_statement);
            if (!empty($stmt) && !preg_match('/^(CREATE DATABASE|USE)/i', $stmt)) {
                $statements[] = $stmt;
            }
            $current_statement = '';
        }
    }
    
    // Agregar el último statement si no terminó con ;
    if (!empty(trim($current_statement))) {
        $stmt = trim($current_statement);
        if (!empty($stmt) && !preg_match('/^(CREATE DATABASE|USE)/i', $stmt)) {
            $statements[] = $stmt;
        }
    }
} else {
    // Para MySQL/SQLite, división simple
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^(CREATE DATABASE|USE)/i', $stmt) &&
                   !preg_match('/^--/', $stmt);
        }
    );
}

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
                    // Ejecutar el statement
                    $link->pdo->exec($statement);
                    $success_count++;
                    
                    // Identificar qué tipo de statement es
                    $stmt_type = '';
                    if (preg_match('/CREATE TABLE/i', $statement)) {
                        preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?(\w+)/i', $statement, $matches);
                        $stmt_type = $matches[1] ?? 'tabla';
                    } elseif (preg_match('/CREATE (?:OR REPLACE )?FUNCTION/i', $statement)) {
                        $stmt_type = 'función';
                    } elseif (preg_match('/CREATE TRIGGER/i', $statement)) {
                        $stmt_type = 'trigger';
                    } elseif (preg_match('/INSERT INTO/i', $statement)) {
                        preg_match('/INSERT INTO\s+(\w+)/i', $statement, $matches);
                        $stmt_type = 'INSERT en ' . ($matches[1] ?? 'tabla');
                    }
                    
                    if ($stmt_type) {
                        echo "<p class='success'>✅ $stmt_type creado/ejecutado correctamente</p>";
                    } else {
                        echo "<p class='success'>✅ " . htmlspecialchars(substr($statement, 0, 60)) . "...</p>";
                    }
                } catch (PDOException $pdo_e) {
                    $error_count++;
                    $error_msg = $pdo_e->getMessage();
                    $errors[] = $error_msg;
                    
                    // Determinar si es un error que podemos ignorar
                    $is_duplicate_error = (
                        strpos($error_msg, 'already exists') !== false || 
                        strpos($error_msg, 'Duplicate') !== false ||
                        (strpos($error_msg, 'relation') !== false && strpos($error_msg, 'already exists') !== false)
                    );
                    
                    // Si es un error de "relation does not exist" en un CREATE TABLE, es un problema real
                    $is_critical_error = (
                        strpos($error_msg, 'does not exist') !== false && 
                        preg_match('/CREATE TABLE/i', $statement) &&
                        strpos($error_msg, 'relation') !== false &&
                        strpos($error_msg, 'already exists') === false
                    );
                    
                    if ($is_duplicate_error) {
                        // Ya existe, ignorar pero contar como éxito
                        $success_count++;
                        echo "<p class='info'>ℹ️ Ya existe (ignorado): " . htmlspecialchars(substr($statement, 0, 50)) . "...</p>";
                    } else {
                        // Error real, mostrar y mantener el conteo de error
                        echo "<p class='error'>❌ Error: " . htmlspecialchars($error_msg) . "</p>";
                        echo "<pre>" . htmlspecialchars(substr($statement, 0, 300)) . "...</pre>";
                        
                        // Si es un error crítico, intentar continuar pero advertir
                        if ($is_critical_error) {
                            echo "<p class='warning'>⚠️ Error crítico detectado. Puede ser por dependencias faltantes.</p>";
                        }
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

// Verificar tablas creadas después de la ejecución
echo "<h3>📋 Verificación Post-Inicialización:</h3>";
try {
    if (isset($link->pdo)) {
        if (isset($link->type) && $link->type === 'postgresql') {
            $stmt = $link->pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
            $created_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "<p class='info'>Tablas encontradas después de la inicialización (" . count($created_tables) . "):</p>";
            if (!empty($created_tables)) {
                echo "<ul>";
                foreach ($created_tables as $table) {
                    echo "<li>" . htmlspecialchars($table) . "</li>";
                }
                echo "</ul>";
            }
        }
    }
} catch (Exception $e) {
    echo "<p class='warning'>⚠️ No se pudieron listar las tablas: " . htmlspecialchars($e->getMessage()) . "</p>";
}

if ($error_count == 0 || (count($errors) > 0 && 
    (strpos(implode(' ', $errors), 'already exists') !== false || 
     strpos(implode(' ', $errors), 'Duplicate') !== false))) {
    echo "<p class='success'><strong>🎉 ¡Base de datos configurada completamente!</strong></p>";
    echo "<p>Puedes acceder al sistema en: <a href='index.php'>Iniciar Sesión</a></p>";
    echo "<p>O verifica el estado en: <a href='verify_database.php'>Verificar Base de Datos</a></p>";
} else {
    echo "<p class='error'>Por favor, revisa los errores anteriores.</p>";
    echo "<p>Verifica el estado en: <a href='verify_database.php'>Verificar Base de Datos</a></p>";
}
?>

    </div>
</body>
</html>

