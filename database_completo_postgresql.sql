-- ============================================================================
-- BASE DE DATOS COMPLETA PARA POSTGRESQL
-- Sistema FIME - Gestión de Gastos Personales
-- ============================================================================
-- Este script crea TODAS las tablas y las llena con datos reales
-- Compatible con PostgreSQL 12+
-- ============================================================================

-- ============================================================================
-- 1. CREAR TABLA: usuarios
-- ============================================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
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
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 2. CREAR TABLA: cuentas_bancarias
-- ============================================================================
CREATE TABLE IF NOT EXISTS cuentas_bancarias (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(20) NOT NULL,
    banco VARCHAR(100) NOT NULL,
    numero_cuenta VARCHAR(50) UNIQUE NOT NULL,
    balance_actual DECIMAL(15,2) DEFAULT 0.00,
    balance_disponible DECIMAL(15,2) DEFAULT 0.00,
    limite_credito DECIMAL(15,2) DEFAULT 0.00,
    fecha_apertura DATE,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================================
-- 3. CREAR TABLA: categorias
-- ============================================================================
CREATE TABLE IF NOT EXISTS categorias (
    id SERIAL PRIMARY KEY,
    usuario_id INT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(20) NOT NULL,
    color VARCHAR(7) DEFAULT '#007bff',
    icono VARCHAR(50) DEFAULT 'ri-folder-line',
    descripcion TEXT,
    es_predefinida BOOLEAN DEFAULT FALSE,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================================
-- 4. CREAR TABLA: transacciones
-- ============================================================================
CREATE TABLE IF NOT EXISTS transacciones (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    cuenta_id INT NOT NULL,
    categoria_id INT NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    monto DECIMAL(15,2) NOT NULL,
    tipo VARCHAR(20) NOT NULL,
    fecha DATE NOT NULL,
    notas TEXT,
    recurrente BOOLEAN DEFAULT FALSE,
    frecuencia VARCHAR(20) NULL,
    fecha_fin_recurrencia DATE NULL,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (cuenta_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- ============================================================================
-- 5. CREAR TABLA: transferencias
-- ============================================================================
CREATE TABLE IF NOT EXISTS transferencias (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    cuenta_origen_id INT NOT NULL,
    cuenta_destino_id INT NOT NULL,
    monto DECIMAL(15,2) NOT NULL,
    descripcion VARCHAR(255),
    fecha DATE NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (cuenta_origen_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE,
    FOREIGN KEY (cuenta_destino_id) REFERENCES cuentas_bancarias(id) ON DELETE CASCADE
);

-- ============================================================================
-- 6. CREAR TABLA: presupuestos
-- ============================================================================
CREATE TABLE IF NOT EXISTS presupuestos (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    monto_limite DECIMAL(15,2) NOT NULL,
    categoria_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- ============================================================================
-- 7. CREAR TABLA: metas_ahorro
-- ============================================================================
CREATE TABLE IF NOT EXISTS metas_ahorro (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    monto_objetivo DECIMAL(15,2) NOT NULL,
    monto_actual DECIMAL(15,2) DEFAULT 0.00,
    fecha_inicio DATE NOT NULL,
    fecha_objetivo DATE NOT NULL,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- ============================================================================
-- 8. CREAR TABLA: configuraciones
-- ============================================================================
CREATE TABLE IF NOT EXISTS configuraciones (
    id SERIAL PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- CREAR TRIGGERS PARA ACTUALIZAR fecha_actualizacion AUTOMÁTICAMENTE
-- ============================================================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Aplicar trigger a todas las tablas que tienen fecha_actualizacion
CREATE TRIGGER update_usuarios_updated_at BEFORE UPDATE ON usuarios
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_cuentas_bancarias_updated_at BEFORE UPDATE ON cuentas_bancarias
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_transacciones_updated_at BEFORE UPDATE ON transacciones
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_presupuestos_updated_at BEFORE UPDATE ON presupuestos
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_metas_ahorro_updated_at BEFORE UPDATE ON metas_ahorro
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_configuraciones_updated_at BEFORE UPDATE ON configuraciones
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================================================
-- INSERTAR DATOS
-- ============================================================================

-- ============================================================================
-- 1. INSERTAR USUARIOS
-- ============================================================================
INSERT INTO usuarios (nombre, email, password_hash, telefono, fecha_nacimiento, genero, ciudad, estado, activo) VALUES
('Administrador', 'admin@fime.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '8112345678', '1990-01-15', 'masculino', 'Monterrey', 'Nuevo León', TRUE),
('Juan Pérez García', 'juan.perez@fime.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '8123456789', '1998-05-15', 'masculino', 'Monterrey', 'Nuevo León', TRUE),
('María González López', 'maria.gonzalez@fime.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '8123456790', '1999-08-20', 'femenino', 'San Pedro', 'Nuevo León', TRUE),
('Carlos Ramírez Martínez', 'carlos.ramirez@fime.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '8123456791', '1997-12-10', 'masculino', 'Monterrey', 'Nuevo León', TRUE)
ON CONFLICT (email) DO NOTHING;

-- ============================================================================
-- 2. INSERTAR CATEGORÍAS PREDEFINIDAS
-- ============================================================================
INSERT INTO categorias (nombre, tipo, color, icono, es_predefinida) VALUES
-- Ingresos
('Salario', 'ingreso', '#28a745', 'ri-money-dollar-circle-line', TRUE),
('Freelance', 'ingreso', '#28a745', 'ri-briefcase-line', TRUE),
('Inversiones', 'ingreso', '#28a745', 'ri-line-chart-line', TRUE),
('Regalos', 'ingreso', '#28a745', 'ri-gift-line', TRUE),
('Otros Ingresos', 'ingreso', '#28a745', 'ri-add-circle-line', TRUE),
('Beca Escolar', 'ingreso', '#17a2b8', 'ri-award-line', TRUE),
('Trabajo Part Time', 'ingreso', '#28a745', 'ri-user-line', TRUE),
('Préstamo Pagado', 'ingreso', '#28a745', 'ri-exchange-dollar-line', TRUE),
-- Gastos
('Alimentación', 'gasto', '#dc3545', 'ri-restaurant-line', TRUE),
('Transporte', 'gasto', '#dc3545', 'ri-car-line', TRUE),
('Vivienda', 'gasto', '#dc3545', 'ri-home-line', TRUE),
('Entretenimiento', 'gasto', '#dc3545', 'ri-movie-line', TRUE),
('Salud', 'gasto', '#dc3545', 'ri-heart-pulse-line', TRUE),
('Educación', 'gasto', '#dc3545', 'ri-book-line', TRUE),
('Ropa', 'gasto', '#dc3545', 'ri-clothes-line', TRUE),
('Servicios', 'gasto', '#dc3545', 'ri-tools-line', TRUE),
('Otros Gastos', 'gasto', '#dc3545', 'ri-shopping-cart-line', TRUE),
('Teléfono Celular', 'gasto', '#6c757d', 'ri-phone-line', TRUE),
('Internet', 'gasto', '#6c757d', 'ri-wifi-line', TRUE),
('Gimnasio', 'gasto', '#6f42c1', 'ri-dumbbell-line', TRUE),
('Suscripciones', 'gasto', '#6f42c1', 'ri-shopping-cart-2-line', TRUE)
ON CONFLICT DO NOTHING;

-- ============================================================================
-- 3. INSERTAR CUENTAS BANCARIAS
-- ============================================================================
INSERT INTO cuentas_bancarias (usuario_id, nombre, tipo, banco, numero_cuenta, balance_actual, balance_disponible, activa) VALUES
-- Cuentas del Administrador
(1, 'Cuenta Principal BBVA', 'corriente', 'BBVA Bancomer', '1234567890', 8500.00, 8000.00, TRUE),
(1, 'Cuenta de Ahorros Santander', 'ahorros', 'Santander', '0987654321', 15000.00, 15000.00, TRUE),
(1, 'Tarjeta de Crédito HSBC', 'credito', 'HSBC', '555544443333', -3500.00, -8000.00, TRUE),
(1, 'Cuenta Inversión', 'inversion', 'GNP', '999988887777', 20000.00, 20000.00, TRUE),
-- Cuentas de Juan Pérez
(2, 'Cuenta Principal Banorte', 'corriente', 'Banorte', '1111222233', 5500.00, 5000.00, TRUE),
(2, 'Cuenta Ahorro', 'ahorros', 'Banorte', '2222333344', 3000.00, 3000.00, TRUE),
-- Cuentas de María González
(3, 'Cuenta Principal Banregio', 'corriente', 'Banregio', '3333444455', 4200.00, 4000.00, TRUE),
(3, 'Tarjeta de Crédito', 'credito', 'Banamex', '4444555566', -1500.00, -5000.00, TRUE),
-- Cuentas de Carlos Ramírez
(4, 'Cuenta Principal Scotiabank', 'corriente', 'Scotiabank', '5555666677', 6800.00, 6000.00, TRUE)
ON CONFLICT (numero_cuenta) DO NOTHING;

-- ============================================================================
-- 4. INSERTAR TRANSACCIONES (50 transacciones realistas)
-- ============================================================================
-- Transacciones para el Administrador (usuario_id = 1)
INSERT INTO transacciones (usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas) VALUES
-- Enero 2025 - Ingresos
(1, 1, 1, 'Salario mensual', 8000.00, 'ingreso', '2025-01-15', 'Pago quincenal'),
(1, 1, 2, 'Proyecto freelance desarrollo web', 3500.00, 'ingreso', '2025-01-18', 'Desarrollo de sitio e-commerce'),
(1, 2, 3, 'Dividendos inversiones', 1200.00, 'ingreso', '2025-01-25', 'Rendimiento trimestral'),
-- Enero 2025 - Gastos
(1, 1, 9, 'Supermercado Costco', 1800.00, 'gasto', '2025-01-03', 'Despensa mensual'),
(1, 1, 9, 'Despensa Walmart', 650.00, 'gasto', '2025-01-10', 'Alimentos semanales'),
(1, 1, 9, 'Supermercado', 520.00, 'gasto', '2025-01-17', NULL),
(1, 3, 11, 'Renta departamento', 4500.00, 'gasto', '2025-01-01', 'Renta mensual centro'),
(1, 1, 10, 'Gasolina', 850.00, 'gasto', '2025-01-05', '50 litros'),
(1, 1, 10, 'Estacionamiento', 150.00, 'gasto', '2025-01-12', 'Semanal'),
(1, 1, 12, 'Netflix + Spotify', 380.00, 'gasto', '2025-01-01', 'Suscripciones mensuales'),
(1, 1, 12, 'Cine con amigos', 350.00, 'gasto', '2025-01-08', NULL),
(1, 1, 13, 'Consulta médica general', 600.00, 'gasto', '2025-01-15', 'Checkup anual'),
(1, 1, 15, 'Luz', 420.00, 'gasto', '2025-01-10', NULL),
(1, 1, 15, 'Agua', 180.00, 'gasto', '2025-01-12', NULL),
(1, 1, 15, 'Gas', 250.00, 'gasto', '2025-01-14', NULL),
-- Febrero 2025 - Ingresos
(1, 1, 1, 'Salario mensual', 8000.00, 'ingreso', '2025-02-15', NULL),
(1, 1, 4, 'Dinero cumpleaños', 1500.00, 'ingreso', '2025-02-20', 'Regalo familiar'),
-- Febrero 2025 - Gastos
(1, 1, 9, 'Despensa Costco', 1900.00, 'gasto', '2025-02-01', NULL),
(1, 1, 9, 'Supermercado', 680.00, 'gasto', '2025-02-08', NULL),
(1, 3, 11, 'Renta departamento', 4500.00, 'gasto', '2025-02-01', NULL),
(1, 1, 10, 'Gasolina y lavado', 920.00, 'gasto', '2025-02-07', NULL),
(1, 1, 14, 'Calzado deportivo', 1350.00, 'gasto', '2025-02-10', 'Tenis Nike'),
(1, 1, 15, 'Internet fibra óptica', 550.00, 'gasto', '2025-02-01', NULL),
(1, 1, 14, 'Ropa casual', 850.00, 'gasto', '2025-02-15', NULL),
-- Marzo 2025 - Ingresos
(1, 1, 1, 'Salario mensual', 8000.00, 'ingreso', '2025-03-15', NULL),
(1, 1, 2, 'Diseño gráfico', 2800.00, 'ingreso', '2025-03-22', 'Logos y branding'),
-- Marzo 2025 - Gastos
(1, 1, 9, 'Despensa completa', 1700.00, 'gasto', '2025-03-02', NULL),
(1, 1, 9, 'Supermercado', 590.00, 'gasto', '2025-03-09', NULL),
(1, 3, 11, 'Renta departamento', 4500.00, 'gasto', '2025-03-01', NULL),
(1, 1, 10, 'Uber transporte', 480.00, 'gasto', '2025-03-05', 'Viajes varios'),
(1, 1, 12, 'Evento musical', 450.00, 'gasto', '2025-03-18', 'Concierto'),
(1, 1, 13, 'Farmacia medicamentos', 320.00, 'gasto', '2025-03-20', NULL),
(1, 1, 14, 'Material de oficina', 150.00, 'gasto', '2025-03-12', NULL)
ON CONFLICT DO NOTHING;

-- Transacciones para Juan Pérez (usuario_id = 2)
INSERT INTO transacciones (usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas) VALUES
(2, 5, 6, 'Beca escolar semestral', 8000.00, 'ingreso', '2025-01-15', 'Beca por buen promedio'),
(2, 5, 6, 'Beca alimenticia mensual', 500.00, 'ingreso', '2025-02-01', NULL),
(2, 5, 9, 'Despensa universidad', 450.00, 'gasto', '2025-01-10', NULL),
(2, 5, 10, 'Transporte público mensual', 220.00, 'gasto', '2025-01-05', NULL),
(2, 5, 12, 'Netflix, Spotify, Disney+', 450.00, 'gasto', '2025-01-01', NULL),
(2, 5, 11, 'Libros de texto', 1200.00, 'gasto', '2025-01-25', 'Semestre nuevo'),
(2, 5, 13, 'Consulta dental', 800.00, 'gasto', '2025-02-10', NULL),
(2, 5, 14, 'Ropa casual universidad', 650.00, 'gasto', '2025-02-20', NULL)
ON CONFLICT DO NOTHING;

-- Transacciones para María González (usuario_id = 3)
INSERT INTO transacciones (usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas) VALUES
(3, 7, 1, 'Salario part time', 3200.00, 'ingreso', '2025-01-15', 'Cajera medio tiempo'),
(3, 7, 1, 'Salario part time', 3200.00, 'ingreso', '2025-02-15', NULL),
(3, 7, 9, 'Supermercado', 580.00, 'gasto', '2025-01-05', NULL),
(3, 8, 12, 'Suscripciones streaming', 380.00, 'gasto', '2025-01-01', NULL),
(3, 7, 10, 'Gasolina moto', 250.00, 'gasto', '2025-01-08', NULL),
(3, 7, 15, 'Teléfono celular', 400.00, 'gasto', '2025-01-15', 'Plan mensual'),
(3, 7, 14, 'Vestido y accesorios', 950.00, 'gasto', '2025-02-14', 'Fecha especial')
ON CONFLICT DO NOTHING;

-- Transacciones para Carlos Ramírez (usuario_id = 4)
INSERT INTO transacciones (usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas) VALUES
(4, 9, 2, 'Proyecto diseño gráfico', 4500.00, 'ingreso', '2025-01-20', 'Branding para cliente'),
(4, 9, 2, 'Edición de video', 2800.00, 'ingreso', '2025-02-10', 'Video corporativo'),
(4, 9, 9, 'Despensa mensual', 900.00, 'gasto', '2025-01-12', NULL),
(4, 9, 10, 'Combustible', 600.00, 'gasto', '2025-01-15', NULL),
(4, 9, 21, 'Gimnasio mensual', 350.00, 'gasto', '2025-01-01', NULL),
(4, 9, 16, 'Internet hogar', 450.00, 'gasto', '2025-01-01', NULL),
(4, 9, 14, 'Equipo de cómputo', 8500.00, 'gasto', '2025-02-05', 'Laptop nueva Dell')
ON CONFLICT DO NOTHING;

-- ============================================================================
-- 5. INSERTAR TRANSFERENCIAS
-- ============================================================================
INSERT INTO transferencias (usuario_id, cuenta_origen_id, cuenta_destino_id, monto, descripcion, fecha) VALUES
(1, 1, 2, 3000.00, 'Transferencia a ahorros', '2025-01-15'),
(1, 1, 2, 2500.00, 'Ahorro programado', '2025-01-29'),
(1, 2, 1, 5000.00, 'Retiro para gastos', '2025-02-10'),
(1, 4, 2, 8000.00, 'Ahorro de inversión', '2025-02-25'),
(2, 5, 6, 1000.00, 'Transferencia a ahorro', '2025-01-20')
ON CONFLICT DO NOTHING;

-- ============================================================================
-- 6. INSERTAR PRESUPUESTOS
-- ============================================================================
INSERT INTO presupuestos (usuario_id, nombre, monto_limite, categoria_id, fecha_inicio, fecha_fin, descripcion, activo) VALUES
(1, 'Presupuesto Alimentación Marzo', 2200.00, 9, '2025-03-01', '2025-03-31', 'Gastos de comida del mes', TRUE),
(1, 'Presupuesto Transporte', 1200.00, 10, '2025-03-01', '2025-03-31', 'Gasolina y transporte', TRUE),
(1, 'Presupuesto Entretenimiento', 700.00, 12, '2025-03-01', '2025-03-31', 'Streaming y ocio', TRUE),
(1, 'Presupuesto Servicios Básicos', 800.00, 15, '2025-03-01', '2025-03-31', 'Luz, agua, gas', TRUE),
(2, 'Presupuesto Universitario Mensual', 2500.00, 18, '2025-03-01', '2025-03-31', 'Gastos personales mensuales', TRUE),
(3, 'Presupuesto Básico', 1800.00, 18, '2025-03-01', '2025-03-31', 'Gastos básicos del mes', TRUE)
ON CONFLICT DO NOTHING;

-- ============================================================================
-- 7. INSERTAR METAS DE AHORRO
-- ============================================================================
INSERT INTO metas_ahorro (usuario_id, nombre, monto_objetivo, monto_actual, fecha_inicio, fecha_objetivo, descripcion, activa) VALUES
(1, 'Vacaciones Cancún 2025', 8000.00, 4200.00, '2025-01-01', '2025-06-01', 'Ahorro para vacaciones de verano en Cancún', TRUE),
(1, 'MacBook Pro para Desarrollo', 25000.00, 8500.00, '2025-01-01', '2025-09-01', 'Laptop para desarrollo profesional', TRUE),
(1, 'Fondo de Emergencia', 20000.00, 5000.00, '2025-02-01', '2025-12-31', 'Fondo de emergencia 6 meses de gastos', TRUE),
(2, 'Tablet iPad para Estudios', 4500.00, 800.00, '2025-02-01', '2025-08-01', 'Tablet para tomar notas digitales', TRUE),
(3, 'Viaje a CDMX', 5000.00, 1200.00, '2025-03-01', '2025-07-01', 'Viaje turístico a Ciudad de México', TRUE),
(4, 'Bicicleta MTB', 6500.00, 2000.00, '2025-01-01', '2025-06-01', 'Bicicleta de montaña profesional', TRUE)
ON CONFLICT DO NOTHING;

-- ============================================================================
-- 8. INSERTAR CONFIGURACIONES
-- ============================================================================
INSERT INTO configuraciones (clave, valor, descripcion) VALUES
('moneda_default', 'MXN', 'Moneda por defecto del sistema'),
('interes_mensual_tarjeta', '3.5', 'Interés mensual de tarjeta de crédito'),
('alertas_email', 'true', 'Activar alertas por email'),
('theme_default', 'light', 'Tema por defecto del sistema'),
('retenimiento_nomina', '30', 'Porcentaje de retención de nómina'),
('fecha_cierre_ciclo', '28', 'Día de cierre de tarjeta de crédito')
ON CONFLICT (clave) DO UPDATE SET valor = EXCLUDED.valor;

-- ============================================================================
-- RESUMEN DE DATOS
-- ============================================================================
-- Usuarios: 4
-- Cuentas bancarias: 9
-- Categorías: 18 predefinidas
-- Transacciones: ~50
-- Presupuestos: 6
-- Metas de ahorro: 6
-- Transferencias: 5
-- Configuraciones: 6
-- ============================================================================

