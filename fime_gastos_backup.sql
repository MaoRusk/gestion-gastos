-- FIME Gastos Database Export
-- Generated: 2025-10-27 22:01:38

-- Table: usuarios
CREATE TABLE usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
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
        activo TINYINT(1) DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

INSERT INTO usuarios (id, nombre, email, password_hash, telefono, fecha_nacimiento, genero, direccion, ciudad, estado, codigo_postal, pais, activo, fecha_creacion, fecha_actualizacion) VALUES ('1', 'Administrador', 'admin@fime.com', '$2y$12$y00rlL.NvBSzm6jtKhdRlOiOerWSvp7vT2LQlYQQWV1/fscpL3WSW', NULL, NULL, 'otro', NULL, NULL, NULL, NULL, 'México', '1', '2025-10-08 23:14:22', '2025-10-27 21:56:00');

-- Table: cuentas_bancarias
CREATE TABLE cuentas_bancarias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INTEGER NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        tipo VARCHAR(20) NOT NULL,
        banco VARCHAR(100) NOT NULL,
        numero_cuenta VARCHAR(50) UNIQUE NOT NULL,
        balance_actual DECIMAL(15,2) DEFAULT 0.00,
        balance_disponible DECIMAL(15,2) DEFAULT 0.00,
        limite_credito DECIMAL(15,2) DEFAULT 0.00,
        fecha_apertura DATE,
        activa TINYINT(1) DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    );

INSERT INTO cuentas_bancarias (id, usuario_id, nombre, tipo, banco, numero_cuenta, balance_actual, balance_disponible, limite_credito, fecha_apertura, activa, fecha_creacion, fecha_actualizacion) VALUES ('1', '1', 'Cuenta Principal', 'corriente', 'BBVA', '1234567890', '-670', '0', '0', NULL, '1', '2025-10-08 23:14:22', '2025-10-08 23:14:22');
INSERT INTO cuentas_bancarias (id, usuario_id, nombre, tipo, banco, numero_cuenta, balance_actual, balance_disponible, limite_credito, fecha_apertura, activa, fecha_creacion, fecha_actualizacion) VALUES ('2', '1', 'Cuenta de Ahorros', 'ahorros', 'Santander', '0987654321', '10800', '0', '0', NULL, '1', '2025-10-08 23:14:22', '2025-10-08 23:14:22');
INSERT INTO cuentas_bancarias (id, usuario_id, nombre, tipo, banco, numero_cuenta, balance_actual, balance_disponible, limite_credito, fecha_apertura, activa, fecha_creacion, fecha_actualizacion) VALUES ('3', '1', 'Tarjeta de Crédito', 'credito', 'HSBC', '555544443333', '-2000', '0', '0', NULL, '1', '2025-10-08 23:14:22', '2025-10-08 23:14:22');

-- Table: categorias
CREATE TABLE categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INTEGER,
        nombre VARCHAR(100) NOT NULL,
        tipo VARCHAR(20) NOT NULL,
        color VARCHAR(7) DEFAULT '#007bff',
        icono VARCHAR(50) DEFAULT 'ri-folder-line',
        descripcion TEXT,
        es_predefinida TINYINT(1) DEFAULT 0,
        activa TINYINT(1) DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    );

INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('1', NULL, 'Salario', 'ingreso', '#28a745', 'ri-money-dollar-circle-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('2', NULL, 'Freelance', 'ingreso', '#28a745', 'ri-briefcase-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('3', NULL, 'Inversiones', 'ingreso', '#28a745', 'ri-line-chart-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('4', NULL, 'Regalos', 'ingreso', '#28a745', 'ri-gift-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('5', NULL, 'Otros Ingresos', 'ingreso', '#28a745', 'ri-add-circle-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('6', NULL, 'Alimentación', 'gasto', '#dc3545', 'ri-restaurant-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('7', NULL, 'Transporte', 'gasto', '#dc3545', 'ri-car-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('8', NULL, 'Vivienda', 'gasto', '#dc3545', 'ri-home-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('9', NULL, 'Entretenimiento', 'gasto', '#dc3545', 'ri-movie-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('10', NULL, 'Salud', 'gasto', '#dc3545', 'ri-heart-pulse-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('11', NULL, 'Educación', 'gasto', '#dc3545', 'ri-book-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('12', NULL, 'Ropa', 'gasto', '#dc3545', 'ri-clothes-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('13', NULL, 'Servicios', 'gasto', '#dc3545', 'ri-tools-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('14', NULL, 'Otros Gastos', 'gasto', '#dc3545', 'ri-shopping-cart-line', NULL, '1', '1', '2025-10-08 23:14:22');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('15', NULL, 'Salario', 'ingreso', '#28a745', 'ri-money-dollar-circle-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('16', NULL, 'Freelance', 'ingreso', '#28a745', 'ri-briefcase-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('17', NULL, 'Inversiones', 'ingreso', '#28a745', 'ri-line-chart-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('18', NULL, 'Regalos', 'ingreso', '#28a745', 'ri-gift-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('19', NULL, 'Otros Ingresos', 'ingreso', '#28a745', 'ri-add-circle-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('20', NULL, 'Alimentación', 'gasto', '#dc3545', 'ri-restaurant-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('21', NULL, 'Transporte', 'gasto', '#dc3545', 'ri-car-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('22', NULL, 'Vivienda', 'gasto', '#dc3545', 'ri-home-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('23', NULL, 'Entretenimiento', 'gasto', '#dc3545', 'ri-movie-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('24', NULL, 'Salud', 'gasto', '#dc3545', 'ri-heart-pulse-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('25', NULL, 'Educación', 'gasto', '#dc3545', 'ri-book-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('26', NULL, 'Ropa', 'gasto', '#dc3545', 'ri-clothes-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('27', NULL, 'Servicios', 'gasto', '#dc3545', 'ri-tools-line', NULL, '1', '1', '2025-10-21 00:36:21');
INSERT INTO categorias (id, usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa, fecha_creacion) VALUES ('28', NULL, 'Otros Gastos', 'gasto', '#dc3545', 'ri-shopping-cart-line', NULL, '1', '1', '2025-10-21 00:36:21');

-- Table: transacciones
CREATE TABLE transacciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INTEGER NOT NULL,
        cuenta_id INTEGER NOT NULL,
        categoria_id INTEGER NOT NULL,
        descripcion VARCHAR(255) NOT NULL,
        monto DECIMAL(15,2) NOT NULL,
        tipo VARCHAR(20) NOT NULL,
        fecha DATE NOT NULL,
        notas TEXT,
        recurrente TINYINT(1) DEFAULT 0,
        frecuencia VARCHAR(20),
        fecha_fin_recurrencia DATE,
        activa TINYINT(1) DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (cuenta_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
    );

INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('1', '1', '1', '15', 'Salario mensual', '5000', 'ingreso', '2025-01-15', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('2', '1', '1', '15', 'Salario mensual', '5000', 'ingreso', '2025-02-15', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('3', '1', '1', '15', 'Salario mensual', '5000', 'ingreso', '2025-03-15', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('4', '1', '1', '16', 'Pago freelance', '1500', 'ingreso', '2025-01-20', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('5', '1', '2', '17', 'Rendimiento inversiones', '800', 'ingreso', '2025-02-10', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('6', '1', '1', '22', 'Renta departamento', '3500', 'gasto', '2025-01-01', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('7', '1', '1', '22', 'Renta departamento', '3500', 'gasto', '2025-02-01', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('8', '1', '1', '22', 'Renta departamento', '3500', 'gasto', '2025-03-01', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('9', '1', '1', '20', 'Supermercado', '1500', 'gasto', '2025-01-05', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('10', '1', '1', '20', 'Supermercado', '1800', 'gasto', '2025-02-05', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('11', '1', '1', '20', 'Supermercado', '1600', 'gasto', '2025-03-05', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('12', '1', '1', '21', 'Combustible', '800', 'gasto', '2025-01-10', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('13', '1', '1', '21', 'Combustible', '750', 'gasto', '2025-02-12', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('14', '1', '1', '21', 'Combustible', '820', 'gasto', '2025-03-08', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('15', '1', '1', '23', 'Cine', '250', 'gasto', '2025-01-15', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('16', '1', '1', '23', 'Netflix', '200', 'gasto', '2025-02-01', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('17', '1', '1', '23', 'Spotify', '150', 'gasto', '2025-03-01', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('18', '1', '1', '24', 'Consulta médica', '500', 'gasto', '2025-02-20', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('19', '1', '1', '24', 'Medicamentos', '300', 'gasto', '2025-02-25', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('20', '1', '1', '27', 'Luz', '350', 'gasto', '2025-01-10', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('21', '1', '1', '27', 'Agua', '250', 'gasto', '2025-01-12', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('22', '1', '1', '27', 'Internet', '400', 'gasto', '2025-02-01', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('23', '1', '1', '26', 'Ropa nueva', '1200', 'gasto', '2025-01-25', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO transacciones (id, usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia, fecha_fin_recurrencia, activa, fecha_creacion, fecha_actualizacion) VALUES ('24', '1', '1', '25', 'Libros de estudio', '800', 'gasto', '2025-02-05', NULL, '0', NULL, NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');

-- Table: transferencias
CREATE TABLE transferencias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INTEGER NOT NULL,
        cuenta_origen_id INTEGER NOT NULL,
        cuenta_destino_id INTEGER NOT NULL,
        monto DECIMAL(15,2) NOT NULL,
        descripcion VARCHAR(255),
        fecha DATE NOT NULL,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (cuenta_origen_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE,
        FOREIGN KEY (cuenta_destino_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE
    );


-- Table: presupuestos
CREATE TABLE presupuestos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INTEGER NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        monto_limite DECIMAL(15,2) NOT NULL,
        categoria_id INTEGER NOT NULL,
        fecha_inicio DATE NOT NULL,
        fecha_fin DATE NOT NULL,
        descripcion TEXT,
        activo TINYINT(1) DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
    );

INSERT INTO presupuestos (id, usuario_id, nombre, monto_limite, categoria_id, fecha_inicio, fecha_fin, descripcion, activo, fecha_creacion, fecha_actualizacion) VALUES ('1', '1', 'Presupuesto Alimentación', '2000', '20', '2025-03-01', '2025-03-31', NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO presupuestos (id, usuario_id, nombre, monto_limite, categoria_id, fecha_inicio, fecha_fin, descripcion, activo, fecha_creacion, fecha_actualizacion) VALUES ('2', '1', 'Presupuesto Transporte', '1000', '21', '2025-03-01', '2025-03-31', NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO presupuestos (id, usuario_id, nombre, monto_limite, categoria_id, fecha_inicio, fecha_fin, descripcion, activo, fecha_creacion, fecha_actualizacion) VALUES ('3', '1', 'Presupuesto Entretenimiento', '500', '23', '2025-03-01', '2025-03-31', NULL, '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');

-- Table: metas_ahorro
CREATE TABLE metas_ahorro (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INTEGER NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        monto_objetivo DECIMAL(15,2) NOT NULL,
        monto_actual DECIMAL(15,2) DEFAULT 0.00,
        fecha_inicio DATE NOT NULL,
        fecha_objetivo DATE NOT NULL,
        descripcion TEXT,
        activa TINYINT(1) DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    );

INSERT INTO metas_ahorro (id, usuario_id, nombre, monto_objetivo, monto_actual, fecha_inicio, fecha_objetivo, descripcion, activa, fecha_creacion, fecha_actualizacion) VALUES ('1', '1', 'Vacaciones Cancún', '5000', '2800', '2025-01-01', '2025-06-01', 'Ahorrar para vacaciones de verano', '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO metas_ahorro (id, usuario_id, nombre, monto_objetivo, monto_actual, fecha_inicio, fecha_objetivo, descripcion, activa, fecha_creacion, fecha_actualizacion) VALUES ('2', '1', 'Laptop nueva', '8000', '5200', '2025-01-01', '2025-08-01', 'Comprar una MacBook para la universidad', '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');
INSERT INTO metas_ahorro (id, usuario_id, nombre, monto_objetivo, monto_actual, fecha_inicio, fecha_objetivo, descripcion, activa, fecha_creacion, fecha_actualizacion) VALUES ('3', '1', 'Fondo de emergencia', '10000', '1500', '2025-03-01', '2025-12-31', 'Fondo de emergencia personal', '1', '2025-10-27 21:58:01', '2025-10-27 21:58:01');

-- Table: configuraciones
CREATE TABLE configuraciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        clave VARCHAR(100) UNIQUE NOT NULL,
        valor TEXT,
        descripcion TEXT,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );


