<?php
// CLI test for prefill of cuentas-agregar.php
session_start();
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';

// Emulate logged user (adjust if your test user is different)
$_SESSION['user_id'] = 1;
$_SESSION['user_email'] = 'admin@fime.com';
$_SESSION['user_name'] = 'Administrador';

$uid = getCurrentUserId();
if (!$uid) $uid = 1;

// Find one account for the user
$row = false;
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare('SELECT id, nombre, tipo, banco FROM cuentas_bancarias WHERE usuario_id = ? LIMIT 1');
    $stmt->execute([$uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, 'SELECT id, nombre, tipo, banco FROM cuentas_bancarias WHERE usuario_id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
}

if (!$row) {
    echo "NO_ACCOUNT_FOR_USER\n";
    exit(0);
}

// Simulate GET params
$_GET['id'] = $row['id'];
$_GET['mode'] = 'view';

// Capture output of the page
ob_start();
include 'cuentas-agregar.php';
$html = ob_get_clean();

// Check if the account name appears in an input value attribute
$needle = 'value="' . htmlspecialchars($row['nombre']);
$found = (strpos($html, $needle) !== false) ? 'FOUND' : 'MISSING';

echo "ACCOUNT_ID: {$row['id']}\n";
echo "NAME_CHECK: {$found}\n";

if ($found === 'FOUND') {
    $pos = strpos($html, $needle);
    $start = max(0, $pos - 60);
    $snippet = substr($html, $start, 160);
    echo "SNIPPET: ..." . $snippet . "...\n";
} else {
    // provide small diagnostic: show portion around input name attribute index
    $pos2 = strpos($html, htmlspecialchars($row['nombre']));
    if ($pos2 !== false) {
        $start = max(0, $pos2 - 60);
        $snippet = substr($html, $start, 160);
        echo "SNIPPET_NO_VALUE_ATTR: ..." . $snippet . "...\n";
    }
}

// Minimal success exit code
exit(0);
