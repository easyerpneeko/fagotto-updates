-- =====================================================
-- MIGRACIÓN: Agregar campo discount_percentage a products
-- FECHA: 2025-10-27
-- DESCRIPCIÓN: Permite configurar % de descuento por producto
-- =====================================================

-- Ejecutar en la base de datos MATRIZ (easyerp)
USE easyerp;

-- Agregar columna discount_percentage a la tabla products
ALTER TABLE `products` 
ADD COLUMN `discount_percentage` DECIMAL(5,2) NULL DEFAULT 0.00 
COMMENT 'Porcentaje de descuento para pedidos (0-100)';

-- Verificar la columna creada
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    COLUMN_DEFAULT, 
    IS_NULLABLE, 
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'easyerp' 
  AND TABLE_NAME = 'products' 
  AND COLUMN_NAME = 'discount_percentage';

-- =====================================================
-- EJEMPLOS DE USO:
-- =====================================================

-- Aplicar 15% de descuento a producto "Champiñón"
-- UPDATE products SET discount_percentage = 15.00 WHERE name LIKE '%Champiñón%';

-- Aplicar 20% de descuento a producto "Camarón"
-- UPDATE products SET discount_percentage = 20.00 WHERE name LIKE '%Camarón%';

-- Aplicar 10% de descuento a producto "Pesto"
-- UPDATE products SET discount_percentage = 10.00 WHERE name LIKE '%Pesto%';

-- Ver productos con descuento
-- SELECT id, name, price, discount_percentage FROM products WHERE discount_percentage > 0;
