-- ============================================================================
-- SCRIPTS SQL PARA INSERTAR DATOS MANUALMENTE
-- Sistema FIME - Gestión de Gastos Personales
-- ============================================================================
-- INSTRUCCIONES:
-- 1. Abre MySQL Workbench
-- 2. Conéctate a tu servidor MySQL/MariaDB
-- 3. Ejecuta estos comandos uno por uno o todos juntos
-- 4. Asegúrate de estar en la base de datos 'fime_gastos'
-- ============================================================================

USE fime_gastos;

-- ============================================================================
-- 1. INSERTAR USUARIOS ADICIONALES
-- ============================================================================
INSERT INTO usuarios (nombre, email, password_hash, telefono, fecha_nacimiento, genero, ciudad, estado, activo) VALUES
('Juan Pérez', 'juan.perez@fime.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '8123456789', '1998-05-15', 'masculino', 'Monterrey', 'Nuevo León', 1),
('María González', 'maria.gonzalez@fime.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '8123456790', '1999-08-20', 'femenino', 'San Pedro', 'Nuevo León', 1),
('Carlos Ramírez', 'carlos.ramirez@fime.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '8123456791', '1997-12-10', 'masculino', 'Monterrey', 'Nuevo León', 1);

-- ============================================================================
-- 2. INSERTAR CUENTAS BANCARIAS (para el usuario admin y otros)
-- ============================================================================
INSERT INTO cuentas_bancarias (usuario_id, nombre, tipo, banco, numero_cuenta, balance_actual, balance_disponible, activa) VALUES
(1, 'Cuenta Principal BBVA', 'corriente', 'BBVA', '1234567890', 5000.00, 4500.00, 1),
(1, 'Cuenta de Ahorros', 'ahorros', 'Santander', '0987654321', 10000.00, 10000.00, 1),
(1, 'Tarjeta de Crédito', 'credito', 'HSBC', '555544443333', -2000.00, -5000.00, 1),
(1, 'Cuenta Inversión', 'inversion', 'GNP', '999988887777', 15000.00, 15000.00, 1),
(2, 'Cuenta Principal', 'corriente', 'Banorte', '1111222233', 3000.00, 2800.00, 1),
(3, 'Cuenta Egresada', 'corriente', 'Banregio', '4444555566', 2500.00, 2400.00, 1);

-- ============================================================================
-- 3. INSERTAR CATEGORÍAS PERSONALIZADAS
-- ============================================================================
-- (Ya existen las predefinidas, estas son adicionales)
INSERT INTO categorias (usuario_id, nombre, tipo, color, icono, descripcion, es_predefinida, activa) VALUES
(2, 'Trabajo Freelance', 'ingreso', '#17a2b8', 'ri-briefcase-3-line', 'Trabajos como freelance', 0, 1),
(2, 'Beca Escolar', 'ingreso', '#28a745', 'ri-award-line', 'Beca de estudios', 0, 1),
(2, 'Gimnasio', 'gasto', '#dc3545', 'ri-dumbbell-line', 'Cuota de gimnasio', 0, 1),
(2, 'Suscripciones', 'gasto', '#6f42c1', 'ri-shopping-cart-2-line', 'Netflix, Spotify, etc.', 0, 1);

-- ============================================================================
-- 4. INSERTAR TRANSACCIONES (Últimos 3 meses para admin)
-- ============================================================================
INSERT INTO transacciones (usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha, notas, recurrente, frecuencia) VALUES
-- Enero 2025 - Ingresos
(1, 1, 1, 'Salario enero', 5000.00, 'ingreso', '2025-01-15', NULL, 1, 'mensual'),
(1, 1, 2, 'Proyecto freelance sitio web', 1500.00, 'ingreso', '2025-01-20', 'Desarrollo página web para cliente', 0, NULL),
(1, 2, 3, 'Rendimiento inversión', 800.00, 'ingreso', '2025-01-25', 'Dividendos trimestrales', 0, NULL),

-- Enero 2025 - Gastos
(1, 1, 6, 'Supermercado semana 1', 1200.00, 'gasto', '2025-01-02', NULL, 0, NULL),
(1, 1, 6, 'Supermercado semana 2', 1350.00, 'gasto', '2025-01-09', NULL, 0, NULL),
(1, 1, 6, 'Supermercado semana 3', 1180.00, 'gasto', '2025-01-16', NULL, 0, NULL),
(1, 1, 8, 'Renta departamento', 3500.00, 'gasto', '2025-01-01', 'Pago mensual', 1, 'mensual'),
(1, 1, 7, 'Gasolina', 750.00, 'gasto', '2025-01-05', NULL, 0, NULL),
(1, 1, 9, 'Cine con amigos', 280.00, 'gasto', '2025-01-10', NULL, 0, NULL),
(1, 1, 13, 'Luz', 320.00, 'gasto', '2025-01-12', NULL, 0, NULL),
(1, 1, 13, 'Agua', 180.00, 'gasto', '2025-01-14', NULL, 0, NULL),
(1, 1, 10, 'Consulta médica', 500.00, 'gasto', '2025-01-18', NULL, 0, NULL),

-- Febrero 2025 - Ingresos
(1, 1, 1, 'Salario febrero', 5000.00, 'ingreso', '2025-02-15', NULL, 0, NULL),
(1, 1, 4, 'Regalo cumpleaños', 1000.00, 'ingreso', '2025-02-20', 'Dinero recibido por cumpleaños', 0, NULL),

-- Febrero 2025 - Gastos
(1, 1, 6, 'Supermercado semana 1', 1400.00, 'gasto', '2025-02-02', NULL, 0, NULL),
(1, 1, 6, 'Supermercado semana 2', 1250.00, 'gasto', '2025-02-09', NULL, 0, NULL),
(1, 1, 8, 'Renta departamento', 3500.00, 'gasto', '2025-02-01', NULL, 0, NULL),
(1, 1, 7, 'Gasolina y estacionamiento', 850.00, 'gasto', '2025-02-08', NULL, 0, NULL),
(1, 1, 13, 'Internet', 480.00, 'gasto', '2025-02-01', NULL, 1, 'mensual'),
(1, 1, 11, 'Libros de texto', 800.00, 'gasto', '2025-02-05', NULL, 0, NULL),
(1, 1, 12, 'Ropa nueva temporada', 1200.00, 'gasto', '2025-02-12', NULL, 0, NULL),
(1, 1, 10, 'Medicamentos', 320.00, 'gasto', '2025-02-25', NULL, 0, NULL),

-- Marzo 2025 - Ingresos
(1, 1, 1, 'Salario marzo', 5000.00, 'ingreso', '2025-03-15', NULL, 0, NULL),
(1, 1, 2, 'Edición de video', 2000.00, 'ingreso', '2025-03-20', 'Proyecto de edición de video', 0, NULL),

-- Marzo 2025 - Gastos
(1, 1, 6, 'Supermercado semana 1', 1300.00, 'gasto', '2025-03-02', NULL, 0, NULL),
(1, 1, 6, 'Supermercado semana 2', 1180.00, 'gasto', '2025-03-09', NULL, 0, NULL),
(1, 1, 8, 'Renta departamento', 3500.00, 'gasto', '2025-03-01', NULL, 0, NULL),
(1, 1, 7, 'Servicio de uber', 420.00, 'gasto', '2025-03-10', NULL, 0, NULL),
(1, 1, 9, 'Netflix', 200.00, 'gasto', '2025-03-01', NULL, 1, 'mensual'),
(1, 1, 9, 'Spotify', 150.00, 'gasto', '2025-03-01', NULL, 1, 'mensual');

-- ============================================================================
-- 5. ACTUALIZAR SALDOS DE CUENTAS (después de las transacciones)
-- ============================================================================
-- Nota: Los saldos ya deberían actualizarse automáticamente
-- Pero por si acaso, aquí los actualizamos manualmente

UPDATE cuentas_bancarias SET balance_actual = 
    (SELECT COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE -monto END), 0) 
     FROM transacciones WHERE cuenta_id = cuentas_bancarias.id)
    WHERE id IN (SELECT DISTINCT cuenta_id FROM transacciones);

-- ============================================================================
-- 6. INSERTAR PRESUPUESTOS
-- ============================================================================
INSERT INTO presupuestos (usuario_id, nombre, monto_limite, categoria_id, fecha_inicio, fecha_fin, descripcion, activo) VALUES
(1, 'Presupuesto Mensual Alimentación', 2000.00, 6, '2025-03-01', '2025-03-31', 'Presupuesto mensual para gastos de alimentación', 1),
(1, 'Presupuesto Transporte', 1000.00, 7, '2025-03-01', '2025-03-31', 'Gasolina, uber y transporte público', 1),
(1, 'Presupuesto Entretenimiento', 500.00, 9, '2025-03-01', '2025-03-31', 'Cine, suscripciones y ocio', 1),
(1, 'Presupuesto Educación', 1000.00, 11, '2025-03-01', '2025-03-31', 'Libros y materiales de estudio', 1);

-- ============================================================================
-- 7. INSERTAR METAS DE AHORRO
-- ============================================================================
INSERT INTO metas_ahorro (usuario_id, nombre, monto_objetivo, monto_actual, fecha_inicio, fecha_objetivo, descripcion, activa) VALUES
(1, 'Vacaciones Cancún', 5000.00, 2800.00, '2025-01-01', '2025-06-01', 'Ahorrar para vacaciones de verano', 1),
(1, 'Laptop nueva MacBook', 18000.00, 5200.00, '2025-01-01', '2025-08-01', 'Comprar una MacBook Pro para la universidad', 1),
(1, 'Fondo de emergencia', 15000.00, 3500.00, '2025-03-01', '2025-12-31', 'Fondo de emergencia para imprevistos', 1),
(2, 'Tablet para estudios', 3000.00, 500.00, '2025-03-01', '2025-09-01', 'Tablet para tomar notas en clase', 1),
(3, 'Bicicleta nueva', 4500.00, 1200.00, '2025-02-01', '2025-07-01', 'Bicicleta de montaña nueva', 1);

-- ============================================================================
-- 8. INSERTAR TRANSFERENCIAS (entre cuentas)
-- ============================================================================
INSERT INTO transferencias (usuario_id, cuenta_origen_id, cuenta_destino_id, monto, descripcion, fecha) VALUES
(1, 1, 2, 2000.00, 'Transferencia a cuenta de ahorros', '2025-01-15'),
(1, 1, 2, 1500.00, 'Ahorro semanal', '2025-01-22'),
(1, 1, 2, 2000.00, 'Transferencia mensual a ahorros', '2025-02-15'),
(1, 4, 1, 5000.00, 'Retiro de inversión para gastos', '2025-02-10');

-- ============================================================================
-- 9. INSERTAR TRANSACCIONES PARA OTROS USUARIOS
-- ============================================================================
-- Transacciones para Juan Pérez (usuario_id = 2)
INSERT INTO transacciones (usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha) VALUES
(2, 5, 1, 'Beca semestral', 6000.00, 'ingreso', '2025-01-20'),
(2, 5, 6, 'Despensa mensual', 800.00, 'gasto', '2025-01-05'),
(2, 5, 7, 'Transporte público', 200.00, 'gasto', '2025-01-10'),
(2, 5, 9, 'Servicios streaming', 350.00, 'gasto', '2025-01-15'),
(2, 5, 11, 'Material de laboratorio', 450.00, 'gasto', '2025-02-05');

-- Transacciones para Carlos Ramírez (usuario_id = 3)
INSERT INTO transacciones (usuario_id, cuenta_id, categoria_id, descripcion, monto, tipo, fecha) VALUES
(3, 6, 2, 'Trabajo freelance diseño', 2500.00, 'ingreso', '2025-01-25'),
(3, 6, 6, 'Supermercado', 600.00, 'gasto', '2025-01-30'),
(3, 6, 7, 'Combustible', 300.00, 'gasto', '2025-02-02'),
(3, 6, 9, 'Videoconferencia streaming', 180.00, 'gasto', '2025-02-10'),
(3, 6, 11, 'Cursos online', 1200.00, 'gasto', '2025-02-15');

-- ============================================================================
-- 10. VERIFICAR DATOS INSERTADOS
-- ============================================================================
-- Ejecuta estas queries para verificar que todo se insertó correctamente:

SELECT 'usuarios' as tabla, COUNT(*) as total FROM usuarios
UNION ALL
SELECT 'cuentas_bancarias', COUNT(*) FROM cuentas_bancarias
UNION ALL
SELECT 'categorias', COUNT(*) FROM categorias
UNION ALL
SELECT 'transacciones', COUNT(*) FROM transacciones
UNION ALL
SELECT 'presupuestos', COUNT(*) FROM presupuestos
UNION ALL
SELECT 'metas_ahorro', COUNT(*) FROM metas_ahorro
UNION ALL
SELECT 'transferencias', COUNT(*) FROM transferencias;

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
-- 
-- Después de ejecutar este script, tendrás:
-- - 3 usuarios (admin, juan, maria, carlos)
-- - 6 cuentas bancarias
-- - ~40 transacciones
-- - 4 presupuestos activos
-- - 5 metas de ahorro
-- - 4 transferencias
-- - Todas las categorías
-- ============================================================================

