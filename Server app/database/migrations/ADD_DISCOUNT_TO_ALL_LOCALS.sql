-- =====================================================
-- SCRIPT AUTOMÁTICO: Agregar discount_amount a TODOS los locales
-- FECHA: 2025-10-27
-- =====================================================

-- Este script busca todas las BDs de locales y agrega la columna automáticamente

-- 1️⃣ Primero agregar discount_percentage a products en MATRIZ
USE easyerp;
ALTER TABLE `products` 
ADD COLUMN IF NOT EXISTS `discount_percentage` DECIMAL(5,2) NULL DEFAULT 0.00 
COMMENT 'Porcentaje de descuento para pedidos (0-100)';

-- 2️⃣ Crear procedimiento para agregar columna a todas las BDs de locales
DELIMITER $$

DROP PROCEDURE IF EXISTS AddDiscountToAllLocals$$

CREATE PROCEDURE AddDiscountToAllLocals()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE db_name VARCHAR(255);
    DECLARE sql_stmt VARCHAR(500);
    
    -- Cursor para obtener todas las BDs de locales desde la tabla data_bases
    DECLARE db_cursor CURSOR FOR 
        SELECT name FROM easyerp.data_bases WHERE name IS NOT NULL;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN db_cursor;
    
    read_loop: LOOP
        FETCH db_cursor INTO db_name;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Construir y ejecutar ALTER TABLE para cada BD
        SET @sql_stmt = CONCAT(
            'ALTER TABLE `', db_name, '`.`requests` ',
            'ADD COLUMN IF NOT EXISTS `discount_amount` DECIMAL(10,2) NULL DEFAULT 0.00 ',
            'COMMENT ''Monto de descuento aplicado por productos con discount_percentage'''
        );
        
        -- Ejecutar el statement
        PREPARE stmt FROM @sql_stmt;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
        
        SELECT CONCAT('✅ Columna agregada a: ', db_name) AS Status;
        
    END LOOP;
    
    CLOSE db_cursor;
    
    SELECT '🎉 Proceso completado! Todas las BDs actualizadas.' AS FinalStatus;
END$$

DELIMITER ;

-- 3️⃣ Ejecutar el procedimiento
CALL AddDiscountToAllLocals();

-- 4️⃣ Limpiar (opcional)
DROP PROCEDURE IF EXISTS AddDiscountToAllLocals;

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

-- Ver todas las BDs que tienen la columna discount_amount
SELECT 
    TABLE_SCHEMA as 'Base de Datos',
    TABLE_NAME as 'Tabla',
    COLUMN_NAME as 'Columna',
    DATA_TYPE as 'Tipo',
    COLUMN_DEFAULT as 'Default'
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE COLUMN_NAME = 'discount_amount' 
  AND TABLE_NAME = 'requests'
  AND TABLE_SCHEMA IN (SELECT name FROM easyerp.data_bases)
ORDER BY TABLE_SCHEMA;
