-- ============================================
-- AGREGAR NUEVOS PRODUCTOS A PEDIDOFINAL_PRECIOS
-- Fecha: 30 de diciembre de 2025
-- Tabla: pedidofinal_precios
-- ============================================

-- NUEVOS PRODUCTOS
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo) VALUES
('Papel Mantequilla', 1.00, 'Unidad', 80.00, 'Insumos', 1),
('Sticker set 124 unidades', 1.00, 'Unidad', 5000.00, 'Insumos', 1),
('Bolsa de Delivery', 1.00, 'Unidad', 138.00, 'Insumos', 1),
('Pasta seca Fettuccini', 1.00, 'Kilo', 4081.00, 'Pastas', 1),
('Pasta seca Bigoli', 1.00, 'Kilo', 4081.00, 'Pastas', 1);

-- ============================================
-- ACTUALIZAR PESO DE HARINA/MEZCLA A 2.9 KILOS
-- ============================================

UPDATE pedidofinal_precios 
SET unidad_medida = 'Bolsa 2.9k', unidad_venta = 2.90
WHERE producto LIKE '%Harina%' OR producto LIKE '%Mezcla%';

-- ============================================
-- VERIFICAR LOS CAMBIOS
-- ============================================

-- Ver los nuevos productos agregados
SELECT id, producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo
FROM pedidofinal_precios 
WHERE producto IN (
    'Papel Mantequilla',
    'Sticker set 124 unidades',
    'Bolsa de Delivery',
    'Pasta seca Fettuccini',
    'Pasta seca Bigoli'
)
ORDER BY producto;

-- Ver productos con "Harina" o "Mezcla" para verificar el peso actualizado
SELECT id, producto, unidad_venta, unidad_medida, precio_por_unidad, categoria
FROM pedidofinal_precios 
WHERE producto LIKE '%Harina%' OR producto LIKE '%Mezcla%'
ORDER BY producto;

