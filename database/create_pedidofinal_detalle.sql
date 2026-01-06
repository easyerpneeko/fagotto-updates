-- =============================================
-- TABLA DE DETALLE DE PEDIDOS - Para reportes e historial
-- Ejecutar en phpMyAdmin de easyerp (DB MAESTRA)
-- =============================================

USE easyerp;

-- TABLA: Detalle de cada producto en cada pedido
CREATE TABLE IF NOT EXISTS pedidofinal_detalle (
  id INT AUTO_INCREMENT PRIMARY KEY,
  
  -- Relación con el pedido
  request_id INT NOT NULL COMMENT 'ID del pedido en tabla requests (de cada local)',
  app_id INT NOT NULL COMMENT 'ID de la aplicación/local que hizo el pedido',
  
  -- Datos del producto al momento del pedido (snapshot)
  producto_id INT NOT NULL COMMENT 'ID del producto en pedidofinal_precios',
  producto_nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del producto al momento del pedido',
  categoria VARCHAR(50) DEFAULT 'general' COMMENT 'Categoría del producto',
  
  -- Cantidades y precios
  cantidad DECIMAL(10,2) NOT NULL COMMENT 'Cantidad solicitada',
  unidad_medida VARCHAR(50) NOT NULL COMMENT 'Bolsa, Caja, unidad, etc',
  precio_unitario DECIMAL(10,2) NOT NULL COMMENT 'Precio por unidad al momento del pedido',
  subtotal DECIMAL(10,2) NOT NULL COMMENT 'cantidad * precio_unitario',
  
  -- Información del pedido
  contact_name VARCHAR(70) COMMENT 'Nombre del contacto que pidió',
  status VARCHAR(50) COMMENT 'Estado del pedido: pendiente, aprobado, enviado, etc',
  
  -- Fechas y auditoría
  fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha en que se hizo el pedido',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Índices para consultas rápidas
  INDEX idx_request_id (request_id),
  INDEX idx_app_id (app_id),
  INDEX idx_producto_id (producto_id),
  INDEX idx_fecha_pedido (fecha_pedido),
  INDEX idx_categoria (categoria),
  INDEX idx_status (status),
  INDEX idx_app_fecha (app_id, fecha_pedido) COMMENT 'Para reportes por local y fecha'
  
) ENGINE=InnoDB COMMENT='Detalle de productos en cada pedido para reportes e historial';

-- =============================================
-- VERIFICACIÓN
-- =============================================
SELECT '✅ Tabla pedidofinal_detalle creada exitosamente' as mensaje;

SHOW COLUMNS FROM pedidofinal_detalle;

-- =============================================
-- QUERIES DE EJEMPLO PARA REPORTES
-- =============================================

-- 1. Total de compras por producto en un rango de fechas
-- SELECT 
--   producto_nombre,
--   categoria,
--   SUM(cantidad) as total_cantidad,
--   unidad_medida,
--   SUM(subtotal) as total_gastado,
--   COUNT(*) as num_pedidos
-- FROM pedidofinal_detalle
-- WHERE fecha_pedido BETWEEN '2026-01-01' AND '2026-01-20'
-- GROUP BY producto_id, producto_nombre, categoria, unidad_medida
-- ORDER BY total_gastado DESC;

-- 2. Productos más pedidos
-- SELECT 
--   producto_nombre,
--   COUNT(*) as veces_pedido,
--   SUM(cantidad) as cantidad_total
-- FROM pedidofinal_detalle
-- WHERE fecha_pedido >= DATE_SUB(NOW(), INTERVAL 30 DAY)
-- GROUP BY producto_id, producto_nombre
-- ORDER BY cantidad_total DESC
-- LIMIT 10;

-- 3. Compras por local (app_id)
-- SELECT 
--   app_id,
--   COUNT(DISTINCT request_id) as total_pedidos,
--   SUM(subtotal) as total_gastado
-- FROM pedidofinal_detalle
-- WHERE fecha_pedido BETWEEN '2026-01-01' AND '2026-01-31'
-- GROUP BY app_id
-- ORDER BY total_gastado DESC;

-- 4. Historial diario de compras
-- SELECT 
--   DATE(fecha_pedido) as fecha,
--   COUNT(DISTINCT request_id) as pedidos,
--   SUM(subtotal) as total
-- FROM pedidofinal_detalle
-- WHERE fecha_pedido >= DATE_SUB(NOW(), INTERVAL 30 DAY)
-- GROUP BY DATE(fecha_pedido)
-- ORDER BY fecha DESC;
