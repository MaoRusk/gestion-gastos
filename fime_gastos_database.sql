-- =====================================================
-- FIME - Sistema de Gestión de Gastos Personales
-- Base de Datos MySQL/SQLite
-- =====================================================

-- Crear base de datos (solo para MySQL)
-- CREATE DATABASE IF NOT EXISTS fime_gastos_db;
-- USE fime_gastos_db;

-- =====================================================
-- TABLA: usuarios
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    fecha_nacimiento DATE,
    genero ENUM('masculino', 'femenino', 'otro') DEFAULT 'otro',
    direccion TEXT,
    ciudad VARCHAR(50),
    estado VARCHAR(50),
    codigo_postal VARCHAR(10),
    pais VARCHAR(50) DEFAULT 'México',
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA: cuentas_bancarias
-- =====================================================
CREATE TABLE IF NOT EXISTS cuentas_bancarias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('corriente', 'ahorros', 'credito', 'inversion') NOT NULL,
    banco VARCHAR(100) NOT NULL,
    numero_cuenta VARCHAR(50) UNIQUE NOT NULL,
    balance_actual DECIMAL(15,2) DEFAULT 0.00,
    balance_disponible DECIMAL(15,2) DEFAULT 0.00,
    limite_credito DECIMAL(15,2) DEFAULT 0.00,
    fecha_apertura DATE,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: categorias
-- =====================================================
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('ingreso', 'gasto') NOT NULL,
    color VARCHAR(7) DEFAULT '#007bff',
    icono VARCHAR(50) DEFAULT 'ri-folder-line',
    descripcion TEXT,
    es_predefinida BOOLEAN DEFAULT FALSE,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: transacciones
