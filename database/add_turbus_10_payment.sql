-- ============================================
-- Agregar método de pago Turbus 10%
-- Fecha: 2026-01-14
-- Descripción: Agrega el ajuste turbus_10 al módulo SII para permitir descuento del 10%
-- Este método se identificará en la gestión de ventas mediante specialPayment en los datos de la venta
-- ============================================

-- Paso 1: Verificar que existe el submódulo SII
SELECT @submodule_sii_id := id FROM submodules WHERE keyname = 'sii' LIMIT 1;

-- Paso 2: Insertar el ajuste turbus_10 en settings_submodules
INSERT INTO `settings_submodules` (`name`, `keyname`, `submodule_id`, `created_at`, `updated_at`) 
VALUES 
('Turbus 10%', 'turbus_10', @submodule_sii_id, NOW(), NOW());

-- Paso 3: Obtener el ID del setting recién creado
SELECT @setting_turbus_id := LAST_INSERT_ID();

-- Paso 4: Activar el ajuste en setting_submodules_modules_apps (opcional - por defecto estará desactivado)
-- Nota: Necesitas saber el submodule_module_apps_id del módulo SII
-- Puedes ejecutar esto después desde el panel de administración

-- SELECT @sii_app_id := id FROM submodules_modules_apps WHERE submodule_id = @submodule_sii_id LIMIT 1;
-- INSERT INTO `setting_submodules_modules_apps` 
-- (`submodule_module_apps_id`, `setting_submodule_id`, `active`, `created_at`, `updated_at`) 
-- VALUES 
-- (@sii_app_id, @setting_turbus_id, 0, NOW(), NOW());

-- ============================================
-- IDENTIFICACIÓN EN GESTIÓN DE VENTAS
-- ============================================
-- El método de pago Turbus 10% se identificará en el sistema de la siguiente manera:
--
-- 1. En la venta se guarda un campo specialPayment con la siguiente estructura:
--    {
--      "paymentType": "turbus_10",
--      "method": "Turbus 10%",
--      "description": "Descuento Turbus del 10% aplicado al total",
--      "originalTotal": 10000,
--      "discountAmount": 1000,
--      "finalTotal": 9000,
--      "discountPercentage": 10,
--      "date": "2026-01-14T...",
--      "enabled": true
--    }
--
-- 2. El typeCreateTicket será 'boleta' (genera boleta del SII)
--
-- 3. Para identificar ventas con Turbus 10% en reportes y gestión de ventas:
--    - Buscar en el campo JSON de la venta: specialPayment.paymentType = 'turbus_10'
--    - El total de la venta ya incluye el descuento aplicado (finalTotal)
--    - El monto original antes del descuento está en specialPayment.originalTotal
--    - El monto descontado está en specialPayment.discountAmount
--
-- 4. Ejemplo de query para filtrar ventas con Turbus 10%:
--    SELECT * FROM orders 
--    WHERE JSON_EXTRACT(data, '$.specialPayment.paymentType') = 'turbus_10';
--
-- 5. Para reportes, puedes sumar los descuentos totales:
--    SELECT 
--      SUM(JSON_EXTRACT(data, '$.specialPayment.discountAmount')) as total_descuento_turbus
--    FROM orders 
--    WHERE JSON_EXTRACT(data, '$.specialPayment.paymentType') = 'turbus_10'
--      AND DATE(created_at) = CURDATE();

-- ============================================
-- FIN DEL SCRIPT
-- ============================================

-- Para activar el módulo manualmente después:
-- UPDATE setting_submodules_modules_apps 
-- SET active = 1 
-- WHERE setting_submodule_id = (SELECT id FROM settings_submodules WHERE keyname = 'turbus_10');

-- Para desactivar:
-- UPDATE setting_submodules_modules_apps 
-- SET active = 0 
-- WHERE setting_submodule_id = (SELECT id FROM settings_submodules WHERE keyname = 'turbus_10');
