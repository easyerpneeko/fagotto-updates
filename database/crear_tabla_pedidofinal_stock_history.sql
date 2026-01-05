-- Crear tabla de historial de cambios de stock para pedidofinal_precios
-- Ejecutar en la base de datos MAESTRA (easyerp)

CREATE TABLE IF NOT EXISTS `pedidofinal_stock_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL COMMENT 'ID del producto en pedidofinal_precios',
  `producto_nombre` varchar(255) NOT NULL COMMENT 'Nombre del producto',
  `stock_anterior` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Stock antes del cambio',
  `stock_agregado` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Cantidad agregada (puede ser negativa)',
  `stock_nuevo` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Stock después del cambio',
  `usuario` varchar(100) DEFAULT NULL COMMENT 'Usuario que realizó el cambio',
  `fecha` datetime NOT NULL COMMENT 'Fecha y hora del cambio',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_producto_id` (`producto_id`),
  KEY `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Historial de cambios de stock en pedidofinal_precios';

-- Crear índices adicionales para mejorar rendimiento de consultas
CREATE INDEX `idx_producto_fecha` ON `pedidofinal_stock_history` (`producto_id`, `fecha`);
CREATE INDEX `idx_usuario` ON `pedidofinal_stock_history` (`usuario`);
