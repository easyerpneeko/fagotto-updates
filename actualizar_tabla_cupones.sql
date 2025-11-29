-- ============================================
-- ACTUALIZAR TABLA CUPONES - AGREGAR COLUMNAS DE TRACKING
-- ============================================
-- Ejecutar este SQL en la base de datos master del servidor

-- Agregar columnas de tracking una por una
ALTER TABLE cupones ADD COLUMN sucursal_id INT NULL AFTER orden_id;
ALTER TABLE cupones ADD COLUMN usuario_id INT NULL AFTER sucursal_id;
ALTER TABLE cupones ADD COLUMN categoria_id INT NULL AFTER usuario_id;
ALTER TABLE cupones ADD COLUMN categoria_nombre VARCHAR(100) NULL AFTER categoria_id;
ALTER TABLE cupones ADD COLUMN producto_id INT NULL AFTER categoria_nombre;
ALTER TABLE cupones ADD COLUMN producto_nombre VARCHAR(255) NULL AFTER producto_id;
ALTER TABLE cupones ADD COLUMN precio_original DECIMAL(10,2) NULL AFTER producto_nombre;
ALTER TABLE cupones ADD COLUMN precio_con_cupon DECIMAL(10,2) NULL AFTER precio_original;

-- Agregar índices para mejorar las consultas
ALTER TABLE cupones ADD INDEX idx_sucursal (sucursal_id);
ALTER TABLE cupones ADD INDEX idx_usuario (usuario_id);
ALTER TABLE cupones ADD INDEX idx_producto (producto_id);

-- Verificar estructura actualizada
DESCRIBE cupones;

-- Verificar que los cupones existan
SELECT COUNT(*) as 'Total Cupones', 
       SUM(usado = 0) as 'Disponibles',
       SUM(usado = 1) as 'Usados'
FROM cupones;
