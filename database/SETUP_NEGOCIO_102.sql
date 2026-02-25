-- =====================================================
-- CONFIGURACIÓN PARA NEGOCIO 102 (app_id = 102)
-- Solo este negocio tendrá precios personalizados
-- =====================================================

-- PASO 1: Insertar precios personalizados SOLO para negocio 102
-- Los demás negocios seguirán usando precios base

INSERT INTO pedidofinal_precios_negocio (app_id, producto_id, precio_por_unidad, nota) VALUES
-- Salsas con precios especiales para negocio 102
(102, 4, 9000.00, 'Salsa Boloñesa - Precio especial local 102'),
(102, 5, 12000.00, 'Queso - Precio especial local 102'),
(102, 6, 9500.00, 'Salsa Alfredo - Precio especial local 102'),
(102, 7, 15000.00, 'Salsa Camarón - Precio especial local 102'),
(102, 8, 14500.00, 'Salsa Champiñón - Precio especial local 102'),
(102, 9, 38000.00, 'Salsa Pesto - Precio especial local 102'),

-- Ciabattas
(102, 10, 1700.00, 'Ciabatta Pesto - Precio especial local 102'),
(102, 11, 1700.00, 'Ciabatta Queso Crema Salame - Precio especial local 102'),
(102, 12, 1150.00, 'Ciabatta Aliato - Precio especial local 102'),

-- Pastas
(102, 65, 4500.00, 'Pasta Fettuccini - Precio especial local 102'),
(102, 66, 4500.00, 'Pasta Bigoli - Precio especial local 102'),

-- Insumos
(102, 2, 65000.00, 'Vaso PP 470ml - Precio especial local 102'),
(102, 3, 35000.00, 'Sobre de tenedor - Precio especial local 102');

-- =====================================================
-- PRUEBA 1: Ver precios que verá NEGOCIO 102
-- =====================================================

SELECT 
    p.id,
    p.producto,
    p.categoria,
    p.precio_por_unidad as precio_base,
    ppn.precio_por_unidad as precio_custom,
    COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as PRECIO_QUE_VERA_102
FROM pedidofinal_precios p
LEFT JOIN pedidofinal_precios_negocio ppn 
    ON ppn.producto_id = p.id 
    AND ppn.app_id = 102  -- ⭐ Negocio 102
    AND ppn.activo = 1
WHERE p.activo = 1
  AND p.id IN (4, 5, 6, 7, 9, 10, 2)
ORDER BY p.categoria, p.producto;

-- RESULTADO ESPERADO para NEGOCIO 102:
-- id | producto           | precio_base | precio_custom | PRECIO_QUE_VERA_102
-- ---+--------------------+-------------+---------------+--------------------
-- 2  | Vaso PP 470ml      | 61000.00    | 65000.00      | 65000.00  ✅ CUSTOM
-- 4  | Salsa Boloñesa     | 9604.00     | 9000.00       | 9000.00   ✅ CUSTOM
-- 5  | Queso              | 10668.00    | 12000.00      | 12000.00  ✅ CUSTOM
-- 6  | Salsa Alfredo      | 8603.00     | 9500.00       | 9500.00   ✅ CUSTOM
-- 7  | Salsa Camarón      | 13840.00    | 15000.00      | 15000.00  ✅ CUSTOM
-- 9  | Salsa Pesto        | 37125.00    | 38000.00      | 38000.00  ✅ CUSTOM
-- 10 | Ciabatta Pesto     | 1597.00     | 1700.00       | 1700.00   ✅ CUSTOM

-- =====================================================
-- PRUEBA 2: Ver precios que verá NEGOCIO 15 (otro negocio)
-- =====================================================

SELECT 
    p.id,
    p.producto,
    p.categoria,
    p.precio_por_unidad as precio_base,
    ppn.precio_por_unidad as precio_custom,
    COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as PRECIO_QUE_VERA_15
FROM pedidofinal_precios p
LEFT JOIN pedidofinal_precios_negocio ppn 
    ON ppn.producto_id = p.id 
    AND ppn.app_id = 15  -- ⭐ Negocio 15 (DIFERENTE al 102)
    AND ppn.activo = 1
WHERE p.activo = 1
  AND p.id IN (4, 5, 6, 7, 9, 10, 2)
