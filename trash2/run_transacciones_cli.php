<?php
// Small runner to include transacciones-lista.php in CLI for debugging
ini_set('display_errors', '1');
error_reporting(E_ALL);
// emulate web env
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET = [];
// start session and set a logged-in user id used by requireAuth()/getCurrentUserId()
if (session_status() == PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1;
// include the page
include __DIR__ . '/transacciones-lista.php';
