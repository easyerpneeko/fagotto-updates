-- ============================================================================
-- SCRIPT DE PRUEBA COMPLETA PARA MERCHISE
-- Ejecuta este script DESPUÉS de migration_add_platform_discounts.sql
-- y create_merchise_modifiers_options.sql
-- ============================================================================

-- Limpiar datos de ejemplo anteriores (opcional)
DELETE FROM merchise_pedidos_options;
DELETE FROM merchise_pedidos_modifiers;
DELETE FROM merchise_pedidos_items;
DELETE FROM merchise_options;
DELETE FROM merchise_modifiers;
DELETE FROM merchise_items;
DELETE FROM merchise_sections;

-- ============================================================================
-- 1. CREAR SECCIONES DEL MENÚ
-- ============================================================================

INSERT INTO `merchise_sections` (`sku`, `name`, `description`, `imagen`, `orden`, `activo`) VALUES
('sec-1', 'Combos', 'Nuestros combos más populares para compartir', 'https://posfagotto.cl/assets/combos.jpg', 1, 1),
('sec-2', 'Pastas Bigoli', 'Pasta artesanal italiana fresca hecha al momento', 'https://posfagotto.cl/assets/pastas.jpg', 2, 1),
('sec-3', 'Bebidas', 'Bebidas refrescantes para acompañar tu comida', 'https://posfagotto.cl/assets/bebidas.jpg', 3, 1);

-- ============================================================================
-- 2. CREAR ITEMS DEL MENÚ
-- ============================================================================

-- Combo para 2
INSERT INTO `merchise_items` 
(`section_id`, `sku`, `name`, `price`, `description`, `image`, `available`, `calories`, `preparation_time`, `tags`, `orden`) 
VALUES
(1, 'item-15.0', 'Combo para 2', 15090.00, 
 '2 Pastas bigoli a elegir y 2 bebidas de 350ml. Perfecto para compartir.', 
 'https://posfagotto.cl/assets/combo-para-2.jpg', 
 1, 1200, 20, 
 '["popular", "combo", "para-compartir"]', 1);

-- Combo Individual
INSERT INTO `merchise_items` 
(`section_id`, `sku`, `name`, `price`, `description`, `image`, `available`, `calories`, `preparation_time`, `tags`, `orden`) 
VALUES
(1, 'item-2.0', 'Combo Individual', 7550.00, 
 '1 Pasta bigoli a elegir y 1 bebida de 350ml.', 
 'https://posfagotto.cl/assets/combo-individual.jpg', 
 1, 600, 15, 
 '["popular", "combo"]', 2);

-- Pasta Pesto individual
INSERT INTO `merchise_items` 
(`section_id`, `sku`, `name`, `price`, `description`, `image`, `available`, `calories`, `preparation_time`, `tags`, `orden`) 
VALUES
(2, 'item-42.0', 'Pasta Pesto', 6290.00, 
 'Pasta bigoli artesanal con nuestra salsa pesto de albahaca fresca.', 
 'https://posfagotto.cl/assets/pasta-pesto.jpg', 
 1, 480, 12, 
 '["vegetariano", "popular"]', 1);

-- ============================================================================
-- 3. CREAR MODIFIERS PARA COMBO PARA 2
-- ============================================================================

-- Modifier: Elige tus pastas (2)
INSERT INTO `merchise_modifiers` 
(`item_id`, `sku`, `name`, `description`, `required`, `min`, `max`, `orden`) 
VALUES
(1, 'mod-pasta-combo2', 'Elige tus pastas (2)', 'Selecciona 2 pastas de tu preferencia', 1, 2, 2, 1);

-- Options de pastas
INSERT INTO `merchise_options` 
(`modifier_id`, `sku`, `name`, `price`, `quantity`, `description`, `available`, `default`, `orden`) 
VALUES
(1, 'opt-pesto', 'Pasta Pesto', 0.00, 1, 'Salsa de albahaca fresca con piñones', 1, 0, 1),
(1, 'opt-alfredo', 'Pasta Alfredo', 0.00, 1, 'Cremosa salsa blanca a base de queso', 1, 0, 2),
(1, 'opt-bolognesa', 'Pasta Bolognesa', 0.00, 1, 'Salsa de carne molida tradicional italiana', 1, 0, 3),
(1, 'opt-champinon', 'Pasta Champiñón', 0.00, 1, 'Cremosa salsa blanca con champiñones', 1, 0, 4),
(1, 'opt-camarones', 'Pasta Camarones', 1500.00, 1, 'Salsa cremosa con camarones frescos (+$1.500)', 1, 0, 5);

-- Modifier: Elige tus bebidas (2)
INSERT INTO `merchise_modifiers` 
(`item_id`, `sku`, `name`, `description`, `required`, `min`, `max`, `orden`) 
VALUES
(1, 'mod-bebida-combo2', 'Elige tus bebidas (2)', 'Selecciona 2 bebidas de 350ml', 1, 2, 2, 2);

