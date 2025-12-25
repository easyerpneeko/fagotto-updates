-- Tabla para almacenar las metas de ventas por local/sucursal
CREATE TABLE IF NOT EXISTS `metas_locales` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sucursal_id` INT NOT NULL COMMENT 'ID de la aplicación/sucursal',
  `sucursal_nombre` VARCHAR(255) NULL COMMENT 'Nombre de la sucursal para referencia',
  `meta_lunes` DECIMAL(12, 2) DEFAULT 0 COMMENT 'Meta de venta para lunes',
  `meta_martes` DECIMAL(12, 2) DEFAULT 0 COMMENT 'Meta de venta para martes',
  `meta_miercoles` DECIMAL(12, 2) DEFAULT 0 COMMENT 'Meta de venta para miércoles',
  `meta_jueves` DECIMAL(12, 2) DEFAULT 0 COMMENT 'Meta de venta para jueves',
  `meta_viernes` DECIMAL(12, 2) DEFAULT 0 COMMENT 'Meta de venta para viernes',
  `meta_sabado` DECIMAL(12, 2) DEFAULT 0 COMMENT 'Meta de venta para sábado',
  `meta_domingo` DECIMAL(12, 2) DEFAULT 0 COMMENT 'Meta de venta para domingo',
  `fecha_inicio` DATE NULL COMMENT 'Fecha desde la cual aplica esta meta',
  `activo` TINYINT(1) DEFAULT 1 COMMENT '1 = activa, 0 = inactiva',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_sucursal_activa` (`sucursal_id`, `activo`),
  INDEX `idx_sucursal` (`sucursal_id`),
  INDEX `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Metas de ventas diarias por sucursal';
