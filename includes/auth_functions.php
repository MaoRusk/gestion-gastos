<?php
/**
 * Funciones de autenticación para el sistema de gastos personales
 */

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verificar si el usuario está autenticado
 */
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

/**
 * Obtener el ID del usuario actual
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Obtener el nombre del usuario actual
 */
function getCurrentUserName() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Usuario';
}

/**
 * Obtener el email del usuario actual
 */
function getCurrentUserEmail() {
    return isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
}

/**
 * Verificar si el usuario actual es administrador
 * Criterio actual: email en la lista de administradores
 */
function isAdmin() {
    if (!isLoggedIn()) return false;
    $adminEmails = [
        'admin@fime.com'
    ];
    return in_array(getCurrentUserEmail(), $adminEmails, true);
}

/**
 * Registrar un nuevo usuario
 */
function registerUser($nombre, $email, $password, $telefono = null, $fecha_nacimiento = null, $genero = null, $ciudad = null, $estado = null) {
    global $link;
    
    // Verificar si el email ya existe
    $check_email = "SELECT id FROM usuarios WHERE email = ?";
    
    // Check if using PDO
    if (isset($link->pdo)) {
        // Use PDO directly
        $stmt = $link->pdo->prepare($check_email);
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'El email ya está registrado'];
        }
        
        // Hash de la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar nuevo usuario (incluye genero/ciudad/estado)
        $insert_user = "INSERT INTO usuarios (nombre, email, password_hash, telefono, fecha_nacimiento, genero, ciudad, estado, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $link->pdo->prepare($insert_user);
        
        // activo por defecto = TRUE/1
        $activo_val = 1;
        if ($stmt->execute([$nombre, $email, $hashed_password, $telefono, $fecha_nacimiento, $genero, $ciudad, $estado, $activo_val])) {
            $user_id = $link->pdo->lastInsertId();
            
            // Crear categorías personalizadas para el usuario
            createUserCategories($user_id);
            
            return ['success' => true, 'message' => 'Usuario registrado exitosamente', 'user_id' => $user_id];
        } else {
            $error = $link->pdo->errorInfo();
            return ['success' => false, 'message' => 'Error al registrar usuario: ' . (isset($error[2]) ? $error[2] : 'Error desconocido')];
        }
    } else {
        // Use mysqli
        $stmt = mysqli_prepare($link, $check_email);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            return ['success' => false, 'message' => 'El email ya está registrado'];
        }
        
        // Hash de la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar nuevo usuario (incluye genero/ciudad/estado y activo)
        $insert_user = "INSERT INTO usuarios (nombre, email, password_hash, telefono, fecha_nacimiento, genero, ciudad, estado, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $insert_user);
        $activo_int = 1;
        mysqli_stmt_bind_param($stmt, "ssssssssi", $nombre, $email, $hashed_password, $telefono, $fecha_nacimiento, $genero, $ciudad, $estado, $activo_int);
        
        if (mysqli_stmt_execute($stmt)) {
            $user_id = mysqli_insert_id($link);
            
            // Crear categorías personalizadas para el usuario
            createUserCategories($user_id);
            
            return ['success' => true, 'message' => 'Usuario registrado exitosamente', 'user_id' => $user_id];
        } else {
            return ['success' => false, 'message' => 'Error al registrar usuario: ' . mysqli_error($link)];
        }
    }
}

/**
 * Autenticar usuario
 */
