<?php
/**
 * Página de logout para el sistema de gastos personales
 */

// Incluir funciones de autenticación
require_once "includes/auth_functions.php";

// Cerrar sesión
logoutUser();

// Redirigir al login
header("location: auth-signin-basic.php");
exit;
?>