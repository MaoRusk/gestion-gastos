<?php
/**
 * Script de diagnóstico para problemas de login
 */

require_once "layouts/config.php";
require_once "includes/auth_functions.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Login</title>
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
        <h1>🔍 Diagnóstico de Login</h1>
        <hr>

<?php
// Verificar conexión
if (!isset($link)) {
    die("<p class='error'>❌ No se pudo establecer conexión a la base de datos.</p>");
}

echo "<h2>1. Verificación de Conexión</h2>";
echo "<p class='success'>✅ Conexión establecida correctamente</p>";
echo "<p class='info'>Tipo: " . (isset($link->type) ? $link->type : 'mysql') . "</p>";

// Verificar si la tabla usuarios existe
echo "<h2>2. Verificación de Tabla Usuarios</h2>";
try {
    if (isset($link->pdo)) {
        $stmt = $link->pdo->query("SELECT COUNT(*) FROM usuarios");
        $user_count = $stmt->fetchColumn();
        echo "<p class='success'>✅ Tabla 'usuarios' existe con <strong>$user_count</strong> usuarios</p>";
    } else {
        $result = mysqli_query($link, "SELECT COUNT(*) as count FROM usuarios");
        $row = mysqli_fetch_assoc($result);
        $user_count = $row['count'];
        echo "<p class='success'>✅ Tabla 'usuarios' existe con <strong>$user_count</strong> usuarios</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    die("</div></body></html>");
}

// Buscar usuario admin
echo "<h2>3. Verificación de Usuario Admin</h2>";
$admin_email = 'admin@fime.com';
$admin_password = 'admin123';

try {
    if (isset($link->pdo)) {
        $stmt = $link->pdo->prepare("SELECT id, nombre, email, password_hash, activo FROM usuarios WHERE email = ?");
        $stmt->execute([$admin_email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = mysqli_prepare($link, "SELECT id, nombre, email, password_hash, activo FROM usuarios WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $admin_email);
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
        echo "<tr><td>Activo</td><td>" . ($admin['activo'] ? 'Sí ✅' : 'No ❌') . "</td></tr>";
        echo "<tr><td>Password Hash</td><td>" . htmlspecialchars(substr($admin['password_hash'], 0, 30)) . "...</td></tr>";
        echo "</table>";
        
        // Verificar contraseña
        echo "<h2>4. Verificación de Contraseña</h2>";
        if (password_verify($admin_password, $admin['password_hash'])) {
            echo "<p class='success'>✅ La contraseña 'admin123' es correcta para este hash</p>";
        } else {
            echo "<p class='error'>❌ La contraseña 'admin123' NO coincide con el hash almacenado</p>";
            echo "<p class='warning'>⚠️ El hash almacenado es: <code>" . htmlspecialchars($admin['password_hash']) . "</code></p>";
            echo "<p class='info'>💡 Necesitas actualizar la contraseña del usuario admin</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Usuario admin NO encontrado con email: $admin_email</p>";
        echo "<p class='info'>💡 Necesitas crear el usuario admin. Visita: <a href='create_admin.php'>create_admin.php</a></p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error verificando usuario admin: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Probar función loginUser
echo "<h2>5. Prueba de Función loginUser()</h2>";
try {
    $result = loginUser($admin_email, $admin_password);
    
    echo "<p class='info'>Resultado de loginUser():</p>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result['success']) {
        echo "<p class='success'>✅ La función loginUser() funciona correctamente</p>";
        echo "<p class='info'>Usuario ID en sesión: " . getCurrentUserId() . "</p>";
        echo "<p class='info'>Nombre en sesión: " . getCurrentUserName() . "</p>";
    } else {
        echo "<p class='error'>❌ La función loginUser() falló: " . htmlspecialchars($result['message']) . "</p>";
        
        if (isset($result['needs_init'])) {
            echo "<p class='warning'>⚠️ La base de datos necesita inicialización</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error ejecutando loginUser(): " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Verificar sesión
echo "<h2>6. Estado de Sesión</h2>";
if (session_status() == PHP_SESSION_NONE) {
    echo "<p class='warning'>⚠️ Sesión no iniciada</p>";
} else {
    echo "<p class='success'>✅ Sesión activa</p>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

// Resumen
echo "<hr>";
echo "<h2>📋 Resumen y Recomendaciones</h2>";

if (isset($admin) && $admin) {
    if (password_verify($admin_password, $admin['password_hash'])) {
        echo "<p class='success'><strong>✅ Todo parece estar correcto.</strong></p>";
        echo "<p>Si aún no puedes hacer login, el problema podría ser:</p>";
        echo "<ul>";
        echo "<li>El usuario no está activo (activo = false)</li>";
        echo "<li>Problema con las sesiones PHP</li>";
        echo "<li>Error en el formulario de login</li>";
        echo "</ul>";
    } else {
        echo "<p class='error'><strong>❌ La contraseña no coincide.</strong></p>";
        echo "<p>Necesitas actualizar la contraseña. Ve a: <a href='create_admin.php'>create_admin.php</a></p>";
    }
} else {
    echo "<p class='error'><strong>❌ Usuario admin no existe.</strong></p>";
    echo "<p>Necesitas crear el usuario admin. Ve a: <a href='create_admin.php'>create_admin.php</a></p>";
}
?>

    </div>
</body>
</html>
