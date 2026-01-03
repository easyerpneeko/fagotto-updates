-- Agregar columnas de stock a la tabla pedidofinal_precios
-- Esto permite bloquear productos sin stock en Pedido Final

USE easyerp_master;

ALTER TABLE pedidofinal_precios
ADD COLUMN stock INT NULL DEFAULT NULL COMMENT 'Stock disponible del producto (NULL = sin control de stock)',
ADD COLUMN min_stock INT NULL DEFAULT 0 COMMENT 'Stock mínimo requerido para permitir pedidos';

-- Agregar índice para mejorar consultas por stock
ALTER TABLE pedidofinal_precios
ADD INDEX idx_stock (stock);

-- Comentario explicativo
ALTER TABLE pedidofinal_precios
COMMENT = 'Tabla de precios centralizados con control de stock para Pedido Final';

-- Ejemplos de uso:
-- Producto SIN control de stock (funciona siempre):
-- UPDATE pedidofinal_precios SET stock = NULL, min_stock = NULL WHERE id = 1;

-- Producto CON control de stock:
-- UPDATE pedidofinal_precios SET stock = 500, min_stock = 100 WHERE producto = 'Salsa Alfredo';

-- Ver productos sin stock suficiente:
-- SELECT * FROM pedidofinal_precios WHERE stock IS NOT NULL AND stock < min_stock;
