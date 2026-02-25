-- =====================================================
-- DIAGRAMA VISUAL: Cómo funciona el aislamiento
-- =====================================================

-- BASE DE DATOS:
-- 
-- Tabla: pedidofinal_precios (TODOS LOS NEGOCIOS)
-- +----+--------------------+--------------------+
-- | id | producto           | precio_por_unidad  |
-- +----+--------------------+--------------------+
-- | 4  | Salsa Boloñesa     | 9604.00            |
-- | 9  | Salsa Pesto        | 37125.00           |
-- +----+--------------------+--------------------+
--
-- Tabla: pedidofinal_precios_negocio (SOLO NEGOCIO 102)
-- +--------+-------------+--------------------+
-- | app_id | producto_id | precio_por_unidad  |
-- +--------+-------------+--------------------+
-- | 102    | 4           | 9000.00            |
-- | 102    | 9           | 38000.00           |
-- +--------+-------------+--------------------+

-- =====================================================
-- CUANDO NEGOCIO 102 HACE LA CONSULTA:
-- =====================================================

-- Backend ejecuta (CurrentApp::getApp() = 102):
-- 
-- SELECT 
--     p.*,
--     COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as precio_por_unidad
-- FROM pedidofinal_precios p
-- LEFT JOIN pedidofinal_precios_negocio ppn 
--     ON ppn.producto_id = p.id 
--     AND ppn.app_id = 102  ←←← BUSCA SOLO SUS PRECIOS
--     AND ppn.activo = 1
-- WHERE p.activo = 1;

-- PASO A PASO para producto Salsa Boloñesa (id=4):
-- 1. Lee pedidofinal_precios.id=4 → precio_base = 9604.00
-- 2. LEFT JOIN busca en pedidofinal_precios_negocio:
--    WHERE producto_id=4 AND app_id=102
-- 3. ENCUENTRA: precio_custom = 9000.00
-- 4. COALESCE(9000.00, 9604.00) = 9000.00 ✅
-- 
-- RESULTADO PARA NEGOCIO 102:
-- producto: "Salsa Boloñesa"
-- precio_por_unidad: 9000.00  ← CUSTOM

-- =====================================================
-- CUANDO NEGOCIO 15 HACE LA CONSULTA:
-- =====================================================

-- Backend ejecuta (CurrentApp::getApp() = 15):
-- 
-- SELECT 
--     p.*,
--     COALESCE(ppn.precio_por_unidad, p.precio_por_unidad) as precio_por_unidad
-- FROM pedidofinal_precios p
-- LEFT JOIN pedidofinal_precios_negocio ppn 
--     ON ppn.producto_id = p.id 
--     AND ppn.app_id = 15  ←←← BUSCA SOLO SUS PRECIOS (NO ENCUENTRA NADA)
--     AND ppn.activo = 1
-- WHERE p.activo = 1;

-- PASO A PASO para producto Salsa Boloñesa (id=4):
-- 1. Lee pedidofinal_precios.id=4 → precio_base = 9604.00
-- 2. LEFT JOIN busca en pedidofinal_precios_negocio:
--    WHERE producto_id=4 AND app_id=15
-- 3. NO ENCUENTRA NADA (solo hay app_id=102 en esa tabla)
-- 4. COALESCE(NULL, 9604.00) = 9604.00 ✅
-- 
-- RESULTADO PARA NEGOCIO 15:
-- producto: "Salsa Boloñesa"
-- precio_por_unidad: 9604.00  ← BASE

-- =====================================================
-- CUANDO NEGOCIO 22 HACE LA CONSULTA:
-- =====================================================

-- Backend ejecuta (CurrentApp::getApp() = 22):
-- LEFT JOIN busca: app_id = 22
-- NO ENCUENTRA NADA
-- Devuelve precio base: 9604.00 ✅

-- =====================================================
-- CUANDO NEGOCIO 30 HACE LA CONSULTA:
-- =====================================================

-- Backend ejecuta (CurrentApp::getApp() = 30):
-- LEFT JOIN busca: app_id = 30
-- NO ENCUENTRA NADA
-- Devuelve precio base: 9604.00 ✅

