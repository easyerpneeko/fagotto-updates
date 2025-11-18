-- Tabla para configuración del módulo Gelateria
CREATE TABLE IF NOT EXISTS `gelateria_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Estado del módulo: 0=Inactivo, 1=Activo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar registro por defecto (desactivado)
INSERT INTO `gelateria_config` (`id`, `is_active`) VALUES (1, 0)
ON DUPLICATE KEY UPDATE `id` = 1;

-- Agregar configuración en settings_submodules
-- Nota: Reemplaza el número 6 con el ID del submódulo de ventas donde quieres que aparezca esta opción
-- Puedes verificar con: SELECT id, name FROM submodules WHERE name LIKE '%venta%';
INSERT INTO `settings_submodules` (`name`, `keyname`, `submodule_id`, `created_at`, `updated_at`) 
VALUES ('Activar Gelateria', 'gelateria_active', 6, NULL, NULL);
