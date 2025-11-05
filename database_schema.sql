CREATE TABLE usuarios (
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
CREATE TABLE sqlite_sequence(name,seq);
CREATE TABLE cuentas_bancarias (
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
CREATE TABLE categorias (
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
CREATE TABLE transacciones (
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
CREATE TABLE transferencias (
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
CREATE TABLE presupuestos (
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
CREATE TABLE metas_ahorro (
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
CREATE TABLE configuraciones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        clave VARCHAR(100) UNIQUE NOT NULL,
        valor TEXT,
        descripcion TEXT,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
    );
