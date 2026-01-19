-- ========================================
-- PRUEBA: AGREGAR CAMPOS DE PAGO SOLO A FAGOTTO AGUSTINAS
-- Ejecutar en: ssh root@posfagotto.cl
-- Luego: mysql -u root -p < este_archivo.sql
-- ========================================

-- Base de datos: Fagotto Agustinas
USE erd_app_faggotoo_agustina_65a757ec101ac;

-- Agregar solo la columna whatsapp (las demás ya existen)
ALTER TABLE requests ADD COLUMN whatsapp VARCHAR(20) AFTER contact_phone;

-- Actualizar el último registro con el número de prueba
UPDATE requests 
SET whatsapp = '+56959236215' 
WHERE id = (SELECT id FROM (SELECT id FROM requests ORDER BY created_at DESC LIMIT 1) AS temp);

-- Verificar el último registro actualizado
SELECT id, contact_name, contact_phone, whatsapp, payment_status, created_at
FROM requests 
ORDER BY created_at DESC 
LIMIT 1;

-- Verificar que se agregaron las columnas
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'erd_app_faggotoo_agustina_65a757ec101ac'
AND TABLE_NAME = 'requests'
AND COLUMN_NAME IN ('payment_status', 'payment_id', 'payment_method', 'whatsapp')
ORDER BY ORDINAL_POSITION;

-- Mensaje final
SELECT '✅ Script de prueba completado - Revisar columnas arriba' AS mensaje;
