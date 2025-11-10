<?php
/**
 * Script para sincronizar base de datos local con producción
 * 
 * Este script:
 * 1. Lee todos los datos de la base de datos LOCAL
 * 2. Limpia las tablas en PRODUCCIÓN (o local si se especifica)
 * 3. Inserta todos los datos de local en producción
 * 
 * Uso desde navegador: http://localhost/sync_database.php
 * 
 * ADVERTENCIA: Este script eliminará TODOS los datos de la base de datos destino
 * y los reemplazará con los datos de la base de datos origen (local)
 */

// Configuración
// Si quieres sincronizar a producción, cambia estas variables de entorno
// o modifica directamente los valores aquí
$TARGET_DB_TYPE = getenv('TARGET_DB_TYPE') ?: 'postgresql'; // 'postgresql', 'mysql', 'sqlite'
$TARGET_DB_HOST = getenv('DB_HOST') ?: 'localhost';
$TARGET_DB_PORT = getenv('TARGET_DB_PORT') ?: '5432';
$TARGET_DB_NAME = getenv('TARGET_DB_NAME') ?: 'fime_gastos_db';
$TARGET_DB_USER = getenv('DB_USER') ?: 'root';
$TARGET_DB_PASS = getenv('DB_PASSWORD') ?: '1234';

