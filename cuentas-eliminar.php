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
    header('Location: cuentas-lista.php');
    exit;
}

// Get and validate account ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    $_SESSION['error_message'] = 'ID de cuenta inválido';
    header('Location: cuentas-lista.php');
    exit;
}

// Verify ownership before deletion
$ownerId = null;
$cuenta_nombre = '';

if (isset($link->pdo) && $link->pdo instanceof PDO) {
    // Using PDO
    $stmt = $link->pdo->prepare('SELECT usuario_id, nombre FROM cuentas_bancarias WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $ownerId = $row['usuario_id'];
        $cuenta_nombre = $row['nombre'];
    }
} else {
    // Using mysqli
    $stmt = mysqli_prepare($link, 'SELECT usuario_id, nombre FROM cuentas_bancarias WHERE id = ? LIMIT 1');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        if ($row) {
            $ownerId = $row['usuario_id'];
            $cuenta_nombre = $row['nombre'];
        }
        mysqli_stmt_close($stmt);
    }
}

// Check if account exists
if (!$ownerId) {
    $_SESSION['error_message'] = 'La cuenta no existe';
    header('Location: cuentas-lista.php');
    exit;
}

// Check ownership
if ($ownerId != getCurrentUserId()) {
    $_SESSION['error_message'] = 'No tienes permiso para eliminar esta cuenta';
    header('Location: cuentas-lista.php');
    exit;
}

// Check if there are transactions associated with this account (informational)
$has_transactions = false;
if (isset($link->pdo) && $link->pdo instanceof PDO) {
    $stmt = $link->pdo->prepare('SELECT COUNT(*) FROM transacciones WHERE cuenta_id = ?');
    $stmt->execute([$id]);
    $has_transactions = $stmt->fetchColumn() > 0;
} else {
    $stmt = mysqli_prepare($link, 'SELECT COUNT(*) FROM transacciones WHERE cuenta_id = ?');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        $has_transactions = $row && $row['COUNT(*)'] > 0;
        mysqli_stmt_close($stmt);
    }
}

// Delete the account
// Note: Due to ON DELETE CASCADE, associated transactions and transfers will be automatically deleted
$success = false;
$error_msg = '';

if (isset($link->pdo) && $link->pdo instanceof PDO) {
    // Using PDO
    try {
        $stmt = $link->pdo->prepare('DELETE FROM cuentas_bancarias WHERE id = ?');
        $success = $stmt->execute([$id]);
        if (!$success) {
            $error_info = $stmt->errorInfo();
            $error_msg = isset($error_info[2]) ? $error_info[2] : 'Error desconocido al eliminar la cuenta';
        }
    } catch (Exception $e) {
        $error_msg = 'Error al eliminar la cuenta: ' . $e->getMessage();
    }
} else {
    // Using mysqli
    $stmt = mysqli_prepare($link, 'DELETE FROM cuentas_bancarias WHERE id = ?');
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
    $message = 'Cuenta "' . htmlspecialchars($cuenta_nombre) . '" eliminada exitosamente';
    if ($has_transactions) {
        $message .= '. Las transacciones asociadas también fueron eliminadas.';
    }
    $_SESSION['success_message'] = $message;
} else {
    $_SESSION['error_message'] = $error_msg ?: 'Error al eliminar la cuenta';
}

// Redirect back to accounts list
header('Location: cuentas-lista.php');
exit;

