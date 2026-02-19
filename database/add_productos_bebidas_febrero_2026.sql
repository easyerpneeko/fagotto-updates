-- ============================================
-- AGREGAR PRODUCTOS DE BEBIDAS A PEDIDOFINAL_PRECIOS
-- Fecha: 19 de febrero de 2026
-- Tabla: pedidofinal_precios
-- ============================================

-- PRODUCTOS BEBIDAS - AGUA
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo) VALUES
('Cachantún Sin Gas', 1.00, 'unidad', 0.00, 'bebidas', 1),
('Cachantún Con Gas', 1.00, 'unidad', 0.00, 'bebidas', 1),
('Jugo Junaeb', 1.00, 'unidad', 0.00, 'bebidas', 1);

-- PRODUCTOS BEBIDAS - LATAS 220 CC
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo) VALUES
('Lata Pepsi 220 CC', 1.00, 'unidad', 0.00, 'bebidas', 1),
('Lata Pepsi Zero 220 CC', 1.00, 'unidad', 0.00, 'bebidas', 1);

-- PRODUCTOS BEBIDAS - LATAS 350 CC
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo) VALUES
('Lata Pepsi 350 CC', 1.00, 'unidad', 0.00, 'bebidas', 1),
('Lata Pepsi Zero 350 CC', 1.00, 'unidad', 0.00, 'bebidas', 1),
('Lata Seven Up 350 CC', 1.00, 'unidad', 0.00, 'bebidas', 1),
('Lata Crush 350 CC', 1.00, 'unidad', 0.00, 'bebidas', 1);

-- PRODUCTOS BEBIDAS - BOTELLAS 1,5 LTS
INSERT INTO pedidofinal_precios (producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo) VALUES
('Botella Pepsi 1,5 LTS', 1.00, 'unidad', 0.00, 'bebidas', 1),
('Botella Pepsi Zero 1,5 LTS', 1.00, 'unidad', 0.00, 'bebidas', 1),
('Botella Crush 1,5 LTS', 1.00, 'unidad', 0.00, 'bebidas', 1),
('Botella Seven Up 1,5 LTS', 1.00, 'unidad', 0.00, 'bebidas', 1);

-- ============================================
-- VERIFICAR LOS CAMBIOS
-- ============================================

-- Ver todos los nuevos productos de bebidas agregados
SELECT id, producto, unidad_venta, unidad_medida, precio_por_unidad, categoria, activo
FROM pedidofinal_precios 
WHERE producto IN (
    'Cachantún Sin Gas',
    'Cachantún Con Gas',
    'Jugo Junaeb',
    'Lata Pepsi 220 CC',
    'Lata Pepsi Zero 220 CC',
    'Lata Pepsi 350 CC',
    'Lata Pepsi Zero 350 CC',
    'Lata Seven Up 350 CC',
    'Lata Crush 350 CC',
    'Botella Pepsi 1,5 LTS',
    'Botella Pepsi Zero 1,5 LTS',
    'Botella Crush 1,5 LTS',
    'Botella Seven Up 1,5 LTS'
)
ORDER BY producto;

-- Ver todos los productos de bebidas
SELECT id, producto, unidad_venta, unidad_medida, precio_por_unidad, categoria
FROM pedidofinal_precios 
WHERE categoria = 'bebidas'
ORDER BY producto;