-- Options de bebidas
INSERT INTO `merchise_options` 
(`modifier_id`, `sku`, `name`, `price`, `quantity`, `available`, `default`, `orden`) 
VALUES
(2, 'opt-cocacola', 'Coca-Cola Original 350ml', 0.00, 1, 1, 0, 1),
(2, 'opt-cocacola-zero', 'Coca-Cola Zero 350ml', 0.00, 1, 1, 0, 2),
(2, 'opt-sprite', 'Sprite Original 350ml', 0.00, 1, 1, 0, 3),
(2, 'opt-sprite-zero', 'Sprite Zero 350ml', 0.00, 1, 1, 0, 4),
(2, 'opt-fanta', 'Fanta Naranja 350ml', 0.00, 1, 1, 0, 5);

-- ============================================================================
-- 4. CREAR MODIFIERS PARA COMBO INDIVIDUAL
-- ============================================================================

-- Modifier: Elige tu pasta
INSERT INTO `merchise_modifiers` 
(`item_id`, `sku`, `name`, `description`, `required`, `min`, `max`, `orden`) 
VALUES
(2, 'mod-pasta-combo1', 'Elige tu pasta', 'Selecciona 1 pasta', 1, 1, 1, 1);

-- Options de pastas
INSERT INTO `merchise_options` 
(`modifier_id`, `sku`, `name`, `price`, `quantity`, `description`, `available`, `default`, `orden`) 
VALUES
(3, 'opt-pesto-i', 'Pasta Pesto', 0.00, 1, 'Salsa de albahaca fresca', 1, 0, 1),
(3, 'opt-alfredo-i', 'Pasta Alfredo', 0.00, 1, 'Cremosa salsa blanca', 1, 0, 2),
(3, 'opt-bolognesa-i', 'Pasta Bolognesa', 0.00, 1, 'Salsa de carne tradicional', 1, 0, 3);

-- Modifier: Elige tu bebida
INSERT INTO `merchise_modifiers` 
(`item_id`, `sku`, `name`, `description`, `required`, `min`, `max`, `orden`) 
VALUES
(2, 'mod-bebida-combo1', 'Elige tu bebida', 'Selecciona 1 bebida de 350ml', 1, 1, 1, 2);

-- Options de bebidas
INSERT INTO `merchise_options` 
(`modifier_id`, `sku`, `name`, `price`, `quantity`, `available`, `default`, `orden`) 
VALUES
(4, 'opt-cocacola-i', 'Coca-Cola Original 350ml', 0.00, 1, 1, 0, 1),
(4, 'opt-sprite-i', 'Sprite Original 350ml', 0.00, 1, 1, 0, 2),
(4, 'opt-fanta-i', 'Fanta Naranja 350ml', 0.00, 1, 1, 0, 3);

-- ============================================================================
-- VERIFICAR DATOS CARGADOS
-- ============================================================================

SELECT '✅ SECCIONES CREADAS' AS 'Status';
SELECT sku, name, activo FROM merchise_sections ORDER BY orden;

SELECT '✅ ITEMS CREADOS' AS 'Status';
SELECT i.sku, i.name, i.price, s.name AS seccion 
FROM merchise_items i 
JOIN merchise_sections s ON i.section_id = s.id 
ORDER BY s.orden, i.orden;

SELECT '✅ MODIFIERS CREADOS' AS 'Status';
SELECT 
    m.sku,
    m.name,
    CONCAT(m.min, ' a ', m.max) AS rango,
    m.required,
    i.name AS item
FROM merchise_modifiers m
JOIN merchise_items i ON m.item_id = i.id
ORDER BY i.id, m.orden;

SELECT '✅ OPTIONS CREADAS' AS 'Status';
SELECT 
    o.sku,
    o.name,
    o.price,
    o.available,
    m.name AS modifier
FROM merchise_options o
JOIN merchise_modifiers m ON o.modifier_id = m.id
ORDER BY m.id, o.orden;

SELECT '✅ RESUMEN COMPLETO' AS 'Status';
SELECT 
    s.name AS 'Sección',
    i.sku AS 'SKU Item',
    i.name AS 'Item',
    CONCAT('$', FORMAT(i.price, 0)) AS 'Precio',
    COUNT(DISTINCT m.id) AS 'Modifiers',
    COUNT(o.id) AS 'Total Options'
FROM merchise_sections s
LEFT JOIN merchise_items i ON s.id = i.section_id
LEFT JOIN merchise_modifiers m ON i.id = m.item_id
LEFT JOIN merchise_options o ON m.id = o.modifier_id
WHERE i.available = 1
GROUP BY s.id, i.id
ORDER BY s.orden, i.orden;

-- ============================================================================
-- FIN DEL SCRIPT DE PRUEBA
-- ============================================================================
