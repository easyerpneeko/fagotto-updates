-- Tabla para metas diarias por local, mes y año
CREATE TABLE IF NOT EXISTS `metas_locales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aplication_id` int(11) NOT NULL COMMENT 'ID del local/sucursal',
  `mes` int(2) NOT NULL COMMENT 'Mes (1-12)',
  `anio` int(4) NOT NULL COMMENT 'Año (ej: 2025)',
  `meta_diaria` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Meta diaria para ese mes',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_local_mes_anio` (`aplication_id`, `mes`, `anio`),
  KEY `idx_aplication` (`aplication_id`),
  KEY `idx_mes_anio` (`mes`, `anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Metas diarias por local y mes';
