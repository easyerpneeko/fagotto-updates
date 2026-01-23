-- ===============================================
-- Script para agregar configuración de Merchise
-- ===============================================
-- Este script agrega el setting 'merchise' a la tabla settings_submodules
-- para permitir activar/desactivar el botón Merchise por local
-- Debe ejecutarse en la base de datos de cada local
-- ===============================================

-- Agregar setting de Merchise (id 29)
-- submodule_id = 1 (SII - Ventas) para que aparezca junto con Boleta, Débito, Crédito, etc.
INSERT INTO `settings_submodules` (`id`, `name`, `keyname`, `submodule_id`, `created_at`, `updated_at`) 
VALUES (29, 'Merchise', 'merchise', 1, NOW(), NOW());

-- Agregar setting de Gelateria (id 30)
INSERT INTO `settings_submodules` (`id`, `name`, `keyname`, `submodule_id`, `created_at`, `updated_at`) 
VALUES (30, 'Gelateria', 'gelateria', 1, NOW(), NOW());

-- Nota: Después de ejecutar este script, debes activar los settings en cada local desde el panel de configuración
