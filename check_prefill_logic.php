<?php
// Check the prefill logic used by cuentas-agregar.php without including full HTML page
require_once 'layouts/config.php';
require_once 'includes/auth_functions.php';

// Emulate logged user
session_start();
$_SESSION['user_id'] = 1;

$uid = getCurrentUserId();
if (!$uid) $uid = 1;

// Fetch one account
$row = false;
if (isset($link->pdo)) {
    $stmt = $link->pdo->prepare('SELECT * FROM cuentas_bancarias WHERE usuario_id = ? LIMIT 1');
    $stmt->execute([$uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = mysqli_prepare($link, 'SELECT * FROM cuentas_bancarias WHERE usuario_id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
}

if (!$row) {
    echo "NO_ACCOUNT\n";
    exit(0);
}

// Simulate the prefill mapping used in cuentas-agregar.php
$nombre = $row['nombre'];
$raw_tipo = strtolower(trim($row['tipo'] ?? ''));
$tipo_map = [
    'corriente' => 'cuenta_corriente',
    'cuenta_corriente' => 'cuenta_corriente',
    'ahorros' => 'cuenta_ahorros',
    'cuenta_ahorros' => 'cuenta_ahorros',
    'tarjeta_credito' => 'tarjeta_credito',
    'credito' => 'tarjeta_credito',
    'tarjeta' => 'tarjeta_credito',
    'efectivo' => 'efectivo',
    'inversion' => 'inversion',
    'inversión' => 'inversion'
];
$tipo = isset($tipo_map[$raw_tipo]) ? $tipo_map[$raw_tipo] : ($row['tipo'] ?? '');
$banco = $row['banco'];
$numero_cuenta = $row['numero_cuenta'];
$balance_inicial = $row['balance_inicial'];
$limite_credito = $row['limite_credito'];
$color = !empty($row['color']) ? $row['color'] : '#007bff';

$out = [
    'id' => $row['id'],
    'nombre' => $nombre,
    'raw_tipo' => $row['tipo'],
    'mapped_tipo' => $tipo,
    'banco' => $banco,
    'numero_cuenta' => $numero_cuenta,
    'balance_inicial' => $balance_inicial,
    'limite_credito' => $limite_credito,
    'color' => $color
];

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
