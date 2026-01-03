-- ============================================================================
-- INSTRUCCIONES DE EJECUCIÓN COMPLETA
-- Ejecutar en orden: Primero agregar productos, luego reorganizar
-- Base de datos: easyerp
-- Tabla: pedidofinal_precios
-- ============================================================================

-- PASO 1: Agregar los 5 nuevos productos
-- Archivo: add_nuevos_productos_pedido_final_simple.sql
-- ============================================================================

-- 1. Papel Mantequilla
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo)
VALUES ('Papel Mantequilla', 1.00, 'unidad', 80, 'insumos', 1);

-- 2. Stickers Fagotto
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo)
VALUES ('Stickers Fagotto', 1.00, 'unidad', 5000, 'insumos', 1);

-- 3. Bolsa Delivery Biodegradable
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo)
VALUES ('Bolsa Delivery Biodegradable', 1.00, 'unidad', 138, 'insumos', 1);

-- 4. Pasta Fettuccini
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo)
VALUES ('Pasta Fettuccini', 2.27, 'kg', 4081, 'pastas', 1);

-- 5. Pasta Bigoli
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo)
VALUES ('Pasta Bigoli', 2.27, 'kg', 4081, 'pastas', 1);

-- 6. Actualizar Harina/Mezcla a Bolsa 2.9k
UPDATE pedidofinal_precios 
SET producto = 'Bolsa 2.9k',
    unidad_venta = 2.90
WHERE producto LIKE '%Harina%' OR producto LIKE '%Mezcla%'
LIMIT 1;

-- ============================================================================
-- PASO 2: Normalizar y reorganizar todos los productos
-- Archivo: reorganizar_productos_pedidofinal.sql
-- ============================================================================

-- Normalizar categorías a lowercase
UPDATE pedidofinal_precios SET categoria = 'insumos' WHERE categoria = 'Insumos';
UPDATE pedidofinal_precios SET categoria = 'salsas' WHERE categoria = 'Salsas';
UPDATE pedidofinal_precios SET categoria = 'ciabatta' WHERE categoria = 'Ciabatta';
UPDATE pedidofinal_precios SET categoria = 'pastas' WHERE categoria = 'Pastas';

-- Normalizar nombres de productos (ya existentes)
UPDATE pedidofinal_precios SET producto = 'Bolsa 2.9k' WHERE producto LIKE '%Bolsa 2.9%';
UPDATE pedidofinal_precios SET producto = 'Mezcla' WHERE producto LIKE '%Mezcla%' AND producto NOT LIKE '%Bolsa%';
UPDATE pedidofinal_precios SET producto = 'Papel Mantequilla' WHERE producto LIKE '%Papel Mantequilla%';
UPDATE pedidofinal_precios SET producto = 'Sobre tenedor + servilleta' WHERE producto LIKE '%Sobre tenedor%';
UPDATE pedidofinal_precios SET producto = 'Stickers Fagotto' WHERE producto LIKE '%Sticker%';
UPDATE pedidofinal_precios SET producto = 'Vaso PP 470ml' WHERE producto LIKE '%Vaso%';

-- ============================================================================
-- VERIFICACIÓN: Consulta para ver el orden final
-- ============================================================================
SELECT 
    id,
    producto,
    CONCAT(unidad_venta, ' ', unidad_medida) AS unidad,
    CONCAT('$', FORMAT(precio_por_unidad, 0)) AS precio,
    categoria,
    CASE 
        WHEN categoria = 'insumos' THEN 1
        WHEN categoria = 'salsas' THEN 2
        WHEN categoria = 'ciabatta' THEN 3
        WHEN categoria = 'pastas' THEN 4
        ELSE 5
    END AS orden_categoria,
    activo
FROM pedidofinal_precios
WHERE activo = 1
ORDER BY orden_categoria, producto;

-- ============================================================================
-- RESULTADO ESPERADO (19 productos en orden):
-- ============================================================================
-- INSUMOS (6 productos):
--   1. Bolsa 2.9k
--   2. Bolsa Delivery Biodegradable
--   3. Mezcla
--   4. Papel Mantequilla
--   5. Sobre tenedor + servilleta
--   6. Stickers Fagotto
--   7. Vaso PP 470ml
--
-- SALSAS (9 productos):
--   8. Albahaca pesto
--   9. Bolognesa
--   10. Champiñones
--   11. Cuatro Quesos
--   12. Pesto Rojo
--   13. Pollo Champi
--   14. Pomodoro
--   15. Tuco
--   16. Vodka
--
-- CIABATTA (2 productos):
--   17. Ciabatta Grande
--   18. Ciabatta Pequeña
--
-- PASTAS (2 productos):
--   19. Pasta Bigoli
--   20. Pasta Fettuccini
-- ============================================================================
