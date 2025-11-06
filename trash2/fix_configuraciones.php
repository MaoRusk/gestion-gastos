<?php
/**
 * Script para crear manualmente la tabla configuraciones si falta
 */

require_once "layouts/config.php";

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Crear Tabla Configuraciones</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Crear Tabla Configuraciones</h1>
        <hr>

<?php
// Verificar conexión
if (!isset($link)) {
    die("<p class='error'>❌ No se pudo establecer conexión a la base de datos.</p>");
}

// SQL para crear la tabla configuraciones
$sql = "CREATE TABLE IF NOT EXISTS configuraciones (
    id SERIAL PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

try {
    if (isset($link->pdo)) {
        // Usando PDO
        $link->pdo->exec($sql);
        echo "<p class='success'>✅ Tabla 'configuraciones' creada exitosamente.</p>";
        
        // Crear trigger para actualizar fecha_actualizacion
        $trigger_sql = "
        CREATE OR REPLACE FUNCTION update_updated_at_column()
        RETURNS TRIGGER AS \$\$
        BEGIN
            NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
            RETURN NEW;
        END;
        \$\$ language 'plpgsql';
        
        DROP TRIGGER IF EXISTS update_configuraciones_updated_at ON configuraciones;
        CREATE TRIGGER update_configuraciones_updated_at BEFORE UPDATE ON configuraciones
            FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
        ";
        
        try {
            $link->pdo->exec($trigger_sql);
            echo "<p class='success'>✅ Trigger para fecha_actualizacion creado.</p>";
        } catch (PDOException $e) {
            echo "<p class='info'>ℹ️ Trigger ya existe o no se pudo crear: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Insertar datos de configuración si no existen
        $insert_sql = "
        INSERT INTO configuraciones (clave, valor, descripcion) VALUES
        ('moneda_default', 'MXN', 'Moneda por defecto del sistema'),
        ('interes_mensual_tarjeta', '3.5', 'Interés mensual de tarjeta de crédito'),
        ('alertas_email', 'true', 'Activar alertas por email'),
        ('theme_default', 'light', 'Tema por defecto del sistema'),
        ('retenimiento_nomina', '30', 'Porcentaje de retención de nómina'),
        ('fecha_cierre_ciclo', '28', 'Día de cierre de tarjeta de crédito')
        ON CONFLICT (clave) DO UPDATE SET valor = EXCLUDED.valor;
        ";
        
        try {
            $link->pdo->exec($insert_sql);
            echo "<p class='success'>✅ Datos de configuración insertados.</p>";
        } catch (PDOException $e) {
            echo "<p class='info'>ℹ️ Datos ya existen o no se pudieron insertar: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Solo compatible con PDO.</p>";
    }
    
    echo "<hr>";
    echo "<p class='success'><strong>✅ Proceso completado.</strong></p>";
    echo "<p><a href='verify_database.php'>Verificar Base de Datos</a> | <a href='index.php'>Ir al Sistema</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

    </div>
</body>
</html>

