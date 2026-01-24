-- ============================================================================
-- LIMPIAR TELÉFONOS PREDETERMINADOS DE PRUEBA
-- Descripción: Elimina números de teléfono predeterminados de la base de datos
--              para que el cajero deba ingresar el número real del cliente
-- Fecha: 23 de enero de 2026
-- ============================================================================

-- Actualizar merchise_pedidos - remover números de prueba
UPDATE `merchise_pedidos` 
SET `customer_phone` = NULL 
WHERE `customer_phone` IN ('+56912345678', '+56987654321', '+56900000000');

-- Si tienes otra tabla de pedidos, actualízala también
-- UPDATE `pedidos` 
-- SET `contact_phone` = NULL 
-- WHERE `contact_phone` IN ('+56912345678', '+56987654321', '+56900000000');

-- Verificar los cambios
SELECT 
    id,
    order_id_mercadise,
    customer_name,
    customer_phone,
    platform,
    status,
    created_at
FROM `merchise_pedidos`
WHERE `customer_phone` IS NULL OR `customer_phone` = ''
ORDER BY created_at DESC
LIMIT 10;

-- ============================================================================
-- NOTA IMPORTANTE
-- ============================================================================
-- Después de ejecutar este script:
-- 1. Los números predeterminados se eliminarán
-- 2. El cajero DEBE ingresar el número real del cliente
-- 3. Para usar Twilio sandbox, ingresar: +14155238886
-- 4. Asegúrate de que el número esté verificado en Twilio
-- ============================================================================
