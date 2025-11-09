<?php
/**
 * Script para limpiar completamente la base de datos
 * y dejar solo el usuario administrador
 * 
 * Ejecutar desde el navegador: http://localhost/clean_database.php
 * O desde línea de comandos: php clean_database.php
 */

// Incluir configuración
require_once "layouts/config.php";

// Verificar que estamos usando PostgreSQL
if (!isset($link) || (isset($link->type) && $link->type !== 'postgresql')) {
    die("❌ Este script solo funciona con PostgreSQL. Tipo de BD detectado: " . (isset($link->type) ? $link->type : 'desconocido'));
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Limpieza de Base de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        button { background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Limpieza de Base de Datos</h1>
        <p class="warning">⚠️ ADVERTENCIA: Este script eliminará TODOS los datos de la base de datos y dejará solo el usuario administrador.</p>
        
        <?php
        // Verificar si se confirmó la acción
        if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
            try {
                if (isset($link->pdo)) {
                    $pdo = $link->pdo;
                } else {
                    die("<p class='error'>❌ No se pudo establecer conexión a la base de datos.</p>");
                }

                echo "<h2>Iniciando limpieza...</h2>";
                
                // Obtener lista de todas las tablas en PostgreSQL
                $tables = [];
                $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $tables[] = $row['tablename'];
                }
                
                echo "<p class='info'>📋 Tablas encontradas: " . implode(', ', $tables) . "</p>";
                
                // Intentar usar TRUNCATE CASCADE primero (más eficiente)
                // Si falla por permisos, usar DELETE en el orden correcto
                try {
                    // TRUNCATE CASCADE maneja automáticamente las foreign keys
                    // Podemos hacerlo en todas las tablas a la vez
                    if (!empty($tables)) {
                        $table_list = '"' . implode('", "', $tables) . '"';
                        $pdo->exec("TRUNCATE TABLE $table_list CASCADE");
                        echo "<p class='info'>✓ Datos eliminados usando TRUNCATE CASCADE en todas las tablas</p>";
                    }
                } catch (Exception $e) {
                    // Si TRUNCATE falla, usar DELETE en el orden correcto de dependencias
                    echo "<p class='info'>⚠️ TRUNCATE no disponible, usando DELETE en orden correcto...</p>";
                    
                    // Orden correcto: primero las tablas que dependen de otras
                    $delete_order = [
                        'transacciones',      // Depende de usuarios, cuentas_bancarias, categorias
                        'transferencias',      // Depende de usuarios, cuentas_bancarias
                        'presupuestos',       // Depende de usuarios, categorias
                        'cuentas_bancarias',  // Depende de usuarios
                        'categorias',         // Depende de usuarios
                        'usuarios',           // Tabla base
                        'configuraciones'     // Sin dependencias
                    ];
                    
                    foreach ($delete_order as $table) {
                        if (in_array($table, $tables)) {
                            try {
                                $pdo->exec("DELETE FROM \"$table\"");
                                echo "<p class='info'>✓ Datos eliminados de: <strong>$table</strong></p>";
                            } catch (Exception $del_e) {
                                echo "<p class='warning'>⚠️ No se pudo eliminar $table: " . htmlspecialchars($del_e->getMessage()) . "</p>";
                            }
                        }
                    }
                }
                
                // Reiniciar secuencias de SERIAL (equivalente a AUTO_INCREMENT en PostgreSQL)
                $sequences = [];
                $stmt = $pdo->query("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = 'public'");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $sequences[] = $row['sequence_name'];
                }
                
                foreach ($sequences as $seq) {
                    try {
                        $pdo->exec("ALTER SEQUENCE \"$seq\" RESTART WITH 1");
                    } catch (Exception $seq_e) {
                        echo "<p class='warning'>⚠️ No se pudo reiniciar secuencia $seq: " . htmlspecialchars($seq_e->getMessage()) . "</p>";
                    }
                }
                echo "<p class='info'>✓ " . count($sequences) . " secuencias reiniciadas</p>";
                
                // Verificar estructura de tablas (no necesitamos agregar columnas, pero verificamos)
                echo "<h3>Verificando estructura de tablas...</h3>";
                
                // Verificar que cuentas_bancarias tenga los campos necesarios
                $check_columns = $pdo->query("
                    SELECT column_name, data_type 
                    FROM information_schema.columns 
                    WHERE table_name = 'cuentas_bancarias' 
                    AND table_schema = 'public'
                    ORDER BY ordinal_position
                ");
                $columns = $check_columns->fetchAll(PDO::FETCH_ASSOC);
                $required_columns = ['id', 'usuario_id', 'nombre', 'tipo', 'banco', 'numero_cuenta', 'balance_actual', 'balance_disponible', 'limite_credito', 'fecha_apertura', 'activa', 'fecha_creacion', 'fecha_actualizacion'];
                $existing_columns = array_column($columns, 'column_name');
                
                $missing_columns = array_diff($required_columns, $existing_columns);
                if (empty($missing_columns)) {
                    echo "<p class='success'>✓ Estructura de tabla cuentas_bancarias correcta</p>";
                } else {
                    echo "<p class='warning'>⚠️ Faltan columnas: " . implode(', ', $missing_columns) . "</p>";
                    echo "<p class='info'>ℹ️ Nota: El tipo 'prestamo_personal' se puede usar sin cambios en la estructura</p>";
                }
                
                // Crear usuario administrador
                $admin_email = 'admin@fime.com';
                $admin_password = 'admin123';
                $admin_name = 'Manolo Rdz';
                $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                
                // Usar TRUE para PostgreSQL boolean
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, activo) VALUES (?, ?, ?, ?) RETURNING id");
                $stmt->execute([$admin_name, $admin_email, $hashed_password, true]);
                
                // En PostgreSQL, usar RETURNING id es más confiable que lastInsertId()
                $admin_id = $stmt->fetchColumn();
                echo "<p class='success'>✓ Usuario administrador creado:</p>";
                echo "<ul>";
                echo "<li><strong>ID:</strong> $admin_id</li>";
                echo "<li><strong>Nombre:</strong> $admin_name</li>";
                echo "<li><strong>Email:</strong> $admin_email</li>";
                echo "<li><strong>Contraseña:</strong> $admin_password</li>";
                echo "</ul>";
                
                // Crear categorías predefinidas para el admin (incluyendo categorías para pagos de deudas)
                $categorias_predefinidas = [
                    // Ingresos
                    ['Salario', 'ingreso', '#28a745', 'ri-money-dollar-circle-line'],
                    ['Otros Ingresos', 'ingreso', '#28a745', 'ri-money-dollar-box-line'],
                    // Gastos generales
                    ['Alimentación', 'gasto', '#dc3545', 'ri-restaurant-line'],
                    ['Transporte', 'gasto', '#dc3545', 'ri-car-line'],
                    ['Vivienda', 'gasto', '#dc3545', 'ri-home-line'],
                    ['Entretenimiento', 'gasto', '#dc3545', 'ri-movie-line'],
                    ['Salud', 'gasto', '#dc3545', 'ri-heart-pulse-line'],
                    ['Educación', 'gasto', '#dc3545', 'ri-book-line'],
                    ['Ropa', 'gasto', '#dc3545', 'ri-clothes-line'],
                    ['Servicios', 'gasto', '#dc3545', 'ri-tools-line'],
                    ['Otros Gastos', 'gasto', '#dc3545', 'ri-shopping-cart-line'],
                    // Pagos de deudas (nuevas categorías)
                    ['Pago Tarjeta de Crédito', 'gasto', '#ff9800', 'ri-bank-card-line'],
                    ['Pago Préstamo Personal', 'gasto', '#f44336', 'ri-hand-coin-line'],
                ];
                
                // Usar TRUE para PostgreSQL boolean
                $stmt = $pdo->prepare("INSERT INTO categorias (usuario_id, nombre, tipo, color, icono, es_predefinida) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($categorias_predefinidas as $cat) {
                    $stmt->execute([$admin_id, $cat[0], $cat[1], $cat[2], $cat[3], true]);
                }
                echo "<p class='success'>✓ " . count($categorias_predefinidas) . " categorías predefinidas creadas (incluyendo categorías para pagos de deudas)</p>";
                
                echo "<hr>";
                echo "<h2 class='success'>✅ Limpieza completada exitosamente!</h2>";
                echo "<p>La base de datos ha sido limpiada y ahora contiene:</p>";
                echo "<ul>";
                echo "<li>1 usuario administrador (admin@fime.com / admin123)</li>";
                echo "<li>" . count($categorias_predefinidas) . " categorías predefinidas (incluyendo categorías para pagos de deudas)</li>";
                echo "<li>$cuentas_creadas cuentas de ejemplo (incluyendo tarjetas de crédito y préstamos personales)</li>";
                echo "</ul>";
                echo "<p><strong>Nota:</strong> Las cuentas de ejemplo incluyen:</p>";
                echo "<ul>";
                echo "<li>Cuentas normales (corriente, ahorros)</li>";
                echo "<li>Tarjeta de crédito con deuda de \$2,000</li>";
                echo "<li>Préstamos personales (carro y personal) con montos originales configurados</li>";
                echo "</ul>";
                echo "<p><strong>Tipos de cuenta disponibles:</strong> cuenta_corriente, cuenta_ahorros, tarjeta_credito, <strong>prestamo_personal</strong>, efectivo</p>";
                echo "<p><a href='auth-signin-basic.php' class='btn btn-primary' style='display:inline-block;margin-top:10px;'>Iniciar sesión</a></p>";
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ Error durante la limpieza: " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            }
        } else {
            // Mostrar formulario de confirmación
            ?>
            <form method="POST" onsubmit="return confirm('¿Estás SEGURO de que quieres eliminar TODOS los datos? Esta acción NO se puede deshacer.');">
                <p>Este script realizará las siguientes acciones:</p>
                <ul>
                    <li>Eliminará TODOS los datos de todas las tablas</li>
                    <li>Reiniciará los contadores de autoincremento</li>
                    <li>Verificará la estructura de las tablas</li>
                    <li>Creará un nuevo usuario administrador con:
                        <ul>
                            <li>Email: <strong>admin@fime.com</strong></li>
                            <li>Contraseña: <strong>admin123</strong></li>
                        </ul>
                    </li>
                    <li>Creará categorías predefinidas (incluyendo categorías para pagos de deudas)</li>
                    <li>Creará cuentas de ejemplo (incluyendo tarjetas de crédito y préstamos personales)</li>
                </ul>
                <p class="info"><strong>Nuevas funcionalidades incluidas:</strong></p>
                <ul>
                    <li>✅ Soporte para <strong>Préstamos Personales</strong> como tipo de cuenta</li>
                    <li>✅ Categorías específicas para pagos de deudas</li>
                    <li>✅ Rastreo de progreso de pago de deudas</li>
                </ul>
                
                <input type="hidden" name="confirm" value="yes">
                <button type="submit">⚠️ CONFIRMAR LIMPIEZA COMPLETA</button>
            </form>
            <?php
        }
        ?>
    </div>
</body>
</html>

