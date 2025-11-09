<?php
/**
 * Script para importar/restaurar datos de la base de datos
 * Lee un archivo SQL y lo ejecuta en la base de datos de producción
 * 
 * Uso desde navegador: http://localhost/import_database.php?file=database_export.sql
 * 
 * ADVERTENCIA: Este script eliminará datos existentes antes de importar
 */

require_once "layouts/config.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Importar Base de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 900px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .danger { background: #dc3545; }
        .danger:hover { background: #c82333; }
        textarea { width: 100%; height: 300px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📥 Importar Base de Datos</h1>
        <p class="warning">⚠️ ADVERTENCIA: Este script eliminará TODOS los datos existentes antes de importar.</p>
        
        <?php
        if (isset($_POST['import']) && $_POST['import'] === 'yes') {
            try {
                if (isset($link->pdo)) {
                    $pdo = $link->pdo;
                    $db_type = isset($link->type) ? $link->type : 'unknown';
                    
                    echo "<h2>Iniciando importación...</h2>";
                    
                    // Leer el archivo SQL o el contenido del textarea
                    $sql_content = '';
                    
                    if (isset($_FILES['sql_file']) && $_FILES['sql_file']['error'] === UPLOAD_ERR_OK) {
                        $sql_content = file_get_contents($_FILES['sql_file']['tmp_name']);
                        echo "<p class='info'>✓ Archivo cargado: " . htmlspecialchars($_FILES['sql_file']['name']) . "</p>";
                    } elseif (isset($_POST['sql_content']) && !empty($_POST['sql_content'])) {
                        $sql_content = $_POST['sql_content'];
                        echo "<p class='info'>✓ Contenido SQL proporcionado desde textarea</p>";
                    } else {
                        throw new Exception("No se proporcionó archivo SQL ni contenido.");
                    }
                    
                    // Limpiar base de datos primero (opcional, solo si se marca la opción)
                    if (isset($_POST['clear_first']) && $_POST['clear_first'] === 'yes') {
                        echo "<h3>Limpiando base de datos...</h3>";
                        
                        // Obtener lista de tablas
                        if ($db_type === 'postgresql') {
                            $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
                            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            // Orden de eliminación
                            $delete_order = [
                                'transacciones',
                                'transferencias',
                                'presupuestos',
                                'cuentas_bancarias',
                                'categorias',
                                'usuarios',
                                'configuraciones'
                            ];
                            
                            foreach ($delete_order as $table) {
                                if (in_array($table, $tables)) {
                                    $pdo->exec("TRUNCATE TABLE \"$table\" CASCADE");
                                    echo "<p class='info'>✓ Datos eliminados de: <strong>$table</strong></p>";
                                }
                            }
                        } else {
                            // MySQL/SQLite
                            $stmt = $pdo->query("SHOW TABLES");
                            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            $delete_order = [
                                'transacciones',
                                'transferencias',
                                'presupuestos',
                                'cuentas_bancarias',
                                'categorias',
                                'usuarios',
                                'configuraciones'
                            ];
                            
                            foreach ($delete_order as $table) {
                                if (in_array($table, $tables)) {
                                    $pdo->exec("TRUNCATE TABLE `$table`");
                                    echo "<p class='info'>✓ Datos eliminados de: <strong>$table</strong></p>";
                                }
                            }
                        }
                    }
                    
                    // Ejecutar SQL
                    echo "<h3>Ejecutando SQL...</h3>";
                    
                    // Dividir el SQL en statements individuales
                    $statements = array_filter(
                        array_map('trim', explode(';', $sql_content)),
                        function($stmt) {
                            return !empty($stmt) && 
                                   !preg_match('/^--/', $stmt) && 
                                   !preg_match('/^\/\*/', $stmt);
                        }
                    );
                    
                    $executed = 0;
                    $errors = 0;
                    
                    foreach ($statements as $statement) {
                        if (empty(trim($statement))) continue;
                        
                        try {
                            $pdo->exec($statement);
                            $executed++;
                        } catch (PDOException $e) {
                            // Algunos errores son esperados (como CREATE TABLE IF NOT EXISTS cuando ya existe)
                            if (strpos($e->getMessage(), 'already exists') === false && 
                                strpos($e->getMessage(), 'duplicate') === false) {
                                echo "<p class='warning'>⚠️ Error en statement: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
                                echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
                                $errors++;
                            }
                        }
                    }
                    
                    echo "<hr>";
                    echo "<h2 class='success'>✅ Importación completada!</h2>";
                    echo "<p>Statements ejecutados: <strong>$executed</strong></p>";
                    if ($errors > 0) {
                        echo "<p class='warning'>Errores encontrados: <strong>$errors</strong> (algunos pueden ser esperados)</p>";
                    }
                    echo "<p><a href='dashboard-gastos.php' class='btn btn-primary' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;'>Ir al Dashboard</a></p>";
                    
                } else {
                    throw new Exception("No se pudo establecer conexión a la base de datos.");
                }
            } catch (Exception $e) {
                echo "<p class='error'>❌ Error durante la importación: " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            }
        } else {
            // Mostrar formulario
            ?>
            <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('¿Estás SEGURO de que quieres importar? Esto puede eliminar datos existentes.');">
                <h3>Opciones de Importación</h3>
                
                <p>
                    <label>
                        <input type="checkbox" name="clear_first" value="yes" checked>
                        Limpiar base de datos antes de importar (recomendado)
                    </label>
                </p>
                
                <h3>Método 1: Subir archivo SQL</h3>
                <p>
                    <input type="file" name="sql_file" accept=".sql,.txt">
                </p>
                
                <h3>Método 2: Pegar contenido SQL</h3>
                <p>
                    <textarea name="sql_content" placeholder="Pega aquí el contenido del archivo SQL exportado..."></textarea>
                </p>
                
                <p class="info">
                    <strong>Nota:</strong> Puedes exportar la base de datos local usando <code>export_database.php</code>
                </p>
                
                <input type="hidden" name="import" value="yes">
                <button type="submit" class="danger">⚠️ IMPORTAR BASE DE DATOS</button>
            </form>
            <?php
        }
        ?>
    </div>
</body>
</html>

