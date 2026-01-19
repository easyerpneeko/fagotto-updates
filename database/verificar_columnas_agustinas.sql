-- ========================================
-- VERIFICAR COLUMNAS EXISTENTES EN REQUESTS (AGUSTINAS)
-- ========================================

USE erd_app_faggotoo_agustina_65a757ec101ac;

-- Ver TODAS las columnas de la tabla requests
SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'erd_app_faggotoo_agustina_65a757ec101ac'
AND TABLE_NAME = 'requests'
ORDER BY ORDINAL_POSITION;
