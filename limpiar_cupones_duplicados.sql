-- ====================================================================
-- Script para eliminar los 3 registros duplicados de Cupón
-- IDs: 39, 40, 41
-- ====================================================================

-- Paso 1: Eliminar las referencias en settings_modules_apps
DELETE FROM settings_modules_apps 
WHERE settings_module_id IN (39, 40, 41);

-- Paso 2: Eliminar los 3 registros de settings_modules
DELETE FROM settings_modules 
WHERE id IN (39, 40, 41);

-- Paso 3: Verificar que se eliminaron
SELECT * FROM settings_modules 
WHERE keyname = 'cupon';