ORDER BY p.categoria, p.producto;

-- RESULTADO ESPERADO para NEGOCIO 15:
-- id | producto           | precio_base | precio_custom | PRECIO_QUE_VERA_15
-- ---+--------------------+-------------+---------------+-------------------
-- 2  | Vaso PP 470ml      | 61000.00    | NULL          | 61000.00  ✅ BASE
-- 4  | Salsa Boloñesa     | 9604.00     | NULL          | 9604.00   ✅ BASE
-- 5  | Queso              | 10668.00    | NULL          | 10668.00  ✅ BASE
-- 6  | Salsa Alfredo      | 8603.00     | NULL          | 8603.00   ✅ BASE
-- 7  | Salsa Camarón      | 13840.00    | NULL          | 13840.00  ✅ BASE
-- 9  | Salsa Pesto        | 37125.00    | NULL          | 37125.00  ✅ BASE
-- 10 | Ciabatta Pesto     | 1597.00     | NULL          | 1597.00   ✅ BASE

-- ✅ Negocio 15 NO VE los precios de negocio 102
-- ✅ Solo ve precios base porque LEFT JOIN no encuentra nada para app_id=15

-- =====================================================
-- PRUEBA 3: Ver precios que verá NEGOCIO 22
-- =====================================================

SELECT 
    p.id,
    p.producto,
    COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as PRECIO_QUE_VERA_22
FROM pedidofinal_precios p
LEFT JOIN pedidofinal_precios_negocio ppn 
    ON ppn.producto_id = p.id 
    AND ppn.app_id = 22  -- ⭐ Negocio 22
    AND ppn.activo = 1
WHERE p.activo = 1 AND p.id = 4;

-- RESULTADO: 9604.00 (precio base)
-- ✅ Negocio 22 NO VE el precio de negocio 102

-- =====================================================
-- PRUEBA 4: Comparar TODOS los negocios
-- =====================================================

-- Ver qué precio ve cada negocio para Salsa Boloñesa (id=4)
SELECT 
    'Negocio 102' as negocio,
    COALESCE(
        (SELECT precio_por_unidad FROM pedidofinal_precios_negocio 
         WHERE app_id=102 AND producto_id=4 AND activo=1),
        (SELECT precio_por_unidad FROM pedidofinal_precios WHERE id=4)
    ) as precio_que_ve
UNION ALL
SELECT 
    'Negocio 15' as negocio,
    COALESCE(
        (SELECT precio_por_unidad FROM pedidofinal_precios_negocio 
         WHERE app_id=15 AND producto_id=4 AND activo=1),
        (SELECT precio_por_unidad FROM pedidofinal_precios WHERE id=4)
    ) as precio_que_ve
UNION ALL
SELECT 
    'Negocio 22' as negocio,
    COALESCE(
        (SELECT precio_por_unidad FROM pedidofinal_precios_negocio 
         WHERE app_id=22 AND producto_id=4 AND activo=1),
        (SELECT precio_por_unidad FROM pedidofinal_precios WHERE id=4)
    ) as precio_que_ve
UNION ALL
SELECT 
    'Negocio 30' as negocio,
    COALESCE(
        (SELECT precio_por_unidad FROM pedidofinal_precios_negocio 
         WHERE app_id=30 AND producto_id=4 AND activo=1),
        (SELECT precio_por_unidad FROM pedidofinal_precios WHERE id=4)
    ) as precio_que_ve;

-- RESULTADO ESPERADO:
-- negocio      | precio_que_ve
-- -------------+--------------
-- Negocio 102  | 9000.00     ← CUSTOM
-- Negocio 15   | 9604.00     ← BASE
-- Negocio 22   | 9604.00     ← BASE  
-- Negocio 30   | 9604.00     ← BASE

-- ✅ SOLO el negocio 102 ve precio diferente
-- ✅ Todos los demás ven precio base

-- =====================================================
-- RESUMEN GARANTIZADO
-- =====================================================

-- ✅ Negocio 102 → Lee su tabla pedidofinal_precios_negocio
-- ✅ Otros negocios → LEFT JOIN no encuentra nada → COALESCE usa precio_base
-- ✅ IMPOSIBLE que un negocio vea precios de otro negocio
-- ✅ El filtro AND ppn.app_id = ? asegura aislamiento total
