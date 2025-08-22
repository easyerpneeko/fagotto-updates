-- ===================================================
-- SCRIPT DE INSTALACIÓN PARA SISTEMA DE ARQUEO DE CAJA
-- ===================================================

-- 1. Crear tabla arqueo_caja
CREATE TABLE IF NOT EXISTS `arqueo_caja` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `app_id` bigint(20) UNSIGNED NOT NULL,
    `fecha_arqueo` date NOT NULL,
    `total_contado` decimal(12,2) NOT NULL DEFAULT 0.00,
    `total_ventas_efectivo` decimal(12,2) NOT NULL DEFAULT 0.00,
    `diferencia` decimal(12,2) NOT NULL DEFAULT 0.00,
    `detalle_conteo` text COLLATE utf8mb4_unicode_ci,
    `observaciones` text COLLATE utf8mb4_unicode_ci,
    `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
    `usuario_nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `negocio_id` bigint(20) UNSIGNED DEFAULT NULL,
    `negocio_nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `estado` enum('abierto','cerrado','revision') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierto',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_arqueo_por_dia` (`app_id`,`fecha_arqueo`),
    KEY `arqueo_caja_app_id_fecha_arqueo_index` (`app_id`,`fecha_arqueo`),
    KEY `arqueo_caja_fecha_arqueo_index` (`fecha_arqueo`),
    KEY `arqueo_caja_app_id_index` (`app_id`),
    KEY `arqueo_caja_usuario_id_index` (`usuario_id`),
    KEY `arqueo_caja_negocio_id_index` (`negocio_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insertar permisos para Arqueo de Caja
INSERT IGNORE INTO `permissions` (`name`, `description`, `module_name`, `created_at`, `updated_at`) VALUES
('gestionar_arqueo', 'Permite crear, editar y eliminar arqueos de caja', 'arqueo_caja', NOW(), NOW()),
('obtener_arqueo', 'Permite consultar arqueos de caja y generar reportes', 'arqueo_caja', NOW(), NOW());

-- 3. Asignar permisos al rol de administrador (ajustar role_id según tu sistema)
-- Nota: Cambiar el valor '1' por el ID del rol administrador en tu sistema
INSERT IGNORE INTO `permission_role` (`permission_id`, `role_id`) 
SELECT p.id, 1 FROM `permissions` p WHERE p.name IN ('gestionar_arqueo', 'obtener_arqueo');

-- 4. Opcional: Crear algunos registros de ejemplo (comentar si no se desea)
/*
INSERT INTO `arqueo_caja` (`app_id`, `fecha_arqueo`, `total_contado`, `total_ventas_efectivo`, `diferencia`, `detalle_conteo`, `observaciones`, `usuario_nombre`, `estado`, `created_at`, `updated_at`) VALUES
(1, CURDATE(), 50000.00, 48500.00, 1500.00, '{"bill_20000":{"cantidad":2,"valor":20000,"subtotal":40000},"bill_10000":{"cantidad":1,"valor":10000,"subtotal":10000}}', 'Arqueo de prueba del sistema', 'Sistema', 'cerrado', NOW(), NOW()),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 75000.00, 75000.00, 0.00, '{"bill_20000":{"cantidad":3,"valor":20000,"subtotal":60000},"bill_10000":{"cantidad":1,"valor":10000,"subtotal":10000},"bill_5000":{"cantidad":1,"valor":5000,"subtotal":5000}}', 'Arqueo exacto', 'Sistema', 'cerrado', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));
*/

-- 5. Verificar instalación
SELECT 'Tabla arqueo_caja creada correctamente' AS status;
SELECT COUNT(*) as total_permisos FROM `permissions` WHERE `module_name` = 'arqueo_caja';
SELECT 'Instalación completada exitosamente' AS mensaje;

-- ===================================================
-- NOTAS IMPORTANTES:
-- ===================================================
-- 1. Asegúrate de ajustar el role_id en la sección 3 según tu sistema
-- 2. Los datos de ejemplo están comentados, descoméntalos si los necesitas
-- 3. Ejecuta este script con privilegios de administrador de base de datos
-- 4. Haz un backup de tu base de datos antes de ejecutar este script
-- 5. Verifica que la tabla 'permissions' y 'permission_role' existan en tu sistema
-- ===================================================
