-- =====================================================
-- SCRIPT: Crear tabla system_config para versiones
-- Fecha: 2026-01-05
-- Descripción: Sistema robusto de gestión de versiones
-- =====================================================

-- Crear tabla de configuración del sistema
CREATE TABLE IF NOT EXISTS `system_config` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `config_key` varchar(255) NOT NULL COMMENT 'Llave única de configuración',
  `config_value` text NOT NULL COMMENT 'Valor de la configuración',
  `config_type` varchar(255) NOT NULL DEFAULT 'string' COMMENT 'Tipo: string, number, boolean, json',
  `description` text DEFAULT NULL COMMENT 'Descripción de la configuración',
  `is_editable` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Si se puede editar desde el panel',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_config_config_key_unique` (`config_key`),
  KEY `system_config_config_key_index` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuraciones iniciales
INSERT INTO `system_config` 
(`config_key`, `config_value`, `config_type`, `description`, `is_editable`, `created_at`, `updated_at`) 
VALUES
(
  'app_official_version',
  '1.11.50',
  'string',
  'Versión oficial actual de la aplicación Fagotto ERP. Esta es la versión que todos los negocios deberían tener.',
  1,
  NOW(),
  NOW()
),
(
  'version_check_source',
  'database',
  'string',
  'Fuente principal para verificar versión: database, github, or package. Prioridad: 1) database, 2) github, 3) package (fallback)',
  1,
  NOW(),
  NOW()
),
(
  'version_last_updated_by',
  'migration_script',
  'string',
  'Último usuario que actualizó la versión oficial',
  0,
  NOW(),
  NOW()
),
(
  'github_repo_url',
  'easyerpneeko/fagotto-updates',
  'string',
  'Repositorio de GitHub para verificar releases',
  1,
  NOW(),
  NOW()
)
ON DUPLICATE KEY UPDATE 
  `updated_at` = NOW();

-- Verificar inserción
SELECT 
  config_key,
  config_value,
  description,
  updated_at
FROM system_config
WHERE config_key IN (
  'app_official_version',
  'version_check_source',
  'version_last_updated_by',
  'github_repo_url'
);

-- =====================================================
-- LISTO! ✅
-- 
-- Próximos pasos:
-- 1. Verifica que la tabla se creó correctamente
-- 2. Ve a https://posfagotto.cl/admin_version.html
-- 3. Actualiza la versión oficial cuando publiques releases
-- =====================================================
