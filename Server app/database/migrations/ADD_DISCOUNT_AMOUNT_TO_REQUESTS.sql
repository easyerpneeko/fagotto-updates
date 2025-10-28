-- =====================================================
-- MIGRACIÓN: Agregar campo discount_amount a requests (pedidos)
-- FECHA: 2025-10-27
-- DESCRIPCIÓN: Guardar el monto de descuento aplicado en pedidos
-- ⚠️ IMPORTANTE: Ejecutar en CADA BD de LOCAL (NO en easyerp)
-- =====================================================

-- ⚠️ CAMBIAR EL NOMBRE DE LA BD POR CADA LOCAL
-- Ejemplos: bd_fagotto_providencia, bd_fagotto_maipu, bd_villa_africana, etc.

-- USE nombre_bd_local_aqui;

-- Agregar columna discount_amount a la tabla requests
ALTER TABLE `requests` 
ADD COLUMN `discount_amount` DECIMAL(10,2) NULL DEFAULT 0.00 
COMMENT 'Monto de descuento aplicado por productos con discount_percentage';

-- Verificar la columna creada
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    COLUMN_DEFAULT, 
    IS_NULLABLE, 
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'easyerp' 
  AND TABLE_NAME = 'requests' 
  AND COLUMN_NAME = 'discount_amount';

-- Ver pedidos con descuento aplicado
-- SELECT id, contact_name, price, discount_amount, (price + discount_amount) as total_sin_descuento 
-- FROM requests 
-- WHERE discount_amount > 0
-- ORDER BY id DESC 
-- LIMIT 10;