-- =====================================================
-- RESUMEN VISUAL:
-- =====================================================

--      ┌──────────────────────────────────────────┐
--      │  pedidofinal_precios (TABLA MAESTRA)     │
--      │  Todos los negocios ven esto por defecto│
--      └──────────────────────────────────────────┘
--                      ▲
--                      │
--                      │ COALESCE: Si no hay custom, usa este
--                      │
--      ┌───────────────┴────────────────────────┐
--      │                                        │
--      │  LEFT JOIN filtra por app_id actual   │
--      │                                        │
--      └───────────────┬────────────────────────┘
--                      │
--                      ▼
--      ┌──────────────────────────────────────────┐
--      │  pedidofinal_precios_negocio             │
--      │  SOLO negocio 102 tiene filas aquí      │
--      │  Otros negocios: LEFT JOIN = NULL       │
--      └──────────────────────────────────────────┘

-- FLUJO DE CADA NEGOCIO:

-- Negocio 102:
--   Query → busca app_id=102 → ENCUENTRA precios custom → usa custom ✅

-- Negocio 15:
--   Query → busca app_id=15 → NO ENCUENTRA → usa base ✅

-- Negocio 22:
--   Query → busca app_id=22 → NO ENCUENTRA → usa base ✅

-- Negocio 30:
--   Query → busca app_id=30 → NO ENCUENTRA → usa base ✅

-- Negocio 999:
--   Query → busca app_id=999 → NO ENCUENTRA → usa base ✅

-- =====================================================
-- GARANTÍAS DEL SISTEMA:
-- =====================================================

-- ✅ IMPOSIBLE que negocio 15 vea precios de negocio 102
--    → LEFT JOIN filtra por app_id=15
--    → No puede acceder a filas con app_id=102
--
-- ✅ IMPOSIBLE que negocio 102 afecte a otros negocios
--    → Cada negocio ejecuta su propia query con su app_id
--    → Aislamiento total en la cláusula WHERE del JOIN
--
-- ✅ Si eliminas precios de negocio 102
--    → Negocio 102 vuelve a ver precio base
--    → Otros negocios: sin cambios (siempre vieron precio base)
--
-- ✅ Si agregas más negocios con precios custom
--    → Cada uno tendrá su propio app_id
--    → Sin interferencia entre negocios

-- =====================================================
-- EJEMPLO FINAL CON 5 NEGOCIOS:
-- =====================================================

-- Precio de Salsa Pesto (id=9) que ve cada negocio:

-- CREATE TEMPORARY TABLE ejemplo_precios AS
-- SELECT 
--     15 as app_id, 'Negocio Centro' as nombre
-- UNION ALL SELECT 102, 'Negocio Plaza' 
-- UNION ALL SELECT 22, 'Negocio Norte'
-- UNION ALL SELECT 30, 'Negocio Sur'
-- UNION ALL SELECT 50, 'Negocio Mall';

-- SELECT 
--     e.app_id,
--     e.nombre,
--     COALESCE(
--         (SELECT precio_por_unidad 
--          FROM pedidofinal_precios_negocio 
--          WHERE app_id=e.app_id AND producto_id=9 AND activo=1),
--         (SELECT precio_por_unidad FROM pedidofinal_precios WHERE id=9)
--     ) as precio_salsa_pesto
-- FROM ejemplo_precios e;

-- RESULTADO:
-- app_id | nombre           | precio_salsa_pesto
-- -------+------------------+-------------------
-- 15     | Negocio Centro   | 37125.00  (BASE)
-- 102    | Negocio Plaza    | 38000.00  (CUSTOM) ← SOLO ESTE
-- 22     | Negocio Norte    | 37125.00  (BASE)
-- 30     | Negocio Sur      | 37125.00  (BASE)
-- 50     | Negocio Mall     | 37125.00  (BASE)

-- ✅ Solo negocio 102 tiene precio diferente
-- ✅ Resto: precio base de pedidofinal_precios
