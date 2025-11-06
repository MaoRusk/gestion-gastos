<?php
// Update current user profile
session_start();
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';

if (!isLoggedIn()) {
    header('Location: auth-signin-basic.php');
    exit;
}

$userId = getCurrentUserId();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pages-profile-settings.php');
    exit;
}

function val($key) { return isset($_POST[$key]) ? trim((string)$_POST[$key]) : null; }

$nombre = val('nombre');
$telefono = val('telefono');
$fecha_nacimiento = val('fecha_nacimiento');
$genero = val('genero');
$direccion = val('direccion');
$ciudad = val('ciudad');
$estado = val('estado');
$codigo_postal = val('codigo_postal');
$pais = val('pais');

if ($nombre === null || $nombre === '') {
    $_SESSION['profile_msg'] = ['type' => 'error', 'text' => 'El nombre es obligatorio.'];
    header('Location: pages-profile-settings.php');
    exit;
}

// Build update dynamically
$fields = [
    'nombre' => $nombre,
    'telefono' => $telefono,
    'fecha_nacimiento' => $fecha_nacimiento,
    'genero' => $genero,
    'direccion' => $direccion,
    'ciudad' => $ciudad,
    'estado' => $estado,
    'codigo_postal' => $codigo_postal,
    'pais' => $pais,
];

$setParts = [];
$values = [];
foreach ($fields as $col => $val) {
    if ($val !== null && $val !== '') {
        $setParts[] = "$col = ?";
        $values[] = $val;
    } else {
        // allow clearing optional fields
        if (in_array($col, ['direccion','ciudad','estado','codigo_postal','pais','telefono','genero','fecha_nacimiento'], true)) {
            $setParts[] = "$col = NULL";
        }
    }
}

if (empty($setParts)) {
    $_SESSION['profile_msg'] = ['type' => 'info', 'text' => 'No hay cambios para guardar.'];
    header('Location: pages-profile-settings.php');
    exit;
}

$setSql = implode(', ', $setParts) . ', fecha_actualizacion = CURRENT_TIMESTAMP';

// Using PDO through compatibility layer
$sql = "UPDATE usuarios SET $setSql WHERE id = ?";
$values[] = $userId;

try {
    if (isset($link->pdo)) {
        $stmt = $link->pdo->prepare($sql);
        $stmt->execute($values);
    } else {
        // Fallback mysqli
        $stmt = mysqli_prepare($link, $sql);
        // Build types string
        $types = str_repeat('s', count($values)-1) . 'i';
        mysqli_stmt_bind_param($stmt, $types, ...$values);
        mysqli_stmt_execute($stmt);
    }
    $_SESSION['profile_msg'] = ['type' => 'success', 'text' => 'Perfil actualizado correctamente.'];
} catch (Exception $e) {
    $_SESSION['profile_msg'] = ['type' => 'error', 'text' => 'Error al actualizar: ' . $e->getMessage()];
}

header('Location: pages-profile-settings.php');
exit;
?>


