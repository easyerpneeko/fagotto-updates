-- =============================================
-- Tabla de Traspasos de Productos entre Locales
-- Autor: Sistema Fagotto ERP
-- Fecha: 2026-02-16
-- =============================================

CREATE TABLE IF NOT EXISTS `traspasos_productos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `local_origen_id` INT(11) NOT NULL COMMENT 'ID del local que envía',
  `local_destino_id` INT(11) NOT NULL COMMENT 'ID del local que recibe',
  `local_origen_nombre` VARCHAR(255) NOT NULL COMMENT 'Nombre del local origen (cache)',
  `local_destino_nombre` VARCHAR(255) NOT NULL COMMENT 'Nombre del local destino (cache)',
  `solicitante_nombre` VARCHAR(255) NULL COMMENT 'Nombre de quien solicita el traspaso',
  `solicitante_telefono` VARCHAR(50) NULL COMMENT 'Teléfono de contacto',
  `productos` TEXT NOT NULL COMMENT 'JSON con array de productos [{id, name, cantidad, unidad_medida}]',
  `total_items` INT(11) NOT NULL DEFAULT 0 COMMENT 'Total de items traspasados',
  `comentarios` TEXT NULL COMMENT 'Observaciones o notas del traspaso',
  `estado` ENUM('pendiente', 'en_transito', 'recibido', 'rechazado') NOT NULL DEFAULT 'pendiente' COMMENT 'Estado del traspaso',
  `fecha_envio` TIMESTAMP NULL COMMENT 'Fecha en que se envió',
  `fecha_recepcion` TIMESTAMP NULL COMMENT 'Fecha en que se recibió',
  `recibido_por` VARCHAR(255) NULL COMMENT 'Nombre de quien recibe en destino',
  `motivo_rechazo` TEXT NULL COMMENT 'Motivo si fue rechazado',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_local_origen` (`local_origen_id`),
  KEY `idx_local_destino` (`local_destino_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_origen_destino` (`local_origen_id`, `local_destino_id`, `estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registro de traspasos de productos entre locales Fagotto';

-- Índice para buscar traspasos por rango de fechas
CREATE INDEX `idx_fecha_estado` ON `traspasos_productos` (`created_at`, `estado`);

-- Índice compuesto para consultas comunes
CREATE INDEX `idx_local_fecha` ON `traspasos_productos` (`local_origen_id`, `created_at` DESC);
