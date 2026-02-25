-- ===============================================
-- Script para agregar configuración de Menu Fagotto
-- ===============================================
-- Agrega el setting 'menu_fagotto' a settings_submodules (submodule_id = 1, SII/Ventas)
-- Activar/desactivar desde el panel de configuración por local
-- ===============================================

INSERT INTO `settings_submodules` (`name`, `keyname`, `submodule_id`, `created_at`, `updated_at`) 
VALUES ('Menu Fagotto', 'menu_fagotto', 1, NOW(), NOW());
