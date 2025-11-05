<?php
/**
 * Script de verificación de base de datos
 * Verifica que las tablas existan y que el usuario admin esté creado
 */

require_once "layouts/config.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verificación de Base de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación de Base de Datos</h1>
        <hr>

<?php
// Verificar conexión
if (!isset($link)) {
    die("<p class='error'>❌ No se pudo establecer conexión a la base de datos.</p>");
}

echo "<p class='info'>ℹ️ Tipo de base de datos: <strong>" . (isset($link->type) ? $link->type : 'mysql') . "</strong></p>";

// Verificar variables de entorno
echo "<h2>📋 Variables de Entorno</h2>";
echo "<table>";
echo "<tr><th>Variable</th><th>Valor</th></tr>";
echo "<tr><td>DB_TYPE</td><td>" . (getenv('DB_TYPE') ?: 'No configurado') . "</td></tr>";
echo "<tr><td>DB_HOST</td><td>" . (getenv('DB_HOST') ?: 'No configurado') . "</td></tr>";
echo "<tr><td>DB_PORT</td><td>" . (getenv('DB_PORT') ?: 'No configurado') . "</td></tr>";
echo "<tr><td>DB_USER</td><td>" . (getenv('DB_USER') ?: 'No configurado') . "</td></tr>";
echo "<tr><td>DB_NAME</td><td>" . (getenv('DB_NAME') ?: 'No configurado') . "</td></tr>";
echo "<tr><td>DB_PASSWORD</td><td>" . (getenv('DB_PASSWORD') ? '***' : 'No configurado') . "</td></tr>";
echo "</table>";

// Verificar tablas
echo "<h2>📊 Verificación de Tablas</h2>";
$required_tables = ['usuarios', 'cuentas_bancarias', 'categorias', 'transacciones', 'transferencias', 'presupuestos'];

$tables_exist = [];
$all_exist = true;

foreach ($required_tables as $table) {
    try {
        if (isset($link->pdo)) {
            // Usando PDO
            $stmt = $link->pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = '$table'");
            $exists = $stmt->fetchColumn() > 0;
        } else {
            // Usando mysqli
            $result = mysqli_query($link, "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_name = '$table'");
            $row = mysqli_fetch_assoc($result);
            $exists = $row['count'] > 0;
        }
        
        $tables_exist[$table] = $exists;
        if (!$exists) $all_exist = false;
        
        $status = $exists ? "✅" : "❌";
        $class = $exists ? "success" : "error";
        echo "<p class='$class'>$status Tabla <strong>$table</strong>: " . ($exists ? "Existe" : "No existe") . "</p>";
    } catch (Exception $e) {
        $tables_exist[$table] = false;
        $all_exist = false;
        echo "<p class='error'>❌ Error verificando tabla <strong>$table</strong>: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Verificar usuario admin
echo "<h2>👤 Verificación de Usuario Admin</h2>";
try {
    if (isset($link->pdo)) {
        $stmt = $link->pdo->prepare("SELECT id, nombre, email, activo FROM usuarios WHERE email = ?");
        $stmt->execute(['admin@fime.com']);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = mysqli_prepare($link, "SELECT id, nombre, email, activo FROM usuarios WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", "admin@fime.com");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);
    }
    
    if ($admin) {
        echo "<p class='success'>✅ Usuario admin encontrado:</p>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        echo "<tr><td>ID</td><td>" . htmlspecialchars($admin['id']) . "</td></tr>";
        echo "<tr><td>Nombre</td><td>" . htmlspecialchars($admin['nombre']) . "</td></tr>";
        echo "<tr><td>Email</td><td>" . htmlspecialchars($admin['email']) . "</td></tr>";
        echo "<tr><td>Activo</td><td>" . ($admin['activo'] ? 'Sí' : 'No') . "</td></tr>";
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Usuario admin NO encontrado. Necesitas crearlo.</p>";
        echo "<p><a href='create_admin.php'>Crear Usuario Admin</a></p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error verificando usuario admin: " . htmlspecialchars($e->getMessage()) . "</p>";
    if (strpos($e->getMessage(), 'does not exist') !== false || strpos($e->getMessage(), 'relation') !== false) {
        echo "<p class='warning'>⚠️ La tabla 'usuarios' no existe. Necesitas ejecutar <a href='init_database.php'>init_database.php</a> primero.</p>";
    }
}

// Resumen
echo "<hr>";
echo "<h2>📋 Resumen</h2>";
if ($all_exist) {
    echo "<p class='success'><strong>✅ Todas las tablas necesarias existen.</strong></p>";
} else {
    echo "<p class='error'><strong>❌ Faltan algunas tablas. Ejecuta <a href='init_database.php'>init_database.php</a>.</strong></p>";
}

if (isset($admin) && $admin) {
    echo "<p class='success'><strong>✅ Usuario admin configurado correctamente.</strong></p>";
    echo "<p>Puedes intentar hacer login ahora.</p>";
} else {
    echo "<p class='warning'><strong>⚠️ Usuario admin no encontrado. Necesitas crearlo.</strong></p>";
}
?>

    </div>
</body>
</html>

