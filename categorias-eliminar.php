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
    header('Location: categorias-lista.php');
    exit;
}

// Get and validate category ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    $_SESSION['error_message'] = 'ID de categoría inválido';
    header('Location: categorias-lista.php');
    exit;
}

// Verify ownership and check if it's predefinida
$ownerId = null;
$es_predefinida = false;
$categoria_nombre = '';

if (isset($link->pdo) && $link->pdo instanceof PDO) {
    // Using PDO
    $stmt = $link->pdo->prepare('SELECT usuario_id, es_predefinida, nombre FROM categorias WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $ownerId = $row['usuario_id'];
        $es_predefinida = (bool)$row['es_predefinida'];
        $categoria_nombre = $row['nombre'];
    }
} else {
    // Using mysqli
    $stmt = mysqli_prepare($link, 'SELECT usuario_id, es_predefinida, nombre FROM categorias WHERE id = ? LIMIT 1');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        if ($row) {
            $ownerId = $row['usuario_id'];
            $es_predefinida = (bool)$row['es_predefinida'];
            $categoria_nombre = $row['nombre'];
        }
        mysqli_stmt_close($stmt);
    }
}

// Check if category exists
if (!$ownerId && !$es_predefinida) {
    $_SESSION['error_message'] = 'La categoría no existe';
    header('Location: categorias-lista.php');
    exit;
}

// Check if it's a predefined category
if ($es_predefinida) {
    $_SESSION['error_message'] = 'No puedes eliminar una categoría predefinida';
    header('Location: categorias-lista.php');
    exit;
}

// Check ownership (only for user-created categories)
if ($ownerId != getCurrentUserId()) {
    $_SESSION['error_message'] = 'No tienes permiso para eliminar esta categoría';
    header('Location: categorias-lista.php');
    exit;
}

// Check if there are transactions associated with this category (informational)
$has_transactions = false;
if (isset($link->pdo) && $link->pdo instanceof PDO) {
    $stmt = $link->pdo->prepare('SELECT COUNT(*) FROM transacciones WHERE categoria_id = ?');
    $stmt->execute([$id]);
    $has_transactions = $stmt->fetchColumn() > 0;
} else {
    $stmt = mysqli_prepare($link, 'SELECT COUNT(*) FROM transacciones WHERE categoria_id = ?');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        $has_transactions = $row && $row['COUNT(*)'] > 0;
        mysqli_stmt_close($stmt);
    }
}

// Soft-delete: set activa = false (DB-aware)
// This preserves the category for historical transactions but hides it from the list
$success = false;
$error_msg = '';

if (isset($link->pdo) && $link->pdo instanceof PDO) {
    // Using PDO
    try {
        if (defined('DB_TYPE') && DB_TYPE === 'postgresql') {
            $stmt = $link->pdo->prepare('UPDATE categorias SET activa = FALSE WHERE id = ?');
        } else {
            $stmt = $link->pdo->prepare('UPDATE categorias SET activa = 0 WHERE id = ?');
        }
        $success = $stmt->execute([$id]);
        if (!$success) {
            $error_info = $stmt->errorInfo();
            $error_msg = isset($error_info[2]) ? $error_info[2] : 'Error desconocido al eliminar la categoría';
        }
    } catch (Exception $e) {
        $error_msg = 'Error al eliminar la categoría: ' . $e->getMessage();
    }
} else {
    // Using mysqli
    if (defined('DB_TYPE') && DB_TYPE === 'postgresql') {
        $stmt = mysqli_prepare($link, 'UPDATE categorias SET activa = FALSE WHERE id = ?');
    } else {
        $stmt = mysqli_prepare($link, 'UPDATE categorias SET activa = 0 WHERE id = ?');
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
    $message = 'Categoría "' . htmlspecialchars($categoria_nombre) . '" eliminada exitosamente';
    if ($has_transactions) {
        $message .= '. La categoría se mantendrá oculta pero las transacciones asociadas se conservarán.';
    }
    $_SESSION['success_message'] = $message;
} else {
    $_SESSION['error_message'] = $error_msg ?: 'Error al eliminar la categoría';
}

// Redirect back to categories list
header('Location: categorias-lista.php');
exit;

