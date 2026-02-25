-- =====================================================
-- PRUEBA PRÁCTICA CON DATOS REALES
-- =====================================================

-- PASO 1: Crear la tabla (ejecuta esto primero)
-- Usa el archivo: database/add_producto_precios_negocio.sql

-- PASO 2: Insertar precios de prueba para 3 negocios diferentes
-- Usaremos productos reales de tu dump

-- =====================================================
-- NEGOCIO 1 (app_id=15) - Precios especiales
-- =====================================================

INSERT INTO pedidofinal_precios_negocio (app_id, producto_id, precio_por_unidad, nota) VALUES
(15, 4, 8500.00, 'Descuento por volumen - Salsa Boloñesa'),
(15, 9, 35000.00, 'Precio especial - Salsa Pesto'),
(15, 10, 1400.00, 'Descuento - Ciabatta Pesto');

-- =====================================================
-- NEGOCIO 2 (app_id=22) - Precios estándar + ajuste
-- =====================================================

INSERT INTO pedidofinal_precios_negocio (app_id, producto_id, precio_por_unidad, nota) VALUES
(22, 4, 10000.00, 'Precio zona premium - Salsa Boloñesa'),
(22, 7, 14500.00, 'Ajuste - Salsa Camarón'),
(22, 9, 39000.00, 'Precio alto - Salsa Pesto');

-- =====================================================
-- NEGOCIO 3 (app_id=30) - Precios personalizados
-- =====================================================

INSERT INTO pedidofinal_precios_negocio (app_id, producto_id, precio_por_unidad, nota) VALUES
(30, 4, 9200.00, 'Salsa Boloñesa'),
(30, 5, 11000.00, 'Queso'),
(30, 6, 8800.00, 'Salsa Alfredo'),
(30, 9, 38000.00, 'Salsa Pesto'),
(30, 10, 1650.00, 'Ciabatta Pesto');

-- =====================================================
-- PASO 3: PROBAR LOS PRECIOS
-- =====================================================

-- Ver precios para NEGOCIO 1 (app_id=15)
SELECT 
    p.id,
    p.producto,
    p.categoria,
    p.precio_por_unidad as precio_base,
    ppn.precio_por_unidad as precio_custom,
    COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as precio_final,
    ppn.nota
FROM pedidofinal_precios p
LEFT JOIN pedidofinal_precios_negocio ppn 
    ON ppn.producto_id = p.id 
    AND ppn.app_id = 15
    AND ppn.activo = 1
WHERE p.id IN (4, 5, 9, 10)
ORDER BY p.id;

-- Resultado esperado:
-- id | producto           | precio_base | precio_custom | precio_final | nota
-- ---+--------------------+-------------+---------------+--------------+---------------------------
-- 4  | Salsa Boloñesa     | 9604.00     | 8500.00       | 8500.00      | Descuento por volumen
-- 5  | Queso              | 10668.00    | NULL          | 10668.00     | NULL (usa precio base)
-- 9  | Salsa Pesto        | 37125.00    | 35000.00      | 35000.00     | Precio especial
-- 10 | Ciabatta Pesto     | 1597.00     | 1400.00       | 1400.00      | Descuento

-- =====================================================
-- Ver precios para NEGOCIO 2 (app_id=22)
-- =====================================================

SELECT 
    p.id,
    p.producto,
    p.precio_por_unidad as precio_base,
    COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as precio_final
FROM pedidofinal_precios p
LEFT JOIN pedidofinal_precios_negocio ppn 
    ON ppn.producto_id = p.id 
    AND ppn.app_id = 22
    AND ppn.activo = 1
WHERE p.id IN (4, 7, 9)
ORDER BY p.id;

-- Resultado esperado:
-- id | producto           | precio_base | precio_final
-- ---+--------------------+-------------+--------------
-- 4  | Salsa Boloñesa     | 9604.00     | 10000.00
-- 7  | Salsa Camarón      | 13840.00    | 14500.00
-- 9  | Salsa Pesto        | 37125.00    | 39000.00

-- =====================================================
-- COMPARAR precios del mismo producto entre negocios
-- =====================================================

SELECT 
    ppn.app_id as negocio,
    p.producto,
    p.precio_por_unidad as precio_base,
    ppn.precio_por_unidad as precio_custom,
    (ppn.precio_por_unidad - p.precio_por_unidad) as diferencia,
    ROUND(((ppn.precio_por_unidad - p.precio_por_unidad) / p.precio_por_unidad * 100), 2) as porcentaje_diferencia,
    ppn.nota
FROM pedidofinal_precios_negocio ppn
JOIN pedidofinal_precios p ON p.id = ppn.producto_id
WHERE ppn.producto_id = 9  -- Salsa Pesto
  AND ppn.activo = 1
ORDER BY ppn.app_id;

-- Resultado esperado:
-- negocio | producto     | precio_base | precio_custom | diferencia | porcentaje | nota
-- --------+--------------+-------------+---------------+------------+------------+----------------
-- 15      | Salsa Pesto  | 37125.00    | 35000.00      | -2125.00   | -5.72%     | Precio especial
-- 22      | Salsa Pesto  | 37125.00    | 39000.00      | 1875.00    | 5.05%      | Precio alto
-- 30      | Salsa Pesto  | 37125.00    | 38000.00      | 875.00     | 2.36%      | NULL

-- =====================================================
-- Ver TODOS los productos con precios para negocio 15
-- =====================================================

SELECT 
    p.id,
    p.producto,
    p.categoria,
    p.unidad_medida,
    COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as precio_final,
    CASE 
        WHEN ppn.precio_por_unidad IS NOT NULL THEN 'CUSTOM'
        ELSE 'BASE'
    END as tipo_precio
FROM pedidofinal_precios p
LEFT JOIN pedidofinal_precios_negocio ppn 
    ON ppn.producto_id = p.id 
    AND ppn.app_id = 15
    AND ppn.activo = 1
WHERE p.activo = 1
ORDER BY p.categoria, p.producto;

-- Esto te mostrará TODOS los productos:
-- - Los que tienen precio custom (tipo_precio='CUSTOM')
-- - Los que usan precio base (tipo_precio='BASE')

-- =====================================================
-- PASO 4: Probar desactivar un precio custom
-- =====================================================

-- Desactivar precio custom de Salsa Pesto para negocio 15
UPDATE pedidofinal_precios_negocio 
SET activo = 0 
WHERE app_id = 15 AND producto_id = 9;

-- Ahora negocio 15 usará el precio base de Salsa Pesto (37125.00)

-- =====================================================
-- PASO 5: Eliminar un precio custom
-- =====================================================

-- Si quieres eliminar completamente (no solo desactivar)
-- DELETE FROM pedidofinal_precios_negocio 
-- WHERE app_id = 15 AND producto_id = 9;

-- =====================================================
-- RESUMEN DE LO QUE CAMBIA EN TU BACKEND
-- =====================================================

-- ANTES (query actual en tu API):
-- SELECT * FROM pedidofinal_precios WHERE activo = 1;

-- DESPUÉS (query nuevo):
-- SELECT 
--     p.id,
--     p.producto,
--     p.unidad_venta,
--     p.unidad_medida,
--     p.categoria,
--     p.stock,
--     COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as precio_por_unidad
-- FROM pedidofinal_precios p
-- LEFT JOIN pedidofinal_precios_negocio ppn 
--     ON ppn.producto_id = p.id 
--     AND ppn.app_id = [TU_APP_ID_AQUI]
--     AND ppn.activo = 1
-- WHERE p.activo = 1;

-- ¡ESO ES TODO! El resto de tu código sigue funcionando igual.
