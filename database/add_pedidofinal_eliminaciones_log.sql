-- ===============================================
-- 🗑️ TABLA DE AUDITORÍA: Registro de eliminaciones
-- ===============================================
-- Registra quién, cuándo y qué pedido se eliminó de pedidofinal_detalle
-- Útil para auditoría y seguimiento de correcciones de duplicados

CREATE TABLE IF NOT EXISTS `easyerp`.`pedidofinal_eliminaciones_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_id` INT UNSIGNED NOT NULL COMMENT 'ID del local/negocio',
  `request_id` INT UNSIGNED NOT NULL COMMENT 'ID del pedido eliminado',
  `registros_eliminados` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Cantidad de registros borrados',
  `usuario_nombre` VARCHAR(255) NULL COMMENT 'Nombre del usuario que eliminó',
  `usuario_id` INT UNSIGNED NULL COMMENT 'ID del usuario (si está logueado)',
  `fecha_eliminacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de eliminación',
  `motivo` VARCHAR(500) NULL COMMENT 'Motivo de eliminación (opcional)',
  PRIMARY KEY (`id`),
  INDEX `idx_app_request` (`app_id`, `request_id`),
  INDEX `idx_fecha` (`fecha_eliminacion`),
  INDEX `idx_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de eliminaciones de pedidos duplicados';

-- Ejemplo de consulta para ver historial
-- SELECT * FROM pedidofinal_eliminaciones_log ORDER BY fecha_eliminacion DESC LIMIT 50;