function loginUser($email, $password) {
    global $link;
    
    // Use boolean TRUE for PostgreSQL, numeric 1 for MySQL/SQLite
    $activeCondition = (defined('DB_TYPE') && DB_TYPE === 'postgresql') ? 'activo = TRUE' : 'activo = 1';
    $sql = "SELECT id, nombre, email, password_hash FROM usuarios WHERE email = ? AND " . $activeCondition;
    
    // Check if using PDO (PostgreSQL, SQLite, or MySQL via PDO)
    if (isset($link->pdo)) {
        // Use PDO directly
        try {
            $stmt = $link->pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Check if error is about table not existing
            if (strpos($e->getMessage(), 'does not exist') !== false || 
                strpos($e->getMessage(), 'Undefined table') !== false ||
                strpos($e->getMessage(), 'relation') !== false) {
                return [
                    'success' => false,
                    'message' => "La base de datos no está inicializada. Ejecuta '/init_database.php' desde el navegador o, si estás en la máquina de desarrollo, ejecuta: php migrate_database.php",
                    'needs_init' => true
                ];
            }
            throw $e; // Re-throw if it's a different error
        }
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Iniciar sesión
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            
            // Actualizar último acceso
            $update_access = "UPDATE usuarios SET fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = ?";
            $update_stmt = $link->pdo->prepare($update_access);
            $update_stmt->execute([$user['id']]);
            
            return ['success' => true, 'message' => 'Login exitoso'];
        } else {
            return ['success' => false, 'message' => $user ? 'Contraseña incorrecta' : 'Usuario no encontrado'];
        }
    } else {
        // Use mysqli
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $user['password_hash'])) {
                // Iniciar sesión
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                
                // Actualizar último acceso
                $update_access = "UPDATE usuarios SET fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = ?";
                $stmt = mysqli_prepare($link, $update_access);
                mysqli_stmt_bind_param($stmt, "i", $user['id']);
                mysqli_stmt_execute($stmt);
                
                return ['success' => true, 'message' => 'Login exitoso'];
            } else {
                return ['success' => false, 'message' => 'Contraseña incorrecta'];
            }
        } else {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
    }
}

/**
 * Cerrar sesión
 */
function logoutUser() {
    session_unset();
    session_destroy();
}

/**
 * Crear categorías personalizadas para un usuario
 */
function createUserCategories($user_id) {
    global $link;
    
    // Obtener categorías predefinidas
    // Use boolean TRUE for PostgreSQL, numeric 1 for MySQL/SQLite
    $esPredefCondition = (defined('DB_TYPE') && DB_TYPE === 'postgresql') ? 'es_predefinida = TRUE' : 'es_predefinida = 1';
    $sql = "SELECT * FROM categorias WHERE " . $esPredefCondition;
    
    // Check if using PDO
    if (isset($link->pdo)) {
        // Use PDO directly
        $stmt = $link->pdo->prepare($sql);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($categories as $category) {
            // Use FALSE for PostgreSQL booleans, 0 otherwise
            $esPredefValue = (defined('DB_TYPE') && DB_TYPE === 'postgresql') ? 'FALSE' : '0';
            $insert = "INSERT INTO categorias (usuario_id, nombre, tipo, color, icono, es_predefinida) VALUES (?, ?, ?, ?, ?, " . $esPredefValue . ")";
            $insert_stmt = $link->pdo->prepare($insert);
            $insert_stmt->execute([$user_id, $category['nombre'], $category['tipo'], $category['color'], $category['icono']]);
        }
    } else {
        // Use mysqli
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($category = mysqli_fetch_assoc($result)) {
            $insert = "INSERT INTO categorias (usuario_id, nombre, tipo, color, icono, es_predefinida) VALUES (?, ?, ?, ?, ?, 0)";
            $stmt = mysqli_prepare($link, $insert);
            mysqli_stmt_bind_param($stmt, "issss", $user_id, $category['nombre'], $category['tipo'], $category['color'], $category['icono']);
            mysqli_stmt_execute($stmt);
        }
    }
}

/**
 * Requerir autenticación - redirigir si no está logueado
 */
function requireAuth() {
    // Debug helper: if environment variable DEBUG_SKIP_AUTH=1 is set, skip redirect (useful for CLI/debugging)
    if (getenv('DEBUG_SKIP_AUTH') === '1') {
        return;
    }

    if (!isLoggedIn()) {
        header("location: auth-signin-basic.php");
        exit;
    }
}

/**
 * Validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar contraseña (mínimo 6 caracteres)
 */
function validatePassword($password) {
    return strlen($password) >= 6;
}

/**
 * Sanitizar entrada
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
?>
