-- ✅ FIX URGENTE: Permitir NULL en columna product de products_sells
-- Esto permite que las colaciones funcionen correctamente sin necesitar un product_id real
-- ✅ SOLUCIÓN MEJORADA: Maneja foreign keys automáticamente y limpia datos inválidos

-- Paso 1: Buscar y eliminar la foreign key si existe
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

-- Paso 2: Modificar columna para aceptar NULL
ALTER TABLE `products_sells` MODIFY COLUMN `product` BIGINT UNSIGNED NULL;

-- Paso 3: Limpiar datos inválidos (IDs 0 o productos que no existen)
UPDATE `products_sells` SET `product` = NULL WHERE `product` = 0 OR `product` NOT IN (SELECT id FROM products);

-- Paso 4: Recrear foreign key con soporte para NULL
ALTER TABLE `products_sells` ADD CONSTRAINT `products_sells_product_foreign` FOREIGN KEY (`product`) REFERENCES `products` (`id`);

-- ✅ Listo! Ahora las colaciones funcionan perfectamente con product = NULL
