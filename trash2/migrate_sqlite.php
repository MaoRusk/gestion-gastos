<?php
/**
 * SQLite Database Migration Script
 * Creates all necessary tables for the expense management system
 */

require_once 'layouts/config.php';

echo "Creating SQLite database and tables...\n";

try {
    // Create tables
    $sql = "
    -- Users table
    CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        telefono VARCHAR(20),
        fecha_nacimiento DATE,
        genero VARCHAR(20) DEFAULT 'otro',
        direccion TEXT,
        ciudad VARCHAR(50),
        estado VARCHAR(50),
        codigo_postal VARCHAR(10),
        pais VARCHAR(50) DEFAULT 'México',
        activo BOOLEAN DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Bank accounts table
    CREATE TABLE IF NOT EXISTS cuentas_bancarias (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        tipo VARCHAR(20) NOT NULL,
        banco VARCHAR(100) NOT NULL,
        numero_cuenta VARCHAR(50) UNIQUE NOT NULL,
        balance_actual DECIMAL(15,2) DEFAULT 0.00,
        balance_disponible DECIMAL(15,2) DEFAULT 0.00,
        limite_credito DECIMAL(15,2) DEFAULT 0.00,
        fecha_apertura DATE,
        activa BOOLEAN DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    );

    -- Categories table
    CREATE TABLE IF NOT EXISTS categorias (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER,
        nombre VARCHAR(100) NOT NULL,
        tipo VARCHAR(20) NOT NULL,
        color VARCHAR(7) DEFAULT '#007bff',
        icono VARCHAR(50) DEFAULT 'ri-folder-line',
        descripcion TEXT,
        es_predefinida BOOLEAN DEFAULT 0,
        activa BOOLEAN DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    );

    -- Transactions table
    CREATE TABLE IF NOT EXISTS transacciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL,
        cuenta_id INTEGER NOT NULL,
        categoria_id INTEGER NOT NULL,
        descripcion VARCHAR(255) NOT NULL,
        monto DECIMAL(15,2) NOT NULL,
        tipo VARCHAR(20) NOT NULL,
        fecha DATE NOT NULL,
        notas TEXT,
        recurrente BOOLEAN DEFAULT 0,
        frecuencia VARCHAR(20),
        fecha_fin_recurrencia DATE,
        activa BOOLEAN DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (cuenta_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
    );

    -- Transfers table
    CREATE TABLE IF NOT EXISTS transferencias (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL,
        cuenta_origen_id INTEGER NOT NULL,
        cuenta_destino_id INTEGER NOT NULL,
        monto DECIMAL(15,2) NOT NULL,
        descripcion VARCHAR(255),
        fecha DATE NOT NULL,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (cuenta_origen_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE,
        FOREIGN KEY (cuenta_destino_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE
    );

    -- Budgets table
    CREATE TABLE IF NOT EXISTS presupuestos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        monto_limite DECIMAL(15,2) NOT NULL,
        categoria_id INTEGER NOT NULL,
        fecha_inicio DATE NOT NULL,
        fecha_fin DATE NOT NULL,
        descripcion TEXT,
        activo BOOLEAN DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
    );

    -- Savings goals table
    CREATE TABLE IF NOT EXISTS metas_ahorro (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario_id INTEGER NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        monto_objetivo DECIMAL(15,2) NOT NULL,
        monto_actual DECIMAL(15,2) DEFAULT 0.00,
        fecha_inicio DATE NOT NULL,
        fecha_objetivo DATE NOT NULL,
        descripcion TEXT,
        activa BOOLEAN DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    );

    -- System settings table
    CREATE TABLE IF NOT EXISTS configuraciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        clave VARCHAR(100) UNIQUE NOT NULL,
        valor TEXT,
        descripcion TEXT,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ";

    // Execute the SQL
    $link->pdo->exec($sql);
    echo "✅ Tables created successfully!\n";

    // Insert default categories
    $default_categories = [
        // Income categories
        ['Salario', 'ingreso', '#28a745', 'ri-money-dollar-circle-line'],
        ['Freelance', 'ingreso', '#28a745', 'ri-briefcase-line'],
        ['Inversiones', 'ingreso', '#28a745', 'ri-line-chart-line'],
        ['Regalos', 'ingreso', '#28a745', 'ri-gift-line'],
        ['Otros Ingresos', 'ingreso', '#28a745', 'ri-add-circle-line'],
        
        // Expense categories
        ['Alimentación', 'gasto', '#dc3545', 'ri-restaurant-line'],
        ['Transporte', 'gasto', '#dc3545', 'ri-car-line'],
        ['Vivienda', 'gasto', '#dc3545', 'ri-home-line'],
        ['Entretenimiento', 'gasto', '#dc3545', 'ri-movie-line'],
        ['Salud', 'gasto', '#dc3545', 'ri-heart-pulse-line'],
        ['Educación', 'gasto', '#dc3545', 'ri-book-line'],
        ['Ropa', 'gasto', '#dc3545', 'ri-clothes-line'],
        ['Servicios', 'gasto', '#dc3545', 'ri-tools-line'],
        ['Otros Gastos', 'gasto', '#dc3545', 'ri-shopping-cart-line']
    ];

    $stmt = $link->pdo->prepare("INSERT INTO categorias (nombre, tipo, color, icono, es_predefinida) VALUES (?, ?, ?, ?, 1)");
    
    foreach ($default_categories as $category) {
        $stmt->execute($category);
    }
    echo "✅ Default categories inserted!\n";

    // Insert default admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $link->pdo->prepare("INSERT OR IGNORE INTO usuarios (nombre, email, password_hash, activo) VALUES (?, ?, ?, 1)");
    $stmt->execute(['Administrador', 'admin@fime.com', $admin_password]);
    echo "✅ Default admin user created!\n";

    // Insert sample data
    $sample_data = "
    INSERT OR IGNORE INTO cuentas_bancarias (usuario_id, nombre, tipo, banco, numero_cuenta, balance_actual) VALUES 
    (1, 'Cuenta Principal', 'corriente', 'BBVA', '1234567890', 5000.00),
    (1, 'Cuenta de Ahorros', 'ahorros', 'Santander', '0987654321', 10000.00),
    (1, 'Tarjeta de Crédito', 'credito', 'HSBC', '555544443333', -2000.00);
    ";
    
    $link->pdo->exec($sample_data);
    echo "✅ Sample data inserted!\n";

    echo "\n🎉 Database setup completed successfully!\n";
    echo "You can now access the system at: http://localhost:8000\n";
    echo "Login with: admin@fime.com / admin123\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