-- =====================================================
CREATE TABLE IF NOT EXISTS transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    cuenta_id INT NOT NULL,
    categoria_id INT NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    monto DECIMAL(15,2) NOT NULL,
    tipo ENUM('ingreso', 'gasto', 'transferencia') NOT NULL,
    fecha DATE NOT NULL,
    notas TEXT,
    recurrente BOOLEAN DEFAULT FALSE,
    frecuencia ENUM('diaria', 'semanal', 'mensual', 'anual') NULL,
    fecha_fin_recurrencia DATE NULL,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (cuenta_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: transferencias
-- =====================================================
CREATE TABLE IF NOT EXISTS transferencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    cuenta_origen_id INT NOT NULL,
    cuenta_destino_id INT NOT NULL,
    monto DECIMAL(15,2) NOT NULL,
    descripcion VARCHAR(255),
    fecha DATE NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (cuenta_origen_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE,
    FOREIGN KEY (cuenta_destino_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: presupuestos
-- =====================================================
CREATE TABLE IF NOT EXISTS presupuestos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    monto_limite DECIMAL(15,2) NOT NULL,
    categoria_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: metas_ahorro
-- =====================================================
CREATE TABLE IF NOT EXISTS metas_ahorro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    monto_objetivo DECIMAL(15,2) NOT NULL,
    monto_actual DECIMAL(15,2) DEFAULT 0.00,
    fecha_inicio DATE NOT NULL,
    fecha_objetivo DATE NOT NULL,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- =====================================================
-- TABLA: configuraciones
-- =====================================================
CREATE TABLE IF NOT EXISTS configuraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- DATOS INICIALES
-- =====================================================

-- Usuario administrador
INSERT INTO usuarios (nombre, email, password_hash, activo) VALUES
('Administrador', 'admin@fime.com', '$2y$12$y00rlL.NvBSzm6jtKhdRlOiOerWSvp7vT2LQlYQQWV1/fscpL3WSW', TRUE);

-- Categorías predefinidas - Ingresos
INSERT INTO categorias (nombre, tipo, color, icono, es_predefinida) VALUES
('Salario', 'ingreso', '#28a745', 'ri-money-dollar-circle-line', TRUE),
('Freelance', 'ingreso', '#28a745', 'ri-briefcase-line', TRUE),
('Inversiones', 'ingreso', '#28a745', 'ri-line-chart-line', TRUE),
('Regalos', 'ingreso', '#28a745', 'ri-gift-line', TRUE),
('Otros Ingresos', 'ingreso', '#28a745', 'ri-add-circle-line', TRUE);

-- Categorías predefinidas - Gastos
INSERT INTO categorias (nombre, tipo, color, icono, es_predefinida) VALUES
('Alimentación', 'gasto', '#dc3545', 'ri-restaurant-line', TRUE),
('Transporte', 'gasto', '#dc3545', 'ri-car-line', TRUE),
('Vivienda', 'gasto', '#dc3545', 'ri-home-line', TRUE),
('Entretenimiento', 'gasto', '#dc3545', 'ri-movie-line', TRUE),
('Salud', 'gasto', '#dc3545', 'ri-heart-pulse-line', TRUE),
('Educación', 'gasto', '#dc3545', 'ri-book-line', TRUE),
('Ropa', 'gasto', '#dc3545', 'ri-clothes-line', TRUE),
('Servicios', 'gasto', '#dc3545', 'ri-tools-line', TRUE),
('Otros Gastos', 'gasto', '#dc3545', 'ri-shopping-cart-line', TRUE);

-- Cuentas de ejemplo para el usuario administrador
INSERT INTO cuentas_bancarias (usuario_id, nombre, tipo, banco, numero_cuenta, balance_actual) VALUES
(1, 'Cuenta Principal', 'corriente', 'BBVA', '1234567890', 5000.00),
(1, 'Cuenta de Ahorros', 'ahorros', 'Santander', '0987654321', 10000.00),
(1, 'Tarjeta de Crédito', 'credito', 'HSBC', '555544443333', -2000.00);

-- =====================================================
-- ÍNDICES PARA OPTIMIZACIÓN
-- =====================================================

-- Índices para usuarios
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_activo ON usuarios(activo);

-- Índices para cuentas bancarias
CREATE INDEX idx_cuentas_usuario ON cuentas_bancarias(usuario_id);
CREATE INDEX idx_cuentas_activa ON cuentas_bancarias(activa);

-- Índices para categorías
CREATE INDEX idx_categorias_usuario ON categorias(usuario_id);
CREATE INDEX idx_categorias_tipo ON categorias(tipo);
CREATE INDEX idx_categorias_activa ON categorias(activa);

-- Índices para transacciones
CREATE INDEX idx_transacciones_usuario ON transacciones(usuario_id);
CREATE INDEX idx_transacciones_cuenta ON transacciones(cuenta_id);
CREATE INDEX idx_transacciones_categoria ON transacciones(categoria_id);
CREATE INDEX idx_transacciones_fecha ON transacciones(fecha);
CREATE INDEX idx_transacciones_tipo ON transacciones(tipo);

-- Índices para presupuestos
CREATE INDEX idx_presupuestos_usuario ON presupuestos(usuario_id);
CREATE INDEX idx_presupuestos_categoria ON presupuestos(categoria_id);
CREATE INDEX idx_presupuestos_fechas ON presupuestos(fecha_inicio, fecha_fin);

-- Índices para metas de ahorro
CREATE INDEX idx_metas_usuario ON metas_ahorro(usuario_id);
CREATE INDEX idx_metas_fechas ON metas_ahorro(fecha_inicio, fecha_objetivo);

-- =====================================================
-- COMENTARIOS FINALES
-- =====================================================

/*
SISTEMA DE GESTIÓN DE GASTOS PERSONALES - FIME

ESTRUCTURA DE LA BASE DE DATOS:
- usuarios: Información de usuarios del sistema
- cuentas_bancarias: Cuentas bancarias de cada usuario
- categorias: Categorías para clasificar transacciones
- transacciones: Registro de ingresos y gastos
- transferencias: Transferencias entre cuentas
- presupuestos: Presupuestos por categoría
- metas_ahorro: Objetivos de ahorro de usuarios
- configuraciones: Configuraciones del sistema

USUARIO ADMINISTRADOR:
- Email: admin@fime.com
- Contraseña: admin123

CARACTERÍSTICAS:
- Compatible con MySQL y SQLite
- Relaciones de integridad referencial
- Índices optimizados para consultas frecuentes
- Categorías predefinidas para facilitar el uso
- Datos de ejemplo para testing

VERSIÓN: 1.0
FECHA: 2025
AUTOR: Sistema FIME
*/
