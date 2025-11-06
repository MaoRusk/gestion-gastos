<?php
/**
 * Script de depuración para verificar configuración de base de datos
 * Muestra exactamente qué valores está usando el código
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug - Configuración de Base de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 900px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .config-value { font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug - Configuración de Base de Datos</h1>
        <p>Este script muestra exactamente qué valores está usando el código para conectarse a la base de datos.</p>
        <hr>

<?php
// Mostrar variables de entorno RAW
echo "<h2>1. Variables de Entorno (getenv)</h2>";
echo "<table>";
echo "<tr><th>Variable</th><th>Valor (getenv)</th><th>¿Está Configurada?</th></tr>";

$env_vars = [
    'DB_TYPE' => 'Tipo de base de datos',
    'DB_HOST' => 'Host de la base de datos',
    'DB_PORT' => 'Puerto de la base de datos',
    'DB_USER' => 'Usuario de la base de datos',
    'DB_PASSWORD' => 'Contraseña de la base de datos',
    'DB_NAME' => 'Nombre de la base de datos'
];

foreach ($env_vars as $var => $desc) {
    $value = getenv($var);
    $is_set = $value !== false;
    $display_value = $is_set ? ($var === 'DB_PASSWORD' ? '***' : htmlspecialchars($value)) : '<em>No configurado</em>';
    $status = $is_set ? '✅' : '❌';
    $class = $is_set ? 'success' : 'error';
    
    echo "<tr>";
    echo "<td><strong>$var</strong><br><small>$desc</small></td>";
    echo "<td><span class='config-value'>$display_value</span></td>";
    echo "<td class='$class'>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Mostrar valores que el código está usando (después de cargar config.php)
echo "<h2>2. Valores que el Código Está Usando (después de config.php)</h2>";

// Cargar config.php para ver qué valores define
require_once "layouts/config.php";

echo "<table>";
echo "<tr><th>Constante</th><th>Valor Usado</th><th>Fuente</th></tr>";

$configs = [
    'DB_TYPE' => DB_TYPE,
    'DB_SERVER' => DB_SERVER,
    'DB_PORT' => defined('DB_PORT') ? DB_PORT : 'No definido',
    'DB_USERNAME' => DB_USERNAME,
    'DB_PASSWORD' => DB_PASSWORD ? '***' : 'Vacío',
    'DB_NAME' => DB_NAME
];

foreach ($configs as $const => $value) {
    $env_var = str_replace('DB_SERVER', 'DB_HOST', str_replace('DB_USERNAME', 'DB_USER', $const));
    $env_value = getenv($env_var);
    $source = $env_value !== false ? 'Variable de Entorno' : 'Valor por Defecto';
    $class = $env_value !== false ? 'success' : 'warning';
    
    echo "<tr>";
    echo "<td><strong>$const</strong></td>";
    echo "<td><span class='config-value'>" . htmlspecialchars($value) . "</span></td>";
    echo "<td class='$class'>$source</td>";
    echo "</tr>";
}

echo "</table>";

// Mostrar string de conexión que se está usando
echo "<h2>3. String de Conexión que se Está Usando</h2>";

$connection_string = "pgsql:host=" . DB_SERVER . ";port=" . (defined('DB_PORT') ? DB_PORT : '5432') . ";dbname=" . DB_NAME;
echo "<p class='info'>String de conexión PDO:</p>";
echo "<pre>" . htmlspecialchars($connection_string) . "</pre>";

echo "<p class='info'>Usuario: <strong>" . htmlspecialchars(DB_USERNAME) . "</strong></p>";
echo "<p class='info'>Contraseña: <strong>" . (DB_PASSWORD ? '***' : 'Vacía') . "</strong></p>";

// Intentar conexión
echo "<h2>4. Prueba de Conexión</h2>";

if (isset($link)) {
    echo "<p class='success'>✅ Variable \$link está definida</p>";
    
    if (isset($link->type)) {
        echo "<p class='info'>Tipo de conexión: <strong>" . $link->type . "</strong></p>";
    }
    
    if (isset($link->pdo)) {
        echo "<p class='success'>✅ Conexión PDO establecida</p>";
        
        try {
            $stmt = $link->pdo->query("SELECT current_database(), current_user, version()");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            echo "<tr><td>Base de Datos Conectada</td><td>" . htmlspecialchars($result['current_database']) . "</td></tr>";
            echo "<tr><td>Usuario Conectado</td><td>" . htmlspecialchars($result['current_user']) . "</td></tr>";
            echo "<tr><td>Versión PostgreSQL</td><td>" . htmlspecialchars(substr($result['version'], 0, 50)) . "...</td></tr>";
            echo "</table>";
            
            // Verificar tablas
            $stmt = $link->pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public'");
            $table_count = $stmt->fetchColumn();
            echo "<p class='info'>Tablas en la base de datos: <strong>$table_count</strong></p>";
            
        } catch (PDOException $e) {
            echo "<p class='error'>❌ Error ejecutando consulta: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p class='warning'>⚠️ Variable \$link existe pero no tiene PDO</p>";
    }
} else {
    echo "<p class='error'>❌ Variable \$link NO está definida</p>";
    echo "<p class='error'>Esto significa que hubo un error al cargar config.php</p>";
}

// Comparar con valores esperados
echo "<h2>5. Comparación con Valores Esperados</h2>";

$expected = [
    'DB_HOST' => 'dpg-d45svnvdiees738gdg90-a',
    'DB_PORT' => '5432',
    'DB_USER' => 'fime_gastos_db_user',
    'DB_NAME' => 'fime_gastos_db'
];

echo "<table>";
echo "<tr><th>Variable</th><th>Valor Esperado</th><th>Valor Actual</th><th>¿Coincide?</th></tr>";

foreach ($expected as $var => $expected_value) {
    $actual = getenv($var);
    if ($var === 'DB_HOST') {
        $actual = DB_SERVER;
    } elseif ($var === 'DB_USER') {
        $actual = DB_USERNAME;
    } elseif ($var === 'DB_NAME') {
        $actual = DB_NAME;
    } elseif ($var === 'DB_PORT') {
        $actual = defined('DB_PORT') ? DB_PORT : getenv('DB_PORT');
    }
    
    $matches = ($actual === $expected_value);
    $status = $matches ? '✅' : '❌';
    $class = $matches ? 'success' : 'error';
    
    echo "<tr class='$class'>";
    echo "<td><strong>$var</strong></td>";
    echo "<td><span class='config-value'>" . htmlspecialchars($expected_value) . "</span></td>";
    echo "<td><span class='config-value'>" . htmlspecialchars($actual ?: 'No configurado') . "</span></td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

// Recomendaciones
echo "<hr>";
echo "<h2>📋 Recomendaciones</h2>";

$missing_vars = [];
foreach ($env_vars as $var => $desc) {
    if (getenv($var) === false) {
        $missing_vars[] = $var;
    }
}

if (!empty($missing_vars)) {
    echo "<p class='error'><strong>❌ Variables de entorno faltantes:</strong></p>";
    echo "<ul>";
    foreach ($missing_vars as $var) {
        echo "<li><strong>$var</strong></li>";
    }
    echo "</ul>";
    
    echo "<p class='warning'><strong>⚠️ Necesitas configurar estas variables en Render:</strong></p>";
    echo "<ol>";
    echo "<li>Ve a Render Dashboard → Tu Web Service → Environment</li>";
    echo "<li>Agrega las siguientes variables:</li>";
    echo "</ol>";
    
    echo "<pre>";
    echo "DB_TYPE=postgresql\n";
    echo "DB_HOST=dpg-d45svnvdiees738gdg90-a\n";
    echo "DB_PORT=5432\n";
    echo "DB_USER=fime_gastos_db_user\n";
    echo "DB_PASSWORD=XwQkjDmX8JZP27hLdPFxKbvSjqlKESvB\n";
    echo "DB_NAME=fime_gastos_db\n";
    echo "</pre>";
    
    echo "<p class='info'>💡 <strong>Importante:</strong> Después de agregar las variables, Render reiniciará automáticamente el servicio.</p>";
} else {
    echo "<p class='success'><strong>✅ Todas las variables de entorno están configuradas.</strong></p>";
    
    if (DB_SERVER !== 'dpg-d45svnvdiees738gdg90-a') {
        echo "<p class='warning'><strong>⚠️ El host configurado no coincide con el esperado.</strong></p>";
        echo "<p>Host actual: <strong>" . htmlspecialchars(DB_SERVER) . "</strong></p>";
        echo "<p>Host esperado: <strong>dpg-d45svnvdiees738gdg90-a</strong></p>";
        echo "<p class='info'>💡 En Render, usa el <strong>Internal Host</strong>, no el External Host.</p>";
    }
}

?>

    </div>
</body>
</html>

