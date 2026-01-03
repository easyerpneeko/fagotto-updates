-- ============================================
-- REORGANIZAR Y RENOMBRAR PRODUCTOS PEDIDOFINAL_PRECIOS
-- Fecha: 30 de diciembre de 2025
-- ============================================

-- Paso 1: Normalizar las categorías (todas en minúsculas)
UPDATE pedidofinal_precios SET categoria = 'insumos' WHERE categoria IN ('Insumos', 'insumos');
UPDATE pedidofinal_precios SET categoria = 'salsas' WHERE categoria IN ('Salsas', 'salsas');
UPDATE pedidofinal_precios SET categoria = 'ciabatta' WHERE categoria IN ('Ciabatta', 'ciabatta');
UPDATE pedidofinal_precios SET categoria = 'pastas' WHERE categoria IN ('Pastas', 'pastas');

-- Paso 2: Renombrar productos específicos para mejor ordenamiento
UPDATE pedidofinal_precios SET producto = 'Bolsa de Delivery' WHERE producto = 'Bolsa de Delivery';
UPDATE pedidofinal_precios SET producto = 'Mezcla' WHERE producto = 'Mezcla';
UPDATE pedidofinal_precios SET producto = 'Papel Mantequilla' WHERE producto = 'Papel Mantequilla';
UPDATE pedidofinal_precios SET producto = 'Sobre de tenedor' WHERE producto = 'Sobre de tenedor';
UPDATE pedidofinal_precios SET producto = 'Sticker set 124 unidades' WHERE producto = 'Sticker set 124 unidades';
UPDATE pedidofinal_precios SET producto = 'Vaso' WHERE producto = 'Vaso';

-- Paso 3: Normalizar nombres de Ciabattas
UPDATE pedidofinal_precios SET producto = 'Ciabatta Aliato' WHERE producto IN ('Ciabatta Aliato', 'Ciabatta Aleto');
UPDATE pedidofinal_precios SET producto = 'Ciabatta Pesto' WHERE producto = 'Ciabatta Pesto';
UPDATE pedidofinal_precios SET producto = 'Ciabatta Queso Crema Salame' WHERE producto = 'Ciabatta Queso Crema Salame';

-- ============================================
-- VERIFICAR EL ORDEN FINAL
-- ============================================

-- Ver todos los productos ordenados por categoría y nombre
SELECT 
    id, 
    producto, 
    unidad_venta, 
    unidad_medida, 
    precio_por_unidad, 
    categoria, 
    activo
FROM pedidofinal_precios
WHERE activo = 1
ORDER BY 
    CASE categoria
        WHEN 'insumos' THEN 1
        WHEN 'salsas' THEN 2
        WHEN 'ciabatta' THEN 3
        WHEN 'pastas' THEN 4
        ELSE 5
    END,
    producto ASC;

-- ============================================
-- RESULTADO ESPERADO (solo activos):
-- ============================================
-- INSUMOS:
--   - Bolsa de Delivery
--   - Mezcla
--   - Papel Mantequilla
--   - Sobre de tenedor
--   - Sticker set 124 unidades
--   - Vaso
--
-- SALSAS:
--   - Queso
--   - Salsa Alfredo
--   - Salsa Boloñesa
--   - Salsa Camarón
--   - Salsa Champiñón
--   - Salsa Pesto
--
-- CIABATTA:
--   - Ciabatta Aliato
--   - Ciabatta Pesto
--   - Ciabatta Queso Crema Salame
--
-- PASTAS:
--   - Pasta seca Bigoli
--   - Pasta seca Fettuccini
