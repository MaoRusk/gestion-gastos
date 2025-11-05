<?php
// Initialize the session
session_start();

// Include config file and auth functions
require_once "layouts/config.php";
require_once "includes/auth_functions.php";

// Check if the user is logged in, if not then redirect to login page
if (isLoggedIn()) {
    header("Location: dashboard-gastos.php");
    exit;
} else {
    header("Location: auth-signin-basic.php");
    exit;
}
?>