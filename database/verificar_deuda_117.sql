-- Verificar si el local 117 tiene pedidos con deuda pendiente
-- Ejecutar en la base de datos del local 117 (fagotto_local_117 o similar)

-- 1. Ver configuración de bloqueo en easyerp
USE easyerp;
SELECT * FROM pedidofinal_config WHERE app_id = 117;

-- 2. Ver pedidos impagos del local 117
-- (Cambiar 'fagotto_local_117' por el nombre correcto de la BD del local)
USE fagotto_local_117;

SELECT 
    id,
    contact_name,
    price,
    status,
    status_payment,
    created_at
FROM requests
WHERE app_id = 117
    AND status_payment != 'pagado'
    AND status NOT IN ('rechazado', 'cancelado')
ORDER BY created_at DESC;

-- 3. Resumen de deuda
SELECT 
    COUNT(*) as total_pedidos_impagos,
    SUM(price) as monto_total_deuda
FROM requests
WHERE app_id = 117
    AND status_payment != 'pagado'
    AND status NOT IN ('rechazado', 'cancelado');