// Incluir configuración para obtener conexión LOCAL
require_once "layouts/config.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sincronizar Base de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 1000px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        .danger { background: #dc3545; }
        .danger:hover { background: #c82333; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 4px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        .form-group { margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Sincronizar Base de Datos</h1>
        <p class="warning">⚠️ ADVERTENCIA: Este script eliminará TODOS los datos de la base de datos destino y los reemplazará con los datos de la base de datos local.</p>
        
        <?php
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            try {
                // Obtener configuración de destino desde el formulario o usar defaults
                $target_db_type = $_POST['target_db_type'] ?? $TARGET_DB_TYPE;
                $target_db_host = $_POST['target_db_host'] ?? $TARGET_DB_HOST;
                $target_db_port = $_POST['target_db_port'] ?? $TARGET_DB_PORT;
                $target_db_name = $_POST['target_db_name'] ?? $TARGET_DB_NAME;
                $target_db_user = $_POST['target_db_user'] ?? $TARGET_DB_USER;
                $target_db_pass = $_POST['target_db_pass'] ?? $TARGET_DB_PASS;
                
                echo "<h2>Iniciando sincronización...</h2>";
                echo "<p class='info'>📥 Origen: Base de datos LOCAL (config.php)</p>";
                echo "<p class='info'>📤 Destino: $target_db_type://$target_db_user@$target_db_host:$target_db_port/$target_db_name</p>";
                
                // Conectar a base de datos LOCAL (origen)
                if (!isset($link) || !isset($link->pdo)) {
                    throw new Exception("No se pudo conectar a la base de datos LOCAL. Verifica layouts/config.php");
                }
                
                $source_pdo = $link->pdo;
                $source_type = isset($link->type) ? $link->type : 'unknown';
                
                echo "<p class='success'>✓ Conectado a base de datos LOCAL ($source_type)</p>";
                
                // Conectar a base de datos DESTINO
                try {
                    if ($target_db_type === 'postgresql') {
                        $target_dsn = "pgsql:host=$target_db_host;port=$target_db_port;dbname=$target_db_name";
                    } elseif ($target_db_type === 'mysql') {
                        $target_dsn = "mysql:host=$target_db_host;dbname=$target_db_name";
                    } else {
                        throw new Exception("Tipo de base de datos destino no soportado: $target_db_type");
                    }
                    
                    $target_pdo = new PDO($target_dsn, $target_db_user, $target_db_pass);
                    $target_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    echo "<p class='success'>✓ Conectado a base de datos DESTINO ($target_db_type)</p>";
                } catch (PDOException $e) {
                    throw new Exception("Error conectando a base de datos destino: " . $e->getMessage());
                }
                
                // Tablas a sincronizar (excluyendo metas_ahorro)
                $tables_to_sync = [
                    'usuarios',
                    'cuentas_bancarias',
                    'categorias',
                    'transacciones',
                    'transferencias',
                    'presupuestos',
                    'configuraciones'
                ];
                
                echo "<h3>📊 Sincronizando tablas...</h3>";
                
                $total_records = 0;
                $total_tables = 0;
                
                foreach ($tables_to_sync as $table) {
                    echo "<h4>Tabla: $table</h4>";
                    
                    // 1. Leer datos de la base de datos LOCAL
                    try {
                        $source_stmt = $source_pdo->query("SELECT * FROM \"$table\"");
                        $rows = $source_stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($rows)) {
                            echo "<p class='info'>⏭️ Tabla $table está vacía en origen, omitiendo...</p><br>";
                            continue;
                        }
                        
                        echo "<p class='info'>📥 Leídos " . count($rows) . " registros de LOCAL</p>";
                        
                        // 2. Limpiar tabla en DESTINO
                        try {
                            if ($target_db_type === 'postgresql') {
                                $target_pdo->exec("TRUNCATE TABLE \"$table\" CASCADE");
                            } else {
                                $target_pdo->exec("TRUNCATE TABLE `$table`");
                            }
                            echo "<p class='info'>🗑️ Tabla $table limpiada en DESTINO</p>";
                        } catch (PDOException $e) {
                            // Si la tabla no existe, intentar crearla (estructura básica)
                            echo "<p class='warning'>⚠️ Tabla $table no existe en destino. Asegúrate de que la estructura esté creada primero.</p>";
                            echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                            continue;
                        }
                        
                        // 3. Obtener nombres de columnas
                        $columns = array_keys($rows[0]);
                        $columns_str = '"' . implode('", "', $columns) . '"';
                        
                        // 4. Insertar datos en DESTINO
                        $inserted = 0;
                        $errors = 0;
                        
                        // Preparar statement de inserción
                        $placeholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
                        $insert_sql = "INSERT INTO \"$table\" ($columns_str) VALUES $placeholders";
                        
                        $target_stmt = $target_pdo->prepare($insert_sql);
                        
                        foreach ($rows as $row) {
                            try {
                                $values = array_values($row);
                                $target_stmt->execute($values);
                                $inserted++;
                            } catch (PDOException $e) {
                                $errors++;
                                if ($errors <= 3) { // Mostrar solo los primeros 3 errores
                                    echo "<p class='warning'>⚠️ Error insertando registro: " . htmlspecialchars($e->getMessage()) . "</p>";
                                }
                            }
                        }
                        
                        echo "<p class='success'>✓ Insertados $inserted registros en DESTINO</p>";
                        if ($errors > 0) {
                            echo "<p class='error'>❌ Errores: $errors</p>";
                        }
                        
                        $total_records += $inserted;
                        $total_tables++;
                        
                        echo "<br>";
                        
                    } catch (PDOException $e) {
                        echo "<p class='error'>❌ Error procesando tabla $table: " . htmlspecialchars($e->getMessage()) . "</p><br>";
                        continue;
                    }
                }
                
                // Reiniciar secuencias en PostgreSQL
                if ($target_db_type === 'postgresql') {
                    echo "<h3>🔄 Reiniciando secuencias...</h3>";
                    try {
                        $sequences = $target_pdo->query("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = 'public'");
                        $seq_count = 0;
                        while ($seq_row = $sequences->fetch(PDO::FETCH_ASSOC)) {
                            $seq_name = $seq_row['sequence_name'];
                            try {
                                // Obtener el máximo ID de la tabla correspondiente
                                $table_name = str_replace('_id_seq', '', $seq_name);
                                if (in_array($table_name, $tables_to_sync)) {
                                    $max_stmt = $target_pdo->query("SELECT COALESCE(MAX(id), 0) FROM \"$table_name\"");
                                    $max_id = $max_stmt->fetchColumn();
                                    $target_pdo->exec("ALTER SEQUENCE \"$seq_name\" RESTART WITH " . ($max_id + 1));
                                    $seq_count++;
                                }
                            } catch (Exception $e) {
                                // Ignorar errores de secuencias que no corresponden a tablas
                            }
                        }
                        echo "<p class='success'>✓ $seq_count secuencias reiniciadas</p>";
                    } catch (Exception $e) {
                        echo "<p class='warning'>⚠️ No se pudieron reiniciar secuencias: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                
                echo "<hr>";
                echo "<h2 class='success'>✅ Sincronización completada!</h2>";
                echo "<p><strong>Resumen:</strong></p>";
                echo "<ul>";
                echo "<li>Tablas sincronizadas: <strong>$total_tables</strong></li>";
                echo "<li>Registros insertados: <strong>$total_records</strong></li>";
                echo "<li>Base de datos destino: <strong>$target_db_name</strong></li>";
                echo "</ul>";
                echo "<p><a href='dashboard-gastos.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#007bff;color:white;text-decoration:none;border-radius:4px;'>Ir al Dashboard</a></p>";
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Error durante la sincronización: " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            }
        } else {
            // Mostrar formulario
            ?>
            <form method="POST" onsubmit="return confirm('¿Estás SEGURO de que quieres sincronizar? Esto eliminará TODOS los datos de la base de datos destino.');">
                <h2>Configuración de Base de Datos Destino</h2>
                <p class="info">Los datos se leerán de la base de datos LOCAL (configurada en layouts/config.php) y se insertarán en la base de datos destino que especifiques.</p>
                
                <div class="form-group">
                    <label>Tipo de Base de Datos Destino:</label>
                    <select name="target_db_type" required>
                        <option value="postgresql" <?php echo $TARGET_DB_TYPE === 'postgresql' ? 'selected' : ''; ?>>PostgreSQL</option>
                        <option value="mysql" <?php echo $TARGET_DB_TYPE === 'mysql' ? 'selected' : ''; ?>>MySQL/MariaDB</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Host:</label>
                    <input type="text" name="target_db_host" value="<?php echo htmlspecialchars($TARGET_DB_HOST); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Puerto:</label>
                    <input type="text" name="target_db_port" value="<?php echo htmlspecialchars($TARGET_DB_PORT); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Nombre de Base de Datos:</label>
                    <input type="text" name="target_db_name" value="<?php echo htmlspecialchars($TARGET_DB_NAME); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Usuario:</label>
                    <input type="text" name="target_db_user" value="<?php echo htmlspecialchars($TARGET_DB_USER); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Contraseña:</label>
                    <input type="password" name="target_db_pass" value="<?php echo htmlspecialchars($TARGET_DB_PASS); ?>" required>
                </div>
                
                <p class="warning">
                    <strong>⚠️ IMPORTANTE:</strong><br>
                    - Este script eliminará TODOS los datos de la base de datos destino<br>
                    - Asegúrate de que la estructura de tablas esté creada en el destino<br>
                    - Se sincronizarán las siguientes tablas: usuarios, cuentas_bancarias, categorias, transacciones, transferencias, presupuestos, configuraciones<br>
                    - La tabla metas_ahorro NO se sincroniza (no se usa en el proyecto)
                </p>
                
                <p class="info">
                    <strong>💡 Tip:</strong> Para sincronizar a producción en Render.com, usa las credenciales de tu base de datos de producción.<br>
                    Puedes encontrarlas en las variables de entorno de tu servicio en Render.com.
                </p>
                
                <input type="hidden" name="confirm" value="yes">
                <button type="submit" class="danger">⚠️ CONFIRMAR SINCRONIZACIÓN</button>
            </form>
            <?php
        }
        ?>
    </div>
</body>
</html>

