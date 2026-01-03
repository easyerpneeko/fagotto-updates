-- ============================================
-- AGREGAR NUEVOS PRODUCTOS A PEDIDOFINAL_PRECIOS
-- Fecha: 30 de diciembre de 2025
-- Tabla: pedidofinal_precios
-- ============================================

-- 1. PAPEL MANTEQUILLA - $80 por unidad
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo)
VALUES ('Papel Mantequilla', 1.00, 'Unidad', 80.00, 'Insumos', 1);

-- 2. STICKER SET 124 UNIDADES - $5,000 por unidad
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo)
VALUES ('Sticker set 124 unidades', 1.00, 'Unidad', 5000.00, 'Insumos', 1);

-- 3. BOLSA DE DELIVERY - $138 por unidad
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo)
VALUES ('Bolsa de Delivery', 1.00, 'Unidad', 138.00, 'Insumos', 1);

-- 4. PASTA SECA FETTUCCINI - $4,081 por kilo
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo)
VALUES ('Pasta seca Fettuccini', 1.00, 'Kilo', 4081.00, 'Pastas', 1);

-- 5. PASTA SECA BIGOLI - $4,081 por kilo
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo)
VALUES ('Pasta seca Bigoli', 1.00, 'Kilo', 4081.00, 'Pastas', 1);

-- ============================================
-- ACTUALIZAR PESO DE HARINA/MEZCLA A 2.9 KILOS
-- ============================================

-- Actualizar la unidad de medida y cantidad de venta de Harina/Mezcla
UPDATE pedidofinal_precios 
SET unidad_medida = 'Bolsa 2.9k', 
    unidad_venta = 2.90
WHERE producto LIKE '%Harina%' OR producto LIKE '%Mezcla%';

-- ============================================
-- VERIFICAR LOS CAMBIOS
-- ============================================

-- Ver todos los nuevos productos agregados
SELECT id, producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo, fecha_actualizacion
FROM pedidofinal_precios 
WHERE producto IN (
    'Papel Mantequilla',
    'Sticker set 124 unidades',
    'Bolsa de Delivery',
    'Pasta seca Fettuccini',
    'Pasta seca Bigoli'
)
ORDER BY categoria, producto;

-- Ver productos con "Harina" o "Mezcla" para verificar el peso actualizado a 2.9k
SELECT id, producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo
FROM pedidofinal_precios 
WHERE producto LIKE '%Harina%' OR producto LIKE '%Mezcla%'
ORDER BY producto;

-- Ver todos los productos por categoría para confirmar
SELECT categoria, COUNT(*) as total_productos
FROM pedidofinal_precios
WHERE activo = 1
GROUP BY categoria
ORDER BY categoria;

