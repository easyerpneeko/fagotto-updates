-- Tabla para registrar stock por negocio (historial independiente)
CREATE TABLE IF NOT EXISTS `pedidofinal_stock_por_negocio` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `id_producto` INT NOT NULL,
  `producto_nombre` VARCHAR(100) NOT NULL,
  `cantidad_reportada` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `unidad_medida` VARCHAR(50) NULL,
  `id_negocio` INT NULL,
  `app_id` VARCHAR(50) NULL,
  `nombre_negocio` VARCHAR(255) NULL,
  `usuario` VARCHAR(100) NULL,
  `observacion` TEXT NULL,
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_producto` (`id_producto`),
  INDEX `idx_negocio` (`id_negocio`),
  INDEX `idx_fecha` (`fecha_registro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de stock por negocio - historial independiente';
