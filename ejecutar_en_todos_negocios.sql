-- ====================================================================
-- Script para ejecutar el INSERT en TODAS las bases de datos
-- ====================================================================

-- ⚠️ INSTRUCCIONES:
-- 1. Reemplaza 'DB_NAME_1', 'DB_NAME_2', etc. con los nombres reales de tus BDs
-- 2. Ejecuta este script como root o usuario con permisos en todas las BDs
-- ====================================================================

-- Lista de bases de datos (ajustar según tus negocios)
-- Ejemplo de nombres: fagotto_local_1, fagotto_local_2, fagotto_las_condes, etc.

USE fagotto_local_1;
SOURCE add_productos_pasta_all_locals.sql;

USE fagotto_local_2;
SOURCE add_productos_pasta_all_locals.sql;

USE fagotto_local_3;
SOURCE add_productos_pasta_all_locals.sql;

-- Agregar más negocios aquí...
-- USE fagotto_local_4;
-- SOURCE add_productos_pasta_all_locals.sql;

-- ====================================================================
-- Verificación global (ejecutar después)
-- ====================================================================

-- Ver en qué bases de datos se insertaron los productos
SELECT 
    'fagotto_local_1' AS base_datos,
    COUNT(*) AS productos_insertados
FROM fagotto_local_1.products 
WHERE name IN ('Pasta Bigoli Boloñesa', 'Pasta Fettucine Champiñon')

UNION ALL

SELECT 
    'fagotto_local_2' AS base_datos,
    COUNT(*) AS productos_insertados
FROM fagotto_local_2.products 
WHERE name IN ('Pasta Bigoli Boloñesa', 'Pasta Fettucine Champiñon')

UNION ALL

SELECT 
    'fagotto_local_3' AS base_datos,
    COUNT(*) AS productos_insertados
FROM fagotto_local_3.products 
WHERE name IN ('Pasta Bigoli Boloñesa', 'Pasta Fettucine Champiñon');

-- Agregar más UNIONs para cada negocio...
