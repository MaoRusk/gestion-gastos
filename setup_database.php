<?php
/**
 * Script para configurar la base de datos del sistema de gastos personales
 * Ejecutar desde el navegador: http://localhost/tu-proyecto/setup_database.php
 */

// Incluir configuración existente
require_once "layouts/config.php";

// Verificar si la base de datos existe
$check_db = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'fime_gastos'";
$result = mysqli_query($link, $check_db);

if (mysqli_num_rows($result) == 0) {
    // Crear la base de datos
    $create_db = "CREATE DATABASE fime_gastos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if (mysqli_query($link, $create_db)) {
        echo "✅ Base de datos 'fime_gastos' creada exitosamente.<br>";
    } else {
        echo "❌ Error creando la base de datos: " . mysqli_error($link) . "<br>";
        exit;
    }
} else {
    echo "ℹ️ La base de datos 'fime_gastos' ya existe.<br>";
}

// Reconectar a la nueva base de datos
mysqli_close($link);
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, 'fime_gastos');

if ($link === false) {
    die("ERROR: Could not connect to fime_gastos database. " . mysqli_connect_error());
}

// Leer y ejecutar el esquema SQL
$sql = file_get_contents('database_schema.sql');
$statements = explode(';', $sql);

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
        if (mysqli_query($link, $statement)) {
            $success_count++;
            echo "✅ " . substr($statement, 0, 50) . "...<br>";
        } else {
            $error_count++;
            echo "⚠️ Error: " . mysqli_error($link) . "<br>";
        }
    }
}

echo "<br><strong>Resumen:</strong><br>";
echo "✅ Comandos ejecutados exitosamente: $success_count<br>";
echo "⚠️ Errores: $error_count<br>";

if ($error_count == 0) {
    echo "<br>🎉 ¡Base de datos configurada completamente!<br>";
    echo "<a href='dashboard-gastos.php'>Ir al Dashboard</a>";
} else {
    echo "<br>⚠️ La configuración se completó con algunos errores. Revisa los mensajes arriba.";
}

mysqli_close($link);
?>
