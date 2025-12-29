-- ===========================================================
-- ACTUALIZACIÓN: Sistema de Asistencia con Nombres de Negocio
-- Cambiar de app_id a negocio_nombre para mayor seguridad
-- ===========================================================

USE asistencias;

-- 1. Agregar columna negocio_nombre a asistencias_sessions
ALTER TABLE asistencias_sessions 
ADD COLUMN negocio_nombre VARCHAR(100) AFTER session_id;

-- 2. Hacer negocio_nombre único para cada local
ALTER TABLE asistencias_locales 
ADD UNIQUE KEY uk_nombre (nombre);

-- 3. Eliminar columna app_id si existe (ya no se usa)
SET @column_exists = (
    SELECT COUNT(*) 
    FROM information_schema.columns 
    WHERE table_schema = 'asistencias'
    AND table_name = 'asistencias_sessions'
    AND column_name = 'app_id'
);

SET @sql = IF(@column_exists > 0, 
    'ALTER TABLE asistencias_sessions DROP COLUMN app_id', 
    'SELECT "Columna app_id no existe, nada que hacer"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificar estructura final
DESCRIBE asistencias_sessions;
DESCRIBE asistencias_locales;

SELECT '✅ Sistema actualizado. Ahora usa nombre del negocio en lugar de app_id.' as mensaje;
