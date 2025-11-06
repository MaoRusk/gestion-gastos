<?php
// Script simple de prueba de conexión a la base de datos usando layouts/config.php
// Ejecuta: php test_db_connection.php

require_once __DIR__ . '/layouts/config.php';

// `config.php` debe dejar la conexión en $link
if (!isset($link)) {
    echo "No se encontró la variable \$link. Revisa layouts/config.php\n";
    exit(1);
}

try {
    if (isset($link->type) && $link->type === 'sqlite' && isset($link->pdo)) {
        $stmt = $link->pdo->query('SELECT sqlite_version() AS version');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "SQLite OK - version: " . ($row['version'] ?? 'desconocida') . "\n";
    } elseif (isset($link->type) && $link->type === 'postgresql' && isset($link->pdo)) {
        $stmt = $link->pdo->query('SELECT version()');
        $row = $stmt->fetch(PDO::FETCH_NUM);
        echo "PostgreSQL OK - version: " . ($row[0] ?? 'desconocida') . "\n";
    } elseif (isset($link->type) && $link->type === 'mysql' && isset($link->pdo)) {
        $stmt = $link->pdo->query('SELECT VERSION()');
        $row = $stmt->fetch(PDO::FETCH_NUM);
        echo "MySQL (PDO) OK - version: " . ($row[0] ?? 'desconocida') . "\n";
    } elseif (extension_loaded('mysqli') && is_resource($link) || (is_object($link) && ($link instanceof mysqli))) {
        // mysqli connection
        if (is_object($link) && ($link instanceof mysqli)) {
            $mysqli = $link;
        } else {
            $mysqli = $link; // in case config stored the mysqli connection directly
        }
        $res = $mysqli->query('SELECT VERSION() AS version');
        if ($res) {
            $row = $res->fetch_assoc();
            echo "MySQL (mysqli) OK - version: " . ($row['version'] ?? 'desconocida') . "\n";
        } else {
            echo "MySQL (mysqli) - error al ejecutar consulta: " . $mysqli->error . "\n";
        }
    } else {
        echo "Tipo de conexión desconocido o no soportado por el script.\n";
        // Mostrar info útil
        if (isset($link->type)) echo "Tipo detectado: " . $link->type . "\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "Error al probar la conexión: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
