<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect him to login page
requireAuth();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: presupuestos-lista.php');
    exit;
}

// Get and validate budget ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    $_SESSION['error_message'] = 'ID de presupuesto inválido';
    header('Location: presupuestos-lista.php');
    exit;
}

// Verify ownership
$ownerId = null;
$presupuesto_nombre = '';

if (isset($link->pdo) && $link->pdo instanceof PDO) {
    // Using PDO
    $stmt = $link->pdo->prepare('SELECT usuario_id, nombre FROM presupuestos WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $ownerId = $row['usuario_id'];
        $presupuesto_nombre = $row['nombre'];
    }
} else {
    // Using mysqli
    $stmt = mysqli_prepare($link, 'SELECT usuario_id, nombre FROM presupuestos WHERE id = ? LIMIT 1');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        if ($row) {
            $ownerId = $row['usuario_id'];
            $presupuesto_nombre = $row['nombre'];
        }
        mysqli_stmt_close($stmt);
    }
}

// Check if budget exists
if (!$ownerId) {
    $_SESSION['error_message'] = 'El presupuesto no existe';
    header('Location: presupuestos-lista.php');
    exit;
}

// Check ownership
if ($ownerId != getCurrentUserId()) {
    $_SESSION['error_message'] = 'No tienes permiso para eliminar este presupuesto';
    header('Location: presupuestos-lista.php');
    exit;
}

// Soft-delete: set activo = false (DB-aware)
$success = false;
$error_msg = '';

if (isset($link->pdo) && $link->pdo instanceof PDO) {
    // Using PDO
    try {
        if (defined('DB_TYPE') && DB_TYPE === 'postgresql') {
            $stmt = $link->pdo->prepare('UPDATE presupuestos SET activo = FALSE WHERE id = ?');
        } else {
            $stmt = $link->pdo->prepare('UPDATE presupuestos SET activo = 0 WHERE id = ?');
        }
        $success = $stmt->execute([$id]);
        if (!$success) {
            $error_info = $stmt->errorInfo();
            $error_msg = isset($error_info[2]) ? $error_info[2] : 'Error desconocido al eliminar el presupuesto';
        }
    } catch (Exception $e) {
        $error_msg = 'Error al eliminar el presupuesto: ' . $e->getMessage();
    }
} else {
    // Using mysqli
    if (defined('DB_TYPE') && DB_TYPE === 'postgresql') {
        $stmt = mysqli_prepare($link, 'UPDATE presupuestos SET activo = FALSE WHERE id = ?');
    } else {
        $stmt = mysqli_prepare($link, 'UPDATE presupuestos SET activo = 0 WHERE id = ?');
    }
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $success = mysqli_stmt_execute($stmt);
        if (!$success) {
            $error_msg = mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_msg = 'Error al preparar la consulta de eliminación';
    }
}

// Set success or error message
if ($success) {
    $_SESSION['success_message'] = 'Presupuesto "' . htmlspecialchars($presupuesto_nombre) . '" eliminado exitosamente';
} else {
    $_SESSION['error_message'] = $error_msg ?: 'Error al eliminar el presupuesto';
}

// Redirect back to budgets list
header('Location: presupuestos-lista.php');
exit;

