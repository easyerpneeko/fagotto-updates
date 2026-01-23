-- Fix para permitir NULL en columna product de products_sells
-- Esto permite que las colaciones no requieran un product_id válido

-- 1. Ver el nombre de la constraint existente (ejecutar primero para verificar):
-- SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
-- WHERE TABLE_NAME = 'products_sells' AND COLUMN_NAME = 'product' AND CONSTRAINT_SCHEMA = DATABASE();

-- 2. Eliminar la foreign key existente
SET @constraint_name = (
    SELECT CONSTRAINT_NAME 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_NAME = 'products_sells' 
    AND COLUMN_NAME = 'product' 
    AND CONSTRAINT_SCHEMA = DATABASE()
    AND REFERENCED_TABLE_NAME IS NOT NULL
    LIMIT 1
);

SET @sql = IF(@constraint_name IS NOT NULL, 
    CONCAT('ALTER TABLE `products_sells` DROP FOREIGN KEY `', @constraint_name, '`'),
    'SELECT "No foreign key found" AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Modificar la columna para permitir NULL PRIMERO
ALTER TABLE `products_sells` MODIFY COLUMN `product` BIGINT UNSIGNED NULL;

-- 4. AHORA actualizar registros con product = 0 a NULL
UPDATE `products_sells` SET `product` = NULL WHERE `product` = 0 OR `product` NOT IN (SELECT id FROM products);

-- 5. Volver a crear la foreign key
ALTER TABLE `products_sells` ADD CONSTRAINT `products_sells_product_foreign` 
  FOREIGN KEY (`product`) REFERENCES `products` (`id`);
