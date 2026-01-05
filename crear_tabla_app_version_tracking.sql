-- Tabla para tracking de versiones de aplicación por negocio
-- Ejecutar en la base de datos centralizada (no en cada local)

CREATE TABLE IF NOT EXISTS `app_version_tracking` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `app_id` INT(11) NOT NULL COMMENT 'ID del negocio/local desde aplication.json',
  `app_name` VARCHAR(255) NOT NULL COMMENT 'Nombre del negocio',
  `current_version` VARCHAR(50) NOT NULL COMMENT 'Versión actual de la app (ej: 1.11.42)',
  `last_ping` DATETIME NOT NULL COMMENT 'Última vez que la app reportó su versión',
  `system_info` TEXT NULL COMMENT 'Información del sistema operativo (opcional)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_app_id` (`app_id`),
  KEY `idx_version` (`current_version`),
  KEY `idx_last_ping` (`last_ping`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para historial de actualizaciones (opcional, para auditoría)
CREATE TABLE IF NOT EXISTS `app_version_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `app_id` INT(11) NOT NULL,
  `from_version` VARCHAR(50) NULL,
  `to_version` VARCHAR(50) NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_app_id` (`app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
