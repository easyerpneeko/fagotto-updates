-- CREAR TABLA HISTORIAL DE STOCK POR DÍA
-- Guarda cada cambio de stock por negocio y fecha para poder consultar histórico

USE easyerp;

CREATE TABLE IF NOT EXISTS `historial_stock_diario` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_producto` INT(11) NOT NULL COMMENT 'FK a pedidofinal_precios',
  `producto_nombre` VARCHAR(255) NOT NULL,
  `id_negocio` INT(11) NULL COMMENT 'FK a aplications',
  `nombre_negocio` VARCHAR(255) NULL,
  `app_id` INT(11) NULL,
  `cantidad_reportada` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `unidad_medida` VARCHAR(50) NULL,
  `fecha_reporte` DATE NOT NULL COMMENT 'Fecha del reporte',
  `usuario` VARCHAR(100) NULL COMMENT 'Usuario que registró',
  `observacion` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_producto` (`id_producto`),
  KEY `idx_negocio` (`id_negocio`),
  KEY `idx_fecha` (`fecha_reporte`),
  KEY `idx_producto_fecha` (`id_producto`, `fecha_reporte`),
  KEY `idx_negocio_fecha` (`id_negocio`, `fecha_reporte`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de stock diario por negocio y producto';

-- Índice único para evitar duplicados de mismo producto, negocio y fecha
CREATE UNIQUE INDEX `idx_unico_producto_negocio_fecha` 
ON `historial_stock_diario` (`id_producto`, `id_negocio`, `fecha_reporte`);

SELECT '✅ Tabla historial_stock_diario creada correctamente' as mensaje;
SELECT 'Ahora se guardará el historial de stock día a día' as info;
