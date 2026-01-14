-- Tabla para trackear stock reportado por cada negocio individual
CREATE TABLE IF NOT EXISTS `pedidofinal_stock_por_negocio` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_producto` INT(11) NOT NULL COMMENT 'FK a pedidofinal_precios',
  `producto_nombre` VARCHAR(100) NOT NULL COMMENT 'Nombre del producto al momento del registro',
  `id_negocio` INT(11) DEFAULT NULL COMMENT 'ID del negocio/sucursal',
  `app_id` VARCHAR(50) DEFAULT NULL COMMENT 'App ID del negocio',
  `nombre_negocio` VARCHAR(100) DEFAULT NULL COMMENT 'Nombre del negocio (ej: Merced, Providencia)',
  `cantidad_reportada` DECIMAL(10,2) NOT NULL COMMENT 'Cantidad de stock que reportó el negocio',
  `unidad_medida` VARCHAR(50) DEFAULT NULL COMMENT 'Unidad de medida (kg, unidad, litros, etc)',
  `usuario` VARCHAR(100) DEFAULT NULL COMMENT 'Usuario que hizo el registro',
  `observacion` TEXT DEFAULT NULL COMMENT 'Observaciones adicionales',
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_producto` (`id_producto`),
  INDEX `idx_negocio` (`id_negocio`),
  INDEX `idx_app_id` (`app_id`),
  INDEX `idx_fecha` (`fecha_registro`),
  INDEX `idx_negocio_producto` (`id_negocio`, `id_producto`, `fecha_registro`),
  INDEX `idx_app_producto` (`app_id`, `id_producto`, `fecha_registro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de stock reportado por cada negocio individual';
