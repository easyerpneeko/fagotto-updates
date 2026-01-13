-- ========================================================
-- Fix AUTOMÁTICO: Agregar "Tarjeta" en TODAS las bases de datos
-- Fecha: 2026-01-12
-- Problema: SQLSTATE[01000]: Warning: 1265 Data truncated for column 'paymode'
-- ========================================================

-- OPCIÓN 1: Script SQL Automático (Ejecutar en MySQL Workbench o phpMyAdmin)
-- Este script encuentra TODAS las bases de datos con tabla 'requests' y aplica el fix

DROP PROCEDURE IF EXISTS fix_paymode_all_databases;

DELIMITER $$

CREATE PROCEDURE fix_paymode_all_databases()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE db_name VARCHAR(255);
    DECLARE sql_statement TEXT;
    
    -- Cursor para obtener todas las bases de datos que tienen la tabla 'requests'
    DECLARE db_cursor CURSOR FOR 
        SELECT DISTINCT TABLE_SCHEMA 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'requests' 
            AND COLUMN_NAME = 'paymode'
            AND TABLE_SCHEMA NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys');
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN db_cursor;
    
    read_loop: LOOP
        FETCH db_cursor INTO db_name;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Construir y ejecutar el ALTER TABLE para cada base de datos
        SET @sql = CONCAT('ALTER TABLE `', db_name, '`.`requests` MODIFY COLUMN paymode ENUM(\'Efectivo\', \'Transferencia\', \'Tarjeta\') DEFAULT \'Efectivo\'');
        
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        
        SELECT CONCAT('✅ Fix aplicado en: ', db_name) AS resultado;
        
    END LOOP;
    
    CLOSE db_cursor;
    
    SELECT '🎉 Fix completado en todas las bases de datos' AS resultado_final;
END$$

DELIMITER ;

-- EJECUTAR EL PROCEDIMIENTO (esto aplica el fix en TODAS las bases de datos)
CALL fix_paymode_all_databases();

-- LIMPIAR (opcional)
DROP PROCEDURE IF EXISTS fix_paymode_all_databases;

-- ========================================================
-- VERIFICACIÓN: Ver todas las bases de datos afectadas
-- ========================================================
SELECT 
    TABLE_SCHEMA AS base_datos,
    COLUMN_TYPE AS tipo_actual
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'requests' 
    AND COLUMN_NAME = 'paymode'
    AND TABLE_SCHEMA NOT IN ('information_schema', 'mysql', 'performance_schema', 'sys')
ORDER BY TABLE_SCHEMA;
