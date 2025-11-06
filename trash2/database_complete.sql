PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
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
INSERT INTO usuarios VALUES(1,'Administrador','admin@fime.com','$2y$12$y00rlL.NvBSzm6jtKhdRlOiOerWSvp7vT2LQlYQQWV1/fscpL3WSW',NULL,NULL,'otro',NULL,NULL,NULL,NULL,'México',1,'2025-10-08 23:14:22','2025-10-16 14:46:28');
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
INSERT INTO cuentas_bancarias VALUES(1,1,'Cuenta Principal','corriente','BBVA','1234567890',5000,0,0,NULL,1,'2025-10-08 23:14:22','2025-10-08 23:14:22');
INSERT INTO cuentas_bancarias VALUES(2,1,'Cuenta de Ahorros','ahorros','Santander','0987654321',10000,0,0,NULL,1,'2025-10-08 23:14:22','2025-10-08 23:14:22');
INSERT INTO cuentas_bancarias VALUES(3,1,'Tarjeta de Crédito','credito','HSBC','555544443333',-2000,0,0,NULL,1,'2025-10-08 23:14:22','2025-10-08 23:14:22');
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
INSERT INTO categorias VALUES(1,NULL,'Salario','ingreso','#28a745','ri-money-dollar-circle-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(2,NULL,'Freelance','ingreso','#28a745','ri-briefcase-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(3,NULL,'Inversiones','ingreso','#28a745','ri-line-chart-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(4,NULL,'Regalos','ingreso','#28a745','ri-gift-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(5,NULL,'Otros Ingresos','ingreso','#28a745','ri-add-circle-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(6,NULL,'Alimentación','gasto','#dc3545','ri-restaurant-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(7,NULL,'Transporte','gasto','#dc3545','ri-car-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(8,NULL,'Vivienda','gasto','#dc3545','ri-home-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(9,NULL,'Entretenimiento','gasto','#dc3545','ri-movie-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(10,NULL,'Salud','gasto','#dc3545','ri-heart-pulse-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(11,NULL,'Educación','gasto','#dc3545','ri-book-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(12,NULL,'Ropa','gasto','#dc3545','ri-clothes-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(13,NULL,'Servicios','gasto','#dc3545','ri-tools-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(14,NULL,'Otros Gastos','gasto','#dc3545','ri-shopping-cart-line',NULL,1,1,'2025-10-08 23:14:22');
INSERT INTO categorias VALUES(15,NULL,'Salario','ingreso','#28a745','ri-money-dollar-circle-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(16,NULL,'Freelance','ingreso','#28a745','ri-briefcase-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(17,NULL,'Inversiones','ingreso','#28a745','ri-line-chart-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(18,NULL,'Regalos','ingreso','#28a745','ri-gift-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(19,NULL,'Otros Ingresos','ingreso','#28a745','ri-add-circle-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(20,NULL,'Alimentación','gasto','#dc3545','ri-restaurant-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(21,NULL,'Transporte','gasto','#dc3545','ri-car-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(22,NULL,'Vivienda','gasto','#dc3545','ri-home-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(23,NULL,'Entretenimiento','gasto','#dc3545','ri-movie-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(24,NULL,'Salud','gasto','#dc3545','ri-heart-pulse-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(25,NULL,'Educación','gasto','#dc3545','ri-book-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(26,NULL,'Ropa','gasto','#dc3545','ri-clothes-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(27,NULL,'Servicios','gasto','#dc3545','ri-tools-line',NULL,1,1,'2025-10-21 00:36:21');
INSERT INTO categorias VALUES(28,NULL,'Otros Gastos','gasto','#dc3545','ri-shopping-cart-line',NULL,1,1,'2025-10-21 00:36:21');
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
INSERT INTO sqlite_sequence VALUES('categorias',28);
INSERT INTO sqlite_sequence VALUES('usuarios',2);
INSERT INTO sqlite_sequence VALUES('cuentas_bancarias',6);
COMMIT;
