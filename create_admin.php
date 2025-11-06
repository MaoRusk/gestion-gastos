<?php
/**
 * Script para crear usuario admin
 * Ejecutar después de inicializar la base de datos
 */

require_once "layouts/config.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Crear Usuario Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
    </style>
</head>
</body>
</html>

<?php
// Verificar conexión
if (!isset($link)) {
    die("<p class='error'>❌ No se pudo establecer conexión a la base de datos.</p>");
}

// Verificar que la tabla usuarios existe
try {
    if (isset($link->pdo)) {
        $stmt = $link->pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'usuarios'");
        $table_exists = $stmt->fetchColumn() > 0;
    } else {
        $result = mysqli_query($link, "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_name = 'usuarios'");
        $row = mysqli_fetch_assoc($result);
        $table_exists = $row['count'] > 0;
    }
    
    if (!$table_exists) {
        die("<p class='error'>❌ La tabla 'usuarios' no existe. Por favor ejecuta <a href='init_database.php'>init_database.php</a> primero.</p>");
    }
} catch (Exception $e) {
    die("<p class='error'>❌ Error verificando tabla: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// Verificar si el usuario admin ya existe
$admin_exists = false;
try {
    if (isset($link->pdo)) {
        $stmt = $link->pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute(['admin@fime.com']);
        $admin_exists = $stmt->fetch() !== false;
    } else {
        $stmt = mysqli_prepare($link, "SELECT id FROM usuarios WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", "admin@fime.com");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin_exists = mysqli_num_rows($result) > 0;
    }
} catch (Exception $e) {
    echo "<p class='error'>⚠️ Error verificando usuario: " . htmlspecialchars($e->getMessage()) . "</p>";
}

if ($admin_exists) {
    echo "<p class='info'>ℹ️ El usuario admin ya existe. No es necesario crearlo.</p>";
    echo "<p>Puedes intentar hacer login con:</p>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> admin@fime.com</li>";
    echo "<li><strong>Contraseña:</strong> admin123</li>";
    echo "</ul>";
    echo "<p><a href='auth-signin-basic.php'>Ir al Login</a></p>";
} else {
    // Crear usuario admin
    $nombre = "Administrador";
    $email = "admin@fime.com";
    $password = "admin123";
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        if (isset($link->pdo)) {
            $stmt = $link->pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, activo) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$nombre, $email, $hashed_password, true]);
            
            if ($result) {
                $user_id = $link->pdo->lastInsertId();
                echo "<p class='success'><strong>✅ Usuario admin creado exitosamente!</strong></p>";
                echo "<p>Credenciales:</p>";
                echo "<ul>";
                echo "<li><strong>Email:</strong> $email</li>";
                echo "<li><strong>Contraseña:</strong> $password</li>";
                echo "</ul>";
                echo "<p><a href='auth-signin-basic.php'>Ir al Login</a></p>";
            } else {
                echo "<p class='error'>❌ Error al crear usuario admin.</p>";
            }
        } else {
            $stmt = mysqli_prepare($link, "INSERT INTO usuarios (nombre, email, password_hash, activo) VALUES (?, ?, ?, ?)");
            $activo = 1;
            mysqli_stmt_bind_param($stmt, "sssi", $nombre, $email, $hashed_password, $activo);
            
            if (mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($link);
                echo "<p class='success'><strong>✅ Usuario admin creado exitosamente!</strong></p>";
                echo "<p>Credenciales:</p>";
                echo "<ul>";
                echo "<li><strong>Email:</strong> $email</li>";
                echo "<li><strong>Contraseña:</strong> $password</li>";
                echo "</ul>";
                echo "<p><a href='auth-signin-basic.php'>Ir al Login</a></p>";
            } else {
                echo "<p class='error'>❌ Error al crear usuario admin: " . mysqli_error($link) . "</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error al crear usuario admin: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

    </div>
</body>
</html>

