-- ===================================================
-- MIGRACIÓN PARA ACTUALIZAR TABLA ARQUEO_CAJA
-- Agregar campos de negocio para mejorar el tracking
-- ===================================================

-- 1. Verificar si la tabla existe
SET @table_exists = (
    SELECT COUNT(*)
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'arqueo_caja'
);

-- 2. Agregar nuevas columnas si la tabla existe y no tiene las columnas
SET @sql = IF(@table_exists > 0,
    'ALTER TABLE `arqueo_caja` 
     ADD COLUMN IF NOT EXISTS `negocio_id` bigint(20) UNSIGNED DEFAULT NULL AFTER `usuario_nombre`,
     ADD COLUMN IF NOT EXISTS `negocio_nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `negocio_id`,
     ADD INDEX IF NOT EXISTS `arqueo_caja_negocio_id_index` (`negocio_id`);',
    'SELECT "Tabla arqueo_caja no existe, ejecute primero install_arqueo_caja.sql" as mensaje;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Verificar que las columnas se agregaron correctamente
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'arqueo_caja'
    AND COLUMN_NAME IN ('negocio_id', 'negocio_nombre')
ORDER BY ORDINAL_POSITION;

-- 4. Mensaje de confirmación
SELECT 'Migración completada: Columnas negocio_id y negocio_nombre agregadas a arqueo_caja' as resultado;

-- ===================================================
-- NOTAS:
-- ===================================================
-- 1. Este script es seguro para ejecutar múltiples veces
-- 2. No afecta datos existentes en la tabla
-- 3. Las nuevas columnas permiten NULL para compatibilidad con registros antiguos
-- 4. Ejecutar después de install_arqueo_caja.sql si la tabla ya existe
-- ===================================================
